<?php
/**
 * হোম পেজ — "মাষ্টার মশাই — Home Page Update" requirement অনুযায়ী।
 * ক্রম: Hero → How it works → Subject → Class → Location → Tutor promotion → Why choose → SEO info
 *       → Reviews → FAQ → Final CTA (তারপর লেআউটের ফুটার)
 * পুরো পেজে H1 মাত্র একটা (Hero), বাকি সব বড় সেকশনে H2।
 */
$settings = ($ViewBag->SiteSettings instanceof SiteSettings) ? $ViewBag->SiteSettings : new SiteSettings();
$brand = 'মাষ্টার মশাই';

// ===== SEO (লেআউট এগুলো <head> এ বসায়) =====
$ViewData['Title'] = 'Home';
$ViewData['FullTitle'] = 'Find Tutors in Bangladesh | Home & Private Tuition | ' . $brand;
$ViewData['Description'] = 'Find suitable home and private tutors in Bangladesh for school, college, university and professional subjects. Search by subject, class and location with ' . $brand . '.';
$ViewData['Canonical'] = 'https://mashtermoshai.com/';
$ViewData['Robots'] = 'index, follow';

$heroImages = array_values($settings->GetHeroImageList());
if (count($heroImages) == 0) $heroImages[] = 'https://i.ibb.co.com/pqbXLMx/learn-learning-education-studying-concept.jpg';
$ViewData['OgImage'] = $heroImages[0];
$tutorSectionImg = is_blank($settings->TutorSectionImageUrl) ? 'https://i.ibb.co.com/Hfpwnx46/111.png' : $settings->TutorSectionImageUrl;

$counts = is_array($ViewBag->TutorCounts) ? $ViewBag->TutorCounts : [];
$cnt = fn(string $f, string $slug) => (int)($counts[$f][$slug] ?? 0);
$tutorsLabel = fn(int $n) => $n > 0 ? $n . ' tutor' . ($n === 1 ? '' : 's') : '';

$isLoggedIn = session_get('UserName') !== null;
$role = session_get('UserRole');
// "Post Your Tuition Requirement" — লগইন থাকলে সরাসরি পোস্ট লেখার বক্স খোলে
$postHref = $isLoggedIn ? '/Tuition/Circular?post=1' : '/Account/Login?returnUrl=' . rawurlencode('/Tuition/Circular?post=1');

// ===== Tutor promotion বাটন (লগইন / আবেদনের অবস্থা অনুযায়ী) =====
$bt = become_tutor_content();
$btStatus = null;
if ($isLoggedIn && $role !== 'Teacher' && current_user_id()) {
    try { $btStatus = db()->GetUserTeacherRequestStatus((int)current_user_id()); } catch (Throwable $e) { $btStatus = null; }
}
if (!$isLoggedIn) {
    $btHref = '/Account/Register'; $btLabel = 'Join as a Tutor'; $btNote = 'অ্যাকাউন্ট আছে? <a href="/Account/Login?returnUrl=' . e(rawurlencode('/Teacher/RequestForm')) . '">লগইন করে আবেদন করুন</a>';
} elseif ($role === 'Teacher' || $btStatus === 'Approved') {
    $btHref = '/Teacher/TeacherDashboard'; $btLabel = 'Go to Teacher Dashboard'; $btNote = 'আপনি ইতিমধ্যে একজন টিউটর 🎉';
} elseif ($btStatus === 'Pending') {
    $btHref = '/Profile/Show?tab=profile#ab-tutor'; $btLabel = 'Application under review'; $btNote = 'অ্যাডমিন অনুমোদন দিলে নোটিফিকেশন পাবেন';
} else {
    $btHref = '/Teacher/RequestForm'; $btLabel = 'Join as a Tutor'; $btNote = 'মাত্র ২ মিনিটে আবেদন করুন';
}

