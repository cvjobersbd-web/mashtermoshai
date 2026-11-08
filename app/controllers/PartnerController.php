<?php
/**
 * এজেন্সির রিপোর্ট লিংক — লগইন ছাড়াই শুধু সংখ্যা (ক্লিক / সাইনআপ / কেনা), কোনো ইউজারের নাম/ফোন/ইমেইল না।
 *  GET /Partner/Report?t=TOKEN&from=Y-m-d&to=Y-m-d
 * টোকেন অ্যাডমিন যেকোনো সময় বদলাতে পারেন (Admin → Partner → নতুন লিংক)।
 */
class PartnerController extends Controller
{
    public function Index(): void { redirect('/'); }

    public function Report(): void
    {
        if (!headers_sent()) { header('X-Robots-Tag: noindex, nofollow'); header('Referrer-Policy: no-referrer'); header('Cache-Control: no-store'); }
        $p = $this->db->GetPartnerByToken((string)input_str('t', ''));
        if (!$p) { http_response_code(404); $this->view('Report', null, ['NotFound' => true]); }
        $d = function (string $k): ?string { $v = trim((string)input_str($k, '')); $x = DateTime::createFromFormat('!Y-m-d', $v); return $x && $x->format('Y-m-d') === $v ? $v : null; };
        $from = $d('from'); $to = $d('to');
        if ($from && $to && $from > $to) [$from, $to] = [$to, $from];
        $r = $this->db->GetPartnerReport((int)$p->Id, $from, $to);
        $r['users'] = [];   // ব্যক্তিগত তথ্য এজেন্সিকে দেখানো হয় না
        $this->view('Report', $p, ['Report' => $r, 'From' => $from, 'To' => $to, 'Token' => (string)$p->ReportToken]);
    }
}
