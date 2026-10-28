<?php
$ViewData['Title'] = 'Teacher Overview';

$allTeachers = $ViewBag->AllTeachers ?? [];
$selectedTeacher = $ViewBag->SelectedTeacher;

// যদি কোনো টিচার সিলেক্ট করা না থাকে, তবে ডিফল্ট ভ্যালু
$stats = $ViewBag->TeacherStats ?? obj(['TotalPosts' => 0, 'TotalComments' => 0, 'TotalLikes' => 0, 'TotalReplies' => 0, 'TotalTuitionApplications' => 0, 'TotalResponses' => 0]);
$postStats = $ViewBag->PostStats ?? [];
$directRequests = $ViewBag->DirectRequests ?? [];
$tuitionApps = $ViewBag->TuitionApps ?? [];
?>

<style>
    /* ===== Main Content Layout ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== Teacher List Sidebar ===== */
    .teacher-list-card {
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

        .teacher-list-card::-webkit-scrollbar {
            width: 5px;
        }

        .teacher-list-card::-webkit-scrollbar-thumb {
            background: #B9D6E6;
            border-radius: 10px;
        }

    .teacher-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

        .teacher-item:hover {
            background: #F5FBFF;
        }

        .teacher-item.active {
            background: #CFF2FC;
            border-left: 4px solid #02B8F5;
        }

    .t-avatar {
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
    }

    .t-info .t-name {
        font-size: 14px;
        font-weight: 700;
        color: #08224A;
        line-height: 1.3;
    }

    .t-info .t-email {
        font-size: 11.5px;
        color: #7A8CA0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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

        .stat-card.reqs {
            border-left-color: #f39c12;
        }

        .stat-card.apps {
            border-left-color: #2ecc71;
        }

        .stat-card.interactions {
            border-left-color: #e74c3c;
        }

        .stat-card .num {
            font-size: 28px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.1;
            font-family: var(--font-heading);
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

        .list-item.req {
            border-left-color: #f39c12;
        }

        .list-item.app {
            border-left-color: #2ecc71;
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

        .teacher-list-card {
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
                <i class="fas fa-chart-line me-2"></i>Teacher Overview
            </h2>
            <p style="color:#64748B; margin:6px 0 0 0; font-size:14px;">
                টিচারদের পোস্ট, রিকোয়েস্ট এবং অ্যাক্টিভিটি ট্র্যাক করুন
            </p>
        </div>
    </div>

    <div class="row g-4">

        <!-- ===== Left Sidebar: Teacher List ===== -->
        <div class="col-lg-3">
            <div class="teacher-list-card">
                <div style="padding: 16px 20px; background: #02B8F5; color: white; font-weight: 700; border-radius: 14px 14px 0 0; position: sticky; top: 0; z-index: 10;">
                    All Teachers (<?= e(count($allTeachers)) ?>)
                </div>

                <?php if (count($allTeachers) === 0): ?>
                    <div class="text-center p-4 text-muted" style="font-size: 13px;">
                        No teachers found.
                    </div>
                <?php else: ?>
                    <?php foreach ($allTeachers as $t): ?>
                        <?php $isActive = $selectedTeacher !== null && $selectedTeacher->Id === $t->Id; ?>
                        <a href="/Admin/TeacherOverview?id=<?= e($t->Id) ?>" class="teacher-item <?= $isActive ? 'active' : '' ?>">
                            <div class="t-avatar">
                                <?php if (!is_blank($t->ProfileImageLink)): ?>
                                    <img src="<?= e($t->ProfileImageLink) ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover;" />
                                <?php else: ?>
                                    <?= e(mb_strlen((string)$t->FullName) > 0 ? mb_strtoupper(mb_substr((string)$t->FullName, 0, 1)) : 'T') ?>
                                <?php endif; ?>
                            </div>
                            <div class="t-info" style="min-width: 0;">
                                <div class="t-name"><?= e($t->FullName) ?></div>
                                <div class="t-email"><i class="fas fa-envelope me-1" style="font-size:10px;"></i><?= e($t->Email) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== Right Content Area ===== -->
        <div class="col-lg-9">
            <?php if ($selectedTeacher === null): ?>
                <div class="empty-placeholder">
                    <i class="fas fa-hand-pointer"></i>
                    <h4 style="color:#08224A; font-weight:700;">Select a Teacher</h4>
                    <p style="font-size: 15px;">বাম দিকের তালিকা থেকে যেকোনো একজন টিচার সিলেক্ট করুন তার বিস্তারিত দেখতে।</p>
                </div>
            <?php else: ?>
                <!-- ১. টিচার প্রোফাইল হেডার -->
                <div class="detail-card d-flex align-items-center gap-4 flex-wrap">
                    <img src="<?= e(is_blank($selectedTeacher->ProfileImageLink) ? 'https://ui-avatars.com/api/?name=' . rawurlencode((string)$selectedTeacher->FullName) . '&background=1F3B2C&color=fff&size=100' : $selectedTeacher->ProfileImageLink) ?>"
                         style="width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 4px solid #CFF2FC;" />
                    <div style="flex: 1; min-width: 200px;">
                        <h3 style="margin: 0 0 6px; color: #08224A; font-weight: 800;"><?= e($selectedTeacher->FullName) ?></h3>
                        <div style="font-size: 13.5px; color: #64748B; display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-envelope me-1 text-muted"></i> <?= e($selectedTeacher->Email) ?></span>
                            <span><i class="fas fa-phone me-1 text-muted"></i> <?= e($selectedTeacher->MobileNumber) ?></span>
                            <span><i class="far fa-calendar me-1 text-muted"></i> Joined: <?= e(fmt_date($selectedTeacher->CreatedAt, 'M Y')) ?></span>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success" style="font-size: 11px; padding: 5px 12px;">Teacher</span>
                            <?php if ($selectedTeacher->IsActive): ?>
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
                    <div class="stat-card reqs">
                        <div class="num"><?= e(count($directRequests)) ?></div>
                        <div class="lbl"><i class="fas fa-envelope-open-text text-warning"></i> Direct Requests</div>
                    </div>
                    <div class="stat-card apps">
                        <div class="num"><?= e(count($tuitionApps)) ?></div>
                        <div class="lbl"><i class="fas fa-user-check text-success"></i> Tuition Apps</div>
                    </div>
                    <div class="stat-card interactions">
                        <div class="num"><?= e($stats->TotalResponses ?? 0) ?></div>
                        <div class="lbl"><i class="fas fa-comments text-danger"></i> Interactions</div>
                    </div>
                </div>

                <div class="row">
                    <!-- ৩. বাম কলাম: Recent Posts -->
                    <div class="col-md-6">
                        <div class="detail-card" style="height: calc(100% - 20px);">
                            <div class="detail-title"><i class="fas fa-file-alt text-primary"></i> Recent Posts</div>

                            <?php if (count($postStats) === 0): ?>
                                <p class="text-muted text-center py-4" style="font-size: 14px;">কোনো পোস্ট পাওয়া যায়নি।</p>
                            <?php else: ?>
                                <div class="scroll-area">
                                    <?php foreach ($postStats as $p): ?>
                                        <div class="list-item">
                                            <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px; line-height: 1.5;">
                                                <?= e(mb_strlen((string)$p->Content) > 120 ? mb_substr((string)$p->Content, 0, 120) . '...' : $p->Content) ?>
                                            </p>
                                            <?php if (!is_blank($p->ImageUrl)): ?>
                                                <div style="font-size: 11px; color: #3498db; margin-bottom: 6px;"><i class="fas fa-image"></i> Has Image</div>
                                            <?php endif; ?>
                                            <div style="font-size: 11.5px; color: #7A8CA0; display: flex; gap: 12px; align-items: center;">
                                                <span><i class="far fa-clock"></i> <?= e($p->TimeAgo) ?></span>
                                                <span><i class="fas fa-heart" style="color:#e74c3c;"></i> <?= e($p->LikeCount) ?></span>
                                                <span><i class="fas fa-comment" style="color:#3498db;"></i> <?= e($p->CommentCount) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ৪. ডান কলাম: Direct Requests ও Applications -->
                    <div class="col-md-6">
                        <!-- Direct Requests -->
                        <div class="detail-card">
                            <div class="detail-title"><i class="fas fa-envelope-open-text text-warning"></i> Direct Requests</div>
                            <?php if (count($directRequests) === 0): ?>
                                <p class="text-muted text-center py-3" style="font-size: 13px;">কোনো রিকোয়েস্ট নেই।</p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 200px;">
                                    <?php foreach ($directRequests as $r): ?>
                                        <div class="list-item req">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong style="color:#08224A; font-size:13.5px;"><?= e($r->StudentName) ?></strong>
                                                <span class="badge bg-<?= e($r->Status === 'Pending' ? 'warning' : ($r->Status === 'Contacted' ? 'info' : 'success')) ?> text-dark" style="font-size:9px; padding: 3px 8px;"><?= e($r->Status) ?></span>
                                            </div>
                                            <div style="font-size: 12px; color: #64748B; display: flex; flex-wrap: wrap; gap: 8px;">
                                                <?php if (!is_blank($r->RequiredSubject)): ?>
                                                    <span><i class="fas fa-book"></i> <?= e($r->RequiredSubject) ?></span>
                                                <?php endif; ?>
                                                <?php if (!is_blank($r->MonthlyBudget)): ?>
                                                    <span><i class="fas fa-money-bill-wave"></i> ৳<?= e($r->MonthlyBudget) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="font-size: 11px; color: #7A8CA0; margin-top: 4px;">
                                                <i class="fas fa-phone me-1"></i> <?= e($r->PhoneNumber) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tuition Applications -->
                        <div class="detail-card">
                            <div class="detail-title"><i class="fas fa-user-check text-success"></i> Tuition Applications</div>
                            <?php if (count($tuitionApps) === 0): ?>
                                <p class="text-muted text-center py-3" style="font-size: 13px;">কোনো অ্যাপ্লিকেশন নেই।</p>
                            <?php else: ?>
                                <div class="scroll-area" style="max-height: 200px;">
                                    <?php foreach ($tuitionApps as $app): ?>
                                        <div class="list-item app">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong style="color:#08224A; font-size:13px;"><?= e($app->TuitionPost?->Title ?? 'Unknown Post') ?></strong>
                                                <span class="badge bg-<?= e($app->Status === 'Pending' ? 'warning' : ($app->Status === 'Selected' ? 'success' : 'secondary')) ?> text-dark" style="font-size:9px; padding: 3px 8px;"><?= e($app->Status) ?></span>
                                            </div>
                                            <?php if ($app->TuitionPost !== null): ?>
                                                <div style="font-size: 12px; color: #64748B; display: flex; flex-wrap: wrap; gap: 10px; margin-top: 4px;">
                                                    <?php if (!is_blank($app->TuitionPost->Area)): ?>
                                                        <span><i class="fas fa-map-marker-alt"></i> <?= e($app->TuitionPost->Area) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!is_blank($app->TuitionPost->Salary)): ?>
                                                        <span><i class="fas fa-money-bill-wave"></i> ৳<?= e($app->TuitionPost->Salary) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="font-size: 11px; color: #7A8CA0; margin-top: 6px;">
                                                <i class="far fa-calendar me-1"></i> Applied: <?= e(fmt_date($app->AppliedAt, 'd M Y')) ?>
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
