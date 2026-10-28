<?php
/**
 * ✅ Student / Teacher ড্যাশবোর্ডের মোবাইল ড্রয়ার (অ্যাডমিন প্যানেলের মতো)
 * ≤ 992px: উপরে sticky বার (☰ + বর্তমান সেকশন), ☰ চাপলে সাইডবার বাম দিক থেকে স্লাইড করে আসে।
 *
 * ব্যবহার: partial('Shared/_DashDrawer', ['ddTarget' => 'udSidebar', 'ddKicker' => 'Student Dashboard', 'ddTitle' => 'Overview', 'ddBadge' => 0]);
 * যে সাইডবারকে ড্রয়ার বানাতে চাও তাতে id="{ddTarget}" আর class="dd-drawer" দিতে হবে।
 */
$ddTarget = (string)($ddTarget ?? 'ddSidebar');
$ddKicker = (string)($ddKicker ?? 'Dashboard');
$ddTitle = (string)($ddTitle ?? '');
$ddBadge = (int)($ddBadge ?? 0);
?>
<style>
    .dd-bar, .dd-close, .dd-overlay { display: none; }

    @media (max-width: 992px) {
        .dd-bar {
            display: flex; align-items: center; gap: 12px;
            position: sticky; top: 80px; z-index: 90;
            margin: -20px -15px 16px; padding: 10px 15px;
            background: rgba(245,251,255,0.94); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid #DCECF5; box-shadow: 0 6px 18px rgba(8,34,74,0.06);
        }
        .dd-btn {
            position: relative; width: 44px; height: 44px; flex-shrink: 0; border: none; cursor: pointer;
            border-radius: 12px; background: #134856; box-shadow: 0 6px 14px rgba(19,72,86,.25);
            transition: transform .15s ease;
        }
        .dd-btn:active { transform: scale(.94); }
        .dd-btn span {
            position: absolute; left: 12px; width: 20px; height: 2.4px; border-radius: 2px; background: #fff;
            transition: transform .4s cubic-bezier(.22,1,.36,1), opacity .25s, top .4s cubic-bezier(.22,1,.36,1);
        }
        .dd-btn span:nth-child(1) { top: 15px; }
        .dd-btn span:nth-child(2) { top: 21px; width: 14px; }
        .dd-btn span:nth-child(3) { top: 27px; }
        html.d-open .dd-btn span:nth-child(1) { top: 21px; transform: rotate(45deg); }
        html.d-open .dd-btn span:nth-child(2) { opacity: 0; transform: translateX(-8px); }
        html.d-open .dd-btn span:nth-child(3) { top: 21px; transform: rotate(-45deg); }
        .dd-dot {
            position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px; padding: 0 5px; font-style: normal;
            border-radius: 9px; background: #FFB300; color: #08224A; font-size: 10.5px; font-weight: 800;
            display: flex; align-items: center; justify-content: center; border: 2px solid #F5FBFF;
        }
        .dd-title { min-width: 0; display: flex; flex-direction: column; line-height: 1.2; }
        .dd-title small { font-size: 10.5px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #028EA5; }
        .dd-title strong { font-size: 15px; color: #08224A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* সাইডবার → বাম দিক থেকে ড্রয়ার (ভেতরের ডিজাইন ডেস্কটপের মতোই থাকে) */
        .dd-drawer {
            position: fixed !important; top: 0 !important; left: 0; bottom: 0;
            width: 290px !important; max-width: 84vw; height: auto !important; margin: 0 !important;
            z-index: 1101; overflow-y: auto; overscroll-behavior: contain;
            border-radius: 0 22px 22px 0 !important; border: none !important;
            box-shadow: 18px 0 40px rgba(8,34,74,0.35) !important;
            transform: translateX(-105%); visibility: hidden;
            transition: transform .5s cubic-bezier(.22,1,.36,1), visibility .5s;
            padding-bottom: env(safe-area-inset-bottom);
        }
        html.d-open .dd-drawer { transform: translateX(0); visibility: visible; }
        .dd-drawer .dd-close {
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 12px; right: 12px; z-index: 2;
            width: 36px; height: 36px; border-radius: 50%; border: none; cursor: pointer;
            background: #EEF5FA; color: #08224A; font-size: 15px; transition: transform .3s, background .2s;
        }
        .dd-drawer .dd-close:hover { background: #DCECF5; transform: rotate(90deg); }

        .dd-drawer ul > li { opacity: 0; transform: translateX(-22px); }
        html.d-open .dd-drawer ul > li { animation: ddIn .45s cubic-bezier(.22,1,.36,1) forwards; }
        html.d-open .dd-drawer ul > li:nth-child(1) { animation-delay: .07s; }
        html.d-open .dd-drawer ul > li:nth-child(2) { animation-delay: .11s; }
        html.d-open .dd-drawer ul > li:nth-child(3) { animation-delay: .15s; }
        html.d-open .dd-drawer ul > li:nth-child(4) { animation-delay: .19s; }
        html.d-open .dd-drawer ul > li:nth-child(5) { animation-delay: .23s; }
        html.d-open .dd-drawer ul > li:nth-child(n+6) { animation-delay: .27s; }
        @keyframes ddIn { to { opacity: 1; transform: none; } }

        .dd-overlay {
            display: block; position: fixed; inset: 0; z-index: 1100;
            background: rgba(8,34,74,0.45); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
            opacity: 0; visibility: hidden; transition: opacity .4s ease, visibility .4s ease;
        }
        html.d-open .dd-overlay { opacity: 1; visibility: visible; }
        html.d-open, html.d-open body { overflow: hidden; }
    }

    @media (prefers-reduced-motion: reduce) {
        .dd-drawer, .dd-overlay, .dd-btn span { transition: none !important; }
        .dd-drawer ul > li { opacity: 1 !important; transform: none !important; animation: none !important; }
    }
</style>

<div class="dd-bar">
    <button type="button" class="dd-btn" id="ddToggle" aria-label="Open dashboard menu" aria-controls="<?= e($ddTarget) ?>" aria-expanded="false">
        <span></span><span></span><span></span>
        <?php if ($ddBadge > 0): ?><em class="dd-dot"><?= e($ddBadge) ?></em><?php endif; ?>
    </button>
    <div class="dd-title">
        <small><?= e($ddKicker) ?></small>
        <strong><?= e($ddTitle) ?></strong>
    </div>
</div>
<div class="dd-overlay" id="ddOverlay"></div>

<script>
    (function () {
        function init() {
            var sidebar = document.getElementById(<?= js($ddTarget) ?>);
            var toggle = document.getElementById('ddToggle');
            var overlay = document.getElementById('ddOverlay');
            if (!sidebar || !toggle || !overlay) return;
            var root = document.documentElement;

            // ড্রয়ারের ভেতরে ✕ বাটন
            var x = document.createElement('button');
            x.type = 'button'; x.className = 'dd-close'; x.setAttribute('aria-label', 'Close menu');
            x.innerHTML = '<i class="fas fa-times"></i>';
            sidebar.insertBefore(x, sidebar.firstChild);

            function open() { root.classList.add('d-open'); toggle.setAttribute('aria-expanded', 'true'); }
            function close() { root.classList.remove('d-open'); toggle.setAttribute('aria-expanded', 'false'); }

            toggle.addEventListener('click', function () { root.classList.contains('d-open') ? close() : open(); });
            overlay.addEventListener('click', close);
            x.addEventListener('click', close);
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
            sidebar.querySelectorAll('a[href]').forEach(function (a) { a.addEventListener('click', function () { setTimeout(close, 120); }); });
            window.addEventListener('resize', function () { if (window.innerWidth > 992) close(); });
            window.addEventListener('pageshow', close);

            // বাম দিকে সোয়াইপ করলে বন্ধ
            var sx = null, sy = null, dx = 0;
            sidebar.addEventListener('touchstart', function (e) {
                if (window.innerWidth > 992) return;
                sx = e.touches[0].clientX; sy = e.touches[0].clientY; dx = 0; sidebar.style.transition = 'none';
            }, { passive: true });
            sidebar.addEventListener('touchmove', function (e) {
                if (sx === null) return;
                dx = e.touches[0].clientX - sx;
                if (dx < 0 && Math.abs(dx) > Math.abs(e.touches[0].clientY - sy)) sidebar.style.transform = 'translateX(' + dx + 'px)';
            }, { passive: true });
            sidebar.addEventListener('touchend', function () {
                if (sx === null) return;
                sidebar.style.transition = ''; sidebar.style.transform = '';
                if (dx < -70) close();
                sx = null;
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    })();
</script>
