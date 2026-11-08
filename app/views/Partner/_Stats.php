<?php
/**
 * পার্টনার রিপোর্টের সংখ্যা — অ্যাডমিন (Admin/Partner) আর এজেন্সির রিপোর্ট লিংক (Partner/Report) দুই জায়গায় একই।
 * @var array $R   Db::GetPartnerReport() এর ফলাফল
 */
$t = $R['totals'];
$pct = fn(int $a, int $b) => $b > 0 ? round($a * 100 / $b, 1) . '%' : '—';
?>
<style>
    .pr-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .pr-k { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-top: 4px solid var(--c); min-width: 0; }
    .pr-k small { display: flex; align-items: center; gap: 6px; color: #64748B; font-size: 12px; font-weight: 700; }
    .pr-k small i { color: var(--c); }
    .pr-k b { display: block; font-size: 24px; color: #0F172A; line-height: 1.25; margin-top: 4px; }
    .pr-k span { display: block; font-size: 12px; color: #94A3B8; font-weight: 600; }
    .pr-funnel { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 13px; color: #475569; font-weight: 700; margin: -6px 0 18px; }
    .pr-funnel i { color: #CBD5E1; font-size: 11px; }
    .pr-funnel em { font-style: normal; background: #F1F5F9; border-radius: 999px; padding: 3px 10px; }
    .pr-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .pr-card h3 { margin: 0 0 12px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .pr-tw { overflow-x: auto; }
    .pr-tbl { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 760px; }
    .pr-tbl th { text-align: right; font-size: 11.5px; color: #64748B; text-transform: uppercase; padding: 9px 10px; border-bottom: 1px solid #E5ECF3; white-space: nowrap; }
    .pr-tbl th:first-child, .pr-tbl td:first-child { text-align: left; }
    .pr-tbl td { padding: 10px; border-bottom: 1px solid #F1F5F9; color: #334155; text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .pr-tbl tfoot td { font-weight: 800; color: #0F172A; border-top: 2px solid #E2E8F0; border-bottom: none; }
    .pr-src { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #0F172A; }
    .pr-src i { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; }
    .pr-money { color: #15803D; font-weight: 800; }
    .pr-bar { height: 6px; border-radius: 999px; background: #F1F5F9; margin-top: 5px; overflow: hidden; min-width: 80px; }
    .pr-bar > div { height: 100%; border-radius: 999px; }
    .pr-empty { text-align: center; color: #94A3B8; padding: 22px 0; }
    @media (max-width: 1200px) { .pr-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 560px) { .pr-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .pr-k b { font-size: 20px; } }
</style>

<div class="pr-kpis">
    <div class="pr-k" style="--c:#0EA5E9;"><small><i class="fas fa-arrow-pointer"></i> লিংকে ক্লিক</small><b><?= number_format($t['clicks']) ?></b><span><?= number_format($t['visitors']) ?> জন আলাদা ভিজিটর</span></div>
    <div class="pr-k" style="--c:#6366F1;"><small><i class="fas fa-user-plus"></i> সাইনআপ</small><b><?= number_format($t['signups']) ?></b><span>ক্লিক থেকে <?= $pct($t['signups'], $t['visitors']) ?></span></div>
    <div class="pr-k" style="--c:#14B8A6;"><small><i class="fas fa-envelope-circle-check"></i> ইমেইল ভেরিফাইড</small><b><?= number_format($t['verified']) ?></b><span>সাইনআপের <?= $pct($t['verified'], $t['signups']) ?></span></div>
    <div class="pr-k" style="--c:#16A34A;"><small><i class="fas fa-bag-shopping"></i> প্যাকেজ কিনেছে</small><b><?= number_format($t['buyers']) ?> জন</b><span>সাইনআপের <?= $pct($t['buyers'], $t['signups']) ?></span></div>
    <div class="pr-k" style="--c:#15803D;"><small><i class="fas fa-sack-dollar"></i> মোট বিক্রি</small><b>৳<?= number_format($t['revenue']) ?></b><span><?= number_format($t['orders']) ?>টা প্যাকেজ</span></div>
    <div class="pr-k" style="--c:#F59E0B;"><small><i class="fas fa-hourglass-half"></i> পেমেন্ট অপেক্ষমাণ</small><b><?= number_format($t['pending']) ?></b><span>Approve হলে "কিনেছে" তে যোগ হবে</span></div>
</div>

<div class="pr-card">
    <h3><i class="fas fa-share-nodes" style="color:#0EA5E9;"></i> কোন প্ল্যাটফর্ম থেকে কত জন</h3>
    <?php if (!$R['sources']): ?>
        <div class="pr-empty"><i class="fas fa-chart-simple"></i> এখনো কোনো ক্লিক বা সাইনআপ আসেনি।</div>
    <?php else: $maxC = max(1, ...array_values(array_map(fn($x) => $x['clicks'], $R['sources']))); ?>
    <div class="pr-tw">
        <table class="pr-tbl">
            <thead><tr><th>প্ল্যাটফর্ম</th><th>ক্লিক</th><th>আলাদা ভিজিটর</th><th>সাইনআপ</th><th>ভেরিফাইড</th><th>কিনেছে</th><th>বিক্রি</th><th>ক্লিক → সাইনআপ</th><th>সাইনআপ → কেনা</th></tr></thead>
            <tbody>
            <?php foreach ($R['sources'] as $s => $x): [$sn, $si, $sc] = Db::partnerSource((string)$s); ?>
                <tr>
                    <td><span class="pr-src"><i class="<?= e($si) ?>" style="background:<?= e($sc) ?>;"></i><?= e($sn) ?></span>
                        <?php if ($x['clicks'] > 0): ?><div class="pr-bar"><div style="width:<?= round($x['clicks'] * 100 / $maxC) ?>%;background:<?= e($sc) ?>;"></div></div><?php endif; ?></td>
                    <td><?= $s === 'code' ? '—' : number_format($x['clicks']) ?></td>
                    <td><?= $s === 'code' ? '—' : number_format($x['visitors']) ?></td>
                    <td><b><?= number_format($x['signups']) ?></b></td>
                    <td><?= number_format($x['verified']) ?></td>
                    <td><b><?= number_format($x['buyers']) ?></b></td>
                    <td class="pr-money">৳<?= number_format($x['revenue']) ?></td>
                    <td><?= $s === 'code' ? '—' : $pct($x['signups'], $x['visitors']) ?></td>
                    <td><?= $pct($x['buyers'], $x['signups']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><td>মোট</td><td><?= number_format($t['clicks']) ?></td><td><?= number_format($t['visitors']) ?></td><td><?= number_format($t['signups']) ?></td><td><?= number_format($t['verified']) ?></td>
                <td><?= number_format($t['buyers']) ?></td><td class="pr-money">৳<?= number_format($t['revenue']) ?></td><td><?= $pct($t['signups'], $t['visitors']) ?></td><td><?= $pct($t['buyers'], $t['signups']) ?></td></tr></tfoot>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="pr-card">
    <h3><i class="fas fa-calendar-days" style="color:#6366F1;"></i> দিন অনুযায়ী</h3>
    <?php if (!$R['daily']): ?>
        <div class="pr-empty">এই সময়ে কোনো হিসাব নেই।</div>
    <?php else: ?>
    <div class="pr-tw">
        <table class="pr-tbl" style="min-width:560px;">
            <thead><tr><th>তারিখ</th><th>ক্লিক</th><th>সাইনআপ</th><th>কিনেছে</th><th>বিক্রি</th><th>অপেক্ষমাণ</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($R['daily'], 0, 90, true) as $d => $x): ?>
                <tr><td><b><?= e(fmt_date($d, 'd M Y')) ?></b> <small style="color:#94A3B8;"><?= e(date('D', strtotime($d))) ?></small></td>
                    <td><?= number_format($x['clicks']) ?></td><td><?= number_format($x['signups']) ?></td><td><?= number_format($x['buyers']) ?></td>
                    <td class="pr-money">৳<?= number_format($x['revenue']) ?></td><td><?= number_format($x['pending']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
