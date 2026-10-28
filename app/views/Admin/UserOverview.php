<?php
$ViewData['Title'] = 'User Overview';

$allUsers = $ViewBag->AllUsers ?? [];
$selected = $ViewBag->SelectedUser;

$stats = $ViewBag->UserStats ?? obj(['TotalPosts' => 0, 'TotalLikes' => 0, 'TotalComments' => 0, 'TotalTeachersConnected' => 0]);
$postStats = $ViewBag->UserPostStats ?? [];
$teachers = $ViewBag->TeachersEngaged ?? [];
$apps = $ViewBag->UserTuitionApps ?? [];
?>

<style>
    /* ===== Main Content Layout ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== User List Sidebar ===== */
    .user-list-card {
        background: white;
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.04);
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        height: fit-content;
        position: sticky;
        top: 100px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }

        .user-list-card::-webkit-scrollbar {
            width: 5px;
        }

        .user-list-card::-webkit-scrollbar-thumb {
            background: #B9D6E6;
            border-radius: 10px;
        }

    .user-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

        .user-item:hover {
            background: #F5FBFF;
        }

        .user-item.active {
            background: #CFF2FC;
            border-left: 4px solid #02B8F5;
        }

    .u-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        background: #02B8F5;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
        overflow: hidden;
    }

        .u-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

    .u-info .u-name {
        font-size: 14px;
        font-weight: 700;
        color: #08224A;
        line-height: 1.3;
    }

    .u-info .u-email {
        font-size: 11.5px;
        color: #7A8CA0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .u-role-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 2px;
    }

        .u-role-badge.teacher {
            background: #d1ecf1;
            color: #0c6b7c;
        }

        .u-role-badge.user {
            background: #eef2f6;
            color: #64748B;
        }

    /* ===== Dashboard Stats ===== */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        border-left: 5px solid #ccc;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        transition: transform 0.2s;
    }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card.posts {
            border-left-color: #3498db;
        }

        .stat-card.likes {
            border-left-color: #e74c3c;
        }

        .stat-card.comments {
            border-left-color: #f39c12;
        }

        .stat-card.teachers {
            border-left-color: #2ecc71;
        }

        .stat-card .num {
            font-size: 28px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.1;
            font-family: 'Georgia', serif;
        }

        .stat-card .lbl {
            font-size: 12px;
            color: #64748B;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

    /* ===== Detail Cards ===== */
    .detail-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .detail-title {
        font-size: 16px;
        font-weight: 700;
        color: #08224A;
        margin-bottom: 16px;
        border-bottom: 2px solid #F5FBFF;
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

        .detail-title i {
            font-size: 15px;
        }

    /* ===== Empty State ===== */
    .empty-placeholder {
        text-align: center;
        padding: 60px 20px;
        color: #7A8CA0;
        background: white;
        border-radius: 16px;
        border: 2px dashed #DCECF5;
    }

        .empty-placeholder i {
            font-size: 48px;
            color: #B9D6E6;
            margin-bottom: 16px;
            display: block;
        }

    /* ===== Scrollable List Areas ===== */
    .scroll-area {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 5px;
    }

        .scroll-area::-webkit-scrollbar {
            width: 4px;
        }

        .scroll-area::-webkit-scrollbar-thumb {
            background: #DCECF5;
            border-radius: 4px;
        }

    .list-item {
        background: #F5FBFF;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 3px solid #02B8F5;
    }

        .list-item.post {
            border-left-color: #3498db;
        }

        .list-item.teacher-item {
            border-left-color: #2ecc71;
        }

        .list-item.app {
            border-left-color: #f39c12;
        }

    /* ===== Badge helpers ===== */
    .badge-teacher-yes {
        background: #d4edda;
        color: #155724;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
    }

    .badge-teacher-no {
        background: #eef2f6;
        color: #64748B;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 15px;
        }

        .stat-grid {
            grid-template-columns: 1fr 1fr;
        }

        .user-list-card {
            position: static;
            margin-bottom: 20px;
            max-height: 400px;
        }
    }

    @media (max-width: 576px) {
        .stat-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- ===== Admin Sidebar ===== -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ===== Main Content ===== -->
<div class="admin-main-content">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:#08224A; margin:0; font-weight:800;">
                <i class="fas fa-users-viewfinder me-2"></i>User Overview
            </h2>
            <p style="color:#64748B; margin:6px 0 0 0; font-size:14px;">
                ইউজারদের পোস্ট, টিচার রেসপন্স এবং অ্যাক্টিভিটি ট্র্যাক করুন
            </p>
        </div>
    </div>

    <div class="row g-4">

        <!-- ===== Left Sidebar: User List ===== -->
        <div class="col-lg-3">
            <div class="user-list-card">
                <div style="padding: 16px 20px; background: #02B8F5; color: white; font-weight: 700; border-radius: 14px 14px 0 0; position: sticky; top: 0; z-index: 10;">
                    All Users (<?= e(count($allUsers)) ?>)
                </div>

                <?php if (count($allUsers) === 0): ?>
                    <div class="text-center p-4 text-muted" style="font-size: 13px;">
                        No users found.
                    </div>
                <?php else: ?>
                    <?php foreach ($allUsers as $u): ?>
                        <?php
                        $isActive = $selected !== null && $selected->Id === $u->Id;
                        $roleClass = $u->Role === 'Teacher' ? 'teacher' : 'user';
                        ?>

                        <a href="/Admin/UserOverview?id=<?= e($u->Id) ?>" class="user-item <?= $isActive ? 'active' : '' ?>">
                            <div class="u-avatar">
                                <?php if (!is_blank($u->ProfileImageLink)): ?>
                                    <img src="<?= e($u->ProfileImageLink) ?>" alt="<?= e($u->FullName) ?>" />
                                <?php else: ?>
                                    <?= e(mb_strlen((string)$u->FullName) > 0 ? mb_strtoupper(mb_substr((string)$u->FullName, 0, 1)) : 'U') ?>
                                <?php endif; ?>
                            </div>
                            <div class="u-info" style="min-width: 0;">
                                <div class="u-name"><?= e($u->FullName) ?></div>
                                <div class="u-email">
                                    <i class="fas fa-envelope me-1" style="font-size:10px;"></i><?= e($u->Email) ?>
                                </div>
                                <span class="u-role-badge <?= e($roleClass) ?>"><?= e($u->Role) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== Right Content Area ===== -->
        <div class="col-lg-9">
            <?php if ($selected === null): ?>
                <div class="empty-placeholder">
                    <i class="fas fa-hand-pointer"></i>
                    <h4 style="color:#08224A; font-weight:700;">Select a User</h4>
                    <p style="font-size: 15px;">বাম দিকের তালিকা থেকে যেকোনো একজন ইউজার সিলেক্ট করুন তার অ্যাক্টিভিটি দেখতে।</p>
                </div>
            <?php else: ?>
                <!-- ১. ইউজার প্রোফাইল হেডার -->
                <div class="detail-card d-flex align-items-center gap-4 flex-wrap">
                    <img src="<?= e(is_blank($selected->ProfileImageLink) ? 'https://ui-avatars.com/api/?name=' . rawurlencode((string)$selected->FullName) . '&background=1F3B2C&color=fff&size=100' : $selected->ProfileImageLink) ?>"
                         style="width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 4px solid #CFF2FC;" />
                    <div style="flex: 1; min-width: 200px;">
                        <h3 style="margin: 0 0 6px; color: #08224A; font-weight: 800;"><?= e($selected->FullName) ?></h3>
                        <div style="font-size: 13.5px; color: #64748B; display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-envelope me-1 text-muted"></i> <?= e($selected->Email) ?></span>
                            <span><i class="fas fa-phone me-1 text-muted"></i> <?= e(is_blank($selected->MobileNumber) ? '—' : $selected->MobileNumber) ?></span>
                            <span><i class="far fa-calendar me-1 text-muted"></i> Joined: <?= e(fmt_date($selected->CreatedAt, 'M Y')) ?></span>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success" style="font-size: 11px; padding: 5px 12px;"><?= e($selected->Role) ?></span>
                            <?php if ($selected->IsActive): ?>
                                <span class="badge bg-primary" style="font-size: 11px; padding: 5px 12px;">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger" style="font-size: 11px; padding: 5px 12px;">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ২. স্ট্যাটস গ্রিড -->
                <div class="stat-grid">
                    <div class="stat-card posts">
                        <div class="num"><?= e($stats->TotalPosts ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-file-alt text-primary"></i> Total Posts</div>
                    </div>
                    <div class="stat-card likes">
                        <div class="num"><?= e($stats->TotalLikes ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-heart text-danger"></i> Total Likes</div>
                    </div>
                    <div class="stat-card comments">
                        <div class="num"><?= e($stats->TotalComments ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-comment text-warning"></i> Comments</div>
                    </div>
                    <div class="stat-card teachers">
                        <div class="num"><?= e($stats->TotalTeachersConnected ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-chalkboard-teacher text-success"></i> Teachers Responded</div>
                    </div>
                </div>

                <div class="row">
                    <!-- ৩. বাম কলাম: Posts -->
                    <div class="col-md-7">
                        <div class="detail-card" style="height: calc(100% - 20px);">
                            <div class="detail-title">
                                <i class="fas fa-file-alt text-primary"></i>
                                Posts (<?= e(count($postStats)) ?>)
                            </div>

                            <?php if (count($postStats) === 0): ?>
                                <p class="text-muted text-center py-4" style="font-size: 14px;">কোনো পোস্ট পাওয়া যায়নি।</p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 480px;">
                                    <?php foreach ($postStats as $p): ?>
                                        <div class="list-item post">
                                            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-1">
                                                <span style="font-size: 11.5px; color: #7A8CA0;">
                                                    <i class="far fa-clock"></i> <?= e($p->TimeAgo) ?>
                                                </span>
                                                <?php if ($p->HasTeacherResponse): ?>
                                                    <span class="badge-teacher-yes">
                                                        <i class="fas fa-chalkboard-teacher me-1"></i><?= e($p->TeacherResponses) ?> teacher(s) responded
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge-teacher-no">
                                                        <i class="fas fa-hourglass-half me-1"></i>No teacher response yet
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px; line-height: 1.5;">
                                                <?= e($p->ContentPreview) ?>
                                            </p>

                                            <?php if (!is_blank($p->PreferredArea) || !is_blank($p->Subjects) || !is_blank($p->ClassLevel)): ?>
                                                <div style="font-size: 11.5px; color: #64748B; display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 6px;">
                                                    <?php if (!is_blank($p->PreferredArea)): ?>
                                                        <span><i class="fas fa-map-marker-alt"></i> <?= e($p->PreferredArea) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!is_blank($p->ClassLevel)): ?>
                                                        <span><i class="fas fa-graduation-cap"></i> <?= e($p->ClassLevel) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!is_blank($p->Subjects)): ?>
                                                        <span><i class="fas fa-book"></i> <?= e($p->Subjects) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div style="font-size: 11.5px; color: #7A8CA0; display: flex; gap: 12px;">
                                                <span><i class="fas fa-heart" style="color:#e74c3c;"></i> <?= e($p->LikeCount) ?></span>
                                                <span><i class="fas fa-comment" style="color:#3498db;"></i> <?= e($p->CommentCount) ?></span>
                                                <?php if (!is_blank($p->Budget)): ?>
                                                    <span><i class="fas fa-money-bill-wave" style="color:#2ecc71;"></i> ৳<?= e($p->Budget) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ৪. ডান কলাম: Teachers Responded + Applications -->
                    <div class="col-md-5">
                        <!-- Teachers Responded -->
                        <div class="detail-card">
                            <div class="detail-title">
                                <i class="fas fa-chalkboard-teacher text-success"></i>
                                Teachers Responded (<?= e(count($teachers)) ?>)
                            </div>

                            <?php if (count($teachers) === 0): ?>
                                <p class="text-muted text-center py-3" style="font-size: 13px;">
                                    কোনো টিচার এখনো রেসপন্স করেনি।
                                </p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 220px;">
                                    <?php foreach ($teachers as $t): ?>
                                        <div class="list-item teacher-item d-flex align-items-center gap-2">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #02B8F5; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; overflow: hidden;">
                                                <?php if (!is_blank($t->ProfileImageLink)): ?>
                                                    <img src="<?= e($t->ProfileImageLink) ?>" style="width:100%; height:100%; object-fit:cover;" />
                                                <?php else: ?>
                                                    <?= e($t->Initials) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-size: 13px; font-weight: 700; color: #08224A;"><?= e($t->FullName) ?></div>
                                                <div style="font-size: 11px; color: #7A8CA0;">
                                                    <i class="fas fa-comment me-1"></i><?= e($t->ResponseCount) ?> response(s) • <?= e($t->LastResponseAgo) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tuition Applications -->
                        <div class="detail-card">
                            <div class="detail-title">
                                <i class="fas fa-user-check text-warning"></i>
                                Tuition Applications (<?= e(count($apps)) ?>)
                            </div>

                            <?php if (count($apps) === 0): ?>
                                <p class="text-muted text-center py-3" style="font-size: 13px;">
                                    কোনো অ্যাপ্লিকেশন নেই।
                                </p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 220px;">
                                    <?php foreach ($apps as $a): ?>
                                        <?php
                                        $statusBg = $a->Status === 'Pending' ? 'warning'
                                                     : ($a->Status === 'Selected' ? 'success'
                                                     : ($a->Status === 'Shortlisted' ? 'primary'
                                                     : 'secondary'));
                                        ?>

                                        <div class="list-item app">
                                            <div class="d-flex justify-content-between align-items-center mb-1 gap-1">
                                                <strong style="color: #08224A; font-size: 12.5px;">
                                                    <?= e($a->TuitionPost !== null ? $a->TuitionPost->Title : 'Tuition #' . $a->TuitionPostId) ?>
                                                </strong>
                                                <span class="badge bg-<?= e($statusBg) ?> text-dark" style="font-size: 9px; padding: 3px 8px;"><?= e($a->Status) ?></span>
                                            </div>
                                            <?php if ($a->TuitionPost !== null): ?>
                                                <div style="font-size: 11.5px; color: #64748B; display: flex; flex-wrap: wrap; gap: 10px;">
                                                    <?php if (!is_blank($a->TuitionPost->Area)): ?>
                                                        <span><i class="fas fa-map-marker-alt"></i> <?= e($a->TuitionPost->Area) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!is_blank($a->TuitionPost->Salary)): ?>
                                                        <span><i class="fas fa-money-bill-wave"></i> ৳<?= e($a->TuitionPost->Salary) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="font-size: 10.5px; color: #7A8CA0; margin-top: 4px;">
                                                <i class="far fa-calendar me-1"></i><?= e(fmt_date($a->AppliedAt, 'd M Y')) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
