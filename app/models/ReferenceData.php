<?php
/**
 * রেফারেন্স — "কার রেফারেন্সে / কোথা থেকে জেনে এসেছেন?" (Facebook, LinkedIn, Instagram, YouTube ...)
 *  - সাইনআপে বাছাই করে  → Users.HowHeard (+ "অন্যান্য" হলে HowHeardNote)
 *  - প্যাকেজ কেনার সময় → PaymentRequests.Reference (+ ReferenceNote)
 *  - অপশনের তালিকা অ্যাডমিন চালু/বন্ধ/যোগ করতে পারেন: SiteContents.ref_sources (JSON)
 * কলাম নিজে থেকেই তৈরি হয় — SQL ইমপোর্ট লাগবে না।
 */
trait ReferenceData
{
    private bool $howReady = false;

    /** ডিফল্ট অপশন: key => [নাম, আইকন, রং] */
    public const REFERENCE_DEFAULTS = [
        'facebook' => ['Facebook', 'fab fa-facebook', '#1877F2'],
        'instagram' => ['Instagram', 'fab fa-instagram', '#E1306C'],
        'linkedin' => ['LinkedIn', 'fab fa-linkedin', '#0A66C2'],
        'youtube' => ['YouTube', 'fab fa-youtube', '#FF0000'],
        'tiktok' => ['TikTok', 'fab fa-tiktok', '#111827'],
        'whatsapp' => ['WhatsApp', 'fab fa-whatsapp', '#25D366'],
        'telegram' => ['Telegram', 'fab fa-telegram', '#229ED9'],
        'google' => ['Google সার্চ', 'fab fa-google', '#EA4335'],
    ];

    /** আগে ছিল, এখন বাছাই করা যায় না — শুধু পুরোনো ডাটার নাম দেখাতে */
    public const REFERENCE_LEGACY = [
        'friend' => ['বন্ধু / পরিচিত কেউ', 'fas fa-user-group', '#0EA5E9'],
        'teacher' => ['শিক্ষক / টিউটর', 'fas fa-chalkboard-user', '#7C3AED'],
        'agency' => ['এজেন্সি / প্রতিনিধি', 'fas fa-handshake', '#9333EA'],
        'poster' => ['পোস্টার / লিফলেট', 'fas fa-newspaper', '#CA8A04'],
        'other' => ['অন্যান্য', 'fas fa-ellipsis', '#64748B'],
    ];

    public function EnsureReferenceCols(): void
    {
        if ($this->howReady) return;
        $sqlite = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        $this->EnsurePaymentTables();
        foreach ([['Users', 'HowHeard', 30], ['Users', 'HowHeardNote', 100], ['PaymentRequests', 'Reference', 30], ['PaymentRequests', 'ReferenceNote', 100]] as [$t, $c, $n]) {
            try { $this->pdo->query("SELECT `$c` FROM `$t` LIMIT 1"); }
            catch (Throwable $e) {
                try { $this->pdo->exec("ALTER TABLE `$t` ADD COLUMN `$c` " . ($sqlite ? 'TEXT' : "VARCHAR($n)") . ' NULL'); }
                catch (Throwable $e2) { error_log("[ref col $t.$c] " . $e2->getMessage()); }
            }
        }
        $this->howReady = true;
    }

    /**
     * সব অপশন (অ্যাডমিনের যোগ করা সহ): key => ['label', 'icon', 'color', 'on' => bool, 'custom' => bool]
     * "অন্যান্য" সবসময় শেষে আর সবসময় চালু।
     */
    public function ReferenceOptions(bool $onlyOn = false): array
    {
        $saved = json_decode((string)($this->GetSiteContent(['ref_sources' => ''])['ref_sources'] ?? ''), true);
        $saved = is_array($saved) ? $saved : [];
        $out = [];
        foreach (self::REFERENCE_DEFAULTS as $k => [$l, $i, $c]) {
            $out[$k] = ['label' => $l, 'icon' => $i, 'color' => $c, 'on' => !in_array($k, $saved['off'] ?? [], true), 'custom' => false];
        }
        foreach ($saved['custom'] ?? [] as $k => $l) {
            if (is_string($k) && preg_match('/^c_[a-z0-9]{4,12}$/', $k) && is_string($l) && $l !== '')
                $out[$k] = ['label' => $l, 'icon' => 'fas fa-bullhorn', 'color' => '#0F766E', 'on' => !in_array($k, $saved['off'] ?? [], true), 'custom' => true];
        }
        return $onlyOn ? array_filter($out, fn($o) => $o['on']) : $out;
    }

