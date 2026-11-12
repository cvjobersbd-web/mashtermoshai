<?php
/**
 * Educational Feed — শিক্ষামূলক পোস্ট (ছবি, ভিডিও, YouTube, নোট)।
 * ইউজার/টিচারের পোস্ট আগে অ্যাডমিনের কাছে যায় → Approve হলে এখানে দেখায়।
 */
$posts = is_array($Model) ? $Model : [];
$tab = $ViewBag->Tab ?? 'all';
$subject = (string)($ViewBag->Subject ?? '');
$q = (string)($ViewBag->Q ?? '');
$subjects = is_array($ViewBag->Subjects) ? $ViewBag->Subjects : [];
$me = current_user_id();
$isAdmin = is_admin();
$videoMb = max(1, (int)floor(((int)$ViewBag->VideoLimit) / 1048576));
$opts = function_exists('tuition_options') ? tuition_options() : ['subjects' => []];
$myName = (string)(session_get('UserFullName') ?? session_get('UserName') ?? '');
$myPhoto = '';
if ($me !== null) { try { $u = db()->GetUserById($me); $myPhoto = (string)($u->ProfileImageLink ?? ''); } catch (Throwable $e) { } }

$ViewData['Title'] = 'Educational Feed';
$ViewData['FullTitle'] = ($subject !== '' ? $subject . ' — ' : '') . 'Educational Feed | মাষ্টার মশাই';
$ViewData['Description'] = 'Educational Feed — শিক্ষক ও শিক্ষার্থীদের শেয়ার করা নোট, ছবি, ভিডিও, টিপস আর শিক্ষামূলক পোস্ট। মাষ্টার মশাই-এ প্রতিটা পোস্ট রিভিউ করে প্রকাশ করা হয়।';
if ($tab === 'mine' || $q !== '') $ViewData['Robots'] = 'noindex, follow';
$baseQs = function (array $over) use ($subject, $q, $tab) {
    $a = array_filter(array_merge(['tab' => $tab === 'mine' ? 'mine' : '', 'subject' => $subject, 'q' => $q], $over), fn($v) => $v !== '' && $v !== null);
    return '/Feed' . ($a ? '?' . http_build_query($a) : '');
};
?>
<?php partial('Feed/_Styles'); ?>
<style>
    .efp { background: #F0F3F8; min-height: 80vh; padding: 0 0 60px; }
    .efp-hero { background: radial-gradient(circle at 85% 0%, rgba(2,184,245,.35), transparent 45%), linear-gradient(120deg, #08224A 0%, #134856 70%, #0F6E86 100%); color: #fff; padding: 30px 0 34px; }
    .efp-wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
    .efp-hero h1 { margin: 0; font-family: var(--font-heading); font-size: clamp(26px, 3.6vw, 38px); font-weight: 700; color: #fff; display: flex; gap: 12px; align-items: center; }
    .efp-hero p { margin: 8px 0 0; opacity: .88; font-size: 15px; max-width: 680px; }
    .efp-grid { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 24px; margin-top: -18px; align-items: start; }
    .efp-main { min-width: 0; max-width: 680px; width: 100%; justify-self: center; }

    .efp-tabs { display: flex; gap: 6px; background: #fff; border: 1px solid #E3E9F1; border-radius: 14px; padding: 6px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(15,27,45,.05); }
    .efp-tabs a { flex: 1; text-align: center; padding: 10px; border-radius: 10px; color: #475569; font-weight: 700; font-size: 14.5px; text-decoration: none; display: flex; gap: 8px; justify-content: center; align-items: center; }
    .efp-tabs a.on { background: #08224A; color: #fff; }
    .efp-tabs a:not(.on):hover { background: #F1F5F9; }
    .efp-tabs .b { background: #F5A623; color: #fff; font-size: 11px; padding: 1px 7px; border-radius: 9px; }

    /* কম্পোজার */

    .efp-filter { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 14px; color: #475569; font-size: 14px; }
    .efp-filter a { color: #0369A1; font-weight: 700; text-decoration: none; }

    /* ডান পাশ */
    .efp-side { position: sticky; top: 96px; display: flex; flex-direction: column; gap: 16px; }
    .efs { background: #fff; border: 1px solid #E3E9F1; border-radius: 16px; padding: 16px 18px; box-shadow: 0 2px 12px rgba(15,27,45,.05); }
    .efs h3 { margin: 0 0 12px; font-size: 16px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .efs form { position: relative; }
    .efs form i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 13px; }
    .efs input { width: 100%; height: 40px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 12px 0 34px; font-size: 14px; outline: none; font-family: inherit; }
    .efs input:focus { border-color: #02B8F5; }
    .efs-chips { display: flex; gap: 7px; flex-wrap: wrap; }
    .efs-chips a { padding: 6px 12px; border-radius: 999px; background: #F1F5F9; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; }
    .efs-chips a:hover { background: #E0F2FE; color: #0369A1; }
    .efs-chips a.on { background: #08224A; color: #fff; }
    .efs ol { margin: 0; padding-left: 20px; color: #475569; font-size: 13.5px; line-height: 1.6; display: flex; flex-direction: column; gap: 6px; }
    .efs ul { margin: 0; padding: 0; list-style: none; color: #475569; font-size: 13.5px; line-height: 1.5; display: flex; flex-direction: column; gap: 7px; }
    .efs ul li { display: flex; gap: 8px; } .efs ul li i { margin-top: 3px; width: 14px; flex-shrink: 0; }
    .efp-mchips { display: none; }
    @media (max-width: 960px) {
        .efp-grid { grid-template-columns: minmax(0, 1fr); margin-top: -14px; }
        .efp-side { display: none; }
        .efp-mchips { display: flex; gap: 7px; overflow-x: auto; scrollbar-width: none; margin-bottom: 12px; padding-bottom: 2px; }
        .efp-mchips::-webkit-scrollbar { display: none; }
        .efp-mchips a { flex-shrink: 0; padding: 7px 13px; border-radius: 999px; background: #fff; border: 1px solid #DCE5EF; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; }
        .efp-mchips a.on { background: #08224A; color: #fff; border-color: #08224A; }
    }
    @media (max-width: 640px) {
        .efp-wrap { padding: 0 10px; }
        .efp-hero { padding: 22px 0 28px; } .efp-hero p { font-size: 14px; }
        .efp-tabs a { font-size: 13.5px; padding: 9px 6px; }
    }
</style>

<div class="efp">
    <section class="efp-hero">
        <div class="efp-wrap">
            <h1><i class="fas fa-graduation-cap"></i> Educational Feed</h1>
            <p>শিক্ষক আর শিক্ষার্থীদের শেয়ার করা নোট, ছবি, ভিডিও আর পড়াশোনার টিপস। প্রতিটা পোস্ট অ্যাডমিন রিভিউ করার পর এখানে প্রকাশ হয়।</p>
        </div>
    </section>

    <div class="efp-wrap">
        <div class="efp-grid">
            <main class="efp-main">
                <?php if ($me !== null): ?>
                    <nav class="efp-tabs" aria-label="Feed tabs">
                        <a href="/Feed" class="<?= $tab === 'all' ? 'on' : '' ?>"><i class="fas fa-earth-asia"></i> All Posts</a>
                        <a href="/Feed?tab=mine" class="<?= $tab === 'mine' ? 'on' : '' ?>"><i class="fas fa-user-pen"></i> My Posts<?php if ((int)$ViewBag->MyPending): ?><span class="b" title="রিভিউতে আছে"><?= (int)$ViewBag->MyPending ?></span><?php endif; ?></a>
                    </nav>
                <?php endif; ?>

                <!-- ===== কম্পোজার ===== -->
                <?php partial('Feed/_Composer', ['me' => $me, 'isAdmin' => $isAdmin, 'myName' => $myName, 'myPhoto' => $myPhoto, 'videoMb' => $videoMb, 'subjects' => $subjects, 'opts' => $opts, 'returnUrl' => '/Feed']); ?>
                <?php if ($me !== null && !$isAdmin): ?><div style="font-size:12.5px;color:#64748B;margin:-6px 0 12px;padding:0 4px;"><i class="fas fa-circle-info" style="color:#00B5E0;"></i> আপনার পোস্ট অ্যাডমিন দেখে Approve করলে ফিডে দেখাবে।</div><?php endif; ?>

                <?php if ($tab === 'all' && $subjects): ?>
                    <nav class="efp-mchips" aria-label="Subjects">
                        <a href="/Feed" class="<?= $subject === '' ? 'on' : '' ?>">All</a>
                        <?php foreach ($subjects as $s): ?><a href="/Feed?subject=<?= rawurlencode($s) ?>" class="<?= $s === $subject ? 'on' : '' ?>"><?= e($s) ?></a><?php endforeach; ?>
                    </nav>
                <?php endif; ?>
                <?php if ($tab === 'all' && ($subject !== '' || $q !== '')): ?>
                    <div class="efp-filter"><i class="fas fa-filter"></i>
                        <?php if ($subject !== ''): ?>বিষয়: <b><?= e($subject) ?></b><?php endif; ?>
                        <?php if ($q !== ''): ?>খোঁজ: <b>“<?= e($q) ?>”</b><?php endif; ?>
                        · <a href="/Feed">সব পোস্ট দেখুন</a></div>
                <?php endif; ?>

                <div id="efList">
                    <?php foreach ($posts as $p) partial('Feed/_Post', ['p' => $p, 'me' => $me]); ?>
                </div>
                <div class="efp-empty" id="efEmpty"<?= $posts ? ' hidden' : '' ?>>
                    <i class="fas <?= $tab === 'mine' ? 'fa-pen-to-square' : 'fa-newspaper' ?>"></i>
                    <?php if ($tab === 'mine'): ?>আপনি এখনো কোনো পোস্ট দেননি। উপরে লিখে প্রথম পোস্টটা দিন!
                    <?php elseif ($subject !== '' || $q !== ''): ?>এই ফিল্টারে কোনো পোস্ট পাওয়া যায়নি।
                    <?php else: ?>এখনো কোনো পোস্ট প্রকাশ হয়নি। প্রথম শিক্ষামূলক পোস্টটা আপনিই দিন!<?php endif; ?>
                </div>
                <?php if ($ViewBag->HasMore): ?><button type="button" class="efp-load" id="efLoad">আরো পোস্ট দেখুন</button><?php endif; ?>
            </main>

            <aside class="efp-side">
                <div class="efs">
                    <h3><i class="fas fa-magnifying-glass" style="color:#02B8F5;"></i> খুঁজুন</h3>
                    <form action="/Feed" method="get" role="search"><i class="fas fa-magnifying-glass"></i><input type="search" name="q" value="<?= e($q) ?>" maxlength="60" placeholder="বিষয়, লেখা বা নাম..." aria-label="Feed খুঁজুন" /></form>
                </div>
                <?php if ($subjects): ?>
                    <div class="efs">
                        <h3><i class="fas fa-book-open" style="color:#22C55E;"></i> বিষয় অনুযায়ী</h3>
                        <div class="efs-chips">
                            <a href="/Feed" class="<?= $subject === '' && $tab === 'all' ? 'on' : '' ?>">All</a>
                            <?php foreach ($subjects as $s): ?><a href="/Feed?subject=<?= rawurlencode($s) ?>" class="<?= $s === $subject ? 'on' : '' ?>"><?= e($s) ?></a><?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="efs">
                    <h3><i class="fas fa-circle-info" style="color:#F5A623;"></i> কীভাবে কাজ করে</h3>
                    <ol>
                        <li><?= $isAdmin ? 'লেখা, ছবি, ভিডিও বা YouTube লিংক দিয়ে পোস্ট করুন।' : 'লেখা আর ছবি দিয়ে পোস্ট করুন।' ?></li>
                        <li>অ্যাডমিন পোস্টটি রিভিউ করবেন।</li>
                        <li>Approve হলে ফিডে সবাই দেখবে — নোটিফিকেশনও পাবেন।</li>
                    </ol>
                </div>
                <div class="efs">
                    <h3><i class="fas fa-shield-halved" style="color:#1D63E0;"></i> পোস্টের নিয়ম</h3>
                    <ul>
                        <li><i class="fas fa-check" style="color:#22C55E;"></i>নোট, টিপস, প্রশ্ন সমাধান, শিক্ষামূলক ভিডিও</li>
                        <li><i class="fas fa-check" style="color:#22C55E;"></i>নিজের তৈরি বা শেয়ার করার অনুমতি আছে এমন কনটেন্ট</li>
                        <li><i class="fas fa-xmark" style="color:#EF4444;"></i>বিজ্ঞাপন, ফোন নম্বর, অপ্রাসঙ্গিক বা আপত্তিকর কিছু নয়</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php partial('Feed/_Js'); ?>
<?php section_start('Scripts'); ?>
<script>
(function () {
    // ===== আরো পোস্ট =====
    var list = document.getElementById('efList'), more = document.getElementById('efLoad'), empty = document.getElementById('efEmpty');
    var QS = <?= js(['tab' => $tab === 'mine' ? 'mine' : '', 'subject' => $subject, 'q' => $q]) ?>;
    if (more) more.addEventListener('click', async function () {
        var last = list.querySelector('[data-post]:last-child'); if (!last) return;
        more.disabled = true; more.textContent = 'লোড হচ্ছে...';
        var p = new URLSearchParams(QS); p.set('before', last.getAttribute('data-post'));
        try {
            var r = await fetch('/Feed/More?' + p.toString());
            list.insertAdjacentHTML('beforeend', await r.text());
            if (r.headers.get('X-Has-More') !== '1') more.remove(); else { more.disabled = false; more.textContent = 'আরো পোস্ট দেখুন'; }
        } catch (e) { more.disabled = false; more.textContent = 'আবার চেষ্টা করুন'; }
    });
    document.addEventListener('ef:removed', function () { empty.hidden = !!list.querySelector('[data-post]'); });

})();
</script>
<?php partial('Feed/_ComposerJs', ['videoLimit' => (int)$ViewBag->VideoLimit, 'isAdmin' => $isAdmin, 'tab' => $tab]); ?>
<?php section_end(); ?>
