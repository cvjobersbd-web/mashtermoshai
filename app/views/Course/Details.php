<?php
/** কোর্সের বিস্তারিত — কী শিখবেন, কী কী থাকছে, ক্লাসের তালিকা, শিক্ষক, দাম, কিনুন */
$c = $Model; $lessons = $ViewBag->Lessons ?? []; $tasks = $ViewBag->Tasks ?? [];
$enrolled = !empty($ViewBag->Enrolled); $manage = !empty($ViewBag->CanManage); $pendingPay = !empty($ViewBag->PendingPay);
$tn = (string)($c->TeacherName ?: $c->TeacherUser ?: 'মাষ্টার মশাই');
$ViewData['Title'] = $c->Title;
$ViewData['FullTitle'] = $c->Title . ' | মাষ্টার মশাই কোর্স';
$ViewData['Description'] = mb_substr(trim(($c->Subtitle ?: '') . ' — ' . $tn . ' এর কোর্স। ' . strip_tags((string)$c->Description)), 0, 300);
if (!is_blank($c->Thumbnail)) $ViewData['OgImage'] = $c->Thumbnail;
$byLesson = [];
foreach ($tasks as $t) $byLesson[(int)$t->LessonId][] = $t;
$nLive = count(array_filter($lessons, fn($l) => $l->Kind === 'live'));
$nQuiz = count(array_filter($tasks, fn($t) => $t->Kind === 'quiz'));
$nAsg = count($tasks) - $nQuiz;
$me = current_user_id();
?>
<?php partial('Course/_Styles'); ?>
<style>
    .cd-top { background: linear-gradient(120deg, #08224A, #0A4F7A); color: #fff; padding: 34px 0 30px; }
    .cd-top .cx-back { color: #BFE8FF; }
    .cd-g { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 26px; align-items: start; }
    .cd-top h1 { font-family: var(--font-heading); font-size: clamp(26px, 3vw, 38px); font-weight: 800; margin: 8px 0 0; line-height: 1.25; }
    .cd-top .st { margin: 10px 0 0; font-size: 16.5px; opacity: .92; }
    .cd-meta { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 16px; font-size: 14px; font-weight: 600; opacity: .95; }
    .cd-meta span { display: inline-flex; gap: 7px; align-items: center; }
    .cd-buy { background: #fff; color: #0F172A; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,.25); position: relative; z-index: 2; margin-bottom: -1000px; }
    .cd-buy .in { padding: 18px 20px 20px; display: flex; flex-direction: column; gap: 12px; }
    .cd-buy .pr { font-size: 30px; } .cd-buy .pr .cx-price s { font-size: 17px; }
    .cd-buy .off { color: #DC2626; font-weight: 800; font-size: 14px; }
    .cd-buy ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px; color: #334155; }
    .cd-buy ul i { width: 20px; color: #0EA5E9; text-align: center; }
    .cd-body { padding-top: 26px; }
    .cd-desc { color: #334155; font-size: 15.5px; line-height: 1.75; overflow-wrap: anywhere; }
    .cd-inc { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 18px; list-style: none; padding: 0; margin: 0; }
    .cd-inc li { display: flex; gap: 10px; color: #334155; font-size: 14.5px; line-height: 1.5; } .cd-inc li i { color: #16A34A; margin-top: 4px; }
    .cd-ls { display: flex; flex-direction: column; gap: 8px; }
    .cd-l { display: flex; gap: 12px; align-items: center; border: 1px solid #EEF2F7; border-radius: 12px; padding: 11px 14px; }
    .cd-l .n { width: 30px; height: 30px; border-radius: 9px; background: #F1F5F9; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0; }
    .cd-l .t { flex: 1; min-width: 0; } .cd-l .t b { display: block; color: #0F172A; font-size: 14.5px; } .cd-l .t small { color: #64748B; font-size: 12.5px; }
    .cd-l .x { display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end; }
    .cd-teach { display: flex; gap: 14px; align-items: center; }
    .cd-teach .av { width: 64px; height: 64px; border-radius: 50%; background: #E0F2FE; color: #0369A1; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; overflow: hidden; flex-shrink: 0; }
    .cd-teach .av img { width: 100%; height: 100%; object-fit: cover; }
    .cd-teach b { font-size: 17px; color: #0F172A; display: block; } .cd-teach a { color: #0369A1; font-weight: 700; font-size: 14px; text-decoration: none; }
    @media (max-width: 960px) { .cd-g { grid-template-columns: minmax(0, 1fr); } .cd-buy { position: static; margin-bottom: 0; } .cd-inc { grid-template-columns: minmax(0, 1fr); } }
</style>
<?= csrf_field() ?>
<section class="cd-top">
    <div class="cx-wrap">
        <a class="cx-back" href="/Course"><i class="fas fa-arrow-left"></i> সব কোর্স</a>
        <div class="cd-g">
            <div>
                <?php if ($c->Kind === 'live'): ?><span class="cx-pill" style="background:#DC2626;color:#fff;"><i class="fas fa-tower-broadcast"></i> লাইভ ক্লাস</span><?php endif; ?>
                <?php if (!is_blank($c->Category)): ?><span class="cx-pill blue"><?= e($c->Category) ?></span><?php endif; ?>
                <?php if (!$c->IsLive): ?> <span class="cx-pill warn"><?= $c->AdminHidden ? 'অ্যাডমিন লুকিয়েছেন' : 'Draft — এখনো প্রকাশ হয়নি' ?></span><?php endif; ?>
                <h1><?= e($c->Title) ?></h1>
                <?php if (!is_blank($c->Subtitle)): ?><p class="st"><?= e($c->Subtitle) ?></p><?php endif; ?>
                <div class="cd-meta">
                    <span><i class="fas fa-chalkboard-user"></i> <?= e($tn) ?></span>
                    <span><i class="fas fa-circle-play"></i> <?= count($lessons) ?>টা ক্লাস<?= $nLive ? ' (' . $nLive . 'টা লাইভ)' : '' ?></span>
                    <span><i class="fas fa-user-graduate"></i> <?= (int)$ViewBag->Students ?> জন শিক্ষার্থী</span>
                    <?php if (!is_blank($c->Level)): ?><span><i class="fas fa-signal"></i> <?= e($c->Level) ?></span><?php endif; ?>
                </div>
            </div>
            <aside class="cd-buy">
                <?= course_thumb_html($c) ?>
                <div class="in">
                    <div class="pr"><?= course_price_html($c) ?><?php if (!empty($c->RegularPrice) && $c->Price > 0): ?> <span class="off"><?= (int)round(100 - $c->Price * 100 / $c->RegularPrice) ?>% ছাড়</span><?php endif; ?></div>
                    <?php if ($enrolled): ?>
                        <a class="cx-btn ok lg" href="/Course/Learn/<?= (int)$c->Id ?>"><i class="fas fa-circle-play"></i> ক্লাসে যান</a>
                    <?php elseif ($manage): ?>
                        <a class="cx-btn pri lg" href="/Course/Build/<?= (int)$c->Id ?>"><i class="fas fa-pen-to-square"></i> কোর্স সাজান</a>
                    <?php elseif ($pendingPay): ?>
                        <div class="cx-pill warn" style="white-space:normal;padding:10px 12px;font-size:13px;"><i class="fas fa-hourglass-half"></i> আপনার পেমেন্ট যাচাই হচ্ছে — Approve হলেই কোর্স খুলে যাবে।</div>
                    <?php elseif (!$me): ?>
                        <a class="cx-btn pri lg" href="/Account/Login?returnUrl=<?= rawurlencode('/Course/Details/' . (int)$c->Id) ?>"><i class="fas fa-right-to-bracket"></i> লগইন করে কিনুন</a>
                    <?php elseif ((int)$c->Price <= 0): ?>
                        <button type="button" class="cx-btn ok lg" id="cdEnroll"><i class="fas fa-user-plus"></i> ফ্রি তে ভর্তি হোন</button>
                    <?php else: ?>
                        <a class="cx-btn pri lg" href="/Payment/Checkout?course=<?= (int)$c->Id ?>"><i class="fas fa-cart-shopping"></i> কোর্সটি কিনুন</a>
                    <?php endif; ?>
                    <ul>
                        <li><i class="fas fa-circle-play"></i> <?= count($lessons) - $nLive ?>টা রেকর্ড করা ক্লাস</li>
                        <?php if ($nLive): ?><li><i class="fas fa-tower-broadcast"></i> <?= $nLive ?>টা সরাসরি লাইভ ক্লাস</li><?php endif; ?>
                        <?php if ($nAsg): ?><li><i class="fas fa-paperclip"></i> <?= $nAsg ?>টা অ্যাসাইনমেন্ট</li><?php endif; ?>
                        <?php if ($nQuiz): ?><li><i class="fas fa-list-check"></i> <?= $nQuiz ?>টা কুইজ</li><?php endif; ?>
                        <li><i class="fas fa-infinity"></i> আজীবন অ্যাক্সেস</li>
                        <li><i class="fas fa-award"></i> কোর্স শেষে সার্টিফিকেট</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>
<div class="cx-page cd-body">
    <div class="cx-wrap">
        <div class="cd-g">
            <div>
                <?php if ($c->IncludesList): ?>
                <div class="cx-card"><h3><i class="fas fa-list-check"></i> এই কোর্সে যা থাকছে</h3>
                    <ul class="cd-inc"><?php foreach ($c->IncludesList as $i): ?><li><i class="fas fa-check"></i><span><?= e($i) ?></span></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if (!is_blank($c->Description)): ?>
                <div class="cx-card"><h3><i class="fas fa-align-left"></i> কোর্স সম্পর্কে</h3><div class="cd-desc"><?= sanitize_rich_html($c->Description) ?></div></div>
                <?php endif; ?>
                <div class="cx-card"><h3><i class="fas fa-layer-group"></i> ক্লাসের তালিকা <span class="cx-pill"><?= count($lessons) ?>টা</span></h3>
                    <?php if (!$lessons): ?><div class="cx-empty" style="padding:16px;">শীঘ্রই ক্লাস যোগ হবে।</div><?php endif; ?>
                    <div class="cd-ls">
                        <?php foreach ($lessons as $i => $l): $tk = $byLesson[$l->Id] ?? []; ?>
                            <div class="cd-l">
                                <span class="n"><?= $i + 1 ?></span>
                                <div class="t"><b><?= e($l->Title) ?></b>
                                    <small><?php if ($l->Kind === 'live'): ?><i class="fas fa-tower-broadcast" style="color:#DC2626;"></i> লাইভ · <?= e(fmt_date($l->StartsAt, 'd M Y, h:i A')) ?><?php else: ?><i class="fas fa-circle-play"></i> রেকর্ড ক্লাস<?= $l->RecordingMin ? ' · ' . (int)$l->RecordingMin . ' মিনিট' : '' ?><?= $l->Locked ? ' · খুলবে ' . e(fmt_date($l->UnlockAt, 'd M Y, h:i A')) : '' ?><?php endif; ?></small></div>
                                <div class="x"><?php foreach ($tk as $t): ?><span class="cx-pill <?= $t->Kind === 'quiz' ? 'purple' : 'blue' ?>"><i class="fas <?= $t->Kind === 'quiz' ? 'fa-list-check' : 'fa-paperclip' ?>"></i> <?= $t->Kind === 'quiz' ? 'কুইজ' : 'অ্যাসাইনমেন্ট' ?></span><?php endforeach; ?>
                                    <?php if (!$enrolled && !$manage): ?><i class="fas fa-lock" style="color:#CBD5E1;"></i><?php endif; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="cx-card"><h3><i class="fas fa-chalkboard-user"></i> শিক্ষক</h3>
                    <div class="cd-teach"><span class="av"><?= e(initials($tn)) ?><?php if (!is_blank($c->TeacherPhoto)): ?><img src="<?= e($c->TeacherPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                        <div><b><?= e($tn) ?></b><?php if (($c->TeacherRole ?? '') === 'Teacher'): ?><a href="/Tutors/Profile/<?= (int)$c->TeacherId ?>">প্রোফাইল দেখুন →</a><?php endif; ?></div></div>
                </div>
            </div>
            <div></div>
        </div>
    </div>
</div>
<?php if ($me && !$enrolled && !$manage && (int)$c->Price <= 0): ?>
<script>
document.getElementById('cdEnroll').addEventListener('click', async function () {
    this.disabled = true;
    var d = await cxPost('/Course/Enroll', { id: <?= (int)$c->Id ?> });
    if (d.redirect) { location.href = d.redirect; return; }
    this.disabled = false; cxToast(d.message || 'হয়নি।');
});
</script>
<?php endif; ?>
