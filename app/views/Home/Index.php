<?php
$ViewData['Title'] = 'Home';
$settings = ($ViewBag->SiteSettings instanceof SiteSettings) ? $ViewBag->SiteSettings : new SiteSettings();
$heroImages = $settings->GetHeroImageList();
if (count($heroImages) == 0)
{
    $heroImages[] = "https://i.ibb.co.com/tMC8kk2L/Teacher-and-Student-Study-Session.png";
}
$heroImages = array_values($heroImages);

// ✅ Tutor Section Image (fallback to default if empty)
$tutorSectionImg = is_blank($settings->TutorSectionImageUrl)
    ? "https://i.ibb.co.com/Hfpwnx46/111.png"
    : $settings->TutorSectionImageUrl;

// ✅ হোম পেইজের জন্য টিউশন পোস্ট
$homeTuitions = is_array($ViewBag->HomeTuitions) ? $ViewBag->HomeTuitions : [];
?>

<?php if (home_section_on('hero')): ?>
<!-- ========================================= -->
<!-- HERO SECTION — লোগো + শিরোনাম + সার্চ + ডান পাশে ইলাস্ট্রেশন -->
<!-- ========================================= -->
<?php
    // লগইন থাকলে Tuition Circular-এ সরাসরি পোস্ট লেখার বক্স খোলে, না থাকলে আগে লগইন
    $heroSlider = count($heroImages) > 1 && $settings->SliderEnabled;
    // ডিফল্ট টিচার-স্টুডেন্ট ছবিটা থাকলে অ্যানিমেটেড সংস্করণ দেখায়; অ্যাডমিন অন্য ছবি দিলে সেটাই স্থির দেখাবে
    $heroAnimated = !$heroSlider && $heroImages[0] === HomeController::HERO_IMAGE;
    $hxA = '/uploads/hero/anim/v1-';
