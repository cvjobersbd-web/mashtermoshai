<?php
$ViewData['Title'] = 'Home';
$settings = ($ViewBag->SiteSettings instanceof SiteSettings) ? $ViewBag->SiteSettings : new SiteSettings();
$heroImages = $settings->GetHeroImageList();
if (count($heroImages) == 0)
{
    $heroImages[] = "https://i.ibb.co.com/pqbXLMx/learn-learning-education-studying-concept.jpg";
}
$heroImages = array_values($heroImages);

// ✅ Tutor Section Image (fallback to default if empty)
$tutorSectionImg = is_blank($settings->TutorSectionImageUrl)
    ? "https://i.ibb.co.com/Hfpwnx46/111.png"
    : $settings->TutorSectionImageUrl;

// ✅ হোম পেইজের জন্য টিউশন পোস্ট
$homeTuitions = is_array($ViewBag->HomeTuitions) ? $ViewBag->HomeTuitions : [];
?>

<!-- ========================================= -->
<!-- HERO SECTION -->
<!-- ========================================= -->
<section class="relative overflow-hidden bg-white">
    <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[85vh]">

        <!-- Left Side -->
        <div class="relative flex flex-col justify-center px-4 sm:px-6 md:px-10 lg:px-20 py-12 sm:py-16 md:py-20 lg:py-0"
             style="background-color:#CFF2FC;
            background-image: radial-gradient(#A9DDEE 1px, transparent 1px);
            background-size: 26px 26px;">

            <div class="relative max-w-lg mx-auto lg:mx-0">
                <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl lg:text-6xl leading-[1.15] text-center lg:text-left"
                    style="color:#08224A;">
                    Find the Right Tutor in Bangladesh
                </h1>

                <p class="mt-4 sm:mt-6 text-sm sm:text-base md:text-lg leading-6 sm:leading-7 md:leading-8 text-center lg:text-left"
                   style="color:<?= e($settings->TextColor) ?>;">
                    Find suitable home tutors and private tutors for school, college, university and professional subjects. Search by subject, class, curriculum and location, or post your tuition requirement and connect with the right teacher.
                </p>

                <?php
                    // লগইন থাকলে Tuition Circular-এ সরাসরি পোস্ট লেখার বক্স খোলে, না থাকলে আগে লগইন
                    $postReqHref = session_get('UserName') !== null ? '/Tuition/Circular?post=1' : '/Account/Login?returnUrl=' . rawurlencode('/Tuition/Circular?post=1');
                ?>
                <div class="text-center lg:text-left mt-6 sm:mt-8 flex flex-col sm:flex-row sm:flex-wrap gap-4 justify-center lg:justify-start">
                    <a href="/FindTutor"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-white transition hover:opacity-90 text-sm sm:text-base shadow-lg"
                       style="background-color:<?= e($settings->PrimaryColor) ?>; white-space:nowrap;">
                        Find a Tutor
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="<?= e($postReqHref) ?>"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold border transition hover:opacity-80 text-sm sm:text-base"
                       style="color:#0099D6; border-color:<?= e($settings->PrimaryColor) ?>; background-color:transparent; white-space:nowrap;">
                        Post Your Tuition Requirement
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="hidden lg:block absolute top-0 right-0 h-full w-40 pointer-events-none"
                 style="background: linear-gradient(to right, #CFF2FC 0%, rgba(207,242,252,0.7) 35%, rgba(207,242,252,0) 100%);
                transform: translateX(100%);
                z-index: 10;">
            </div>
        </div>

        <!-- Right Side: Image / Slider -->
        <div class="relative min-h-[250px] sm:min-h-[300px] md:min-h-[350px] lg:min-h-full order-first lg:order-last overflow-hidden">

            <?php if (count($heroImages) > 1 && $settings->SliderEnabled): ?>
                <!-- ✅ Slider Mode: Multiple Images -->
                <?php $idx = 0;
                foreach ($heroImages as $img): ?>
                    <img src="<?= e($img) ?>"
                         alt="Hero image <?= e($idx + 1) ?>"
                         class="hero-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000"
                         style="opacity:<?= e($idx == 0 ? "1" : "0") ?>;"
                         data-index="<?= e($idx) ?>" />
                    <?php $idx++;
                endforeach; ?>

                <!-- Slider Dots -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    <?php for ($i = 0; $i < count($heroImages); $i++): ?>
                        <button class="slider-dot" data-index="<?= e($i) ?>"
                                style="width:10px; height:10px; border-radius:50%; border:none; cursor:pointer; background:<?= e($i == 0 ? "white" : "rgba(255,255,255,0.5)") ?>; transition: all 0.3s;"></button>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <!-- Single Image Mode -->
                <img src="<?= e($heroImages[0]) ?>"
                     alt="Learning concept"
                     class="absolute inset-0 w-full h-full object-cover" />
            <?php endif; ?>

            <div class="absolute inset-0 pointer-events-none"
                 style="background: linear-gradient(to bottom, rgba(207,242,252,0.3) 0%, rgba(207,242,252,0) 50%, rgba(207,242,252,0) 100%);">
            </div>
        </div>

    </div>
</section>

<?php if (count($heroImages) > 1 && $settings->SliderEnabled): ?>
    <script>
        (function () {
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.slider-dot');
            let current = 0;
            const interval = <?= (int)($settings->SliderIntervalSeconds > 0 ? $settings->SliderIntervalSeconds : 5) ?> * 1000;
            let timer = null;

            function showSlide(idx) {
                if (idx === current) return;
                slides.forEach((s, i) => {
                    s.style.opacity = i === idx ? '1' : '0';
                });
                dots.forEach((d, i) => {
                    d.style.background = i === idx ? 'white' : 'rgba(255,255,255,0.5)';
                    d.style.transform = i === idx ? 'scale(1.3)' : 'scale(1)';
                });
                current = idx;
            }

            function startAuto() {
                stopAuto();
                timer = setInterval(() => {
                    const next = (current + 1) % slides.length;
                    showSlide(next);
                }, interval);
            }

            function stopAuto() {
                if (timer) { clearInterval(timer); timer = null; }
            }

            startAuto();

            dots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    showSlide(parseInt(dot.dataset.index));
                    startAuto();
                });
            });

            const heroContainer = document.querySelector('.hero-slide')?.parentElement;
            if (heroContainer) {
                heroContainer.addEventListener('mouseenter', stopAuto);
                heroContainer.addEventListener('mouseleave', startAuto);
            }
        })();
    </script>
