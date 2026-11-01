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
    .efc { background: #fff; border: 1px solid #E3E9F1; border-radius: 16px; padding: 14px 16px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(15,27,45,.05); }
    .efc-top { display: flex; gap: 11px; align-items: flex-start; }
    .efc textarea { flex: 1; border: none; resize: none; outline: none; font-size: 16px; font-family: inherit; color: #0F172A; min-height: 46px; max-height: 320px; padding: 11px 14px; background: #F1F5F9; border-radius: 14px; line-height: 1.5; }
    .efc textarea:focus { background: #EEF6FC; }
    .efc-more[hidden] { display: none; }
    .efc-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 12px; }
    .efc-subj { flex: 1 1 200px; height: 40px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 12px; font-size: 14px; outline: none; font-family: inherit; color: #0F172A; }
    .efc-subj:focus, .efc-yt:focus { border-color: #02B8F5; }
    .efc-yt { width: 100%; height: 40px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 12px; font-size: 14px; outline: none; font-family: inherit; margin-top: 10px; }
    .efc-tools { display: flex; gap: 6px; flex-wrap: wrap; border-top: 1px solid #EEF2F7; margin-top: 12px; padding-top: 10px; align-items: center; }
    .efc-tools label, .efc-tools button.t { display: inline-flex; align-items: center; gap: 7px; padding: 8px 12px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 13.5px; color: #475569; margin: 0; border: none; background: none; font-family: inherit; }
    .efc-tools label:hover, .efc-tools button.t:hover { background: #F1F5F9; }
    .efc-tools .sp { flex: 1; }
    .efc-send { height: 40px; padding: 0 22px; border-radius: 10px; border: none; background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; font-weight: 700; font-size: 14.5px; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 8px; }
    .efc-send:disabled { opacity: .6; cursor: default; }
    .efc-prev { display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: 8px; margin-top: 12px; }
    .efc-prev:empty { display: none; }
    .efc-prev .it { position: relative; aspect-ratio: 1; border-radius: 10px; overflow: hidden; background: #E2E8F0; }
    .efc-prev .it.vid { grid-column: 1 / -1; aspect-ratio: auto; background: #000; }
    .efc-prev img, .efc-prev video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .efc-prev video { max-height: 280px; object-fit: contain; }
    .efc-prev .rm { position: absolute; top: 5px; right: 5px; width: 26px; height: 26px; border-radius: 50%; border: none; background: rgba(15,23,42,.75); color: #fff; cursor: pointer; font-size: 12px; z-index: 2; }
    .efc-note { font-size: 12.5px; color: #64748B; margin-top: 10px; display: flex; gap: 7px; align-items: flex-start; }
    .efc-note i { color: #F5A623; margin-top: 3px; }
    .efc-bar { height: 6px; border-radius: 4px; background: #E2E8F0; overflow: hidden; margin-top: 10px; }
    .efc-bar[hidden] { display: none; }
    .efc-bar span { display: block; height: 100%; width: 0; background: linear-gradient(90deg, #1D7FE5, #02B8F5); transition: width .2s; }
    .efc-login { display: flex; gap: 14px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .efc-login p { margin: 0; color: #334155; font-size: 15px; }
    .efc-login a { padding: 10px 18px; border-radius: 10px; background: #08224A; color: #fff; text-decoration: none; font-weight: 700; font-size: 14px; white-space: nowrap; }

    .efp-filter { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 14px; color: #475569; font-size: 14px; }
    .efp-filter a { color: #0369A1; font-weight: 700; text-decoration: none; }
    .efp-empty { background: #fff; border: 1px dashed #CBD5E1; border-radius: 16px; padding: 44px 20px; text-align: center; color: #64748B; }
    .efp-empty i { font-size: 38px; color: #B9D6E6; display: block; margin-bottom: 12px; }
    .efp-load { display: block; margin: 6px auto 0; padding: 11px 26px; border-radius: 999px; border: 1px solid #DCE5EF; background: #fff; color: #08224A; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; }
    .efp-load:disabled { opacity: .6; }

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
        .efc { padding: 12px; border-radius: 12px; }
        .efc textarea { font-size: 15px; }
        .efc-tools label, .efc-tools button.t { padding: 8px; font-size: 13px; }
        .efc-tools .lbl { display: none; }
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
                <?php if ($me !== null): ?>
                    <form class="efc" id="efc" autocomplete="off" novalidate>
                        <div class="efc-top">
                            <span class="ef-av"><?= e(initials($myName, 'U')) ?><?php if ($myPhoto !== ''): ?><img src="<?= e($myPhoto) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                            <textarea name="content" id="efcText" rows="1" maxlength="5000" placeholder="কী শেখাতে বা শেয়ার করতে চান, <?= e(explode(' ', trim($myName))[0] ?: 'বন্ধু') ?>?" aria-label="পোস্ট লিখুন"></textarea>
                        </div>
                        <div class="efc-more" id="efcMore" hidden>
                            <div class="efc-row">
                                <input class="efc-subj" name="subject" list="efcSubjList" maxlength="60" placeholder="বিষয় (ঐচ্ছিক) — যেমন: Mathematics, Physics" aria-label="বিষয়" />
                                <datalist id="efcSubjList"><?php foreach (array_unique(array_merge($subjects, $opts['subjects'] ?? [])) as $s): ?><option value="<?= e($s) ?>"></option><?php endforeach; ?></datalist>
                            </div>
                            <input class="efc-yt" name="youtube" id="efcYt" maxlength="200" placeholder="YouTube ভিডিওর লিংক — https://youtu.be/..." hidden aria-label="YouTube লিংক" />
                            <div class="efc-prev" id="efcPrev"></div>
                            <div class="efc-bar" id="efcBar" hidden><span></span></div>
                            <?php if (!$isAdmin): ?><div class="efc-note"><i class="fas fa-shield-halved"></i><span>পোস্টটি আগে অ্যাডমিনের কাছে রিভিউর জন্য যাবে — Approve হলে সবাই দেখতে পাবে। শুধু শিক্ষামূলক পোস্ট দিন।</span></div><?php endif; ?>
                        </div>
                        <div class="efc-tools">
                            <label title="ছবি (সর্বোচ্চ ৬টা, প্রতিটা ৫MB)"><i class="fas fa-images" style="color:#22C55E;"></i><span class="lbl">Photo</span><input type="file" id="efcImg" accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden /></label>
                            <label title="ভিডিও (MP4/WEBM, সর্বোচ্চ <?= $videoMb ?>MB)"><i class="fas fa-video" style="color:#EF4444;"></i><span class="lbl">Video</span><input type="file" id="efcVid" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v" hidden /></label>
                            <button type="button" class="t" id="efcYtBtn" title="YouTube লিংক"><i class="fab fa-youtube" style="color:#FF0000;"></i><span class="lbl">YouTube</span></button>
                            <span class="sp"></span>
                            <button type="submit" class="efc-send" id="efcSend"><i class="fas fa-paper-plane"></i> <?= $isAdmin ? 'Post' : 'Submit' ?></button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="efc efc-login">
                        <p><i class="fas fa-pen-to-square" style="color:#02B8F5;"></i> নিজের নোট, ছবি বা ভিডিও শেয়ার করতে লগইন করুন।</p>
                        <a href="/Account/Login?returnUrl=<?= rawurlencode('/Feed') ?>">Login</a>
                    </div>
                <?php endif; ?>

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
                        <li>লেখা, ছবি, ভিডিও বা YouTube লিংক দিয়ে পোস্ট করুন।</li>
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

    // ===== কম্পোজার =====
    var form = document.getElementById('efc'); if (!form) return;
    var ta = document.getElementById('efcText'), moreBox = document.getElementById('efcMore'), prev = document.getElementById('efcPrev');
    var imgIn = document.getElementById('efcImg'), vidIn = document.getElementById('efcVid'), yt = document.getElementById('efcYt'), ytBtn = document.getElementById('efcYtBtn');
    var send = document.getElementById('efcSend'), bar = document.getElementById('efcBar');
    var VIDEO_MAX = <?= (int)$ViewBag->VideoLimit ?>, IMG_MAX = 5 * 1024 * 1024, MAX_IMGS = 6, TAB = <?= js($tab) ?>;
    var images = [], video = null, urls = [];
    function expand() { moreBox.hidden = false; }
    ta.addEventListener('focus', expand);
    ta.addEventListener('input', function () { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 320) + 'px'; });
    function clearUrls() { urls.forEach(URL.revokeObjectURL); urls = []; }
    function draw() {
        clearUrls(); prev.innerHTML = '';
        images.forEach(function (f, i) {
            var u = URL.createObjectURL(f); urls.push(u);
            prev.insertAdjacentHTML('beforeend', '<div class="it"><img src="' + u + '" alt="" /><button type="button" class="rm" data-rm="img" data-i="' + i + '" aria-label="সরান"><i class="fas fa-xmark"></i></button></div>');
        });
        if (video) {
            var vu = URL.createObjectURL(video); urls.push(vu);
            prev.insertAdjacentHTML('beforeend', '<div class="it vid"><video src="' + vu + '" controls muted playsinline></video><button type="button" class="rm" data-rm="vid" aria-label="সরান"><i class="fas fa-xmark"></i></button></div>');
        }
    }
    function only(kind) {
        // ছবি / ভিডিও / YouTube — যেকোনো একটা
        if (kind !== 'img' && images.length) { images = []; }
        if (kind !== 'vid') video = null;
        if (kind !== 'yt') { yt.value = ''; yt.hidden = true; }
    }
    prev.addEventListener('click', function (e) {
        var b = e.target.closest('[data-rm]'); if (!b) return;
        if (b.getAttribute('data-rm') === 'vid') video = null; else images.splice(+b.getAttribute('data-i'), 1);
        draw();
    });
    imgIn.addEventListener('change', function () {
        var files = Array.prototype.slice.call(imgIn.files); imgIn.value = ''; if (!files.length) return;
        expand(); only('img');
        for (var i = 0; i < files.length; i++) {
            var f = files[i];
            if (!/^image\/(jpeg|png|gif|webp)$/.test(f.type)) { EF.toast('"' + f.name + '" — শুধু JPG, PNG, GIF বা WEBP ছবি।', true); continue; }
            if (f.size > IMG_MAX) { EF.toast('"' + f.name + '" ৫MB এর বেশি বড়।', true); continue; }
            if (images.length >= MAX_IMGS) { EF.toast('সর্বোচ্চ ' + MAX_IMGS + 'টা ছবি দেওয়া যাবে।', true); break; }
            images.push(f);
        }
        draw();
    });
    vidIn.addEventListener('change', function () {
        var f = vidIn.files[0]; vidIn.value = ''; if (!f) return;
        if (f.size > VIDEO_MAX) { EF.toast('ভিডিও ' + Math.floor(VIDEO_MAX / 1048576) + 'MB এর বেশি বড়। বড় ভিডিও YouTube-এ দিয়ে লিংক শেয়ার করুন।', true); return; }
        expand(); only('vid'); video = f; draw();
    });
    ytBtn.addEventListener('click', function () { expand(); only('yt'); yt.hidden = false; draw(); yt.focus(); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = ta.value.trim();
        if (!text && !images.length && !video && !yt.value.trim()) { ta.focus(); EF.toast('কিছু লিখুন অথবা ছবি/ভিডিও দিন।', true); return; }
        var fd = new FormData();
        fd.append('content', text); fd.append('subject', form.subject.value.trim()); fd.append('youtube', yt.hidden ? '' : yt.value.trim());
        images.forEach(function (f) { fd.append('images[]', f, f.name); });
        if (video) fd.append('video', video, video.name);
        fd.append('__RequestVerificationToken', EF.token());
        send.disabled = true; send.innerHTML = '<i class="fas fa-spinner fa-spin"></i> পাঠানো হচ্ছে';
        var hasFiles = images.length || video; bar.hidden = !hasFiles; bar.firstElementChild.style.width = '0';
        // XHR — যাতে আপলোডের অগ্রগতি দেখানো যায়
        var x = new XMLHttpRequest();
        x.open('POST', '/Feed/Create'); x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        x.upload.onprogress = function (ev) { if (ev.lengthComputable) bar.firstElementChild.style.width = Math.round(ev.loaded / ev.total * 100) + '%'; };
        x.onload = function () {
            var r; try { r = JSON.parse(x.responseText); } catch (er) { r = { success: false, message: x.status === 413 ? 'ফাইল খুব বড় — ছোট ফাইল দিন।' : 'সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।' }; }
            if (!r.success && x.status === 400 && hasFiles) r.message = 'ফাইলগুলো সার্ভারের সীমার চেয়ে বড় হতে পারে — ছোট ফাইল দিয়ে আবার চেষ্টা করুন।';
            done(r);
        };
        x.onerror = function () { done({ success: false, message: 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।' }); };
        x.send(fd);
    });
    function done(r) {
        send.disabled = false; send.innerHTML = '<i class="fas fa-paper-plane"></i> ' + <?= js($isAdmin ? 'Post' : 'Submit') ?>; bar.hidden = true;
        if (!r.success) { EF.toast(r.message || 'পোস্ট করা যায়নি।', true); return; }
        EF.toast(r.message);
        ta.value = ''; ta.style.height = ''; form.subject.value = ''; images = []; video = null; only('none'); draw(); moreBox.hidden = true;
        if (r.status === 'Approved' || TAB === 'mine') { list.insertAdjacentHTML('afterbegin', r.html); empty.hidden = true; }
        else {
            var n = document.createElement('div'); n.className = 'ef-status Pending'; n.style.margin = '0 0 16px';
            n.innerHTML = '<i class="fas fa-hourglass-half"></i><span>আপনার পোস্ট রিভিউতে আছে। <a href="/Feed?tab=mine" style="color:inherit;font-weight:800;">My Posts</a>-এ দেখতে পাবেন।</span>';
            form.after(n);
        }
    }
})();
</script>
<?php section_end(); ?>
