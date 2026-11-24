<?php
/** Admin → Reference: ইউজাররা কার রেফারেন্সে / কোথা থেকে এসেছে — সাইনআপ আর প্যাকেজ কেনার সময় যা বেছেছে */
$ViewData['Title'] = 'Reference Report';
$R = $Model; $t = $R['totals']; $opts = $ViewBag->Options; $from = $ViewBag->From; $to = $ViewBag->To;
$db = db();
$today = date('Y-m-d');
$quick = ['আজ' => [$today, $today], 'গত ৭ দিন' => [date('Y-m-d', strtotime('-6 days')), $today], 'গত ৩০ দিন' => [date('Y-m-d', strtotime('-29 days')), $today],
    'এই মাস' => [date('Y-m-01'), $today], 'গত মাস' => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))]];
$maxS = max(1, ...array_values(array_map(fn($r) => $r['signups'], $R['rows'] ?: [['signups' => 0]])));
$maxB = max(1, ...array_values(array_map(fn($r) => $r['orders'], $R['rows'] ?: [['orders' => 0]])));
$chip = function (?string $k) use ($db) { $o = $db->ReferenceInfo($k); return '<span class="rf-chip"><i class="' . e($o['icon']) . '" style="color:' . e($o['color']) . ';"></i> ' . e($o['label']) . '</span>'; };
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .rf h2 { color: #08224A; margin: 0; font-size: 24px; }
    .rf .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 920px; }
    .rf-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; min-width: 0; }
    .rf-card h3 { margin: 0 0 12px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .rf-card .hint { color: #64748B; font-size: 13px; margin: -6px 0 12px; }
    .rf-flt { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
    .rf-flt label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .rf-flt input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 10px; font-size: 14px; font-family: inherit; }
    .rf-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13.5px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; white-space: nowrap; }
    .rf-btn.ghost { background: #F1F5F9; color: #334155; } .rf-btn.ok { background: #15803D; }
    .rf-quick { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
    .rf-quick a { padding: 5px 12px; border-radius: 999px; background: #EFF6FF; color: #1D4ED8; font-weight: 700; font-size: 12.5px; text-decoration: none; }
    .rf-quick a.on { background: #1D4ED8; color: #fff; }
    .rf-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .rf-k { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); }
    .rf-k small { display: block; color: #64748B; font-size: 12px; font-weight: 700; } .rf-k b { display: block; font-size: 24px; color: #0F172A; margin-top: 4px; }
    .rf-k span { display: block; font-size: 12px; color: #94A3B8; font-weight: 600; }
    .rf-tw { overflow-x: auto; }
    .rf-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 720px; }
    .rf-tbl th { text-align: right; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 10px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .rf-tbl td { padding: 11px 10px; border-bottom: 1px solid #F1F5F9; color: #334155; text-align: right; white-space: nowrap; vertical-align: middle; }
    .rf-tbl th:first-child, .rf-tbl td:first-child { text-align: left; white-space: normal; }
    .rf-tbl tfoot td { font-weight: 800; color: #0F172A; border-top: 2px solid #E2E8F0; border-bottom: none; }
    .rf-src { display: inline-flex; align-items: center; gap: 9px; font-weight: 800; color: #0F172A; }
    .rf-src i { width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; flex-shrink: 0; }
    .rf-bar { display: flex; align-items: center; gap: 8px; justify-content: flex-end; }
    .rf-bar span { display: inline-block; height: 7px; border-radius: 999px; background: var(--c); min-width: 2px; }
    .rf-money { color: #15803D; font-weight: 800; }
    .rf-notes { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 6px; }
    .rf-notes span { font-size: 11.5px; background: #F1F5F9; color: #475569; border-radius: 999px; padding: 2px 9px; font-weight: 600; }
    .rf-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .rf-list { display: flex; flex-direction: column; max-height: 460px; overflow-y: auto; }
    .rf-li { display: flex; align-items: center; gap: 10px; padding: 9px 2px; border-bottom: 1px solid #F1F5F9; font-size: 13.5px; }
    .rf-li .w { flex: 1; min-width: 0; } .rf-li .w b { display: block; color: #0F172A; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .rf-li .w small { color: #94A3B8; font-size: 12px; }
    .rf-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 999px; padding: 3px 10px; white-space: nowrap; color: #334155; }
    .rf-opts { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .rf-opt { display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #E2E8F0; border-radius: 999px; padding: 6px 12px; font-size: 13px; font-weight: 700; color: #334155; cursor: pointer; user-select: none; background: #fff; }
    .rf-opt input { width: 16px; height: 16px; accent-color: #15803D; margin: 0; }
    .rf-opt.off { opacity: .55; text-decoration: line-through; }
    .rf-opt .x { border: none; background: #FEE2E2; color: #B91C1C; width: 20px; height: 20px; border-radius: 50%; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
    .rf-add { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .rf-add input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; min-width: 240px; flex: 0 1 320px; }
    #rfMsg { font-size: 13.5px; font-weight: 600; } #rfMsg.ok { color: #15803D; } #rfMsg.err { color: #B91C1C; }
    .rf-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    @media (max-width: 1200px) { .rf-two { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .rf-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .rf-add input { min-width: 0; flex: 1 1 100%; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content rf">
    <?= csrf_field() ?>
    <?php partial('Admin/_RefTabs'); ?>
    <h2><i class="fas fa-bullhorn" style="color:#0EA5E9;"></i> রেফারেন্স রিপোর্ট</h2>
    <p class="sub">সাইনআপ করার সময় আর প্যাকেজ কেনার সময় ইউজার বেছে নেয় সে কার রেফারেন্সে / কোথা থেকে জেনে এসেছে (Facebook, LinkedIn, Instagram, YouTube ...)। এখানে দেখবেন কোনটা থেকে কত জন সাইনআপ করেছে আর কত জন প্যাকেজ কিনেছে।</p>

    <div class="rf-card">
        <form class="rf-flt" method="get" action="/Admin/References">
            <div><label for="fFrom">শুরু</label><input type="date" id="fFrom" name="from" value="<?= e($from ?? '') ?>" max="<?= $today ?>" /></div>
            <div><label for="fTo">শেষ</label><input type="date" id="fTo" name="to" value="<?= e($to ?? '') ?>" max="<?= $today ?>" /></div>
            <button type="submit" class="rf-btn"><i class="fas fa-magnifying-glass"></i> দেখুন</button>
            <a class="rf-btn ghost" href="/Admin/References">সব সময়</a>
        </form>
        <div class="rf-quick"><?php foreach ($quick as $lbl => [$a, $b]): ?><a class="<?= $from === $a && $to === $b ? 'on' : '' ?>" href="/Admin/References?from=<?= $a ?>&to=<?= $b ?>"><?= $lbl ?></a><?php endforeach; ?></div>
    </div>

    <div class="rf-kpis">
        <div class="rf-k" style="--c:#6366F1;"><small>সাইনআপ</small><b><?= number_format($t['signups']) ?></b><span>নতুন অ্যাকাউন্ট</span></div>
        <div class="rf-k" style="--c:#16A34A;"><small>প্যাকেজ কিনেছে</small><b><?= number_format($t['buyers']) ?> জন</b><span><?= number_format($t['orders']) ?>টা প্যাকেজ</span></div>
        <div class="rf-k" style="--c:#15803D;"><small>মোট বিক্রি</small><b>৳<?= number_format($t['revenue']) ?></b><span>Approve করা পেমেন্ট</span></div>
        <div class="rf-k" style="--c:#F59E0B;"><small>পেমেন্ট অপেক্ষমাণ</small><b><?= number_format($t['pending']) ?></b><span>Approve হলে "কিনেছে" তে যাবে</span></div>
    </div>

    <div class="rf-card">
        <h3><i class="fas fa-chart-simple" style="color:#0EA5E9;"></i> কোন রেফারেন্স থেকে কত জন</h3>
        <?php if (!$R['rows']): ?>
            <div class="rf-empty">এই সময়ে কোনো সাইনআপ বা পেমেন্ট নেই।</div>
        <?php else: ?>
        <div class="rf-tw">
            <table class="rf-tbl">
                <thead><tr><th>রেফারেন্স</th><th>সাইনআপ</th><th>প্যাকেজ কিনেছে</th><th>প্যাকেজ</th><th>বিক্রি</th><th>অপেক্ষমাণ</th></tr></thead>
                <tbody>
                <?php foreach ($R['rows'] as $k => $r): $o = $db->ReferenceInfo((string)$k); ?>
                    <tr>
                        <td><span class="rf-src"><i class="<?= e($o['icon']) ?>" style="background:<?= e($o['color']) ?>;"></i><?= e($o['label']) ?><?= $k === '' ? ' <small style="color:#94A3B8;font-weight:600;">(এই অপশন চালুর আগের)</small>' : '' ?></span>
                            <?php if ($r['notes']): ?><div class="rf-notes"><?php foreach ($r['notes'] as $n => $c): ?><span><?= e((string)$n) ?><?= $c > 1 ? ' ×' . $c : '' ?></span><?php endforeach; ?></div><?php endif; ?></td>
                        <td><div class="rf-bar"><span style="--c:#6366F1;width:<?= round($r['signups'] * 70 / $maxS) ?>px;"></span><b><?= number_format($r['signups']) ?></b></div></td>
                        <td><div class="rf-bar"><span style="--c:#16A34A;width:<?= round($r['orders'] * 70 / $maxB) ?>px;"></span><b><?= number_format($r['buyers']) ?> জন</b></div></td>
                        <td><?= number_format($r['orders']) ?></td>
                        <td class="rf-money">৳<?= number_format($r['revenue']) ?></td>
                        <td><?= number_format($r['pending']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><td>মোট</td><td><?= number_format($t['signups']) ?></td><td><?= number_format($t['buyers']) ?> জন</td><td><?= number_format($t['orders']) ?></td><td class="rf-money">৳<?= number_format($t['revenue']) ?></td><td><?= number_format($t['pending']) ?></td></tr></tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="rf-two">
        <div class="rf-card">
            <h3><i class="fas fa-user-plus" style="color:#6366F1;"></i> সাম্প্রতিক সাইনআপ</h3>
            <?php if (!$R['recent']): ?><div class="rf-empty">কেউ নেই।</div><?php else: ?>
            <div class="rf-list">
                <?php foreach ($R['recent'] as $u): ?>
                    <div class="rf-li"><div class="w"><b><?= e(!is_blank($u->FullName) ? $u->FullName : $u->UserName) ?></b><small><?= $u->Role === 'Teacher' ? 'Teacher' : 'Student' ?> · <?= e(fmt_date($u->CreatedAt, 'd M Y, h:i A')) ?><?= $u->HowHeard === 'other' && !is_blank($u->HowHeardNote) ? ' · ' . e($u->HowHeardNote) : '' ?></small></div><?= $chip($u->HowHeard) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="rf-card">
            <h3><i class="fas fa-bag-shopping" style="color:#16A34A;"></i> সাম্প্রতিক প্যাকেজ কেনা</h3>
            <?php if (!$R['buys']): ?><div class="rf-empty">কেউ নেই।</div><?php else: ?>
            <div class="rf-list">
                <?php foreach ($R['buys'] as $p): ?>
                    <div class="rf-li"><div class="w"><b><?= e(!is_blank($p->FullName) ? $p->FullName : (string)$p->UserName) ?></b><small><?= e($p->PackageName) ?> · <b style="display:inline;color:#15803D;">৳<?= number_format((int)$p->Amount) ?></b> · <?= e(fmt_date($p->When, 'd M Y')) ?><?= $p->Reference === 'other' && !is_blank($p->ReferenceNote) ? ' · ' . e($p->ReferenceNote) : '' ?></small></div><?= $chip($p->Reference) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="rf-card">
        <h3><i class="fas fa-list-check" style="color:#F59E0B;"></i> সাইনআপ / প্যাকেজ পেজে কোন কোন অপশন দেখাবে</h3>
        <p class="hint">টিক তুলে দিলে ঐ অপশন আর দেখাবে না (পুরোনো হিসাব থাকবে)। নতুন অপশনও যোগ করতে পারবেন। "অন্যান্য" সবসময় থাকে — বাছাই করলে ইউজার নিজে লিখে দেয়।</p>
        <div class="rf-opts" id="rfOpts">
            <?php foreach ($opts as $k => $o): ?>
                <label class="rf-opt<?= $o['on'] ? '' : ' off' ?>">
                    <input type="checkbox" value="<?= e($k) ?>"<?= $o['on'] ? ' checked' : '' ?><?= $k === 'other' ? ' disabled' : '' ?> />
                    <i class="<?= e($o['icon']) ?>" style="color:<?= e($o['color']) ?>;"></i> <?= e($o['label']) ?>
                    <?php if ($o['custom']): ?><button type="button" class="x" data-remove="<?= e($k) ?>" title="মুছুন"><i class="fas fa-xmark"></i></button><?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="rf-add">
            <input type="text" id="rfAdd" maxlength="40" placeholder="নতুন অপশন — যেমন: সেমিনার, কোচিং সেন্টার, Twitter" />
            <button type="button" class="rf-btn" id="rfAddBtn"><i class="fas fa-plus"></i> যোগ করুন</button>
            <button type="button" class="rf-btn ok" id="rfSave"><i class="fas fa-floppy-disk"></i> চালু/বন্ধ সেভ করুন</button>
            <span id="rfMsg"></span>
        </div>
    </div>
</div>
<script>
(function () {
    var tok = document.querySelector('input[name="__RequestVerificationToken"]').value, msg = document.getElementById('rfMsg');
    async function post(data) {
        var fd = new FormData(); fd.append('__RequestVerificationToken', tok);
        Object.keys(data).forEach(function (k) { [].concat(data[k]).forEach(function (v) { fd.append(k, v); }); });
        try { return await (await fetch('/Admin/SaveReferenceOptions', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); }
        catch (e) { return { success: false, message: 'সার্ভারে সমস্যা — আবার চেষ্টা করুন।' }; }
    }
    function off() { return [].slice.call(document.querySelectorAll('#rfOpts input:not(:checked):not(:disabled)')).map(function (i) { return i.value; }); }
    function done(d) { msg.className = d.success ? 'ok' : 'err'; msg.textContent = d.message || ''; if (d.success) setTimeout(function () { location.reload(); }, 600); }
    document.getElementById('rfOpts').addEventListener('change', function (e) { var l = e.target.closest('.rf-opt'); if (l) l.classList.toggle('off', !e.target.checked); });
    document.getElementById('rfSave').addEventListener('click', async function () { var o = off(); done(await post(o.length ? { 'off[]': o } : { 'off[]': '' })); });
    document.getElementById('rfAddBtn').addEventListener('click', async function () {
        var v = document.getElementById('rfAdd').value.trim(); if (!v) { msg.className = 'err'; msg.textContent = 'নতুন অপশনের নাম লিখুন।'; return; }
        done(await post({ add: v }));
    });
    document.getElementById('rfAdd').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); document.getElementById('rfAddBtn').click(); } });
    document.getElementById('rfOpts').addEventListener('click', async function (e) {
        var x = e.target.closest('[data-remove]'); if (!x) return; e.preventDefault();
        if (!confirm('এই অপশনটা মুছে ফেলবেন? (আগে যারা বেছেছে তাদের হিসাব থাকবে)')) return;
        done(await post({ remove: x.getAttribute('data-remove') }));
    });
})();
</script>