<?php endif; ?>

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
    .hw-step:nth-child(2) { transition-delay: .12s; } .hw-step:nth-child(3) { transition-delay: .24s; } .hw-step:nth-child(4) { transition-delay: .36s; }

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

    /* বাঁকা ড্যাশ তীর (একটা উপরে, একটা নিচে — পালাক্রমে) */
    .hw-arrow {
        position: absolute; z-index: 1; pointer-events: none;
        left: calc(50% + 50px); width: calc(100% - 76px); height: 80px; overflow: visible;
    }
    .hw-arrow.up { top: 2px; }
    .hw-arrow.down { bottom: 2px; }
    .hw-arrow path { fill: none; stroke: #8FD8F2; stroke-width: 2; stroke-dasharray: 7 7; stroke-linecap: round; }
    .hw-step.in .hw-arrow path { animation: hwDash 1.2s linear infinite; }
    @keyframes hwDash { to { stroke-dashoffset: -28; } }

    .hw-step h3 { margin: 18px 0 10px; font-size: 20px; font-weight: 700; color: #08224A; }
    .hw-step p { margin: 0 auto; max-width: 250px; font-size: 14.5px; line-height: 1.65; color: #64748B; }
    .hw-step .hw-go { display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; font-size: 13px; font-weight: 700; color: #028EA5; opacity: 0; transform: translateY(4px); transition: .25s; }
    .hw-step:hover .hw-go { opacity: 1; transform: none; }

    /* ট্যাবলেট: ২ কলাম, তীর লুকানো */
    @media (max-width: 1024px) {
        .hw-steps { grid-template-columns: repeat(2, 1fr); row-gap: 36px; }
        .hw-arrow { display: none; }
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
        .hw-step.in .hw-arrow path { animation: none; }
    }
</style>

<section id="how-it-works" class="hw-section" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hw-wrap">
        <h2 class="hw-title">The ways <span class="hl">Parents / Students</span> find a tutor.</h2>
        <p class="hw-sub">মাত্র ৪টি সহজ ধাপে আপনার পছন্দের টিউটর খুঁজে নিন</p>

        <div class="hw-steps">
            <?php foreach ($hwSteps as $i => [$icon, $title, $desc, $href]): ?>
                <a class="hw-step" href="<?= e($href) ?>">
                    <div class="hw-icon-wrap">
                        <span class="hw-icon"><i class="fas <?= e($icon) ?>"></i><span class="hw-num"><?= $i + 1 ?></span></span>
                        <?php if ($i < count($hwSteps) - 1): $up = $i % 2 === 0; ?>
                            <svg class="hw-arrow <?= $up ? 'up' : 'down' ?>" viewBox="0 0 200 80" preserveAspectRatio="none" aria-hidden="true">
                                <?php if ($up): ?>
                                    <path d="M4 66 C 50 -6, 150 -6, 190 58" />
                                    <path d="M180 50 L 191 60 L 194 45" style="stroke-dasharray:none" />
                                <?php else: ?>
                                    <path d="M4 14 C 50 86, 150 86, 190 22" />
                                    <path d="M180 30 L 191 20 L 194 35" style="stroke-dasharray:none" />
                                <?php endif; ?>
                            </svg>
                        <?php endif; ?>
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
</script>

<!-- ========================================= -->
<!-- ✅ FEATURED TUITIONS (Home Page) -->
<!-- ========================================= -->
<?php if (count($homeTuitions) > 0): ?>
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
                Everything you need to find the best tuition, all in one place
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 md:gap-6 lg:gap-7 mt-6 sm:mt-8 md:mt-10">

            <div class="rounded-2xl overflow-hidden border" style="background-color:#FFFFFF; border-color:#DCECF5;">
                <img src="https://i.ibb.co.com/S40QJtbp/AI-Tutor.webp" alt="Verified Tutors" class="w-full h-36 sm:h-40 md:h-44 object-cover" />
                <div class="p-4 sm:p-5 md:p-6">
                    <h3 class="text-base sm:text-lg font-bold" style="color:#08224A;">Verified Tutors</h3>
                    <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm leading-5 sm:leading-6" style="color:#64748B;">
                        Connect with admin-verified tutors or submit a request — we'll match the perfect one for you
                    </p>
                </div>
            </div>

            <div class="rounded-2xl overflow-hidden border" style="background-color:#FFFFFF; border-color:#DCECF5;">
                <img src="https://i.ibb.co.com/dJKpVzDX/Adaptive-Learning.png" alt="Tuition Circular" class="w-full h-36 sm:h-40 md:h-44 object-cover" />
                <div class="p-4 sm:p-5 md:p-6">
                    <h3 class="text-base sm:text-lg font-bold" style="color:#08224A;">Tuition Circular</h3>
                    <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm leading-5 sm:leading-6" style="color:#64748B;">
                        Browse daily tuition posts. Apply easily and get matched with students in your area.
                    </p>
                </div>
            </div>

            <div class="rounded-2xl overflow-hidden border" style="background-color:#FFFFFF; border-color:#DCECF5;">
                <img src="https://i.ibb.co.com/pB4DZS31/Progress-Tracking.png" alt="Admin Support" class="w-full h-36 sm:h-40 md:h-44 object-cover" />
                <div class="p-4 sm:p-5 md:p-6">
                    <h3 class="text-base sm:text-lg font-bold" style="color:#08224A;">Direct Admin Support</h3>
                    <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm leading-5 sm:leading-6" style="color:#64748B;">
                        We handle all the communication and verification so you can learn or teach safely.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

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
    .hm-faq .ans { padding: 0 20px 18px 66px; font-size: 15px; line-height: 1.75; color: #475569; margin: 0; white-space: pre-line; }
    @media (max-width: 560px) {
        .hm-sec { padding: 46px 14px; }
        .hm-faq summary { font-size: 15px; padding: 15px 16px; } .hm-faq .ans { padding: 0 16px 16px; }
    }
</style>

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

<!-- ========================================= -->
<!-- ✅ REVIEWS / TESTIMONIALS (অ্যাডমিন Approve করা রিভিউ) -->
<!-- ========================================= -->
<?php $tsReviews = db()->GetApprovedReviews(12); ?>
<?php if (!empty($tsReviews)): ?>
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

<!-- ========================================= -->
<!-- ✅ FAQ (প্রশ্ন-উত্তর Admin → Settings থেকে বদলানো যায়) -->
<!-- ========================================= -->
<?php $faqs = home_faqs(); ?>
<?php if ($faqs): ?>
<section id="faq" class="hm-sec" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hm-wrap">
        <h2 class="hm-h2">Frequently Asked Questions About Tutors &amp; Tuition</h2>
        <p class="hm-sub">Quick answers for students, parents and tutors.</p>
        <div class="hm-faq">
            <?php foreach ($faqs as $i => [$q, $a]): ?>
                <details<?= $i === 0 ? ' open' : '' ?>>
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
<?php endif; ?>