?>
<style>
    /* ✅ ক্লিন হিরো — হালকা ব্যাকগ্রাউন্ড, বাঁয়ে লেখা + সার্চ, ডানে ছবি */
    .hx { position: relative; overflow: hidden; margin-top: -80px; padding-top: 80px; background: #F4F7FB; }
    .hx-in { position: relative; max-width: 1440px; margin: 0 auto; padding: 56px 64px 72px 96px; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 48px; align-items: center; min-height: 480px; }
    .hx h1 { margin: 0; font-family: var(--font-heading); font-weight: 600; font-size: clamp(30px, 2.6vw, 48px); line-height: 1.22; color: #1F2937; letter-spacing: -.3px; }
    .hx h1 .bd { color: #00CFFF; }
    .hx-p { margin: 22px 0 0; max-width: 580px; font-size: clamp(15px, 1.05vw, 18px); line-height: 1.6; color: #4B5563; }
    .hx-search { margin-top: 34px; max-width: 600px; display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 12px; padding: 10px;
        box-shadow: 0 8px 28px rgba(15,40,80,.08); }
    .hx-search .fld { position: relative; flex: 1; min-width: 0; display: block; border: 1px solid #E5E7EB; border-radius: 8px; height: 46px; text-decoration: none; transition: border-color .2s; }
    .hx-search .fld:hover { border-color: #00CFFF; }
    .hx-search .hx-btn { display: inline-flex; align-items: center; text-decoration: none; flex-shrink: 0; border: none; border-radius: 8px; background: #00CFFF; color: #fff; font-weight: 600; font-size: 15px; padding: 0 24px; height: 46px; cursor: pointer;
        font-family: inherit; transition: background .2s; white-space: nowrap; }
    .hx-search .hx-btn:hover { background: #00B5E0; color: #fff; }
    .hx-art { position: relative; display: flex; align-items: center; justify-content: center; }
    /* ছবির সাদা ব্যাকগ্রাউন্ড হিরোর রঙের সাথে মিশে যায় */
    .hx-art img { display: block; width: 100%; max-width: 560px; height: auto; object-fit: contain; mix-blend-mode: multiply; }
    .hx-art .hero-slide { position: absolute; inset: 0; margin: auto; transition: opacity 1s ease; }
    .hx-art.slider { aspect-ratio: 16 / 12; width: 100%; max-width: 560px; margin: 0 auto; }
    .hx-art.slider .hero-slide { height: 100%; }
    .hx-dotsnav { position: absolute; bottom: 4px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 2; }
    .hx-dotsnav button { width: 9px; height: 9px; border-radius: 50%; border: none; padding: 0; cursor: pointer; background: #C9D6E5; transition: all .3s; }
    .hx-dotsnav button.on { background: #00CFFF; transform: scale(1.3); }
    @media (max-width: 1280px) { .hx-in { padding: 44px 40px 56px 56px; gap: 32px; min-height: 0; } }
    @media (max-width: 1024px) {
        .hx-in { grid-template-columns: minmax(0, 1fr); padding: 36px 28px 48px; }
        .hx-text { min-width: 0; }
        .hx-art { max-width: 520px; margin: 0 auto; width: 100%; }
    }
    @media (max-width: 640px) {
        .hx-in { padding: 24px 16px 36px; gap: 24px; }
        .hx h1 { font-size: 28px; }
        .hx-p { margin-top: 14px; font-size: 15px; }
        .hx-search { margin-top: 22px; padding: 8px; gap: 8px; }
        .hx-search .fld { height: 44px; }
        .hx-search .hx-btn { font-size: 14.5px; padding: 0 16px; height: 44px; }
    }
    /* ✅ বক্সে চলমান লেখা (ডান থেকে বাঁয়ে) — ক্লিক করলে Find Tutor পেজ */
    .hx-mq { position: absolute; inset: 0 12px; overflow: hidden; pointer-events: none; display: flex; align-items: center; color: #6B7280; font-size: 15px; transition: opacity .2s;
        -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 14px, #000 calc(100% - 14px), transparent 100%); mask-image: linear-gradient(90deg, transparent 0, #000 14px, #000 calc(100% - 14px), transparent 100%); }
    .hx-mq-track { display: inline-flex; white-space: nowrap; animation: hxMarquee 12s linear infinite; will-change: transform; }
    .hx-mq-track span { padding-right: 120px; }
    @keyframes hxMarquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    @media (prefers-reduced-motion: reduce) { .hx-mq-track { animation: none; } }
    @media (max-width: 640px) { .hx-mq { font-size: 14.5px; } .hx-mq-track span { padding-right: 60px; } }
    /* ===== অ্যানিমেটেড ছবি: স্যার কথা বলছেন আর হাতে দেখাচ্ছেন, ছাত্র মাথা দুলিয়ে শুনছে আর খাতায় লিখছে ===== */
    .hx-anim { position: relative; width: 100%; max-width: 560px; aspect-ratio: 600 / 429; container-type: inline-size; }
    .hx-anim > img, .hx-anim .ly { position: absolute; display: block; max-width: none; height: auto; mix-blend-mode: normal; user-select: none; pointer-events: none; }
    .hx-anim > img.base { inset: 0; width: 100%; height: 100%; mix-blend-mode: multiply; }
    .hx-anim .ly img { display: block; width: 100%; max-width: none; height: auto; mix-blend-mode: normal; }
    .ly.t-head  { left: 27.417%; top: 26.923%; width: 16.083%; transform-origin: 57.5% 97%; animation: hxTalk 4.2s ease-in-out infinite; }
    .ly.t-arm   { left: 39.084%; top: 54.196%; width: 13.416%; transform-origin: 6.8% 74.8%; animation: hxPoint 3.4s ease-in-out infinite; }
    .ly.s-head  { left: 59.084%; top: 36.247%; width: 16.584%; transform-origin: 47.7% 93%; animation: hxDream 5.6s ease-in-out infinite; }
    .ly.s-hand  { left: 56.917%; top: 62.587%; width: 7.750%;  animation: hxWrite 1.6s linear infinite; }
    .ly.plant   { left: 8.417%;  top: 51.865%; width: 14.916%; transform-origin: 57.5% 98.8%; animation: hxSway 5s ease-in-out infinite; }
    .ly.vine    { left: 76.416%; top: 33.683%; width: 8.250%;  transform-origin: 43.4% 2%; animation: hxSway 4.4s ease-in-out infinite reverse; }
    @keyframes hxTalk  { 0%, 100% { transform: rotate(0); } 18% { transform: rotate(-2.2deg) translateY(-.6%); } 36% { transform: rotate(.8deg); } 55% { transform: rotate(-1.6deg) translateY(-.4%); } 75% { transform: rotate(.6deg); } }
    @keyframes hxPoint { 0%, 100% { transform: rotate(0); } 14% { transform: rotate(-4.5deg); } 28% { transform: rotate(.6deg); } 42% { transform: rotate(-3.5deg); } 56% { transform: rotate(0); } }
    @keyframes hxDream { 0%, 100% { transform: rotate(0); } 35% { transform: rotate(2.4deg); } 50% { transform: rotate(2.4deg); } 80% { transform: rotate(-.8deg); } }
    @keyframes hxWrite { 0%, 100% { transform: translate(0, 0); } 15% { transform: translate(3%, -2%); } 30% { transform: translate(-1%, 1.5%); } 45% { transform: translate(4%, -1%); } 60% { transform: translate(1%, 2%); } 75% { transform: translate(6%, 0); } 90% { transform: translate(2%, 1%); } }
    @keyframes hxSway  { 0%, 100% { transform: rotate(-2.5deg); } 50% { transform: rotate(2.5deg); } }
    /* চোখের পাতা — মাঝে মাঝে পলক */
    .ly .lid { position: absolute; border-radius: 50%; background: #F2B777; transform: scaleY(0); transform-origin: 50% 0; box-shadow: inset 0 -1.5px 0 #3B2B22; animation: hxBlink 4.6s infinite; }
    .t-head .lid { animation-delay: .8s; } .s-head .lid { animation-duration: 5.3s; animation-delay: 2.1s; }
    @keyframes hxBlink { 0%, 93%, 100% { transform: scaleY(0); } 95.5% { transform: scaleY(1); } }
    /* খাতায় লেখার দাগ */
    .hx-ink { position: absolute; left: 63.882%; top: 71.6%; width: 8.775%; overflow: visible; }
    .hx-ink path { fill: none; stroke: #1F2A44; stroke-width: 1.4; stroke-linecap: round; stroke-dasharray: 60; stroke-dashoffset: 60; animation: hxInk 3.2s ease-in-out infinite; opacity: .55; }
    .hx-ink path + path { animation-delay: 1.6s; }
    @keyframes hxInk { 0% { stroke-dashoffset: 60; opacity: .55; } 45% { stroke-dashoffset: 0; } 80% { stroke-dashoffset: 0; opacity: .55; } 100% { stroke-dashoffset: 0; opacity: 0; } }
    /* ল্যাপটপের স্ক্রিনে আলো */
    .hx-glow { position: absolute; inset: 0; clip-path: polygon(77.8% 61.5%, 91.4% 59.7%, 89.6% 76%, 74.6% 77.4%);
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%) no-repeat; background-size: 300% 100%; animation: hxGlowMove 4s ease-in-out infinite; }
    @keyframes hxGlowMove { 0% { background-position: 100% 0; } 60%, 100% { background-position: 0 0; } }
    /* ছোট তারা — ছাত্রের মাথার পাশে */
    .hx-spark { position: absolute; width: 3.042%; aspect-ratio: 1; color: #00CFFF; animation: hxTwinkle 2.6s ease-in-out infinite; }
    .hx-spark svg { display: block; width: 100%; height: 100%; }
    .hx-spark.a { left: 76.050%; top: 41%; } .hx-spark.b { left: 57.330%; top: 33%; width: 2.223%; animation-delay: -.9s; } .hx-spark.c { left: 78.975%; top: 52%; width: 1.872%; animation-delay: -1.7s; color: #FFC233; }
    @keyframes hxTwinkle { 0%, 100% { transform: scale(.3) rotate(0); opacity: 0; } 50% { transform: scale(1) rotate(45deg); opacity: 1; } }
    @media (prefers-reduced-motion: reduce) { .hx-anim *, .hx-anim .ly { animation: none !important; } .hx-ink path { stroke-dashoffset: 0; } }
</style>
<?php $hx = hero_texts(); $hxTitle = trim(html_entity_decode(strip_tags(str_replace('<br>', ' ', $hx['hx_title_html'])), ENT_QUOTES, 'UTF-8')); ?>
<section class="hx" aria-label="<?= e($hxTitle) ?>">
    <div class="hx-in">
        <div class="hx-text">
            <h1><?= $hx['hx_title_html'] /* sanitize_rich_html করা */ ?></h1>
            <p class="hx-p"><?= $hx['hx_desc_html'] /* sanitize_rich_html করা */ ?></p>
            <div class="hx-search">
                <a class="fld" href="<?= e($hx['hx_btn_link']) ?>" aria-label="<?= e($hx['hx_marquee'] !== '' ? $hx['hx_marquee'] : $hx['hx_btn']) ?>">
                    <?php if ($hx['hx_marquee'] !== ''): ?><span class="hx-mq" aria-hidden="true"><span class="hx-mq-track"><span><?= e($hx['hx_marquee']) ?></span><span><?= e($hx['hx_marquee']) ?></span></span></span><?php endif; ?>
                </a>
                <a class="hx-btn" href="<?= e($hx['hx_btn_link']) ?>"><?= e($hx['hx_btn']) ?></a>
            </div>
        </div>

        <div class="hx-art<?= $heroSlider ? ' slider' : '' ?>">
            <?php if ($heroSlider): ?>
                <?php foreach ($heroImages as $idx => $img): ?>
                    <img src="<?= e($img) ?>" alt="<?= $idx === 0 ? 'শিক্ষক শিক্ষার্থীকে পড়াচ্ছেন' : '' ?>" class="hero-slide" style="opacity:<?= $idx === 0 ? '1' : '0' ?>;" data-index="<?= (int)$idx ?>"<?= $idx === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?> />
                <?php endforeach; ?>
                <div class="hx-dotsnav">
                    <?php foreach ($heroImages as $idx => $img): ?><button type="button" class="slider-dot<?= $idx === 0 ? ' on' : '' ?>" data-index="<?= (int)$idx ?>" aria-label="ছবি <?= $idx + 1 ?>"></button><?php endforeach; ?>
                </div>
            <?php elseif ($heroAnimated): ?>
                <div class="hx-anim" role="img" aria-label="শিক্ষক শিক্ষার্থীকে পড়াচ্ছেন">
                    <img class="base" src="<?= $hxA ?>base.webp" alt="" width="1200" height="858" fetchpriority="high" />
                    <span class="ly plant"><img src="<?= $hxA ?>plant_l.webp" alt="" /></span>
                    <span class="ly vine"><img src="<?= $hxA ?>vine.webp" alt="" /></span>
                    <span class="hx-glow"></span>
                    <span class="ly t-arm"><img src="<?= $hxA ?>t_arm.webp" alt="" /></span>
                    <span class="ly t-head"><img src="<?= $hxA ?>t_head.webp" alt="" />
                        <i class="lid" style="left:52.3%;top:55.8%;width:15.5%;height:12%;"></i><i class="lid" style="left:80.3%;top:48.2%;width:14.5%;height:13%;"></i></span>
                    <span class="ly s-head"><img src="<?= $hxA ?>s_head.webp" alt="" />
                        <i class="lid" style="left:11.6%;top:50.9%;width:10%;height:15%;"></i><i class="lid" style="left:30.7%;top:59%;width:12%;height:12.7%;"></i></span>
                    <svg class="hx-ink" viewBox="0 0 60 16" aria-hidden="true"><path d="M2 6 q4 -4 8 0 t8 0 t8 0 t8 0"/><path d="M2 13 q4 -4 8 0 t8 0 t8 0 t8 0 t8 0"/></svg>
                    <span class="ly s-hand"><img src="<?= $hxA ?>s_hand.webp" alt="" /></span>
                    <span class="hx-spark a"><svg viewBox="0 0 20 20"><path fill="currentColor" d="M10 0c.8 5.2 4.8 9.2 10 10-5.2.8-9.2 4.8-10 10-.8-5.2-4.8-9.2-10-10 5.2-.8 9.2-4.8 10-10Z"/></svg></span>
                    <span class="hx-spark b"><svg viewBox="0 0 20 20"><path fill="currentColor" d="M10 0c.8 5.2 4.8 9.2 10 10-5.2.8-9.2 4.8-10 10-.8-5.2-4.8-9.2-10-10 5.2-.8 9.2-4.8 10-10Z"/></svg></span>
                    <span class="hx-spark c"><svg viewBox="0 0 20 20"><path fill="currentColor" d="M10 0c.8 5.2 4.8 9.2 10 10-5.2.8-9.2 4.8-10 10-.8-5.2-4.8-9.2-10-10 5.2-.8 9.2-4.8 10-10Z"/></svg></span>
                </div>
            <?php else: ?>
                <img src="<?= e($heroImages[0]) ?>" alt="শিক্ষক শিক্ষার্থীকে পড়াচ্ছেন" fetchpriority="high" />
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($heroSlider): ?>
    <script>
        (function () {
            var slides = document.querySelectorAll('.hx-art .hero-slide'), dots = document.querySelectorAll('.hx-dotsnav .slider-dot');
            var cur = 0, ms = <?= (int)($settings->SliderIntervalSeconds > 0 ? $settings->SliderIntervalSeconds : 5) ?> * 1000, timer = null;
            function show(i) { if (i === cur) return; slides.forEach(function (s, k) { s.style.opacity = k === i ? '1' : '0'; }); dots.forEach(function (d, k) { d.classList.toggle('on', k === i); }); cur = i; }
            function start() { stop(); timer = setInterval(function () { show((cur + 1) % slides.length); }, ms); }
            function stop() { if (timer) { clearInterval(timer); timer = null; } }
            start();
            dots.forEach(function (d) { d.addEventListener('click', function () { show(parseInt(d.getAttribute('data-index'), 10)); start(); }); });
            var box = document.querySelector('.hx-art'); box.addEventListener('mouseenter', stop); box.addEventListener('mouseleave', start);
        })();
    </script>
<?php endif; ?>
<?php endif; /* home_section: hero */ ?>

<?php if (home_section_on('how')): ?>
<!-- ========================================= -->
<!-- ✅ HOW IT WORKS — ধাপে ধাপে (Parents / Students) -->
<!-- ========================================= -->
<?php
    $hwLoggedIn = session_get('UserName') !== null;
    $hwSteps = [
        ['fa-user-plus',   'Create Account',        'Sign up for free and complete your profile in a minute.',
            $hwLoggedIn ? '/Profile' : '/Account/Register'],
        ['fa-file-pen',    'Post Your Requirement', 'Post class, subjects, area & budget on the Tuition Circular.',
            '/Tuition/Circular'],
        ['fa-id-card',     'Get Tutor Applications','Verified tutors apply — see their profile, education & contact.',
            $hwLoggedIn ? '/User/Dashboard?section=applications' : '/Account/Login?returnUrl=' . rawurlencode('/User/Dashboard?section=applications')],
        ['fa-circle-check','Select Your Tutor',     'Call or WhatsApp, accept your favourite one & start learning.',
            '/FindTutor'],
    ];
?>
<style>
    .hw-section { padding: 56px 16px 64px; }
    .hw-wrap { max-width: 1180px; margin: 0 auto; }
    .hw-title {
        font-family: var(--font-heading); font-weight: 700; color: #08224A; text-align: center;
        font-size: 40px; line-height: 1.2; margin: 0 0 10px;
    }
    .hw-title .hl { color: #02B8F5; }
    .hw-sub { text-align: center; color: #64748B; font-size: 16px; margin: 0 0 54px; }

    .hw-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; position: relative; }
    .hw-step {
        position: relative; text-align: center; text-decoration: none; color: inherit;
        padding: 0 8px; opacity: 0; transform: translateY(18px);
        transition: opacity .6s ease, transform .6s cubic-bezier(.22,1,.36,1);
    }
    .hw-step.in { opacity: 1; transform: none; }
    .hw-step:nth-of-type(2) { transition-delay: .12s; } .hw-step:nth-of-type(3) { transition-delay: .24s; } .hw-step:nth-of-type(4) { transition-delay: .36s; }

    .hw-icon-wrap { position: relative; height: 150px; display: flex; align-items: center; justify-content: center; }
    .hw-icon {
        position: relative; z-index: 2;
        width: 86px; height: 86px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: #02B8F5; color: #fff; font-size: 30px;
        border: 5px solid #fff; box-shadow: 0 10px 26px rgba(2,184,245,0.28);
        transition: transform .3s cubic-bezier(.22,1,.36,1), box-shadow .3s, background .3s;
    }
    .hw-num {
        position: absolute; top: -6px; right: -6px; width: 28px; height: 28px; border-radius: 50%;
        background: #FFB300; color: #08224A; font-size: 13px; font-weight: 800; font-family: inherit;
        display: flex; align-items: center; justify-content: center; border: 3px solid #fff;
    }
    .hw-step:hover .hw-icon { transform: translateY(-6px) scale(1.04); background: #0099D6; box-shadow: 0 16px 30px rgba(2,184,245,0.38); }

    /* বাঁকা ড্যাশ তীর — JS দিয়ে আসল বৃত্তের অবস্থান মেপে আঁকা (এক বৃত্তের কিনারা থেকে পরেরটার কিনারায়) */
    .hw-lines { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1; overflow: visible; opacity: 0; transition: opacity .6s ease .3s; }
    .hw-lines.on { opacity: 1; }
    .hw-lines path.l { fill: none; stroke: #8FD8F2; stroke-width: 2; stroke-dasharray: 7 7; stroke-linecap: round; animation: hwDash 1.2s linear infinite; }
    @keyframes hwDash { to { stroke-dashoffset: -28; } }

    .hw-step h3 { margin: 18px 0 10px; font-size: 20px; font-weight: 700; color: #08224A; }
    .hw-step p { margin: 0 auto; max-width: 250px; font-size: 14.5px; line-height: 1.65; color: #64748B; }
    .hw-step .hw-go { display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; font-size: 13px; font-weight: 700; color: #028EA5; opacity: 0; transform: translateY(4px); transition: .25s; }
    .hw-step:hover .hw-go { opacity: 1; transform: none; }

    /* ট্যাবলেট: ২ কলাম, তীর লুকানো */
    @media (max-width: 1024px) {
        .hw-steps { grid-template-columns: repeat(2, 1fr); row-gap: 36px; }
        .hw-lines { display: none; }
        .hw-icon-wrap { height: 110px; }
    }
    /* মোবাইল: উপর-নিচে টাইমলাইন */
    @media (max-width: 640px) {
        .hw-section { padding: 40px 16px 44px; }
        .hw-title { font-size: 28px; }
        .hw-sub { margin-bottom: 30px; font-size: 14.5px; }
        .hw-steps { grid-template-columns: 1fr; gap: 0; }
        .hw-step {
            display: grid; grid-template-columns: 76px 1fr; column-gap: 16px; text-align: left; padding: 0 0 26px;
        }
        .hw-step:not(:last-child)::before {
            content: ''; position: absolute; left: 37px; top: 72px; bottom: 0;
            border-left: 2px dashed #8FD8F2;
        }
        .hw-icon-wrap { height: auto; grid-row: span 3; align-items: flex-start; }
        .hw-icon { width: 70px; height: 70px; font-size: 24px; border-width: 4px; }
        .hw-num { width: 24px; height: 24px; font-size: 11px; }
        .hw-step h3 { margin: 10px 0 6px; font-size: 18px; }
        .hw-step p { margin: 0; max-width: none; font-size: 14px; }
        .hw-step .hw-go { opacity: 1; transform: none; margin-top: 8px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .hw-step { opacity: 1; transform: none; transition: none; }
        .hw-lines path.l { animation: none; }
    }
</style>

<section id="how-it-works" class="hw-section" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hw-wrap">
        <h2 class="hw-title">The ways <span class="hl">Parents / Students</span> find a tutor.</h2>
        <p class="hw-sub">মাত্র ৪টি সহজ ধাপে আপনার পছন্দের টিউটর খুঁজে নিন</p>

        <div class="hw-steps" id="hwSteps">
            <svg class="hw-lines" id="hwLines" aria-hidden="true">
                <defs><marker id="hwHead" viewBox="0 0 12 12" refX="10" refY="6" markerWidth="12" markerHeight="12" markerUnits="userSpaceOnUse" orient="auto">
                    <path d="M2 2 L10 6 L2 10" fill="none" stroke="#5CC8EE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></marker></defs>
            </svg>
            <?php foreach ($hwSteps as $i => [$icon, $title, $desc, $href]): ?>
                <a class="hw-step" href="<?= e($href) ?>">
                    <div class="hw-icon-wrap">
                        <span class="hw-icon"><i class="fas <?= e($icon) ?>"></i><span class="hw-num"><?= $i + 1 ?></span></span>
                    </div>
                    <h3><?= e($title) ?></h3>
                    <p><?= e($desc) ?></p>
                    <span class="hw-go">শুরু করুন <i class="fas fa-arrow-right"></i></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
    (function () {
        var steps = document.querySelectorAll('.hw-step');
        if (!('IntersectionObserver' in window)) { steps.forEach(function (s) { s.classList.add('in'); }); return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
        }, { threshold: 0.2 });
        steps.forEach(function (s) { io.observe(s); });
    })();
    // তীর আঁকা: প্রতিটা বৃত্তের কেন্দ্র মেপে, একটা উপরে বাঁকা, পরেরটা নিচে — পালাক্রমে
    (function () {
        var box = document.getElementById('hwSteps'), svg = document.getElementById('hwLines'); if (!box || !svg) return;
        var NS = 'http://www.w3.org/2000/svg';
        function pos(el) { // transform বাদ দিয়ে লেআউটের আসল অবস্থান (fade-in এর সময়ও ঠিক থাকে)
            var x = 0, y = 0, e = el;
            while (e && e !== box) { x += e.offsetLeft; y += e.offsetTop; e = e.offsetParent; }
            if (e !== box) { var r = el.getBoundingClientRect(), b = box.getBoundingClientRect(); return { x: r.left - b.left, y: r.top - b.top }; }
            return { x: x, y: y };
        }
        function draw() {
            svg.querySelectorAll('path.l').forEach(function (p) { p.remove(); });
            if (getComputedStyle(svg).display === 'none') return;
            var icons = box.querySelectorAll('.hw-icon');
            for (var i = 0; i < icons.length - 1; i++) {
                var a = icons[i], b = icons[i + 1], pa = pos(a), pb = pos(b);
                var ra = a.offsetWidth / 2, rb = b.offsetWidth / 2;
                var ca = { x: pa.x + ra, y: pa.y + a.offsetHeight / 2 }, cb = { x: pb.x + rb, y: pb.y + b.offsetHeight / 2 };
                if (Math.abs(ca.y - cb.y) > 20) continue; // একই লাইনে না থাকলে তীর নয়
                var up = i % 2 === 0, s = up ? -1 : 1, gap = 10, ang = 40 * Math.PI / 180;
                var sx = ca.x + (ra + gap) * Math.cos(ang), sy = ca.y + s * (ra + gap) * Math.sin(ang);
                var ex = cb.x - (rb + gap) * Math.cos(ang), ey = cb.y + s * (rb + gap) * Math.sin(ang);
                var d = ex - sx, h = Math.min(70, Math.max(34, d * 0.32));
                var p = document.createElementNS(NS, 'path');
                p.setAttribute('class', 'l');
                p.setAttribute('d', 'M' + sx.toFixed(1) + ' ' + sy.toFixed(1) + ' C ' + (sx + d * 0.22).toFixed(1) + ' ' + (sy + s * h).toFixed(1) + ', '
                    + (ex - d * 0.22).toFixed(1) + ' ' + (ey + s * h).toFixed(1) + ', ' + ex.toFixed(1) + ' ' + ey.toFixed(1));
                p.setAttribute('marker-end', 'url(#hwHead)');
                svg.appendChild(p);
            }
            svg.classList.add('on');
        }
        var t; function later() { clearTimeout(t); t = setTimeout(draw, 80); }
        draw(); window.addEventListener('resize', later); window.addEventListener('load', draw);
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(draw);
    })();
</script>
<?php endif; /* home_section: how */ ?>

<!-- ========================================= -->
<!-- ✅ CATEGORIES (Admin → Categories থেকে) -->
<!-- ========================================= -->
<?php $homeCats = is_array($ViewBag->HomeCategories) ? $ViewBag->HomeCategories : []; ?>
<?php partial('Home/_Styles'); ?>
<?php if ($homeCats && home_section_on('categories')): ?>
<?php partial('Categories/_CardStyles'); ?>
<section class="hs-sec hs-cats" id="home-categories" style="background:#fff;" aria-label="Categories">
    <div class="hs-wrap">
        <?php partial('Home/_SectionHead', ['title' => 'Explore', 'hl' => 'Categories', 'sub' => 'Play থেকে HSC, IELTS থেকে Guitar — যে বিষয়ে শিখতে চান, সেই ক্যাটাগরির অভিজ্ঞ টিউটর খুঁজুন।', 'href' => '/Categories', 'btn' => 'View More']); ?>
        <div class="cc-grid">
            <?php foreach ($homeCats as $c) partial('Categories/_Card', ['c' => $c]); ?>
        </div>
        <div class="hs-foot"><a class="hs-more" href="/Categories">View More Categories <i class="fas fa-arrow-right"></i></a></div>
    </div>
</section>
<?php endif; ?>

<!-- ========================================= -->
<!-- ✅ FEATURED TUITIONS (Home Page) -->
<!-- ========================================= -->
<?php if (count($homeTuitions) > 0 && home_section_on('tuitions')): ?>
    <section class="py-8 sm:py-10 md:py-12 lg:py-16" style="background-color:<?= e($settings->SecondaryColor) ?>;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-end mb-6 sm:mb-8">
                <div>
                    <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl" style="color:#08224A;">
                        Latest Tuition Circulars
                    </h2>
                    <p class="mt-3 text-base sm:text-lg" style="color:<?= e($settings->TextColor) ?>;">
                        Explore the most recent tuition opportunities curated for you.
                    </p>
                </div>
                <!-- ✅ View More Button -->
                <a href="/Tuition/Circular" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-semibold text-white transition hover:opacity-90 text-sm shadow-md" style="background-color:<?= e($settings->PrimaryColor) ?>;">
                    View More Tuitions
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            <!-- Tuition Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($homeTuitions as $post): ?>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-black/5 hover:shadow-md transition-shadow flex flex-col h-full">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4 gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide bg-[#FFEBB8] text-[#8a5a00]">
                                <i class="fas fa-user-tie"></i> 
                                <?= e((string)$post->TutorGenderPreference === '' ? "Any Gender" : $post->TutorGenderPreference) ?>
                            </span>
                            <?php if ((string)$post->TuitionCode !== ''): ?>
                                <span class="bg-[#08224A] text-[#FFB300] px-3 py-1 rounded-full text-[11px] font-bold font-mono tracking-wider">
                                    <?= e($post->TuitionCode) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Title -->
                        <h3 class="font-serif text-lg font-bold mb-3 line-clamp-2" style="color:#08224A;">
                            <?= e($post->Title) ?>
                        </h3>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-y-2 gap-x-4 mb-4 text-sm text-gray-600">
                            <?php if ((string)$post->StudentClass !== ''): ?>
                                <div class="flex items-start gap-1.5">
                                    <i class="fas fa-graduation-cap mt-1 text-xs" style="color:#028EA5;"></i>
                                    <span class="line-clamp-1"><strong>Class:</strong> <?= e($post->StudentClass) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ((string)$post->Subjects !== ''): ?>
                                <div class="flex items-start gap-1.5">
                                    <i class="fas fa-book mt-1 text-xs" style="color:#028EA5;"></i>
                                    <span class="line-clamp-1"><strong>Subjects:</strong> <?= e($post->Subjects) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ((string)$post->Area !== ''): ?>
                                <div class="col-span-2 flex items-start gap-1.5">
                                    <i class="fas fa-map-marker-alt mt-1 text-xs" style="color:#028EA5;"></i>
                                    <span class="line-clamp-1"><strong>Area:</strong> <?= e($post->Area) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Salary -->
                        <?php if ((string)$post->Salary !== ''): ?>
                            <div class="mt-auto mb-4 inline-flex items-center gap-2 bg-[#d4edda] text-[#155724] px-4 py-2 rounded-lg font-bold text-sm w-fit">
                                <i class="fas fa-money-bill-wave"></i> ৳<?= e($post->Salary) ?> /month
                            </div>
                        <?php endif; ?>

                        <!-- View Details Button -->
                        <a href="/Tuition/Details/<?= e($post->Id) ?>" class="mt-auto block text-center w-full py-2.5 rounded-xl font-bold text-sm text-white transition hover:opacity-90" style="background-color:<?= e($settings->PrimaryColor) ?>;">
                            View Details
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Mobile View More Button -->
            <div class="mt-8 text-center sm:hidden">
                <a href="/Tuition/Circular" class="inline-flex items-center justify-center gap-2 px-6 py-3 w-full rounded-full font-semibold text-white transition hover:opacity-90 text-sm shadow-md" style="background-color:<?= e($settings->PrimaryColor) ?>;">
                    View More Tuitions
                </a>
            </div>

        </div>
    </section>
<?php endif; ?>

<?php if (home_section_on('notice')): $homeNotices = []; try { $homeNotices = array_slice(db()->GetAllNotices(), 0, 3); } catch (Throwable $e) { } ?>
<!-- ========================================= -->
<!-- ✅ NOTICE — সর্বশেষ নোটিশ, ক্লিক করলে Notice পেজ -->
<!-- ========================================= -->
<style>
    .hn { padding: 56px 0; background: #fff; }
    .hn-in { max-width: 1152px; margin: 0 auto; padding: 0 20px; }
    .hn-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 26px; }
    .hn-head h2 { margin: 0; font-family: var(--font-heading); font-size: clamp(28px, 3.2vw, 44px); color: #08224A; display: flex; align-items: center; gap: 12px; }
    .hn-head h2 i { width: 50px; height: 50px; border-radius: 14px; background: #FFF4D6; color: #D97706; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; }
    .hn-head p { margin: 8px 0 0; color: #64748B; font-size: 16px; }
    .hn-all { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 999px; background: #08224A; color: #fff; font-weight: 700; font-size: 14.5px; text-decoration: none; box-shadow: 0 8px 20px rgba(8,34,74,.18); }
    .hn-all:hover { background: #0A3A6B; color: #fff; }
    .hn-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
    .hn-card { display: flex; flex-direction: column; background: #fff; border: 1px solid #E5EDF5; border-radius: 18px; overflow: hidden; text-decoration: none; color: inherit; box-shadow: 0 6px 22px rgba(15,52,103,.06); transition: transform .2s, box-shadow .2s; min-width: 0; }
    .hn-card:hover { transform: translateY(-4px); box-shadow: 0 14px 34px rgba(15,52,103,.12); color: inherit; }
    .hn-img { aspect-ratio: 16 / 8; background: linear-gradient(135deg, #E0F7FE, #FFF4D6); display: flex; align-items: center; justify-content: center; color: #F59E0B; font-size: 38px; overflow: hidden; }
    .hn-img img { width: 100%; height: 100%; object-fit: cover; }
    .hn-body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .hn-meta { display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 12.5px; color: #64748B; }
    .hn-cat { font-weight: 800; font-size: 11.5px; padding: 3px 10px; border-radius: 999px; background: #E0F2FE; color: #0369A1; }
    .hn-cat.ann { background: #FEE2E2; color: #B91C1C; }
    .hn-card h3 { margin: 0; font-size: 17px; font-weight: 800; color: #0F172A; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .hn-card p { margin: 0; color: #475569; font-size: 14px; line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .hn-more { margin-top: auto; padding-top: 6px; color: #0EA5E9; font-weight: 700; font-size: 13.5px; }
    .hn-empty { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; background: linear-gradient(120deg, #FFF8E6, #E8F7FE); border: 1px solid #F3E3B5; border-radius: 18px; padding: 22px 26px; text-decoration: none; color: #0F172A; }
    .hn-empty i { font-size: 30px; color: #D97706; } .hn-empty b { font-size: 17px; display: block; } .hn-empty span { flex: 1; min-width: 200px; color: #475569; }
    .hn-mob { display: none; margin-top: 18px; text-align: center; }
    @media (max-width: 900px) { .hn-grid { grid-template-columns: minmax(0, 1fr); } .hn-head .hn-all { display: none; } .hn-mob { display: block; } .hn { padding: 40px 0; } }
</style>
<section class="hn" id="notice">
    <div class="hn-in">
        <div class="hn-head">
            <div><h2><i class="fas fa-bullhorn"></i> Notice</h2><p>মাষ্টার মশাই এর সর্বশেষ নোটিশ, খবর আর ঘোষণা।</p></div>
            <a class="hn-all" href="/Notice">সব নোটিশ দেখুন <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php if ($homeNotices): ?>
            <div class="hn-grid">
                <?php foreach ($homeNotices as $n): $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string)$n->Content), ENT_QUOTES, 'UTF-8'))); ?>
                    <a class="hn-card" href="/Notice/Details/<?= (int)$n->Id ?>">
                        <div class="hn-img"><?php if (!is_blank($n->ImageUrl)): ?><img src="<?= e($n->ImageUrl) ?>" alt="" loading="lazy" onerror="this.insertAdjacentHTML('afterend','<i class=&quot;fas fa-bullhorn&quot;></i>');this.remove()" /><?php else: ?><i class="fas fa-bullhorn"></i><?php endif; ?></div>
                        <div class="hn-body">
                            <div class="hn-meta"><span class="hn-cat<?= $n->IsAnnouncement ? ' ann' : '' ?>"><?= e($n->IsAnnouncement ? 'Announcement' : ($n->Category ?: 'News')) ?></span><span><i class="far fa-calendar"></i> <?= e(fmt_date($n->PublishedDate ?? $n->CreatedAt ?? null, 'd M Y')) ?></span></div>
                            <h3><?= e($n->Title) ?></h3>
                            <?php if ($plain !== ''): ?><p><?= e(mb_substr($plain, 0, 160)) ?></p><?php endif; ?>
                            <span class="hn-more">বিস্তারিত পড়ুন <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <a class="hn-empty" href="/Notice"><i class="fas fa-bullhorn"></i><span><b>নোটিশ বোর্ড</b>নতুন নোটিশ, খবর আর ঘোষণা এখানে আসবে — দেখতে ক্লিক করুন।</span><b style="color:#0369A1;">Notice পেজে যান <i class="fas fa-arrow-right"></i></b></a>
        <?php endif; ?>
        <div class="hn-mob"><a class="hn-all" href="/Notice">সব নোটিশ দেখুন <i class="fas fa-arrow-right"></i></a></div>
    </div>
</section>
<?php endif; ?>

<?php if (home_section_on('feed')): ?>
<!-- ========================================= -->
<!-- ✅ EDUCATIONAL FEED — সর্বশেষ Approve হওয়া পোস্ট -->
<!-- ========================================= -->
<?php $homeFeed = is_array($ViewBag->HomeFeed) ? $ViewBag->HomeFeed : []; ?>
<section class="hs-sec" id="home-feed" style="background:#fff;" aria-label="Educational Feed">
    <div class="hs-wrap">
        <?php partial('Home/_SectionHead', ['title' => 'Educational', 'hl' => 'Feed', 'sub' => 'শিক্ষক আর শিক্ষার্থীদের শেয়ার করা নোট, ছবি, ভিডিও আর পড়াশোনার টিপস।', 'href' => '/Feed', 'btn' => 'View More']); ?>
        <?php if ($homeFeed): ?>
            <div class="hf-grid">
                <?php foreach ($homeFeed as $fp):
                    $txt = trim(preg_replace('/\s+/u', ' ', $fp->Content)); ?>
                    <a class="hf-card" href="<?= e($fp->Url) ?>">
                        <?php if ($fp->Images): ?>
                            <div class="hf-media"><img src="<?= e($fp->Images[0]) ?>" alt="" loading="lazy" /><?php if (count($fp->Images) > 1): ?><span class="cnt"><i class="fas fa-images"></i> <?= count($fp->Images) ?></span><?php endif; ?></div>
                        <?php elseif ($fp->YouTube !== ''): ?>
                            <div class="hf-media"><img src="https://i.ytimg.com/vi/<?= e($fp->YouTube) ?>/hqdefault.jpg" alt="" loading="lazy" /><span class="play yt"><i class="fas fa-play"></i></span></div>
                        <?php elseif ($fp->Video !== ''): ?>
                            <div class="hf-media"><video src="<?= e($fp->Video) ?>#t=0.5" preload="metadata" muted playsinline></video><span class="play"><i class="fas fa-play"></i></span></div>
                        <?php else: ?>
                            <div class="hf-media txt"><i class="fas fa-quote-right q"></i><p><?= e(mb_substr($txt, 0, 180)) ?></p></div>
                        <?php endif; ?>
                        <div class="hf-body">
                            <div class="hf-who">
                                <span class="hf-av"><?= e($fp->Initials) ?><?php if ($fp->AuthorPhoto !== ''): ?><img src="<?= e($fp->AuthorPhoto) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                                <div style="min-width:0;"><b><?= e($fp->AuthorName) ?><span class="hf-role<?= $fp->AuthorRole === 'Teacher' ? ' t' : '' ?>"><?= e(role_label($fp->AuthorRole, $fp->UserId ?? 0)) ?></span></b>
                                    <small><?= e(time_ago($fp->ApprovedAt ?? $fp->CreatedAt)) ?></small></div>
                            </div>
                            <?php if ($fp->Subject !== ''): ?><span class="hf-subj"><i class="fas fa-book-open"></i><?= e($fp->Subject) ?></span><?php endif; ?>
                            <?php if ($txt !== '' && ($fp->Images || $fp->YouTube !== '' || $fp->Video !== '')): ?><p class="hf-text"><?= e($txt) ?></p><?php endif; ?>
                            <div class="hf-meta"><span><span><i class="far fa-thumbs-up"></i><?= (int)$fp->LikeCount ?></span><span><i class="far fa-comment"></i><?= (int)$fp->CommentCount ?></span></span><b>Read more <i class="fas fa-arrow-right" style="color:inherit;margin:0 0 0 2px;"></i></b></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="hf-empty">
                <span class="ic"><i class="fas fa-graduation-cap"></i></span>
                <div><h3>আপনার জ্ঞান শেয়ার করুন</h3><p>নোট, প্রশ্নের সমাধান, শিক্ষামূলক ভিডিও বা পড়াশোনার টিপস পোস্ট করুন — অ্যাডমিন রিভিউ করার পর সবাই দেখতে পাবে।</p></div>
                <a class="hs-more" href="/Feed">Educational Feed <i class="fas fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>
        <div class="hs-foot"><a class="hs-more" href="/Feed">View More Posts <i class="fas fa-arrow-right"></i></a></div>
    </div>
</section>
<?php endif; /* home_section: feed */ ?>

<!-- ========================================= -->
<!-- ✅ BECOME A TUTOR (Teacher Request Form এর সাথে যুক্ত) -->
<!-- ========================================= -->
<?php
    $bt = become_tutor_content();
    $btPerks = array_values(array_filter(array_map('trim', explode("\n", (string)$bt['bt_perks'])), fn($x) => $x !== ''));
    $btLoggedIn = session_get('UserName') !== null;
    $btRole = session_get('UserRole');
    $btStatus = null;
    if ($btLoggedIn && $btRole !== 'Teacher' && current_user_id()) {
        try { $btStatus = db()->GetUserTeacherRequestStatus((int)current_user_id()); } catch (Throwable $e) { $btStatus = null; }
    }
    if (!$btLoggedIn) {
        $btHref = '/Account/Login?returnUrl=' . rawurlencode('/Teacher/RequestForm');
        $btLabel = 'Apply Now'; $btIcon = 'fa-paper-plane'; $btNote = 'নতুন? <a href="/Account/Register">ফ্রি অ্যাকাউন্ট খুলুন</a>';
    } elseif ($btRole === 'Teacher' || $btStatus === 'Approved') {
        $btHref = '/Teacher/TeacherDashboard';
        $btLabel = 'Teacher Dashboard'; $btIcon = 'fa-chalkboard-teacher'; $btNote = 'আপনি ইতিমধ্যে একজন টিউটর 🎉';
    } elseif ($btStatus === 'Pending') {
        $btHref = '/Profile?section=become-tutor';
        $btLabel = 'আবেদন রিভিউ চলছে'; $btIcon = 'fa-hourglass-half'; $btNote = 'অ্যাডমিন অনুমোদন দিলে নোটিফিকেশন পাবেন';
    } else {
        $btHref = '/Teacher/RequestForm';
        $btLabel = 'Apply Now'; $btIcon = 'fa-paper-plane'; $btNote = 'মাত্র ২ মিনিটে আবেদন করুন';
    }
?>
<style>
    .bt-section { padding: 28px 16px 8px; }
    .bt-card {
        position: relative; overflow: hidden;
        max-width: 1100px; margin: 0 auto;
        display: grid; grid-template-columns: 1.1fr 1px 1.2fr 1px 1fr; align-items: center; gap: 0;
        background: #FFFFFF; border: 1px solid #DCECF5; border-radius: 22px;
        box-shadow: 0 18px 45px rgba(8,34,74,0.08);
        padding: 34px 36px;
    }
    .bt-card::before {
        content: ''; position: absolute; top: -70px; right: -70px; width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(2,184,245,0.14) 0%, transparent 70%); pointer-events: none;
    }
    .bt-card::after {
        content: ''; position: absolute; bottom: -80px; left: -60px; width: 200px; height: 200px; border-radius: 50%;
        background: radial-gradient(circle, rgba(255,179,0,0.12) 0%, transparent 70%); pointer-events: none;
    }
    .bt-divider { width: 1px; align-self: stretch; background: linear-gradient(180deg, transparent, #B9D6E6 20%, #B9D6E6 80%, transparent); }

    .bt-col { position: relative; z-index: 1; padding: 0 28px; }
    .bt-kicker { font-size: 20px; color: #334155; font-weight: 500; margin: 0; }
    .bt-big {
        margin: 2px 0 0; font-family: var(--font-heading); font-weight: 800; letter-spacing: 1px; line-height: 1;
        font-size: 64px; color: #08224A;
    }
    .bt-big .hl { color: #02B8F5; }
    .bt-badge {
        display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px;
        background: #CFF2FC; color: #028EA5; font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;
        padding: 4px 12px; border-radius: 20px;
    }

    .bt-tag { margin: 0; font-size: 22px; line-height: 1.5; color: #08224A; font-weight: 600; }
    .bt-tag .l2 { display: block; padding-left: 70px; color: #028EA5; }
    .bt-tag .hl { position: relative; white-space: nowrap; }
    .bt-tag .hl::after { content: ''; position: absolute; left: 0; right: 0; bottom: 2px; height: 8px; background: rgba(255,179,0,0.35); border-radius: 4px; z-index: -1; }
    .bt-perks { list-style: none; padding: 0; margin: 12px 0 0; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 13px; color: #64748B; }
    .bt-perks i { color: #2ecc71; margin-right: 4px; }

    .bt-cta-col { text-align: center; }
    .bt-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 9px;
        padding: 14px 30px; border-radius: 14px; font-weight: 700; font-size: 16px; text-decoration: none;
        background: #02B8F5; color: #fff; box-shadow: 0 10px 24px rgba(2,184,245,0.35);
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }
    .bt-btn:hover { background: #0099D6; color: #fff; transform: translateY(-2px); box-shadow: 0 14px 28px rgba(2,184,245,0.4); }
    .bt-btn:active { transform: scale(.97); }
    .bt-btn.pending { background: #FFF7E0; color: #8a5a00; box-shadow: none; border: 1px solid #FFB300; }
    .bt-btn.pending:hover { background: #FFEBB8; color: #8a5a00; }
    .bt-btn.teacher { background: #134856; box-shadow: 0 10px 24px rgba(19,72,86,0.3); }
    .bt-btn.teacher:hover { background: #08224A; }
    .bt-note { margin: 10px 0 0; font-size: 12.5px; color: #7A8CA0; }
    .bt-note a { color: #028EA5; font-weight: 600; text-decoration: none; }
    .bt-note a:hover { text-decoration: underline; }

    @media (max-width: 900px) {
        .bt-card { grid-template-columns: 1fr; padding: 28px 22px; text-align: center; gap: 18px; }
        .bt-divider { width: 60%; height: 1px; justify-self: center; background: linear-gradient(90deg, transparent, #B9D6E6, transparent); }
        .bt-col { padding: 0; }
        .bt-big { font-size: 48px; }
        .bt-tag { font-size: 19px; }
        .bt-tag .l2 { padding-left: 0; }
        .bt-perks { justify-content: center; }
        .bt-btn { width: 100%; max-width: 320px; }
    }
</style>

<?php if ($bt['bt_enabled'] !== '0'): ?>
<section class="bt-section" style="background-color:<?= e($settings->SecondaryColor) ?>;" aria-label="Become a Tutor">
    <div class="bt-card">
        <div class="bt-col">
            <?php if (!is_blank($bt['bt_badge'])): ?>
                <span class="bt-badge"><i class="fas fa-chalkboard-teacher"></i> <?= e($bt['bt_badge']) ?></span>
            <?php endif; ?>
            <p class="bt-kicker"><?= e($bt['bt_kicker']) ?></p>
            <h2 class="bt-big"><?= highlight_brackets($bt['bt_title'], 'hl') ?></h2>
        </div>

        <div class="bt-divider" aria-hidden="true"></div>

        <div class="bt-col">
            <p class="bt-tag">
                <?= highlight_brackets($bt['bt_line1'], 'hl') ?>
                <?php if (!is_blank($bt['bt_line2'])): ?><span class="l2"><?= highlight_brackets($bt['bt_line2'], 'hl') ?></span><?php endif; ?>
            </p>
            <?php if ($btPerks): ?>
                <ul class="bt-perks">
                    <?php foreach ($btPerks as $pk): ?>
                        <li><i class="fas fa-check-circle"></i><?= e($pk) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="bt-divider" aria-hidden="true"></div>

        <div class="bt-col bt-cta-col">
            <a href="<?= e($btHref) ?>" class="bt-btn <?= $btStatus === 'Pending' ? 'pending' : (($btRole === 'Teacher' || $btStatus === 'Approved') ? 'teacher' : '') ?>">
                <i class="fas <?= e($btIcon) ?>"></i> <?= e($btLabel) ?>
            </a>
            <p class="bt-note"><?= $btNote ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (home_section_on('features')): ?>
<!-- ========================================= -->
<!-- KEY FEATURES -->
<!-- ========================================= -->
<section id="key-features" class="py-8 sm:py-10 md:py-12 lg:py-16" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center">
            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl" style="color:#08224A;">
                Key Features
            </h2>
            <p class="mt-3 text-base sm:text-lg" style="color:<?= e($settings->TextColor) ?>;">
                Everything you need to find the right tutor — safely, all in one place
            </p>
        </div>

        <!-- ৬টা ফিচার — লেখা core/helpers.php → key_features_defaults(), ছবি Admin → Settings → Home Page থেকে -->
        <style>
            .kf-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; margin-top: 40px; }
            .kf-card { background: #fff; border: 1px solid #DCECF5; border-radius: 18px; overflow: hidden; display: flex; flex-direction: column; transition: transform .25s ease, box-shadow .25s ease; }
            .kf-card:hover { transform: translateY(-4px); box-shadow: 0 14px 34px rgba(8,34,74,.10); }
            /* ছবি পুরোটা দেখায় (কাটা হয় না) — ছবির নিজের মাপে উচ্চতা; ছবি না থাকলে ২:১ আইকন প্যানেল */
            .kf-media { position: relative; background: #EAF7FD; overflow: hidden; aspect-ratio: 2 / 1; }
            .kf-media.has-img { aspect-ratio: auto; background: #F4FAFE; }
            .kf-media img { width: 100%; height: auto; display: block; }
            .kf-ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
                background: radial-gradient(circle at 80% 15%, rgba(255,255,255,.35) 0 60px, transparent 61px), linear-gradient(135deg, #E0F7FF 0%, #BDEFFF 55%, #8FE3FB 100%); }
            .kf-ph[hidden] { display: none; }
            .kf-ph i { width: 76px; height: 76px; border-radius: 22px; background: #fff; color: #00B5E0; font-size: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 26px rgba(0,150,200,.18); }
            .kf-body { padding: 20px 22px 24px; }
            .kf-body h3 { margin: 0; font-size: 18px; font-weight: 800; color: #08224A; line-height: 1.3; }
            .kf-tag { margin: 6px 0 0; font-size: 14px; font-weight: 600; color: #00A8D6; }
            .kf-desc { margin: 8px 0 0; font-size: 14px; line-height: 1.6; color: #64748B; }
            @media (max-width: 1023px) { .kf-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; } }
            @media (max-width: 639px) { .kf-grid { grid-template-columns: minmax(0, 1fr); gap: 14px; margin-top: 24px; } .kf-body { padding: 16px 18px 20px; } }
        </style>
        <div class="kf-grid">
            <?php foreach (key_features() as $kfi => $kf): ?>
                <div class="kf-card">
                    <div class="kf-media<?= $kf['image'] !== '' ? ' has-img' : '' ?>">
                        <?php if ($kf['image'] !== ''): ?><img src="<?= e($kf['image']) ?>" alt="<?= e($kf['title']) ?>" loading="lazy" onerror="this.parentNode.classList.remove('has-img'); this.nextElementSibling.hidden = false; this.remove()" /><?php endif; ?>
                        <div class="kf-ph" aria-hidden="true"<?= $kf['image'] !== '' ? ' hidden' : '' ?>><i class="fas <?= e($kf['icon']) ?>"></i></div>
                    </div>
                    <div class="kf-body">
                        <h3><?= e($kf['title']) ?></h3>
                        <p class="kf-tag"><?= e($kf['tagline']) ?></p>
                        <p class="kf-desc"><?= e($kf['desc']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; /* home_section: features */ ?>

<!-- ========================================= -->
<!-- ✅ PACKAGES — Admin → Packages থেকে -->
<!-- ========================================= -->
<?php
$homePk = is_array($ViewBag->HomePackages) ? $ViewBag->HomePackages : ['Teacher' => [], 'Student' => []];
$hpFree = (bool)$ViewBag->StudentFree;
$hpTab = 'Teacher'; // সবসময় "শিক্ষকদের জন্য" ট্যাব আগে খোলা থাকে
if (!$homePk[$hpTab] && $homePk[$hpTab === 'Teacher' ? 'Student' : 'Teacher']) $hpTab = $hpTab === 'Teacher' ? 'Student' : 'Teacher';
?>
<?php if (($homePk['Teacher'] || $homePk['Student']) && home_section_on('packages')): ?>
<?php partial('Package/_CardStyles'); ?>
<section class="hs-sec" id="home-packages" style="background-color:<?= e($settings->SecondaryColor) ?>;" aria-label="Packages">
    <div class="hs-wrap">
        <?php partial('Home/_SectionHead', ['title' => 'Our', 'hl' => 'Packages', 'sub' => 'শিক্ষকদের জন্য Subscription আর শিক্ষার্থীদের জন্য Tuition Post প্যাকেজ — আপনার প্রয়োজন মতো বেছে নিন।', 'href' => '/Package?for=' . strtolower($hpTab), 'btn' => 'View More']); ?>
        <?php if ($homePk['Teacher'] && $homePk['Student']): ?>
            <div style="text-align:center;"><div class="hp-tabs" role="tablist">
                <button type="button" role="tab" data-hp="Teacher" class="<?= $hpTab === 'Teacher' ? 'on' : '' ?>" aria-selected="<?= $hpTab === 'Teacher' ? 'true' : 'false' ?>"><i class="fas fa-chalkboard-user"></i> শিক্ষকদের জন্য</button>
                <button type="button" role="tab" data-hp="Student" class="<?= $hpTab === 'Student' ? 'on' : '' ?>" aria-selected="<?= $hpTab === 'Student' ? 'true' : 'false' ?>"><i class="fas fa-user-graduate"></i> শিক্ষার্থীদের জন্য</button>
            </div></div>
        <?php endif; ?>
        <?php foreach (['Teacher', 'Student'] as $aud): if (!$homePk[$aud]) continue; ?>
            <div class="pk-grid<?= count($homePk[$aud]) < 3 ? ' few' : '' ?>" data-hp-grid="<?= $aud ?>"<?= $aud === $hpTab ? '' : ' hidden' ?>>
                <?php foreach (array_slice($homePk[$aud], 0, 4) as $p) partial('Package/_Card', ['p' => $p, 'cta' => $aud === 'Teacher' ? 'Subscribe করুন' : 'পোস্ট প্যাকেজ নিন', 'studentFree' => $hpFree]); ?>
            </div>
        <?php endforeach; ?>
        <div class="hs-foot"><a class="hs-more" href="/Package?for=<?= strtolower($hpTab) ?>">View All Packages <i class="fas fa-arrow-right"></i></a></div>
    </div>
</section>
<script>
(function () {
    var tabs = document.querySelectorAll('[data-hp]');
    tabs.forEach(function (b) {
        b.addEventListener('click', function () {
            var a = b.getAttribute('data-hp');
            tabs.forEach(function (x) { var on = x === b; x.classList.toggle('on', on); x.setAttribute('aria-selected', on ? 'true' : 'false'); });
            document.querySelectorAll('[data-hp-grid]').forEach(function (g) { g.hidden = g.getAttribute('data-hp-grid') !== a; });
            var m = document.querySelector('#home-packages .hs-head .hs-more'), f = document.querySelector('#home-packages .hs-foot .hs-more');
            [m, f].forEach(function (l) { if (l) l.href = '/Package?for=' + a.toLowerCase(); });
        });
    });
})();
</script>
<?php endif; ?>

<!-- ========================================= -->
<!-- ✅ WHY CHOOSE + FAQ — স্টাইল -->
<!-- ========================================= -->
<style>
    .hm-sec { padding: 64px 16px; }
    .hm-wrap { max-width: 1180px; margin: 0 auto; }
    .hm-h2 { font-family: var(--font-heading); font-weight: 700; color: #08224A; text-align: center; font-size: clamp(26px, 3.4vw, 38px); line-height: 1.25; margin: 0 0 10px; }
    .hm-h2 .hl { color: #02B8F5; }
    .hm-sub { text-align: center; color: #64748B; font-size: 16px; line-height: 1.6; margin: 0 auto 36px; max-width: 640px; }

    .hm-why { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    .hm-why a { display: block; background: #fff; border: 1px solid #DCECF5; border-radius: 20px; padding: 26px 22px; text-decoration: none; color: inherit;
        box-shadow: 0 4px 18px rgba(8,34,74,.04); transition: transform .25s, box-shadow .25s; }
    .hm-why a:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(8,34,74,.09); }
    .hm-why .ic { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 23px; margin-bottom: 16px; }
    .hm-why h3 { font-size: 17.5px; font-weight: 700; color: #08224A; margin: 0 0 8px; }
    .hm-why p { margin: 0; font-size: 14.5px; line-height: 1.65; color: #64748B; }
    @media (max-width: 1024px) { .hm-why { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 560px) { .hm-why { grid-template-columns: 1fr; } }

    .hm-faq { max-width: 860px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; }
    .hm-faq details { background: #fff; border: 1px solid #DCECF5; border-radius: 16px; box-shadow: 0 2px 10px rgba(8,34,74,.03); transition: border-color .2s, box-shadow .2s; }
    .hm-faq details[open] { border-color: #9FDDF5; box-shadow: 0 10px 26px rgba(2,184,245,.1); }
    .hm-faq summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 14px; padding: 18px 20px; font-weight: 700; font-size: 16px; color: #08224A; }
    .hm-faq summary::-webkit-details-marker { display: none; }
    .hm-faq summary h3 { all: unset; flex: 1; }
    .hm-faq summary .q { width: 32px; height: 32px; border-radius: 10px; background: #E3F6FD; color: #028EA5; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
    .hm-faq summary .pl { color: #028EA5; transition: transform .25s; flex-shrink: 0; }
    .hm-faq details[open] summary .pl { transform: rotate(45deg); }
    .hm-faq details { overflow: hidden; }
    .hm-faq summary .pl { transition: transform .35s cubic-bezier(.22,1,.36,1); }
    .hm-faq .ans { transition: opacity .3s ease, transform .35s cubic-bezier(.22,1,.36,1); }
    .hm-faq details.closing .ans, .hm-faq details.opening-start .ans { opacity: 0; transform: translateY(-6px); }
    .hm-faq details.closing summary .pl { transform: rotate(0deg); }
    .hm-faq .ans { padding: 0 20px 18px 66px; font-size: 15px; line-height: 1.75; color: #475569; margin: 0; white-space: pre-line; }
    @media (max-width: 560px) {
        .hm-sec { padding: 46px 14px; }
        .hm-faq summary { font-size: 15px; padding: 15px 16px; } .hm-faq .ans { padding: 0 16px 16px; }
    }
</style>

<?php if (home_section_on('why')): ?>
<!-- ========================================= -->
<!-- ✅ WHY CHOOSE মাষ্টার মশাই -->
<!-- ========================================= -->
<?php
$why = [
    ['fa-user-shield', '#E3F6FD', '#028EA5', 'Verified Tutor Profiles', 'Explore tutor profiles with relevant education, experience and subject information.', '/FindTutor'],
    ['fa-clipboard-list', '#FFF4D6', '#B7791F', 'Tuition Opportunities', 'Browse tuition requirements and discover opportunities that match your subject and location.', '/Tuition/Circular'],
    ['fa-magnifying-glass-location', '#E7F8EF', '#157347', 'Easy Tutor Search', 'Find suitable tutors based on subject, class, curriculum and location.', '/FindTutor#ftFilters'],
    ['fa-headset', '#F1EAFE', '#6D28D9', 'Student & Parent Support', 'Get help when you need assistance finding a suitable teacher.', '/Helpdesk'],
];
?>
<section id="why-choose-us" class="hm-sec" style="background:#fff;">
    <div class="hm-wrap">
        <h2 class="hm-h2">Why Choose <span class="hl">মাষ্টার মশাই</span> for Tuition?</h2>
        <p class="hm-sub">Everything you need to find the right tutor or the right tuition — in one place.</p>
        <div class="hm-why">
            <?php foreach ($why as [$icon, $bg, $fg, $title, $desc, $href]): ?>
                <a href="<?= e($href) ?>">
                    <span class="ic" style="background:<?= $bg ?>;color:<?= $fg ?>;"><i class="fas <?= e($icon) ?>"></i></span>
                    <h3><?= e($title) ?></h3>
                    <p><?= e($desc) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; /* home_section: why */ ?>

<!-- ========================================= -->
<!-- ✅ REVIEWS / TESTIMONIALS (অ্যাডমিন Approve করা রিভিউ) -->
<!-- ========================================= -->
<?php $tsReviews = db()->GetApprovedReviews(12); ?>
<?php if (!empty($tsReviews) && home_section_on('reviews')): ?>
<style>
    .ts-section { padding: 56px 0 50px; overflow: hidden; }
    .ts-head { max-width: 1180px; margin: 0 auto; padding: 0 16px; }
    .ts-title { font-family: var(--font-heading); font-weight: 700; color: #08224A; font-size: 40px; line-height: 1.2; margin: 0 0 8px; }
    .ts-title .hl { color: #02B8F5; }
    .ts-sub { color: #64748B; font-size: 15.5px; margin: 0; }

    .ts-viewport { max-width: 1180px; margin: 0 auto; position: relative; }
    .ts-track { position: relative;
        display: flex; gap: 28px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth;
        padding: 70px 16px 46px; scroll-padding-inline: 16px; scrollbar-width: none; -webkit-overflow-scrolling: touch;
    }
    .ts-track::-webkit-scrollbar { display: none; }

    .ts-card {
        position: relative; flex: 0 0 calc((100% - 28px) / 2); scroll-snap-align: start;
        background: #fff; border-radius: 34px 46px 40px 30px;
        padding: 66px 34px 34px; text-align: center;
        box-shadow: 0 20px 45px rgba(8,34,74,0.08);
        transform: rotate(-1.2deg);
        transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s;
    }
    .ts-card:nth-child(even) { transform: rotate(1.2deg); }
    .ts-card:hover { transform: rotate(0) translateY(-4px); box-shadow: 0 26px 50px rgba(8,34,74,0.12); }
    /* স্পিচ-বাবলের লেজ */
    .ts-card::after {
        content: ''; position: absolute; left: 70px; bottom: -34px; width: 0; height: 0;
        border-left: 26px solid transparent; border-right: 30px solid transparent; border-top: 36px solid #fff;
        filter: drop-shadow(0 8px 6px rgba(8,34,74,0.05));
    }
    .ts-quote { position: absolute; top: 18px; left: 26px; font-family: var(--font-heading); font-size: 110px; line-height: 1; color: #02B8F5; opacity: .9; font-weight: 700; }
    .ts-ava {
        position: absolute; top: -54px; left: 50%; transform: translateX(-50%);
        width: 108px; height: 108px; border-radius: 50%; padding: 4px; background: #fff;
        border: 2px solid #02B8F5; box-shadow: 0 10px 24px rgba(2,184,245,0.2);
    }
    .ts-ava img, .ts-ava span { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-size: 32px; font-weight: 700; }
    .ts-name { margin: 0; font-size: 22px; font-weight: 700; color: #02B8F5; }
    .ts-subt { margin: 2px 0 0; font-size: 14.5px; color: #475569; }
    .ts-role { display: inline-block; margin-top: 6px; font-size: 11px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; background: #CFF2FC; color: #028EA5; }
    .ts-role.Teacher { background: #FFF7E0; color: #8a5a00; }
    .ts-stars { margin: 10px 0 0; color: #FFB300; font-size: 14px; letter-spacing: 2px; }
    .ts-stars .off { color: #DCECF5; }
    .ts-text { margin: 14px auto 0; max-width: 470px; font-size: 15.5px; line-height: 1.75; color: #334155; white-space: pre-line;
        display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden; }

    .ts-dots { display: flex; justify-content: center; gap: 10px; margin-top: 4px; }
    .ts-dot { width: 10px; height: 10px; border-radius: 10px; border: none; padding: 0; background: #B9D6E6; cursor: pointer; transition: width .3s, background .3s; }
    .ts-dot.active { width: 28px; background: #02B8F5; }

    @media (max-width: 860px) {
        .ts-card { flex-basis: 100%; }
        .ts-title { font-size: 30px; }
    }
    @media (max-width: 576px) {
        .ts-section { padding: 40px 0 36px; }
        .ts-title { font-size: 26px; }
        .ts-track { gap: 16px; padding: 64px 16px 44px; }
        .ts-card { padding: 60px 20px 26px; border-radius: 26px 32px 30px 24px; transform: none !important; }
        .ts-quote { font-size: 80px; top: 14px; left: 16px; }
        .ts-ava { width: 96px; height: 96px; top: -48px; }
        .ts-name { font-size: 19px; }
        .ts-text { font-size: 14.5px; }
    }
    @media (prefers-reduced-motion: reduce) { .ts-track { scroll-behavior: auto; } .ts-card { transition: none; } }
</style>

<section class="ts-section" id="reviews" style="background-color:<?= e($settings->SecondaryColor) ?>;" aria-label="Reviews">
    <div class="ts-head">
        <h2 class="ts-title">Stakeholders' <span class="hl">Statements</span> About Us</h2>
        <p class="ts-sub">আমাদের স্টুডেন্ট, অভিভাবক ও টিউটররা মাষ্টারমশাই নিয়ে যা বলছেন</p>
    </div>
    <div class="ts-viewport">
        <div class="ts-track" id="tsTrack">
            <?php foreach ($tsReviews as $r): ?>
                <article class="ts-card">
                    <span class="ts-quote" aria-hidden="true">&ldquo;</span>
                    <div class="ts-ava">
                        <?php if (!is_blank($r->ProfileImageLink)): ?>
                            <img src="<?= e($r->ProfileImageLink) ?>" alt="<?= e($r->FullName) ?>" loading="lazy" />
                        <?php else: ?>
                            <span><?= e($r->Initials) ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="ts-name"><?= e($r->FullName) ?></h3>
                    <?php if (!is_blank($r->Subtitle)): ?><p class="ts-subt"><?= e($r->Subtitle) ?></p><?php endif; ?>
                    <span class="ts-role <?= e($r->Role) ?>"><?= e($r->Role === 'Teacher' ? 'Tutor' : 'Student / Parent') ?></span>
                    <div class="ts-stars" aria-label="<?= e($r->Rating) ?> out of 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star <?= $i > $r->Rating ? 'off' : '' ?>"></i><?php endfor; ?>
                    </div>
                    <p class="ts-text"><?= e($r->Content) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="ts-dots" id="tsDots"></div>
    </div>
</section>
<script>
    (function () {
        var track = document.getElementById('tsTrack'), dotsBox = document.getElementById('tsDots');
        if (!track) return;
        var cards = track.querySelectorAll('.ts-card'), timer = null, dots = [];

        function perView() { return window.innerWidth <= 860 ? 1 : 2; }
        function pages() { return Math.max(1, Math.ceil(cards.length / perView())); }
        function pad() { return parseFloat(getComputedStyle(track).paddingLeft) || 0; }
        function current() {
            var x = track.scrollLeft + pad(), best = 0, bd = Infinity;
            cards.forEach(function (c, i) { var d = Math.abs(c.offsetLeft - x); if (d < bd) { bd = d; best = i; } });
            return Math.round(best / perView());
        }
        function go(i) {
            var n = pages(); i = (i + n) % n;
            var c = cards[Math.min(i * perView(), cards.length - 1)];
            track.scrollTo({ left: c.offsetLeft - pad(), behavior: 'smooth' });
        }

        function buildDots() {
            dotsBox.innerHTML = ''; dots = [];
            var n = pages();
            if (n < 2) return;
            for (var i = 0; i < n; i++) {
                var b = document.createElement('button');
                b.type = 'button'; b.className = 'ts-dot'; b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                (function (k) { b.addEventListener('click', function () { go(k); restart(); }); })(i);
                dotsBox.appendChild(b); dots.push(b);
            }
            mark();
        }
        function mark() { var c = current(); dots.forEach(function (d, i) { d.classList.toggle('active', i === c); }); }
        function restart() { clearInterval(timer); if (pages() > 1) timer = setInterval(function () { go(current() + 1); }, 5000); }

        var t; track.addEventListener('scroll', function () { clearTimeout(t); t = setTimeout(mark, 60); }, { passive: true });
        track.addEventListener('mouseenter', function () { clearInterval(timer); });
        track.addEventListener('mouseleave', restart);
        track.addEventListener('touchstart', function () { clearInterval(timer); }, { passive: true });
        track.addEventListener('touchend', restart, { passive: true });
        var rt; window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { buildDots(); restart(); }, 150); });

        buildDots(); restart();
    })();
</script>
<?php endif; ?>

<?php if (home_section_on('findteacher')): ?>
<!-- ========================================= -->
<!-- FIND YOUR TEACHER (DB Image + Clickable) -->
<!-- ========================================= -->
<section id="need-a-tutor" class="py-8 sm:py-10 md:py-12 lg:py-16" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="relative rounded-2xl overflow-hidden border" style="background-color:#FFFFFF; border-color:#DCECF5;">

            <!-- ✅ Image from DB (or default fallback) -->
            <a href="/FindTutor/Index" class="block group relative" aria-label="Find Your Teacher">
                <img src="<?= e($tutorSectionImg) ?>"
                     alt="Find Your Teacher"
                     class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-[1.02]" />

                <!-- Soft hover overlay -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                     style="background: linear-gradient(135deg, rgba(2,184,245,0.05) 0%, rgba(2,184,245,0.15) 100%);">
                </div>
            </a>

        </div>

    </div>
</section>
<?php endif; /* home_section: findteacher */ ?>

<!-- ========================================= -->
<!-- ✅ FAQ (প্রশ্ন-উত্তর Admin → Settings থেকে বদলানো যায়) -->
<!-- ========================================= -->
<?php $faqs = function_exists('home_faqs') ? home_faqs() : []; ?>
<?php if ($faqs): ?>
<section id="faq" class="hm-sec" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hm-wrap">
        <h2 class="hm-h2">Frequently Asked Questions About Tutors &amp; Tuition</h2>
        <p class="hm-sub">Quick answers for students, parents and tutors.</p>
        <div class="hm-faq">
            <?php foreach ($faqs as $i => [$q, $a]): ?>
                <details>
                    <summary><span class="q">Q<?= $i + 1 ?></span><h3><?= e($q) ?></h3><i class="fas fa-plus pl"></i></summary>
                    <p class="ans"><?= e($a) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script>
    // FAQ — প্রশ্নে ক্লিক করলে উত্তর স্মুথলি খোলে/বন্ধ হয়
    (function () {
        var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
        document.querySelectorAll('.hm-faq details').forEach(function (d) {
            var sum = d.querySelector('summary'), anim = null;
            if (!sum || !d.animate || reduce) return;
            function run(from, to, done) {
                if (anim) anim.cancel();
                anim = d.animate({ height: [from + 'px', to + 'px'] }, { duration: 380, easing: 'cubic-bezier(.22,1,.36,1)' });
                anim.onfinish = function () { anim = null; d.style.height = ''; done && done(); };
                anim.oncancel = function () { anim = null; };
            }
            sum.addEventListener('click', function (e) {
                e.preventDefault();
                var start = d.offsetHeight, bw = d.offsetHeight - d.clientHeight;
                if (d.open && !d.classList.contains('closing')) {
                    d.classList.add('closing');
                    run(start, sum.offsetHeight + bw, function () { d.open = false; d.classList.remove('closing'); });
                } else {
                    d.classList.remove('closing');
                    d.classList.add('opening-start');
                    d.open = true;
                    var end = d.scrollHeight + bw;
                    requestAnimationFrame(function () { d.classList.remove('opening-start'); });
                    run(start, end);
                }
            });
        });
    })();
</script>
<?php endif; ?>
