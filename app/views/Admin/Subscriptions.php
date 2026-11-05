<?php
/** Admin → Subscriptions: কে কোন প্যাকেজ নিয়েছে, কত টাকা, মোট কত কিনেছে, মেয়াদ কবে শেষ + সতর্ক করার দিন */
$ViewData['Title'] = 'Subscriptions';
$list = is_array($Model) ? $Model : [];
$st = $ViewBag->Stats; $aud = (string)$ViewBag->Aud; $fs = (string)$ViewBag->St; $days = (int)$ViewBag->AlertDays;
$from = $ViewBag->From; $to = $ViewBag->To; $sum = $ViewBag->Sum; $daily = $ViewBag->Daily ?? [];
$params = array_filter(['aud' => $aud, 'st' => $fs, 'from' => $from, 'to' => $to], fn($v) => $v !== null && $v !== '' && $v !== 'all');
$csvUrl = '/Admin/SubscriptionsCsv' . ($params ? '?' . http_build_query($params) : '');
$payLbl = ['Approved' => ['কিনেছে (Approved)', 'ok'], 'Pending' => ['অপেক্ষমাণ', 'warn'], 'Rejected' => ['বাতিল (Rejected)', 'no']];
$dur = fn(int $m, int $d) => $m > 0 ? bn_num($m) . ' মাস' . ($d > 0 ? ' <small style="color:#64748B;">(' . $d . ' দিন)</small>' : '') : ($d > 0 ? $d . ' দিন' : 'মেয়াদহীন');
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .sb h2 { color: #08224A; margin: 0; font-size: 24px; }
    .sb .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; }
    .sb-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .sb-st { background: #fff; border-radius: 16px; padding: 16px 18px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); }
    .sb-st h4 { margin: 0 0 10px; font-size: 15px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .sb-st h4 i { color: var(--c); }
    .sb-st .g { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .sb-st small { display: block; color: #64748B; font-size: 12px; font-weight: 700; }
    .sb-st b { font-size: 20px; color: #0F172A; }
    .sb-st b.rev { color: #15803D; }
    .sb-st b.warn { color: #B45309; }
    .sb-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .sb-card h3 { margin: 0 0 6px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .sb-alert { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-top: 10px; }
    .sb-alert label { font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 6px; }
    .sb-alert .in { display: flex; align-items: center; border: 1.5px solid #DCECF5; border-radius: 10px; overflow: hidden; }
    .sb-alert input { width: 90px; height: 42px; border: none; padding: 0 12px; font-size: 17px; font-weight: 800; font-family: inherit; outline: none; }
    .sb-alert .in span { padding: 0 12px; color: #64748B; font-size: 13.5px; font-weight: 700; background: #F8FAFC; height: 42px; display: flex; align-items: center; border-left: 1px solid #E2E8F0; }
    .sb-btn { height: 42px; padding: 0 18px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; display: inline-flex; align-items: center; gap: 7px; }
    #sbMsg { font-size: 13.5px; font-weight: 600; } #sbMsg.ok { color: #15803D; } #sbMsg.err { color: #B91C1C; }
    .sb-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
    .sb-tabs a { padding: 7px 14px; border-radius: 999px; background: #F1F5F9; color: #334155; font-weight: 700; font-size: 13px; text-decoration: none; }
    .sb-tabs a.on { background: #08224A; color: #fff; }
    .sb-tw { overflow-x: auto; }
    .sb-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 860px; }
    .sb-tbl th { text-align: left; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 8px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .sb-tbl td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
    .sb-who { display: flex; align-items: center; gap: 10px; }
    .sb-av { width: 36px; height: 36px; border-radius: 50%; background: #E0F2FE; color: #0369A1; font-weight: 800; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; font-size: 13px; }
    .sb-av img { width: 100%; height: 100%; object-fit: cover; }
    .sb-who a { color: #0F172A; font-weight: 700; text-decoration: none; } .sb-who small { display: block; color: #94A3B8; font-size: 12px; }
    .sb-aud { font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; }
    .sb-aud.t { background: #E0F2FE; color: #0369A1; } .sb-aud.s { background: #F3E8FF; color: #7E22CE; }
    .sb-pill { font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .sb-pill.ok { background: #DCFCE7; color: #166534; } .sb-pill.warn { background: #FEF3C7; color: #92400E; } .sb-pill.no { background: #FEE2E2; color: #991B1B; }
    .sb-money { font-weight: 800; color: #0F172A; white-space: nowrap; }
    .sb-flt { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; align-items: end; margin-top: 10px; }
    .sb-flt label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .sb-flt select, .sb-flt input { width: 100%; height: 42px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 10px; font-size: 14px; font-family: inherit; background: #fff; color: #0F172A; }
    .sb-flt-b { grid-column: 1 / -1; display: flex; gap: 8px; flex-wrap: wrap; }
    .sb-btn.ghost { background: #F1F5F9; color: #334155; text-decoration: none; }
    .sb-btn.green { background: #15803D; text-decoration: none; margin-left: auto; }
    .sb-quick { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; margin-top: 10px; font-size: 12.5px; }
    .sb-quick span { color: #94A3B8; font-weight: 700; }
    .sb-quick a { padding: 4px 11px; border-radius: 999px; background: #EFF6FF; color: #1D4ED8; font-weight: 700; text-decoration: none; }
    .sb-range { margin: 16px 0 8px; font-size: 13.5px; font-weight: 700; color: #334155; }
    .sb-sum { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .sb-sum > div { border: 1px solid #E2E8F0; border-radius: 12px; padding: 12px 14px; border-left: 4px solid #94A3B8; }
    .sb-sum .ok { border-left-color: #16A34A; } .sb-sum .warn { border-left-color: #F59E0B; } .sb-sum .no { border-left-color: #DC2626; }
    .sb-sum small { display: block; color: #64748B; font-size: 12px; font-weight: 700; } .sb-sum b { font-size: 20px; color: #0F172A; display: block; } .sb-sum span { font-size: 13px; color: #475569; font-weight: 700; }
    @media (max-width: 760px) { .sb-flt, .sb-sum { grid-template-columns: repeat(2, minmax(0, 1fr)); } .sb-btn.green { margin-left: 0; } }
    .sb-empty { text-align: center; color: #94A3B8; padding: 26px 0; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .sb-stats { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 520px) { .sb-st .g { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content sb">
    <?= csrf_field() ?>
    <h2><i class="fas fa-id-card-clip" style="color:#00B5E0;"></i> Subscriptions</h2>
    <p class="sub">কোন টিচার / শিক্ষার্থী কোন প্যাকেজ নিয়েছেন, কত টাকার, মোট কত টাকার প্যাকেজ কিনেছেন আর মেয়াদ কবে শেষ — সব এক জায়গায়। টাকার হিসাব শুধু Approve করা পেমেন্ট থেকে।</p>

    <div class="sb-stats">
        <?php foreach (['Teacher' => ['শিক্ষক', 'fa-chalkboard-user', '#0EA5E9'], 'Student' => ['শিক্ষার্থী', 'fa-user-graduate', '#A855F7']] as $a => [$lbl, $ic, $c]): $x = $st[$a]; ?>
            <div class="sb-st" style="--c:<?= $c ?>;">
                <h4><i class="fas <?= $ic ?>"></i> <?= $lbl ?></h4>
                <div class="g">
                    <div><small>প্যাকেজ নিয়েছেন</small><b><?= number_format($x['users']) ?> জন</b></div>
                    <div><small>এখন চালু</small><b><?= number_format($x['active']) ?></b></div>
                    <div><small>শিগগির শেষ হবে</small><b class="warn"><?= number_format($x['expiring']) ?></b></div>
                    <div><small>মোট আয়</small><b class="rev">৳<?= number_format($x['revenue']) ?></b></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="sb-card">
        <h3><i class="fas fa-bell" style="color:#F59E0B;"></i> মেয়াদ শেষের সতর্কতা</h3>
        <p style="font-size:13.5px;color:#64748B;margin:0;">প্যাকেজের মেয়াদ শেষ হওয়ার এত দিন আগে থেকে টিচার আর শিক্ষার্থী নোটিফিকেশন পাবেন আর সাইটের নিচে রিনিউ করার বার দেখবেন। শেষ দিনের আগেও আরেকবার মনে করিয়ে দেওয়া হয়।</p>
        <form class="sb-alert" id="sbForm" novalidate>
            <div><label for="sbDays">কত দিন আগে থেকে সতর্ক করবে</label>
                <div class="in"><input type="number" id="sbDays" name="days" min="1" max="30" value="<?= $days ?>" /><span>দিন আগে</span></div></div>
            <button type="submit" class="sb-btn"><i class="fas fa-save"></i> Save</button>
            <span id="sbMsg" role="status"></span>
        </form>
    </div>

    <div class="sb-card">
        <h3><i class="fas fa-filter" style="color:#00B5E0;"></i> রিপোর্ট</h3>
        <form class="sb-flt" method="get" action="/Admin/Subscriptions">
            <div><label for="fAud">কার</label><select id="fAud" name="aud">
                <?php foreach (['all' => 'সবাই (টিচার + শিক্ষার্থী)', 'Teacher' => 'শিক্ষক', 'Student' => 'শিক্ষার্থী'] as $k => $l): ?><option value="<?= $k ?>"<?= $aud === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            <div><label for="fSt">অবস্থা</label><select id="fSt" name="st">
                <?php foreach (['all' => 'সব পেমেন্ট', 'Approved' => 'প্যাকেজ কিনেছে (Approved)', 'Pending' => 'অপেক্ষমাণ (Pending)', 'Rejected' => 'বাতিল (Rejected)', 'never' => 'এখনো কোনো প্যাকেজ কেনেনি'] as $k => $l): ?><option value="<?= $k ?>"<?= $fs === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            <div><label for="fFrom">From</label><input type="date" id="fFrom" name="from" value="<?= e((string)$from) ?>" max="<?= date('Y-m-d') ?>" /></div>
            <div><label for="fTo">To</label><input type="date" id="fTo" name="to" value="<?= e((string)$to) ?>" max="<?= date('Y-m-d') ?>" /></div>
            <div class="sb-flt-b">
                <button type="submit" class="sb-btn"><i class="fas fa-magnifying-glass"></i> দেখুন</button>
                <a class="sb-btn ghost" href="/Admin/Subscriptions">Reset</a>
                <a class="sb-btn green" href="<?= e($csvUrl) ?>"><i class="fas fa-file-csv"></i> Export CSV</a>
            </div>
        </form>
        <div class="sb-quick">
            <?php $qd = fn($f, $t) => '/Admin/Subscriptions?' . http_build_query(array_filter(['aud' => $aud !== 'all' ? $aud : null, 'st' => $fs !== 'all' ? $fs : null, 'from' => $f, 'to' => $t])); ?>
            <span>দ্রুত:</span>
            <a href="<?= e($qd(date('Y-m-d'), date('Y-m-d'))) ?>">আজ</a>
            <a href="<?= e($qd(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'))) ?>">গত ৭ দিন</a>
            <a href="<?= e($qd(date('Y-m-01'), date('Y-m-d'))) ?>">এই মাস</a>
            <a href="<?= e($qd(date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month')))) ?>">গত মাস</a>
            <a href="<?= e($qd(date('Y-01-01'), date('Y-m-d'))) ?>">এই বছর</a>
        </div>

        <p class="sb-range"><i class="far fa-calendar"></i> <?= $from || $to ? e(($from ? fmt_date($from, 'd M Y') : 'শুরু থেকে') . ' — ' . ($to ? fmt_date($to, 'd M Y') : 'আজ পর্যন্ত')) : 'সব সময়ের হিসাব' ?> · <?= ['all' => 'টিচার + শিক্ষার্থী', 'Teacher' => 'শুধু শিক্ষক', 'Student' => 'শুধু শিক্ষার্থী'][$aud] ?></p>
        <div class="sb-sum">
            <div class="ok"><small>প্যাকেজ কিনেছে</small><b><?= (int)$sum['Approved'][0] ?> টি</b><span>৳<?= number_format($sum['Approved'][1]) ?></span></div>
            <div class="warn"><small>অপেক্ষমাণ</small><b><?= (int)$sum['Pending'][0] ?> টি</b><span>৳<?= number_format($sum['Pending'][1]) ?></span></div>
            <div class="no"><small>বাতিল</small><b><?= (int)$sum['Rejected'][0] ?> টি</b><span>৳<?= number_format($sum['Rejected'][1]) ?></span></div>
            <div><small>এখনো কেনেনি</small><b><?= (int)$ViewBag->NeverCount ?> জন</b><span><?= $from || $to ? 'এই সময়ে অ্যাকাউন্ট খোলা' : 'সব অ্যাকাউন্ট' ?></span></div>
        </div>
    </div>

    <?php if ($fs !== 'never' && $daily): ?>
    <div class="sb-card">
        <h3><i class="fas fa-calendar-days" style="color:#8B5CF6;"></i> তারিখ অনুযায়ী</h3>
        <div class="sb-tw"><table class="sb-tbl" style="min-width:560px;">
            <tr><th>তারিখ</th><th>কিনেছে</th><th>আয়</th><th>অপেক্ষমাণ</th><th>বাতিল</th></tr>
            <?php foreach (array_slice($daily, 0, 62, true) as $day => $x): ?>
                <tr><td><b><?= e(fmt_date($day, 'd M Y')) ?></b> <small style="color:#94A3B8;"><?= e(fmt_date($day, 'l')) ?></small></td>
                    <td><?= $x['Approved'][0] ? '<span class="sb-pill ok">' . (int)$x['Approved'][0] . ' জন</span>' : '—' ?></td>
                    <td class="sb-money"><?= $x['Approved'][1] ? '৳' . number_format($x['Approved'][1]) : '—' ?></td>
                    <td><?= $x['Pending'][0] ? '<span class="sb-pill warn">' . (int)$x['Pending'][0] . '</span>' : '—' ?></td>
                    <td><?= $x['Rejected'][0] ? '<span class="sb-pill no">' . (int)$x['Rejected'][0] . '</span>' : '—' ?></td></tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php endif; ?>

    <div class="sb-card">
        <h3><i class="fas fa-list" style="color:#0EA5E9;"></i> <?= $fs === 'never' ? 'যারা এখনো কোনো প্যাকেজ কেনেননি' : 'পেমেন্ট / প্যাকেজের তালিকা' ?> <small style="font-weight:600;color:#94A3B8;font-size:13px;">(<?= count($list) ?>)</small></h3>
        <?php if (!$list): ?><div class="sb-empty">এই ফিল্টারে কিছু পাওয়া যায়নি।</div>
        <?php elseif ($fs === 'never'): ?>
        <div class="sb-tw"><table class="sb-tbl">
            <tr><th>ইউজার</th><th>ধরন</th><th>মোবাইল</th><th>অ্যাকাউন্ট খোলা</th><th>শেষ লগইন</th><th>পেমেন্টের চেষ্টা</th></tr>
            <?php foreach ($list as $u): ?>
                <tr>
                    <td><div class="sb-who"><span class="sb-av"><?= e(initials($u->Name, 'U')) ?></span><div><a href="/Profile/Show/<?= (int)$u->UserId ?>" target="_blank" rel="noopener"><?= e($u->Name) ?></a><small>@<?= e($u->UserName) ?><?= $u->Email !== '' ? ' · ' . e($u->Email) : '' ?></small></div></div></td>
                    <td><span class="sb-aud <?= $u->Audience === 'Teacher' ? 't' : 's' ?>"><?= $u->Audience === 'Teacher' ? 'শিক্ষক' : 'শিক্ষার্থী' ?></span></td>
                    <td><?= $u->Phone !== '' ? e($u->Phone) : '—' ?></td>
                    <td><?= e(fmt_date($u->Joined, 'd M Y')) ?></td>
                    <td><?= $u->LastLogin ? e(fmt_date($u->LastLogin, 'd M Y')) : '<span style="color:#94A3B8;">কখনো না</span>' ?></td>
                    <td><?= $u->Pending ? '<span class="sb-pill warn">' . $u->Pending . ' অপেক্ষমাণ</span> ' : '' ?><?= $u->Rejected ? '<span class="sb-pill no">' . $u->Rejected . ' বাতিল</span>' : '' ?><?= !$u->Pending && !$u->Rejected ? '<span style="color:#94A3B8;">কোনো চেষ্টা নেই</span>' : '' ?></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php else: ?>
        <div class="sb-tw"><table class="sb-tbl">
            <tr><th>তারিখ</th><th>ইউজার</th><th>ধরন</th><th>প্যাকেজ</th><th>দাম</th><th>মেয়াদ</th><th>অবস্থা</th><th>মেয়াদ শেষ</th><th>মোট কেনা</th></tr>
            <?php foreach ($list as $r): [$pl, $pc] = $payLbl[$r->Status] ?? [$r->Status, '']; ?>
                <tr>
                    <td style="white-space:nowrap;"><?= e(fmt_date($r->Date, 'd M Y')) ?><br><small style="color:#94A3B8;"><?= e(fmt_date($r->Date, 'h:i A')) ?></small></td>
                    <td><div class="sb-who"><span class="sb-av"><?= e(initials($r->Name, 'U')) ?></span><div><a href="/Profile/Show/<?= (int)$r->UserId ?>" target="_blank" rel="noopener"><?= e($r->Name) ?></a><small>@<?= e($r->UserName) ?><?= $r->Phone !== '' ? ' · ' . e($r->Phone) : '' ?></small></div></div></td>
                    <td><span class="sb-aud <?= $r->Audience === 'Teacher' ? 't' : 's' ?>"><?= $r->Audience === 'Teacher' ? 'শিক্ষক' : 'শিক্ষার্থী' ?></span></td>
                    <td><b><?= e($r->Package) ?></b><br><small style="color:#94A3B8;"><?= e($r->Method) ?></small></td>
                    <td class="sb-money">৳<?= number_format($r->Amount) ?></td>
                    <td style="white-space:nowrap;"><?= $dur($r->Months, $r->Days) ?></td>
                    <td><span class="sb-pill <?= $pc ?>"><?= $pl ?></span></td>
                    <td style="white-space:nowrap;"><?php if ($r->EndsAt): ?><?= e(fmt_date($r->EndsAt, 'd M Y')) ?><br><small style="color:<?= $r->DaysLeft > 0 ? '#64748B' : '#B91C1C' ?>;"><?= $r->DaysLeft > 0 ? 'আর ' . (int)$r->DaysLeft . ' দিন' : 'মেয়াদ শেষ' ?></small><?php else: ?>—<?php endif; ?></td>
                    <td class="sb-money">৳<?= number_format($r->UserTotal) ?></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    var f = document.getElementById('sbForm'), m = document.getElementById('sbMsg');
    f.addEventListener('submit', async function (e) {
        e.preventDefault(); m.className = ''; m.textContent = 'সেভ হচ্ছে...';
        var fd = new FormData(); fd.append('days', f.days.value);
        fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        try {
            var d = await (await fetch('/Admin/SaveSubAlertDays', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            m.className = d.success ? 'ok' : 'err'; m.textContent = (d.success ? '✓ ' : '') + (d.message || '');
        } catch (x) { m.className = 'err'; m.textContent = 'সেভ করা যায়নি।'; }
    });
})();
</script>
