<?php
/** Course — সব কোর্স (টিচারদের), দাম সহ; খুঁজুন / বিভাগ */
$list = is_array($Model) ? $Model : [];
$ViewData['Title'] = 'Courses';
$ViewData['FullTitle'] = 'কোর্স | মাষ্টার মশাই';
$ViewData['Description'] = 'মাষ্টার মশাই এর শিক্ষকদের অনলাইন কোর্স — রেকর্ড করা ক্লাস, লাইভ ক্লাস, অ্যাসাইনমেন্ট, কুইজ আর সার্টিফিকেট।';
$q = (string)$ViewBag->Q; $cat = (string)$ViewBag->Cat; $cats = $ViewBag->Cats ?? []; $mine = $ViewBag->Mine ?? [];
?>
<?php partial('Course/_Styles'); ?>
<style>
    .ci-hero { background: linear-gradient(120deg, #08224A 0%, #0A5A86 55%, #0EA5E9 100%); color: #fff; padding: 46px 0 40px; position: relative; overflow: hidden; }
    .ci-hero::after { content: ''; position: absolute; right: -60px; top: -60px; width: 260px; height: 260px; border-radius: 50%; background: rgba(255,255,255,.08); }
    .ci-hero h1 { font-family: var(--font-heading); font-size: clamp(28px, 3.4vw, 44px); font-weight: 800; margin: 0; }
    .ci-hero p { margin: 10px 0 0; font-size: 16.5px; opacity: .92; max-width: 640px; }
    .ci-feat { display: flex; gap: 18px; flex-wrap: wrap; margin-top: 16px; font-size: 14px; font-weight: 600; opacity: .95; }
    .ci-feat span { display: inline-flex; align-items: center; gap: 7px; }
    .ci-srch { display: flex; gap: 8px; margin-top: 22px; max-width: 640px; background: #fff; border-radius: 14px; padding: 6px; }
    .ci-srch input { flex: 1; min-width: 0; border: none; outline: none; padding: 0 12px; font-size: 15px; font-family: inherit; color: #0F172A; }
    .ci-cats { display: flex; gap: 8px; flex-wrap: wrap; margin: 22px 0 18px; }
    .ci-cats a { padding: 7px 15px; border-radius: 999px; background: #fff; border: 1px solid #DCE6F0; color: #334155; font-weight: 700; font-size: 13.5px; text-decoration: none; }
    .ci-cats a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .ci-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
    .ci-c { background: #fff; border: 1px solid #E3EAF3; border-radius: 18px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: inherit; box-shadow: 0 6px 22px rgba(15,52,103,.06); transition: transform .2s, box-shadow .2s; }
    .ci-c:hover { transform: translateY(-4px); box-shadow: 0 16px 36px rgba(15,52,103,.13); color: inherit; }
    .ci-b { padding: 15px 16px 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .ci-b h3 { margin: 0; font-size: 17px; font-weight: 800; color: #0F172A; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ci-b .st { color: #64748B; font-size: 13.5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ci-t { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; font-weight: 600; }
    .ci-t span.av { width: 26px; height: 26px; border-radius: 50%; background: #E0F2FE; color: #0369A1; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; overflow: hidden; }
    .ci-t span.av img { width: 100%; height: 100%; object-fit: cover; }
    .ci-m { display: flex; gap: 12px; flex-wrap: wrap; font-size: 12.5px; color: #64748B; }
    .ci-f { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 10px; border-top: 1px solid #F1F5F9; font-size: 19px; }
    .ci-mine { position: absolute; top: 10px; left: 10px; z-index: 2; }
    @media (max-width: 980px) { .ci-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 620px) { .ci-grid { grid-template-columns: minmax(0, 1fr); } .ci-hero { padding: 32px 0 28px; } }
</style>
<section class="ci-hero">
    <div class="cx-wrap">
        <h1><i class="fas fa-book-open-reader"></i> কোর্স</h1>
        <p>অভিজ্ঞ শিক্ষকদের কোর্স — রেকর্ড করা ক্লাস, সরাসরি লাইভ ক্লাস, অ্যাসাইনমেন্ট, কুইজ আর কোর্স শেষে সার্টিফিকেট।</p>
        <div class="ci-feat"><span><i class="fas fa-circle-play"></i> রেকর্ড ক্লাস</span><span><i class="fas fa-tower-broadcast"></i> লাইভ ক্লাস</span><span><i class="fas fa-list-check"></i> কুইজ ও অ্যাসাইনমেন্ট</span><span><i class="fas fa-award"></i> সার্টিফিকেট</span></div>
        <form class="ci-srch" method="get" action="/Course">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="কোর্স বা শিক্ষকের নাম লিখুন..." />
            <?php if ($cat !== ''): ?><input type="hidden" name="cat" value="<?= e($cat) ?>" /><?php endif; ?>
            <button type="submit" class="cx-btn pri"><i class="fas fa-magnifying-glass"></i> খুঁজুন</button>
        </form>
    </div>
</section>
<div class="cx-page" style="padding-top:0;">
    <div class="cx-wrap">
        <div class="ci-cats">
            <a href="/Course<?= $q !== '' ? '?q=' . rawurlencode($q) : '' ?>" class="<?= $cat === '' ? 'on' : '' ?>">সব কোর্স</a>
            <?php foreach ($cats as $k): ?><a href="/Course?<?= e(http_build_query(array_filter(['q' => $q, 'cat' => $k]))) ?>" class="<?= $cat === $k ? 'on' : '' ?>"><?= e($k) ?></a><?php endforeach; ?>
            <?php if (current_user_id()): ?><a href="/Course/My" style="margin-left:auto;"><i class="fas fa-bookmark"></i> আমার কোর্স</a><?php endif; ?>
            <?php if (current_role() === 'Teacher'): ?><a href="/Course/Manage"><i class="fas fa-chalkboard-user"></i> কোর্স বানান / বিক্রি করুন</a><?php endif; ?>
        </div>
        <?php if (!$list): ?>
            <div class="cx-card cx-empty"><i class="fas fa-book-open"></i><?= $q !== '' || $cat !== '' ? 'এই খোঁজে কোনো কোর্স পাওয়া যায়নি।' : 'এখনো কোনো কোর্স প্রকাশ হয়নি — শীঘ্রই আসছে!' ?></div>
        <?php else: ?>
        <div class="ci-grid">
            <?php foreach ($list as $c): $tn = (string)($c->TeacherName ?: $c->TeacherUser ?: 'মাষ্টার মশাই'); ?>
                <a class="ci-c" href="/Course/Details/<?= (int)$c->Id ?>">
                    <div style="position:relative;"><?= course_thumb_html($c) ?><?php if (isset($mine[$c->Id])): ?><span class="cx-pill ok ci-mine"><i class="fas fa-check"></i> ভর্তি আছেন</span><?php endif; ?></div>
                    <div class="ci-b">
                        <?php if (!is_blank($c->Category)): ?><span class="cx-pill blue" style="align-self:flex-start;"><?= e($c->Category) ?></span><?php endif; ?>
                        <h3><?= e($c->Title) ?></h3>
                        <?php if (!is_blank($c->Subtitle)): ?><div class="st"><?= e($c->Subtitle) ?></div><?php endif; ?>
                        <div class="ci-t"><span class="av"><?= e(initials($tn)) ?><?php if (!is_blank($c->TeacherPhoto) && viewer_sees_teacher_photos()): ?><img src="<?= e($c->TeacherPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span><?= e($tn) ?></div>
                        <div class="ci-m"><span><i class="fas fa-circle-play"></i> <?= (int)$c->LessonCount ?>টা ক্লাস</span><span><i class="fas fa-user-graduate"></i> <?= (int)$c->StudentCount ?> জন</span><?php if (!is_blank($c->Level)): ?><span><i class="fas fa-signal"></i> <?= e($c->Level) ?></span><?php endif; ?></div>
                        <div class="ci-f"><?= course_price_html($c) ?><span class="cx-btn sm pri">বিস্তারিত <i class="fas fa-arrow-right"></i></span></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
