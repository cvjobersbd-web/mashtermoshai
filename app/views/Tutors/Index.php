<?php
/**
 * Tutor List (Tutor Hub) — পরিসংখ্যান, Tutor ID দিয়ে খোঁজা, জেলা, আর সেকশন আকারে টিউটর (প্রতিটার "See All")
 * @var array $Model  ['all' => ['title', 'tutors'], 'premium' => ..., ...]
 */
$sections = is_array($Model) ? $Model : [];
// Schema.org: পেজের টিউটরদের তালিকা (Person)
$__seen = []; $__items = [];
foreach ($sections as $__s) foreach (($__s['tutors'] ?? []) as $__t) { if (isset($__seen[(int)$__t->UserId])) continue; $__seen[(int)$__t->UserId] = 1;
    $__items[] = ['name' => (string)$__t->FullName, 'url' => '/Tutors/Profile/' . (int)$__t->UserId, 'item' => array_filter(['@type' => 'Person', 'jobTitle' => 'Tutor', 'affiliation' => !is_blank($__t->Institution ?? '') ? ['@type' => 'EducationalOrganization', 'name' => (string)$__t->Institution] : null])]; }
$ViewData['SchemaType'] = 'CollectionPage';
seo_schema(seo_item_list(t('যাচাই করা টিউটর', 'Verified tutors'), $__items));
$cities = is_array($ViewBag->Cities) ? $ViewBag->Cities : [];
$city = (string)($ViewBag->City ?? '');
$ViewData['Title'] = 'Tutor List';
$ViewData['FullTitle'] = t('টিউটর লিস্ট — যাচাই করা হোম টিউটর খুঁজুন', 'Tutor List — Find Verified Home Tutors in BD') . ' | ' . seo_brand();
$ViewData['Description'] = t('মাষ্টার মশাই এর অনুমোদিত সব টিউটরের তালিকা — জেলা, প্রতিষ্ঠান আর প্রোফাইল দেখে পছন্দের টিউটর বেছে নিন।', 'All approved tutors on Mashter Moshai — choose your tutor by district, institution and profile.');
?>
<?php partial('Tutors/_Styles'); ?>
<style>
    .tl-top h1 { margin: 0 0 18px; font-size: 26px; font-weight: 600; color: #1F2937; font-family: var(--font-heading); }
    .tl-cities { position: relative; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(15,27,45,.05); padding: 8px 46px; }
    .tl-cities .tl-crow { display: flex; gap: 14px; overflow-x: auto; scroll-behavior: smooth; scrollbar-width: none; }
    .tl-cities .tl-crow::-webkit-scrollbar { display: none; }
    .tl-cities a { flex: 0 0 auto; min-width: 200px; text-align: center; padding: 9px 14px; border-radius: 8px; background: #F3F6FA; color: #1F2937; font-weight: 700; font-size: 12.5px; text-transform: uppercase; text-decoration: none; letter-spacing: .3px; }
    .tl-cities a:hover { background: #E6F9FF; color: #0284C7; }
    .tl-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 28px; height: 28px; border-radius: 50%; border: none; background: #6B7280; color: #fff; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; }
    .tl-arrow:hover { background: #00B5E0; }
    .tl-arrow.l { left: 10px; } .tl-arrow.r { right: 10px; }
    .tl-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; margin-top: 18px; }
    .tl-stat { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(15,27,45,.05); padding: 18px; display: flex; align-items: center; gap: 16px; }
    .tl-stat i { width: 48px; height: 48px; border-radius: 8px; background: #00CFFF; color: #fff; font-size: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .tl-stat b { display: block; font-size: 24px; color: #1F2937; line-height: 1.1; }
    .tl-stat span { font-size: 12.5px; color: #6B7280; }
    a.tl-stat { text-decoration: none; color: inherit; border: 2px solid transparent; transition: border-color .15s, transform .15s, box-shadow .15s; }
    a.tl-stat:hover { border-color: #00CFFF; transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0,207,255,.18); }
    a.tl-stat > div { flex: 1; min-width: 0; }
    a.tl-stat em { color: #CBD5E1; font-size: 15px; transition: color .15s, transform .15s; }
    a.tl-stat:hover em { color: #00B5E0; transform: translateX(3px); }
    .tl-cities .tl-crow a.on { background: #00CFFF; color: #fff; }
    .tl-sel { margin-top: 14px; background: #E6F9FF; border: 1px solid #A5E9FA; color: #0F172A; border-radius: 10px; padding: 10px 14px; font-size: 13.5px; }
    .tl-sel i { color: #00B5E0; } .tl-sel a { color: #0284C7; font-weight: 700; margin-left: 6px; text-decoration: none; white-space: nowrap; }
    .tl-findrow { display: flex; align-items: center; justify-content: center; gap: 10px; margin: 44px auto 6px; max-width: 600px; }
    .tl-findrow .tl-find { margin: 0; flex: 1; min-width: 0; max-width: none; }
    .tl-findrow .tl-find input { width: 100%; }
    .tl-fbtn { flex-shrink: 0; height: 50px; min-width: 50px; padding: 0 18px; border-radius: 999px; border: none; background: linear-gradient(135deg, #00CFFF, #0284C7); color: #fff; font-weight: 800; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(2,132,199,.25); line-height: 1; }
    .tl-fbtn i { font-size: 17px; margin: 0; }
    .tl-fbtn:hover { filter: brightness(1.05); }
    .tl-fhint { text-align: center; font-size: 13px; color: #64748B; margin: 4px 0 10px; }
    .tl-fhint i { color: #00B5E0; } .tl-fhint button { border: none; background: none; color: #0369A1; font-weight: 700; padding: 0; cursor: pointer; text-decoration: underline; text-underline-offset: 3px; }
    @media (max-width: 520px) {
        .tl-findrow { gap: 8px; margin-top: 30px; }
        .tl-findrow .tl-find { height: 48px; padding: 4px 4px 4px 14px; }
        .tl-findrow .tl-find button { height: 40px; padding: 0 16px; }
        .tl-fbtn { width: 48px; min-width: 48px; height: 48px; padding: 0; border-radius: 50%; }
        .tl-fbtn span { display: none; }
        .tl-fhint { font-size: 12.5px; line-height: 1.6; padding: 0 6px; }
    }
    .tl-find { margin: 44px auto 10px; max-width: 520px; display: flex; align-items: center; gap: 8px; background: #fff; border-radius: 999px; padding: 5px 5px 5px 18px; box-shadow: 0 2px 12px rgba(15,27,45,.08); }
    .tl-find i { color: #6B7280; }
    .tl-find input { flex: 1; min-width: 0; border: none; outline: none; font-size: 14px; font-family: inherit; background: transparent; padding: 8px 0; }
    .tl-find button { border: none; background: #00CFFF; color: #fff; font-weight: 700; font-size: 13px; padding: 0 20px; height: 36px; border-radius: 999px; cursor: pointer; font-family: inherit; }
    .tl-find button:hover { background: #00B5E0; }
    .tl-err { text-align: center; color: #DC2626; font-size: 13.5px; font-weight: 600; }
    .tl-sec { margin-top: 50px; }
    .tl-car { position: relative; }
    .tl-track { display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - 3 * 22px) / 4); gap: 22px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; scrollbar-width: none; padding: 4px 2px 6px; }
    .tl-track::-webkit-scrollbar { display: none; }
    .tl-track > .tl-card { scroll-snap-align: start; }
    .tl-nav { display: flex; justify-content: center; gap: 10px; margin-top: 16px; }
    .tl-nav button { width: 26px; height: 26px; border-radius: 50%; border: none; background: #9CA3AF; color: #fff; cursor: pointer; font-size: 11px; }
    .tl-nav button:hover { background: #00B5E0; }
    .tl-empty { background: #fff; border-radius: 12px; padding: 50px 20px; text-align: center; color: #64748B; }
    .tl-empty i { font-size: 42px; color: #B9E9F7; display: block; margin-bottom: 12px; }
    .tl-teach { margin-top: 50px; background: linear-gradient(120deg, #08224A, #0A5A86 60%, #00CFFF); color: #fff; border-radius: 16px; padding: 24px 26px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .tl-teach h3 { margin: 0; font-size: 20px; font-weight: 800; }
    .tl-teach p { margin: 4px 0 0; opacity: .9; font-size: 14px; }
    .tl-teach a { background: #fff; color: #08224A !important; font-weight: 800; padding: 11px 20px; border-radius: 10px; text-decoration: none; font-size: 14px; white-space: nowrap; }
    @media (max-width: 991px) { .tl-track { grid-auto-columns: calc((100% - 2 * 16px) / 3); gap: 16px; } }
    @media (max-width: 700px) {
        .tl-stats { grid-template-columns: minmax(0, 1fr); gap: 10px; }
        .tl-track { grid-auto-columns: calc((100% - 12px) / 2); gap: 12px; }
        .tl-cities { padding: 8px 40px; } .tl-cities a { min-width: 150px; }
        .tl-top h1 { font-size: 21px; }
    }
</style>

<div class="tl">
    <div class="tl-wrap">
        <div class="tl-top">
            <h1>যাচাই করা টিউটর খুঁজুন — কয়েক ক্লিকেই!</h1>
            <?php if ($cities): ?>
                <div class="tl-cities">
                    <button type="button" class="tl-arrow l" data-scroll="-1" aria-label="আগের"><i class="fas fa-chevron-left"></i></button>
                    <div class="tl-crow" id="tlCities">
                        <a class="<?= $city === '' ? 'on' : '' ?>" href="/Tutors#tlStats"><?= t('সব জেলা', 'All Districts') ?></a>
                        <?php foreach ($cities as $c => $n): ?><a class="<?= $city === $c ? 'on' : '' ?>" href="/Tutors?city=<?= rawurlencode($c) ?>#tlStats"><?= e($c) ?> (<?= (int)$n ?>)</a><?php endforeach; ?>
                    </div>
                    <button type="button" class="tl-arrow r" data-scroll="1" aria-label="পরের"><i class="fas fa-chevron-right"></i></button>
                </div>
            <?php endif; ?>
            <?php $cq = $city !== '' ? '&city[]=' . rawurlencode($city) : ''; ?>
            <?php if ($city !== ''): ?><div class="tl-sel"><i class="fas fa-location-dot"></i> <b><?= e($city) ?></b> <?= t('জেলার টিউটর — নিচে Male / Female চাপলে ওই জেলার ছেলে / মেয়ে টিউটর দেখাবে।', 'tutors — tap Male / Female below to see them.') ?> <a href="/Tutors#tlStats"><?= t('সব জেলা', 'All districts') ?> ✕</a></div><?php endif; ?>
            <div class="tl-stats" id="tlStats">
                <a class="tl-stat" href="/Tutors/All<?= $cq !== '' ? '?' . ltrim($cq, '&') : '' ?>"><i class="fas fa-user-group"></i><div><b><?= number_format((int)$ViewBag->Total) ?></b><span>Total Tutors<?= $city !== '' ? ' · ' . e($city) : '' ?></span></div><em class="fas fa-arrow-right"></em></a>
                <a class="tl-stat" href="/Tutors/All?gender=male<?= $cq ?>"><i class="fas fa-person"></i><div><b><?= number_format((int)$ViewBag->Male) ?></b><span>Male Tutors<?= $city !== '' ? ' · ' . e($city) : '' ?></span></div><em class="fas fa-arrow-right"></em></a>
                <a class="tl-stat" href="/Tutors/All?gender=female<?= $cq ?>"><i class="fas fa-person-dress"></i><div><b><?= number_format((int)$ViewBag->Female) ?></b><span>Female Tutors<?= $city !== '' ? ' · ' . e($city) : '' ?></span></div><em class="fas fa-arrow-right"></em></a>
            </div>
            <div class="tl-findrow">
                <form class="tl-find" action="/Tutors" method="get" role="search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" autocomplete="off" name="tid" value="<?= e($ViewBag->Tid) ?>" maxlength="60" placeholder="Tutor ID বা নাম দিয়ে খুঁজুন — যেমন TCH-1024 বা Rahim" aria-label="Tutor ID বা নাম" />
                    <button type="submit">Search</button>
                </form>
                <button type="button" class="tl-fbtn" data-tf-open aria-label="ফিল্টার" title="এলাকা, ক্লাস, বেতন, সময়, বিশ্ববিদ্যালয় দিয়ে খুঁজুন"><i class="fas fa-sliders"></i><span>ফিল্টার</span></button>
            </div>
            <p class="tl-fhint"><i class="fas fa-wand-magic-sparkles"></i> নিজের দরকার মতো টিচার খুঁজুন — <button type="button" data-tf-open>এলাকা, ক্লাস, বেতন, সময়, বিশ্ববিদ্যালয় দিয়ে ফিল্টার করুন</button></p>
            <?php if ($ViewBag->TidError !== ''): ?><div class="tl-err"><?= e($ViewBag->TidError) ?></div><?php endif; ?>
        </div>

        <?php if (!empty($ViewBag->NameHits)): ?>
            <section class="tl-sec" id="tlHits">
                <div class="tl-h"><h2>সার্চের ফলাফল: “<?= e($ViewBag->Tid) ?>” — <?= count($ViewBag->NameHits) ?> জন টিউটর</h2><a class="tl-btn" href="/Tutors">সার্চ মুছুন <i class="fas fa-xmark"></i></a></div>
                <div class="tl-grid"><?php foreach ($ViewBag->NameHits as $t) partial('Tutors/_Card', ['t' => $t]); ?></div>
            </section>
        <?php endif; ?>

        <?php if (!$sections): ?>
            <div class="tl-sec tl-empty"><i class="fas fa-chalkboard-user"></i>এখনো কোনো অনুমোদিত টিউটর নেই।</div>
        <?php endif; ?>

        <?php foreach ($sections as $key => $sec): ?>
            <section class="tl-sec">
                <div class="tl-h">
                    <h2><?= e($sec['title']) ?></h2>
                    <a class="tl-btn" href="/Tutors/All?type=<?= e($key) ?><?= $city !== '' ? '&amp;city[]=' . rawurlencode($city) : '' ?>">See All <i class="fas fa-caret-right"></i></a>
                </div>
                <div class="tl-car">
                    <div class="tl-track" id="tlT-<?= e($key) ?>">
                        <?php foreach ($sec['tutors'] as $t) partial('Tutors/_Card', ['t' => $t]); ?>
                    </div>
                    <?php if (count($sec['tutors']) > 2): ?>
                        <div class="tl-nav"><button type="button" data-track="tlT-<?= e($key) ?>" data-dir="-1" aria-label="আগের"><i class="fas fa-chevron-left"></i></button><button type="button" data-track="tlT-<?= e($key) ?>" data-dir="1" aria-label="পরের"><i class="fas fa-chevron-right"></i></button></div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <div class="tl-teach">
            <div><h3>আপনি কি টিচার?</h3><p>"পড়াতে চাই" পোস্ট দিন — স্টুডেন্ট আর অভিভাবকরা আপনাকে খুঁজে পাবে।</p></div>
            <a href="/FindTutor"><i class="fas fa-pen"></i> পড়াতে চাই পোস্ট</a>
        </div>
    </div>
</div>

<script>(function () { var i = document.querySelector('.tl-find input[name="tid"]'); if (i && window.innerWidth < 520) i.placeholder = 'Tutor ID বা নাম'; })();</script>
<?php partial('Tutors/_FilterPanel', ['f' => $ViewBag->F, 'counts' => $ViewBag->Counts, 'areaCounts' => [], 'type' => 'all', 'total' => (int)$ViewBag->AllCount]); ?>
<?php section_start('Scripts'); ?>
<script>
// সার্চের ফলাফল: ঠিকানা থেকে ?tid= সরানো (রিলোড দিলে আবার সাধারণ পেজ), আর সার্চ বক্স খালি করলে ফলাফল / ভুলের লেখা সাথে সাথে চলে যায়
(function () {
    var inp = document.querySelector('.tl-find input[name="tid"]');
    if (location.search.indexOf('tid=') !== -1 && history.replaceState) history.replaceState(null, '', location.pathname);
    if (!inp) return;
    inp.addEventListener('input', function () {
        if (inp.value.trim() !== '') return;
        var h = document.getElementById('tlHits'); if (h) h.remove();
        var er = document.querySelector('.tl-err'); if (er) er.remove();
    });
})();
</script>
<script>
(function () {
    document.querySelectorAll('[data-track]').forEach(function (b) {
        b.addEventListener('click', function () {
            var t = document.getElementById(b.getAttribute('data-track')), dir = +b.getAttribute('data-dir');
            var atEnd = t.scrollLeft + t.clientWidth >= t.scrollWidth - 4;
            if (dir > 0 && atEnd) t.scrollTo({ left: 0 }); else if (dir < 0 && t.scrollLeft <= 4) t.scrollTo({ left: t.scrollWidth }); else t.scrollBy({ left: dir * t.clientWidth });
        });
    });
    // বাছাই করা জেলার চিপটা দেখা যায় এমন জায়গায়
    (function () { var on = document.querySelector('#tlCities a.on'), r = document.getElementById('tlCities'); if (on && r) r.scrollLeft = on.offsetLeft - r.offsetLeft - 20; })();
    document.querySelectorAll('[data-scroll]').forEach(function (b) {
        b.addEventListener('click', function () { var r = document.getElementById('tlCities'); r.scrollBy({ left: +b.getAttribute('data-scroll') * r.clientWidth * .8 }); });
    });
})();
</script>
<?php section_end(); ?>
