<?php
$ViewData['Title'] = 'Tuition Posts - Admin Panel';
?>

<style>
    /* ======================
           MAIN CONTENT LAYOUT
           ====================== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ======================
           STATS GRID
           ====================== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-box {
        background: white;
        border-radius: 14px;
        padding: 22px 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        border-left: 5px solid #02B8F5;
        transition: transform 0.25s;
    }

        .stat-box:hover {
            transform: translateY(-3px);
        }

        .stat-box.open {
            border-left-color: #2ecc71;
        }

        .stat-box.apps {
            border-left-color: #3498db;
        }

        .stat-box.pending {
            border-left-color: #f39c12;
        }

        .stat-box .num {
            font-size: 30px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.1;
            font-family: 'Georgia', serif;
        }

        .stat-box .lbl {
            font-size: 13px;
            color: #64748B;
            margin-top: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

            .stat-box .lbl i {
                color: #028EA5;
                font-size: 12px;
            }

    /* ======================
           POST ROW
           ====================== */
    .post-row {
        background: white;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 12px;
        display: flex;
        gap: 18px;
        align-items: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        border-left: 5px solid #2ecc71;
        flex-wrap: wrap;
        transition: all 0.25s;
    }

        .post-row.closed {
            border-left-color: #e74c3c;
            opacity: 0.75;
        }

        /* ✅ হোম পেইজে শো করলে বিশেষ হাইলাইট */
        .post-row.showing-on-home {
            background: linear-gradient(135deg, #f9fffa 0%, #ffffff 100%);
            border-left-color: #3498db;
            box-shadow: 0 4px 20px rgba(52, 152, 219, 0.1);
        }

        .post-row:hover {
            box-shadow: 0 6px 22px rgba(2,184,245,0.08);
            transform: translateY(-2px);
        }

    /* ======================
           POST INFO
           ====================== */
    .post-info {
        flex: 1;
        min-width: 260px;
    }

        .post-info h5 {
            font-size: 16.5px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 8px;
            line-height: 1.4;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .post-info .post-code {
            display: inline-flex;
            align-items: center;
            background: #08224A;
            color: #FFB300;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
        }

        /* ✅ হোম ব্যাজ */
        .post-info .home-badge {
            display: inline-flex;
            align-items: center;
            background: #e3f2fd;
            color: #1565c0;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .post-info .post-meta {
            font-size: 12.5px;
            color: #64748B;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

            .post-info .post-meta span {
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }

                .post-info .post-meta span i {
                    color: #028EA5;
                    font-size: 12px;
                }

    /* ======================
           STATUS PILL
           ====================== */
    .status-pill {
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }

        .status-pill.open {
            background: #d4edda;
            color: #155724;
        }

        .status-pill.closed {
            background: #f8d7da;
            color: #721c24;
        }

    /* ======================
           POST ACTIONS
           ====================== */
    .post-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-act {
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.22s;
        white-space: nowrap;
    }

        .btn-act:hover {
            transform: translateY(-1px);
        }

    .btn-apps {
        background: #3498db;
        color: white;
    }

        .btn-apps:hover {
            background: #2980b9;
            color: white;
        }

    .btn-view {
        background: #F5FBFF;
        color: #08224A;
    }

        .btn-view:hover {
            background: #DCECF5;
            color: #08224A;
        }

    .btn-edit {
        background: #8e63d9;
        color: white;
    }

        .btn-edit:hover {
            background: #6f42c1;
            color: white;
        }

    .btn-toggle-open {
        background: #f39c12;
        color: white;
    }

        .btn-toggle-open:hover {
            background: #e67e22;
            color: white;
        }

    .btn-toggle-close {
        background: #16a085;
        color: white;
    }

        .btn-toggle-close:hover {
            background: #138d75;
            color: white;
        }

    /* ✅ নতুন: Show on Home বাটন */
    .btn-home-on {
        background: #e3f2fd;
        color: #1565c0;
        border: 1px solid #bbdefb;
    }

        .btn-home-on:hover {
            background: #bbdefb;
            color: #0d47a1;
        }

    .btn-home-off {
        background: #f5f5f5;
        color: #757575;
        border: 1px solid #e0e0e0;
    }

        .btn-home-off:hover {
            background: #e0e0e0;
            color: #424242;
        }

    .btn-delete {
        background: white;
        color: #e74c3c;
        border: 1.5px solid #e74c3c;
        padding: 9px 14px;
    }

        .btn-delete:hover {
            background: #f8d7da;
            color: #e74c3c;
        }

    /* ======================
           ADD BUTTON
           ====================== */
    .btn-primary-custom {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #02B8F5;
        color: white;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }

        .btn-primary-custom:hover {
            opacity: 0.92;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2,184,245,0.22);
        }

    /* ======================
           EMPTY STATE
           ====================== */
    .empty-state {
        text-align: center;
        padding: 70px 24px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
    }

        .empty-state i {
            font-size: 64px;
            color: #DCECF5;
            display: block;
            margin-bottom: 18px;
        }

        .empty-state h4 {
            color: #08224A;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .empty-state p {
            color: #7A8CA0;
            font-size: 14.5px;
            margin: 0 0 22px;
            line-height: 1.6;
        }

    /* ======================
           RESPONSIVE
           ====================== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .post-row {
            flex-direction: column;
            align-items: stretch;
        }

        .post-actions {
            justify-content: flex-start;
        }

        .btn-act {
            flex: 1;
        }

        .status-pill {
            align-self: flex-start;
        }
    }
</style>

<!-- ============================================================ -->
<!-- ADMIN SIDEBAR                                                 -->
<!-- ============================================================ -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ============================================================ -->
<!-- MAIN CONTENT                                                  -->
<!-- ============================================================ -->
<div class="admin-main-content">

    <!-- ======================
         PAGE HEADER
         ====================== -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 style="color:#08224A; margin:0; font-family:'Georgia',serif; font-weight:700;">
                <i class="fas fa-clipboard-list me-2"></i>Tuition Posts
            </h2>
            <p style="color:#64748B; margin:6px 0 0 0; font-size:14px;">
                Tuition Circular পোস্ট তৈরি করুন এবং Applications ম্যানেজ করুন
            </p>
        </div>
        <a href="/Admin/AddTuitionPost" class="btn-primary-custom">
            <i class="fas fa-plus-circle"></i> Add New Tuition
        </a>
    </div>

    <!-- ======================
         ALERTS
         ====================== -->
    <?php if ($TempData['Success'] !== null): ?>
        <div class="alert alert-success" style="border-radius:12px; border-left:4px solid #2ecc71;">
            <i class="fas fa-check-circle me-2"></i><?= e($TempData['Success']) ?>
        </div>
    <?php endif; ?>
    <?php if ($TempData['Error'] !== null): ?>
        <div class="alert alert-danger" style="border-radius:12px; border-left:4px solid #e74c3c;">
            <i class="fas fa-exclamation-circle me-2"></i><?= e($TempData['Error']) ?>
        </div>
    <?php endif; ?>

    <!-- ======================
         STATS GRID
         ====================== -->
    <div class="stats-grid">

        <div class="stat-box">
            <div class="num"><?= e($ViewBag->TotalPosts ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-list"></i> Total Posts
            </div>
        </div>

        <div class="stat-box open">
            <div class="num"><?= e($ViewBag->OpenPosts ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-check-circle"></i> Open Posts
            </div>
        </div>

        <div class="stat-box apps">
            <div class="num"><?= e($ViewBag->TotalApplications ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-user-check"></i> Applications
            </div>
        </div>

        <div class="stat-box pending">
            <div class="num"><?= e($ViewBag->PendingApplications ?? 0) ?></div>
            <div class="lbl">
                <i class="fas fa-clock"></i> Pending Review
            </div>
        </div>

    </div>

    <!-- ======================
         ANTI-FORGERY TOKEN
         ====================== -->
    <?= csrf_field() ?>

    <!-- ======================
         POSTS LIST
         ====================== -->
    <?php if ($Model === null || count($Model) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h4>No Tuition Posts Yet</h4>
            <p>এখনো কোনো Tuition Circular তৈরি করেননি।<br />প্রথম পোস্ট তৈরি করে শুরু করুন।</p>
            <a href="/Admin/AddTuitionPost" class="btn-primary-custom">
                <i class="fas fa-plus-circle"></i> Add First Tuition Post
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($Model as $p):
            $appCount = $p->Applications !== null ? count($p->Applications) : 0;
            $isOpen = $p->Status === 'Open';
            $isShowing = (bool)$p->ShowOnHome;
        ?>

            <div class="post-row <?= $isOpen ? '' : 'closed' ?> <?= $isShowing ? 'showing-on-home' : '' ?>" id="post-<?= e($p->Id) ?>">

                <div class="post-info">

                    <h5>
                        <span><?= e($p->Title) ?></span>
                        <?php if (!is_blank($p->TuitionCode)): ?>
                            <span class="post-code">
                                <i class="fas fa-hashtag me-1" style="font-size:10px;"></i><?= e($p->TuitionCode) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($isShowing): ?>
                            <span class="home-badge">
                                <i class="fas fa-home me-1" style="font-size:10px;"></i>Home Page
                            </span>
                        <?php endif; ?>
                    </h5>

                    <div class="post-meta">
                        <?php if (!is_blank($p->Area)): ?>
                            <span><i class="fas fa-map-marker-alt"></i><?= e($p->Area) ?></span>
                        <?php endif; ?>
                        <?php if (!is_blank($p->StudentClass)): ?>
                            <span><i class="fas fa-graduation-cap"></i><?= e($p->StudentClass) ?></span>
                        <?php endif; ?>
                        <?php if (!is_blank($p->Salary)): ?>
                            <span><i class="fas fa-money-bill-wave"></i>৳<?= e($p->Salary) ?></span>
                        <?php endif; ?>
                        <?php if (!is_blank($p->TutorGenderPreference)): ?>
                            <span><i class="fas fa-user-tie"></i><?= e($p->TutorGenderPreference) ?></span>
                        <?php endif; ?>
                        <span><i class="fas fa-calendar"></i><?= e(fmt_date($p->PostedDate, 'd M Y')) ?></span>
                        <span><i class="fas fa-user-check"></i><?= e($appCount) ?> Application<?= $appCount != 1 ? 's' : '' ?></span>
                    </div>

                </div>

                <span class="status-pill <?= $isOpen ? 'open' : 'closed' ?>">
                    <?php if ($isOpen): ?>
                        <i class="fas fa-circle" style="font-size:6px;"></i>
                        Open
                    <?php else: ?>
                        <i class="fas fa-lock" style="font-size:10px;"></i>
                        Closed
                    <?php endif; ?>
                </span>

                <div class="post-actions">

                    <!-- ✅ Show/Hide on Home Button -->
                    <button class="btn-act <?= $isShowing ? 'btn-home-on' : 'btn-home-off' ?>"
                            onclick="toggleShowOnHome(<?= (int)$p->Id ?>)"
                            title="<?= $isShowing ? 'Remove from Home Page' : 'Show on Home Page' ?>">
                        <i class="fas <?= $isShowing ? 'fa-home' : 'fa-eye-slash' ?>"></i>
                        <?= $isShowing ? 'On Home' : 'Show on Home' ?>
                    </button>

                    <a href="/Admin/TuitionApplications?postId=<?= e($p->Id) ?>"
                       class="btn-act btn-apps"
                       title="View Applications">
                        <i class="fas fa-users"></i> <?= e($appCount) ?>
                    </a>

                    <a href="/Tuition/Details/<?= e($p->Id) ?>" target="_blank"
                       class="btn-act btn-view"
                       title="View Public Post">
                        <i class="fas fa-eye"></i>
                    </a>

                    <a href="/Admin/EditTuitionPost/<?= e($p->Id) ?>"
                       class="btn-act btn-edit"
                       title="Edit Post">
                        <i class="fas fa-edit"></i>
                    </a>

                    <?php if ($isOpen): ?>
                        <button class="btn-act btn-toggle-open"
                                onclick="toggleStatus(<?= (int)$p->Id ?>)"
                                title="Close This Post">
                            <i class="fas fa-power-off"></i> Close
                        </button>
                    <?php else: ?>
                        <button class="btn-act btn-toggle-close"
                                onclick="toggleStatus(<?= (int)$p->Id ?>)"
                                title="Reopen This Post">
                            <i class="fas fa-power-off"></i> Reopen
                        </button>
                    <?php endif; ?>

                    <button class="btn-act btn-delete"
                            onclick="deletePost(<?= (int)$p->Id ?>, <?= e(js((string)$p->Title)) ?>)"
                            title="Delete Post">
                        <i class="fas fa-trash"></i>
                    </button>

                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- ============================================================ -->
<!-- SCRIPTS                                                       -->
<!-- ============================================================ -->
<?php section_start('Scripts'); ?>
    <script>
        // ===== Anti-Forgery Token Helper =====
        function getToken() {
            const t = document.querySelector('input[name="__RequestVerificationToken"]');
            return t ? t.value : '';
        }

        // ===== ✅ Toggle Show On Home =====
        function toggleShowOnHome(id) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('__RequestVerificationToken', getToken());

            fetch('/Admin/ToggleShowOnHome', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Sweet alert এর বদলে সাধারণ alert
                    // চাইলে location.reload() দিতে পারেন
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update');
                }
            })
            .catch(err => {
                alert('Error updating status: ' + err.message);
            });
        }

        // ===== Toggle Tuition Status =====
        function toggleStatus(id) {
            if (!confirm('Are you sure you want to change the status of this tuition post?')) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('__RequestVerificationToken', getToken());

            fetch('/Admin/ToggleTuitionStatus', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => {
                    alert('Error updating status: ' + err.message);
                });
        }

        // ===== Delete Tuition Post =====
        function deletePost(id, title) {
            const msg = '⚠️ Delete "' + title + '"?\n\n' +
                'This will also delete ALL its applications permanently.\n' +
                'This action CANNOT be undone.\n\n' +
                'Are you sure?';

            if (!confirm(msg)) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('__RequestVerificationToken', getToken());

            fetch('/Admin/DeleteTuitionPost', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => {
                    alert('Error deleting post: ' + err.message);
                });
        }
    </script>
<?php section_end(); ?>
