<?php
/** Admin → Referrals: বোনাসের পরিমাণ, উইথড্র আবেদন (টাকা পাঠিয়ে Paid / বাতিল), রেফারেল তালিকা */
$ViewData['Title'] = 'Referrals';
$set = $ViewBag->Settings; $st = $ViewBag->Stats; $tab = (string)$ViewBag->Tab;
$sc = ['Pending' => 'p', 'Paid' => 'ok', 'Rejected' => 'no'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .rfa h2 { color: #08224A; margin: 0; font-size: 24px; }
    .rfa .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; }
    .rfa-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .rfa-stat { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); }
    .rfa-stat small { color: #64748B; font-weight: 700; font-size: 12px; } .rfa-stat b { display: block; font-size: 22px; color: #08224A; }
    .rfa-stat.warn b { color: #B45309; }
    .rfa-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .rfa-card h3 { margin: 0 0 12px; font-size: 17px; font-weight: 800; color: #08224A; display: flex; align-items: center; gap: 8px; }
    .rfa-set { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; }
    .rfa-set label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; }
    .rfa-set input[type=number] { height: 42px; width: 160px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 15px; font-weight: 700; font-family: inherit; }
    .rfa-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 6px; }
    .rfa-btn.g { background: #16A34A; } .rfa-btn.r { background: #FEE2E2; color: #B91C1C; } .rfa-btn.l { background: #F1F5F9; color: #334155; }
    .rfa-tabs { display: flex; gap: 6px; margin-bottom: 12px; flex-wrap: wrap; }
    .rfa-tabs a { padding: 8px 14px; border-radius: 999px; background: #F1F5F9; color: #334155; font-weight: 700; font-size: 13px; text-decoration: none; }
    .rfa-tabs a.on { background: #08224A; color: #fff; }
    .rfa-w { border: 1px solid #EEF2F7; border-radius: 12px; padding: 14px; margin-bottom: 10px; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; }
    .rfa-w .who b { color: #0F172A; font-size: 15px; } .rfa-w .who small { color: #64748B; font-size: 12.5px; display: block; margin-top: 2px; }
    .rfa-w .amt { font-size: 20px; font-weight: 800; color: #16A34A; }
    .rfa-w .acc { margin-top: 8px; background: #F8FAFC; border-radius: 10px; padding: 8px 12px; font-size: 13.5px; color: #334155; }
    .rfa-w .acc b { font-family: monospace; font-size: 15px; color: #0F172A; letter-spacing: .5px; }
    .rfa-w .acts { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .rfa-pill { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; }
    .rfa-pill.p { background: #FEF3C7; color: #92400E; } .rfa-pill.ok { background: #DCFCE7; color: #166534; } .rfa-pill.no { background: #FEE2E2; color: #991B1B; }
    .rfa-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rfa-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 8px 6px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .rfa-tbl td { padding: 9px 6px; border-bottom: 1px solid #F1F5F9; color: #334155; }
    .rfa-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    .rfa-tw { overflow-x: auto; }
    .rfg-search { display: flex; align-items: center; gap: 10px; border: 1.5px solid #DCECF5; border-radius: 12px; padding: 0 14px; margin-bottom: 12px; background: #fff; }
    .rfg-search i { color: #94A3B8; } .rfg-search input { border: none; outline: none; height: 44px; flex: 1; min-width: 0; font-size: 14.5px; font-family: inherit; }
    .rfg { border: 1px solid #E5ECF3; border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
    .rfg[open] { border-color: #C4B5FD; }
    .rfg summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 10px 14px; flex-wrap: wrap; padding: 12px 14px; background: #FAFBFF; }
    .rfg summary::-webkit-details-marker { display: none; }
    .rfg-who { font-size: 15px; font-weight: 800; color: #0F172A; } .rfg-who i { color: #7C3AED; margin-right: 4px; }
    .rfg-who a { color: inherit; text-decoration: none; } .rfg-who a:hover { text-decoration: underline; }
    .rfg-who small { font-weight: 600; color: #64748B; font-size: 12.5px; }
    .rfg-code { font-family: ui-monospace, monospace; font-weight: 800; font-size: 13px; background: #FEF3C7; color: #92400E; border-radius: 8px; padding: 3px 9px; letter-spacing: .5px; }
    .rfg-n { margin-left: auto; font-size: 13.5px; color: #334155; }
    .rfg-car { color: #94A3B8; transition: transform .2s; } .rfg[open] .rfg-car { transform: rotate(180deg); }
    .rfg .rfa-tbl { margin: 0; } .rfg .rfa-tw { padding: 4px 12px 10px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .rfa-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .rfa-w { grid-template-columns: 1fr; } .rfa-w .acts { justify-content: flex-start; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content rfa">
    <h2><i class="fas fa-gift" style="color:#F59E0B;"></i> Referrals</h2>
    <p class="sub">ইউজাররা নিজের লিংক বা রেফারেল কোড শেয়ার করে — কেউ সেই লিংক দিয়ে সাইনআপ করে, অথবা প্যাকেজ কেনার সময় কোড দিয়ে প্যাকেজ কিনলে (পেমেন্ট Approve হলে) রেফারার বোনাস পায় — একজন ইউজারের জন্য একবারই। উইথড্র আবেদন দেখতে ও টাকা পাঠাতে বাম মেনুর <a href="/Admin/Withdrawals"><b>Withdraw</b></a> পেজে যান।</p>

    <div class="rfa-stats">
        <div class="rfa-stat"><small>মোট রেফারেল · প্যাকেজ কিনেছে</small><b><?= number_format($st['referrals']) ?> · <?= number_format($st['bought'] ?? 0) ?></b></div>
        <div class="rfa-stat"><small>মোট বোনাস দেওয়া হয়েছে</small><b>৳<?= number_format($st['earned']) ?></b></div>
        <div class="rfa-stat"><small>পাঠানো (Paid) · প্যাকেজে ছাড়</small><b>৳<?= number_format($st['paid']) ?> · ৳<?= number_format($st['discount'] ?? 0) ?></b></div>
        <div class="rfa-stat warn"><small>অপেক্ষমাণ উইথড্র</small><b><?= (int)$st['pendingCount'] ?> টি · ৳<?= number_format($st['pendingAmount']) ?></b></div>
    </div>

    <div class="rfa-card">
        <h3><i class="fas fa-sliders" style="color:#00B5E0;"></i> বোনাস সেটিং</h3>
        <form class="rfa-set" id="rfaSet">
            <div><label for="rfaBonus">প্যাকেজ কিনলে বোনাস (৳)</label><input type="number" id="rfaBonus" name="bonus" min="0" max="10000" value="<?= (int)$set['bonus'] ?>" /></div>
            <div><label for="rfaMin">সর্বনিম্ন উইথড্র (৳)</label><input type="number" id="rfaMin" name="min" min="1" value="<?= (int)$set['min'] ?>" /></div>
            <div class="form-check form-switch" style="padding-bottom:8px;"><input class="form-check-input" type="checkbox" id="rfaOn" name="enabled" <?= $set['enabled'] ? 'checked' : '' ?> /><label class="form-check-label fw-semibold" for="rfaOn">রেফারেল চালু</label></div>
            <button type="submit" class="rfa-btn"><i class="fas fa-save"></i> Save</button>
        </form>
        <p style="font-size:12.5px; color:#64748B; margin:10px 0 0;">বোনাস বদলালে এরপর যারা প্যাকেজ কিনবে তাদের রেফারার নতুন পরিমাণ পাবে — আগে দেওয়া বোনাস যেমন আছে তেমন থাকবে।</p>
    </div>

    <?php if ((int)$st['pendingCount'] > 0): ?>
    <a class="rfa-card" href="/Admin/Withdrawals" style="display:flex;align-items:center;gap:12px;text-decoration:none;border:1.5px solid #FDE68A;background:#FFFBEB;">
        <i class="fas fa-money-bill-transfer" style="font-size:22px;color:#B45309;"></i>
        <span style="color:#92400E;font-weight:700;"><?= (int)$st['pendingCount'] ?>টা উইথড্র আবেদন অপেক্ষায় আছে (৳<?= number_format($st['pendingAmount']) ?>) — টাকা পাঠাতে <u>Withdraw পেজে যান</u></span>
    </a>
    <?php endif; ?>

    <?php $nb = $ViewBag->NoBonus ?? []; ?>
    <div class="rfa-card" id="noBonus">
        <h3><i class="fas fa-user-slash" style="color:#DC2626;"></i> বোনাস ছাড়া রেফারার <span class="rfa-pill" style="background:#FEE2E2;color:#991B1B;"><?= count($nb) ?> জন</span></h3>
        <p style="font-size:13px;color:#64748B;margin:0 0 12px;">এই তালিকার ইউজারদের লিংক বা কোড দিয়ে কেউ সাইনআপ করে প্যাকেজ কিনলে তারা <b>৳ বোনাস পাবে না</b> — কিন্তু কত জন এনেছে আর কত জন কিনেছে সব নিচের তালিকায় আর "বিস্তারিত" পেজে গোনা হবে।</p>
        <?php
        $nbR = $ViewBag->NoBonusRange ?? []; $nbF = (string)($ViewBag->NbFrom ?? date('Y-m-d')); $nbTo = (string)($ViewBag->NbTo ?? date('Y-m-d'));
        $fdN = fn($d) => date('d M Y', strtotime($d));
        $nbLabel = $nbF === $nbTo ? ($nbF === date('Y-m-d') ? 'আজ' : $fdN($nbF)) : $fdN($nbF) . ' — ' . $fdN($nbTo);
        $nbSumN = array_sum(array_column($nbR, 'n')); $nbSumV = array_sum(array_column($nbR, 'v'));
        ?>
        <?php if ($nb): ?>
        <form class="nb-xl" method="get" action="/Admin/Referrals#noBonus" id="nbForm">
            <span class="nb-xl-h"><i class="fas fa-calendar-days"></i> কোন তারিখে কতজন রেফার করেছে</span>
            <label>শুরু <input type="date" name="from" value="<?= e($nbF) ?>" max="<?= date('Y-m-d') ?>" required /></label>
            <label>শেষ <input type="date" name="to" value="<?= e($nbTo) ?>" max="<?= date('Y-m-d') ?>" required /></label>
            <button type="submit" class="nb-go"><i class="fas fa-magnifying-glass"></i> খুঁজুন</button>
            <button type="submit" formaction="/Admin/NoBonusReferralsExcel"><i class="fas fa-download"></i> Excel ডাউনলোড</button>
            <small>একদিনের হিসাব চাইলে দুই ঘরে একই তারিখ দিন। <b>খুঁজুন</b> চাপলে নিচের তালিকায় ওই তারিখের হিসাব দেখাবে।
                <span class="nb-tot"><?= e($nbLabel) ?> মোট: <b><?= $nbSumN ?> জন</b> · ইমেইল ভেরিফাই করেছে: <b><?= $nbSumV ?> জন</b></span>
                <?php if ($nbF !== date('Y-m-d') || $nbTo !== date('Y-m-d')): ?><a href="/Admin/Referrals#noBonus" class="nb-reset">আজকের হিসাবে ফিরুন ✕</a><?php endif; ?></small>
        </form>
        <style>
            .nb-xl { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 12px 14px; margin: 0 0 14px; }
            .nb-xl-h { font-weight: 800; color: #166534; font-size: 14px; } .nb-xl-h i { color: #16A34A; }
            .nb-xl label { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #334155; margin: 0; }
            .nb-xl input { height: 36px; border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 0 8px; font-family: inherit; font-size: 13px; }
            .nb-xl button { height: 36px; border: none; border-radius: 8px; background: #16A34A; color: #fff; font-weight: 700; padding: 0 16px; cursor: pointer; font-size: 13.5px; }
            .nb-xl button:hover { background: #15803D; }
            .nb-xl button.nb-go { background: #0284C7; } .nb-xl button.nb-go:hover { background: #0369A1; }
            .nb-tot { display: inline-block; margin-left: 6px; background: #fff; border: 1px solid #BBF7D0; border-radius: 999px; padding: 2px 10px; color: #166534; }
            .nb-reset { margin-left: 8px; color: #B91C1C; font-weight: 700; text-decoration: none; }
            .nb-v { display: block; font-size: 11px; color: #64748B; margin-top: 2px; }
            .nb-xl small { flex-basis: 100%; color: #64748B; font-size: 12.5px; }
            .nb-today { display: inline-block; min-width: 28px; text-align: center; font-weight: 800; border-radius: 999px; padding: 2px 10px; font-size: 12.5px; background: #F1F5F9; color: #64748B; }
            .nb-today.on { background: #DCFCE7; color: #166534; }
        </style>
        <?php endif; ?>
        <div class="rfg-search" style="position:relative;"><i class="fas fa-user-plus"></i><input type="search" id="nbQ" placeholder="ইউজার যোগ করুন — নাম, ইউজারনেম, ইমেইল, মোবাইল বা কোড লিখুন" autocomplete="off" /></div>
        <div id="nbRes" style="margin:-4px 0 12px;"></div>
        <?php if (!$nb): ?><div class="rfa-empty" style="padding:10px 0;">এখনো কেউ নেই।</div><?php else: ?>
        <div class="rfa-tw"><table class="rfa-tbl">
            <tr><th>ইউজার</th><th>কোড</th><th style="text-align:center;"><?= e($nbLabel) ?> রেফার</th><th></th></tr>
            <?php foreach ($nb as $u): ?>
                <tr><td><b><?= e($u->FullName ?: $u->UserName) ?></b> <span class="rfg-code" style="font-size:12px;">@<?= e((string)$u->UserName) ?></span> <small style="color:#64748B;"><?= e((string)$u->Email) ?></small></td>
                    <td><span class="rfg-code"><?= e((string)($u->Code ?: '—')) ?></span></td>
                    <td style="text-align:center;"><?php $tn = (int)($nbR[(int)$u->Id]['n'] ?? 0); $tv = (int)($nbR[(int)$u->Id]['v'] ?? 0); ?><span class="nb-today<?= $tn ? ' on' : '' ?>"><?= $tn ?> জন</span><?php if ($tn): ?><span class="nb-v">ভেরিফাই: <?= $tv ?> জন</span><?php endif; ?></td>
                    <td style="white-space:nowrap;text-align:right;"><a class="rfa-btn l" style="height:32px;font-size:12px;text-decoration:none;" href="/Profile/Show/<?= (int)$u->Id ?>" target="_blank" rel="noopener"><i class="fas fa-user"></i> প্রোফাইল</a>
                        <a class="rfa-btn l" style="height:32px;font-size:12px;text-decoration:none;" href="/Admin/ReferralUser/<?= (int)$u->Id ?>">বিস্তারিত</a>
                        <button type="button" class="rfa-btn g" style="height:32px;font-size:12px;" data-nb="<?= (int)$u->Id ?>" data-on="0">বোনাস চালু করুন</button></td></tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>

    <?php
    // রেফারেলে আসা ইউজার টিচার না শিক্ষার্থী — রেফারার অনুযায়ী গোনা (বাতিল করা বাদ)
    $allRefs = $ViewBag->Referrals ?: [];
    $KM = db()->RefKindMap(array_map(fn($r) => (int)$r->ReferredUserId, $allRefs));
    $kindBy = [];
    foreach ($allRefs as $r) { if ($r->Status === 'Revoked') continue; $kk = $KM[(int)$r->ReferredUserId] ?? 'n'; $kindBy[(int)$r->ReferrerId][$kk] = ($kindBy[(int)$r->ReferrerId][$kk] ?? 0) + 1; }
    $KS = ['t' => ['টিচার', 'fa-chalkboard-user', 'kt'], 'ta' => ['টিচার আবেদন', 'fa-hourglass-half', 'ka'], 's' => ['শিক্ষার্থী', 'fa-user-graduate', 'ks'], 'n' => ['ঠিক করেনি', 'fa-circle-question', 'kn']];
    $kindPills = function (array $c, bool $zero = false) use ($KS) { $h = ''; foreach ($KS as $k => [$l, $i, $cl]) { $n = (int)($c[$k] ?? 0); if ($n || $zero) $h .= '<span class="rk ' . $cl . '"><i class="fas ' . $i . '"></i> ' . $l . ' <b>' . $n . '</b></span>'; } return $h ?: '<span class="rk kn">—</span>'; };
    $kindPill = function (string $k) use ($KS) { [$l, $i, $cl] = $KS[$k] ?? $KS['n']; return '<span class="rk ' . $cl . '"><i class="fas ' . $i . '"></i> ' . $l . '</span>'; };
    ?>
    <style>
        .rk { display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 700; border-radius: 999px; padding: 2px 9px; margin: 2px 4px 2px 0; white-space: nowrap; }
        .rk b { font-weight: 800; } .rk i { font-size: 10.5px; }
        .rk.kt { background: #EDE9FE; color: #5B21B6; } .rk.ka { background: #FEF3C7; color: #92400E; } .rk.ks { background: #DCFCE7; color: #166534; } .rk.kn { background: #F1F5F9; color: #64748B; }
        .rk-wrap { display: flex; flex-wrap: wrap; margin-top: 4px; }
    </style>
    <?php $ov = $ViewBag->Overview ?? []; ?>
    <div class="rfa-card">
        <h3><i class="fas fa-ranking-star" style="color:#0EA5E9;"></i> সব রেফারার — কে কতটা শেয়ার করছে <span class="rfa-pill" style="background:#E0F2FE;color:#075985;"><?= count($ov) ?> জন</span></h3>
        <?php if (!$ov): ?><div class="rfa-empty">এখনো কেউ লিংক শেয়ার করেনি।</div><?php else: ?>
        <div class="rfg-search"><i class="fas fa-magnifying-glass"></i><input type="search" id="rovQ" placeholder="নাম, ইউজারনেম বা কোড দিয়ে খুঁজুন" autocomplete="off" /></div>
        <div class="rfa-tw"><table class="rfa-tbl" id="rovT">
            <tr><th>ইউজার</th><th>রেফারেল কোড</th><th title="শেয়ার বাটনে কতবার চেপেছে">শেয়ার</th><th title="লিংক খুলে কত জন সাইটে এসেছে">লিংকে ক্লিক</th><th title="কতজন অ্যাকাউন্ট খুলেছে — নিচে টিচার / শিক্ষার্থী আলাদা">সাইনআপ</th><th>প্যাকেজ কিনেছে</th><th>আয়</th><th>ব্যালেন্স</th><th></th></tr>
            <?php foreach ($ov as $o): ?>
                <tr data-s="<?= e(mb_strtolower($o->Name . ' ' . $o->UserName . ' ' . $o->Code)) ?>">
                    <td><b><?= e($o->Name) ?></b><?= !empty($o->NoBonus) ? ' <span class="rfa-pill no" title="এই ইউজার রেফারেলে বোনাস পায় না">বোনাস নেই</span>' : '' ?><br><small style="color:#64748B;"><?= e(role_label($o->Role, (int)$o->UserId)) ?><?= $o->UserName !== '' ? ' · @' . e($o->UserName) : '' ?></small></td>
                    <td><span class="rfg-code"><?= e($o->Code ?: '—') ?></span></td>
                    <td><?= (int)$o->Shares ?></td><td><?= (int)$o->Clicks ?></td><td><b><?= (int)$o->Signups ?></b><div class="rk-wrap"><?= $kindPills($kindBy[(int)$o->UserId] ?? []) ?></div></td>
                    <td><b style="color:#16A34A;"><?= (int)$o->Buyers ?></b></td><td>৳<?= number_format($o->Earned) ?></td><td>৳<?= number_format($o->Balance) ?></td>
                    <td style="white-space:nowrap;"><a class="rfa-btn l" style="height:32px;font-size:12px;text-decoration:none;" href="/Profile/Show/<?= (int)$o->UserId ?>" target="_blank" rel="noopener"><i class="fas fa-user"></i> প্রোফাইল</a>
                        <a class="rfa-btn" style="height:32px;font-size:12.5px;text-decoration:none;" href="/Admin/ReferralUser/<?= (int)$o->UserId ?>"><i class="fas fa-magnifying-glass-chart"></i> বিস্তারিত</a></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>

    <?php
    $refs = $ViewBag->Referrals ?: [];
    $groups = [];
    foreach ($refs as $r) {
        $k = (int)$r->ReferrerId;
        $groups[$k] ??= ['name' => (string)($r->ReferrerName ?: $r->ReferrerUser ?: ('#' . $k)), 'user' => (string)$r->ReferrerUser, 'code' => (string)$r->RefCode, 'role' => (string)$r->ReferrerRole, 'list' => [], 'ok' => 0];
        $groups[$k]['list'][] = $r;
        if ($r->Status === 'Credited') $groups[$k]['ok']++;
    }
    uasort($groups, fn($a, $b) => count($b['list']) <=> count($a['list']));
    $roleBn = fn($r, $uid = 0) => role_label($r, $uid);
    $srcPill = function ($r) { $k = (string)($r->Src ?? ''); if ($k === '' || !isset(Db::REF_SOURCES[$k])) return ''; [$l, $i, $c] = Db::REF_SOURCES[$k];
        return '<span class="rfa-pill" style="background:#F1F5F9;color:' . $c . ';margin-right:4px;"><i class="' . $i . '"></i> ' . e($l) . '</span>'; };
    $stBn = fn($r) => (($r->Via ?? '') === 'code' ? '<span class="rfa-pill" style="background:#FEF3C7;color:#92400E;margin-right:4px;"><i class="fas fa-ticket"></i> কেনার সময় কোড দিয়ে</span>' : $srcPill($r)) . ($r->Status === 'Credited' ? '<span class="rfa-pill ok">প্যাকেজ কিনেছে · ৳' . (int)$r->Bonus . ' বোনাস</span>' : ($r->Status === 'Waiting' ? '<span class="rfa-pill" style="background:#F1F5F9;color:#64748B;">এখনো প্যাকেজ কেনেনি</span>' : '<span class="rfa-pill no">বাতিল</span>'));
    ?>
    <div class="rfa-card" id="refLink">
        <h3><i class="fas fa-link" style="color:#0284C7;"></i> রেফারেল লিংক চেক — কোন লিংক থেকে কতজন এসেছে</h3>
        <p style="font-size:13px;color:#64748B;margin:0 0 10px;">এক বা একাধিক রেফারেল লিংক পেস্ট করুন (প্রতি লাইনে একটা), অথবা শুধু কোড (যেমন MMDQ4R97)। প্রতিটা লিংক কার, কতবার খোলা হয়েছে, কতজন অ্যাকাউন্ট খুলেছে আর কতজন প্যাকেজ কিনেছে — দেখাবে।</p>
        <textarea id="rlIn" rows="4" class="rl-in" placeholder="https://mashtermoshai.com/?ref=MMDQ4R97&src=copy&#10;https://mashtermoshai.com/?ref=MMB6GPYB&src=copy"></textarea>
        <div style="display:flex;gap:8px;margin:8px 0 4px;flex-wrap:wrap;"><button type="button" class="rfa-btn" id="rlGo" style="background:#0284C7;color:#fff;"><i class="fas fa-magnifying-glass-chart"></i> চেক করুন</button><button type="button" class="rfa-btn l" id="rlClr">মুছুন</button></div>
        <div id="rlOut"></div>
        <style>
            .rl-in { width: 100%; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 10px 12px; font-size: 13.5px; font-family: inherit; resize: vertical; outline: none; }
            .rl-in:focus { border-color: #0284C7; }
            .rl-sum { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0; }
            .rl-sum span { background: #F1F5F9; border-radius: 999px; padding: 5px 12px; font-size: 13px; font-weight: 700; color: #334155; } .rl-sum b { color: #0F172A; }
            .rl-num { font-weight: 800; font-size: 15px; } .rl-num.g { color: #16A34A; } .rl-num.b { color: #0284C7; }
            .rl-sub { display: block; font-size: 11.5px; color: #64748B; font-weight: 600; }
        </style>
    </div>

    <div class="rfa-card">
        <h3><i class="fas fa-sitemap" style="color:#7C3AED;"></i> কে কার রেফারেল কোডে এসেছে <span class="rfa-pill" style="background:#EDE9FE;color:#5B21B6;"><?= count($groups) ?> জন রেফারার · <?= count($refs) ?> জন নতুন ইউজার</span></h3>
        <?php if (!$refs): ?><div class="rfa-empty">এখনো কোনো রেফারেল নেই।</div><?php else: ?>
        <div class="rfg-search"><i class="fas fa-magnifying-glass"></i><input type="search" id="rfgQ" placeholder="নাম, ইউজারনেম, ইমেইল, রেফারেল কোড (যেমন MMAB12CD) বা পুরো রেফারেল লিংক দিয়ে খুঁজুন" autocomplete="off" /></div>
        <div id="rfgList">
        <?php foreach ($groups as $rid => $g): ?>
            <details class="rfg" data-s="<?= e(mb_strtolower($g['name'] . ' ' . $g['user'] . ' ' . $g['code'] . ' ' . implode(' ', array_map(fn($x) => ($x->ReferredName ?? '') . ' ' . ($x->ReferredUser ?? '') . ' ' . ($x->ReferredEmail ?? ''), $g['list'])))) ?>">
                <summary>
                    <span class="rfg-who"><i class="fas fa-user-tie"></i> <a href="/Admin/UserDetails/<?= (int)$rid ?>"><?= e($g['name']) ?></a> <small><?= e($roleBn($g["role"], $rid)) ?><?= $g['user'] !== '' ? ' · @' . e($g['user']) : '' ?></small></span>
                    <?php if ($g['code'] !== ''): ?><span class="rfg-code" title="এই ব্যক্তির রেফারেল কোড"><i class="fas fa-ticket"></i> <?= e($g['code']) ?></span><?php endif; ?>
                    <a href="/Profile/Show/<?= (int)$rid ?>" target="_blank" rel="noopener" style="font-size:12.5px;font-weight:700;color:#0369A1;" onclick="event.stopPropagation();"><i class="fas fa-user"></i> প্রোফাইল</a>
                    <a href="/Admin/ReferralUser/<?= (int)$rid ?>" style="font-size:12.5px;font-weight:700;color:#0369A1;" onclick="event.stopPropagation();"><i class="fas fa-magnifying-glass-chart"></i> বিস্তারিত</a>
                    <span class="rk-wrap" style="margin:0;"><?= $kindPills($kindBy[(int)$rid] ?? []) ?></span>
                    <span class="rfg-n"><b><?= count($g['list']) ?></b> জন এসেছে<?= $g['ok'] ? ' · <span style="color:#16A34A;">' . $g['ok'] . ' জন কিনেছে</span>' : '' ?></span>
                    <i class="fas fa-chevron-down rfg-car"></i>
                </summary>
                <div class="rfa-tw"><table class="rfa-tbl">
                    <tr><th>নতুন ইউজার</th><th>টিচার না শিক্ষার্থী</th><th>কবে এসেছে</th><th>অবস্থা</th><th>IP</th><th></th></tr>
                    <?php foreach ($g['list'] as $r): ?>
                        <tr>
                            <td><?php if ($r->ReferredName || $r->ReferredUser): ?><a href="/Profile/Show/<?= (int)$r->ReferredUserId ?>" target="_blank"><b><?= e($r->ReferredName ?: $r->ReferredUser) ?></b></a> <small style="color:#64748B;">(<?= e($roleBn((string)$r->ReferredRole, (int)$r->ReferredUserId)) ?>)</small><?php else: ?><span style="color:#94A3B8;">Deleted</span><?php endif; ?><br><small style="color:#94A3B8;"><?= e((string)$r->ReferredEmail) ?></small></td>
                            <td><?= $kindPill($KM[(int)$r->ReferredUserId] ?? 'n') ?></td>
                            <td><?= e(fmt_date($r->CreatedAt, 'd M Y, h:i A')) ?></td>
                            <td><?= $stBn($r) ?></td>
                            <td><small><?= e((string)$r->Ip) ?></small></td>
                            <td><button type="button" class="rfa-btn <?= $r->Status !== 'Revoked' ? 'r' : 'l' ?>" style="height:32px; font-size:12px;" data-ref="<?= (int)$r->Id ?>" data-st="<?= $r->Status !== 'Revoked' ? 'Revoked' : 'Credited' ?>"><?= $r->Status !== 'Revoked' ? 'বাতিল' : 'আবার চালু' ?></button></td>
                        </tr>
                    <?php endforeach; ?>
                </table></div>
            </details>
        <?php endforeach; ?>
        </div>
        <div class="rfa-empty" id="rfgNone" hidden>কিছু পাওয়া যায়নি।</div>
        <p style="font-size:12.5px; color:#64748B; margin:10px 0 0;">কারো নামে ক্লিক করলে তার আনা সবার তালিকা খুলবে। একই IP থেকে অনেক সাইনআপ বা ভুয়া অ্যাকাউন্ট দেখলে "বাতিল" চাপুন — সেই বোনাস রেফারারের ব্যালেন্স থেকে কেটে যাবে।</p>
        <?php endif; ?>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    function tok() { return document.querySelector('input[name="__RequestVerificationToken"]').value; }
    async function post(url, data) { var fd = new FormData(); for (var k in data) fd.append(k, data[k]); fd.append('__RequestVerificationToken', tok()); return (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); }
    document.getElementById('rfaSet').addEventListener('submit', async function (e) {
        e.preventDefault(); var f = e.target;
        var r = await post('/Admin/SaveReferralSettings', { bonus: f.bonus.value, min: f.min.value, enabled: f.enabled.checked ? 'true' : 'false' });
        alert((r.success ? '✅ ' : '❌ ') + (r.message || ''));
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-wd]');
        if (b) {
            var st = b.getAttribute('data-st'), data = { id: b.getAttribute('data-wd'), status: st };
            if (st === 'Paid') { var t = prompt('টাকা পাঠিয়েছেন? Transaction ID দিন (ঐচ্ছিক):', ''); if (t === null) return; data.txn = t; }
            else { var n = prompt('বাতিলের কারণ লিখুন (ইউজার দেখতে পাবে):', ''); if (!n) return; data.note = n; }
            b.disabled = true;
            var r = await post('/Admin/ProcessWithdrawal', data);
            if (!r.success) { alert('❌ ' + (r.message || '')); b.disabled = false; return; }
            location.reload(); return;
        }
        var c = e.target.closest('[data-ref]');
        if (c) {
            if (c.getAttribute('data-st') === 'Revoked' && !confirm('এই রেফারেলের বোনাস বাতিল করবেন?')) return;
            var r2 = await post('/Admin/SetReferralStatus', { id: c.getAttribute('data-ref'), status: c.getAttribute('data-st') });
            if (!r2.success) { alert('❌ ' + (r2.message || '')); return; }
            location.reload();
        }
    });    // বোনাস ছাড়া রেফারার: খোঁজা আর যোগ / বাদ
    var nbQ = document.getElementById('nbQ'), nbRes = document.getElementById('nbRes'), nbT = null;
    function escH(t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    if (nbQ) nbQ.addEventListener('input', function () {
        clearTimeout(nbT); var v = nbQ.value.trim(); if (v.length < 2) { nbRes.innerHTML = ''; return; }
        nbT = setTimeout(async function () {
            var r = await post('/Admin/RefUserSearch', { q: v }); if (nbQ.value.trim() !== v) return;
            nbRes.innerHTML = (r.users || []).length ? r.users.map(function (u) {
                var av = '<span style="width:42px;height:42px;border-radius:50%;background:#1E3A8A;color:#fff;font-weight:800;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;position:relative;">' + escH(u.ini)
                    + (u.photo ? '<img src="' + escH(u.photo) + '" alt="' + escH((u.name || 'ইউজার') + ' এর প্রোফাইল ছবি') + '" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" onerror="this.remove()" />' : '') + '</span>';
                return '<div style="display:flex;align-items:center;gap:12px;border:1px solid #E5ECF3;border-radius:10px;padding:8px 12px;margin-bottom:6px;flex-wrap:wrap;">' + av
                    + '<div style="flex:1;min-width:200px;"><b>' + escH(u.name) + '</b> <span class="rfg-code" style="font-size:12px;">@' + escH(u.user) + '</span>'
                    + '<br><small style="color:#64748B;">' + escH(u.role) + ' · ' + escH(u.email) + (u.code ? ' · কোড ' + escH(u.code) : '') + (u.joined ? ' · যোগ দিয়েছে ' + escH(u.joined) : '') + '</small></div>'
                    + '<a class="rfa-btn l" style="height:32px;font-size:12px;text-decoration:none;" href="/Profile/Show/' + u.id + '" target="_blank" rel="noopener"><i class="fas fa-user"></i> প্রোফাইল দেখুন</a>'
                    + (u.off ? '<span class="rfa-pill no">আগেই তালিকায়</span>' : '<button type="button" class="rfa-btn r" style="height:32px;font-size:12px;" data-nb="' + u.id + '" data-on="1">বোনাস বন্ধ করুন</button>') + '</div>';
            }).join('') : '<div class="rfa-empty" style="padding:8px 0;">কাউকে পাওয়া যায়নি।</div>';
        }, 300);
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-nb]'); if (!b) return;
        var on = b.getAttribute('data-on') === '1';
        if (!confirm(on ? 'এই ইউজারের রেফারেলে ৳ বোনাস বন্ধ করবেন? (কত জন আনছে / কিনছে গোনা চলবে)' : 'এই ইউজারের রেফারেল বোনাস আবার চালু করবেন?')) return;
        b.disabled = true;
        var r = await post('/Admin/SetRefNoBonus', { id: b.getAttribute('data-nb'), on: on ? 'true' : 'false' });
        if (!r.success) { alert('❌ ' + (r.message || '')); b.disabled = false; return; }
        location.reload();
    });
    var oq = document.getElementById('rovQ');
    if (oq) oq.addEventListener('input', function () { var v = oq.value.trim().toLowerCase(); document.querySelectorAll('#rovT tr[data-s]').forEach(function (t) { t.hidden = v && t.getAttribute('data-s').indexOf(v) === -1; }); });
    if (/[?&]from=/.test(location.search)) { var nbc = document.getElementById('noBonus'); if (nbc) setTimeout(function () { nbc.scrollIntoView(); }, 50); }
    // ===== রেফারেল লিংক চেক =====
    var rlIn = document.getElementById('rlIn'), rlOut = document.getElementById('rlOut');
    function esc2(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    document.getElementById('rlClr').addEventListener('click', function () { rlIn.value = ''; rlOut.innerHTML = ''; rlIn.focus(); });
    document.getElementById('rlGo').addEventListener('click', async function () {
        var b = this; if (!rlIn.value.trim()) { rlIn.focus(); return; }
        b.disabled = true; rlOut.innerHTML = '<div class="rfa-empty" style="padding:10px 0;">খোঁজা হচ্ছে…</div>';
        var r; try { r = await post('/Admin/RefLinkCheck', { links: rlIn.value }); } catch (e) { r = { success: false, message: 'সার্ভারে সমস্যা' }; }
        b.disabled = false;
        if (!r.success) { rlOut.innerHTML = '<div class="rfa-empty" style="padding:10px 0;color:#B91C1C;">' + esc2(r.message) + '</div>'; return; }
        var T = { clicks: 0, signups: 0, bought: 0, today: 0, t: 0, ta: 0, s: 0, n: 0 }, seen = {};
        var KS = { t: ['টিচার', 'fa-chalkboard-user', 'kt'], ta: ['টিচার আবেদন', 'fa-hourglass-half', 'ka'], s: ['শিক্ষার্থী', 'fa-user-graduate', 'ks'], n: ['ঠিক করেনি', 'fa-circle-question', 'kn'] };
        function kp(c) { return Object.keys(KS).map(function (k) { return '<span class="rk ' + KS[k][2] + '"><i class="fas ' + KS[k][1] + '"></i> ' + KS[k][0] + ' <b>' + (c[k] || 0) + '</b></span>'; }).join(''); }
        var rows = r.items.map(function (x) {
            var link = '<span class="rfg-code">' + esc2(x.code) + '</span>' + (x.src ? ' <small style="color:#64748B;">src=' + esc2(x.src) + '</small>' : '') + (x.times > 1 ? ' <small style="color:#B45309;">(তালিকায় ' + x.times + ' বার)</small>' : '');
            if (x.missing) return '<tr><td>' + link + '</td><td colspan="5" style="color:#B91C1C;">এই কোডের কোনো ইউজার পাওয়া যায়নি</td></tr>';
            if (!seen[x.code]) { seen[x.code] = 1; T.clicks += x.clicks; T.signups += x.signups; T.bought += x.bought; T.today += x.today; ['t', 'ta', 's', 'n'].forEach(function (k) { T[k] += (x.kinds || {})[k] || 0; }); }
            return '<tr><td>' + link + '</td>'
                + '<td><a href="/Admin/UserDetails/' + x.id + '"><b>' + esc2(x.name) + '</b></a> <small style="color:#64748B;">' + esc2(x.role) + (x.user ? ' · @' + esc2(x.user) : '') + '</small>' + (x.nobonus ? ' <span class="rfa-pill" style="background:#FEE2E2;color:#991B1B;">বোনাস নেই</span>' : '') + '</td>'
                + '<td><span class="rl-num">' + x.clicks + '</span> বার' + (x.src ? '<span class="rl-sub">এই (' + esc2(x.srcLabel || x.src) + ') লিংক: ' + x.clicksSrc + ' বার</span>' : '') + '</td>'
                + '<td><span class="rl-num b">' + x.signups + '</span> জন<span class="rl-sub">আজ: ' + x.today + ' জন' + (x.src ? ' · এই লিংকে: ' + x.signupsSrc + ' জন' : '') + '</span><div class="rk-wrap">' + kp(x.kinds || {}) + '</div></td>'
                + '<td><span class="rl-num g">' + x.bought + '</span> জন' + (x.src ? '<span class="rl-sub">এই লিংকে: ' + x.boughtSrc + ' জন</span>' : '') + '</td>'
                + '<td style="white-space:nowrap;"><a class="rfa-btn l" style="height:32px;font-size:12px;text-decoration:none;" href="/Profile/Show/' + x.id + '" target="_blank" rel="noopener"><i class="fas fa-user"></i> প্রোফাইল</a> <a class="rfa-btn l" style="height:32px;font-size:12px;text-decoration:none;" href="/Admin/ReferralUser/' + x.id + '">বিস্তারিত</a></td></tr>';
        });
        rlOut.innerHTML = '<div class="rl-sum"><span>রেফারার: <b>' + Object.keys(seen).length + ' জন</b></span><span>লিংক খোলা: <b>' + T.clicks + ' বার</b></span><span>অ্যাকাউন্ট খুলেছে: <b>' + T.signups + ' জন</b></span><span>আজ: <b>' + T.today + ' জন</b></span><span>প্যাকেজ কিনেছে: <b>' + T.bought + ' জন</b></span></div><div class="rk-wrap" style="margin:-4px 0 10px;"><span style="font-size:12.5px;font-weight:700;color:#334155;margin-right:6px;">যারা এসেছে:</span>' + kp(T) + '</div>'
            + '<div class="rfa-tw"><table class="rfa-tbl"><tr><th>লিংক / কোড</th><th>কার লিংক</th><th>লিংক খোলা হয়েছে</th><th>অ্যাকাউন্ট খুলেছে</th><th>প্যাকেজ কিনেছে</th><th></th></tr>' + rows.join('') + '</table></div>'
            + '<p style="font-size:12px;color:#64748B;margin:8px 0 0;">"লিংক খোলা" — একই ইন্টারনেট সংযোগ (IP) থেকে ঘণ্টায় একবারই গোনা হয়। "অ্যাকাউন্ট খুলেছে" আর "প্যাকেজ কিনেছে" — এই ইউজারের সব রেফারেল লিংক আর কোড মিলিয়ে; নিচের ছোট লেখায় শুধু পেস্ট করা ধরনের লিংক (যেমন copy) থেকে।</p>';
    });

    var q = document.getElementById('rfgQ');
    if (q) q.addEventListener('input', function () {
        var raw = q.value.trim(), mm = raw.match(/\bMM[A-Z0-9]{6}\b/i);   // পুরো লিংক পেস্ট করলেও কোডটা দিয়ে খোঁজে
        var v = (mm ? mm[0] : raw).toLowerCase(), any = false;
        document.querySelectorAll('#rfgList .rfg').forEach(function (d) {
            var hit = !v || d.getAttribute('data-s').indexOf(v) !== -1; d.hidden = !hit; if (hit) any = true;
            if (v && hit) d.open = true; else if (!v) d.open = false;
        });
        document.getElementById('rfgNone').hidden = any;
    });
})();
</script>
<?php section_end(); ?>
