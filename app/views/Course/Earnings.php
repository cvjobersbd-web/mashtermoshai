<?php
/** টিচার → কোর্সের আয়: প্রতিটা বিক্রি, নিজের ভাগ, অ্যাডমিনের ভাগ, পাঠানো টাকা, বাকি */
$E = $Model; $pct = (int)$ViewBag->Pct;
$ViewData['Title'] = 'কোর্সের আয়';
$ViewData['Robots'] = 'noindex, nofollow';
?>
<?php partial('Course/_Styles'); ?>
<div class="cx-page">
    <div class="cx-wrap">
        <a class="cx-back" href="/Course/Manage"><i class="fas fa-arrow-left"></i> আমার কোর্স</a>
        <h1 class="cx-h1"><i class="fas fa-sack-dollar" style="color:#16A34A;"></i> কোর্সের আয়</h1>
        <p class="cx-sub" style="margin-bottom:18px;">প্রতিটা বিক্রি থেকে আপনার ভাগ <b><?= 100 - $pct ?>%</b>, মাষ্টার মশাই <b><?= $pct ?>%</b> (বিক্রির সময়ের হার অনুযায়ী)। পাওনা টাকা অ্যাডমিন পাঠালে এখানে "পাঠানো" তে যোগ হবে আর আপনি নোটিফিকেশন পাবেন।</p>
        <div class="cx-stats">
            <div class="cx-stat" style="--c:#0EA5E9;"><small>মোট বিক্রি (<?= (int)$E['count'] ?>টা)</small><b><?= e(taka($E['gross'])) ?></b></div>
            <div class="cx-stat" style="--c:#16A34A;"><small>আপনার ভাগ</small><b><?= e(taka($E['teacher'])) ?></b></div>
            <div class="cx-stat" style="--c:#7C3AED;"><small>পেয়েছেন</small><b><?= e(taka($E['paid'])) ?></b></div>
            <div class="cx-stat" style="--c:#F59E0B;"><small>পাওনা</small><b><?= e(taka($E['due'])) ?></b></div>
        </div>
        <?php if ((int)$E['pending'] > 0): ?><div class="cx-card" style="background:#FFFBEB;border-color:#FDE68A;color:#92400E;font-weight:600;"><i class="fas fa-clock"></i> আরও <?= (int)$E['pending'] ?>টা পেমেন্ট অ্যাডমিনের যাচাইয়ের অপেক্ষায় — Approve হলে এখানে যোগ হবে।</div><?php endif; ?>
        <div class="cx-card">
            <h3><i class="fas fa-receipt"></i> বিক্রির তালিকা</h3>
            <?php if (!$E['sales']): ?><div class="cx-empty"><i class="fas fa-receipt"></i>এখনো কোনো বিক্রি হয়নি।</div><?php else: ?>
            <div class="cx-tw"><table class="cx-tbl">
                <thead><tr><th>তারিখ</th><th>কোর্স</th><th>শিক্ষার্থী</th><th>দাম</th><th>আপনার ভাগ</th><th>মাষ্টার মশাই</th><th>পেমেন্ট</th></tr></thead>
                <tbody>
                <?php foreach ($E['sales'] as $s): ?>
                    <tr>
                        <td><?= e(fmt_date($s->CreatedAt, 'd M Y')) ?></td>
                        <td><?= e($s->CourseTitle) ?></td>
                        <td><?= e($s->FullName ?: $s->UserName) ?></td>
                        <td><?= e(taka($s->Price)) ?></td>
                        <td><b style="color:#15803D;"><?= e(taka($s->TeacherShare)) ?></b></td>
                        <td><?= e(taka($s->AdminShare)) ?></td>
                        <td><small style="color:#64748B;"><?= e(trim(($s->MethodName ?? '') . ' ' . ($s->TxnId ?? '')) ?: '—') ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
        <div class="cx-card">
            <h3><i class="fas fa-money-bill-transfer"></i> আপনাকে পাঠানো টাকা</h3>
            <?php if (!$E['payouts']): ?><div class="cx-empty"><i class="fas fa-money-bill-transfer"></i>এখনো কোনো টাকা পাঠানো হয়নি।</div><?php else: ?>
            <div class="cx-tw"><table class="cx-tbl">
                <thead><tr><th>তারিখ</th><th>টাকা</th><th>মাধ্যম</th><th>নোট</th></tr></thead>
                <tbody>
                <?php foreach ($E['payouts'] as $p): ?>
                    <tr><td><?= e(fmt_date($p->CreatedAt, 'd M Y, h:i A')) ?></td><td><b><?= e(taka($p->Amount)) ?></b></td><td><?= e($p->Method ?? '—') ?></td><td><?= e($p->Note ?? '') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