    /** একটা key এর নাম/আইকন/রং — মুছে ফেলা বা অজানা হলেও কিছু একটা দেখায় */
    public function ReferenceInfo(?string $key): array
    {
        if ($key === null || $key === '') return ['label' => 'জানা নেই', 'icon' => 'fas fa-circle-question', 'color' => '#94A3B8', 'on' => false, 'custom' => false];
        if (isset(self::REFERENCE_LEGACY[$key])) { [$l, $i, $c] = self::REFERENCE_LEGACY[$key]; return ['label' => $l, 'icon' => $i, 'color' => $c, 'on' => false, 'custom' => false]; }
        return $this->ReferenceOptions()[$key] ?? ['label' => $key, 'icon' => 'fas fa-tag', 'color' => '#64748B', 'on' => false, 'custom' => true];
    }

    /** ফর্ম থেকে আসা মান যাচাই — [key, ''] অথবা null (তালিকায় নেই)। নিজে হাতে লেখার অপশন আর নেই। */
    public function CleanReference(string $key, string $note = ''): ?array
    {
        $opts = $this->ReferenceOptions(true);
        if (!isset($opts[$key])) return null;
        return [$key, ''];
    }

    public function SetUserHowHeard(int $userId, string $key, string $note): void
    {
        $this->EnsureReferenceCols();
        $this->exec('UPDATE `Users` SET `HowHeard` = :k, `HowHeardNote` = :n WHERE `Id` = :u', ['k' => $key, 'n' => $note !== '' ? $note : null, 'u' => $userId]);
    }

    public function SetPaymentReference(int $paymentId, string $key, string $note): void
    {
        $this->EnsureReferenceCols();
        $this->exec('UPDATE `PaymentRequests` SET `Reference` = :k, `ReferenceNote` = :n WHERE `Id` = :i', ['k' => $key, 'n' => $note !== '' ? $note : null, 'i' => $paymentId]);
    }

    /** ইউজারের সাইনআপের সময়ের রেফারেন্স — [key, note] */
    public function GetUserHowHeard(int $userId): array
    {
        $this->EnsureReferenceCols();
        $r = $this->one('SELECT `HowHeard`, `HowHeardNote` FROM `Users` WHERE `Id` = :u', ['u' => $userId]);
        return [(string)($r->HowHeard ?? ''), (string)($r->HowHeardNote ?? '')];
    }

    /** [UserId => Obj(HowHeard, HowHeardNote)] — অ্যাডমিন ড্যাশবোর্ডের জন্য */
    public function GetHowHeardMap(): array
    {
        try {
            $this->EnsureReferenceCols();
            $m = [];
            foreach ($this->all('SELECT `Id`, `HowHeard`, `HowHeardNote` FROM `Users` WHERE `HowHeard` IS NOT NULL') as $r) $m[(int)$r->Id] = $r;
            return $m;
        } catch (Throwable $e) { return []; }
    }

