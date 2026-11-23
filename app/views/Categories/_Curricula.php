<?php
/**
 * শিক্ষাব্যবস্থা অনুযায়ী টিউটর — জাতীয় শিক্ষাক্রম (বাংলা / ইংলিশ ভার্সন), ব্রিটিশ কারিকুলাম (ইংলিশ মিডিয়াম), মাদ্রাসা
 * দরকার: $cur (tf_curricula() + সংখ্যা)
 */
$n = fn($x) => isset($x) ? bn_num((int)$x) : '';
?>
<style>
    .cu-sec { margin: 26px 0 8px; }
    .cu-h { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
    .cu-h h2 { margin: 0; font-family: var(--font-heading); font-size: clamp(21px, 2.3vw, 28px); font-weight: 700; color: #08224A; }
    .cu-h p { margin: 4px 0 0; color: #64748B; font-size: 14.5px; }
    .cu-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .cu-card { background: #fff; border-radius: 18px; border: 1px solid #E8EEF4; box-shadow: 0 6px 22px rgba(8, 34, 74, .05); overflow: hidden; display: flex; flex-direction: column; }
    .cu-top { display: flex; gap: 12px; align-items: flex-start; padding: 16px 18px 12px; }
    .cu-ic { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .cu-top h3 { margin: 0; font-size: 17px; font-weight: 800; color: #0F172A; line-height: 1.35; }
    .cu-top small { display: block; color: #64748B; font-size: 12.5px; margin-top: 2px; }
    .cu-badge { margin-left: auto; flex-shrink: 0; font-size: 11.5px; font-weight: 800; padding: 4px 10px; border-radius: 999px; white-space: nowrap; }
    .cu-note { margin: 0 18px 10px; font-size: 13px; color: #475569; line-height: 1.6; }
    .cu-steps { list-style: none; margin: 0; padding: 4px 18px 8px; position: relative; }
    .cu-steps li { position: relative; padding-left: 26px; }
    .cu-steps li::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: var(--cc); opacity: .25; }
    .cu-steps li:first-child::before { top: 50%; } .cu-steps li:last-child::before { bottom: 50%; }
    .cu-steps li::after { content: ''; position: absolute; left: 3px; top: 50%; width: 12px; height: 12px; margin-top: -6px; border-radius: 50%; background: #fff; border: 3px solid var(--cc); }
    .cu-steps a { display: flex; align-items: center; gap: 10px; padding: 9px 12px; margin: 3px 0; border-radius: 12px; text-decoration: none; color: #0F172A; transition: background .15s; }
    .cu-steps a:hover { background: var(--cb); }
    .cu-steps a b { font-size: 14.5px; font-weight: 800; }
    .cu-steps a span { color: #64748B; font-size: 12.5px; }
    .cu-steps a em { margin-left: auto; font-style: normal; font-size: 12px; font-weight: 700; color: var(--cc); background: var(--cb); border-radius: 999px; padding: 2px 10px; white-space: nowrap; }
    .cu-steps a em.z { color: #94A3B8; background: #F1F5F9; }
    .cu-steps a i.go { color: #CBD5E1; font-size: 12px; }
    .cu-subs { display: flex; flex-wrap: wrap; gap: 6px; padding: 2px 18px 12px; }
    .cu-subs a { font-size: 12.5px; font-weight: 700; text-decoration: none; color: var(--cc); background: var(--cb); border-radius: 999px; padding: 5px 11px; }
    .cu-foot { margin-top: auto; padding: 10px 18px 14px; border-top: 1px solid #F1F5F9; }
    .cu-foot a { display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; font-weight: 800; font-size: 14px; color: #fff; background: var(--cc); border-radius: 12px; padding: 10px; }
    .cu-eq { margin-top: 16px; background: #fff; border-radius: 16px; border: 1px dashed #CBD5E1; padding: 14px 16px; }
    .cu-eq h4 { margin: 0 0 10px; font-size: 15px; font-weight: 800; color: #08224A; }
    .cu-eq table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .cu-eq th, .cu-eq td { padding: 8px 6px; border-bottom: 1px solid #F1F5F9; text-align: center; }
    .cu-eq th { color: #475569; font-size: 12.5px; }
    .cu-eq td:first-child, .cu-eq th:first-child { text-align: left; font-weight: 700; color: #0F172A; }
    @media (max-width: 860px) { .cu-grid { grid-template-columns: 1fr; } }
    @media (max-width: 520px) { .cu-eq { overflow-x: auto; } .cu-eq table { min-width: 460px; } .cu-badge { display: none; } }
</style>
<section class="cu-sec" id="curriculum">
    <div class="cu-h">
        <div>
            <h2>🎓 শিক্ষাব্যবস্থা অনুযায়ী টিউটর খুঁজুন</h2>
            <p>বাংলাদেশের পড়াশোনার ধাপ মিলিয়ে বাছাই করুন — জাতীয় শিক্ষাক্রম, ব্রিটিশ কারিকুলাম বা মাদ্রাসা।</p>
        </div>
    </div>
    <div class="cu-grid">
        <?php foreach ($cur as $k => $c): ?>
            <div class="cu-card" style="--cc:<?= e($c['color']) ?>;--cb:<?= e($c['bg']) ?>;">
                <div class="cu-top">
                    <span class="cu-ic" style="background:<?= e($c['bg']) ?>;color:<?= e($c['color']) ?>;"><i class="fas <?= e($c['icon']) ?>"></i></span>
                    <div><h3><?= e($c['title']) ?></h3><small><?= e($c['en']) ?></small></div>
                    <span class="cu-badge" style="background:<?= e($c['bg']) ?>;color:<?= e($c['color']) ?>;"><?= e($c['badge']) ?></span>
                </div>
                <p class="cu-note"><?= e($c['note']) ?></p>
                <ul class="cu-steps">
                    <?php foreach ($c['stages'] as $st): $cnt = (int)($st[3] ?? 0); ?>
                        <li><a href="<?= e(tf_link($st[2])) ?>"><div><b><?= e($st[0]) ?></b><br /><span><?= e($st[1]) ?></span></div>
                            <em class="<?= $cnt ? '' : 'z' ?>"><?= $cnt ? bn_num($cnt) . ' জন টিউটর' : 'শীঘ্রই' ?></em><i class="fas fa-chevron-right go"></i></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($c['subjects']): ?>
                    <div class="cu-subs"><?php foreach ($c['subjects'] as [$sname, $scnt]): ?><a href="<?= e(tf_link(['med' => $c['stages'][0][2]['med'] ?? [], 'sub' => [$sname]])) ?>"><?= e($sname) ?></a><?php endforeach; ?></div>
                <?php endif; ?>
                <div class="cu-foot"><a href="<?= e(tf_link(['med' => $c['stages'][0][2]['med'] ?? []])) ?>"><i class="fas fa-magnifying-glass"></i> সব <?= e($c['short']) ?> টিউটর দেখুন</a></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cu-eq">
        <h4><i class="fas fa-scale-balanced" style="color:#0284C7;"></i> কোনটা কোনটার সমান?</h4>
        <table>
            <tr><th>ধাপ</th><th>বাংলা মিডিয়াম / ইংলিশ ভার্সন</th><th>ইংলিশ মিডিয়াম</th><th>মাদ্রাসা</th></tr>
            <tr><td>প্রাথমিক</td><td>ক্লাস ১ – ৫</td><td>Primary (Class 1 – 5)</td><td>ইবতেদায়ি</td></tr>
            <tr><td>মাধ্যমিক</td><td>SSC</td><td>O Level</td><td>দাখিল</td></tr>
            <tr><td>উচ্চ মাধ্যমিক</td><td>HSC</td><td>A Level</td><td>আলিম</td></tr>
        </table>
    </div>
</section>
