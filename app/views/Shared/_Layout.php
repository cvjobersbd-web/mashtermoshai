<?php
// ✅ সাইট সেটিংস লোড (থিম কালার, লোগো, সাইট নামের জন্য)
$layoutSettings = site_settings();

// ✅ Admin page হলে Footer hide করা হবে
$currentController = (string)($GLOBALS['__route']['controller'] ?? '');
$isAdminPage = strcasecmp($currentController, 'Admin') === 0;

// ✅ Branding helpers
$safeSiteName = $layoutSettings->GetSafeSiteName();
$navbarLogo = $layoutSettings->GetNavbarLogo();
$footerLogo = $layoutSettings->GetFooterLogo();

// ✅ লোগোর সাইজ অ্যাডমিন প্যানেল থেকে নিবে, তবে নেভবার ভাঙা রুখতে সর্বোচ্চ ৫০px লিমিট দেওয়া হলো
$safeLogoSize = min($layoutSettings->GetSafeLogoSize(), 50);

$logoRadius = $layoutSettings->GetLogoBorderRadius();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php
    // ===== ✅ SEO: টাইটেল, বর্ণনা, favicon, Google এর জন্য সাইটের নাম =====
    $__route = $GLOBALS['__route'] ?? [];
    $__isHome = strcasecmp((string)($__route['controller'] ?? ''), 'Home') === 0 && strcasecmp((string)($__route['action'] ?? ''), 'Index') === 0;
    $__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $__host = preg_replace('/[^A-Za-z0-9.\-:]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'mashtermoshai.com'));
    $__origin = $__scheme . '://' . $__host;
    $__abs = fn(string $u) => preg_match('#^https?://#i', $u) ? $u : $__origin . '/' . ltrim($u, '/');
    $__pageTitle = $__isHome
        ? $safeSiteName . ' – Find Home Tutors & Tuitions in Bangladesh'
        : ($ViewData['Title'] ?? '') . ' - ' . $safeSiteName;
    $__desc = (string)($ViewData['Description'] ?? ($safeSiteName . ' — বাংলাদেশের বিশ্বস্ত টিউশন মিডিয়া। অভিজ্ঞ হোম টিউটর খুঁজুন অথবা টিউটর হিসেবে টিউশনে Apply করুন — সহজ, নিরাপদ ও দ্রুত।'));
    $__favicon = $__abs($layoutSettings->GetFaviconUrl());
    $__altNames = array_values(array_filter(['মাষ্টারমশাই', 'Mashter Moshai', 'MashterMoshai', $__host], fn($n) => strcasecmp($n, $safeSiteName) !== 0));
    ?>
    <title><?= e($__pageTitle) ?></title>
    <meta name="description" content="<?= e($__desc) ?>" />
    <link rel="canonical" href="<?= e($__origin . strtok($_SERVER['REQUEST_URI'] ?? '/', '?')) ?>" />

    <!-- ✅ ট্যাব আইকন + Google সার্চ রেজাল্টের আইকন (অ্যাডমিন সেটিংয়ের Navbar Logo) -->
    <link rel="icon" href="<?= e($__favicon) ?>" />
    <link rel="shortcut icon" href="<?= e($__favicon) ?>" />
    <link rel="apple-touch-icon" href="<?= e($__favicon) ?>" />

    <!-- ✅ Facebook / WhatsApp এ লিংক শেয়ার করলে যা দেখাবে -->
    <meta property="og:site_name" content="<?= e($safeSiteName) ?>" />
    <meta property="og:title" content="<?= e($__pageTitle) ?>" />
    <meta property="og:description" content="<?= e($__desc) ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= e($__origin . ($_SERVER['REQUEST_URI'] ?? '/')) ?>" />
    <meta property="og:image" content="<?= e($__favicon) ?>" />
    <meta name="application-name" content="<?= e($safeSiteName) ?>" />
    <meta name="apple-mobile-web-app-title" content="<?= e($safeSiteName) ?>" />
    <?php if ($__isHome): ?>
    <!-- ✅ Google যেন সার্চ রেজাল্টে সঠিক সাইটের নাম ও লোগো দেখায় -->
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'WebSite', 'name' => $safeSiteName, 'alternateName' => $__altNames, 'url' => $__origin . '/'],
            ['@type' => 'Organization', 'name' => $safeSiteName, 'url' => $__origin . '/', 'logo' => $__favicon],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php endif; ?>

    <!-- ✅ ফন্ট: শিরোনামে Sora, বাকি লেখায় Poppins (বাংলা অক্ষরের জন্য Hind Siliguri) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Sora:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '<?= e($layoutSettings->PrimaryColor) ?>',
                            cream: '<?= e($layoutSettings->SecondaryColor) ?>'
                        },
                        fontFamily: {
                            sans: ['Poppins', '"Hind Siliguri"', 'system-ui', 'sans-serif'],
                            serif: ['Sora', '"Hind Siliguri"', 'system-ui', 'sans-serif']
                        }
                    }
                }
            }
    </script>

    <script>
            if (!('PageSwapEvent' in window)) document.documentElement.classList.add('no-vt');
    </script>

    <script type="speculationrules">
            {
                "prefetch": [
                    {
                        "where": {
                            "and": [
                                { "href_matches": "/*" },
                                { "not": { "selector_matches": "[data-no-prefetch], [download], [target=_blank]" } }
                            ]
                        },
                        "eagerness": "moderate"
                    }
                ]
            }
    </script>

    <style>
            /* ===== Theme Variables (Admin Settings থেকে লোড) ===== */
            :root {
                --primary-color: <?= e($layoutSettings->PrimaryColor) ?>;
                --secondary-color: <?= e($layoutSettings->SecondaryColor) ?>;
                --accent-color: <?= e($layoutSettings->AccentColor) ?>;
                --text-color: <?= e($layoutSettings->TextColor) ?>;
                --navbar-bg: <?= e($layoutSettings->NavbarBgColor) ?>;
                --footer-bg: <?= e($layoutSettings->FooterBgColor) ?>;
                /* ✅ মাস্টারমশাই থিম — গাঢ় Navbar/Footer এর উপর হালকা লেখা */
                --heading-color: #08224A;
                --button-hover: #0099D6;
                --icon-color: #028EA5;
                --nav-text: #EAF7FC;
                --footer-muted: rgba(255,255,255,0.75);

                /* ✅ ফন্ট */
                --font-body: 'Poppins', 'Hind Siliguri', system-ui, -apple-system, 'Segoe UI', sans-serif;
                --font-heading: 'Sora', 'Hind Siliguri', 'Poppins', system-ui, sans-serif;
                --bs-body-font-family: var(--font-body);
                --bs-font-sans-serif: var(--font-body);
            }

            html, body { font-family: var(--font-body); }
            body { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
            input, select, textarea, button, .btn, .form-control, .form-select, .dropdown-menu, .modal, .tooltip, .popover { font-family: inherit; }
            h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, .font-serif { font-family: var(--font-heading); letter-spacing: -0.01em; }


            body {
                background-color: var(--secondary-color);
            }

            .navbar-custom {
                background-color: var(--navbar-bg) !important;
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }

            .btn-primary-custom {
                background-color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
            }

                .btn-primary-custom:hover {
                    background-color: var(--button-hover) !important;
                    border-color: var(--button-hover) !important;
                }

            .text-primary-custom {
                color: var(--primary-color) !important;
            }

            .bg-primary-custom {
                background-color: var(--primary-color) !important;
            }

            .border-primary-custom {
                border-color: var(--primary-color) !important;
            }

            .footer-custom {
                background-color: var(--footer-bg);
                border-top: 1px solid rgba(0,0,0,0.05);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 99;
            }

                .sidebar-overlay.active {
                    display: block;
                }

            .page-content {
                padding-top: 80px;
            }

            .admin-page .main-content {
                margin-left: 250px;
                padding: 20px;
                min-height: calc(100vh - 80px);
            }

            @media (max-width: 992px) {
                .admin-page .main-content {
                    margin-left: 0;
                    padding: 20px 15px;
                }
            }

            /* ===== Navbar overlap/wrap fix ===== */
            .nav-desktop-link {
                white-space: nowrap;
                flex-shrink: 0;
            }

            .profile-dropdown .dropdown-toggle {
                white-space: nowrap;
            }

                .profile-dropdown .dropdown-toggle > span:not(.caret) {
                    max-width: 170px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

            /* ===== ড্রপডাউন মেনু স্টাইল ===== */
            .profile-dropdown .dropdown-toggle {
                color: var(--nav-text) !important;
                text-decoration: none !important;
                font-weight: 500;
                transition: opacity 0.3s;
                background: none;
                border: none;
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 0;
                cursor: pointer;
            }

                .profile-dropdown .dropdown-toggle:hover {
                    opacity: 0.7;
                }

                .profile-dropdown .dropdown-toggle i {
                    font-size: 18px;
                }

                .profile-dropdown .dropdown-toggle .caret {
                    font-size: 12px;
                    margin-left: 4px;
                }

            .profile-dropdown .dropdown-menu {
                border: none;
                border-radius: 12px;
                box-shadow: 0 8px 30px rgba(0,0,0,0.12);
                padding: 8px 0;
                min-width: 240px;
                background: white;
                margin-top: 8px;
            }

                .profile-dropdown .dropdown-menu .dropdown-header {
                    padding: 10px 20px 6px;
                    font-weight: 600;
                    color: var(--heading-color);
                    border-bottom: 1px solid #f0f0f0;
                    margin-bottom: 4px;
                }

                    .profile-dropdown .dropdown-menu .dropdown-header small {
                        display: block;
                        font-weight: 400;
                        color: #64748B;
                        font-size: 12px;
                    }

                .profile-dropdown .dropdown-menu .dropdown-item {
                    padding: 10px 20px;
                    color: #334155;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                    .profile-dropdown .dropdown-menu .dropdown-item:hover {
                        background-color: var(--secondary-color);
                        color: var(--heading-color);
                    }

                    .profile-dropdown .dropdown-menu .dropdown-item i {
                        width: 20px;
                        text-align: center;
                        color: #64748B;
                        font-size: 14px;
                    }

                    .profile-dropdown .dropdown-menu .dropdown-item:hover i {
                        color: var(--heading-color);
                    }

                .profile-dropdown .dropdown-menu .dropdown-divider {
                    margin: 4px 0;
                    border-color: #f0f0f0;
                }

                .profile-dropdown .dropdown-menu .dropdown-item.text-danger {
                    color: #e74c3c;
                }

                    .profile-dropdown .dropdown-menu .dropdown-item.text-danger i {
                        color: #e74c3c;
                    }

                    .profile-dropdown .dropdown-menu .dropdown-item.text-danger:hover {
                        background-color: #fde8e8;
                    }

                .profile-dropdown .dropdown-menu .dropdown-item .badge-role {
                    font-size: 10px;
                    padding: 2px 10px;
                    border-radius: 20px;
                    font-weight: 600;
                    background: #eef2f6;
                    color: #64748B;
                    margin-left: auto;
                }

                    .profile-dropdown .dropdown-menu .dropdown-item .badge-role.teacher {
                        background: #d1ecf1;
                        color: #0c6b7c;
                    }

                    .profile-dropdown .dropdown-menu .dropdown-item .badge-role.admin {
                        background: #fff3cd;
                        color: #b8860b;
                    }

            .profile-dropdown .dropdown-menu {
                right: 0;
                left: auto;
                top: 100%;
                display: block;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-8px);
                transition: all 0.2s ease;
                pointer-events: none;
            }

            .profile-dropdown.show .dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
                pointer-events: auto;
            }

            /* ===== Notification Bell ===== */
            .notification-wrapper {
                position: relative;
                margin-right: 14px;
            }

            .notification-bell {
                background: none;
                border: none;
                color: var(--nav-text);
                font-size: 20px;
                cursor: pointer;
                position: relative;
                padding: 8px;
                transition: transform 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
            }

                .notification-bell:hover {
                    transform: scale(1.1);
                }

                .notification-bell .notif-badge {
                    position: absolute;
                    top: 2px;
                    right: 0px;
                    background: #e74c3c;
                    color: white;
                    font-size: 10px;
                    font-weight: 700;
                    padding: 2px 6px;
                    border-radius: 10px;
                    min-width: 18px;
                    text-align: center;
                    border: 2px solid var(--navbar-bg);
                    display: none;
                }

                    .notification-bell .notif-badge.show {
                        display: block;
                    }

            .notif-dropdown {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                width: 380px;
                max-width: 92vw;
                background: white;
                border-radius: 14px;
                box-shadow: 0 12px 40px rgba(0,0,0,0.15);
                border: 1px solid rgba(0,0,0,0.06);
                opacity: 0;
                visibility: hidden;
                transform: translateY(-8px);
                transition: all 0.2s ease;
                pointer-events: none;
                z-index: 1000;
                overflow: hidden;
            }

            .notification-wrapper.show .notif-dropdown {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
                pointer-events: auto;
            }

            .notif-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 14px 18px;
                border-bottom: 1px solid #f0f0f0;
                background: #F5FBFF;
            }

                .notif-header h6 {
                    margin: 0;
                    font-size: 14px;
                    font-weight: 700;
                    color: var(--heading-color);
                }

            .notif-header-actions {
                display: flex;
                gap: 8px;
            }

                .notif-header-actions button {
                    background: none;
                    border: none;
                    color: var(--heading-color);
                    font-size: 11px;
                    font-weight: 600;
                    cursor: pointer;
                    padding: 4px 8px;
                    border-radius: 6px;
                    transition: background 0.2s;
                }

                    .notif-header-actions button:hover {
                        background: #DCECF5;
                    }

            .notif-list {
                max-height: 400px;
                overflow-y: auto;
            }

                .notif-list::-webkit-scrollbar {
                    width: 5px;
                }

                .notif-list::-webkit-scrollbar-thumb {
                    background: #B9D6E6;
                    border-radius: 10px;
                }

            .notif-item {
                display: flex;
                gap: 12px;
                padding: 14px 18px;
                border-bottom: 1px solid #f5f5f5;
                transition: background 0.2s;
                cursor: pointer;
                text-decoration: none;
                color: inherit;
            }

                .notif-item:hover {
                    background: #F5FBFF;
                }

                .notif-item.unread {
                    background: #FFFBF0;
                    border-left: 3px solid var(--accent-color);
                }

            .notif-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 16px;
            }

                .notif-icon.info {
                    background: #d1ecf1;
                    color: #0c6b7c;
                }

                .notif-icon.success {
                    background: #d4edda;
                    color: #155724;
                }

                .notif-icon.warning {
                    background: #fff3cd;
                    color: #856404;
                }

                .notif-icon.danger {
                    background: #f8d7da;
                    color: #721c24;
                }

            .notif-content {
                flex: 1;
                min-width: 0;
            }

                .notif-content .notif-title {
                    font-size: 13.5px;
                    font-weight: 700;
                    color: var(--heading-color);
                    margin: 0 0 2px;
                    line-height: 1.3;
                }

                .notif-content .notif-message {
                    font-size: 12.5px;
                    color: #64748B;
                    margin: 0 0 4px;
                    line-height: 1.4;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }

                .notif-content .notif-time {
                    font-size: 11px;
                    color: #7A8CA0;
                }

            .notif-empty {
                text-align: center;
                padding: 40px 20px;
                color: #7A8CA0;
            }

                .notif-empty i {
                    font-size: 36px;
                    color: #DCECF5;
                    margin-bottom: 10px;
                    display: block;
                }

                .notif-empty p {
                    margin: 0;
                    font-size: 13px;
                }

            .notif-footer {
                padding: 10px 18px;
                border-top: 1px solid #f0f0f0;
                background: #F5FBFF;
                text-align: center;
            }

                .notif-footer a {
                    font-size: 12px;
                    font-weight: 600;
                    color: var(--heading-color);
                    text-decoration: none;
                }

                    .notif-footer a:hover {
                        text-decoration: underline;
                    }

            /* ✅ মোবাইল/ট্যাবলেট: বেল হ্যামবার্গারের পাশে, ড্রপডাউন পুরো চওড়ায় */
            @media (max-width: 1279px) {
                .notification-wrapper { margin-right: 0; }
                .notification-bell {
                    width: 40px; height: 44px; padding: 0;
                    background: transparent; border: none; box-shadow: none;
                    color: var(--nav-text); font-size: 21px;
                }
                .notification-bell:hover { background: transparent; transform: scale(1.08); }
                .notification-bell .notif-badge { top: 4px; right: 0; }
                .notif-dropdown {
                    position: fixed; top: 76px; left: 10px; right: 10px;
                    width: auto; max-width: 440px; margin-left: auto;
                }
                .notif-list { max-height: 60vh; }
            }

            /* ===== Smooth Page Transition ===== */
            @media (prefers-reduced-motion: no-preference) {
                @view-transition {
                    navigation: auto;
                }
            }

            ::view-transition-old(root),
            ::view-transition-new(root) {
                animation-duration: 0.3s;
                animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            }

            @keyframes pageEnter {
                from {
                    opacity: 0;
                    transform: translateY(12px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            html.no-vt main {
                animation: pageEnter 0.4s cubic-bezier(0.22, 1, 0.36, 1) backwards;
                transition: opacity 0.2s ease, transform 0.2s ease;
            }

                html.no-vt main.page-leaving {
                    opacity: 0;
                    transform: translateY(-6px);
                    pointer-events: none;
                }

            #pageProgress {
                position: fixed;
                top: 0;
                left: 0;
                height: 3px;
                width: 0;
                background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
                z-index: 9999;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
            }

                #pageProgress.active {
                    opacity: 1;
                    width: 75%;
                    transition: width 6s cubic-bezier(0.1, 0.6, 0.2, 1), opacity 0.2s ease;
                }

            /* ===== Smooth Buttons ===== */
            button,
            .btn,
            a.rounded-full {
                transition-property: color, background-color, border-color, opacity, box-shadow, transform;
                transition-duration: 0.2s;
                transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            }

                .btn-primary-custom:hover,
                a.rounded-full[style*="background-color"]:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 6px 16px rgba(2,184,245, 0.22);
                }

                button:not(:disabled):active,
                .btn:not(:disabled):active,
                a.rounded-full:active,
                .btn-primary-custom:active,
                a.rounded-full[style*="background-color"]:active {
                    transform: scale(0.96);
                    box-shadow: none;
                }

                button:focus-visible,
                .btn:focus-visible,
                a.rounded-full:focus-visible {
                    outline: 2px solid var(--primary-color);
                    outline-offset: 2px;
                }

            @media (prefers-reduced-motion: reduce) {
                html.no-vt main {
                    animation: none;
                    transition: none;
                }

                    html.no-vt main.page-leaving {
                        transform: none;
                    }

                #pageProgress.active {
                    transition: none;
                }

                button, .btn, a.rounded-full {
                    transition: none;
                }

                    button:active, .btn:active, a.rounded-full:active,
                    .btn-primary-custom:hover, .btn-primary-custom:active,
                    a.rounded-full[style*="background-color"]:hover,
                    a.rounded-full[style*="background-color"]:active {
                        transform: none;
                    }
            }

            /* ===== ✅ Logo Container Box (Fixed) ===== */
            .brand-logo-box {
                width: <?= e($safeLogoSize) ?> px;
                height: <?= e($safeLogoSize) ?> px;
                min-width: <?= e($safeLogoSize) ?> px;
                min-height: <?= e($safeLogoSize) ?> px;
                max-width: <?= e($safeLogoSize) ?> px;
                max-height: <?= e($safeLogoSize) ?> px;
                border-radius: <?= e($logoRadius) ?>;
                overflow: hidden;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #fff;
                border: 1px solid rgba(0,0,0,0.05);
                transition: transform 0.2s ease;
                padding: 2px;
                box-sizing: border-box;
            }

                .brand-logo-box:hover {
                    transform: scale(1.04);
                }

                .brand-logo-box img {
                    display: block;
                    width: 100%;
                    height: 100%;
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                }

            .brand-site-name {
                color: var(--nav-text);
                font-weight: 700;
                letter-spacing: 0.3px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 220px;
            }

            /* ফুটারের জন্যও একই বক্স */
            .footer-brand-logo-box {
                width: <?= e($safeLogoSize) ?> px;
                height: <?= e($safeLogoSize) ?> px;
                min-width: <?= e($safeLogoSize) ?> px;
                min-height: <?= e($safeLogoSize) ?> px;
                max-width: <?= e($safeLogoSize) ?> px;
                max-height: <?= e($safeLogoSize) ?> px;
                border-radius: <?= e($logoRadius) ?>;
                overflow: hidden;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #fff;
                border: 1px solid rgba(0,0,0,0.05);
                padding: 2px;
                box-sizing: border-box;
            }

                .footer-brand-logo-box img {
                    display: block;
                    width: 100%;
                    height: 100%;
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                }

            @media (max-width: 768px) {
                .brand-site-name {
                    display: none;
                }
            }

            /* =========================================================
               ✅ MOBILE HAMBURGER + RIGHT-SIDE DRAWER (৫০% চওড়া)
               ========================================================= */
            .m-burger {
                position: relative; width: 44px; height: 44px; border: none; background: rgba(255,255,255,0.08);
                border-radius: 12px; cursor: pointer; flex-shrink: 0; z-index: 1102;
                transition: background .25s ease;
            }
            .m-burger:hover { background: rgba(255,255,255,0.16); }
            .m-burger span {
                position: absolute; left: 11px; width: 22px; height: 2.4px; border-radius: 2px; background: var(--nav-text);
                transition: transform .4s cubic-bezier(.22,1,.36,1), opacity .25s ease, top .4s cubic-bezier(.22,1,.36,1);
            }
            .m-burger span:nth-child(1) { top: 14px; }
            .m-burger span:nth-child(2) { top: 21px; }
            .m-burger span:nth-child(3) { top: 28px; }
            html.m-open .m-burger span:nth-child(1) { top: 21px; transform: rotate(45deg); }
            html.m-open .m-burger span:nth-child(2) { opacity: 0; transform: translateX(8px); }
            html.m-open .m-burger span:nth-child(3) { top: 21px; transform: rotate(-45deg); }

            .m-overlay {
                position: fixed; inset: 0; z-index: 1100; background: rgba(8,34,74,0.45);
                backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
                opacity: 0; visibility: hidden; transition: opacity .4s ease, visibility .4s ease;
            }
            .m-drawer {
                position: fixed; top: 0; right: 0; bottom: 0; z-index: 1101;
                width: 50%; min-width: 180px;
                background: var(--navbar-bg); color: var(--nav-text);
                display: flex; flex-direction: column;
                transform: translateX(100%); visibility: hidden;
                transition: transform .5s cubic-bezier(.22,1,.36,1), visibility .5s;
                box-shadow: -18px 0 40px rgba(8,34,74,0.35);
                border-top-left-radius: 22px; border-bottom-left-radius: 22px;
                padding-bottom: env(safe-area-inset-bottom);
                overscroll-behavior: contain;
            }
            html.m-open { overflow: hidden; }
            html.m-open body { overflow: hidden; }
            html.m-open .m-overlay { opacity: 1; visibility: visible; }
            html.m-open .m-drawer { transform: translateX(0); visibility: visible; }
            @media (min-width: 1280px) { .m-drawer, .m-overlay { display: none; } }

            .m-drawer-head {
                display: flex; align-items: center; justify-content: space-between; gap: 10px;
                padding: 16px 16px 14px; border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            .m-brand { display: flex; align-items: center; gap: 10px; min-width: 0; text-decoration: none; color: #fff; }
            .m-brand-logo { width: 38px; height: 38px; border-radius: 50%; overflow: hidden; background: #fff; flex-shrink: 0; display: flex; }
            .m-brand-logo img { width: 100%; height: 100%; object-fit: contain; }
            .m-brand-name { font-family: var(--font-heading); font-weight: 700; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .m-close {
                width: 38px; height: 38px; border-radius: 50%; border: none; flex-shrink: 0;
                background: rgba(255,255,255,0.1); color: #fff; font-size: 16px; cursor: pointer;
                transition: transform .3s ease, background .2s;
            }
            .m-close:hover { background: rgba(255,255,255,0.2); transform: rotate(90deg); }

            .m-drawer-body { flex: 1; overflow-y: auto; padding: 12px 10px 16px; -webkit-overflow-scrolling: touch; }
            .m-user {
                display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 6px; border-radius: 14px;
                background: rgba(255,255,255,0.08); text-decoration: none; color: #fff;
            }
            .m-avatar {
                width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
                background: var(--primary-color); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center;
                border: 2px solid rgba(255,255,255,0.6);
            }
            .m-avatar img { width: 100%; height: 100%; object-fit: cover; }
            .m-user-info { min-width: 0; display: flex; flex-direction: column; }
            .m-user-info strong { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .m-user-info small { font-size: 11.5px; opacity: .75; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

            .m-label { font-size: 10.5px; letter-spacing: 1.2px; text-transform: uppercase; opacity: .55; font-weight: 700; padding: 14px 12px 6px; }
            .m-nav { display: flex; flex-direction: column; gap: 2px; }
            .m-nav a {
                display: flex; align-items: center; gap: 12px; padding: 12px 12px; border-radius: 12px;
                color: var(--nav-text); text-decoration: none; font-size: 14.5px; font-weight: 500;
                transition: background .2s ease, padding-left .25s ease;
                opacity: 0; transform: translateX(24px);
            }
            .m-nav a i { width: 20px; text-align: center; font-size: 15px; opacity: .85; flex-shrink: 0; }
            .m-nav a span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .m-nav a:hover { background: rgba(255,255,255,0.08); padding-left: 16px; color: #fff; }
            .m-nav a.active { background: var(--primary-color); color: #fff; box-shadow: 0 6px 16px rgba(2,184,245,0.3); }
            .m-nav a.active i { opacity: 1; }
            .m-badge { margin-left: auto; font-style: normal; background: #e74c3c; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 8px; border-radius: 10px; }

            html.m-open .m-nav a { animation: mSlideIn .45s cubic-bezier(.22,1,.36,1) forwards; }
            html.m-open .m-nav a:nth-child(1) { animation-delay: .08s; }
            html.m-open .m-nav a:nth-child(2) { animation-delay: .12s; }
            html.m-open .m-nav a:nth-child(3) { animation-delay: .16s; }
            html.m-open .m-nav a:nth-child(4) { animation-delay: .20s; }
            html.m-open .m-nav a:nth-child(5) { animation-delay: .24s; }
            html.m-open .m-nav a:nth-child(n+6) { animation-delay: .28s; }
            @keyframes mSlideIn { to { opacity: 1; transform: none; } }

            .m-drawer-foot { padding: 12px 14px 16px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column; gap: 8px; }
            .m-logout, .m-cta {
                width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
                padding: 11px; border-radius: 12px; font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer;
            }
            .m-logout { background: rgba(231,76,60,0.12); color: #ff8a7a; border: 1px solid rgba(231,76,60,0.35); }
            .m-logout:hover { background: rgba(231,76,60,0.22); }
            .m-cta { background: var(--primary-color); color: #fff; border: none; }
            .m-cta:hover { background: var(--button-hover); color: #fff; }
            .m-cta.ghost { background: transparent; border: 1px solid rgba(255,255,255,0.35); }

            /* ছোট ফোনে ৫০% চওড়ায় সব যেন সুন্দরভাবে ধরে */
            @media (max-width: 520px) {
                .m-drawer-head { padding: 12px 10px; }
                .m-brand-name { display: none; }
                .m-drawer-body { padding: 8px 6px 12px; }
                .m-user { padding: 9px; gap: 8px; }
                .m-avatar { width: 34px; height: 34px; font-size: 12px; }
                .m-user-info strong { font-size: 13px; }
                .m-label { padding: 12px 8px 4px; }
                .m-nav a { padding: 11px 8px; gap: 9px; font-size: 13.5px; }
                .m-nav a:hover { padding-left: 11px; }
                .m-nav a i { width: 18px; font-size: 14px; }
                .m-drawer-foot { padding: 10px 8px 14px; }
                .m-logout, .m-cta { font-size: 13px; padding: 10px 6px; }
            }

            @media (prefers-reduced-motion: reduce) {
                .m-drawer, .m-overlay, .m-burger span { transition: none; }
                html.m-open .m-nav a { animation: none; opacity: 1; transform: none; }
            }

            /* =========================================================
               ✅ GLOBAL MOBILE POLISH
               ========================================================= */
            body { max-width: 100%; overflow-x: clip; }
            img, video, iframe { max-width: 100%; }
            @media (max-width: 576px) {
                .modal-dialog { margin: 10px; }
                .container, .container-fluid { padding-left: 14px; padding-right: 14px; }
                input, select, textarea { font-size: 16px !important; } /* iOS zoom বন্ধ */
            }
    
        /* ===== Footer social icons ===== */
        .footer-social { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .footer-social-icons { display: flex; flex-wrap: wrap; gap: 8px; }
        .footer-social-link { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.08); color: var(--footer-muted); font-size: 15px; text-decoration: none;
            transition: background .2s, color .2s, transform .2s; }
        .footer-social-link:hover { background: #fff; color: var(--brand); transform: translateY(-2px); }
        @media (max-width: 640px) { .footer-social { justify-content: center; width: 100%; } }
</style>
</head>
<body>
    <?php if (session_get('UserName') === null): ?><?= csrf_field() ?><?php endif; ?>

    <!-- Page transition loading bar -->
    <div id="pageProgress"></div>

    <!-- ========================================= -->
    <!-- NAVBAR -->
    <!-- ========================================= -->
    <header class="sticky-top border-b border-black/5" style="background-color:var(--navbar-bg); z-index:1050;">
        <nav class="max-w-[1440px] mx-auto px-4 sm:px-6 xl:px-8">
            <div class="h-20 flex items-center justify-between gap-4">

                <!-- ✅ Logo + Site Name (Dynamic) -->
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center gap-2.5">
                        <!-- Logo Box -->
                        <div class="brand-logo-box"
                             style="width:<?= e($safeLogoSize) ?>px !important; height:<?= e($safeLogoSize) ?>px !important; min-width:<?= e($safeLogoSize) ?>px !important; max-width:<?= e($safeLogoSize) ?>px !important; min-height:<?= e($safeLogoSize) ?>px !important; max-height:<?= e($safeLogoSize) ?>px !important; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <img src="<?= e($navbarLogo) ?>"
                                 alt="<?= e($safeSiteName) ?> logo"
                                 style="width:100% !important; height:100% !important; max-width:100% !important; max-height:100% !important; object-fit:contain !important; display:block;" />
                        </div>

                        <span class="brand-site-name hidden sm:inline font-serif" style="font-size:16px;">
                            <?= e($safeSiteName) ?>
                        </span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden xl:flex items-center gap-5 2xl:gap-8">
                    <a href="/" class="nav-desktop-link text-sm font-medium transition hover:opacity-70" style="color:var(--nav-text);">
                        <i class="fas fa-house me-1" style="font-size:12px;"></i>Home
                    </a>
                    <a href="/FindTutor" class="nav-desktop-link text-sm font-medium transition hover:opacity-70" style="color:var(--nav-text);">
                        <i class="fas fa-chalkboard-user me-1" style="font-size:12px;"></i>Find Tutor
                    </a>
                    <a href="/Tuition/Circular" class="nav-desktop-link text-sm font-medium transition hover:opacity-70" style="color:var(--nav-text);">
                        <i class="fas fa-clipboard-list me-1" style="font-size:12px;"></i>Tuition Circular
                    </a>
                    <a href="/Notice" class="nav-desktop-link text-sm font-medium transition hover:opacity-70" style="color:var(--nav-text);">
                        <i class="fas fa-bullhorn me-1" style="font-size:12px;"></i>Notice
                    </a>
                    <a href="/Helpdesk" class="nav-desktop-link text-sm font-medium transition hover:opacity-70" style="color:var(--nav-text);">
                        <i class="fas fa-headset me-1" style="font-size:12px;"></i>Helpdesk
                    </a>
                </div>

                <!-- Right Side (বেল সব স্ক্রিনে, প্রোফাইল/লগইন শুধু ডেস্কটপে, ☰ শুধু মোবাইলে) -->
                <div class="flex items-center gap-2 xl:gap-0 flex-shrink-0">
                    <?php if (session_get('UserName') !== null):
                        $userRole = session_get('UserRole');
                        $isTeacher = $userRole === 'Teacher';
                        $isAdmin = $userRole === 'Admin';
                        $userFullName = session_get('UserFullName') ?? session_get('UserName');
                    ?>

                        <!-- Notification Bell -->
                        <div class="notification-wrapper" id="notifWrapper">
                            <button class="notification-bell" id="notifBell" type="button" title="Notifications">
                                <i class="fas fa-bell"></i>
                                <span class="notif-badge" id="notifBadge">0</span>
                            </button>
                            <div class="notif-dropdown" id="notifDropdown">
                                <div class="notif-header">
                                    <h6><i class="fas fa-bell me-1"></i>Notifications</h6>
                                    <div class="notif-header-actions">
                                        <button onclick="markAllRead()" title="Mark all as read">
                                            <i class="fas fa-check-double"></i> Mark all read
                                        </button>
                                        <button onclick="clearAllNotifications()" title="Clear all">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="notif-list" id="notifList">
                                    <div class="notif-empty">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Loading...</p>
                                    </div>
                                </div>
                                <div class="notif-footer">
                                    <a href="/Notification/All">View All Notifications</a>
                                </div>
                            </div>
                        </div>

                        <div class="profile-dropdown dropdown hidden xl:block" id="profileDropdown">
                            <button class="dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <span><?= e($userFullName) ?></span>
                                <span class="caret"><i class="fas fa-chevron-down"></i></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                                <li class="dropdown-header">
                                    <?= e($userFullName) ?>
                                    <small><?= e(session_get('UserName')) ?></small>
                                </li>
                                <li><hr class="dropdown-divider" /></li>
                                <li>
                                    <a class="dropdown-item" href="/Profile">
                                        <i class="fas fa-id-card"></i> My Profile
                                    </a>
                                </li>

                                <?php if ($userRole === 'User' || $userRole === 'Admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="/User/Dashboard">
                                            <i class="fas fa-chart-pie"></i> Student Dashboard
                                            <span class="badge-role" style="background:#e3f2fd; color:#1565c0;">Student</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($isTeacher): ?>
                                    <li>
                                        <a class="dropdown-item" href="/Teacher/TeacherDashboard">
                                            <i class="fas fa-chalkboard-teacher"></i> Teacher Dashboard
                                            <span class="badge-role teacher">Teacher</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($isAdmin): ?>
                                    <li>
                                        <a class="dropdown-item" href="/Admin/Dashboard">
                                            <i class="fas fa-shield-alt"></i> Admin Panel
                                            <span class="badge-role admin">Admin</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="/Admin/Settings">
                                            <i class="fas fa-cog"></i> Site Settings
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider" /></li>
                                <li>
                                    <form method="post" action="/Account/Logout" id="logoutFormDesktop">
                                        <?= csrf_field() ?>
                                        <a class="dropdown-item text-danger" href="#" onclick="document.getElementById('logoutFormDesktop').submit(); return false;">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="hidden xl:flex items-center gap-2">
                            <a href="/Account/Login" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full font-semibold text-white text-sm transition hover:opacity-90" style="background-color:var(--primary-color);">
                                <i class="fas fa-sign-in-alt"></i> Login
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0-4 4m4-4H3" />
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- ✅ Mobile Hamburger (animated → X) -->
                    <button id="mobileMenuButton" type="button" class="m-burger xl:hidden" aria-label="Open menu" aria-controls="mobileDrawer" aria-expanded="false">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- ========================================= -->
    <!-- ✅ MOBILE DRAWER (ডান দিক থেকে স্লাইড করে আসে) -->
    <!-- ========================================= -->
    <?php
        $mPath = '/' . strtolower(trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/'));
        $mActive = function (string $href) use ($mPath): string {
            $h = '/' . strtolower(trim($href, '/'));
            if ($h === '/') return ($mPath === '/' || $mPath === '/home' || $mPath === '/home/index') ? 'active' : '';
            return ($mPath === $h || str_starts_with($mPath, $h . '/')) ? 'active' : '';
        };
        $mLoggedIn = session_get('UserName') !== null;
        $mRole = session_get('UserRole');
        $mName = session_get('UserFullName') ?? session_get('UserName') ?? '';
        $mImg = session_get('ProfileImageLink');
        $mUnread = 0;
        if ($mLoggedIn && current_user_id()) { try { $mUnread = db()->GetUnreadNotificationCount((int)current_user_id()); } catch (Throwable $e) {} }
    ?>
    <div class="m-overlay" id="mobileDrawerOverlay" aria-hidden="true"></div>
    <aside class="m-drawer" id="mobileDrawer" aria-hidden="true" aria-label="Mobile menu">
        <div class="m-drawer-head">
            <a href="/" class="m-brand">
                <span class="m-brand-logo"><img src="<?= e($navbarLogo) ?>" alt="logo" /></span>
                <span class="m-brand-name"><?= e($safeSiteName) ?></span>
            </a>
            <button type="button" class="m-close" id="mobileDrawerClose" aria-label="Close menu"><i class="fas fa-times"></i></button>
        </div>

        <div class="m-drawer-body">
            <?php if ($mLoggedIn): ?>
                <a href="/Profile" class="m-user">
                    <span class="m-avatar">
                        <?php if (!is_blank($mImg)): ?><img src="<?= e($mImg) ?>" alt="" /><?php else: ?><?= e(initials($mName, 'U')) ?><?php endif; ?>
                    </span>
                    <span class="m-user-info">
                        <strong><?= e($mName) ?></strong>
                        <small><?= e($mRole === 'User' ? 'Student' : $mRole) ?> · @<?= e(session_get('UserName')) ?></small>
                    </span>
                </a>
            <?php endif; ?>

            <div class="m-label">Menu</div>
            <nav class="m-nav">
                <a href="/" class="<?= $mActive('/') ?>"><i class="fas fa-house"></i><span>Home</span></a>
                <a href="/FindTutor" class="<?= $mActive('/FindTutor') ?>"><i class="fas fa-chalkboard-user"></i><span>Find Tutor</span></a>
                <a href="/Tuition/Circular" class="<?= $mActive('/Tuition') ?>"><i class="fas fa-clipboard-list"></i><span>Tuition Circular</span></a>
                <a href="/Notice" class="<?= $mActive('/Notice') ?>"><i class="fas fa-bullhorn"></i><span>Notice</span></a>
                <a href="/Helpdesk" class="<?= $mActive('/Helpdesk') ?>"><i class="fas fa-headset"></i><span>Helpdesk</span></a>
            </nav>

            <?php if ($mLoggedIn): ?>
                <div class="m-label">My Account</div>
                <nav class="m-nav">
                    <a href="/Profile" class="<?= $mActive('/Profile') ?>"><i class="fas fa-id-card"></i><span>My Profile</span></a>
                    <?php if ($mRole === 'User' || $mRole === 'Admin'): ?>
                        <a href="/User/Dashboard" class="<?= $mActive('/User') ?>"><i class="fas fa-chart-pie"></i><span>Student Dashboard</span></a>
                    <?php endif; ?>
                    <?php if ($mRole === 'Teacher'): ?>
                        <a href="/Teacher/TeacherDashboard" class="<?= $mActive('/Teacher') ?>"><i class="fas fa-chalkboard-teacher"></i><span>Teacher Dashboard</span></a>
                    <?php endif; ?>
                </nav>

                <?php if ($mRole === 'Admin'): ?>
                    <div class="m-label">Admin Panel</div>
                    <nav class="m-nav">
                        <a href="/Admin/Dashboard" class="<?= $mActive('/Admin/Dashboard') ?>"><i class="fas fa-shield-alt"></i><span>Admin Dashboard</span></a>
                        <a href="/Admin/Settings" class="<?= $mActive('/Admin/Settings') ?>"><i class="fas fa-cog"></i><span>Site Settings</span></a>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="m-drawer-foot">
            <?php if ($mLoggedIn): ?>
                <form method="post" action="/Account/Logout">
                    <?= csrf_field() ?>
                    <button type="submit" class="m-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            <?php else: ?>
                <a href="/Account/Login" class="m-cta"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="/Account/Register" class="m-cta ghost"><i class="fas fa-user-plus"></i> Create Account</a>
            <?php endif; ?>
        </div>
    </aside>

    <main>
        <?= $RenderBody ?>
    </main>

    <!-- ========================================= -->
    <!-- FOOTER (Admin page এ hide করা হয়েছে) -->
    <!-- ========================================= -->
    <?php if (!$isAdminPage): ?>
        <footer class="border-t border-black/5" style="background-color:var(--footer-bg);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
                <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6">

                    <div class="flex items-start gap-2.5 text-center sm:text-left">
                        <!-- ✅ Footer Logo (Dynamic) -->
                        <div class="footer-brand-logo-box"
                             style="width:<?= e($safeLogoSize) ?>px !important; height:<?= e($safeLogoSize) ?>px !important; min-width:<?= e($safeLogoSize) ?>px !important; max-width:<?= e($safeLogoSize) ?>px !important; min-height:<?= e($safeLogoSize) ?>px !important; max-height:<?= e($safeLogoSize) ?>px !important; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <img src="<?= e($footerLogo) ?>"
                                 alt="<?= e($safeSiteName) ?> logo"
                                 style="width:100% !important; height:100% !important; max-width:100% !important; max-height:100% !important; object-fit:contain !important; display:block;" />
                        </div>

                        <div>
                            <p class="font-serif text-base sm:text-lg font-bold tracking-wide" style="color:#FFFFFF;">
                                <?= e($safeSiteName) ?>
                            </p>
                            <p class="mt-1 text-xs sm:text-sm" style="color:rgba(255,255,255,0.8);">
                                Personalized, adaptive, and interactive learning experiences powered by AI.
                            </p>
                        </div>
                    </div>

                    <?php $__social = social_links(); $__pl = social_platforms(); ?>
                    <?php if ($__social): ?>
                    <div class="footer-social">
                        <span class="text-sm" style="color:var(--footer-muted);">Follow us</span>
                        <div class="footer-social-icons">
                            <?php foreach ($__social as $__s): $__d = $__pl[$__s['p']]; $__ext = str_starts_with($__s['url'], 'http'); ?>
                                <a href="<?= e($__s['url']) ?>" aria-label="<?= e($__d[0]) ?>" title="<?= e($__d[0]) ?>" class="footer-social-link"
                                   style="--brand:<?= e($__d[3]) ?>;"<?= $__ext ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                                    <i class="<?= e($__d[1]) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    </div>
                </div>

                <div class="border-t border-white/15 mt-8 pt-6 text-center">
                    <p class="text-xs sm:text-sm" style="color:var(--footer-muted);">
                        &copy; <?= date('Y') ?> <?= e($safeSiteName) ?>. All Rights Reserved.
                    </p>
                </div>
            </div>
        </footer>
    <?php endif; ?>

    <!-- ========================================= -->
    <!-- JAVASCRIPT -->
    <!-- ========================================= -->
    <script>
            // ===== SMOOTH PAGE TRANSITION =====
            (function () {
                const progress = document.getElementById('pageProgress');
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const LEAVE_DELAY = prefersReducedMotion ? 0 : 200;
                const nativeTransition = document.documentElement.classList.contains('no-vt') === false;
                let navigating = false;

                function startProgress() {
                    if (!progress) return;
                    progress.classList.add('active');
                }

                function resetState() {
                    navigating = false;
                    document.querySelector('main')?.classList.remove('page-leaving');
                    if (progress) {
                        progress.classList.remove('active');
                        progress.style.width = '0';
                        void progress.offsetWidth;
                        progress.style.width = '';
                    }
                }

                window.smoothNavigate = function (url) {
                    if (navigating) return;
                    navigating = true;
                    startProgress();
                    if (nativeTransition) {
                        window.location.href = url;
                    } else {
                        document.querySelector('main')?.classList.add('page-leaving');
                        setTimeout(function () { window.location.href = url; }, LEAVE_DELAY);
                    }
                    setTimeout(resetState, 10000);
                };

                document.addEventListener('click', function (e) {
                    if (e.defaultPrevented) return;
                    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || /^(javascript|mailto|tel):/i.test(href)) return;
                    if (link.target && link.target !== '_self') return;
                    if (link.hasAttribute('download') || link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-no-transition')) return;

                    let url;
                    try { url = new URL(link.href, window.location.href); } catch { return; }
                    if (url.origin !== window.location.origin) return;
                    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;

                    if (nativeTransition) {
                        startProgress();
                        setTimeout(resetState, 10000);
                        return;
                    }

                    e.preventDefault();
                    window.smoothNavigate(url.href);
                });

                document.addEventListener('submit', function (e) {
                    if (e.defaultPrevented) return;
                    startProgress();
                    setTimeout(resetState, 10000);
                });

                window.addEventListener('pageshow', function (e) {
                    if (e.persisted) resetState();
                });
            })();
    </script>

    <script>
            // ===== ✅ Mobile Drawer (ডান দিক থেকে স্লাইড) =====
            (function () {
                const btn = document.getElementById('mobileMenuButton');
                const drawer = document.getElementById('mobileDrawer');
                const overlay = document.getElementById('mobileDrawerOverlay');
                const closeBtn = document.getElementById('mobileDrawerClose');
                if (!btn || !drawer || !overlay) return;
                let lastFocus = null;

                function open() {
                    lastFocus = document.activeElement;
                    document.documentElement.classList.add('m-open');
                    drawer.setAttribute('aria-hidden', 'false');
                    btn.setAttribute('aria-expanded', 'true');
                    setTimeout(function () { closeBtn && closeBtn.focus({ preventScroll: true }); }, 50);
                }
                function close() {
                    document.documentElement.classList.remove('m-open');
                    drawer.setAttribute('aria-hidden', 'true');
                    btn.setAttribute('aria-expanded', 'false');
                    if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
                }
                window.closeMobileDrawer = close;

                btn.addEventListener('click', function () {
                    document.documentElement.classList.contains('m-open') ? close() : open();
                });
                overlay.addEventListener('click', close);
                closeBtn && closeBtn.addEventListener('click', close);
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
                drawer.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { setTimeout(close, 120); }); });
                window.addEventListener('resize', function () { if (window.innerWidth >= 1280) close(); });
                window.addEventListener('pageshow', function () { close(); });

                // ডান দিকে সোয়াইপ করলে বন্ধ হবে
                let sx = null, sy = null, dx = 0;
                drawer.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; sy = e.touches[0].clientY; dx = 0; drawer.style.transition = 'none'; }, { passive: true });
                drawer.addEventListener('touchmove', function (e) {
                    if (sx === null) return;
                    dx = e.touches[0].clientX - sx;
                    const dy = Math.abs(e.touches[0].clientY - sy);
                    if (dx > 0 && dx > dy) drawer.style.transform = 'translateX(' + dx + 'px)';
                }, { passive: true });
                drawer.addEventListener('touchend', function () {
                    drawer.style.transition = ''; drawer.style.transform = '';
                    if (dx > 70) close();
                    sx = null;
                });
            })();

            // ===== Profile Dropdown =====
            document.addEventListener('DOMContentLoaded', function () {
                const dropdownToggle = document.querySelector('.profile-dropdown .dropdown-toggle');
                if (dropdownToggle) {
                    dropdownToggle.addEventListener('click', function (e) {
                        e.preventDefault();
                        const parent = this.closest('.profile-dropdown');
                        if (parent) {
                            parent.classList.toggle('show');
                        }
                        document.getElementById('notifWrapper')?.classList.remove('show');
                    });
                }

                document.addEventListener('click', function (e) {
                    const dropdown = document.querySelector('.profile-dropdown');
                    if (dropdown && !dropdown.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });

            // ===== NOTIFICATION SYSTEM =====
            (function () {
                const wrapper = document.getElementById('notifWrapper');
                const bell = document.getElementById('notifBell');
                const badge = document.getElementById('notifBadge');
                const list = document.getElementById('notifList');

                if (!wrapper || !bell) return;

                function getToken() {
                    const token = document.querySelector('input[name="__RequestVerificationToken"]');
                    return token ? token.value : '';
                }

                function loadUnreadCount() {
                    fetch('/Notification/GetUnreadCount')
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.count > 0) {
                                badge.textContent = data.count > 99 ? '99+' : data.count;
                                badge.classList.add('show');
                            } else {
                                badge.classList.remove('show');
                            }
                        })
                        .catch(() => { });
                }

                function loadNotifications() {
                    list.innerHTML = '<div class="notif-empty"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

                    fetch('/Notification/GetNotifications')
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success || !data.notifications || data.notifications.length === 0) {
                                list.innerHTML = `
                                                <div class="notif-empty">
                                                    <i class="fas fa-bell-slash"></i>
                                                    <p>No notifications yet</p>
                                                </div>`;
                                return;
                            }

                            let html = '';
                            data.notifications.forEach(n => {
                                const unreadClass = n.isRead ? '' : 'unread';
                                const typeClass = n.type || 'info';
                                const icon = n.icon || 'fa-bell';
                                const linkAttr = n.link ? `data-link="${n.link}"` : '';

                                html += `
                                                <div class="notif-item ${unreadClass}" ${linkAttr} data-id="${n.id}">
                                                    <div class="notif-icon ${typeClass}">
                                                        <i class="fas ${icon}"></i>
                                                    </div>
                                                    <div class="notif-content">
                                                        <p class="notif-title">${escapeHtml(n.title)}</p>
                                                        <p class="notif-message">${escapeHtml(n.message)}</p>
                                                        <span class="notif-time"><i class="far fa-clock me-1"></i>${n.timeAgo}</span>
                                                    </div>
                                                </div>`;
                            });

                            list.innerHTML = html;

                            list.querySelectorAll('.notif-item').forEach(item => {
                                item.addEventListener('click', function () {
                                    const id = this.getAttribute('data-id');
                                    const link = this.getAttribute('data-link');
                                    markOneRead(id);
                                    if (link) {
                                        window.smoothNavigate ? window.smoothNavigate(link) : (window.location.href = link);
                                    }
                                });
                            });
                        })
                        .catch(() => {
                            list.innerHTML = '<div class="notif-empty"><i class="fas fa-exclamation-circle"></i><p>Failed to load</p></div>';
                        });
                }

                function escapeHtml(text) {
                    if (!text) return '';
                    return String(text)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }

                function markOneRead(id) {
                    const formData = new FormData();
                    formData.append('id', id);
                    const headers = { 'RequestVerificationToken': getToken() };

                    fetch('/Notification/MarkAsRead', {
                        method: 'POST',
                        body: formData,
                        headers: headers
                    }).then(() => {
                        loadUnreadCount();
                    }).catch(() => { });
                }

                window.markAllRead = function () {
                    const formData = new FormData();
                    const headers = { 'RequestVerificationToken': getToken() };

                    fetch('/Notification/MarkAllAsRead', {
                        method: 'POST',
                        body: formData,
                        headers: headers
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                loadUnreadCount();
                                loadNotifications();
                            }
                        });
                };

                window.clearAllNotifications = function () {
                    if (!confirm('সব নোটিফিকেশন মুছে ফেলতে চান?')) return;

                    const formData = new FormData();
                    const headers = { 'RequestVerificationToken': getToken() };

                    fetch('/Notification/ClearAll', {
                        method: 'POST',
                        body: formData,
                        headers: headers
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                loadUnreadCount();
                                loadNotifications();
                            }
                        });
                };

                bell.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = wrapper.classList.contains('show');

                    if (isOpen) {
                        wrapper.classList.remove('show');
                    } else {
                        wrapper.classList.add('show');
                        loadNotifications();
                        document.querySelector('.profile-dropdown')?.classList.remove('show');
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!wrapper.contains(e.target)) {
                        wrapper.classList.remove('show');
                    }
                });

                loadUnreadCount();
                setInterval(loadUnreadCount, 30000);
            })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?= render_section('Scripts') ?>

</body>
</html>