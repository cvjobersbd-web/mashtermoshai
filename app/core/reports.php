<?php
/**
 * Admin → Reports: সাইটের সব রিপোর্ট এক জায়গায় (ইউজার, টিচার, শিক্ষার্থী, রেফারেল, পেমেন্ট, প্যাকেজ / মেয়াদ, উইথড্র)।
 * প্রতিটা রিপোর্ট = ['title', 'cols' => [key => লেবেল], 'rows' => [[key => মান, '_uid' => প্রোফাইলের id ...]], 'totals' => [...]]
 * একই ডাটা পেজে টেবিল, Excel (CSV) আর PDF (প্রিন্ট) — তিন জায়গায় ব্যবহার হয়।
 */

const REPORT_TYPES = [
    'summary'       => ['সারসংক্ষেপ', 'fa-chart-pie'],
    'users'         => ['সব ইউজার', 'fa-users'],
    'teachers'      => ['টিচার', 'fa-chalkboard-user'],
    'students'      => ['শিক্ষার্থী / ইউজার', 'fa-user-graduate'],
    'referrals'     => ['কে কার রেফারেন্সে', 'fa-gift'],
    'payments'      => ['পেমেন্ট / প্যাকেজ কেনা', 'fa-money-bill-wave'],
    'subscriptions' => ['প্যাকেজ ও মেয়াদ', 'fa-calendar-check'],
    'withdrawals'   => ['উইথড্র ও ছাড়', 'fa-money-bill-transfer'],
];

/** তারিখ ফিল্টার — Y-m-d হলে রাখে */
function report_date(?string $d): string
{
    $d = trim((string)$d);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : '';
}

/** সময়টা ফিল্টারের মধ্যে কিনা */
function report_in_range(?string $dt, string $from, string $to): bool
{
    $d = substr((string)$dt, 0, 10);
    if ($from !== '' && $d < $from) return false;
    if ($to !== '' && $d > $to) return false;
    return true;
}

/** নাম / ইউজারনেম / ইমেইল / মোবাইল খোঁজা — সব শব্দ মিলতে হবে */
function report_match(string $q, array $fields): bool
{
    $q = mb_strtolower(trim($q));
    if ($q === '') return true;
    $hay = mb_strtolower(implode(' ', array_map('strval', $fields)));
    foreach (preg_split('/\s+/u', ltrim($q, '@')) as $w) if ($w !== '' && !str_contains($hay, $w)) return false;
    return true;
}

function report_dt(?string $v, string $f = 'd M Y'): string { return $v ? fmt_date($v, $f, '') : ''; }

/** সব ইউজারের তথ্য + কার রেফারেন্সে + চালু প্যাকেজ + মোট পেমেন্ট — একবারই পড়া */
function report_base(): array
{
    static $b = null;
    if ($b !== null) return $b;
    $db = db();
    try { $db->EnsureRefCodeCols(); $db->EnsureKycTable(); $db->EnsurePaymentTables(); $db->EnsureReferenceCols(); $db->EnsureCouponTable(); } catch (Throwable $e) { }
    $users = [];
    foreach ($db->all('SELECT `Id`, `FullName`, `UserName`, `Email`, `MobileNumber`, `Role`, `IsActive`, `CreatedAt`, `LastLoginAt` FROM `Users` ORDER BY `Id` DESC') as $u) $users[(int)$u->Id] = $u;
    $now = now();
    $subs = [];   // [uid][Teacher|Student] = চালু থাকা সবচেয়ে দেরিতে শেষ হওয়া
    foreach ($db->all('SELECT * FROM `UserSubscriptions` ORDER BY `EndsAt`') as $s) {
        if ($s->EndsAt !== null && $s->EndsAt <= $now) continue;
        $subs[(int)$s->UserId][(string)$s->Audience] = $s;
    }
    $paid = [];
    foreach ($db->all("SELECT `UserId`, COUNT(*) AS N, SUM(`Amount`) AS S FROM `PaymentRequests` WHERE `Status` = 'Approved' GROUP BY `UserId`") as $r) $paid[(int)$r->UserId] = [(int)$r->N, (int)$r->S];
    $kyc = [];
    foreach ($db->all('SELECT k.* FROM `KycRequests` k WHERE k.`Id` = (SELECT MAX(x.`Id`) FROM `KycRequests` x WHERE x.`UserId` = k.`UserId` AND x.`Kind` = k.`Kind`)') as $k) $kyc[(int)$k->UserId][(string)$k->Kind] = $k;
    $inst = [];
    try { foreach ($db->all('SELECT `UserId`, `Institution` FROM `TeacherRequests` ORDER BY `Id`') as $r) if (!is_blank($r->Institution)) $inst[(int)$r->UserId] = (string)$r->Institution; } catch (Throwable $e) { }
    return $b = compact('users', 'subs', 'paid', 'kyc', 'inst');
}

