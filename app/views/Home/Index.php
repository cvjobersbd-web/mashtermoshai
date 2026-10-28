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
                    Find Your Perfect Tutor,<br />
                    Excel in Every Subject
                </h1>

                <p class="mt-4 sm:mt-6 text-sm sm:text-base md:text-lg leading-6 sm:leading-7 md:leading-8 text-center lg:text-left"
                   style="color:<?= e($settings->TextColor) ?>;">
                    Bangladesh's most trusted tuition media. Whether you want to hire a teacher or apply for tuitions — our admin team handles everything securely for you.
                </p>

                <div class="text-center lg:text-left mt-6 sm:mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="/Tuition/Circular"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-white transition hover:opacity-90 text-sm sm:text-base shadow-lg"
                       style="background-color:<?= e($settings->PrimaryColor) ?>;">
                        Browse Tuitions
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="/FindTutor/Index"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold border transition hover:opacity-80 text-sm sm:text-base"
                       style="color:#0099D6; border-color:<?= e($settings->PrimaryColor) ?>; background-color:transparent;">
                        Find a Tutor
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
<!-- HOW IT WORKS -->
<!-- ========================================= -->
<section id="how-it-works" class="py-8 sm:py-10 md:py-12 lg:py-16" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

        <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl" style="color:#08224A;">
            How It Works
        </h2>
        <p class="mt-3 text-base sm:text-lg" style="color:<?= e($settings->TextColor) ?>;">
            Get connected with the right tutor or tuition in four simple steps
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6 mt-6 sm:mt-8 md:mt-10 text-left">

            <!-- Step 1: Create a Request -->
            <div class="p-5 sm:p-6 md:p-7 rounded-2xl border" style="background-color:#FFFFFF; border-color:#DCECF5;">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center mx-auto sm:mx-0" style="background-color:#DCECF5;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="#028EA5" class="w-5 h-5 sm:w-6 sm:h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <h3 class="mt-4 sm:mt-5 md:mt-6 text-lg sm:text-xl font-bold text-center sm:text-left" style="color:#08224A;">Submit a Request</h3>
                <p class="mt-2 sm:mt-3 text-xs sm:text-sm leading-5 sm:leading-6 text-center sm:text-left" style="color:#64748B;">
                    Browse tuition circulars or fill out a form to request a specific tutor based on your needs.
                </p>
            </div>

            <!-- Step 2: Admin Matches -->
            <div class="p-5 sm:p-6 md:p-7 rounded-2xl border" style="background-color:#FFFFFF; border-color:#DCECF5;">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center mx-auto sm:mx-0" style="background-color:#DCECF5;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="#028EA5" class="w-5 h-5 sm:w-6 sm:h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <h3 class="mt-4 sm:mt-5 md:mt-6 text-lg sm:text-xl font-bold text-center sm:text-left" style="color:#08224A;">Admin Matches</h3>
                <p class="mt-2 sm:mt-3 text-xs sm:text-sm leading-5 sm:leading-6 text-center sm:text-left" style="color:#64748B;">
                    Our admin team verifies your request and matches you with the most suitable, verified tutor.
                </p>
            </div>

            <!-- Step 3: Start Learning -->
            <div class="p-5 sm:p-6 md:p-7 rounded-2xl border" style="background-color:#FFFFFF; border-color:#DCECF5;">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center mx-auto sm:mx-0" style="background-color:#DCECF5;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="#028EA5" class="w-5 h-5 sm:w-6 sm:h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <h3 class="mt-4 sm:mt-5 md:mt-6 text-lg sm:text-xl font-bold text-center sm:text-left" style="color:#08224A;">Start Learning</h3>
                <p class="mt-2 sm:mt-3 text-xs sm:text-sm leading-5 sm:leading-6 text-center sm:text-left" style="color:#64748B;">
                    Connect securely and start your personalized learning journey with the right guidance.
                </p>
            </div>

            <!-- Step 4: Achieve Goals -->
            <div class="p-5 sm:p-6 md:p-7 rounded-2xl border" style="background-color:#FFFFFF; border-color:#DCECF5;">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full flex items-center justify-center mx-auto sm:mx-0" style="background-color:#DCECF5;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="#028EA5" class="w-5 h-5 sm:w-6 sm:h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                    </svg>
                </div>
                <h3 class="mt-4 sm:mt-5 md:mt-6 text-lg sm:text-xl font-bold text-center sm:text-left" style="color:#08224A;">Achieve Your Goals</h3>
                <p class="mt-2 sm:mt-3 text-xs sm:text-sm leading-5 sm:leading-6 text-center sm:text-left" style="color:#64748B;">
                    Excel in your exams and build a strong foundation for a brighter future.
                </p>
            </div>

        </div>

        <div class="text-center">
            <a href="#key-features"
               class="inline-flex items-center gap-2 mt-6 sm:mt-8 md:mt-10 px-5 sm:px-6 md:px-7 py-2.5 sm:py-3 md:py-3.5 rounded-full font-semibold text-white transition hover:opacity-90 text-sm sm:text-base"
               style="background-color:<?= e($settings->PrimaryColor) ?>;">
                Explore Features
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3 sm:w-4 sm:h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0-4 4m4-4H3" />
                </svg>
            </a>
        </div>

    </div>
</section>

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