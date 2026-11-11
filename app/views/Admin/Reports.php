<?php
/** Admin → Reports: সাইটের সব রিপোর্ট — ট্যাব, তারিখ, খোঁজা; Excel (CSV) আর PDF (প্রিন্ট) */
$R = $Model; $V = $ViewBag;
$ViewData['Title'] = 'Reports — ' . $R['title'];
$type = (string)$V->Type; $from = (string)$V->From; $to = (string)$V->To; $q = (string)$V->Q;
$range = $from !== '' || $to !== '' ? (($from !== '' ? fmt_date($from, 'd M Y') : 'শুরু') . ' — ' . ($to !== '' ? fmt_date($to, 'd M Y') : 'আজ')) : 'সব সময়';
$print = !empty($V->Print);
$nameCols = ['name' => '_uid', 'name2' => '_uid2'];
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .rp h2 { color: #08224A; margin: 0; font-size: 24px; }
    .rp .sub { color: #64748B; margin: 4px 0 16px; font-size: 14px; }
    .rp-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .rp-tabs a { display: inline-flex; align-items: center; gap: 7px; padding: 9px 14px; border-radius: 999px; background: #fff; border: 1px solid #DCE7F0; color: #334155; font-weight: 700; font-size: 13.5px; text-decoration: none; }
    .rp-tabs a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .rp-bar { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 14px; display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 14px; }
    .rp-bar label { display: block; font-size: 12px; font-weight: 800; color: #64748B; margin-bottom: 4px; }
    .rp-bar input { height: 42px; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 14px; background: #fff; }
    .rp-bar .grow { flex: 1; min-width: 220px; }
    .rp-bar .grow input { width: 100%; }
    .rp-btn { height: 42px; padding: 0 16px; border-radius: 10px; border: none; font-weight: 800; font-size: 13.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; font-family: inherit; white-space: nowrap; }
    .rp-btn.go { background: #08224A; color: #fff; } .rp-btn.clr { background: #F1F5F9; color: #334155; }
    .rp-btn.xl { background: #16A34A; color: #fff; } .rp-btn.pdf { background: #DC2626; color: #fff; }
    .rp-btn:hover { opacity: .92; color: #fff; } .rp-btn.clr:hover { color: #334155; }
    .rp-quick { display: flex; gap: 6px; flex-wrap: wrap; margin: -4px 0 14px; }
    .rp-quick a { font-size: 12.5px; font-weight: 700; color: #0369A1; background: #E0F2FE; border-radius: 999px; padding: 4px 11px; text-decoration: none; }
    .rp-tot { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 10px; margin-bottom: 14px; }
    .rp-tot div { background: #fff; border-radius: 14px; padding: 12px 14px; box-shadow: 0 4px 16px rgba(0,0,0,.05); }
    .rp-tot small { display: block; font-size: 12px; font-weight: 700; color: #64748B; }
    .rp-tot b { font-size: 20px; color: #08224A; }
    .rp-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 16px; margin-bottom: 16px; }
    .rp-card h3 { margin: 0 0 10px; font-size: 16px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .rp-card h3 small { font-weight: 600; color: #64748B; font-size: 12.5px; }
    .rp-tw { overflow: auto; max-height: 70vh; }
    .rp-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rp-tbl th { position: sticky; top: 0; background: #F8FAFC; text-align: left; font-size: 11.5px; color: #475569; padding: 9px 8px; border-bottom: 1px solid #E2E8F0; white-space: nowrap; cursor: pointer; user-select: none; z-index: 1; }
    .rp-tbl th:hover { color: #08224A; }
    .rp-tbl td { padding: 8px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: top; }
    .rp-tbl tr:hover td { background: #FAFCFF; }
    .rp-tbl td.n { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .rp-tbl a.pl { color: #0F4C5C; font-weight: 800; text-decoration: none; }
    .rp-tbl a.pl:hover { color: #0369A1; text-decoration: underline; }
    .rp-empty { text-align: center; color: #94A3B8; padding: 26px 0; }
    .rp-count { font-size: 12.5px; color: #64748B; font-weight: 700; }
    .rp-mon { display: grid; gap: 6px; }
    .rp-mon div { display: grid; grid-template-columns: 80px 1fr 90px; gap: 10px; align-items: center; font-size: 13px; }
    .rp-mon i { display: block; height: 10px; border-radius: 99px; background: linear-gradient(90deg, #22C55E, #16A34A); }
    .rp-mon span { text-align: right; font-weight: 800; color: #0F172A; }
    .rp-ph { display: none; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } }
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        body * { visibility: hidden !important; }
        .rp, .rp * { visibility: visible !important; }
        .rp { position: absolute; left: 0; top: 0; width: 100%; margin: 0 !important; padding: 0 !important; }
        .rp-noprint, .rp-tabs, .rp-bar, .rp-quick { display: none !important; }
        .rp-ph { display: block; margin-bottom: 8px; }
        .rp-card, .rp-tot div { box-shadow: none; border: 1px solid #E2E8F0; }
        .rp-tw { max-height: none; overflow: visible; }
        .rp-tbl { font-size: 8.5px; } .rp-tbl th { position: static; white-space: normal; } .rp-tbl th i { display: none; } .rp-tbl td { padding: 4px; }
        .rp-tbl tr { page-break-inside: avoid; }
        .rp-tbl a.pl { color: #000; text-decoration: none; }
    }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content rp">
    <div class="rp-ph"><h2 style="font-size:18px;">মাষ্টার মশাই — <?= e($R['title']) ?> রিপোর্ট</h2>
        <div style="font-size:11px;color:#475569;">সময়: <?= e($range) ?><?= $q !== '' ? ' · খোঁজ: “' . e($q) . '”' : '' ?> · তৈরি: <?= e(date('d M Y, h:i A')) ?></div></div>

    <div class="rp-noprint">
        <h2><i class="fas fa-file-lines" style="color:#0EA5E9;"></i> Reports</h2>
        <p class="sub">সাইটের সব হিসাব এক জায়গায় — রিপোর্ট বাছাই করুন, তারিখ দিন বা নাম / ইউজারনেম / ইমেইল দিয়ে খুঁজুন, তারপর Excel বা PDF নামিয়ে নিন। নামে ক্লিক করলে প্রোফাইল খুলবে।</p>
    </div>

    <nav class="rp-tabs">
        <?php foreach (REPORT_TYPES as $k => [$lbl, $ic]): ?>
            <a href="<?= e('/Admin/Reports?' . http_build_query(array_filter(['type' => $k, 'from' => $from, 'to' => $to, 'q' => $q], fn($x) => $x !== ''))) ?>" class="<?= $k === $type ? 'on' : '' ?>"><i class="fas <?= $ic ?>"></i> <?= e($lbl) ?></a>
        <?php endforeach; ?>
    </nav>

    <form class="rp-bar" method="get" action="/Admin/Reports" id="rpForm">
        <input type="hidden" name="type" value="<?= e($type) ?>" />
        <div><label for="rpFrom">শুরুর তারিখ</label><input type="date" id="rpFrom" name="from" value="<?= e($from) ?>" /></div>
        <div><label for="rpTo">শেষ তারিখ</label><input type="date" id="rpTo" name="to" value="<?= e($to) ?>" /></div>
        <?php if ($type !== 'summary'): ?><div class="grow"><label for="rpQ">খুঁজুন</label><input type="search" id="rpQ" name="q" value="<?= e($q) ?>" placeholder="নাম, ইউজারনেম, ইমেইল বা মোবাইল" autocomplete="off" /></div><?php endif; ?>
        <button type="submit" class="rp-btn go"><i class="fas fa-filter"></i> দেখুন</button>
        <?php if ($from !== '' || $to !== '' || $q !== ''): ?><a class="rp-btn clr" href="/Admin/Reports?type=<?= e($type) ?>"><i class="fas fa-xmark"></i> সব</a><?php endif; ?>
        <a class="rp-btn xl" id="rpXl" href="<?= e('/Admin/Reports?' . http_build_query(array_filter(['type' => $type, 'from' => $from, 'to' => $to, 'q' => $q, 'export' => 'csv'], fn($x) => $x !== ''))) ?>"><i class="fas fa-file-excel"></i> Excel</a>
        <button type="button" class="rp-btn pdf" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF</button>
    </form>
    <div class="rp-quick">
        <?php $d = fn($s) => date('Y-m-d', strtotime($s)); $ql = fn($f, $t, $l) => '<a href="' . e('/Admin/Reports?' . http_build_query(array_filter(['type' => $type, 'from' => $f, 'to' => $t, 'q' => $q], fn($x) => $x !== ''))) . '">' . $l . '</a>'; ?>
        <?= $ql($d('today'), $d('today'), 'আজ') ?><?= $ql($d('-6 days'), $d('today'), 'শেষ ৭ দিন') ?><?= $ql($d('first day of this month'), $d('today'), 'এই মাস') ?>
        <?= $ql($d('first day of last month'), $d('last day of last month'), 'গত মাস') ?><?= $ql($d('-29 days'), $d('today'), 'শেষ ৩০ দিন') ?><?= $ql(date('Y') . '-01-01', $d('today'), 'এই বছর') ?><?= $ql('', '', 'সব সময়') ?>
    </div>

    <div class="rp-tot">
        <?php foreach ($R['totals'] as $k => $v): ?><div><small><?= e($k) ?></small><b><?= e(is_int($v) ? number_format($v) : (string)$v) ?></b></div><?php endforeach; ?>
    </div>

    <?php if ($type === 'summary' && !empty($R['months'])): $mx = max(1, max($R['months'])); ?>
        <div class="rp-card">
            <h3><i class="fas fa-chart-column" style="color:#16A34A;"></i> মাস অনুযায়ী আয় <small>(Accepted পেমেন্ট)</small></h3>
            <div class="rp-mon">
                <?php foreach ($R['months'] as $m => $amt): ?><div><b><?= e(date('M Y', strtotime($m . '-01'))) ?></b><i style="width:<?= max(2, round($amt / $mx * 100)) ?>%;"></i><span>৳<?= number_format($amt) ?></span></div><?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="rp-card">
        <h3><i class="fas <?= REPORT_TYPES[$type][1] ?>" style="color:#0EA5E9;"></i> <?= e($type === 'summary' ? 'প্যাকেজ অনুযায়ী বিক্রি' : $R['title']) ?>
            <small><?= e($range) ?></small><span class="rp-count" id="rpCount"><?= count($R['rows']) ?> টি সারি</span></h3>
        <?php if (!$R['rows']): ?>
            <div class="rp-empty">কিছু পাওয়া যায়নি।</div>
        <?php else: ?>
        <div class="rp-tw"><table class="rp-tbl" id="rpT">
            <thead><tr><?php foreach ($R['cols'] as $k => $lbl): ?><th data-k="<?= e($k) ?>" title="সাজাতে চাপুন"><?= e($lbl) ?> <i class="fas fa-sort" style="opacity:.35;"></i></th><?php endforeach; ?></tr></thead>
            <tbody>
            <?php foreach ($R['rows'] as $r): ?>
                <tr>
                    <?php foreach ($R['cols'] as $k => $lbl): $v = $r[$k] ?? ''; $num = is_int($v); ?>
                        <td class="<?= $num ? 'n' : '' ?>" data-v="<?= e((string)$v) ?>"><?php
                            if (isset($nameCols[$k]) && !empty($r[$nameCols[$k]])): ?><a class="pl" href="/Profile/Show/<?= (int)$r[$nameCols[$k]] ?>" target="_blank" rel="noopener" title="প্রোফাইল দেখুন"><?= e((string)$v) ?></a><?php
                            else: ?><?= e($num ? number_format($v) : (string)$v) ?><?php endif; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var t = document.getElementById('rpT'), q = document.getElementById('rpQ'), cnt = document.getElementById('rpCount');
    if (!t) return;
    var rows = Array.prototype.slice.call(t.tBodies[0].rows);
    // লিখতে লিখতে টেবিলে খোঁজা (Enter চাপলে সার্ভার থেকে খোঁজে — Excel / PDF এও সেই খোঁজ থাকে)
    if (q) q.addEventListener('input', function () {
        var ws = q.value.trim().toLowerCase().replace(/^@/, '').split(/\s+/).filter(Boolean), n = 0;
        rows.forEach(function (r) { var s = r.textContent.toLowerCase(); var ok = ws.every(function (w) { return s.indexOf(w) !== -1; }); r.style.display = ok ? '' : 'none'; if (ok) n++; });
        cnt.textContent = n + ' টি সারি';
    });
    // কলামে চাপলে সাজানো
    t.tHead.addEventListener('click', function (e) {
        var th = e.target.closest('th'); if (!th) return;
        var i = th.cellIndex, asc = th.getAttribute('data-dir') !== 'asc';
        t.tHead.querySelectorAll('th').forEach(function (x) { x.removeAttribute('data-dir'); });
        th.setAttribute('data-dir', asc ? 'asc' : 'desc');
        var val = function (r) { var v = r.cells[i].getAttribute('data-v') || ''; var n = parseFloat(v.replace(/[^0-9.\-]/g, '')); return /^-?[\d.,]+$/.test(v) ? n : v.toLowerCase(); };
        rows.sort(function (a, b) { var x = val(a), y = val(b); return (x > y ? 1 : x < y ? -1 : 0) * (asc ? 1 : -1); });
        rows.forEach(function (r) { t.tBodies[0].appendChild(r); });
    });
})();
</script>
<?php section_end(); ?>
