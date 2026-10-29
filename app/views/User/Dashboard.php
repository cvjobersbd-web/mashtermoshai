<?php
$ViewData['Title'] = 'Student Dashboard';

$user = $ViewBag->User;
$stats = $ViewBag->Stats ?? obj(['TotalPosts' => 0, 'TotalLikes' => 0, 'TotalComments' => 0, 'TotalTeachersConnected' => 0]);
$recentPosts = is_array($ViewBag->RecentPosts) ? $ViewBag->RecentPosts : [];
$applications = is_array($ViewBag->Applications) ? $ViewBag->Applications : [];
$pendingApps = count(array_filter($applications, fn($a) => $a->Status === 'Pending'));
$activeSection = in_array(input_str('section'), ['applications', 'review'], true) ? input_str('section') : 'overview';
$waLink = function (?string $phone): string {
    $d = preg_replace('/\D/', '', (string)$phone);
    if (str_starts_with($d, '01')) $d = '88' . $d;
    return 'https://wa.me/' . $d;
};
?>

<style>
    /* ======================
           LAYOUT & WRAPPER
           ====================== */
    .ud-wrapper {
        display: flex;
        gap: 25px;
        max-width: 1300px;
        margin: 0 auto;
        padding: 30px 20px 70px;
        min-height: calc(100vh - 80px);
        background: #F5FBFF;
    }

    /* ======================
           RIGHT SIDEBAR (MENU) -> এখন বাম পাশে থাকবে
           ====================== */
    .ud-sidebar {
        width: 280px;
        flex-shrink: 0;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
        height: fit-content;
        position: sticky;
        top: 100px;
        overflow: hidden;
        order: -1; /* ✅ মেনুটি বাম পাশে দেখাবে */
    }

    .ud-profile-header {
        padding: 30px 20px 20px;
        text-align: center;
        border-bottom: 1px solid #F5FBFF;
        background: linear-gradient(180deg, #F5FBFF 0%, #FFFFFF 100%);
    }

    .ud-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #02B8F5;
        margin: 0 auto 12px;
        display: block;
        box-shadow: 0 4px 12px rgba(2,184,245,0.15);
        background: #DCECF5;
    }

    .ud-name {
        font-family: var(--font-heading);
        font-size: 20px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 4px;
    }

    .ud-role {
        font-size: 12px;
        color: #1565c0;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e3f2fd;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .ud-menu {
        list-style: none;
        padding: 10px 0;
        margin: 0;
    }

        .ud-menu li a {
            display: flex;
            align-items: center;
            padding: 14px 24px;
            color: #475569;
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 600;
            transition: all 0.2s;
            border-right: 4px solid transparent;
        }

            .ud-menu li a:hover,
            .ud-menu li a.active {
                background: #F5FBFF;
                color: #08224A;
                border-right-color: #02B8F5;
            }

            .ud-menu li a i {
                width: 24px;
                font-size: 16px;
                margin-right: 12px;
                color: #7A8CA0;
                text-align: center;
            }

            .ud-menu li a.active i {
                color: #028EA5;
            }

    /* ======================
           LEFT MAIN CONTENT -> এখন ডান পাশে থাকবে
           ====================== */
    .ud-main {
        flex: 1;
        min-width: 0;
        order: 1; /* ✅ কন্টেন্ট ডান পাশে দেখাবে */
    }

    /* ======================
           STATS CARDS
           ====================== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        border-bottom: 4px solid #ccc;
    }

        .stat-card.posts {
            border-bottom-color: #3498db;
        }

        .stat-card.likes {
            border-bottom-color: #e74c3c;
        }

        .stat-card.comments {
            border-bottom-color: #f39c12;
        }

        .stat-card.teachers {
            border-bottom-color: #2ecc71;
        }

    .stat-icon {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .stat-card.posts .stat-icon {
        color: #3498db;
    }

    .stat-card.likes .stat-icon {
        color: #e74c3c;
    }

    .stat-card.comments .stat-icon {
        color: #f39c12;
    }

    .stat-card.teachers .stat-icon {
        color: #2ecc71;
    }

    .stat-num {
        font-size: 28px;
        font-weight: 800;
        color: #08224A;
        line-height: 1.1;
        font-family: var(--font-heading);
    }

    .stat-lbl {
        font-size: 12.5px;
        color: #64748B;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 4px;
    }

    /* ======================
           RECENT POSTS LIST
           ====================== */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

        .section-header h3 {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 700;
            color: #08224A;
            margin: 0;
        }

    .btn-new-post {
        background: #02B8F5;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

        .btn-new-post:hover {
            background: #0099D6;
            color: white;
            transform: translateY(-1px);
        }

    .post-feed {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .user-post-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .post-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 12.5px;
        color: #7A8CA0;
    }

    .post-content {
        font-size: 14.5px;
        color: #08224A;
        line-height: 1.6;
        margin-bottom: 16px;
        white-space: pre-wrap;
    }

    .post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .post-tag {
        background: #F5FBFF;
        color: #08224A;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .post-stats {
        display: flex;
        gap: 16px;
        padding-top: 14px;
        border-top: 1px dashed #DCECF5;
        font-size: 13.5px;
        font-weight: 600;
        color: #64748B;
    }

        .post-stats i {
            color: #028EA5;
            margin-right: 4px;
        }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        border: 2px dashed #DCECF5;
    }

        .empty-state > i {
            font-size: 48px;
            color: #B9D6E6;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #08224A;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #7A8CA0;
            font-size: 14px;
            margin: 0;
        }

    /* ======================
           RESPONSIVE
           ====================== */
    @media (max-width: 992px) {
        .ud-wrapper {
            flex-direction: column;
            padding: 20px 15px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .stat-card { padding: 14px; }
        .stat-num { font-size: 24px; }

        .section-header { flex-wrap: wrap; gap: 10px; }
        .section-header h3 { font-size: 18px; }

        .ta-card { padding: 14px; }
        .ta-status { margin-left: 0; }
        .ta-actions .ta-btn { flex: 1 1 auto; justify-content: center; }
        .ta-actions > span[style*="flex:1"] { display: none; }
        .ta-decide { display: flex; gap: 8px; width: 100%; }
        .ta-decide .ta-btn { flex: 1; justify-content: center; }
    }

    /* ======================
           TEACHER APPLICATIONS
           ====================== */
    .ud-section { display: none; }
    .ud-section.active { display: block; animation: udFade .25s ease; }
    @keyframes udFade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

    .ud-menu .ud-count {
        margin-left: auto;
        background: #FFB300;
        color: #08224A;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 9px;
        border-radius: 12px;
    }

    .app-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .app-filter {
        border: 1px solid #DCECF5; background: white; color: #334155;
        padding: 7px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .app-filter.active { background: #02B8F5; border-color: #02B8F5; color: white; }

    .ta-card {
        background: white; border-radius: 16px; padding: 20px; margin-bottom: 14px;
        border: 1px solid #DCECF5; border-left: 5px solid #FFB300;
        box-shadow: 0 2px 12px rgba(8,34,74,0.04);
    }
    .ta-card.status-Accepted { border-left-color: #2ecc71; }
    .ta-card.status-Rejected { border-left-color: #e74c3c; opacity: .8; }

    .ta-head { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
    .ta-avatar {
        width: 54px; height: 54px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
        background: linear-gradient(135deg, #08224A, #134856); color: white;
        display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px;
    }
    .ta-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ta-name { font-size: 17px; font-weight: 700; color: #08224A; margin: 0; }
    .ta-sub { font-size: 12.5px; color: #64748B; }
    .ta-status {
        margin-left: auto; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: .4px;
    }
    .ta-status.Pending { background: #FFF7E0; color: #8a5a00; }
    .ta-status.Accepted { background: #d4edda; color: #155724; }
    .ta-status.Rejected { background: #f8d7da; color: #721c24; }

    .ta-info { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 14px 0 0; font-size: 13.5px; color: #334155; }
    .ta-info i { color: #028EA5; margin-right: 4px; }
    .ta-msg {
        background: #F5FBFF; border: 1px dashed #DCECF5; border-radius: 10px; padding: 12px 14px;
        margin-top: 12px; font-size: 13.5px; color: #334155; white-space: pre-wrap;
    }
    .ta-post { margin-top: 10px; font-size: 12.5px; color: #64748B; }
    .ta-post i { color: #028EA5; }
    .ta-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; padding-top: 14px; border-top: 1px solid #EEF5FA; }
    .ta-btn {
        border: none; border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px; text-decoration: none; cursor: pointer; color: white;
    }
    .ta-btn:hover { opacity: .9; color: white; }
    .ta-btn.wa { background: #25D366; }
    .ta-btn.call { background: #028EA5; }
    .ta-btn.mail { background: #64748B; }
    .ta-btn.accept { background: #2ecc71; }
    .ta-btn.reject { background: white; color: #e74c3c; border: 1px solid #e74c3c; }
    .ta-btn.reject:hover { color: #e74c3c; background: #fdecea; }
    .ta-btn.undo { background: white; color: #64748B; border: 1px solid #DCECF5; }
    .ta-btn.undo:hover { color: #08224A; }
    .ta-btn:disabled { opacity: .6; cursor: default; }
</style>

<div class="ud-wrapper">

    <!-- ====================== LEFT SIDEBAR (MENU) ====================== -->
    <div class="ud-sidebar dd-drawer" id="udSidebar">
        <div class="ud-profile-header">
            <img src="<?= e(is_blank($user?->ProfileImageLink) ? 'https://ui-avatars.com/api/?name=' . rawurlencode($user?->FullName ?? 'User') . '&background=1F3B2C&color=fff&size=120' : $user?->ProfileImageLink) ?>"
                 alt="Profile" class="ud-avatar" />
            <div>
                <h3 class="ud-name"><?= e($user?->FullName) ?></h3>
                <span class="ud-role"><i class="fas fa-user-graduate"></i> Student</span>
            </div>
        </div>

        <ul class="ud-menu">
            <li>
                <a href="/User/Dashboard" class="<?= $activeSection === 'overview' ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Overview
                </a>
            </li>
            <li>
                <a href="/User/Dashboard?section=applications" class="<?= $activeSection === 'applications' ? 'active' : '' ?>">
                    <i class="fas fa-chalkboard-teacher"></i> Teacher Applications
                    <?php if ($pendingApps > 0): ?><span class="ud-count"><?= e($pendingApps) ?></span><?php endif; ?>
                </a>
            </li>
            <li>
                <a href="/User/Dashboard?section=review" class="<?= $activeSection === 'review' ? 'active' : '' ?>">
                    <i class="fas fa-star"></i> My Review
                </a>
            </li>
            <li>
                <a href="/Profile/Show">
                    <i class="fas fa-id-card"></i> My Profile
                </a>
            </li>
            <li>
                <a href="/Friends">
                    <i class="fas fa-user-group"></i> Friends
                </a>
            </li>
            <li>
                <a href="/Tuition/Circular">
                    <i class="fas fa-clipboard-list"></i> Circular Feed
                </a>
            </li>
            <li>
                <a href="/Helpdesk">
                    <i class="fas fa-headset"></i> Helpdesk
                </a>
            </li>
            <li>
                <a href="/Notification/All">
                    <i class="fas fa-bell"></i> Notifications
                </a>
            </li>
        </ul>
    </div>

    <!-- ====================== RIGHT MAIN CONTENT ====================== -->
    <div class="ud-main">
        <?php partial('Shared/_DashDrawer', [
            'ddTarget' => 'udSidebar',
            'ddKicker' => 'Student Dashboard',
            'ddTitle'  => ['applications' => 'Teacher Applications', 'review' => 'My Review'][$activeSection] ?? 'Overview',
            'ddBadge'  => $pendingApps,
        ]); ?>
        <?= csrf_field() ?>

        <!-- ===================== OVERVIEW ===================== -->
        <div class="ud-section <?= $activeSection === 'overview' ? 'active' : '' ?>" id="sec-overview">
        <?php if ($pendingApps > 0): ?>
            <a href="/User/Dashboard?section=applications" style="display:flex; align-items:center; gap:12px; background:#FFF7E0; border:1px solid #FFB300; border-radius:14px; padding:14px 18px; margin-bottom:20px; text-decoration:none; color:#08224A; font-weight:600;">
                <i class="fas fa-bell" style="color:#E09A00; font-size:18px;"></i>
                <span><?= e($pendingApps) ?> জন টিচার আপনার পোস্টে Apply করেছেন — দেখতে ক্লিক করুন</span>
                <i class="fas fa-arrow-right" style="margin-left:auto;"></i>
            </a>
        <?php endif; ?>
        <div class="section-header">
            <h3>Welcome back, <?= e($user?->FullName ?? 'Student') ?>!</h3>
            <a href="/Tuition/Circular" class="btn-new-post">
                <i class="fas fa-plus"></i> New Post
            </a>
        </div>

        <!-- STATS GRID -->
        <div class="stats-grid">
            <div class="stat-card posts">
                <i class="fas fa-file-alt stat-icon"></i>
                <div class="stat-num"><?= e($stats->TotalPosts) ?></div>
                <div class="stat-lbl">Total Posts</div>
            </div>
            <div class="stat-card likes">
                <i class="fas fa-heart stat-icon"></i>
                <div class="stat-num"><?= e($stats->TotalLikes) ?></div>
                <div class="stat-lbl">Total Likes</div>
            </div>
            <div class="stat-card comments">
                <i class="fas fa-comment-dots stat-icon"></i>
                <div class="stat-num"><?= e($stats->TotalComments) ?></div>
                <div class="stat-lbl">Total Comments</div>
            </div>
            <div class="stat-card teachers">
                <i class="fas fa-chalkboard-teacher stat-icon"></i>
                <div class="stat-num"><?= e($stats->TotalApplications ?? count($applications)) ?></div>
                <div class="stat-lbl">Teacher Applications</div>
            </div>
        </div>

        <!-- RECENT POSTS -->
        <div class="section-header" style="margin-top: 30px;">
            <h3>Your Recent Posts</h3>
        </div>

        <div class="post-feed">
            <?php if (empty($recentPosts)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h5>No Posts Yet</h5>
                    <p>আপনি এখনো কোনো টিউশনের পোস্ট করেননি।</p>
                    <a href="/Tuition/Circular" class="btn-new-post" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> Create Your First Post
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($recentPosts as $post): ?>
                    <div class="user-post-card">
                        <div class="post-meta">
                            <span><i class="far fa-clock"></i> <?= e(fmt_date($post->CreatedAt, 'd M Y, h:i A')) ?></span>
                            <a href="/Tuition/Circular" style="color: #08224A; font-weight: 600; text-decoration: none;">View on Feed <i class="fas fa-arrow-right"></i></a>
                        </div>

                        <div class="post-content"><?= e($post->Content) ?></div>

                        <?php if (!is_blank($post->PreferredArea) || !is_blank($post->ClassLevel) || !is_blank($post->Subjects)): ?>
                            <div class="post-tags">
                                <?php if (!is_blank($post->PreferredArea)): ?>
                                    <span class="post-tag"><i class="fas fa-map-marker-alt"></i> <?= e($post->PreferredArea) ?></span>
                                <?php endif; ?>
                                <?php if (!is_blank($post->ClassLevel)): ?>
                                    <span class="post-tag"><i class="fas fa-graduation-cap"></i> <?= e($post->ClassLevel) ?></span>
                                <?php endif; ?>
                                <?php if (!is_blank($post->Subjects)): ?>
                                    <span class="post-tag"><i class="fas fa-book"></i> <?= e($post->Subjects) ?></span>
                                <?php endif; ?>
                                <?php if (!is_blank($post->Budget)): ?>
                                    <span class="post-tag" style="background:#d4edda; color:#155724;"><i class="fas fa-money-bill-wave"></i> ৳<?= e($post->Budget) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-stats">
                            <span><i class="fas fa-thumbs-up"></i> <?= e($post->LikeCount) ?> Likes</span>
                            <span><i class="fas fa-comment"></i> <?= array_sum(array_map(fn($c) => 1 + count($c->Replies ?? []), $post->Comments ?? [])) ?> Comments</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        </div>

        <!-- ===================== TEACHER APPLICATIONS ===================== -->
        <div class="ud-section <?= $activeSection === 'applications' ? 'active' : '' ?>" id="sec-applications">
            <div class="section-header">
                <h3>Teacher Applications (<?= count($applications) ?>)</h3>
            </div>
            <p style="color:#64748B; font-size:14px; margin:-6px 0 16px;">
                আপনার টিউশন পোস্টে যেসব টিচার Apply করেছেন তারা এখানে দেখাবে। পছন্দ হলে সরাসরি যোগাযোগ করুন এবং Accept করুন।
            </p>

            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h5>এখনো কেউ Apply করেননি</h5>
                    <p>টিচাররা আপনার পোস্টে Apply করলে এখানে দেখতে পাবেন।</p>
                    <a href="/Tuition/Circular" class="btn-new-post" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> নতুন পোস্ট করুন
                    </a>
                </div>
            <?php else: ?>
                <div class="app-filters">
                    <button class="app-filter active" data-f="all">All (<?= count($applications) ?>)</button>
                    <button class="app-filter" data-f="Pending">Pending (<?= $pendingApps ?>)</button>
                    <button class="app-filter" data-f="Accepted">Accepted (<?= count(array_filter($applications, fn($a) => $a->Status === 'Accepted')) ?>)</button>
                    <button class="app-filter" data-f="Rejected">Rejected (<?= count(array_filter($applications, fn($a) => $a->Status === 'Rejected')) ?>)</button>
                </div>

                <?php foreach ($applications as $a): ?>
                    <div class="ta-card status-<?= e($a->Status) ?>" id="ta-<?= e($a->Id) ?>" data-status="<?= e($a->Status) ?>">
                        <div class="ta-head">
                            <div class="ta-avatar">
                                <?php if (!is_blank($a->TeacherImage)): ?>
                                    <img src="<?= e($a->TeacherImage) ?>" alt="<?= e($a->TeacherName) ?>" />
                                <?php else: ?>
                                    <?= e($a->Initials) ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="ta-name"><?= e($a->TeacherName) ?> <span class="badge" style="background:#CFF2FC; color:#028EA5; font-size:10px; vertical-align:middle;">TEACHER</span></p>
                                <div class="ta-sub"><i class="far fa-clock"></i> <?= e($a->TimeAgo) ?> Apply করেছেন</div>
                            </div>
                            <span class="ta-status <?= e($a->Status) ?>" id="ta-status-<?= e($a->Id) ?>"><?= e($a->Status) ?></span>
                        </div>

                        <div class="ta-info">
                            <?php if (!is_blank($a->Education)): ?><span><i class="fas fa-graduation-cap"></i><?= e($a->Education) ?><?= !is_blank($a->Institution) ? ', ' . e($a->Institution) : '' ?></span><?php endif; ?>
                            <?php if (!is_blank($a->SubjectExpertise)): ?><span><i class="fas fa-book"></i><?= e($a->SubjectExpertise) ?></span><?php endif; ?>
                            <?php if (!is_blank($a->Experience)): ?><span><i class="fas fa-briefcase"></i><?= e($a->Experience) ?></span><?php endif; ?>
                            <?php if (!is_blank($a->Phone)): ?><span><i class="fas fa-phone"></i><?= e($a->Phone) ?></span><?php endif; ?>
                        </div>

                        <?php if (!is_blank($a->Message)): ?>
                            <div class="ta-msg"><?= e($a->Message) ?></div>
                        <?php endif; ?>

                        <div class="ta-post"><i class="fas fa-clipboard-list"></i> পোস্ট: “<?= e($a->PostPreview) ?>”</div>

                        <div class="ta-actions">
                            <?php if (!is_blank($a->Phone)): ?>
                                <a class="ta-btn wa" href="<?= e($waLink($a->Phone)) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                                <a class="ta-btn call" href="tel:<?= e($a->Phone) ?>"><i class="fas fa-phone"></i> Call</a>
                            <?php endif; ?>
                            <?php if (!is_blank($a->TeacherEmail)): ?>
                                <a class="ta-btn mail" href="mailto:<?= e($a->TeacherEmail) ?>"><i class="fas fa-envelope"></i> Email</a>
                            <?php endif; ?>
                            <span style="flex:1"></span>
                            <span class="ta-decide" id="ta-decide-<?= e($a->Id) ?>">
                                <?php if ($a->Status === 'Pending'): ?>
                                    <button class="ta-btn accept" onclick="setAppStatus(<?= e($a->Id) ?>, 'Accepted')"><i class="fas fa-check"></i> Accept</button>
                                    <button class="ta-btn reject" onclick="setAppStatus(<?= e($a->Id) ?>, 'Rejected')"><i class="fas fa-times"></i> Reject</button>
                                <?php else: ?>
                                    <button class="ta-btn undo" onclick="setAppStatus(<?= e($a->Id) ?>, 'Pending')"><i class="fas fa-undo"></i> Undo</button>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ===================== MY REVIEW ===================== -->
        <div class="ud-section <?= $activeSection === 'review' ? 'active' : '' ?>" id="sec-review">
            <div class="section-header">
                <h3>My Review</h3>
            </div>
            <p style="color:#64748B; font-size:14px; margin:-6px 0 16px;">
                মাষ্টারমশাই নিয়ে আপনার অভিজ্ঞতা লিখুন। অ্যাডমিন Approve করলে আপনার ছবি ও নামসহ হোম পেজে দেখাবে।
            </p>
            <?php partial('Shared/_ReviewForm', ['rvReview' => $ViewBag->MyReview, 'rvSubtitleHint' => 'যেমন: Class 9 Student / অভিভাবক']); ?>
        </div>
    </div>

</div>

<?php section_start('Scripts'); ?>
<script>
    (function () {
        document.querySelectorAll('.app-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.app-filter').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                var f = btn.getAttribute('data-f');
                document.querySelectorAll('.ta-card').forEach(function (c) {
                    c.style.display = (f === 'all' || c.getAttribute('data-status') === f) ? '' : 'none';
                });
            });
        });
    })();

    async function setAppStatus(id, status) {
        if (status === 'Rejected' && !confirm('এই টিচারের অ্যাপ্লিকেশন Reject করবেন?')) return;
        var box = document.getElementById('ta-decide-' + id);
        box.querySelectorAll('button').forEach(function (b) { b.disabled = true; });

        var fd = new FormData();
        fd.append('id', id);
        fd.append('status', status);
        fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        try {
            var res = await fetch('/User/UpdateCircularApplication', { method: 'POST', body: fd });
            var data = await res.json();
            if (!data.success) { alert(data.message || 'Failed'); box.querySelectorAll('button').forEach(function (b) { b.disabled = false; }); return; }
            var card = document.getElementById('ta-' + id);
            card.className = 'ta-card status-' + status;
            card.setAttribute('data-status', status);
            var st = document.getElementById('ta-status-' + id);
            st.className = 'ta-status ' + status;
            st.textContent = status;
            box.innerHTML = status === 'Pending'
                ? '<button class="ta-btn accept" onclick="setAppStatus(' + id + ', \'Accepted\')"><i class="fas fa-check"></i> Accept</button> '
                  + '<button class="ta-btn reject" onclick="setAppStatus(' + id + ', \'Rejected\')"><i class="fas fa-times"></i> Reject</button>'
                : '<button class="ta-btn undo" onclick="setAppStatus(' + id + ', \'Pending\')"><i class="fas fa-undo"></i> Undo</button>';
        } catch (e) {
            alert('Error: ' + e.message);
            box.querySelectorAll('button').forEach(function (b) { b.disabled = false; });
        }
    }
</script>
<?php section_end(); ?>