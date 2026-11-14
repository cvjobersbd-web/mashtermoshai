<?php
/**
 * Tutor List (Tutor Hub) — পরিসংখ্যান, Tutor ID দিয়ে খোঁজা, জেলা, আর সেকশন আকারে টিউটর (প্রতিটার "See All")
 * @var array $Model  ['all' => ['title', 'tutors'], 'premium' => ..., ...]
 */
$sections = is_array($Model) ? $Model : [];
$cities = is_array($ViewBag->Cities) ? $ViewBag->Cities : [];
$ViewData['Title'] = 'Tutor List';
$ViewData['FullTitle'] = 'Tutor List — যাচাই করা টিউটর খুঁজুন | মাষ্টার মশাই';
$ViewData['Description'] = 'মাষ্টার মশাই এর অনুমোদিত সব টিউটরের তালিকা — জেলা, প্রতিষ্ঠান আর প্রোফাইল দেখে পছন্দের টিউটর বেছে নিন।';
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
                        <?php foreach ($cities as $c => $n): ?><a href="/Tutors/All?city[]=<?= rawurlencode($c) ?>"><?= e($c) ?> (<?= (int)$n ?>)</a><?php endforeach; ?>
                    </div>
                    <button type="button" class="tl-arrow r" data-scroll="1" aria-label="পরের"><i class="fas fa-chevron-right"></i></button>
                </div>
            <?php endif; ?>
            <div class="tl-stats">
                <div class="tl-stat"><i class="fas fa-user-group"></i><div><b><?= number_format((int)$ViewBag->Total) ?></b><span>Total Tutors</span></div></div>
                <div class="tl-stat"><i class="fas fa-person"></i><div><b><?= number_format((int)$ViewBag->Male) ?></b><span>Male Tutors</span></div></div>
                <div class="tl-stat"><i class="fas fa-person-dress"></i><div><b><?= number_format((int)$ViewBag->Female) ?></b><span>Female Tutors</span></div></div>
            </div>
            <form class="tl-find" action="/Tutors" method="get" role="search">
                <i class="fas fa-magnifying-glass"></i>
                <input name="tid" value="<?= e($ViewBag->Tid) ?>" maxlength="60" placeholder="Tutor ID বা নাম দিয়ে খুঁজুন — যেমন TCH-1024 বা Rahim" aria-label="Tutor ID বা নাম" />
                <button type="submit">Search</button>
            </form>
            <?php if ($ViewBag->TidError !== ''): ?><div class="tl-err"><?= e($ViewBag->TidError) ?></div><?php endif; ?>
        </div>

        <?php if (!empty($ViewBag->NameHits)): ?>
            <section class="tl-sec" id="tlHits">
                <div class="tl-h"><h2>“<?= e($ViewBag->Tid) ?>” নামে <?= count($ViewBag->NameHits) ?> জন টিউটর</h2><a class="tl-btn" href="/Tutors">সব দেখুন <i class="fas fa-xmark"></i></a></div>
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
                    <a class="tl-btn" href="/Tutors/All?type=<?= e($key) ?>">See All <i class="fas fa-caret-right"></i></a>
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

<?php section_start('Scripts'); ?>
<script>
(function () {
    document.querySelectorAll('[data-track]').forEach(function (b) {
        b.addEventListener('click', function () {
            var t = document.getElementById(b.getAttribute('data-track')), dir = +b.getAttribute('data-dir');
            var atEnd = t.scrollLeft + t.clientWidth >= t.scrollWidth - 4;
            if (dir > 0 && atEnd) t.scrollTo({ left: 0 }); else if (dir < 0 && t.scrollLeft <= 4) t.scrollTo({ left: t.scrollWidth }); else t.scrollBy({ left: dir * t.clientWidth });
        });
    });
    document.querySelectorAll('[data-scroll]').forEach(function (b) {
        b.addEventListener('click', function () { var r = document.getElementById('tlCities'); r.scrollBy({ left: +b.getAttribute('data-scroll') * r.clientWidth * .8 }); });
    });
})();
</script>
<?php section_end(); ?>