function report_name(?object $u): string { return $u ? (string)($u->FullName ?: $u->UserName) : 'Deleted user'; }

function report_kyc_bn(?object $k): string
{
    if (!$k) return 'জমা দেয়নি';
    return ['Approved' => 'Approved', 'Pending' => 'যাচাই চলছে', 'Rejected' => 'বাতিল'][(string)$k->Status] ?? (string)$k->Status;
}

function report_sub_text(?object $s): array
{
    if (!$s) return ['নেই', '', ''];
    $left = $s->EndsAt ? max(0, (int)ceil((strtotime((string)$s->EndsAt) - time()) / 86400)) : null;
    return [(string)$s->PackageName, $s->EndsAt ? report_dt($s->EndsAt) : 'মেয়াদ নেই', $left === null ? '—' : $left . ' দিন'];
}

/** রিপোর্ট তৈরি */
function build_report(string $type, string $from, string $to, string $q): array
{
    $db = db();
    $B = report_base();
    $U = $B['users'];
    $rows = [];
    $title = REPORT_TYPES[$type][0] ?? 'রিপোর্ট';

    switch ($type) {
        case 'users':
        case 'teachers':
        case 'students':
            $vs = verified_student_ids();
            foreach ($U as $id => $u) {
                if ($u->Role === 'Admin') continue;
                if ($type === 'teachers' && $u->Role !== 'Teacher') continue;
                if ($type === 'students' && $u->Role === 'Teacher') continue;
                if (!report_in_range($u->CreatedAt, $from, $to)) continue;
                if (!report_match($q, [$u->FullName, $u->UserName, $u->Email, $u->MobileNumber, $id])) continue;
                $role = $u->Role === 'Teacher' ? 'Teacher' : (isset($vs[$id]) ? 'Student' : 'User');
                [$pc, $ps] = $B['paid'][$id] ?? [0, 0];
                $r = ['_uid' => $id, 'id' => $id, 'name' => report_name($u), 'user' => '@' . $u->UserName, 'email' => (string)$u->Email, 'mobile' => (string)$u->MobileNumber, 'role' => $role];
                if ($type === 'teachers') {
                    $sub = report_sub_text($B['subs'][$id]['Teacher'] ?? null);
                    $r += ['inst' => $B['inst'][$id] ?? '', 'kyc' => report_kyc_bn($B['kyc'][$id]['teacher'] ?? null),
                        'pkg' => $sub[0], 'ends' => $sub[1], 'left' => $sub[2]];
                } elseif ($type === 'students') {
                    $k = $B['kyc'][$id]['student'] ?? null;
                    $d = $k ? (json_decode((string)$k->Data, true) ?: []) : [];
                    $sub = report_sub_text($B['subs'][$id]['Student'] ?? null);
                    $r += ['class' => Db::KYC_CLASSES[$d['classLevel'] ?? ''] ?? '', 'school' => (string)($d['institution'] ?? ''), 'kyc' => report_kyc_bn($k),
                        'pkg' => $sub[0], 'ends' => $sub[1], 'left' => $sub[2]];
                }
                $r += ['via' => joined_via_text($id), 'orders' => $pc, 'paid' => $ps, 'status' => (int)$u->IsActive ? 'Active' : 'Inactive',
                    'joined' => report_dt($u->CreatedAt, 'd M Y, h:i A'), 'last' => $u->LastLoginAt ? report_dt($u->LastLoginAt, 'd M Y, h:i A') : 'Never'];
                $rows[] = $r;
            }
            $cols = ['id' => 'ID', 'name' => 'নাম', 'user' => 'ইউজারনেম', 'email' => 'ইমেইল', 'mobile' => 'মোবাইল', 'role' => 'ধরন'];
            if ($type === 'teachers') $cols += ['inst' => 'শিক্ষা প্রতিষ্ঠান', 'kyc' => 'Verification', 'pkg' => 'চালু প্যাকেজ', 'ends' => 'মেয়াদ শেষ', 'left' => 'বাকি'];
            if ($type === 'students') $cols += ['class' => 'ক্লাস', 'school' => 'প্রতিষ্ঠান', 'kyc' => 'Verification', 'pkg' => 'চালু প্যাকেজ', 'ends' => 'মেয়াদ শেষ', 'left' => 'বাকি'];
            $cols += ['via' => 'কার রেফারেন্সে', 'orders' => 'কেনা (বার)', 'paid' => 'মোট পেমেন্ট ৳', 'status' => 'অবস্থা', 'joined' => 'যোগ দিয়েছে', 'last' => 'শেষ লগইন'];
            $totals = ['মোট' => count($rows), 'মোট পেমেন্ট' => '৳' . number_format(array_sum(array_column($rows, 'paid')))];
            if ($type === 'users') foreach (['Teacher', 'Student', 'User'] as $rl) $totals[$rl] = count(array_filter($rows, fn($x) => $x['role'] === $rl));
            if ($type !== 'users') $totals['চালু প্যাকেজ আছে'] = count(array_filter($rows, fn($x) => $x['pkg'] !== 'নেই'));
            break;

        case 'referrals':
            $codes = [];
            foreach ($db->all('SELECT `UserId`, `Code` FROM `ReferralCodes`') as $c) $codes[(int)$c->UserId] = (string)$c->Code;
            foreach ($db->all('SELECT * FROM `Referrals` ORDER BY `Id` DESC') as $r) {
                if (!report_in_range($r->CreatedAt, $from, $to)) continue;
                $a = $U[(int)$r->ReferrerId] ?? null; $b = $U[(int)$r->ReferredUserId] ?? null;
                if (!report_match($q, [$a->FullName ?? '', $a->UserName ?? '', $a->Email ?? '', $b->FullName ?? '', $b->UserName ?? '', $b->Email ?? '', $codes[(int)$r->ReferrerId] ?? ''])) continue;
                $src = (string)($r->Src ?? '');
                $srcL = ($r->Via ?? '') === 'code' ? 'প্যাকেজ কেনার সময় কোড' : (($r->Via ?? '') === 'signupcode' ? 'সাইনআপে কোড' : (isset(Db::REF_SOURCES[$src]) && $src !== 'link' ? Db::REF_SOURCES[$src][0] : 'লিংক'));
                $rows[] = ['_uid' => (int)$r->ReferredUserId, '_uid2' => (int)$r->ReferrerId,
                    'name' => report_name($b), 'user' => $b ? '@' . $b->UserName : '', 'email' => (string)($b->Email ?? ''),
                    'name2' => report_name($a), 'user2' => $a ? '@' . $a->UserName : '', 'code' => $codes[(int)$r->ReferrerId] ?? '', 'src' => $srcL,
                    'status' => ['Credited' => 'প্যাকেজ কিনেছে', 'Waiting' => 'এখনো কেনেনি', 'Revoked' => 'বাতিল'][(string)$r->Status] ?? (string)$r->Status,
                    'bonus' => (int)$r->Bonus, 'nobonus' => $db->IsRefNoBonus((int)$r->ReferrerId) ? 'হ্যাঁ' : '', 'date' => report_dt($r->CreatedAt, 'd M Y, h:i A')];
            }
            $cols = ['name' => 'যে এসেছে', 'user' => 'ইউজারনেম', 'email' => 'ইমেইল', 'name2' => 'রেফার করেছে', 'user2' => 'রেফারারের ইউজারনেম', 'code' => 'কোড',
                'src' => 'কোথা থেকে', 'status' => 'অবস্থা', 'bonus' => 'বোনাস ৳', 'nobonus' => 'বোনাস ছাড়া রেফারার', 'date' => 'তারিখ'];
            $totals = ['মোট রেফারেল' => count($rows), 'প্যাকেজ কিনেছে' => count(array_filter($rows, fn($x) => $x['status'] === 'প্যাকেজ কিনেছে')),
                'মোট বোনাস' => '৳' . number_format(array_sum(array_column($rows, 'bonus'))), 'রেফারার' => count(array_unique(array_column($rows, '_uid2')))];
            break;

        case 'payments':
            foreach ($db->all('SELECT * FROM `PaymentRequests` ORDER BY `Id` DESC') as $p) {
                if (!report_in_range($p->CreatedAt, $from, $to)) continue;
                $u = $U[(int)$p->UserId] ?? null;
                if (!report_match($q, [$u->FullName ?? '', $u->UserName ?? '', $u->Email ?? '', $u->MobileNumber ?? '', $p->TxnId, $p->PackageName, $p->Id])) continue;
                $disc = (int)($p->RefDiscount ?? 0);
                $rows[] = ['_uid' => (int)$p->UserId, 'id' => (int)$p->Id, 'date' => report_dt($p->CreatedAt, 'd M Y, h:i A'),
                    'name' => report_name($u), 'user' => $u ? '@' . $u->UserName : '', 'email' => (string)($u->Email ?? ''),
                    'for' => ['Teacher' => 'টিচার', 'Student' => 'শিক্ষার্থী', 'Course' => 'কোর্স'][(string)$p->Audience] ?? (string)$p->Audience,
                    'pkg' => (string)$p->PackageName, 'months' => (int)$p->DurationMonths ?: '', 'price' => (int)$p->Amount + $disc, 'disc' => $disc, 'amount' => (int)$p->Amount,
                    'method' => (string)$p->MethodName, 'txn' => (string)$p->TxnId, 'refcode' => (string)($p->RefCode ?? ''), 'coupon' => (string)($p->CouponCode ?? ''),
                    'status' => ['Approved' => 'Accepted', 'Pending' => 'অপেক্ষায়', 'Rejected' => 'Rejected'][(string)$p->Status] ?? (string)$p->Status,
                    'reviewed' => report_dt($p->ReviewedAt, 'd M Y, h:i A'), 'via' => $u ? joined_via_text((int)$p->UserId) : ''];
            }
            $cols = ['id' => '#', 'date' => 'জমার তারিখ', 'name' => 'নাম', 'user' => 'ইউজারনেম', 'email' => 'ইমেইল', 'for' => 'কার জন্য', 'pkg' => 'প্যাকেজ', 'months' => 'মাস',
                'price' => 'দাম ৳', 'disc' => 'রেফারেল ছাড় ৳', 'amount' => 'পাঠিয়েছে ৳', 'method' => 'মাধ্যম', 'txn' => 'TxnID', 'refcode' => 'রেফারেল কোড', 'coupon' => 'কুপন', 'status' => 'অবস্থা',
                'reviewed' => 'যাচাইয়ের তারিখ', 'via' => 'কার রেফারেন্সে'];
            $ok = array_filter($rows, fn($x) => $x['status'] === 'Accepted');
            $totals = ['মোট পেমেন্ট' => count($rows), 'Accepted' => count($ok), 'অপেক্ষায়' => count(array_filter($rows, fn($x) => $x['status'] === 'অপেক্ষায়')),
                'আয় (Accepted)' => '৳' . number_format(array_sum(array_column($ok, 'amount'))), 'রেফারেল ছাড়' => '৳' . number_format(array_sum(array_column($ok, 'disc')))];
            break;

        case 'subscriptions':
            $now = now();
            foreach ($db->all('SELECT s.*, p.`Amount`, p.`MethodName` FROM `UserSubscriptions` s LEFT JOIN `PaymentRequests` p ON p.`Id` = s.`PaymentId` ORDER BY s.`Id` DESC') as $s) {
                if (!report_in_range($s->StartsAt, $from, $to)) continue;
                $u = $U[(int)$s->UserId] ?? null;
                if (!report_match($q, [$u->FullName ?? '', $u->UserName ?? '', $u->Email ?? '', $s->PackageName])) continue;
                $active = $s->EndsAt === null || $s->EndsAt > $now;
                $left = $s->EndsAt && $active ? (int)ceil((strtotime((string)$s->EndsAt) - time()) / 86400) . ' দিন' : ($active ? '—' : 'শেষ');
                $rows[] = ['_uid' => (int)$s->UserId, 'name' => report_name($u), 'user' => $u ? '@' . $u->UserName : '', 'email' => (string)($u->Email ?? ''),
                    'for' => $s->Audience === 'Teacher' ? 'টিচার' : 'শিক্ষার্থী', 'pkg' => (string)$s->PackageName,
                    'start' => report_dt($s->StartsAt), 'end' => $s->EndsAt ? report_dt($s->EndsAt) : 'মেয়াদ নেই', 'left' => $left,
                    'state' => $active ? 'চালু' : 'মেয়াদ শেষ', 'posts' => $s->Audience === 'Student' ? ((int)$s->PostsTotal === 0 ? 'আনলিমিটেড' : (int)$s->PostsUsed . ' / ' . (int)$s->PostsTotal) : '',
                    'amount' => $s->Amount !== null ? (int)$s->Amount : '', 'method' => (string)($s->MethodName ?? '')];
            }
            $cols = ['name' => 'নাম', 'user' => 'ইউজারনেম', 'email' => 'ইমেইল', 'for' => 'কার জন্য', 'pkg' => 'প্যাকেজ', 'start' => 'শুরু', 'end' => 'শেষ', 'left' => 'বাকি',
                'state' => 'অবস্থা', 'posts' => 'পোস্ট (ব্যবহার / মোট)', 'amount' => 'পেমেন্ট ৳', 'method' => 'মাধ্যম'];
            $totals = ['মোট' => count($rows), 'চালু' => count(array_filter($rows, fn($x) => $x['state'] === 'চালু')),
                'চালু টিচার প্যাকেজ' => count(array_filter($rows, fn($x) => $x['state'] === 'চালু' && $x['for'] === 'টিচার')),
                'চালু শিক্ষার্থী প্যাকেজ' => count(array_filter($rows, fn($x) => $x['state'] === 'চালু' && $x['for'] === 'শিক্ষার্থী'))];
            break;

        case 'withdrawals':
            foreach ($db->all('SELECT * FROM `ReferralWithdrawals` ORDER BY `Id` DESC') as $w) {
                if (!report_in_range($w->CreatedAt, $from, $to)) continue;
                $u = $U[(int)$w->UserId] ?? null;
                if (!report_match($q, [$u->FullName ?? '', $u->UserName ?? '', $u->Email ?? '', $w->AccountNumber, $w->TxnId])) continue;
                $isD = $w->Method === 'Discount';
                $rows[] = ['_uid' => (int)$w->UserId, 'date' => report_dt($w->CreatedAt, 'd M Y, h:i A'), 'name' => report_name($u), 'user' => $u ? '@' . $u->UserName : '',
                    'kind' => $isD ? 'প্যাকেজে ছাড়' : 'টাকা তোলা', 'amount' => (int)$w->Amount, 'method' => $isD ? (string)$w->AccountNumber : (string)$w->Method,
                    'acc' => $isD ? '' : trim((string)$w->AccountName . ' ' . $w->AccountNumber . ' ' . $w->BankName), 'status' => ['Pending' => 'অপেক্ষায়', 'Paid' => $isD ? 'ব্যবহার হয়েছে' : 'পাঠানো হয়েছে', 'Rejected' => $isD ? 'ফেরত' : 'বাতিল'][(string)$w->Status] ?? (string)$w->Status,
                    'txn' => (string)$w->TxnId, 'done' => report_dt($w->ProcessedAt, 'd M Y, h:i A')];
            }
            $cols = ['date' => 'তারিখ', 'name' => 'নাম', 'user' => 'ইউজারনেম', 'kind' => 'ধরন', 'amount' => 'পরিমাণ ৳', 'method' => 'মাধ্যম / পেমেন্ট', 'acc' => 'অ্যাকাউন্ট', 'status' => 'অবস্থা', 'txn' => 'TxnID', 'done' => 'সম্পন্ন'];
            $totals = ['মোট' => count($rows), 'পাঠানো হয়েছে' => '৳' . number_format(array_sum(array_column(array_filter($rows, fn($x) => $x['status'] === 'পাঠানো হয়েছে'), 'amount'))),
                'প্যাকেজে ছাড়' => '৳' . number_format(array_sum(array_column(array_filter($rows, fn($x) => $x['status'] === 'ব্যবহার হয়েছে'), 'amount'))),
                'অপেক্ষায়' => '৳' . number_format(array_sum(array_column(array_filter($rows, fn($x) => $x['status'] === 'অপেক্ষায়' && $x['kind'] === 'টাকা তোলা'), 'amount')))];
            break;

        default: // summary — প্যাকেজ অনুযায়ী বিক্রি (টেবিল) + উপরে কার্ড
            $type = 'summary';
            $vs = verified_student_ids();
            $cards = ['মোট ইউজার' => 0, 'টিচার' => 0, 'শিক্ষার্থী (Verified)' => 0, 'সাধারণ ইউজার' => 0];
            foreach ($U as $id => $u) {
                if ($u->Role === 'Admin' || !report_in_range($u->CreatedAt, $from, $to)) continue;
                $cards['মোট ইউজার']++;
                if ($u->Role === 'Teacher') $cards['টিচার']++; elseif (isset($vs[$id])) $cards['শিক্ষার্থী (Verified)']++; else $cards['সাধারণ ইউজার']++;
            }
            $agg = [];
            $rev = 0; $n = 0; $pend = 0; $months = [];
            foreach ($db->all('SELECT * FROM `PaymentRequests`') as $p) {
                if (!report_in_range($p->CreatedAt, $from, $to)) continue;
                if ($p->Status === 'Pending') { $pend++; continue; }
                if ($p->Status !== 'Approved') continue;
                $k = $p->Audience . '|' . $p->PackageName;
                $agg[$k] ??= ['for' => ['Teacher' => 'টিচার', 'Student' => 'শিক্ষার্থী', 'Course' => 'কোর্স'][(string)$p->Audience] ?? $p->Audience, 'pkg' => (string)$p->PackageName, 'count' => 0, 'buyers' => [], 'amount' => 0, 'disc' => 0];
                $agg[$k]['count']++; $agg[$k]['buyers'][(int)$p->UserId] = 1; $agg[$k]['amount'] += (int)$p->Amount; $agg[$k]['disc'] += (int)($p->RefDiscount ?? 0);
                $rev += (int)$p->Amount; $n++;
                $m = substr((string)($p->ReviewedAt ?: $p->CreatedAt), 0, 7);
                $months[$m] = ($months[$m] ?? 0) + (int)$p->Amount;
            }
            foreach ($agg as $a) $rows[] = ['for' => $a['for'], 'pkg' => $a['pkg'], 'count' => $a['count'], 'buyers' => count($a['buyers']), 'amount' => $a['amount'], 'disc' => $a['disc']];
            usort($rows, fn($a, $b) => $b['amount'] <=> $a['amount']);
            $cols = ['for' => 'কার জন্য', 'pkg' => 'প্যাকেজ', 'count' => 'বিক্রি (বার)', 'buyers' => 'ক্রেতা (জন)', 'amount' => 'আয় ৳', 'disc' => 'রেফারেল ছাড় ৳'];
            $now = now();
            $activeT = (int)$db->scalar("SELECT COUNT(DISTINCT `UserId`) FROM `UserSubscriptions` WHERE `Audience` = 'Teacher' AND (`EndsAt` IS NULL OR `EndsAt` > :n)", ['n' => $now]);
            $activeS = (int)$db->scalar("SELECT COUNT(DISTINCT `UserId`) FROM `UserSubscriptions` WHERE `Audience` = 'Student' AND (`EndsAt` IS NULL OR `EndsAt` > :n)", ['n' => $now]);
            $refs = 0; $refBuy = 0; $bonus = 0;
            foreach ($db->all("SELECT `Status`, `Bonus`, `CreatedAt` FROM `Referrals` WHERE `Status` <> 'Revoked'") as $r) {
                if (!report_in_range($r->CreatedAt, $from, $to)) continue;
                $refs++; if ($r->Status === 'Credited') { $refBuy++; $bonus += (int)$r->Bonus; }
            }
            ksort($months);
            $totals = $cards + ['চালু টিচার প্যাকেজ' => $activeT, 'চালু শিক্ষার্থী প্যাকেজ' => $activeS, 'Accepted পেমেন্ট' => $n, 'মোট আয়' => '৳' . number_format($rev),
                'অপেক্ষমাণ পেমেন্ট' => $pend, 'রেফারেলে এসেছে' => $refs, 'রেফারেলে কিনেছে' => $refBuy, 'রেফারেল বোনাস' => '৳' . number_format($bonus)];
            return ['type' => $type, 'title' => REPORT_TYPES['summary'][0], 'cols' => $cols, 'rows' => $rows, 'totals' => $totals, 'months' => array_slice($months, -12, null, true)];
    }
    return ['type' => $type, 'title' => $title, 'cols' => $cols, 'rows' => $rows, 'totals' => $totals];
}

