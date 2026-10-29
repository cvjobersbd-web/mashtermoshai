<?php
/**
 * ✅ ছোট SMTP মেইলার (কোনো লাইব্রেরি লাগে না)
 * cPanel এর ইমেইল অ্যাকাউন্ট (যেমন noreply@mashtermoshai.com) দিয়ে মেইল পাঠায়।
 * সেটিং: Admin → Settings → Email (SMTP) Settings
 * SMTP Host খালি থাকলে PHP এর mail() দিয়ে পাঠানোর চেষ্টা করে।
 */
class Mailer
{
    /** @var resource|null */
    private $sock = null;
    private array $log = [];

    public function __construct(private array $cfg) {}

    public static function fromSettings(): self
    {
        return new self(mail_settings());
    }

    public function isConfigured(): bool
    {
        return trim((string)($this->cfg['mail_host'] ?? '')) !== '' && trim((string)($this->cfg['mail_user'] ?? '')) !== '';
    }

    public function fromEmail(): string
    {
        $f = trim((string)($this->cfg['mail_from_email'] ?? ''));
        if ($f === '') $f = trim((string)($this->cfg['mail_user'] ?? ''));
        return filter_var($f, FILTER_VALIDATE_EMAIL) ? $f : 'noreply@' . (parse_url(site_public_url(), PHP_URL_HOST) ?: 'localhost');
    }

    public function fromName(): string
    {
        $n = trim((string)($this->cfg['mail_from_name'] ?? ''));
        return $n !== '' ? $n : site_settings()->GetSafeSiteName();
    }

    /**
     * @return bool পাঠানো গেলে true; ব্যর্থ হলে $error এ কারণ
     */
    public function send(string $to, string $toName, string $subject, string $html, string $text, ?string &$error = null): bool
    {
        $error = null;
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid recipient email.'; return false; }
        $msg = $this->buildMessage($to, $toName, $subject, $html, $text);
        try {
            if (!$this->isConfigured()) {
                // SMTP সেট করা না থাকলে সার্ভারের mail() দিয়ে চেষ্টা
                [$headers, $body] = explode("\r\n\r\n", $msg, 2);
                $headers = preg_replace('/^(To|Subject):.*\r\n/mi', '', $headers . "\r\n");
                $ok = @mail($to, $this->encodeHeader($subject), $body, rtrim($headers), '-f' . $this->fromEmail());
                if (!$ok) $error = 'Server mail() failed. Admin → Settings এ SMTP সেটিং দিন।';
                return $ok;
            }
            $this->smtpSend($to, $msg);
            return true;
        } catch (Throwable $e) {
            $error = $e->getMessage();
            error_log('[Mailer] ' . $error . ' | ' . implode(' | ', array_slice($this->log, -6)));
            if ($this->sock) { @fclose($this->sock); $this->sock = null; }
            return false;
        }
    }

