<?php
/** Admin → Partner/{id}: একটা এজেন্সির লিংক, কোড, প্ল্যাটফর্ম অনুযায়ী শেয়ার লিংক, আর ক্লিক / সাইনআপ / কেনার পুরো হিসাব */
$p = $Model; $R = $ViewBag->Report; $from = $ViewBag->From; $to = $ViewBag->To;
$ViewData['Title'] = 'Partner: ' . $p->Name;
$pid = (int)$p->Id; $code = (string)$p->Code;
$mainLink = partner_link($code);
$reportUrl = site_public_url() . '/Partner/Report?t=' . $p->ReportToken;
$qs = array_filter(['from' => $from, 'to' => $to]);
$csvUrl = '/Admin/PartnerCsv/' . $pid . ($qs ? '?' . http_build_query($qs) : '');
$shareSrc = ['facebook', 'messenger', 'whatsapp', 'telegram', 'youtube', 'tiktok', 'instagram', 'imo', 'sms', 'email', 'website', 'other'];
$today = date('Y-m-d');
$quick = ['আজ' => [$today, $today], 'গত ৭ দিন' => [date('Y-m-d', strtotime('-6 days')), $today], 'গত ৩০ দিন' => [date('Y-m-d', strtotime('-29 days')), $today],
    'এই মাস' => [date('Y-m-01'), $today], 'গত মাস' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))]];
