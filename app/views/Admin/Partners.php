<?php
/** Admin → এজেন্সি লিংক ও কোড: একটাই স্থায়ী লিংক + কোড, তা থেকে কত জন সাইনআপ / প্যাকেজ কিনেছে — তারিখ অনুযায়ী, Excel ডাউনলোড */
$ViewData['Title'] = 'Agency Link & Code';
$p = $Model; $R = $ViewBag->Report; $t = $R['totals']; $from = $ViewBag->From; $to = $ViewBag->To;
$code = (string)$p->Code; $link = partner_link($code);
$qs = array_filter(['from' => $from, 'to' => $to]);
$xlsUrl = '/Admin/PartnerCsv' . ($qs ? '?' . http_build_query($qs) : '');
$today = date('Y-m-d');
$quick = ['আজ' => [$today, $today], 'গত ৭ দিন' => [date('Y-m-d', strtotime('-6 days')), $today], 'গত ৩০ দিন' => [date('Y-m-d', strtotime('-29 days')), $today],
    'এই মাস' => [date('Y-m-01'), $today], 'গত মাস' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))]];
$roleLbl = fn($r) => $r === 'Teacher' ? ['Teacher', 't'] : ($r === 'Admin' ? ['Admin', 'a'] : ['Student', 's']);
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .ag h2 { color: #08224A; margin: 0; font-size: 24px; }
    .ag .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 900px; }
    .ag-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; min-width: 0; }
    .ag-card h3 { margin: 0 0 12px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .ag-lc { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); gap: 16px; }
    .ag-box { border: 1.5px dashed #CBD5E1; border-radius: 14px; padding: 16px; background: #F8FAFC; display: flex; flex-direction: column; gap: 10px; min-width: 0; }
    .ag-box small { color: #64748B; font-size: 12px; font-weight: 800; letter-spacing: .3px; }
    .ag-box .v { font-size: 17px; font-weight: 800; color: #0369A1; overflow-wrap: anywhere; }
    .ag-box .v.code { font-size: 28px; color: #854D0E; letter-spacing: 2px; }
    .ag-box .b { display: flex; gap: 8px; flex-wrap: wrap; margin-top: auto; }
    .ag-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; white-space: nowrap; }
    .ag-btn.ghost { background: #F1F5F9; color: #334155; } .ag-btn.xl { background: #15803D; }
    .ag-flt { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
    .ag-flt label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .ag-flt input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 10px; font-size: 14px; font-family: inherit; }
    .ag-flt .xl { margin-left: auto; }
    .ag-quick { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .ag-quick a { padding: 5px 12px; border-radius: 999px; background: #EFF6FF; color: #1D4ED8; font-weight: 700; font-size: 12.5px; text-decoration: none; }
    .ag-quick a.on { background: #1D4ED8; color: #fff; }
    .ag-range { margin: 12px 0 0; font-size: 13.5px; font-weight: 700; color: #334155; }
    .ag-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .ag-k { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); min-width: 0; }
    .ag-k small { display: flex; gap: 6px; align-items: center; color: #64748B; font-size: 12px; font-weight: 700; } .ag-k small i { color: var(--c); }
    .ag-k b { display: block; font-size: 24px; color: #0F172A; margin-top: 4px; } .ag-k span { display: block; font-size: 12px; color: #94A3B8; font-weight: 600; }
    .ag-tw { overflow-x: auto; }
    .ag-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 620px; }
    .ag-tbl th { text-align: right; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 10px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .ag-tbl td { padding: 10px; border-bottom: 1px solid #F1F5F9; color: #334155; text-align: right; white-space: nowrap; vertical-align: middle; }
    .ag-tbl th:first-child, .ag-tbl td:first-child { text-align: left; }
    .ag-tbl tfoot td { font-weight: 800; color: #0F172A; border-top: 2px solid #E2E8F0; border-bottom: none; }
    .ag-tbl.users { min-width: 860px; } .ag-tbl.users th, .ag-tbl.users td { text-align: left; }
    .ag-money { color: #15803D; font-weight: 800; }
    .ag-who b { display: block; color: #0F172A; } .ag-who b a { color: inherit; text-decoration: none; } .ag-who small { display: block; color: #94A3B8; font-size: 12px; }
    .ag-pill { font-size: 11.5px; font-weight: 800; padding: 3px 9px; border-radius: 999px; white-space: nowrap; display: inline-flex; gap: 5px; align-items: center; }
    .ag-pill.t { background: #E0F2FE; color: #0369A1; } .ag-pill.s { background: #F3E8FF; color: #7E22CE; } .ag-pill.a { background: #FFEDD5; color: #C2410C; }
    .ag-pill.ok { background: #DCFCE7; color: #166534; } .ag-pill.no { background: #F1F5F9; color: #64748B; } .ag-pill.warn { background: #FEF3C7; color: #92400E; } .ag-pill.lk { background: #E0F2FE; color: #0369A1; } .ag-pill.cd { background: #FEF9C3; color: #854D0E; }
    .ag-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    .ag-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; }
    @media (max-width: 1200px) { .ag-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .ag-lc { grid-template-columns: minmax(0, 1fr); } .ag-flt .xl { margin-left: 0; } }
    @media (max-width: 560px) { .ag-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ag-k b { font-size: 20px; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content ag">
    <?= csrf_field() ?>
    <?php partial('Admin/_RefTabs'); ?>
    <h2><i class="fas fa-handshake" style="color:#7C3AED;"></i> এজেন্সি লিংক ও কোড</h2>
    <p class="sub">এই লিংক আর কোডটা স্থায়ী — যেকোনো এজেন্সিকে দিতে পারবেন। কেউ এই লিংক দিয়ে সাইনআপ করলে, অথবা সাইনআপের সময় "Referral Code" ঘরে কোডটা লিখলে — এখানে হিসাবে যোগ হবে। সে প্যাকেজ কিনলে সেটাও দেখাবে।</p>

    <div class="ag-card">
        <div class="ag-lc">
            <div class="ag-box"><small>এজেন্সি লিংক</small><div class="v"><?= e($link) ?></div>
                <div class="b"><button type="button" class="ag-btn" data-copy="<?= e($link) ?>"><i class="far fa-copy"></i> লিংক কপি</button>
                    <a class="ag-btn ghost" href="<?= e($link) ?>" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i> খুলে দেখুন</a></div></div>
            <div class="ag-box"><small>এজেন্সি কোড</small><div class="v code"><?= e($code) ?></div>
                <div class="b"><button type="button" class="ag-btn" data-copy="<?= e($code) ?>"><i class="far fa-copy"></i> কোড কপি</button></div></div>
        </div>
    </div>

    <div class="ag-card">
        <form class="ag-flt" method="get" action="/Admin/Partners">
            <div><label for="fFrom">শুরুর তারিখ</label><input type="date" id="fFrom" name="from" value="<?= e($from ?? '') ?>" max="<?= $today ?>" /></div>
            <div><label for="fTo">শেষের তারিখ</label><input type="date" id="fTo" name="to" value="<?= e($to ?? '') ?>" max="<?= $today ?>" /></div>
            <button type="submit" class="ag-btn"><i class="fas fa-magnifying-glass"></i> দেখুন</button>
            <a class="ag-btn ghost" href="/Admin/Partners">সব সময়</a>
            <a class="ag-btn xl" href="<?= e($xlsUrl) ?>"><i class="fas fa-file-excel"></i> Excel ডাউনলোড</a>
        </form>
        <div class="ag-quick"><?php foreach ($quick as $lbl => [$a, $b]): ?><a class="<?= $from === $a && $to === $b ? 'on' : '' ?>" href="/Admin/Partners?from=<?= $a ?>&to=<?= $b ?>"><?= $lbl ?></a><?php endforeach; ?></div>
        <p class="ag-range"><i class="far fa-calendar"></i> <?= $from || $to ? e(($from ? fmt_date($from, 'd M Y') : 'শুরু থেকে') . ' — ' . ($to ? fmt_date($to, 'd M Y') : 'আজ পর্যন্ত')) : 'শুরু থেকে আজ পর্যন্ত (সব সময়)' ?></p>
    </div>

    <div class="ag-kpis">
        <div class="ag-k" style="--c:#0EA5E9;"><small><i class="fas fa-arrow-pointer"></i> লিংকে ক্লিক</small><b><?= number_format($t['clicks']) ?></b><span><?= number_format($t['visitors']) ?> জন আলাদা মানুষ</span></div>
        <div class="ag-k" style="--c:#6366F1;"><small><i class="fas fa-user-plus"></i> সাইনআপ করেছে</small><b><?= number_format($t['signups']) ?> জন</b><span>লিংক ও কোড মিলিয়ে</span></div>
        <div class="ag-k" style="--c:#16A34A;"><small><i class="fas fa-bag-shopping"></i> প্যাকেজ কিনেছে</small><b><?= number_format($t['buyers']) ?> জন</b><span><?= number_format($t['orders']) ?>টা প্যাকেজ</span></div>
        <div class="ag-k" style="--c:#15803D;"><small><i class="fas fa-sack-dollar"></i> মোট বিক্রি</small><b>৳<?= number_format($t['revenue']) ?></b><span>Approve করা পেমেন্ট</span></div>
        <div class="ag-k" style="--c:#F59E0B;"><small><i class="fas fa-hourglass-half"></i> পেমেন্ট অপেক্ষমাণ</small><b><?= number_format($t['pending']) ?></b><span>Approve হলে "কিনেছে" তে যাবে</span></div>
    </div>

    <div class="ag-card">
        <h3><i class="fas fa-calendar-days" style="color:#6366F1;"></i> তারিখ অনুযায়ী — কত জন সাইনআপ করেছে, কত জন প্যাকেজ কিনেছে</h3>
        <?php if (!$R['daily']): ?>
            <div class="ag-empty">এই সময়ে এখনো কেউ আসেনি।</div>
        <?php else: ?>
        <div class="ag-tw">
            <table class="ag-tbl">
                <thead><tr><th>তারিখ</th><th>লিংকে ক্লিক</th><th>সাইনআপ</th><th>প্যাকেজ কিনেছে</th><th>বিক্রি</th><th>অপেক্ষমাণ</th></tr></thead>
                <tbody>
                <?php foreach ($R['daily'] as $d => $x): ?>
                    <tr><td><b><?= e(fmt_date($d, 'd M Y')) ?></b> <small style="color:#94A3B8;"><?= e(date('D', strtotime($d))) ?></small></td>
                        <td><?= number_format($x['clicks']) ?></td><td><b><?= number_format($x['signups']) ?> জন</b></td><td><b><?= number_format($x['buyers']) ?> জন</b></td>
                        <td class="ag-money">৳<?= number_format($x['revenue']) ?></td><td><?= number_format($x['pending']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><td>মোট</td><td><?= number_format($t['clicks']) ?></td><td><?= number_format($t['signups']) ?> জন</td><td><?= number_format($t['buyers']) ?> জন</td><td class="ag-money">৳<?= number_format($t['revenue']) ?></td><td><?= number_format($t['pending']) ?></td></tr></tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="ag-card">
        <h3><i class="fas fa-users" style="color:#16A34A;"></i> এই লিংক / কোড দিয়ে যারা সাইনআপ করেছে (<?= count($R['users']) ?>)</h3>
        <?php if (!$R['users']): ?>
            <div class="ag-empty">এই সময়ে কেউ সাইনআপ করেনি।</div>
        <?php else: ?>
        <div class="ag-tw">
            <table class="ag-tbl users">
                <thead><tr><th>#</th><th>ইউজার</th><th>ধরন</th><th>কিভাবে</th><th>সাইনআপের তারিখ</th><th>প্যাকেজ</th><th>মোট কিনেছে</th></tr></thead>
                <tbody>
                <?php foreach ($R['users'] as $i => $u): [$rl, $rc] = $roleLbl((string)$u->Role); ?>
                    <tr>
                        <td style="color:#94A3B8;font-weight:700;"><?= $i + 1 ?></td>
                        <td class="ag-who"><b><a href="/Admin/UserOverview/<?= (int)$u->UserId ?>"><?= e(!is_blank($u->FullName) ? $u->FullName : $u->UserName) ?></a></b><small>@<?= e($u->UserName) ?> · <?= e($u->Email) ?></small></td>
                        <td><span class="ag-pill <?= $rc ?>"><?= $rl ?></span></td>
                        <td><?= $u->Via === 'code' ? '<span class="ag-pill cd"><i class="fas fa-ticket"></i> কোড</span>' : '<span class="ag-pill lk"><i class="fas fa-link"></i> লিংক</span>' ?></td>
                        <td><?= e(fmt_date($u->SignedUp, 'd M Y')) ?><small style="display:block;color:#94A3B8;"><?= e(fmt_date($u->SignedUp, 'h:i A')) ?></small></td>
                        <td><?php if ($u->Orders > 0): ?><span class="ag-pill ok"><i class="fas fa-bag-shopping"></i> <?= e($u->LastPackage) ?><?= $u->Orders > 1 ? ' +' . ($u->Orders - 1) : '' ?></span>
                            <?php elseif ($u->Pending > 0): ?><span class="ag-pill warn">পেমেন্ট অপেক্ষমাণ</span>
                            <?php else: ?><span class="ag-pill no">এখনো কেনেনি</span><?php endif; ?></td>
                        <td style="font-weight:800;color:<?= $u->Paid > 0 ? '#15803D' : '#94A3B8' ?>;">৳<?= number_format($u->Paid) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="ag-toast" id="agToast"></div>
<script>
(function () {
    function toast(m) { var t = document.getElementById('agToast'); t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 2000); }
    document.addEventListener('click', function (e) {
        var c = e.target.closest('[data-copy]'); if (!c) return;
        var v = c.getAttribute('data-copy');
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(v).then(function () { toast('কপি হয়েছে ✓'); }); return; }
        var a = document.createElement('textarea'); a.value = v; document.body.appendChild(a); a.select(); try { document.execCommand('copy'); toast('কপি হয়েছে ✓'); } catch (x) { } a.remove();
    });
})();
</script>
