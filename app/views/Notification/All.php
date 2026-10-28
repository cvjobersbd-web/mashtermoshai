<?php
$ViewData['Title'] = 'All Notifications';
?>

<style>
    .notif-page-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    .notif-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .notif-page-header h2 {
        font-family: var(--font-heading);
        font-size: 28px;
        color: #08224A;
        margin: 0;
    }

    .notif-page-header h2 i {
        margin-right: 10px;
        color: #028EA5;
    }

    .btn-clear-all {
        background: white;
        color: #e74c3c;
        border: 1.5px solid #e74c3c;
        padding: 8px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-clear-all:hover {
        background: #f8d7da;
    }

    .notif-full-item {
        background: white;
        border-radius: 14px;
        padding: 18px 20px;
        margin-bottom: 12px;
        display: flex;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        border-left: 4px solid #DCECF5;
        transition: all 0.25s;
        text-decoration: none;
        color: inherit;
    }

    .notif-full-item.unread {
        border-left-color: #FFB300;
        background: #FFFBF0;
    }

    .notif-full-item:hover {
        box-shadow: 0 6px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }

    .notif-full-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 20px;
    }

    .notif-full-icon.info { background: #d1ecf1; color: #0c6b7c; }
    .notif-full-icon.success { background: #d4edda; color: #155724; }
    .notif-full-icon.warning { background: #fff3cd; color: #856404; }
    .notif-full-icon.danger { background: #f8d7da; color: #721c24; }

    .notif-full-content {
        flex: 1;
        min-width: 0;
    }

    .notif-full-content h6 {
        font-size: 15px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 6px;
    }

    .notif-full-content p {
        font-size: 14px;
        color: #64748B;
        margin: 0 0 6px;
        line-height: 1.5;
    }

    .notif-full-time {
        font-size: 12px;
        color: #7A8CA0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .notif-full-time i {
        font-size: 11px;
    }

    .notif-unread-dot {
        width: 8px;
        height: 8px;
        background: #e74c3c;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 8px;
    }

    .empty-notif {
        text-align: center;
        padding: 70px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    .empty-notif i {
        font-size: 64px;
        color: #DCECF5;
        margin-bottom: 16px;
        display: block;
    }

    .empty-notif h4 {
        color: #08224A;
        margin-bottom: 8px;
    }

    .empty-notif p {
        color: #7A8CA0;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .empty-notif a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 26px;
        background: #02B8F5;
        color: white;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        font-size: 14px;
    }

    .empty-notif a:hover {
        opacity: 0.88;
        color: white;
    }

    @media (max-width: 576px) {
        .notif-page-header h2 {
            font-size: 22px;
        }

        .notif-full-item {
            padding: 14px 16px;
            gap: 12px;
        }

        .notif-full-icon {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .notif-full-content h6 {
            font-size: 14px;
        }

        .notif-full-content p {
            font-size: 13px;
        }
    }
</style>

<div class="notif-page-wrapper">

    <!-- Header -->
    <div class="notif-page-header">
        <h2><i class="fas fa-bell"></i>All Notifications</h2>

        <?php if ($Model !== null && count($Model) > 0): ?>
            <button class="btn-clear-all" onclick="clearAllNotifications()">
                <i class="fas fa-trash"></i> Clear All
            </button>
        <?php endif; ?>
    </div>

    <?= csrf_field() ?>

    <!-- Notification List -->
    <?php if ($Model === null || count($Model) == 0): ?>
        <div class="empty-notif">
            <i class="fas fa-bell-slash"></i>
            <h4>No notifications yet</h4>
            <p>You're all caught up! New notifications will appear here.</p>
            <a href="/">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($Model as $n):
            $typeClass = (string)$n->Type === '' ? "info" : mb_strtolower($n->Type);
            $icon = (string)$n->Icon === '' ? "fa-bell" : $n->Icon;
            $unreadClass = $n->IsRead ? "" : "unread";

            if ((string)$n->Link !== ''): ?>
                <a href="<?= e($n->Link) ?>" class="notif-full-item <?= e($unreadClass) ?>">
                    <div class="notif-full-icon <?= e($typeClass) ?>">
                        <i class="fas <?= e($icon) ?>"></i>
                    </div>
                    <div class="notif-full-content">
                        <h6><?= e($n->Title) ?></h6>
                        <p><?= e($n->Message) ?></p>
                        <div class="notif-full-time">
                            <i class="far fa-clock"></i>
                            <?= e(fmt_date($n->CreatedAt, 'd M Y, h:i A')) ?>
                        </div>
                    </div>
                    <?php if (!$n->IsRead): ?>
                        <div class="notif-unread-dot"></div>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <div class="notif-full-item <?= e($unreadClass) ?>">
                    <div class="notif-full-icon <?= e($typeClass) ?>">
                        <i class="fas <?= e($icon) ?>"></i>
                    </div>
                    <div class="notif-full-content">
                        <h6><?= e($n->Title) ?></h6>
                        <p><?= e($n->Message) ?></p>
                        <div class="notif-full-time">
                            <i class="far fa-clock"></i>
                            <?= e(fmt_date($n->CreatedAt, 'd M Y, h:i A')) ?>
                        </div>
                    </div>
                    <?php if (!$n->IsRead): ?>
                        <div class="notif-unread-dot"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php section_start('Scripts'); ?>
    <script>
        function clearAllNotifications() {
            if (!confirm('আপনি কি নিশ্চিত যে সব নোটিফিকেশন মুছে ফেলতে চান? এই কাজটি ফিরিয়ে আনা যাবে না।')) {
                return;
            }

            var formData = new FormData();
            var token = document.querySelector('input[name="__RequestVerificationToken"]');
            var headers = {};
            if (token) { headers['RequestVerificationToken'] = token.value; }

            fetch('/Notification/ClearAll', {
                method: 'POST',
                body: formData,
                headers: headers
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to clear notifications');
                }
            })
            .catch(function () {
                alert('Error clearing notifications');
            });
        }
    </script>
<?php section_end(); ?>