    private function smtpSend(string $to, string $msg): void
    {
        $host = trim((string)$this->cfg['mail_host']);
        $port = (int)($this->cfg['mail_port'] ?? 465) ?: 465;
        $secure = strtolower((string)($this->cfg['mail_secure'] ?? 'ssl'));
        $ctx = stream_context_create(['ssl' => ['peer_name' => $host, 'verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
        $remote = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $errno = 0; $errstr = '';
        $this->sock = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$this->sock) throw new RuntimeException("SMTP সার্ভারে কানেক্ট করা যায়নি ($host:$port) — $errstr");
        stream_set_timeout($this->sock, 20);

        $this->expect([220]);
        $ehloHost = parse_url(site_public_url(), PHP_URL_HOST) ?: 'localhost';
        $this->cmd('EHLO ' . $ehloHost, [250]);
        if ($secure === 'tls') {
            $this->cmd('STARTTLS', [220]);
            if (!@stream_socket_enable_crypto($this->sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT : 0))) {
                throw new RuntimeException('STARTTLS ব্যর্থ — Port 465 + SSL দিয়ে চেষ্টা করুন।');
            }
            $this->cmd('EHLO ' . $ehloHost, [250]);
        }
        $this->cmd('AUTH LOGIN', [334]);
        $this->cmd(base64_encode((string)$this->cfg['mail_user']), [334], true);
        try {
            $this->cmd(base64_encode((string)$this->cfg['mail_pass']), [235], true);
        } catch (RuntimeException $e) {
            throw new RuntimeException('ইমেইল/পাসওয়ার্ড ভুল (SMTP login failed)।');
        }
        $this->cmd('MAIL FROM:<' . $this->fromEmail() . '>', [250]);
        $this->cmd('RCPT TO:<' . $to . '>', [250, 251]);
        $this->cmd('DATA', [354]);
        // dot-stuffing
        $data = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], ["\n", "\n"], $msg));
        $data = str_replace("\n", "\r\n", $data);
        $this->cmd($data . "\r\n.", [250]);
        try { $this->cmd('QUIT', [221]); } catch (Throwable $e) { }
        @fclose($this->sock);
        $this->sock = null;
    }

    private function cmd(string $line, array $okCodes, bool $secret = false): string
    {
        $this->log[] = 'C: ' . ($secret ? '****' : mb_substr($line, 0, 80));
        if (@fwrite($this->sock, $line . "\r\n") === false) throw new RuntimeException('SMTP write failed.');
        return $this->expect($okCodes);
    }

    private function expect(array $okCodes): string
    {
        $resp = '';
        while (($l = fgets($this->sock, 1024)) !== false) {
            $resp .= $l;
            if (strlen($l) < 4 || $l[3] === ' ') break;
        }
        $this->log[] = 'S: ' . trim(mb_substr($resp, 0, 120));
        $code = (int)substr($resp, 0, 3);
        if (!in_array($code, $okCodes, true)) {
            $meta = stream_get_meta_data($this->sock);
            throw new RuntimeException($meta['timed_out'] ?? false ? 'SMTP সার্ভার সাড়া দিচ্ছে না (timeout)।' : 'SMTP error: ' . trim($resp ?: 'no response'));
        }
        return $resp;
    }

    private function encodeHeader(string $v): string
    {
        return preg_match('/[^\x20-\x7E]/', $v) ? '=?UTF-8?B?' . base64_encode($v) . '?=' : $v;
    }

    private function buildMessage(string $to, string $toName, string $subject, string $html, string $text): string
    {
        $boundary = 'b_' . bin2hex(random_bytes(12));
        $domain = substr(strrchr($this->fromEmail(), '@'), 1) ?: 'localhost';
        $clean = fn(string $s) => trim(str_replace(["\r", "\n"], ' ', $s));
        $h = [
            'Date: ' . date('r'),
            'From: ' . $this->encodeHeader($clean($this->fromName())) . ' <' . $this->fromEmail() . '>',
            'To: ' . ($toName !== '' ? $this->encodeHeader($clean($toName)) . ' ' : '') . '<' . $to . '>',
            'Subject: ' . $this->encodeHeader($clean($subject)),
            'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $domain . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];
        $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text))
            . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . "--$boundary--\r\n";
        return implode("\r\n", $h) . "\r\n\r\n" . $body;
    }

    /** সাইটের রঙে সুন্দর HTML ইমেইল */
    public static function template(string $title, string $introHtml, ?string $btnText = null, ?string $btnUrl = null, string $footNote = ''): string
    {
        $site = e(site_settings()->GetSafeSiteName());
        $btn = ($btnText && $btnUrl)
            ? '<p style="text-align:center;margin:28px 0;"><a href="' . e($btnUrl) . '" style="background:#02B8F5;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 30px;border-radius:10px;display:inline-block;font-size:15px;">' . e($btnText) . '</a></p>'
              . '<p style="font-size:12.5px;color:#64748B;word-break:break-all;">বাটন কাজ না করলে এই লিংকটা ব্রাউজারে কপি করে খুলুন:<br><a href="' . e($btnUrl) . '" style="color:#028EA5;">' . e($btnUrl) . '</a></p>'
            : '';
        return '<!DOCTYPE html><html><body style="margin:0;background:#F5FBFF;font-family:Arial,Helvetica,sans-serif;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5FBFF;padding:24px 12px;"><tr><td align="center">'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #DCECF5;">'
            . '<tr><td style="background:#134856;padding:18px 24px;color:#ffffff;font-size:18px;font-weight:bold;">' . $site . '</td></tr>'
            . '<tr><td style="padding:26px 24px;color:#334155;font-size:15px;line-height:1.7;">'
            . '<h2 style="margin:0 0 12px;color:#08224A;font-size:20px;">' . e($title) . '</h2>' . $introHtml . $btn
            . ($footNote !== '' ? '<p style="font-size:12.5px;color:#7A8CA0;margin-top:22px;">' . $footNote . '</p>' : '')
            . '</td></tr><tr><td style="background:#F5FBFF;padding:14px 24px;font-size:12px;color:#7A8CA0;text-align:center;">© ' . date('Y') . ' ' . $site . '</td></tr>'
            . '</table></td></tr></table></body></html>';
    }
}