/** Excel (CSV, UTF-8 — বাংলা ঠিক দেখায়) */
function report_csv(array $R, string $fname): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fname . '.csv"');
    header('Cache-Control: no-store');
    $o = fopen('php://output', 'w');
    fwrite($o, "\xEF\xBB\xBF");
    fputcsv($o, [$R['title'] . ' — ' . date('d M Y, h:i A')]);
    foreach ($R['totals'] as $k => $v) fputcsv($o, [$k, (string)$v]);
    fputcsv($o, []);
    fputcsv($o, array_values($R['cols']));
    foreach ($R['rows'] as $r) {
        $line = [];
        foreach (array_keys($R['cols']) as $k) {
            $v = (string)($r[$k] ?? '');
            if (in_array($k, ['user', 'user2'], true)) $v = ltrim($v, '@');
            if ($v !== '' && strpbrk($v[0], '=+-@') !== false && !is_numeric($v)) $v = "'" . $v;   // Excel এ ফর্মুলা হিসেবে না চলে
            if (preg_match('/^0\d{6,}$/', $v)) $v = "\t" . $v;   // মোবাইল নম্বরের শুরুর 0 যেন না হারায়
            $line[] = $v;
        }
        fputcsv($o, $line);
    }
    fclose($o);
    exit;
}
