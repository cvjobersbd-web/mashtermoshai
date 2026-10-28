<?php
$currentAction = (string)($GLOBALS['__route']['action'] ?? '');
$currentControllerSidebar = (string)($GLOBALS['__route']['controller'] ?? '');

// ============================================================
// ===== AUTO-LOAD PENDING COUNTS =====
// ============================================================
$pendingTeacherRequests = 0;
$pendingTuitionApps = 0;

try {
    if ($ViewBag->PendingCount !== null)
        $pendingTeacherRequests = (int)$ViewBag->PendingCount;
    else
        $pendingTeacherRequests = db()->GetPendingTeacherRequestCount();
} catch (Throwable $e) { }

try {
    if ($ViewBag->PendingTuitionApps !== null)
        $pendingTuitionApps = (int)$ViewBag->PendingTuitionApps;
    else
        $pendingTuitionApps = db()->CountTuitionApplications('Pending');
} catch (Throwable $e) { }
?>

<style>
    .admin-sidebar {
        position: fixed;
        top: 80px;
        left: 0;
        bottom: 0;
        width: 250px;
        background-color: #134856;
        color: white;
        padding: 20px 0 20px 0;
        overflow-y: auto;
        z-index: 100;
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
    }

        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.03);
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
        }

            .admin-sidebar::-webkit-scrollbar-thumb:hover {
                background: rgba(255,255,255,0.25);
            }

    .admin-sidebar-brand {
        padding: 0 20px 20px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 15px;
        flex-shrink: 0;
    }

        .admin-sidebar-brand h4 {
            color: #F5FBFF;
            font-weight: 600;
            margin: 0;
            font-size: 18px;
        }

        .admin-sidebar-brand small {
            color: #7A8CA0;
            font-size: 12px;
        }

    .admin-sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
        flex: 1;
    }

        .admin-sidebar-menu li {
            padding: 0;
        }

            .admin-sidebar-menu li a {
                display: flex;
                align-items: center;
                padding: 12px 20px;
                color: #CFE3EC;
                text-decoration: none;
                transition: all 0.3s ease;
                border-left: 3px solid transparent;
                font-size: 14px;
                cursor: pointer;
            }

                .admin-sidebar-menu li a:hover, .admin-sidebar-menu li a.active {
                    background-color: rgba(255,255,255,0.08);
                    color: #ffffff;
                    border-left-color: #FFB300;
                }

                .admin-sidebar-menu li a i {
                    width: 24px;
                    margin-right: 10px;
                    font-size: 16px;
                    text-align: center;
                    flex-shrink: 0;
                }

                .admin-sidebar-menu li a .badge-sidebar {
                    margin-left: auto;
                    background-color: rgba(255,255,255,0.15);
                    padding: 2px 10px;
                    border-radius: 12px;
                    font-size: 11px;
                    color: #F5FBFF;
                    font-weight: 600;
                }

                    .admin-sidebar-menu li a .badge-sidebar.warning {
                        background-color: #FFB300;
                        color: #08224A;
                    }

    /* =========================================================
       ✅ ADMIN — MOBILE / TABLET (≤ 992px)
       সাইডবার মেনু বাম দিক থেকে স্লাইড করে আসে (হ্যামবার্গার)
       ========================================================= */
    .admin-mbar, .admin-sidebar-close { display: none; }
    .admin-sidebar-overlay { display: none; }

    @media (max-width: 992px) {
        /* উপরের sticky বার: ☰ + কোন পেজে আছি */
        .admin-mbar {
            display: flex; align-items: center; gap: 12px;
            position: sticky; top: 80px; z-index: 90;
            padding: 10px 14px;
            background: rgba(245,251,255,0.94); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid #DCECF5; box-shadow: 0 6px 18px rgba(8,34,74,0.06);
        }
        .admin-mbar-btn {
            position: relative; width: 44px; height: 44px; flex-shrink: 0; border: none; cursor: pointer;
            border-radius: 12px; background: #134856; box-shadow: 0 6px 14px rgba(19,72,86,.25);
            transition: transform .15s ease, background .2s;
        }
        .admin-mbar-btn:active { transform: scale(.94); }
        .admin-mbar-btn span {
            position: absolute; left: 12px; width: 20px; height: 2.4px; border-radius: 2px; background: #fff;
            transition: transform .4s cubic-bezier(.22,1,.36,1), opacity .25s, top .4s cubic-bezier(.22,1,.36,1), width .3s;
        }
        .admin-mbar-btn span:nth-child(1) { top: 15px; }
        .admin-mbar-btn span:nth-child(2) { top: 21px; width: 14px; }
        .admin-mbar-btn span:nth-child(3) { top: 27px; }
        html.a-open .admin-mbar-btn span:nth-child(1) { top: 21px; transform: rotate(45deg); }
        html.a-open .admin-mbar-btn span:nth-child(2) { opacity: 0; transform: translateX(-8px); }
        html.a-open .admin-mbar-btn span:nth-child(3) { top: 21px; transform: rotate(-45deg); }
        .admin-mbar-dot {
            position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px; padding: 0 5px;
            border-radius: 9px; background: #FFB300; color: #08224A; font-size: 10.5px; font-weight: 800;
            display: flex; align-items: center; justify-content: center; border: 2px solid #F5FBFF;
        }
        .admin-mbar-title { min-width: 0; display: flex; flex-direction: column; line-height: 1.2; }
        .admin-mbar-title small { font-size: 10.5px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #028EA5; }
        .admin-mbar-title strong { font-size: 15px; color: #08224A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* সাইডবার → বাম দিক থেকে ড্রয়ার */
        .admin-sidebar {
            top: 0 !important; bottom: 0; z-index: 1101;
            width: 280px; max-width: 82vw;
            padding-top: 18px !important;
            transform: translateX(-105%); visibility: hidden;
            transition: transform .5s cubic-bezier(.22,1,.36,1), visibility .5s;
            border-top-right-radius: 22px; border-bottom-right-radius: 22px;
            box-shadow: 18px 0 40px rgba(8,34,74,0.35);
            overscroll-behavior: contain;
        }
        html.a-open .admin-sidebar { transform: translateX(0); visibility: visible; }

        .admin-sidebar-overlay {
            display: block; position: fixed; inset: 0; z-index: 1100;
            background: rgba(8,34,74,0.45); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
            opacity: 0; visibility: hidden; transition: opacity .4s ease, visibility .4s ease;
        }
        html.a-open .admin-sidebar-overlay { opacity: 1; visibility: visible; }
        html.a-open, html.a-open body { overflow: hidden; }

        .admin-sidebar-brand { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .admin-sidebar-close {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 36px; height: 36px; border-radius: 50%; border: none; cursor: pointer;
            background: rgba(255,255,255,0.1); color: #fff; font-size: 15px; transition: transform .3s, background .2s;
        }
        .admin-sidebar-close:hover { background: rgba(255,255,255,0.2); transform: rotate(90deg); }

        .admin-sidebar-menu li a { padding: 13px 22px; }
        .admin-sidebar-menu li { opacity: 0; transform: translateX(-22px); }
        html.a-open .admin-sidebar-menu li { animation: aSlideIn .45s cubic-bezier(.22,1,.36,1) forwards; }
        html.a-open .admin-sidebar-menu li:nth-child(1) { animation-delay: .06s; }
        html.a-open .admin-sidebar-menu li:nth-child(2) { animation-delay: .09s; }
        html.a-open .admin-sidebar-menu li:nth-child(3) { animation-delay: .12s; }
        html.a-open .admin-sidebar-menu li:nth-child(4) { animation-delay: .15s; }
        html.a-open .admin-sidebar-menu li:nth-child(5) { animation-delay: .18s; }
        html.a-open .admin-sidebar-menu li:nth-child(6) { animation-delay: .21s; }
        html.a-open .admin-sidebar-menu li:nth-child(7) { animation-delay: .24s; }
        html.a-open .admin-sidebar-menu li:nth-child(8) { animation-delay: .27s; }
        html.a-open .admin-sidebar-menu li:nth-child(n+9) { animation-delay: .30s; }
        @keyframes aSlideIn { to { opacity: 1; transform: none; } }

        .admin-main-content { margin-left: 0 !important; }
        .admin-main-content .empty-placeholder { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-sidebar, .admin-sidebar-overlay, .admin-mbar-btn span { transition: none !important; }
        .admin-sidebar-menu li { opacity: 1 !important; transform: none !important; animation: none !important; }
    }

    @media (max-width: 576px) {
        .admin-main-content { padding: 14px 12px 40px !important; }

        /* স্ট্যাট কার্ড ২ কলামে */
        .admin-main-content .stats-grid,
        .admin-main-content .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 10px !important; }
        .admin-main-content .stat-box,
        .admin-main-content .stat-card { padding: 14px !important; }
        .admin-main-content .stat-box h3, .admin-main-content .stat-box .num,
        .admin-main-content .stat-card h3, .admin-main-content .stat-card .num { font-size: 22px !important; }
        .admin-dash-stats > [class*="col-"] { flex: 0 0 50%; max-width: 50%; padding-left: 5px; padding-right: 5px; }
        .admin-dash-stats { margin-left: -5px; margin-right: -5px; }
        .admin-dash-stats .stats-card .card-body { padding: 14px 12px; }
        .admin-dash-stats .stats-card h6 { font-size: 12px; margin-bottom: 4px; }
        .admin-dash-stats .stats-card h2 { font-size: 26px; margin: 0; }
        .admin-dash-stats .stats-card .card-body > div > div:last-child { display: none; }

        /* হেডার/বাটন সারি ভাঙবে, চাপাচাপি হবে না */
        .admin-main-content h2 { font-size: 21px; }
        .admin-main-content .page-header { flex-wrap: wrap; gap: 10px; }
        .admin-main-content .card-body { padding: 12px; }
        .admin-main-content .btn-group { flex-wrap: wrap; }

        /* ===== টেবিল → কার্ড (ছোট স্ক্রিনে) ===== */
        .admin-cards-table thead { display: none; }
        .admin-cards-table, .admin-cards-table tbody, .admin-cards-table tr, .admin-cards-table td { display: block; width: 100%; }
        .admin-cards-table tr {
            background: #fff !important; border: 1px solid #DCECF5; border-radius: 14px; padding: 10px 12px; margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(8,34,74,0.04);
        }
        .admin-cards-table td {
            display: flex; justify-content: space-between; align-items: center; gap: 12px;
            padding: 7px 0 !important; border: none !important; border-bottom: 1px dashed #EEF5FA !important;
            text-align: right; font-size: 13.5px; background: transparent !important; box-shadow: none !important; word-break: break-word;
        }
        .admin-cards-table td:last-child { border-bottom: none !important; }
        .admin-cards-table td::before {
            content: attr(data-label); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
            color: #7A8CA0; text-align: left; flex-shrink: 0;
        }
        .admin-cards-table td.td-actions { justify-content: flex-end; flex-wrap: wrap; gap: 6px; padding-top: 10px !important; }
        .admin-cards-table td.td-actions::before { display: none; }
        .admin-cards-table td.td-actions .btn { min-width: 40px; min-height: 36px; }
    }
</style>


<!-- ✅ মোবাইল/ট্যাবলেটে: ☰ বাটন চাপলে সাইডবার বাম দিক থেকে আসে -->
<?php
    $adminPages = [
        'Dashboard' => 'Dashboard', 'TeacherRequests' => 'Teacher Requests', 'TeacherOverview' => 'Teacher Overview',
        'UserOverview' => 'User Overview', 'TuitionPosts' => 'Tuition Posts', 'AddTuitionPost' => 'Add Tuition Post',
        'EditTuitionPost' => 'Edit Tuition Post', 'TuitionApplications' => 'Tuition Applications', 'TutorRequests' => 'Tutor Requests',
        'ManageNotices' => 'News & Announcements', 'AddNotice' => 'Add Notice', 'EditNotice' => 'Edit Notice', 'Settings' => 'Settings',
    ];
    $adminPageTitle = $adminPages[$currentAction] ?? 'Admin';
    $adminPendingTotal = $pendingTeacherRequests + $pendingTuitionApps;
?>
<div class="admin-mbar">
    <button class="admin-mbar-btn" id="adminSidebarToggle" type="button" aria-label="Open admin menu" aria-controls="adminSidebar" aria-expanded="false">
        <span></span><span></span><span></span>
        <?php if ($adminPendingTotal > 0): ?><em class="admin-mbar-dot"><?= e($adminPendingTotal) ?></em><?php endif; ?>
    </button>
    <div class="admin-mbar-title">
        <small><i class="fas fa-shield-alt"></i> Admin Panel</small>
        <strong><?= e($adminPageTitle) ?></strong>
    </div>
</div>
<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<div class="admin-sidebar" id="adminSidebar" aria-label="Admin menu">
    <div class="admin-sidebar-brand">
        <div>
            <h4><i class="fas fa-shield-alt me-2"></i>Admin Panel</h4>
            <small>Smart Tutor Hub</small>
        </div>
        <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close admin menu"><i class="fas fa-times"></i></button>
    </div>

    <ul class="admin-sidebar-menu">
        <li>
            <a href="/Admin/Dashboard" class="<?= $currentAction === 'Dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="/Admin/TeacherRequests" class="<?= $currentAction === 'TeacherRequests' ? 'active' : '' ?>">
                <i class="fas fa-user-graduate"></i> Teacher Requests
                <?php if ($pendingTeacherRequests > 0): ?>
                    <span class="badge-sidebar warning"><?= e($pendingTeacherRequests) ?></span>
                <?php endif; ?>
            </a>
        </li>

        <!-- ✅ Teacher Overview -->
        <li>
            <a href="/Admin/TeacherOverview" class="<?= $currentAction === 'TeacherOverview' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Teacher Overview
            </a>
        </li>

        <!-- ✅ নতুন যুক্ত করা হলো: User Overview -->
        <li>
            <a href="/Admin/UserOverview" class="<?= $currentAction === 'UserOverview' ? 'active' : '' ?>">
                <i class="fas fa-users-viewfinder"></i> User Overview
            </a>
        </li>

        <li>
            <a href="/Admin/TuitionPosts" class="<?= $currentAction === 'TuitionPosts' || $currentAction === 'AddTuitionPost' || $currentAction === 'EditTuitionPost' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i> Tuition Posts
            </a>
        </li>
        <li>
            <a href="/Admin/TuitionApplications" class="<?= $currentAction === 'TuitionApplications' ? 'active' : '' ?>">
                <i class="fas fa-user-check"></i> Tuition Applications
                <?php if ($pendingTuitionApps > 0): ?>
                    <span class="badge-sidebar warning"><?= e($pendingTuitionApps) ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="/Admin/TutorRequests" class="<?= $currentAction === 'TutorRequests' ? 'active' : '' ?>">
                <i class="fas fa-envelope-open-text"></i> Tutor Requests
            </a>
        </li>
        <li>
            <a href="/Helpdesk" class="<?= $currentControllerSidebar === 'Helpdesk' ? 'active' : '' ?>">
                <i class="fas fa-headset"></i> Helpdesk
            </a>
        </li>
        <li>
            <a href="/Admin/ManageNotices" class="<?= $currentAction === 'ManageNotices' || $currentAction === 'AddNotice' || $currentAction === 'EditNotice' ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i> News &amp; Announcements
            </a>
        </li>
        <li>
            <a href="/Admin/Settings" class="<?= $currentAction === 'Settings' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
        <li style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 10px; padding-top: 10px;">
            <a href="#" onclick="document.documentElement.classList.remove('a-open'); adminChangePasswordFromSidebar(); return false;" style="color: #FFB300;">
                <i class="fas fa-key"></i> Change Password
            </a>
        </li>
        <li>
            <form method="post" action="/Account/Logout" id="adminSidebarLogoutForm">
                <?= csrf_field() ?>
                <a href="#" onclick="document.getElementById('adminSidebarLogoutForm').submit(); return false;" style="color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </form>
        </li>
    </ul>
</div>

<script>
    // ✅ অ্যাডমিন সাইডবার — মোবাইলে বাম দিক থেকে স্লাইড
    (function () {
        var toggle = document.getElementById('adminSidebarToggle');
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.getElementById('adminSidebarOverlay');
        var closeBtn = document.getElementById('adminSidebarClose');
        if (!toggle || !sidebar || !overlay) return;
        var root = document.documentElement;

        function open() { root.classList.add('a-open'); toggle.setAttribute('aria-expanded', 'true'); }
        function close() { root.classList.remove('a-open'); toggle.setAttribute('aria-expanded', 'false'); }

        toggle.addEventListener('click', function () { root.classList.contains('a-open') ? close() : open(); });
        overlay.addEventListener('click', close);
        closeBtn && closeBtn.addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
        sidebar.querySelectorAll('a[href]:not([href="#"])').forEach(function (a) { a.addEventListener('click', function () { setTimeout(close, 120); }); });
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
    })();

    // ✅ মোবাইলে Overview পেজে সিলেক্ট করা ইউজারের বিস্তারিত অংশে স্ক্রল
    document.addEventListener('DOMContentLoaded', function () {
        if (window.innerWidth > 992) return;
        if (/[?&]id=\d+/.test(location.search)) {
            var detail = document.querySelector('.admin-main-content .col-lg-9');
            if (detail) setTimeout(function () {
                window.scrollTo({ top: detail.getBoundingClientRect().top + window.scrollY - 150, behavior: 'smooth' });
            }, 150);
        }
    });

    function adminChangePasswordFromSidebar() {
        if (typeof changeAdminPassword === 'function') {
            changeAdminPassword();
            return;
        }
        window.location.href = '/Admin/Dashboard';
    }
</script>