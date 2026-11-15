<?php
/** টিচার → আমার কোর্স: কোর্স বানানো, সাজানো, প্রকাশ, শিক্ষার্থী আর আয়ের সারাংশ */
$list = is_array($Model) ? $Model : []; $E = $ViewBag->Earn;
$ViewData['Title'] = 'আমার কোর্স (টিচার)';
$ViewData['Robots'] = 'noindex, nofollow';
$pct = course_admin_pct();
?>
<?php partial('Course/_Styles'); ?>
<style>
    .mg-row { display: grid; grid-template-columns: 170px minmax(0, 1fr) auto; gap: 16px; align-items: center; border: 1px solid #EEF2F7; border-radius: 16px; padding: 12px; margin-bottom: 12px; background: #fff; }
    .mg-row .cx-thumb { border-radius: 12px; }
    .mg-row h3 { margin: 0; font-size: 17px; font-weight: 800; color: #0F172A; } .mg-row h3 a { color: inherit; text-decoration: none; }
    .mg-m { display: flex; gap: 12px; flex-wrap: wrap; font-size: 13px; color: #64748B; margin-top: 6px; align-items: center; }
    .mg-a { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
    .mg-note { background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E3A8A; border-radius: 14px; padding: 14px 16px; font-size: 14px; line-height: 1.6; margin-bottom: 18px; }
    @media (max-width: 760px) { .mg-row { grid-template-columns: minmax(0, 1fr); } .mg-a { justify-content: flex-start; } }
</style>
<?= csrf_field() ?>
<div class="cx-page">
    <div class="cx-wrap">
        <a class="cx-back" href="<?= is_admin() ? '/Admin/Courses' : '/Teacher/TeacherDashboard' ?>"><i class="fas fa-arrow-left"></i> <?= is_admin() ? 'Admin → Courses' : 'Teacher Dashboard' ?></a>
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:18px;">
            <div><h1 class="cx-h1"><i class="fas fa-chalkboard-user" style="color:#0EA5E9;"></i> আমার কোর্স</h1><p class="cx-sub"><b>কোর্স</b> (রেকর্ড করা ক্লাস, কুইজ, অ্যাসাইনমেন্ট) বানিয়ে নিজের দামে বিক্রি করুন। শিক্ষার্থীদের <b>লাইভ ক্লাস</b> নিতে <a href="/Package?for=teacher">Premium প্যাকেজ</a> নিন — চ্যাট থেকে এক ক্লিকে ক্লাস শুরু।</p></div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;"><a class="cx-btn pri lg" href="/Course/Edit?kind=course"><i class="fas fa-plus"></i> নতুন কোর্স</a></div>
        </div>
        <div class="cx-stats">
            <div class="cx-stat" style="--c:#0EA5E9;"><small>মোট বিক্রি</small><b><?= (int)$E['count'] ?>টা</b></div>
            <div class="cx-stat" style="--c:#16A34A;"><small>আপনার আয় (<?= 100 - $pct ?>%)</small><b><?= e(taka($E['teacher'])) ?></b></div>
            <div class="cx-stat" style="--c:#7C3AED;"><small>পেয়েছেন</small><b><?= e(taka($E['paid'])) ?></b></div>
            <div class="cx-stat" style="--c:#F59E0B;"><small>পাওনা</small><b><?= e(taka($E['due'])) ?></b></div>
        </div>
        <div class="mg-note"><i class="fas fa-circle-info"></i> কোর্সের দাম থেকে <b><?= 100 - $pct ?>%</b> আপনার, <b><?= $pct ?>%</b> মাষ্টার মশাই এর (যেমন ৳6000 এর কোর্স → আপনি ৳<?= number_format(6000 * (100 - $pct) / 100) ?>)। শিক্ষার্থীর পেমেন্ট অ্যাডমিন যাচাই করলে বিক্রি হিসাবে যোগ হয়; আপনার পাওনা অ্যাডমিন পাঠিয়ে দেন। <a href="/Course/Earnings" style="font-weight:700;">পুরো হিসাব দেখুন →</a></div>
        <div class="cx-card">
            <h3><i class="fas fa-layer-group"></i> আমার কোর্স ও লাইভ ক্লাস <span class="cx-pill"><?= count($list) ?>টা</span></h3>
            <?php if (!$list): ?><div class="cx-empty"><i class="fas fa-book-open"></i>এখনো কোনো কোর্স বানাননি — উপরের <b>নতুন কোর্স</b> চাপুন।</div><?php endif; ?>
            <?php foreach ($list as $c): ?>
                <div class="mg-row">
                    <a href="/Course/Build/<?= (int)$c->Id ?>"><?= course_thumb_html($c) ?></a>
                    <div>
                        <h3><a href="/Course/Build/<?= (int)$c->Id ?>"><?= e($c->Title) ?></a></h3>
                        <div class="mg-m">
                            <?= $c->Kind === 'live' ? '<span class="cx-pill" style="background:#FEE2E2;color:#B91C1C;"><i class="fas fa-tower-broadcast"></i> লাইভ ক্লাস</span>' : '<span class="cx-pill" style="background:#E0F2FE;color:#075985;"><i class="fas fa-book-open-reader"></i> কোর্স</span>' ?>
                            <?= $c->AdminBlocked ? '<span class="cx-pill no" title="' . e($c->BlockReason) . '"><i class="fas fa-ban"></i> অ্যাডমিন ব্লক করেছেন' . ($c->BlockReason !== '' ? ' — ' . e($c->BlockReason) : '') . '</span>' : ($c->AdminHidden ? '<span class="cx-pill no"><i class="fas fa-eye-slash"></i> অ্যাডমিন লুকিয়েছেন</span>' : ($c->Status === 'published' ? '<span class="cx-pill ok">● প্রকাশিত</span>' : '<span class="cx-pill warn">Draft</span>')) ?>
                            <?= course_price_html($c) ?>
                            <span><i class="fas fa-circle-play"></i> <?= (int)$c->LessonCount ?>টা ক্লাস</span>
                            <span><i class="fas fa-user-graduate"></i> <?= (int)$c->StudentCount ?> জন</span>
                        </div>
                    </div>
                    <div class="mg-a">
                        <a class="cx-btn pri sm" href="/Course/Build/<?= (int)$c->Id ?>"><i class="fas fa-layer-group"></i> ক্লাস ও কাজ</a>
                        <a class="cx-btn ghost sm" href="/Course/Edit/<?= (int)$c->Id ?>"><i class="fas fa-pen"></i> তথ্য</a>
                        <a class="cx-btn ghost sm" href="/Course/Students/<?= (int)$c->Id ?>"><i class="fas fa-users"></i> শিক্ষার্থী</a>
                        <a class="cx-btn ghost sm" href="/Course/Details/<?= (int)$c->Id ?>" target="_blank" rel="noopener"><i class="fas fa-eye"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