    /** অ্যাডমিন অপশন সেভ: $off = বন্ধ key গুলো, $custom = [key => label] */
    public function SaveReferenceOptions(array $off, array $custom): void
    {
        $this->SaveSiteContent(['ref_sources' => json_encode(['off' => array_values($off), 'custom' => $custom], JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * রিপোর্ট (তারিখ ফিল্টার সহ):
     *  সাইনআপ = ঐ সময়ে যারা অ্যাকাউন্ট খুলেছে (অ্যাডমিন বাদে), সাইনআপে কোন রেফারেন্স বেছেছে
     *  কেনা   = ঐ সময়ে Approve হওয়া পেমেন্ট, কেনার সময় কোন রেফারেন্স বেছেছে
     *  রিটার্ন: ['rows' => [key => [...]], 'totals' => [...], 'recent' => ইউজার, 'buys' => পেমেন্ট]
     */
    public function GetReferenceReport(?string $from, ?string $to): array
    {
        $this->EnsureReferenceCols();
        try { $this->EnsureEmailVerify(); } catch (Throwable $e) { }
        $blank = fn() => ['signups' => 0, 'buyers' => 0, 'orders' => 0, 'revenue' => 0, 'pending' => 0, 'notes' => []];
        $rows = [];
        $inRange = fn(string $d) => !(($from && substr($d, 0, 10) < $from) || ($to && substr($d, 0, 10) > $to));

        $recent = [];
        foreach ($this->all("SELECT `Id`, `FullName`, `UserName`, `Role`, `CreatedAt`, `HowHeard`, `HowHeardNote` FROM `Users` WHERE `Role` <> 'Admin' ORDER BY `Id` DESC") as $u) {
            if (!$inRange((string)$u->CreatedAt)) continue;
            $k = (string)($u->HowHeard ?? '');
            $rows[$k] ??= $blank();
            $rows[$k]['signups']++;
            if ($k === 'other' && !is_blank($u->HowHeardNote)) $rows[$k]['notes'][] = (string)$u->HowHeardNote;
            $recent[] = $u;
        }
        $buys = []; $buyers = []; $allBuyers = [];
        foreach ($this->all("SELECT pr.`Id`, pr.`UserId`, pr.`Amount`, pr.`Status`, pr.`PackageName`, pr.`Audience`, pr.`Reference`, pr.`ReferenceNote`, pr.`CreatedAt`, pr.`ReviewedAt`,
            u.`FullName`, u.`UserName` FROM `PaymentRequests` pr LEFT JOIN `Users` u ON u.`Id` = pr.`UserId` WHERE pr.`Status` IN ('Approved', 'Pending') ORDER BY pr.`Id` DESC") as $p) {
            $when = (string)($p->Status === 'Approved' ? ($p->ReviewedAt ?: $p->CreatedAt) : $p->CreatedAt);
            if (!$inRange($when)) continue;
            $k = (string)($p->Reference ?? '');
            $rows[$k] ??= $blank();
            if ($p->Status === 'Pending') { $rows[$k]['pending']++; continue; }
            $rows[$k]['orders']++; $rows[$k]['revenue'] += (int)$p->Amount;
            $buyers[$k][(int)$p->UserId] = 1; $allBuyers[(int)$p->UserId] = 1;
            if ($k === 'other' && !is_blank($p->ReferenceNote)) $rows[$k]['notes'][] = (string)$p->ReferenceNote;
            $p->When = $when; $buys[] = $p;
        }
        foreach ($buyers as $k => $b) $rows[$k]['buyers'] = count($b);
        foreach ($rows as $k => $r) $rows[$k]['notes'] = array_slice(array_count_values($r['notes']), 0, 12, true);
        uksort($rows, function ($a, $b) use ($rows) {
            if ($a === '' || $b === '') return $a === '' ? 1 : -1;   // "জানা নেই" সবশেষে
            return [$rows[$b]['signups'] + $rows[$b]['orders'], $rows[$b]['revenue']] <=> [$rows[$a]['signups'] + $rows[$a]['orders'], $rows[$a]['revenue']];
        });
        $tot = $blank(); unset($tot['notes']);
        foreach ($rows as $r) foreach (['signups', 'orders', 'revenue', 'pending'] as $x) $tot[$x] += $r[$x];
        $tot['buyers'] = count($allBuyers);
        return ['rows' => $rows, 'totals' => $tot, 'recent' => array_slice($recent, 0, 50), 'buys' => array_slice($buys, 0, 50)];
    }
}
