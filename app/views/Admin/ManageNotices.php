<?php
$ViewData['Title'] = 'Manage Notices';
$notices = is_array($Model) ? $Model : [];
$newsCount = count(array_filter($notices, fn($n) => !$n->IsAnnouncement));
$announcementCount = count(array_filter($notices, fn($n) => $n->IsAnnouncement));
?>

<style>
    /* ===== Main Content Layout ===== */
    .admin-main-content {
        margin-left: 250px;
        padding: 20px;
        min-height: calc(100vh - 80px);
    }

    /* ===== Stats Grid ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-box {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        border-left: 4px solid #02B8F5;
    }

        .stat-box.news {
            border-left-color: #2e8b57;
        }

        .stat-box.announcement {
            border-left-color: #FFB300;
        }

        .stat-box .num {
            font-size: 28px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.2;
        }

        .stat-box .lbl {
            font-size: 13px;
            color: #64748B;
            margin-top: 4px;
        }

            .stat-box .lbl i {
                margin-right: 5px;
            }

    /* ===== Notice Row ===== */
    .notice-row {
        background: white;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 12px;
        display: flex;
        gap: 16px;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        flex-wrap: wrap;
        transition: all 0.25s;
    }

        .notice-row:hover {
            box-shadow: 0 6px 22px rgba(2,184,245,0.08);
            transform: translateY(-2px);
        }

        .notice-row img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: #DCECF5;
        }

        .notice-row .info {
            flex: 1;
            min-width: 200px;
        }

            .notice-row .info h5 {
                font-size: 16px;
                font-weight: 700;
                color: #08224A;
                margin: 0 0 6px;
                line-height: 1.4;
            }

            .notice-row .info .meta {
                font-size: 12.5px;
                color: #64748B;
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

                .notice-row .info .meta span i {
                    color: #028EA5;
                    margin-right: 4px;
                }

    /* ===== Category Badges ===== */
    .cat-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

        .cat-badge.news {
            background: #CFF2FC;
            color: #08224A;
        }

        .cat-badge.workshop {
            background: #02B8F5;
            color: white;
        }

        .cat-badge.course {
            background: #FFEBB8;
            color: #8a5a00;
        }

        .cat-badge.event {
            background: #E7DCF5;
            color: #5a3fa0;
        }

        .cat-badge.result {
            background: #D4EDDA;
            color: #155724;
        }

        .cat-badge.announcement {
            background: #FFB300;
            color: #08224A;
        }

    /* ===== Type Pill ===== */
    .type-pill {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.3px;
        margin-left: 8px;
        display: inline-block;
    }

        .type-pill.news {
            background: #CFF2FC;
            color: #08224A;
        }

        .type-pill.announcement {
            background: #FFEBB8;
            color: #8a5a00;
        }

    /* ===== Action Buttons ===== */
    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-edit,
    .btn-delete {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-edit {
        background: #F5FBFF;
        color: #08224A;
    }

        .btn-edit:hover {
            background: #DCECF5;
            color: #08224A;
            transform: translateY(-1px);
        }

    .btn-delete {
        background: white;
        color: #e74c3c;
        border: 1.5px solid #e74c3c;
    }

        .btn-delete:hover {
            background: #f8d7da;
            transform: translateY(-1px);
        }

    /* ===== Empty State ===== */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

        .empty-state i {
            font-size: 56px;
            color: #DCECF5;
            display: block;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #08224A;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .empty-state p {
            color: #7A8CA0;
            font-size: 14px;
            margin: 0 0 20px;
        }

    .btn-primary-custom {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 26px;
        background: #02B8F5;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
    }

        .btn-primary-custom:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2,184,245,0.22);
            color: white;
        }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .admin-main-content {
            margin-left: 0;
            padding: 20px 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .notice-row {
            flex-direction: column;
            align-items: stretch;
        }

            .notice-row img {
                width: 100%;
                height: 160px;
            }

        .actions {
            justify-content: flex-start;
        }

        .btn-edit,
        .btn-delete {
            flex: 1;
            justify-content: center;
        }
    }
</style>

<!-- ===== Admin Sidebar (Partial) ===== -->
<?php partial('Shared/_AdminSidebar'); ?>