$reviews = [];
try { $reviews = db()->GetApprovedReviews(12); } catch (Throwable $e) { $reviews = []; }
$faqs = home_faqs();
?>
<style>
    .hm { --hm-navy: #08224A; --hm-sky: #02B8F5; --hm-sky-d: #0099D6; --hm-teal: #028EA5; --hm-gold: #FFB300; --hm-ice: #F5FBFF; --hm-line: #DCECF5; --hm-muted: #64748B; }
    .hm-sec { padding: 64px 16px; }
    .hm-wrap { max-width: 1180px; margin: 0 auto; }
    .hm-h2 { font-family: var(--font-heading); font-weight: 700; color: var(--hm-navy); text-align: center; font-size: clamp(26px, 3.4vw, 38px); line-height: 1.25; margin: 0 0 10px; }
    .hm-h2 .hl { color: var(--hm-sky); }
    .hm-sub { text-align: center; color: var(--hm-muted); font-size: 16px; line-height: 1.6; margin: 0 auto 36px; max-width: 640px; }
    .hm-more { text-align: center; margin-top: 28px; }
    .hm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 13px 26px; border-radius: 999px; font-weight: 700; font-size: 15px;
        text-decoration: none; transition: transform .2s, box-shadow .2s, background .2s, color .2s; border: 2px solid transparent; line-height: 1.2; }
    .hm-btn:hover { transform: translateY(-2px); }
    .hm-btn.solid { background: var(--hm-sky); color: #fff; box-shadow: 0 10px 24px rgba(2,184,245,.3); }
    .hm-btn.solid:hover { background: var(--hm-sky-d); color: #fff; }
    .hm-btn.outline { background: #fff; color: var(--hm-navy); border-color: var(--hm-sky); }
    .hm-btn.outline:hover { background: var(--hm-ice); color: var(--hm-navy); }
    .hm-btn.gold { background: var(--hm-gold); color: var(--hm-navy); box-shadow: 0 10px 24px rgba(255,179,0,.3); }
    .hm-btn.gold:hover { background: #E6A100; color: var(--hm-navy); }
    .hm-btn.ghost { color: #fff; border-color: rgba(255,255,255,.55); }
    .hm-btn.ghost:hover { background: rgba(255,255,255,.12); color: #fff; }
    .hm-link { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: var(--hm-teal); text-decoration: none; padding: 10px 18px; border-radius: 999px; border: 1.5px solid var(--hm-line); background: #fff; }
    .hm-link:hover { border-color: var(--hm-sky); color: var(--hm-navy); }

    /* ===== HERO ===== */
    .hm-hero h1 { font-family: var(--font-heading); color: var(--hm-navy); font-weight: 700; line-height: 1.15; margin: 0; font-size: clamp(32px, 4.6vw, 58px); }
    .hm-hero h1 .hl { color: var(--hm-sky); }
    .hm-hero-desc { margin: 20px 0 0; font-size: 17px; line-height: 1.75; color: #334155; }
    .hm-hero-btns { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
    .hm-hero-tags { display: flex; flex-wrap: wrap; gap: 8px 16px; margin-top: 24px; font-size: 13.5px; color: #475569; }
    .hm-hero-tags span { display: inline-flex; align-items: center; gap: 6px; }
    .hm-hero-tags i { color: var(--hm-teal); }
    @media (max-width: 1023px) { .hm-hero-text { text-align: center; } .hm-hero-btns, .hm-hero-tags { justify-content: center; } }
    @media (max-width: 640px) { .hm-hero-desc { font-size: 15px; } .hm-hero-btns .hm-btn { width: 100%; } }

    /* ===== HOW IT WORKS ===== */
    .hw-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; position: relative; }
    .hw-step { position: relative; text-align: center; text-decoration: none; color: inherit; padding: 0 8px; opacity: 0; transform: translateY(18px);
        transition: opacity .6s ease, transform .6s cubic-bezier(.22,1,.36,1); }
    .hw-step.in { opacity: 1; transform: none; }
    .hw-step:nth-child(2) { transition-delay: .12s; } .hw-step:nth-child(3) { transition-delay: .24s; } .hw-step:nth-child(4) { transition-delay: .36s; }
    .hw-icon-wrap { position: relative; height: 150px; display: flex; align-items: center; justify-content: center; }
    .hw-icon { position: relative; z-index: 2; width: 86px; height: 86px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: var(--hm-sky); color: #fff; font-size: 30px; border: 5px solid #fff; box-shadow: 0 10px 26px rgba(2,184,245,0.28);
        transition: transform .3s cubic-bezier(.22,1,.36,1), box-shadow .3s, background .3s; }
    .hw-num { position: absolute; top: -6px; right: -6px; width: 30px; height: 30px; border-radius: 50%; background: var(--hm-gold); color: var(--hm-navy);
        font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 3px solid #fff; }
    .hw-step:hover .hw-icon { transform: translateY(-6px) scale(1.04); background: var(--hm-sky-d); box-shadow: 0 16px 30px rgba(2,184,245,0.38); }
    .hw-arrow { position: absolute; z-index: 1; pointer-events: none; left: calc(50% + 50px); width: calc(100% - 76px); height: 80px; overflow: visible; }
    .hw-arrow.up { top: 2px; } .hw-arrow.down { bottom: 2px; }
    .hw-arrow path { fill: none; stroke: #8FD8F2; stroke-width: 2; stroke-dasharray: 7 7; stroke-linecap: round; }
    .hw-step.in .hw-arrow path { animation: hwDash 1.2s linear infinite; }
    @keyframes hwDash { to { stroke-dashoffset: -28; } }
    .hw-step h3 { margin: 18px 0 10px; font-size: 19px; font-weight: 700; color: var(--hm-navy); }
    .hw-step p { margin: 0 auto; max-width: 250px; font-size: 14.5px; line-height: 1.65; color: var(--hm-muted); }
    .hw-step small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: 1px; color: var(--hm-teal); text-transform: uppercase; margin-top: 2px; }
    @media (max-width: 1024px) { .hw-steps { grid-template-columns: repeat(2, 1fr); row-gap: 36px; } .hw-arrow { display: none; } .hw-icon-wrap { height: 110px; } }
    @media (max-width: 640px) {
        .hw-steps { grid-template-columns: 1fr; gap: 0; }
        .hw-step { display: grid; grid-template-columns: 76px 1fr; column-gap: 16px; text-align: left; padding: 0 0 26px; }
        .hw-step:not(:last-child)::before { content: ''; position: absolute; left: 37px; top: 72px; bottom: 0; border-left: 2px dashed #8FD8F2; }
        .hw-icon-wrap { height: auto; grid-row: span 3; align-items: flex-start; }
        .hw-icon { width: 70px; height: 70px; font-size: 24px; border-width: 4px; }
        .hw-step h3 { margin: 4px 0 6px; font-size: 17px; }
        .hw-step p { margin: 0; max-width: none; font-size: 14px; }
    }

    /* ===== SUBJECT / CLASS / LOCATION CARDS ===== */
    .hm-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
    .hm-grid.loc { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .hm-card { position: relative; display: flex; align-items: center; gap: 14px; padding: 18px; border-radius: 18px; background: #fff; border: 1px solid var(--hm-line);
        text-decoration: none; color: var(--hm-navy); box-shadow: 0 4px 16px rgba(8,34,74,.04); transition: transform .2s, box-shadow .2s, border-color .2s; min-width: 0; }
    a.hm-card:hover { transform: translateY(-3px); box-shadow: 0 14px 30px rgba(2,184,245,.14); border-color: #9FDDF5; color: var(--hm-navy); }
    .hm-card .ic { width: 50px; height: 50px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 21px;
        background: #E3F6FD; color: var(--hm-teal); transition: background .2s, color .2s; }
    a.hm-card:hover .ic { background: var(--hm-sky); color: #fff; }
    .hm-card .tx { min-width: 0; flex: 1; }
    .hm-card b { display: block; font-size: 15.5px; font-weight: 700; line-height: 1.3; }
    .hm-card small { display: block; margin-top: 3px; font-size: 12.5px; color: var(--hm-muted); }
    .hm-card .go { color: #A6B8C8; font-size: 13px; transition: transform .2s, color .2s; }
    a.hm-card:hover .go { color: var(--hm-sky); transform: translateX(3px); }
    .hm-card.off { background: #FAFCFE; border-style: dashed; color: #64748B; box-shadow: none; }
    .hm-card.off .ic { background: #EEF2F6; color: #94A3B8; }
    .hm-grid .hm-card:nth-child(4n+2) .ic { background: #FFF4D6; color: #B7791F; }
    .hm-grid .hm-card:nth-child(4n+3) .ic { background: #E7F8EF; color: #157347; }
    .hm-grid .hm-card:nth-child(4n+4) .ic { background: #F1EAFE; color: #6D28D9; }
    .hm-grid.loc .hm-card .ic { background: #E3F6FD !important; color: var(--hm-teal) !important; }
    .hm-grid.loc .hm-card.off .ic { background: #EEF2F6 !important; color: #94A3B8 !important; }
    @media (max-width: 1024px) { .hm-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 760px) { .hm-grid, .hm-grid.loc { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; } }
    @media (max-width: 480px) {
        .hm-sec { padding: 46px 14px; }
        .hm-card { flex-direction: column; align-items: flex-start; gap: 10px; padding: 14px; }
        .hm-card .ic { width: 42px; height: 42px; font-size: 18px; }
        .hm-card b { font-size: 14px; }
        .hm-card .go { display: none; }
    }

    /* ===== TUTOR PROMOTION ===== */
    .hm-tp { display: grid; grid-template-columns: 1.1fr .9fr; gap: 36px; align-items: center; background: #fff; border: 1px solid var(--hm-line); border-radius: 26px;
        padding: 40px; box-shadow: 0 18px 45px rgba(8,34,74,.08); position: relative; overflow: hidden; }
    .hm-tp::before { content: ''; position: absolute; top: -80px; left: -80px; width: 240px; height: 240px; border-radius: 50%; background: radial-gradient(circle, rgba(2,184,245,.12), transparent 70%); }
    .hm-tp > * { position: relative; }
    .hm-tp .badge-k { display: inline-flex; align-items: center; gap: 7px; background: #CFF2FC; color: var(--hm-teal); font-size: 11.5px; font-weight: 800; letter-spacing: 1px;
        text-transform: uppercase; padding: 5px 13px; border-radius: 20px; }
    .hm-tp h2 { font-family: var(--font-heading); color: var(--hm-navy); font-weight: 700; font-size: clamp(24px, 3vw, 34px); line-height: 1.25; margin: 14px 0 12px; }
    .hm-tp h2 .hl { color: var(--hm-sky); }
    .hm-tp p.d { color: #475569; font-size: 15.5px; line-height: 1.75; margin: 0; }
    .hm-tp ul { list-style: none; padding: 0; margin: 18px 0 0; display: flex; flex-wrap: wrap; gap: 8px 18px; font-size: 14px; color: #334155; }
    .hm-tp ul i { color: #22A35A; margin-right: 6px; }
    .hm-tp .act { margin-top: 26px; display: flex; flex-wrap: wrap; align-items: center; gap: 14px; }
    .hm-tp .note { font-size: 13px; color: #7A8CA0; }
    .hm-tp .note a { color: var(--hm-teal); font-weight: 600; }
    .hm-tp-img { border-radius: 20px; overflow: hidden; box-shadow: 0 12px 30px rgba(8,34,74,.10); }
    .hm-tp-img img { display: block; width: 100%; height: auto; max-height: 420px; object-fit: cover; }
    @media (max-width: 900px) { .hm-tp { grid-template-columns: 1fr; padding: 28px 22px; text-align: center; } .hm-tp ul, .hm-tp .act { justify-content: center; } }

    /* ===== WHY CHOOSE ===== */
    .hm-why { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    .hm-why a { display: block; background: #fff; border: 1px solid var(--hm-line); border-radius: 20px; padding: 26px 22px; text-decoration: none; color: inherit;
        box-shadow: 0 4px 16px rgba(8,34,74,.04); transition: transform .2s, box-shadow .2s; }
    .hm-why a:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(8,34,74,.09); }
    .hm-why .ic { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 23px; margin-bottom: 16px; }
    .hm-why h3 { font-size: 17.5px; font-weight: 700; color: var(--hm-navy); margin: 0 0 8px; }
    .hm-why p { margin: 0; font-size: 14.5px; line-height: 1.65; color: var(--hm-muted); }
    @media (max-width: 1024px) { .hm-why { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 560px) { .hm-why { grid-template-columns: 1fr; } }

    /* ===== SEO INFO ===== */
    .hm-info { max-width: 860px; margin: 0 auto; background: #fff; border: 1px solid var(--hm-line); border-radius: 24px; padding: 38px 40px; box-shadow: 0 4px 18px rgba(8,34,74,.04); }
    .hm-info .hm-h2 { text-align: left; }
    .hm-info p { font-size: 16px; line-height: 1.85; color: #334155; margin: 14px 0 0; }
    .hm-info a { color: var(--hm-teal); font-weight: 600; text-decoration: none; border-bottom: 1px dashed currentColor; }
    .hm-info a:hover { color: var(--hm-navy); }
    @media (max-width: 640px) { .hm-info { padding: 26px 20px; } .hm-info p { font-size: 15px; } }

    /* ===== REVIEWS ===== */
    .ts-viewport { max-width: 1180px; margin: 0 auto; position: relative; }
    .ts-track { position: relative; display: flex; gap: 28px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth;
        padding: 64px 16px 46px; scroll-padding-inline: 16px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
    .ts-track::-webkit-scrollbar { display: none; }
    .ts-card { position: relative; flex: 0 0 calc((100% - 28px) / 2); scroll-snap-align: start; background: #fff; border-radius: 34px 46px 40px 30px;
        padding: 64px 34px 32px; text-align: center; box-shadow: 0 20px 45px rgba(8,34,74,0.08); transform: rotate(-1.2deg);
        transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s; }
    .ts-card:nth-child(even) { transform: rotate(1.2deg); }
    .ts-card:hover { transform: rotate(0) translateY(-4px); box-shadow: 0 26px 50px rgba(8,34,74,0.12); }
    .ts-card::after { content: ''; position: absolute; left: 70px; bottom: -34px; width: 0; height: 0; border-left: 26px solid transparent; border-right: 30px solid transparent;
        border-top: 36px solid #fff; filter: drop-shadow(0 8px 6px rgba(8,34,74,0.05)); }
    .ts-quote { position: absolute; top: 18px; left: 26px; font-family: var(--font-heading); font-size: 110px; line-height: 1; color: var(--hm-sky); opacity: .9; font-weight: 700; }
    .ts-ava { position: absolute; top: -54px; left: 50%; transform: translateX(-50%); width: 108px; height: 108px; border-radius: 50%; padding: 4px; background: #fff;
        border: 2px solid var(--hm-sky); box-shadow: 0 10px 24px rgba(2,184,245,0.2); }
    .ts-ava img, .ts-ava span { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-size: 32px; font-weight: 700; }
    .ts-stars { margin: 0; color: var(--hm-gold); font-size: 15px; letter-spacing: 3px; }
    .ts-stars .off { color: #DCECF5; }
    .ts-text { margin: 12px auto 0; max-width: 470px; font-size: 15.5px; line-height: 1.75; color: #334155; white-space: pre-line;
        display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden; }
    .ts-name { margin: 16px 0 0; font-size: 19px; font-weight: 700; color: var(--hm-navy); }
    .ts-role { display: inline-block; margin-top: 6px; font-size: 11px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; background: #CFF2FC; color: var(--hm-teal); }
    .ts-role.Teacher { background: #FFF7E0; color: #8a5a00; }
    .ts-subt { display: block; margin-top: 4px; font-size: 13px; color: var(--hm-muted); }
    .ts-dots { display: flex; justify-content: center; gap: 10px; margin-top: 4px; }
    .ts-dot { width: 10px; height: 10px; border-radius: 10px; border: none; padding: 0; background: #B9D6E6; cursor: pointer; transition: width .3s, background .3s; }
    .ts-dot.active { width: 28px; background: var(--hm-sky); }
    @media (max-width: 860px) { .ts-card { flex-basis: 100%; } }
    @media (max-width: 576px) {
        .ts-track { gap: 16px; padding: 62px 16px 44px; }
        .ts-card { padding: 58px 20px 26px; border-radius: 26px 32px 30px 24px; transform: none !important; }
        .ts-quote { font-size: 80px; top: 14px; left: 16px; }
        .ts-ava { width: 96px; height: 96px; top: -48px; }
        .ts-text { font-size: 14.5px; }
    }

    /* ===== FAQ ===== */
    .hm-faq { max-width: 860px; margin: 0 auto; display: flex; flex-direction: column; gap: 12px; }
    .hm-faq details { background: #fff; border: 1px solid var(--hm-line); border-radius: 16px; box-shadow: 0 2px 10px rgba(8,34,74,.03); transition: border-color .2s, box-shadow .2s; }
    .hm-faq details[open] { border-color: #9FDDF5; box-shadow: 0 10px 26px rgba(2,184,245,.1); }
    .hm-faq summary { list-style: none; cursor: pointer; display: flex; align-items: center; gap: 14px; padding: 18px 20px; font-weight: 700; font-size: 16px; color: var(--hm-navy); }
    .hm-faq summary::-webkit-details-marker { display: none; }
    .hm-faq summary h3 { all: unset; flex: 1; }
    .hm-faq summary .q { width: 32px; height: 32px; border-radius: 10px; background: #E3F6FD; color: var(--hm-teal); display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
    .hm-faq summary .pl { color: var(--hm-teal); transition: transform .25s; flex-shrink: 0; }
    .hm-faq details[open] summary .pl { transform: rotate(45deg); }
    .hm-faq .ans { padding: 0 20px 18px 66px; font-size: 15px; line-height: 1.75; color: #475569; margin: 0; }
    @media (max-width: 560px) { .hm-faq summary { font-size: 15px; padding: 15px 16px; } .hm-faq .ans { padding: 0 16px 16px; } }

    /* ===== FINAL CTA ===== */
    .hm-cta { max-width: 1100px; margin: 0 auto; border-radius: 28px; padding: 54px 30px; text-align: center; color: #fff; position: relative; overflow: hidden;
        background: radial-gradient(120% 120% at 100% 0%, rgba(2,184,245,.45) 0%, rgba(2,184,245,0) 55%), linear-gradient(135deg, #08224A 0%, #134856 100%);
        box-shadow: 0 24px 50px rgba(8,34,74,.25); }
    .hm-cta h2 { font-family: var(--font-heading); color: #fff; font-weight: 700; font-size: clamp(26px, 3.4vw, 38px); margin: 0 0 12px; }
    .hm-cta p { color: #CFE6F0; font-size: 16.5px; margin: 0 auto; max-width: 560px; line-height: 1.7; }
    .hm-cta .btns { margin-top: 28px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
    @media (max-width: 560px) { .hm-cta { padding: 40px 20px; border-radius: 22px; } .hm-cta .btns .hm-btn { width: 100%; } }

    @media (prefers-reduced-motion: reduce) {
        .hw-step { opacity: 1; transform: none; transition: none; }
        .hw-step.in .hw-arrow path { animation: none; }
        .ts-track { scroll-behavior: auto; } .ts-card { transition: none; }
    }
</style>

<div class="hm">

<!-- ========================================= -->
<!-- 01. HERO -->
<!-- ========================================= -->
<section class="hm-hero relative overflow-hidden bg-white" aria-label="Find a tutor">
    <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[80vh]">
        <div class="relative flex flex-col justify-center px-4 sm:px-6 md:px-10 lg:px-20 py-12 sm:py-16 md:py-20 lg:py-0"
             style="background-color:#CFF2FC; background-image: radial-gradient(#A9DDEE 1px, transparent 1px); background-size: 26px 26px;">
            <div class="hm-hero-text relative max-w-xl mx-auto lg:mx-0">
                <h1>Find the Right Tutor in <span class="hl">Bangladesh</span></h1>
                <p class="hm-hero-desc">Find suitable home tutors and private tutors for school, college, university and professional subjects. Search by subject, class, curriculum and location, or post your tuition requirement and connect with the right teacher.</p>
                <div class="hm-hero-btns">
                    <a href="/FindTutor" class="hm-btn solid">Find a Tutor <i class="fas fa-arrow-right"></i></a>
                    <a href="<?= e($postHref) ?>" class="hm-btn outline">Post Your Tuition Requirement <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="hm-hero-tags">
                    <span><i class="fas fa-user-check"></i> Verified tutor profiles</span>
                    <span><i class="fas fa-location-dot"></i> All over Bangladesh</span>
                    <span><i class="fas fa-laptop-house"></i> Home &amp; online tuition</span>
                </div>
            </div>
            <div class="hidden lg:block absolute top-0 right-0 h-full w-40 pointer-events-none"
                 style="background: linear-gradient(to right, #CFF2FC 0%, rgba(207,242,252,0.7) 35%, rgba(207,242,252,0) 100%); transform: translateX(100%); z-index: 10;"></div>
        </div>

        <div class="relative min-h-[250px] sm:min-h-[300px] md:min-h-[350px] lg:min-h-full order-first lg:order-last overflow-hidden">
            <?php if (count($heroImages) > 1 && $settings->SliderEnabled): ?>
                <?php foreach ($heroImages as $idx => $img): ?>
                    <img src="<?= e($img) ?>" alt="<?= $idx === 0 ? e('Find tutors in Bangladesh with ' . $brand) : '' ?>"
                         class="hero-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000"
                         style="opacity:<?= $idx === 0 ? '1' : '0' ?>;" data-index="<?= (int)$idx ?>"<?= $idx === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?> />
                <?php endforeach; ?>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    <?php for ($i = 0; $i < count($heroImages); $i++): ?>
                        <button type="button" class="slider-dot" data-index="<?= $i ?>" aria-label="Show slide <?= $i + 1 ?>"
                                style="width:10px; height:10px; border-radius:50%; border:none; cursor:pointer; background:<?= $i === 0 ? 'white' : 'rgba(255,255,255,0.5)' ?>; transition: all 0.3s;"></button>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <img src="<?= e($heroImages[0]) ?>" alt="<?= e('Find tutors in Bangladesh with ' . $brand) ?>" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high" />
            <?php endif; ?>
            <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(to bottom, rgba(207,242,252,0.3) 0%, rgba(207,242,252,0) 50%);"></div>
        </div>
    </div>
</section>

<?php if (count($heroImages) > 1 && $settings->SliderEnabled): ?>
<script>
    (function () {
        var slides = document.querySelectorAll('.hero-slide'), dots = document.querySelectorAll('.slider-dot');
        var current = 0, interval = <?= (int)($settings->SliderIntervalSeconds > 0 ? $settings->SliderIntervalSeconds : 5) ?> * 1000, timer = null;
        function showSlide(idx) {
            if (idx === current) return;
            slides.forEach(function (s, i) { s.style.opacity = i === idx ? '1' : '0'; });
            dots.forEach(function (d, i) { d.style.background = i === idx ? 'white' : 'rgba(255,255,255,0.5)'; d.style.transform = i === idx ? 'scale(1.3)' : 'scale(1)'; });
            current = idx;
        }
        function stopAuto() { if (timer) { clearInterval(timer); timer = null; } }
        function startAuto() { stopAuto(); timer = setInterval(function () { showSlide((current + 1) % slides.length); }, interval); }
        startAuto();
        dots.forEach(function (d) { d.addEventListener('click', function () { showSlide(parseInt(d.dataset.index, 10)); startAuto(); }); });
        var box = slides[0] && slides[0].parentElement;
        if (box) { box.addEventListener('mouseenter', stopAuto); box.addEventListener('mouseleave', startAuto); }
    })();
</script>
<?php endif; ?>

<!-- ========================================= -->
<!-- 02. HOW IT WORKS -->
<!-- ========================================= -->
<?php
$hwSteps = [
    ['fa-user-plus', 'Create Your Account', 'Sign up and complete your student or parent profile.', $isLoggedIn ? '/Profile/Show?tab=profile' : '/Account/Register'],
    ['fa-file-pen', 'Post Your Tuition Requirement', 'Add your class, subjects, preferred location and tuition requirements.', $postHref],
    ['fa-id-card', 'Explore Tutor Applications', 'Review suitable tutor applications and check their profile, education and experience.',
        $isLoggedIn ? '/User/Dashboard?section=applications' : '/Account/Login?returnUrl=' . rawurlencode('/User/Dashboard?section=applications')],
    ['fa-circle-check', 'Choose Your Tutor', 'Select a suitable teacher and start your learning journey.', '/FindTutor'],
];
?>
<section id="how-it-works" class="hm-sec" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hm-wrap">
        <h2 class="hm-h2">How to Find a Tutor with <span class="hl"><?= $brand ?></span></h2>
        <p class="hm-sub">Find a suitable tutor in a few simple steps.</p>
        <div class="hw-steps">
            <?php foreach ($hwSteps as $i => [$icon, $title, $desc, $href]): ?>
                <a class="hw-step" href="<?= e($href) ?>">
                    <div class="hw-icon-wrap">
                        <span class="hw-icon"><i class="fas <?= e($icon) ?>"></i><span class="hw-num"><?= sprintf('%02d', $i + 1) ?></span></span>
                        <?php if ($i < count($hwSteps) - 1): $up = $i % 2 === 0; ?>
                            <svg class="hw-arrow <?= $up ? 'up' : 'down' ?>" viewBox="0 0 200 80" preserveAspectRatio="none" aria-hidden="true">
                                <?php if ($up): ?>
                                    <path d="M4 66 C 50 -6, 150 -6, 190 58" /><path d="M180 50 L 191 60 L 194 45" style="stroke-dasharray:none" />
                                <?php else: ?>
                                    <path d="M4 14 C 50 86, 150 86, 190 22" /><path d="M180 30 L 191 20 L 194 35" style="stroke-dasharray:none" />
                                <?php endif; ?>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <small>Step <?= sprintf('%02d', $i + 1) ?></small>
                        <h3><?= e($title) ?></h3>
                        <p><?= e($desc) ?></p>
                    </div>
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
<!-- 03. FIND TUTORS BY SUBJECT -->
<!-- ========================================= -->
<section id="tutors-by-subject" class="hm-sec" style="background:#fff;">
    <div class="hm-wrap">
        <h2 class="hm-h2">Find Tutors by Subject</h2>
        <p class="hm-sub">Explore tutors based on the subject you need help with.</p>
        <div class="hm-grid">
            <?php foreach (tutor_subject_groups() as $slug => [$label, $icon]): $n = $cnt('subject', $slug); ?>
                <a class="hm-card" href="/FindTutor?subject=<?= e($slug) ?>" data-subject="<?= e($slug) ?>">
                    <span class="ic"><i class="fas <?= e($icon) ?>"></i></span>
                    <span class="tx"><b><?= e($label) ?></b><small><?= $n > 0 ? e($tutorsLabel($n)) : 'Explore tutors' ?></small></span>
                    <i class="fas fa-arrow-right go"></i>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="hm-more"><a class="hm-link" href="/FindTutor#ftFilters">View All Subjects <i class="fas fa-arrow-right"></i></a></div>
    </div>
</section>

<!-- ========================================= -->
<!-- 04. FIND TUTORS BY CLASS -->
<!-- ========================================= -->
<section id="tutors-by-class" class="hm-sec" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hm-wrap">
        <h2 class="hm-h2">Find Tutors by Class</h2>
        <p class="hm-sub">Find teachers according to your academic level and learning needs.</p>
        <div class="hm-grid">
            <?php foreach (tutor_class_groups() as $slug => [$label, $icon]): $n = $cnt('class', $slug); ?>
                <a class="hm-card" href="/FindTutor?class=<?= e($slug) ?>" data-class="<?= e($slug) ?>">
                    <span class="ic"><i class="fas <?= e($icon) ?>"></i></span>
                    <span class="tx"><b><?= e($label) ?></b><small><?= $n > 0 ? e($tutorsLabel($n)) : 'Explore tutors' ?></small></span>
                    <i class="fas fa-arrow-right go"></i>
                </a>
            <?php endforeach; ?>
            <a class="hm-card" href="/FindTutor#ftFilters" style="background:linear-gradient(135deg,#08224A,#134856);color:#fff;border-color:transparent;">
                <span class="ic" style="background:rgba(255,255,255,.12);color:#FFB300;"><i class="fas fa-layer-group"></i></span>
                <span class="tx"><b style="color:#fff;">View All Classes</b><small style="color:#CFE6F0;">Every level &amp; curriculum</small></span>
                <i class="fas fa-arrow-right go" style="color:#fff;"></i>
            </a>
        </div>
    </div>
</section>

<!-- ========================================= -->
<!-- 05. FIND TUTORS BY LOCATION -->
<!-- ========================================= -->
<section id="tutors-by-location" class="hm-sec" style="background:#fff;">
    <div class="hm-wrap">
        <h2 class="hm-h2">Find Tutors by Location in Bangladesh</h2>
        <p class="hm-sub">Find suitable tutors based on your preferred location.</p>
        <div class="hm-grid loc">
            <?php foreach (tutor_location_groups() as $slug => [$name]): $n = $cnt('location', $slug); ?>
                <?php if ($n > 0): ?>
                    <a class="hm-card" href="/FindTutor?location=<?= e($slug) ?>" data-location="<?= e($slug) ?>">
                        <span class="ic"><i class="fas fa-location-dot"></i></span>
                        <span class="tx"><b><?= e($name) ?></b><small><?= e($tutorsLabel($n)) ?> available</small></span>
                        <i class="fas fa-arrow-right go"></i>
                    </a>
                <?php else: /* এখনো এই এলাকায় টিউটর নেই — লিংক করা হয় না */ ?>
                    <div class="hm-card off" data-location="<?= e($slug) ?>">
                        <span class="ic"><i class="fas fa-location-dot"></i></span>
                        <span class="tx"><b><?= e($name) ?></b><small>Tutors coming soon</small></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="hm-more"><a class="hm-link" href="/FindTutor#ftFilters">View All Locations <i class="fas fa-arrow-right"></i></a></div>
    </div>
</section>

<!-- ========================================= -->
<!-- 06. TUTOR PROMOTION -->
<!-- ========================================= -->
<?php if (($bt['bt_enabled'] ?? '1') !== '0'): ?>
<section id="become-a-tutor" class="hm-sec" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hm-wrap">
        <div class="hm-tp">
            <div>
                <span class="badge-k"><i class="fas fa-chalkboard-teacher"></i> For Tutors</span>
                <h2>Are You a Tutor? Find Students Through <span class="hl"><?= $brand ?></span></h2>
                <p class="d">Create your tutor profile, showcase your education and experience, and discover tuition opportunities that match your subject and preferred location.</p>
                <ul>
                    <li><i class="fas fa-check-circle"></i>Free tutor profile</li>
                    <li><i class="fas fa-check-circle"></i>Apply to <a href="/Tuition/Circular" style="color:inherit;font-weight:600;">tuition posts</a></li>
                    <li><i class="fas fa-check-circle"></i>Get direct student requests</li>
                </ul>
                <div class="act">
                    <a href="<?= e($btHref) ?>" class="hm-btn solid"><?= e($btLabel) ?> <i class="fas fa-arrow-right"></i></a>
                    <span class="note"><?= $btNote ?></span>
                </div>
            </div>
            <div class="hm-tp-img">
                <img src="<?= e($tutorSectionImg) ?>" alt="Become a tutor and find tuition opportunities" loading="lazy" />
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========================================= -->
<!-- 07. WHY CHOOSE US -->
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
        <h2 class="hm-h2">Why Choose <span class="hl"><?= $brand ?></span> for Tuition?</h2>
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
<!-- 08. SEO INFORMATION -->
<!-- ========================================= -->
<section id="home-tutor-bangladesh" class="hm-sec" style="background-color:<?= e($settings->SecondaryColor) ?>;">
    <div class="hm-wrap">
        <div class="hm-info">
            <h2 class="hm-h2">Find the Right Home Tutor in Bangladesh</h2>
            <p>Finding the right tutor can make a meaningful difference in a student's learning journey. <?= $brand ?> helps students and parents find suitable tutors based on subject, class, curriculum, location and learning requirements.</p>
            <p>Whether you are looking for a <a href="/FindTutor">home tutor</a>, private tutor or subject specialist, you can explore suitable tutor profiles and <a href="/Tuition/Circular">tuition opportunities</a> through one platform.</p>
            <p>Students and parents can also <a href="<?= e($postHref) ?>">post their tuition requirements</a> and find teachers according to their preferred subject, academic level and location.</p>
        </div>
    </div>
</section>

<!-- ========================================= -->
<!-- 09. REVIEWS (শুধু অ্যাডমিন Approve করা আসল রিভিউ) -->
<!-- ========================================= -->
<?php if (!empty($reviews)): ?>
<section id="reviews" class="hm-sec" style="background:#fff; padding-bottom:50px;" aria-label="Reviews">
    <div class="hm-wrap">
        <h2 class="hm-h2">What Students &amp; Parents Say</h2>
        <p class="hm-sub" style="margin-bottom:0;">Real experiences from students, parents and tutors using <?= $brand ?>.</p>
    </div>
    <div class="ts-viewport">
        <div class="ts-track" id="tsTrack">
            <?php foreach ($reviews as $r): $rt = max(0, min(5, (int)round((float)$r->Rating))); ?>
                <article class="ts-card">
                    <span class="ts-quote" aria-hidden="true">&ldquo;</span>
                    <div class="ts-ava">
                        <?php if (!is_blank($r->ProfileImageLink)): ?>
                            <img src="<?= e($r->ProfileImageLink) ?>" alt="<?= e($r->FullName) ?>" loading="lazy" />
                        <?php else: ?>
                            <span aria-hidden="true"><?= e($r->Initials) ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="ts-stars" aria-label="<?= $rt ?> out of 5 stars"><?php for ($i = 1; $i <= 5; $i++): ?><span class="<?= $i > $rt ? 'off' : '' ?>">★</span><?php endfor; ?></p>
                    <p class="ts-text"><?= e($r->Content) ?></p>
                    <h3 class="ts-name"><?= e($r->FullName) ?></h3>
                    <span class="ts-role <?= e($r->Role) ?>"><?= e($r->Role === 'Teacher' ? 'Tutor' : 'Student / Parent') ?></span>
                    <?php if (!is_blank($r->Subtitle)): ?><span class="ts-subt"><?= e($r->Subtitle) ?></span><?php endif; ?>
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
        function go(i) { var n = pages(); i = (i + n) % n; var c = cards[Math.min(i * perView(), cards.length - 1)]; track.scrollTo({ left: c.offsetLeft - pad(), behavior: 'smooth' }); }
        function mark() { var c = current(); dots.forEach(function (d, i) { d.classList.toggle('active', i === c); }); }
        function restart() { clearInterval(timer); if (pages() > 1) timer = setInterval(function () { go(current() + 1); }, 5000); }
        function buildDots() {
            dotsBox.innerHTML = ''; dots = [];
            var n = pages(); if (n < 2) return;
            for (var i = 0; i < n; i++) {
                var b = document.createElement('button');
                b.type = 'button'; b.className = 'ts-dot'; b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                (function (k) { b.addEventListener('click', function () { go(k); restart(); }); })(i);
                dotsBox.appendChild(b); dots.push(b);
            }
            mark();
        }
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
<!-- 10. FAQ -->
<!-- ========================================= -->
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

<!-- ========================================= -->
<!-- 11. FINAL CTA -->
<!-- ========================================= -->
<section class="hm-sec" style="background:#fff;">
    <div class="hm-cta">
        <h2>Ready to Find the Right Tutor?</h2>
        <p>Start your search today and find a teacher who matches your learning needs.</p>
        <div class="btns">
            <a href="/FindTutor" class="hm-btn gold">Find a Tutor <i class="fas fa-arrow-right"></i></a>
            <a href="<?= e($postHref) ?>" class="hm-btn ghost">Post Your Requirement <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

</div>
