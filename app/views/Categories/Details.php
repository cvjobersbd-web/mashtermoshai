<?php
/**
 * একটা ক্যাটাগরির পেজ: ব্যানার → About This Category + Why Choose (Hire A Tutor) → Suggested Tutors → Related Categories
 * লেখাগুলো Admin → Categories থেকে এডিট করা যায় (খালি থাকলে ডিফল্ট লেখা দেখায়)।
 */
$c = $Model;
$tx = $ViewBag->Texts ?? category_texts($c);
$tutors = is_array($ViewBag->Tutors) ? $ViewBag->Tutors : [];
$tutorTotal = (int)($ViewBag->TutorTotal ?? 0);
$related = is_array($ViewBag->Related) ? $ViewBag->Related : [];
$brand = 'মাষ্টার মশাই';
$kind = category_kind((string)$c->GroupName);
$label = $kind === 'school' ? trim($c->GroupName . ' ' . $c->Name) : $c->Name;

$ViewData['Title'] = $c->Name;
$ViewData['FullTitle'] = $label . ' Tutor in Bangladesh | ' . $brand;
$ViewData['Description'] = mb_substr('Find experienced ' . $label . ' tutors in Bangladesh with ' . $brand . '. ' . implode(' ', $tx['Highlights']), 0, 300);
$ViewData['Canonical'] = 'https://mashtermoshai.com' . $c->Url;
if ($c->ImageUrl !== '') $ViewData['OgImage'] = $c->ImageUrl;