<!-- ===== Main Content ===== -->
<div class="admin-main-content">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 style="color:#08224A; margin:0;"><i class="fas fa-newspaper me-2"></i>News &amp; Announcements</h2>
            <p style="color:#64748B; margin:4px 0 0 0; font-size:14px;">সব Notice, News এবং Announcement দেখুন ও Manage করুন</p>
        </div>
        <a href="/Admin/AddNotice" class="btn-primary-custom">
            <i class="fas fa-plus-circle"></i> Add New
        </a>
    </div>

    <?php if ($TempData['Success'] !== null): ?>
        <div class="alert alert-success" style="border-radius:10px;">
            <i class="fas fa-check-circle me-2"></i><?= e($TempData['Success']) ?>
        </div>
    <?php endif; ?>
    <?php if ($TempData['Error'] !== null): ?>
        <div class="alert alert-danger" style="border-radius:10px;">
            <i class="fas fa-exclamation-circle me-2"></i><?= e($TempData['Error']) ?>
        </div>
    <?php endif; ?>

    <?= csrf_field() ?>

    <!-- ===== Stats Grid ===== -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="num"><?= count($notices) ?></div>
            <div class="lbl"><i class="fas fa-list"></i> Total Notices</div>
        </div>
        <div class="stat-box news">
            <div class="num"><?= $newsCount ?></div>
            <div class="lbl"><i class="fas fa-newspaper"></i> News Articles</div>
        </div>
        <div class="stat-box announcement">
            <div class="num"><?= $announcementCount ?></div>
            <div class="lbl"><i class="fas fa-bullhorn"></i> Announcements</div>
        </div>
    </div>

    <!-- ===== Notice List ===== -->
    <?php if (count($notices) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-newspaper"></i>
            <h5>No notices yet</h5>
            <p>শুরু করার জন্য প্রথমে একটি News বা Announcement তৈরি করুন।</p>
            <a href="/Admin/AddNotice" class="btn-primary-custom">
                <i class="fas fa-plus-circle"></i> Add First Notice
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($notices as $n):
            $catClass = mb_strtolower($n->Category ?? 'News');
            if ($n->IsAnnouncement) { $catClass = 'announcement'; }
            $img = is_blank($n->ImageUrl)
                ? 'https://i.ibb.co/fd9s88Y/IMG-6916-1.png'
                : $n->ImageUrl;
        ?>

            <div class="notice-row" id="notice-<?= e($n->Id) ?>">
                <img src="<?= e($img) ?>" alt="<?= e($n->Title) ?>" />

                <div class="info">
                    <h5>
                        <?= e($n->Title) ?>
                        <span class="type-pill <?= $n->IsAnnouncement ? 'announcement' : 'news' ?>">
                            <?= $n->IsAnnouncement ? 'Announcement' : 'News' ?>
                        </span>
                    </h5>
                    <div class="meta">
                        <span><i class="far fa-calendar"></i><?= e(fmt_date($n->PublishedDate, 'd M Y')) ?></span>
                        <span><i class="fas fa-eye"></i><?= $n->IsActive ? 'Active' : 'Hidden' ?></span>
                    </div>
                    <div style="margin-top:8px;">
                        <span class="cat-badge <?= e($catClass) ?>"><?= e($n->IsAnnouncement ? 'Announcement' : $n->Category) ?></span>
                    </div>
                </div>

                <div class="actions">
                    <a href="/Admin/EditNotice/<?= e($n->Id) ?>"
                       class="btn-edit"
                       title="Edit Notice">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button class="btn-delete"
                            onclick="deleteNotice(<?= (int)$n->Id ?>, <?= e(js((string)$n->Title)) ?>)"
                            title="Delete Notice">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php section_start('Scripts'); ?>
    <script>
        // ===== Anti-Forgery Token Helper =====
        function getToken() {
            const t = document.querySelector('input[name="__RequestVerificationToken"]');
            return t ? t.value : '';
        }

        // ===== Delete Notice =====
        function deleteNotice(id, title) {
            if (!confirm(`⚠️ Delete "${title}"?\n\nThis action cannot be undone.`)) return;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('__RequestVerificationToken', getToken());

            fetch('/Admin/DeleteNotice', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(() => alert('Error deleting notice.'));
        }
    </script>
<?php section_end(); ?>