$roleLbl = fn($r) => $r === 'Teacher' ? ['Teacher', 't'] : ($r === 'Admin' ? ['Admin', 'a'] : ['Student', 's']);
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .pd-back { display: inline-flex; align-items: center; gap: 6px; color: #475569; font-weight: 700; font-size: 13.5px; text-decoration: none; margin-bottom: 10px; }
    .pd-head { display: flex; align-items: flex-start; gap: 14px; flex-wrap: wrap; margin-bottom: 16px; }
    .pd-av { width: 54px; height: 54px; border-radius: 14px; background: linear-gradient(135deg, #7C3AED, #0EA5E9); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .pd-head h2 { margin: 0; font-size: 23px; color: #08224A; overflow-wrap: anywhere; }
    .pd-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
    .pd-chip { font-size: 12px; font-weight: 800; padding: 3px 10px; border-radius: 999px; background: #F1F5F9; color: #475569; white-space: nowrap; }
    .pd-chip.code { background: #FEF9C3; color: #854D0E; } .pd-chip.on { background: #DCFCE7; color: #166534; } .pd-chip.no { background: #FEE2E2; color: #991B1B; }
    .pd-head .acts { margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap; }
    .pd-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; white-space: nowrap; }
    .pd-btn.sm { height: 32px; padding: 0 11px; font-size: 12.5px; border-radius: 8px; }
    .pd-btn.ghost { background: #F1F5F9; color: #334155; } .pd-btn.ok { background: #15803D; } .pd-btn.warn { background: #FEF3C7; color: #92400E; } .pd-btn.del { background: #FEE2E2; color: #B91C1C; }
    .pd-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .pd-card h3 { margin: 0 0 4px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .pd-card .hint { color: #64748B; font-size: 13px; margin: 0 0 14px; }
    .pd-main { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr) auto; gap: 16px; align-items: stretch; }
    .pd-box { border: 1.5px dashed #CBD5E1; border-radius: 14px; padding: 14px; background: #F8FAFC; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
    .pd-box small { color: #64748B; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; }
    .pd-box .v { font-size: 15px; font-weight: 800; color: #0369A1; overflow-wrap: anywhere; }
    .pd-box .v.code { font-size: 26px; color: #854D0E; letter-spacing: 1.5px; }
    .pd-box .pd-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: auto; }
    .pd-qr { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .pd-qr #qrBox { background: #fff; padding: 8px; border-radius: 10px; border: 1px solid #E2E8F0; width: 136px; height: 136px; }
    .pd-qr #qrBox img, .pd-qr #qrBox canvas { width: 120px !important; height: 120px !important; }
    .pd-msg { width: 100%; min-height: 64px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 9px 12px; font-size: 14px; font-family: inherit; resize: vertical; outline: none; margin-bottom: 12px; }
    .pd-plats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .pd-pl { display: flex; align-items: center; gap: 10px; border: 1px solid #E2E8F0; border-radius: 12px; padding: 9px 10px; min-width: 0; }
    .pd-pl > i { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; flex-shrink: 0; }
    .pd-pl .t { flex: 1; min-width: 0; } .pd-pl .t b { display: block; font-size: 13.5px; color: #0F172A; }
    .pd-pl .t small { display: block; color: #94A3B8; font-size: 11.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pd-pl .b { display: flex; gap: 5px; flex-shrink: 0; }
    .pd-pl .b button, .pd-pl .b a { width: 32px; height: 32px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; text-decoration: none; }
    .pd-pl .b .cp { background: #F1F5F9; color: #334155; } .pd-pl .b .sh { background: #E0F2FE; color: #0369A1; }
    .pd-rep { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .pd-rep code { flex: 1; min-width: 220px; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 10px; padding: 9px 12px; color: #334155; font-size: 13px; overflow-wrap: anywhere; }
    .pd-flt { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
    .pd-flt label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .pd-flt input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 10px; font-size: 14px; font-family: inherit; }
    .pd-quick { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .pd-quick a { padding: 5px 12px; border-radius: 999px; background: #EFF6FF; color: #1D4ED8; font-weight: 700; font-size: 12.5px; text-decoration: none; }
    .pd-quick a.on { background: #1D4ED8; color: #fff; }
    .pd-range { margin: 12px 0 0; font-size: 13.5px; font-weight: 700; color: #334155; }
    .pd-tw { overflow-x: auto; }
    .pd-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 880px; }
    .pd-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 8px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .pd-tbl td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
    .pd-who b { display: block; color: #0F172A; } .pd-who b a { color: inherit; text-decoration: none; } .pd-who small { display: block; color: #94A3B8; font-size: 12px; }
    .pd-pill { font-size: 11.5px; font-weight: 800; padding: 3px 9px; border-radius: 999px; white-space: nowrap; display: inline-flex; gap: 5px; align-items: center; }
    .pd-pill.t { background: #E0F2FE; color: #0369A1; } .pd-pill.s { background: #F3E8FF; color: #7E22CE; } .pd-pill.a { background: #FFEDD5; color: #C2410C; }
    .pd-pill.ok { background: #DCFCE7; color: #166534; } .pd-pill.no { background: #F1F5F9; color: #64748B; } .pd-pill.warn { background: #FEF3C7; color: #92400E; }
    .pd-edit { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .pd-edit label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .pd-edit input, .pd-edit select { width: 100%; height: 42px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; background: #fff; }
    .pd-edit .full { grid-column: 1 / -1; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    details.pd-card > summary { cursor: pointer; list-style: none; font-size: 16px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    details.pd-card > summary::-webkit-details-marker { display: none; }
    details.pd-card[open] > summary { margin-bottom: 14px; }
    #pdMsg { font-size: 13.5px; font-weight: 600; } #pdMsg.ok { color: #15803D; } #pdMsg.err { color: #B91C1C; }
    .pd-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; }
    .pd-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    @media (max-width: 1300px) { .pd-plats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 1100px) { .pd-main { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } .pd-qr { grid-column: 1 / -1; flex-direction: row; justify-content: center; } }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .pd-edit { grid-template-columns: minmax(0, 1fr); } .pd-head .acts { margin-left: 0; } }
    @media (max-width: 640px) { .pd-main, .pd-plats { grid-template-columns: minmax(0, 1fr); } .pd-qr { flex-direction: column; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content">
    <?= csrf_field() ?>
    <a class="pd-back" href="/Admin/Partners"><i class="fas fa-arrow-left"></i> সব পার্টনার</a>
    <div class="pd-head">
        <div class="pd-av"><i class="fas fa-building"></i></div>
        <div style="min-width:0;">
            <h2><?= e($p->Name) ?></h2>
            <div class="pd-meta">
                <span class="pd-chip">স্পেশাল ID: P-<?= str_pad((string)$pid, 4, '0', STR_PAD_LEFT) ?></span>
                <span class="pd-chip code"><i class="fas fa-ticket"></i> <?= e($code) ?></span>
                <span class="pd-chip <?= (int)$p->IsActive ? 'on' : 'no' ?>"><?= (int)$p->IsActive ? 'চালু' : 'বন্ধ — লিংক / কোড কাজ করছে না' ?></span>
                <?php if (!is_blank($p->Contact)): ?><span class="pd-chip"><i class="fas fa-phone"></i> <?= e($p->Contact) ?></span><?php endif; ?>
                <span class="pd-chip">তৈরি: <?= e(fmt_date($p->CreatedAt, 'd M Y')) ?></span>
            </div>
            <?php if (!is_blank($p->Note)): ?><div style="color:#64748B;font-size:13px;margin-top:6px;"><i class="far fa-note-sticky"></i> <?= e($p->Note) ?></div><?php endif; ?>
        </div>
        <div class="acts">
            <button type="button" class="pd-btn <?= (int)$p->IsActive ? 'warn' : 'ok' ?>" id="pdToggle" data-on="<?= (int)$p->IsActive ? 0 : 1 ?>"><i class="fas fa-power-off"></i> <?= (int)$p->IsActive ? 'বন্ধ করুন' : 'চালু করুন' ?></button>
            <?php if (admin_is_super()): ?><button type="button" class="pd-btn del" id="pdDelete"><i class="fas fa-trash"></i> মুছুন</button><?php endif; ?>
        </div>
    </div>

    <!-- লিংক + কোড + QR -->
    <div class="pd-card">
        <h3><i class="fas fa-link" style="color:#0EA5E9;"></i> রেফারেল লিংক ও কোড</h3>
        <p class="hint">এজেন্সিকে লিংক বা কোড — যেকোনোটা দিন। লিংকে ক্লিক করে কেউ সাইনআপ করলে, অথবা সাইনআপ ফর্মের "Referral Code" ঘরে কোডটা লিখলে, সে এই পার্টনারের হিসাবে যোগ হবে। লিংক ৩০ দিন মনে থাকে (ক্লিকের পরে পরে সাইনআপ করলেও গোনা হয়)।</p>
        <div class="pd-main">
            <div class="pd-box"><small>রেফারেল লিংক</small><div class="v" id="mainLink"><?= e($mainLink) ?></div>
                <div class="pd-row"><button type="button" class="pd-btn sm" data-copy="<?= e($mainLink) ?>"><i class="far fa-copy"></i> লিংক কপি</button>
                    <a class="pd-btn sm ghost" href="<?= e($mainLink) ?>" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i> খুলে দেখুন</a></div></div>
            <div class="pd-box"><small>রেফারেল কোড</small><div class="v code"><?= e($code) ?></div>
                <div class="pd-row"><button type="button" class="pd-btn sm" data-copy="<?= e($code) ?>"><i class="far fa-copy"></i> কোড কপি</button></div></div>
            <div class="pd-qr"><div id="qrBox"></div><button type="button" class="pd-btn sm ghost" id="qrDl"><i class="fas fa-download"></i> QR ডাউনলোড</button></div>
        </div>
    </div>

    <!-- প্ল্যাটফর্ম অনুযায়ী শেয়ার -->
    <div class="pd-card">
        <h3><i class="fas fa-share-nodes" style="color:#7C3AED;"></i> প্ল্যাটফর্ম অনুযায়ী লিংক শেয়ার</h3>
        <p class="hint">প্রতিটা প্ল্যাটফর্মের জন্য আলাদা লিংক — তাই কোন প্ল্যাটফর্ম থেকে কত জন এসেছে আলাদা করে দেখা যায়। ফেসবুকে দিলে Facebook লিংক, WhatsApp এ দিলে WhatsApp লিংক — এভাবে দিন। <i class="fas fa-share" style="color:#0369A1;"></i> চাপলে সরাসরি ঐ অ্যাপে শেয়ার খুলবে।</p>
        <label for="shareMsg" style="font-size:12.5px;font-weight:700;color:#334155;display:block;margin-bottom:5px;">শেয়ার মেসেজ (লিংকের আগে যাবে — বদলাতে পারবেন)</label>
        <textarea class="pd-msg" id="shareMsg" maxlength="400">মাষ্টার মশাই — ভালো টিউটর খুঁজুন বা টিউশন পান সহজে। এখনই ফ্রি অ্যাকাউন্ট খুলুন (রেফারেল কোড: <?= e($code) ?>)</textarea>
        <div class="pd-plats">
            <?php foreach ($shareSrc as $s): [$sn, $si, $sc] = Db::partnerSource($s); $l = partner_link($code, $s); ?>
                <div class="pd-pl" data-src="<?= e($s) ?>" data-link="<?= e($l) ?>">
                    <i class="<?= e($si) ?>" style="background:<?= e($sc) ?>;"></i>
                    <div class="t"><b><?= e($sn) ?></b><small><?= e(preg_replace('#^https?://#', '', $l)) ?></small></div>
                    <div class="b">
                        <button type="button" class="cp" data-copy="<?= e($l) ?>" title="লিংক কপি"><i class="far fa-copy"></i></button>
                        <?php if (in_array($s, ['facebook', 'messenger', 'whatsapp', 'telegram', 'sms', 'email'], true)): ?><a href="#" class="sh" data-share="<?= e($s) ?>" title="<?= e($sn) ?> এ শেয়ার" target="_blank" rel="noopener"><i class="fas fa-share"></i></a>
                        <?php else: ?><button type="button" class="sh" data-native="1" title="শেয়ার"><i class="fas fa-share"></i></button><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- এজেন্সির রিপোর্ট লিংক -->
    <div class="pd-card">
        <h3><i class="fas fa-file-shield" style="color:#16A34A;"></i> এজেন্সির জন্য রিপোর্ট লিংক</h3>
        <p class="hint">এই লিংকটা এজেন্সিকে দিলে তারা লগইন ছাড়াই নিজেদের ক্লিক, সাইনআপ আর কেনার সংখ্যা দেখতে পারবে — কোনো ইউজারের নাম, ফোন বা ইমেইল দেখাবে না। লিংক ফাঁস হলে "নতুন লিংক" চাপুন — পুরোনোটা বন্ধ হয়ে যাবে।</p>
        <div class="pd-rep"><code id="repUrl"><?= e($reportUrl) ?></code>
            <button type="button" class="pd-btn sm" id="repCopy"><i class="far fa-copy"></i> কপি</button>
            <a class="pd-btn sm ghost" id="repOpen" href="<?= e($reportUrl) ?>" target="_blank" rel="noopener"><i class="fas fa-eye"></i> দেখুন</a>
            <button type="button" class="pd-btn sm warn" id="repReset"><i class="fas fa-rotate"></i> নতুন লিংক</button></div>
    </div>

    <!-- তারিখ ফিল্টার -->
    <div class="pd-card">
        <h3><i class="fas fa-filter" style="color:#0EA5E9;"></i> হিসাব — তারিখ অনুযায়ী</h3>
        <form class="pd-flt" method="get" action="/Admin/Partner/<?= $pid ?>">
            <div><label for="fFrom">শুরু</label><input type="date" id="fFrom" name="from" value="<?= e($from ?? '') ?>" max="<?= $today ?>" /></div>
            <div><label for="fTo">শেষ</label><input type="date" id="fTo" name="to" value="<?= e($to ?? '') ?>" max="<?= $today ?>" /></div>
            <button type="submit" class="pd-btn"><i class="fas fa-magnifying-glass"></i> দেখুন</button>
            <a class="pd-btn ghost" href="/Admin/Partner/<?= $pid ?>">সব সময়</a>
            <a class="pd-btn ok" href="<?= e($csvUrl) ?>" style="margin-left:auto;"><i class="fas fa-file-csv"></i> Export CSV</a>
        </form>
        <div class="pd-quick"><?php foreach ($quick as $lbl => [$a, $b]): ?><a class="<?= $from === $a && $to === $b ? 'on' : '' ?>" href="/Admin/Partner/<?= $pid ?>?from=<?= $a ?>&to=<?= $b ?>"><?= $lbl ?></a><?php endforeach; ?></div>
        <p class="pd-range"><i class="far fa-calendar"></i> <?= $from || $to ? e(($from ? fmt_date($from, 'd M Y') : 'শুরু থেকে') . ' — ' . ($to ? fmt_date($to, 'd M Y') : 'আজ পর্যন্ত')) : 'শুরু থেকে আজ পর্যন্ত (সব সময়)' ?></p>
    </div>

    <?php partial('Partner/_Stats', ['R' => $R]); ?>

    <!-- ইউজার তালিকা -->
    <div class="pd-card">
        <h3><i class="fas fa-users" style="color:#6366F1;"></i> এই পার্টনার থেকে যারা সাইনআপ করেছে (<?= count($R['users']) ?>)</h3>
        <?php if (!$R['users']): ?>
            <div class="pd-empty">এই সময়ে কেউ সাইনআপ করেনি।</div>
        <?php else: ?>
        <div class="pd-tw">
            <table class="pd-tbl">
                <thead><tr><th>#</th><th>ইউজার</th><th>ধরন</th><th>কিভাবে এসেছে</th><th>সাইনআপ</th><th>ইমেইল</th><th>প্যাকেজ</th><th>মোট কিনেছে</th></tr></thead>
                <tbody>
                <?php foreach ($R['users'] as $i => $u): [$rl, $rc] = $roleLbl((string)$u->Role); [$sn, $si, $sc] = Db::partnerSource((string)$u->Source); ?>
                    <tr>
                        <td style="color:#94A3B8;font-weight:700;"><?= $i + 1 ?></td>
                        <td class="pd-who"><b><a href="/Admin/UserOverview/<?= (int)$u->UserId ?>"><?= e(!is_blank($u->FullName) ? $u->FullName : $u->UserName) ?></a></b><small>@<?= e($u->UserName) ?> · <?= e($u->Email) ?></small></td>
                        <td><span class="pd-pill <?= $rc ?>"><?= $rl ?></span></td>
                        <td><span class="pd-pill no"><i class="<?= e($si) ?>" style="color:<?= e($sc) ?>;"></i> <?= $u->Via === 'code' ? 'কোড দিয়ে' : e($sn) ?></span></td>
                        <td style="white-space:nowrap;"><?= e(fmt_date($u->SignedUp, 'd M Y')) ?><small style="display:block;color:#94A3B8;"><?= e(fmt_date($u->SignedUp, 'h:i A')) ?></small></td>
                        <td><?= $u->Verified ? '<span class="pd-pill ok"><i class="fas fa-check"></i> ভেরিফাইড</span>' : '<span class="pd-pill warn">OTP দেয়নি</span>' ?></td>
                        <td><?php if ($u->Orders > 0): ?><span class="pd-pill ok"><i class="fas fa-bag-shopping"></i> <?= e($u->LastPackage) ?><?= $u->Orders > 1 ? ' +' . ($u->Orders - 1) : '' ?></span>
                            <?php elseif ($u->Pending > 0): ?><span class="pd-pill warn">পেমেন্ট অপেক্ষমাণ</span>
                            <?php else: ?><span class="pd-pill no">কেনেনি</span><?php endif; ?></td>
                        <td style="font-weight:800;color:<?= $u->Paid > 0 ? '#15803D' : '#94A3B8' ?>;white-space:nowrap;">৳<?= number_format($u->Paid) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- এডিট -->
    <details class="pd-card">
        <summary><i class="fas fa-pen" style="color:#F59E0B;"></i> পার্টনারের তথ্য / কোড বদলান</summary>
        <form class="pd-edit" id="pdEdit" autocomplete="off">
            <div><label for="eName">নাম *</label><input id="eName" name="name" maxlength="100" value="<?= e($p->Name) ?>" required /></div>
            <div><label for="eCode">রেফারেল কোড</label><input id="eCode" name="code" maxlength="20" value="<?= e($code) ?>" style="text-transform:uppercase;font-weight:800;" /></div>
            <div><label for="eContact">যোগাযোগ</label><input id="eContact" name="contact" maxlength="150" value="<?= e($p->Contact ?? '') ?>" /></div>
            <div><label for="eLanding">লিংকে ক্লিক করলে যাবে</label><select id="eLanding" name="landing">
                <option value="register"<?= $p->Landing !== 'home' ? ' selected' : '' ?>>সাইনআপ পেজে (Create Account)</option><option value="home"<?= $p->Landing === 'home' ? ' selected' : '' ?>>হোম পেজে</option></select></div>
            <div style="grid-column:span 2;"><label for="eNote">নোট</label><input id="eNote" name="note" maxlength="300" value="<?= e($p->Note ?? '') ?>" /></div>
            <div class="full"><button type="submit" class="pd-btn ok"><i class="fas fa-floppy-disk"></i> সেভ করুন</button><span id="pdMsg"></span>
                <small style="color:#B45309;font-weight:600;"><i class="fas fa-triangle-exclamation"></i> কোড বদলালে আগের লিংক / কোড আর কাজ করবে না — পুরোনো হিসাব থাকবে।</small></div>
        </form>
    </details>
</div>
<div class="pd-toast" id="pdToast"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    var PID = <?= $pid ?>, CODE = <?= js($code) ?>, QR_LINK = <?= js(partner_link($code, 'qr')) ?>;
    var tok = document.querySelector('input[name="__RequestVerificationToken"]').value;
    function toast(m) { var t = document.getElementById('pdToast'); t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 2400); }
    async function post(url, data) {
        var fd = new FormData(); fd.append('__RequestVerificationToken', tok);
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        try { return await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); }
        catch (e) { return { success: false, message: 'সার্ভারে সমস্যা — আবার চেষ্টা করুন।' }; }
    }
    function copy(v) {
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(v).then(function () { toast('কপি হয়েছে ✓'); }); return; }
        var a = document.createElement('textarea'); a.value = v; document.body.appendChild(a); a.select(); try { document.execCommand('copy'); toast('কপি হয়েছে ✓'); } catch (e) { } a.remove();
    }
    function msg() { return document.getElementById('shareMsg').value.trim(); }
    function shareUrl(s, l) {
        var m = msg(), t = encodeURIComponent(m + '\n' + l), u = encodeURIComponent(l);
        return { facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + u + '&quote=' + encodeURIComponent(m),
            messenger: 'fb-messenger://share/?link=' + u, whatsapp: 'https://wa.me/?text=' + t,
            telegram: 'https://t.me/share/url?url=' + u + '&text=' + encodeURIComponent(m), sms: 'sms:?&body=' + t,
            email: 'mailto:?subject=' + encodeURIComponent('মাষ্টার মশাই') + '&body=' + t }[s];
    }
    document.addEventListener('click', async function (e) {
        var c = e.target.closest('[data-copy]'); if (c) { copy(c.getAttribute('data-copy')); return; }
        var sh = e.target.closest('[data-share]');
        if (sh) { var l = sh.closest('.pd-pl').getAttribute('data-link'); sh.href = shareUrl(sh.getAttribute('data-share'), l); if (/^(fb-messenger|sms|mailto):/.test(sh.href)) { e.preventDefault(); location.href = sh.href; } return; }
        var nt = e.target.closest('[data-native]');
        if (nt) {
            var lk = nt.closest('.pd-pl').getAttribute('data-link');
            if (navigator.share) { try { await navigator.share({ text: msg(), url: lk }); } catch (x) { } }
            else { copy(msg() + '\n' + lk); toast('মেসেজ + লিংক কপি হয়েছে — ঐ অ্যাপে পেস্ট করুন'); }
        }
    });

    // QR (পোস্টার / লিফলেটের জন্য — src=qr)
    try {
        new QRCode(document.getElementById('qrBox'), { text: QR_LINK, width: 360, height: 360, correctLevel: QRCode.CorrectLevel.M });
    } catch (x) { document.getElementById('qrBox').textContent = 'QR লোড হয়নি'; }
    document.getElementById('qrDl').addEventListener('click', function () {
        var cv = document.querySelector('#qrBox canvas'), im = document.querySelector('#qrBox img');
        var src = cv ? cv.toDataURL('image/png') : (im ? im.src : ''); if (!src) return;
        var a = document.createElement('a'); a.href = src; a.download = 'mashtermoshai-' + CODE + '-qr.png'; document.body.appendChild(a); a.click(); a.remove();
    });

    document.getElementById('repCopy').addEventListener('click', function () { copy(document.getElementById('repUrl').textContent); });
    document.getElementById('repReset').addEventListener('click', async function () {
        if (!confirm('নতুন রিপোর্ট লিংক বানাবেন? পুরোনো লিংক আর খুলবে না।')) return;
        var d = await post('/Admin/ResetPartnerToken', { id: PID }); toast(d.message || '');
        if (d.success) { document.getElementById('repUrl').textContent = d.url; document.getElementById('repOpen').href = d.url; }
    });
    document.getElementById('pdToggle').addEventListener('click', async function () {
        var b = this; b.disabled = true;
        var d = await post('/Admin/TogglePartner', { id: PID, on: b.getAttribute('data-on') === '1' ? 'true' : 'false' }); toast(d.message || '');
        if (d.success) setTimeout(function () { location.reload(); }, 700); else b.disabled = false;
    });
    var del = document.getElementById('pdDelete');
    if (del) del.addEventListener('click', async function () {
        if (!confirm('এই পার্টনার মুছে ফেলবেন? ক্লিক আর "কে এই পার্টনার থেকে এসেছে" এর হিসাবও মুছে যাবে (ইউজাররা থাকবে)। শুধু বন্ধ রাখতে চাইলে "বন্ধ করুন" চাপুন।')) return;
        var d = await post('/Admin/DeletePartner', { id: PID }); toast(d.message || '');
        if (d.success) setTimeout(function () { location.href = '/Admin/Partners'; }, 700);
    });
    var ec = document.getElementById('eCode');
    ec.addEventListener('input', function () { ec.value = ec.value.toUpperCase().replace(/[^A-Z0-9_-]/g, ''); });
    document.getElementById('pdEdit').addEventListener('submit', async function (e) {
        e.preventDefault();
        var f = e.target, m = document.getElementById('pdMsg');
        m.className = ''; m.textContent = 'সেভ হচ্ছে...';
        var d = await post('/Admin/SavePartner', { id: PID, name: f.elements['name'].value, code: f.elements['code'].value, contact: f.elements['contact'].value, note: f.elements['note'].value, landing: f.elements['landing'].value });
        m.className = d.success ? 'ok' : 'err'; m.textContent = d.message || '';
        if (d.success) setTimeout(function () { location.reload(); }, 700);
    });
})();
</script>
