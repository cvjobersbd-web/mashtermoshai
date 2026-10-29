<?php
$ViewData['Title'] = 'Teacher Dashboard';

// Active section logic from URL
$activeSection = (string)(input_str('section') ?? '');
if ($activeSection === '') { $activeSection = 'overview'; }
?>

<style>
    /* ============================================================
               PAGE LAYOUT & WRAPPER
               ============================================================ */
    .td-wrapper {
        display: flex;
        gap: 25px;
        max-width: 1300px;
        margin: 0 auto;
        padding: 30px 20px 70px;
        min-height: calc(100vh - 80px);
        background: #F5FBFF;
    }

    /* ============================================================
               SIDEBAR
               ============================================================ */
    .td-sidebar {
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
    }

    .td-profile-header {
        padding: 30px 20px 20px;
        text-align: center;
        border-bottom: 1px solid #F5FBFF;
        background: linear-gradient(180deg, #F5FBFF 0%, #FFFFFF 100%);
    }

    .td-avatar {
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

    .td-name {
        font-family: var(--font-heading);
        font-size: 20px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 4px;
    }

    .td-role {
        font-size: 12px;
        color: #2ecc71;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #d4edda;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .td-menu {
        list-style: none;
        padding: 10px 0;
        margin: 0;
    }

        .td-menu li a {
            display: flex;
            align-items: center;
            padding: 14px 24px;
            color: #475569;
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 600;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

            .td-menu li a:hover {
                background: #F5FBFF;
                color: #08224A;
            }

            .td-menu li a.active {
                background: #F5FBFF;
                color: #08224A;
                border-left-color: #02B8F5;
            }

            .td-menu li a i {
                width: 24px;
                font-size: 16px;
                margin-right: 12px;
                color: #7A8CA0;
                text-align: center;
            }

            .td-menu li a.active i {
                color: #028EA5;
            }

    .td-badge {
        margin-left: auto;
        background: #DCECF5;
        color: #08224A;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 800;
    }

    /* ============================================================
               MAIN CONTENT
               ============================================================ */
    .td-main {
        flex: 1;
        min-width: 0;
    }

    .td-section {
        display: none;
        animation: fadeIn 0.3s ease;
    }

        .td-section.active {
            display: block;
        }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ============================================================
               OVERVIEW CARDS
               ============================================================ */
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .overview-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        transition: transform 0.2s;
    }

        .overview-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(2,184,245,0.08);
        }

    .oc-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

        .oc-icon.posts {
            background: #e3f2fd;
            color: #1565c0;
        }

        .oc-icon.comments {
            background: #fff3cd;
            color: #856404;
        }

        .oc-icon.responses {
            background: #d4edda;
            color: #155724;
        }

        .oc-icon.apps {
            background: #f8d7da;
            color: #721c24;
        }

        .oc-icon.likes {
            background: #f8d7da;
            color: #e74c3c;
        }

        .oc-icon.replies {
            background: #e3f2fd;
            color: #1565c0;
        }

    .oc-info h4 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        color: #08224A;
        line-height: 1.1;
    }

    .oc-info p {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: #7A8CA0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    /* ============================================================
               POSTS LIST
               ============================================================ */
    .post-stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.2s;
    }

        .post-stat-card:hover {
            box-shadow: 0 6px 20px rgba(2,184,245,0.06);
        }

    .ps-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .ps-date {
        font-size: 12.5px;
        color: #7A8CA0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ps-content {
        font-size: 14.5px;
        color: #334155;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .ps-image {
        max-width: 100%;
        max-height: 200px;
        border-radius: 10px;
        object-fit: cover;
        margin-bottom: 16px;
        border: 1px solid #DCECF5;
    }

    .ps-footer {
        display: flex;
        gap: 16px;
        padding-top: 14px;
        border-top: 1px dashed #DCECF5;
    }

    .ps-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #64748B;
    }

        .ps-stat i {
            font-size: 14px;
        }

        .ps-stat.likes i {
            color: #e74c3c;
        }

        .ps-stat.comments i {
            color: #3498db;
        }

    /* ============================================================
               STATUS SUMMARY CARDS
               ============================================================ */
    .status-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }

    .status-summary-card {
        background: white;
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        border-left: 4px solid #ccc;
    }

        .status-summary-card.pending {
            border-left-color: #f39c12;
        }

        .status-summary-card.shortlisted {
            border-left-color: #3498db;
        }

        .status-summary-card.selected {
            border-left-color: #2ecc71;
        }

        .status-summary-card.rejected {
            border-left-color: #e74c3c;
        }

    .ss-label {
        font-size: 11.5px;
        color: #7A8CA0;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .ss-value {
        font-size: 22px;
        font-weight: 800;
        color: #08224A;
    }

    /* ============================================================
               APPLICATION CARD
               ============================================================ */
    .app-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 12px;
        border-left: 5px solid #f39c12;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }

        .app-card.status-Shortlisted {
            border-left-color: #3498db;
        }

        .app-card.status-Selected {
            border-left-color: #2ecc71;
        }

        .app-card.status-Rejected {
            border-left-color: #e74c3c;
            opacity: 0.8;
        }

    .app-title {
        font-size: 17px;
        font-weight: 700;
        color: #08224A;
        margin-bottom: 10px;
    }

    /* ============================================================
               EMPTY STATE
               ============================================================ */
    .td-empty {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        border: 2px dashed #DCECF5;
    }

        .td-empty > i {
            font-size: 48px;
            color: #B9D6E6;
            margin-bottom: 16px;
        }

        .td-empty h5 {
            color: #08224A;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .td-empty p {
            color: #7A8CA0;
            font-size: 14px;
            margin: 0;
        }

    /* ============================================================
               RESPONSIVE
               ============================================================ */
    @media (max-width: 992px) {
        .td-wrapper {
            flex-direction: column;
            padding: 20px 15px;
        }

        .overview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .status-summary {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 576px) {
        .status-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .overview-grid { gap: 10px; }
        .overview-card { padding: 14px; flex-direction: column; align-items: flex-start; gap: 10px; }
        .oc-icon { width: 42px; height: 42px; font-size: 18px; }
        .oc-info p { font-size: 11px; }
    }
</style>

<div class="td-wrapper">

    <!-- ============================================================ -->
    <!-- ===== SIDEBAR ===== -->
    <!-- ============================================================ -->
    <div class="td-sidebar dd-drawer" id="tdSidebar">
        <div class="td-profile-header">
            <!-- ✅ Safe Avatar fallback logic -->
            <img src="<?= e((string)$Model->ProfileImageLink === '' || str_contains($Model->ProfileImageLink, 'imgbb.com') ? 'https://ui-avatars.com/api/?name=' . rawurlencode($Model->FullName ?? 'Teacher') . '&background=1F3B2C&color=fff&size=120' : $Model->ProfileImageLink) ?>"
                 alt="Profile" class="td-avatar" />
            <div>
                <h3 class="td-name"><?= e($Model->FullName) ?></h3>
                <span class="td-role"><i class="fas fa-check-circle"></i> Active Teacher</span>
            </div>
        </div>

        <ul class="td-menu">
            <li>
                <a href="?section=overview" class="<?= $activeSection === 'overview' ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Overview
                </a>
            </li>
            <li>
                <a href="?section=direct-requests" class="<?= $activeSection === 'direct-requests' ? 'active' : '' ?>">
                    <i class="fas fa-envelope-open-text"></i> Direct Requests
                    <span class="td-badge"><?= count($Model->TutorRequests ?? []) ?></span>
                </a>
            </li>
            <li>
                <a href="?section=posts" class="<?= $activeSection === 'posts' ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i> My Posts
                    <span class="td-badge"><?= e($Model->Stats?->TotalPosts ?? 0) ?></span>
                </a>
            </li>
            <li>
                <a href="?section=comments" class="<?= $activeSection === 'comments' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i> Comments
                    <span class="td-badge"><?= e($Model->Stats?->TotalComments ?? 0) ?></span>
                </a>
            </li>
            <li>
                <a href="?section=responses" class="<?= $activeSection === 'responses' ? 'active' : '' ?>">
                    <i class="fas fa-reply-all"></i> Responses
                    <span class="td-badge"><?= e($Model->Stats?->TotalResponses ?? 0) ?></span>
                </a>
            </li>
            <li>
                <a href="?section=applications" class="<?= $activeSection === 'applications' ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap"></i> Applications
                    <span class="td-badge"><?= e($Model->TotalApplications + count($Model->CircularApplications ?? [])) ?></span>
                </a>
            </li>
            <li>
                <a href="?section=review" class="<?= $activeSection === 'review' ? 'active' : '' ?>">
                    <i class="fas fa-star"></i> My Review
                </a>
            </li>
            <li>
                <a href="/Friends">
                    <i class="fas fa-user-group"></i> Friends
                </a>
            </li>
        </ul>
    </div>

    <!-- ============================================================ -->
    <!-- ===== MAIN CONTENT ===== -->
    <!-- ============================================================ -->
    <div class="td-main">
        <?php
            $tdLabels = ['overview' => 'Overview', 'direct-requests' => 'Direct Requests', 'posts' => 'My Posts',
                         'comments' => 'Comments', 'responses' => 'Responses', 'applications' => 'Applications', 'review' => 'My Review'];
            partial('Shared/_DashDrawer', [
                'ddTarget' => 'tdSidebar',
                'ddKicker' => 'Teacher Dashboard',
                'ddTitle'  => $tdLabels[$activeSection] ?? 'Overview',
                'ddBadge'  => count($Model->TutorRequests ?? []),
            ]);
        ?>

        <!-- ============================================================ -->
        <!-- 1. OVERVIEW SECTION -->
        <!-- ============================================================ -->
        <div class="td-section <?= $activeSection === 'overview' ? 'active' : '' ?>" id="sec-overview">
            <h2 style="color:#08224A; font-weight:800; margin-bottom:20px;">Welcome back, <?= e($Model->FullName) ?>! 👋</h2>

            <div class="overview-grid">
                <div class="overview-card">
                    <div class="oc-icon posts"><i class="fas fa-file-alt"></i></div>
                    <div class="oc-info">
                        <h4><?= e($Model->Stats?->TotalPosts ?? 0) ?></h4>
                        <p>Total Posts</p>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="oc-icon comments"><i class="fas fa-comments"></i></div>
                    <div class="oc-info">
                        <h4><?= e($Model->Stats?->TotalComments ?? 0) ?></h4>
                        <p>Total Comments</p>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="oc-icon responses"><i class="fas fa-reply-all"></i></div>
                    <div class="oc-info">
                        <h4><?= e($Model->Stats?->TotalResponses ?? 0) ?></h4>
                        <p>Total Responses</p>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="oc-icon apps"><i class="fas fa-graduation-cap"></i></div>
                    <div class="oc-info">
                        <h4><?= e($Model->TotalApplications) ?></h4>
                        <p>Tuition Applications</p>
                    </div>
                </div>
            </div>

            <div style="background:white; padding:24px; border-radius:16px; border:1px solid rgba(0,0,0,0.04); box-shadow:0 4px 16px rgba(0,0,0,0.02);">
                <h5 style="margin:0 0 10px; color:#08224A; font-weight:700;">Quick Summary</h5>
                <p style="color:#64748B; font-size:14.5px; line-height:1.7; margin:0;">
                    আপনার তৈরি করা পোস্টগুলোতে এখন পর্যন্ত <strong><?= e($Model->Stats?->TotalLikes ?? 0) ?></strong> টি লাইক এবং <strong><?= e($Model->Stats?->TotalComments ?? 0) ?></strong> টি কমেন্ট পড়েছে।
                    এছাড়া আপনার কাছে আসা <strong><?= count($Model->TutorRequests ?? []) ?></strong> টি Direct Request অপেক্ষা করছে। বাম দিকের মেনু থেকে বিস্তারিত দেখতে পারেন।
                </p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- 2. DIRECT REQUESTS SECTION -->
        <!-- ============================================================ -->
        <div class="td-section <?= $activeSection === 'direct-requests' ? 'active' : '' ?>" id="sec-direct-requests">
            <h3 style="color:#08224A; font-weight:800; margin-bottom:20px;">Direct Requests (<?= count($Model->TutorRequests ?? []) ?>)</h3>

            <?php if (empty($Model->TutorRequests)): ?>
                <div class="td-empty">
                    <i class="fas fa-inbox"></i>
                    <h5>No Direct Requests Yet</h5>
                    <p>আপনার কাছে সরাসরি কোনো রিকোয়েস্ট আসেনি। Find Tutor পেইজে আপনার প্রোফাইল থেকে রিকোয়েস্ট আসলে এখানে দেখা যাবে।</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($Model->TutorRequests as $req): ?>
                        <div style="background: white; border-radius: 14px; padding: 20px; border-left: 5px solid #FFB300; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">

                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span style="background: #02B8F5; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                        <?= e($req->RequestTypeLabel) ?>
                                    </span>
                                    <span style="font-size: 12px; color: #7A8CA0;"><i class="far fa-clock"></i> <?= e(fmt_date($req->CreatedAt, 'd M Y, h:i A')) ?></span>
                                </div>
                                <span class="badge <?= $req->Status === 'Pending' ? 'bg-warning' : ($req->Status === 'Contacted' ? 'bg-info' : 'bg-success') ?> text-dark" style="font-size:11px; padding: 5px 12px;">
                                    <?= e($req->Status) ?>
                                </span>
                            </div>

                            <h5 style="color: #08224A; font-weight: 700; margin-bottom: 8px;">
                                <i class="fas fa-user-graduate me-1"></i> <?= e($req->StudentName) ?>
                            </h5>

                            <div style="font-size: 13.5px; color: #64748B; display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 12px;">
                                <?php if ((string)$req->StudentClass !== ''): ?>
                                    <span><i class="fas fa-book" style="color:#08224A;"></i> <?= e($req->StudentClass) ?></span>
                                <?php endif; ?>
                                <?php if ((string)$req->RequiredSubject !== ''): ?>
                                    <span><i class="fas fa-book-open" style="color:#08224A;"></i> <?= e($req->RequiredSubject) ?></span>
                                <?php endif; ?>
                                <?php if ((string)$req->PreferredArea !== ''): ?>
                                    <span><i class="fas fa-map-marker-alt" style="color:#08224A;"></i> <?= e($req->PreferredArea) ?></span>
                                <?php endif; ?>
                                <?php if ((string)$req->MonthlyBudget !== ''): ?>
                                    <span><i class="fas fa-money-bill-wave" style="color:#08224A;"></i> ৳<?= e($req->MonthlyBudget) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ((string)$req->AdditionalNotes !== ''): ?>
                                <div style="background: #F5FBFF; padding: 12px; border-radius: 8px; font-size: 13px; color: #334155; margin-bottom: 16px; border: 1px dashed #DCECF5;">
                                    <strong>Notes:</strong> <?= e($req->AdditionalNotes) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Contact Actions -->
                            <div class="d-flex gap-2 flex-wrap mt-3 pt-3 border-top">
                                <?php if ((string)$req->WhatsAppLink !== ''): ?>
                                    <a href="<?= e($req->WhatsAppLink) ?>" target="_blank" class="btn btn-sm text-white" style="background:#25D366; border-radius:8px; font-weight:600;">
                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                    </a>
                                <?php endif; ?>
                                <?php if ((string)$req->PhoneNumber !== ''): ?>
                                    <a href="tel:<?= e($req->PhoneNumber) ?>" class="btn btn-sm text-white" style="background:#3498db; border-radius:8px; font-weight:600;">
                                        <i class="fas fa-phone"></i> Call
                                    </a>
                                <?php endif; ?>
                                <?php if ((string)$req->Email !== ''): ?>
                                    <a href="mailto:<?= e($req->Email) ?>" class="btn btn-sm text-white" style="background:#6c757d; border-radius:8px; font-weight:600;">
                                        <i class="fas fa-envelope"></i> Email
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================================ -->
        <!-- 3. MY POSTS SECTION -->
        <!-- ============================================================ -->
        <div class="td-section <?= $activeSection === 'posts' ? 'active' : '' ?>" id="sec-posts">
            <h3 style="color:#08224A; font-weight:800; margin-bottom:20px;">My Posts (<?= e($Model->Stats?->TotalPosts ?? 0) ?>)</h3>

            <?php if (empty($Model->PostStats)): ?>
                <div class="td-empty">
                    <i class="fas fa-file-alt"></i>
                    <h5>No Posts Yet</h5>
                    <p>আপনি এখনো কোনো পোস্ট করেননি। Find Tutor পেজে গিয়ে পোস্ট তৈরি করুন।</p>
                    <a href="/FindTutor/Index" style="display:inline-block; margin-top:15px; padding:10px 24px; background:#02B8F5; color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
                        Go to Find Tutor
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($Model->PostStats as $post): ?>
                    <div class="post-stat-card">
                        <div class="ps-header">
                            <span class="ps-date"><i class="far fa-clock"></i> <?= e($post->TimeAgo) ?></span>
                        </div>
                        <div class="ps-content">
                            <?php if ((string)$post->ImageUrl !== ''): ?>
                                <img src="<?= e($post->ImageUrl) ?>" class="ps-image" alt="Post Image" />
                            <?php endif; ?>
                            <p style="margin:0; white-space:pre-line;"><?= e($post->Content) ?></p>
                        </div>
                        <div class="ps-footer">
                            <span class="ps-stat likes"><i class="fas fa-heart"></i> <?= e($post->LikeCount) ?> Likes</span>
                            <span class="ps-stat comments"><i class="fas fa-comment"></i> <?= e($post->CommentCount) ?> Comments</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ============================================================ -->
        <!-- 4. COMMENTS SECTION -->
        <!-- ============================================================ -->
        <div class="td-section <?= $activeSection === 'comments' ? 'active' : '' ?>" id="sec-comments">
            <h3 style="color:#08224A; font-weight:800; margin-bottom:20px;">Comments (<?= e($Model->Stats?->TotalComments ?? 0) ?>)</h3>

            <div class="td-empty" style="border-style:solid; border-color:#F5FBFF;">
                <i class="fas fa-comments" style="color:#3498db;"></i>
                <h5>Total <?= e($Model->Stats?->TotalComments ?? 0) ?> Comments Received</h5>
                <p>আপনার পোস্টগুলোতে মোট <?= e($Model->Stats?->TotalComments ?? 0) ?> টি কমেন্ট এসেছে।</p>
                <div style="margin-top:16px;">
                    <a href="/FindTutor/Index" style="display:inline-block; padding:10px 24px; background:#DCECF5; color:#08224A; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
                        <i class="fas fa-external-link-alt"></i> View Comments on Find Tutor Page
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- 5. RESPONSES SECTION -->
        <!-- ============================================================ -->
        <div class="td-section <?= $activeSection === 'responses' ? 'active' : '' ?>" id="sec-responses">
            <h3 style="color:#08224A; font-weight:800; margin-bottom:20px;">Responses (<?= e($Model->Stats?->TotalResponses ?? 0) ?>)</h3>

            <div class="overview-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="overview-card">
                    <div class="oc-icon likes"><i class="fas fa-heart"></i></div>
                    <div class="oc-info">
                        <h4><?= e($Model->Stats?->TotalLikes ?? 0) ?></h4>
                        <p>Total Likes</p>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="oc-icon replies"><i class="fas fa-reply"></i></div>
                    <div class="oc-info">
                        <h4><?= e($Model->Stats?->TotalReplies ?? 0) ?></h4>
                        <p>Total Replies</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- 6. APPLICATIONS SECTION -->
        <!-- ============================================================ -->
        <div class="td-section <?= $activeSection === 'applications' ? 'active' : '' ?>" id="sec-applications">
            <h3 style="color:#08224A; font-weight:800; margin-bottom:20px;">Tuition Applications (<?= e($Model->TotalApplications) ?>)</h3>

            <?php if ($Model->TotalApplications > 0): ?>
                <div class="status-summary">
                    <div class="status-summary-card pending">
                        <div class="ss-label">Pending</div>
                        <div class="ss-value"><?= e($Model->PendingApplications) ?></div>
                    </div>
                    <div class="status-summary-card shortlisted">
                        <div class="ss-label">Shortlisted</div>
                        <div class="ss-value"><?= e($Model->ShortlistedApplications) ?></div>
                    </div>
                    <div class="status-summary-card selected">
                        <div class="ss-label">Selected</div>
                        <div class="ss-value"><?= e($Model->SelectedApplications) ?></div>
                    </div>
                    <div class="status-summary-card rejected">
                        <div class="ss-label">Rejected</div>
                        <div class="ss-value"><?= e($Model->RejectedApplications) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($Model->TuitionApplications)): ?>
                <div class="td-empty">
                    <i class="fas fa-inbox"></i>
                    <h5>No Applications Yet</h5>
                    <p>আপনি এখনো কোনো টিউশনে অ্যাপ্লাই করেননি।</p>
                    <a href="/Tuition/Circular" style="display:inline-block; margin-top:15px; padding:10px 24px; background:#02B8F5; color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
                        Browse Tuition Circular
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($Model->TuitionApplications as $app): ?>
                    <div class="app-card status-<?= e($app->Status) ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-<?= $app->Status === 'Pending' ? 'warning' : ($app->Status === 'Selected' ? 'success' : ($app->Status === 'Shortlisted' ? 'primary' : 'danger')) ?> text-white" style="font-size:11px; padding:5px 12px;">
                                <?= e($app->Status) ?>
                            </span>
                            <span style="font-size:12px; color:#7A8CA0;">
                                <i class="far fa-calendar"></i> <?= e(fmt_date($app->AppliedAt, 'd M Y')) ?>
                            </span>
                        </div>
                        <h5 class="app-title">
                            <?= e($app->TuitionPost !== null ? $app->TuitionPost->Title : 'Tuition Application #' . $app->Id) ?>
                        </h5>
                        <?php if ($app->TuitionPost !== null): ?>
                            <div style="font-size:13px; color:#64748B; display:flex; gap:15px; flex-wrap:wrap; margin-top:8px;">
                                <?php if ((string)$app->TuitionPost->Area !== ''): ?>
                                    <span><i class="fas fa-map-marker-alt" style="color:#08224A;"></i> <?= e($app->TuitionPost->Area) ?></span>
                                <?php endif; ?>
                                <?php if ((string)$app->TuitionPost->Salary !== ''): ?>
                                    <span><i class="fas fa-money-bill-wave" style="color:#08224A;"></i> ৳<?= e($app->TuitionPost->Salary) ?></span>
                                <?php endif; ?>
                                <?php if ((string)$app->TuitionPost->TuitionCode !== ''): ?>
                                    <span><i class="fas fa-hashtag" style="color:#08224A;"></i> <?= e($app->TuitionPost->TuitionCode) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ✅ স্টুডেন্টদের সার্কুলার পোস্টে করা অ্যাপ্লিকেশন -->
            <h3 style="color:#08224A; font-weight:800; margin:34px 0 8px;">Student Post Applications (<?= count($Model->CircularApplications ?? []) ?>)</h3>
            <p style="color:#64748B; font-size:13.5px; margin:0 0 16px;">Tuition Circular-এ স্টুডেন্টদের পোস্টে আপনি যেগুলোতে Apply করেছেন। স্টুডেন্ট Accept করলে তার নম্বর এখানে দেখাবে।</p>

            <?php if (empty($Model->CircularApplications)): ?>
                <div class="td-empty">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>এখনো কোনো স্টুডেন্ট পোস্টে Apply করেননি</h5>
                    <p>Tuition Circular থেকে স্টুডেন্টদের পোস্টে Apply Now চাপুন।</p>
                    <a href="/Tuition/Circular" style="display:inline-block; margin-top:15px; padding:10px 24px; background:#02B8F5; color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
                        Browse Tuition Circular
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($Model->CircularApplications as $ca):
                    $caColor = $ca->Status === 'Accepted' ? 'success' : ($ca->Status === 'Rejected' ? 'danger' : 'warning');
                    $caCard = $ca->Status === 'Accepted' ? 'Selected' : ($ca->Status === 'Rejected' ? 'Rejected' : 'Pending');
                ?>
                    <div class="app-card status-<?= e($caCard) ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-<?= e($caColor) ?> text-white" style="font-size:11px; padding:5px 12px;"><?= e($ca->Status) ?></span>
                            <span style="font-size:12px; color:#7A8CA0;"><i class="far fa-calendar"></i> <?= e(fmt_date($ca->CreatedAt, 'd M Y')) ?></span>
                        </div>
                        <h5 class="app-title"><i class="fas fa-user-graduate me-1" style="color:#028EA5;"></i> <?= e($ca->OwnerName ?? 'Student') ?>-এর পোস্ট</h5>
                        <p style="font-size:13.5px; color:#334155; margin:0 0 8px;">“<?= e($ca->PostPreview) ?>”</p>
                        <div style="font-size:13px; color:#64748B; display:flex; gap:15px; flex-wrap:wrap;">
                            <?php if (!is_blank($ca->PreferredArea)): ?><span><i class="fas fa-map-marker-alt" style="color:#028EA5;"></i> <?= e($ca->PreferredArea) ?></span><?php endif; ?>
                            <?php if (!is_blank($ca->ClassLevel)): ?><span><i class="fas fa-graduation-cap" style="color:#028EA5;"></i> <?= e($ca->ClassLevel) ?></span><?php endif; ?>
                            <?php if (!is_blank($ca->Subjects)): ?><span><i class="fas fa-book" style="color:#028EA5;"></i> <?= e($ca->Subjects) ?></span><?php endif; ?>
                            <?php if (!is_blank($ca->Budget)): ?><span><i class="fas fa-money-bill-wave" style="color:#028EA5;"></i> ৳<?= e($ca->Budget) ?></span><?php endif; ?>
                        </div>
                        <?php if ($ca->Status === 'Accepted' && (!is_blank($ca->OwnerPhone) || !is_blank($ca->OwnerEmail))): ?>
                            <div class="d-flex gap-2 flex-wrap mt-3 pt-3 border-top">
                                <?php if (!is_blank($ca->OwnerPhone)):
                                    $d = preg_replace('/\D/', '', (string)$ca->OwnerPhone); if (str_starts_with($d, '01')) $d = '88' . $d; ?>
                                    <a href="https://wa.me/<?= e($d) ?>" target="_blank" rel="noopener" class="btn btn-sm text-white" style="background:#25D366; border-radius:8px; font-weight:600;"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                                    <a href="tel:<?= e($ca->OwnerPhone) ?>" class="btn btn-sm text-white" style="background:#028EA5; border-radius:8px; font-weight:600;"><i class="fas fa-phone"></i> <?= e($ca->OwnerPhone) ?></a>
                                <?php endif; ?>
                                <?php if (!is_blank($ca->OwnerEmail)): ?>
                                    <a href="mailto:<?= e($ca->OwnerEmail) ?>" class="btn btn-sm text-white" style="background:#64748B; border-radius:8px; font-weight:600;"><i class="fas fa-envelope"></i> Email</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ============================================================ -->
        <!-- 7. MY REVIEW SECTION -->
        <!-- ============================================================ -->
        <div class="td-section <?= $activeSection === 'review' ? 'active' : '' ?>" id="sec-review">
            <h3 style="color:#08224A; font-weight:800; margin-bottom:8px;">My Review</h3>
            <p style="color:#64748B; font-size:14px; margin-bottom:18px;">
                টিউটর হিসেবে মাষ্টারমশাই নিয়ে আপনার অভিজ্ঞতা লিখুন। অ্যাডমিন Approve করলে আপনার ছবি ও নামসহ হোম পেজে দেখাবে।
            </p>
            <?php partial('Shared/_ReviewForm', ['rvReview' => $Model->MyReview ?? null, 'rvSubtitleHint' => 'যেমন: Dhaka College / Math Tutor']); ?>
        </div>

    </div>
</div>