$isLoggedIn = session_get('UserName') !== null;
$hireHref = $isLoggedIn ? '/Tuition/Circular?post=1' : '/Account/Login?returnUrl=' . rawurlencode('/Tuition/Circular?post=1');
$seeAll = '/FindTutor?cat=' . rawurlencode($c->Slug);
[$tintBg, $tintFg] = category_tint($c->Name);
?>
<?php partial('Categories/_CardStyles'); ?>
<style>
    .cd { background: #F4F6FA; padding-bottom: 70px; color: #1E293B; }
    .cd-wrap { max-width: 1240px; margin: 0 auto; padding: 0 20px; }

    /* ===== ব্যানার ===== */
    .cd-hero { position: relative; overflow: hidden; color: #fff; min-height: 280px; display: flex; align-items: center;
        background: radial-gradient(circle at 85% 20%, rgba(2,184,245,.35), transparent 45%), linear-gradient(120deg, #08224A 0%, #134856 70%, #0F6E86 100%); }
    .cd-hero.has-img { background: linear-gradient(90deg, rgba(8,34,74,.88) 0%, rgba(8,34,74,.72) 45%, rgba(8,34,74,.25) 100%), var(--cd-banner) center / cover no-repeat; }
    .cd-hero-in { position: relative; z-index: 1; width: 100%; max-width: 1240px; margin: 0 auto; padding: 40px 20px; display: flex; align-items: center; justify-content: space-between; gap: 30px; }
    .cd-crumb { font-size: 13px; opacity: .85; margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .cd-crumb a { color: #fff; text-decoration: none; }
    .cd-crumb a:hover { text-decoration: underline; }
    .cd-hero h1 { margin: 0; font-family: var(--font-heading); font-size: clamp(30px, 4.4vw, 50px); font-weight: 700; color: #fff; line-height: 1.15; }
    .cd-hero .lead { margin: 8px 0 12px; font-size: 15px; opacity: .9; }
    .cd-hl { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
    .cd-hl li { font-size: clamp(16px, 1.8vw, 21px); font-weight: 500; display: flex; gap: 10px; align-items: baseline; }
    .cd-hl li::before { content: '\2713'; font-weight: 800; color: #FFD166; }
    .cd-hero-art { flex-shrink: 0; width: 230px; height: 230px; border-radius: 26px; background: rgba(255,255,255,.95); display: flex; align-items: center; justify-content: center; padding: 16px;
        box-shadow: 0 18px 40px rgba(0,0,0,.25); }
    .cd-hero-art img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .cd-hero-art .ic { width: 150px; height: 150px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 62px; }

    /* ===== About + Why ===== */
    .cd-two { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 26px; margin-top: 30px; align-items: stretch; }
    .cd-box { background: #fff; border: 1px solid #E3E9F1; border-radius: 10px; padding: 26px 28px 22px; box-shadow: 0 2px 10px rgba(15,27,45,.05); display: flex; flex-direction: column; }
    .cd-box > h2 { margin: 0 0 18px; font-size: 24px; font-weight: 500; color: #1E293B; font-family: var(--font-heading); }
    .cd-rt { font-size: 14.5px; line-height: 1.75; color: #334155; flex: 1; text-align: justify; }
    .cd-rt h3 { font-size: 15.5px; font-weight: 700; color: #0F172A; margin: 18px 0 4px; text-align: left; }
    .cd-rt h3:first-child { margin-top: 0; }
    .cd-rt p { margin: 0 0 10px; }
    .cd-rt strong { color: #0F172A; }
    .cd-rt ul { list-style: none; margin: 4px 0 12px; padding: 0; display: flex; flex-direction: column; gap: 6px; text-align: left; }
    .cd-rt ul li { display: flex; gap: 10px; align-items: flex-start; }
    .cd-rt ul li::before { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; flex-shrink: 0; width: 18px; height: 18px; border-radius: 4px; margin-top: 4px;
        background: #22C55E; color: #fff; font-size: 10px; display: inline-flex; align-items: center; justify-content: center; }
    .cd-box.why .cd-rt ul { gap: 12px; margin: 10px 0 18px; }
    .cd-foot { display: flex; justify-content: flex-end; margin-top: 18px; }
    .cd-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 40px; padding: 0 18px; border-radius: 6px; border: none; cursor: pointer;
        background: #1E293B; color: #fff; font-size: 14px; font-weight: 600; text-decoration: none; transition: background .2s, transform .15s; font-family: inherit; }
    .cd-btn:hover { background: #0F172A; color: #fff; transform: translateY(-1px); }
    .cd-btn.sky { background: #02B8F5; } .cd-btn.sky:hover { background: #0099D6; }

    /* ===== সেকশন শিরোনাম + ক্যারোসেল ===== */
    .cd-sec { margin-top: 56px; }
    .cd-sec-h { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-bottom: 18px; }
    .cd-sec-h h2 { margin: 0; font-family: var(--font-heading); font-size: clamp(22px, 2.6vw, 32px); font-weight: 500; color: #1E293B; }
    .cd-sec-h small { color: #64748B; font-size: 14px; }
    .cd-track { display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - 3 * 22px) / 4); gap: 22px; overflow-x: auto; scroll-snap-type: x mandatory;
        scroll-behavior: smooth; scrollbar-width: none; padding: 4px 2px 10px; }
    .cd-track::-webkit-scrollbar { display: none; }
    .cd-track > * { scroll-snap-align: start; }
    .cd-nav { display: flex; justify-content: center; gap: 10px; margin-top: 14px; }
    .cd-nav button { width: 32px; height: 32px; border-radius: 50%; border: none; background: #CBD5E1; color: #fff; cursor: pointer; font-size: 12px; transition: background .2s; }
    .cd-nav button:hover { background: #64748B; }
    .cd-nav[hidden] { display: none; }

    /* টিউটর কার্ড */
    .cd-tutor { background: #fff; border: 1px solid #E3E9F1; border-radius: 6px; text-align: center; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 2px 8px rgba(15,27,45,.05); }
    .cd-tutor-b { padding: 18px 12px 14px; flex: 1; display: flex; flex-direction: column; align-items: center; }
    .cd-av { position: relative; width: 74px; height: 74px; border-radius: 50%; border: 3px solid #02B8F5; padding: 2px; }
    .cd-av span { width: 100%; height: 100%; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 700; font-size: 22px; }
    .cd-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .cd-badge { margin-top: 6px; color: #1D63E0; font-size: 15px; height: 18px; }
    .cd-tutor b { font-size: 16px; color: #1E293B; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cd-tutor small { display: block; color: #64748B; font-size: 12.5px; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 3px; }
    .cd-tutor .loc { color: #475569; font-size: 12.5px; margin-top: 6px; }
    .cd-tutor .loc i { color: #02B8F5; margin-right: 4px; }
    .cd-tutor .stars { color: #F5A623; font-size: 12px; margin-top: 4px; }
    .cd-see { display: block; background: #1E293B; color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 10px; transition: background .2s; }
    .cd-see:hover { background: #0F172A; color: #fff; }
    .cd-empty { background: #fff; border: 1px dashed #CBD5E1; border-radius: 10px; padding: 36px 18px; text-align: center; color: #64748B; }
    .cd-empty i { font-size: 34px; color: #B9D6E6; display: block; margin-bottom: 10px; }

    /* নিচের CTA */
    .cd-cta { margin-top: 56px; background: #fff; border: 1px solid #E3E9F1; border-radius: 10px; padding: 28px 22px; text-align: center; box-shadow: 0 2px 10px rgba(15,27,45,.05); }
    .cd-cta .cd-btn { height: 46px; padding: 0 30px; border-radius: 999px; font-size: 15px; }
    .cd-cta p { margin: 16px auto 0; max-width: 720px; border: 1px solid #E3E9F1; border-radius: 6px; padding: 10px 14px; color: #475569; font-size: 14px; }
    .cd-cta p a { color: #028EA5; font-weight: 600; }
    .cd-toast { position: fixed; left: 50%; bottom: 24px; transform: translate(-50%, 20px); opacity: 0; background: #08224A; color: #fff; padding: 11px 18px; border-radius: 10px;
        font-size: 14px; transition: opacity .25s, transform .25s; pointer-events: none; z-index: 3000; }
    .cd-toast.show { opacity: 1; transform: translate(-50%, 0); }
    .cd-off { background: #FEF3C7; color: #92400E; text-align: center; padding: 10px; font-size: 14px; font-weight: 600; }

    @media (max-width: 1024px) {
        .cd-track { grid-auto-columns: calc((100% - 2 * 18px) / 3); gap: 18px; }
        .cd-hero-art { width: 180px; height: 180px; }
    }
    @media (max-width: 800px) {
        .cd-two { grid-template-columns: minmax(0, 1fr); }
        .cd-hero-art { display: none; }
    }
    @media (max-width: 640px) {
        .cd-wrap, .cd-hero-in { padding-left: 12px; padding-right: 12px; }
        .cd-hero { min-height: 230px; }
        .cd-hero-in { padding-top: 28px; padding-bottom: 28px; }
        .cd-box { padding: 20px 16px 16px; }
        .cd-box > h2 { font-size: 21px; }
        .cd-rt { text-align: left; }
        .cd-track { grid-auto-columns: calc((100% - 12px) / 2); gap: 12px; }
        .cd-sec { margin-top: 40px; }
    }
</style>

<div class="cd">
    <?php if (!$c->IsActive || !(int)($c->GroupActive ?? 1)): ?>
        <div class="cd-off"><i class="fas fa-eye-slash"></i> এই ক্যাটাগরিটা এখন বন্ধ — শুধু অ্যাডমিন হিসেবে আপনি দেখতে পাচ্ছেন।</div>
    <?php endif; ?>

    <!-- ===== ব্যানার ===== -->
    <section class="cd-hero<?= $c->BannerUrl !== '' ? ' has-img' : '' ?>"<?= $c->BannerUrl !== '' ? ' style="--cd-banner:url(\'' . e($c->BannerUrl) . '\')"' : '' ?>>
        <div class="cd-hero-in">
            <div>
                <nav class="cd-crumb" aria-label="Breadcrumb">
                    <a href="/">Home</a><i class="fas fa-angle-right"></i><a href="/Categories">Categories</a><i class="fas fa-angle-right"></i>
                    <a href="/Categories#grp-<?= (int)$c->GroupId ?>"><?= e($c->GroupName) ?></a>
                </nav>
                <h1><?= e($c->Name) ?></h1>
                <?php if ($tx['Highlights']): ?>
                    <p class="lead">What you will learn in this category</p>
                    <ul class="cd-hl">
                        <?php foreach ($tx['Highlights'] as $h): ?><li><?= e(ltrim($h, "✓✔- \t")) ?></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php if ($c->BannerUrl === ''): ?>
                <div class="cd-hero-art" aria-hidden="true">
                    <?php if ($c->ImageUrl !== ''): ?><img src="<?= e($c->ImageUrl) ?>" alt="" />
                    <?php else: ?><span class="ic" style="background:<?= $tintBg ?>;color:<?= $tintFg ?>;"><i class="fas <?= e($c->Icon) ?>"></i></span><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="cd-wrap">
        <!-- ===== About This Category + Why Choose ===== -->
        <div class="cd-two">
            <article class="cd-box">
                <h2>About This Category</h2>
                <div class="cd-rt"><?= category_rich_text($tx['About']) ?></div>
                <div class="cd-foot"><button type="button" class="cd-btn" id="cdShare"><i class="fas fa-share-nodes"></i> Share</button></div>
            </article>
            <aside class="cd-box why">
                <h2 class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">Why Choose <?= e($brand) ?></h2>
                <div class="cd-rt"><?= category_rich_text($tx['WhyChoose']) ?></div>
                <div class="cd-foot"><a class="cd-btn" href="<?= e($hireHref) ?>"><i class="fas fa-user-plus"></i> Hire A Tutor</a></div>
            </aside>
        </div>

        <!-- ===== Suggested Tutors ===== -->
        <section class="cd-sec" aria-label="Suggested tutors">
            <div class="cd-sec-h">
                <div><h2>Suggested Tutors For This Category</h2><?php if ($tutorTotal): ?><small><?= $tutorTotal ?> tutor<?= $tutorTotal === 1 ? '' : 's' ?> found</small><?php endif; ?></div>
                <a class="cd-btn sky" href="<?= e($seeAll) ?>">See All <i class="fas fa-caret-right"></i></a>
            </div>
            <?php if ($tutors): ?>
                <div class="cd-track" data-track>
                    <?php foreach ($tutors as $t): ?>
                        <div class="cd-tutor">
                            <div class="cd-tutor-b">
                                <span class="cd-av"><span><?= e($t->Initials) ?><?php if ($t->PhotoUrl !== ''): ?><img src="<?= e($t->PhotoUrl) ?>" alt="<?= e($t->FullName) ?>" loading="lazy" onerror="this.remove()" /><?php endif; ?></span></span>
                                <span class="cd-badge"><?php if (has_blue_tick($t->UserId ?? $t->Id ?? 0)): ?><?= blue_tick_html('Verified Teacher') ?><?php else: ?><i class="fas fa-award" title="Approved tutor"></i><?php endif; ?></span>
                                <b title="<?= e($t->FullName) ?>"><?= e($t->FullName) ?></b>
                                <small title="<?= e($t->Institution) ?>"><?= e($t->Institution !== 'Not specified' ? $t->Institution : $t->Qualification) ?></small>
                                <?php if ($t->TotalReviews): ?><span class="stars"><i class="fas fa-star"></i> <?= e(number_format($t->Rating, 1)) ?> (<?= (int)$t->TotalReviews ?>)</span><?php endif; ?>
                                <span class="loc"><i class="fas fa-location-dot"></i><?= e($t->City !== '' ? $t->City : $t->Location) ?></span>
                            </div>
                            <a class="cd-see" href="/Profile/Show/<?= (int)$t->UserId ?>">See</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cd-nav" data-nav hidden><button type="button" data-dir="-1" aria-label="Previous"><i class="fas fa-chevron-left"></i></button><button type="button" data-dir="1" aria-label="Next"><i class="fas fa-chevron-right"></i></button></div>
            <?php else: ?>
                <div class="cd-empty"><i class="fas fa-chalkboard-user"></i>এই ক্যাটাগরিতে এখনো কোনো টিউটর যুক্ত হননি।<br>
                    <a href="<?= e($hireHref) ?>" style="color:#028EA5;font-weight:600;">আপনার প্রয়োজন পোস্ট করুন</a> — উপযুক্ত টিউটররা আপনার সাথে যোগাযোগ করবেন।</div>
            <?php endif; ?>
        </section>

        <!-- ===== Related Categories ===== -->
        <?php if ($related): ?>
            <section class="cd-sec" aria-label="Related categories">
                <div class="cd-sec-h"><h2>Related Categories</h2><a class="cd-btn" href="/Categories">All Categories</a></div>
                <div class="cd-track" data-track>
                    <?php foreach ($related as $r) { $r->GroupName = $c->GroupName; partial('Categories/_Card', ['c' => $r]); } ?>
                </div>
                <div class="cd-nav" data-nav hidden><button type="button" data-dir="-1" aria-label="Previous"><i class="fas fa-chevron-left"></i></button><button type="button" data-dir="1" aria-label="Next"><i class="fas fa-chevron-right"></i></button></div>
            </section>
        <?php endif; ?>

        <!-- ===== নিচের CTA ===== -->
        <section class="cd-cta">
            <a class="cd-btn sky" href="<?= e($hireHref) ?>"><i class="fas fa-paper-plane"></i> Hire A Tutor For This Category</a>
            <p>"<?= e($c->Name) ?>" ক্যাটাগরি নিয়ে আরও জানতে চাইলে <a href="/Helpdesk">Helpdesk</a>-এ লিখুন — আমাদের টিম সাহায্য করবে।</p>
        </section>
    </div>
</div>
<div class="cd-toast" id="cdToast"></div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    // ক্যারোসেল: ← → বাটন (স্ক্রল করার মতো কার্ড থাকলেই দেখায়)
    document.querySelectorAll('[data-track]').forEach(function (tr) {
        var nav = tr.nextElementSibling; if (!nav || !nav.hasAttribute('data-nav')) return;
        function upd() { nav.hidden = tr.scrollWidth <= tr.clientWidth + 4; }
        upd(); window.addEventListener('resize', upd);
        nav.addEventListener('click', function (e) {
            var b = e.target.closest('button[data-dir]'); if (!b) return;
            var step = tr.clientWidth * 0.9 * +b.getAttribute('data-dir');
            var max = tr.scrollWidth - tr.clientWidth;
            if (step > 0 && tr.scrollLeft >= max - 4) tr.scrollTo({ left: 0 });
            else if (step < 0 && tr.scrollLeft <= 4) tr.scrollTo({ left: max });
            else tr.scrollBy({ left: step });
        });
    });
    // Share
    var toast = document.getElementById('cdToast'), tt;
    function say(m) { toast.textContent = m; toast.classList.add('show'); clearTimeout(tt); tt = setTimeout(function () { toast.classList.remove('show'); }, 2200); }
    document.getElementById('cdShare').addEventListener('click', async function () {
        var data = { title: document.title, url: location.href.split('#')[0] };
        try {
            if (navigator.share) { await navigator.share(data); return; }
            await navigator.clipboard.writeText(data.url); say('লিংক কপি হয়েছে — যেকোনো জায়গায় শেয়ার করুন');
        } catch (e) { if (e && e.name !== 'AbortError') say(data.url); }
    });
})();
</script>
<?php section_end(); ?>
