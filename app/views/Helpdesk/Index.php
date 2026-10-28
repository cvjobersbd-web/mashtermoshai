<?php
$ViewData['Title'] = 'Helpdesk';

$currentFilter = is_string($ViewBag->CurrentFilter) ? $ViewBag->CurrentFilter : 'all';
$searchQuery = is_string($ViewBag->SearchQuery) ? $ViewBag->SearchQuery : '';
$currentUserId = (int)($ViewBag->CurrentUserId ?? 0);
$isAdmin = $ViewBag->IsAdmin === true;

$userFullName = session_get('UserFullName')
                ?? session_get('UserName') ?? 'U';
$userInitials = 'U';
if (!is_blank($userFullName))
{
    $userInitials = initials($userFullName, 'U');
}
$userProfileImg = session_get('ProfileImageLink');
?>

<?= csrf_field() ?>

<style>
    /* ============================================================
       HELPDESK PAGE — Matches Site Theme
       Primary: #08224A | Secondary: #F5FBFF | Accent: #FFB300
       ============================================================ */

    .hd-page {
        background: #F5FBFF;
        padding: 30px 20px 70px;
        min-height: 80vh;
    }

    .hd-container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* ===== Page Header ===== */
    .hd-page-header {
        margin-bottom: 22px;
    }

    .hd-page-header h1 {
        font-family: var(--font-heading);
        font-size: 30px;
        color: #08224A;
        margin: 0 0 4px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hd-page-header p {
        color: #475569;
        margin: 0;
        font-size: 14.5px;
        line-height: 1.5;
    }

    /* ===== Create Post Card ===== */
    .hd-create-card {
        background: white;
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
        margin-bottom: 22px;
    }

    .hd-create-top {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .hd-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #08224A, #134856);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
        overflow: hidden;
    }

    .hd-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hd-avatar.sm {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }

    .hd-create-inputs {
        flex: 1;
        min-width: 0;
    }

    .hd-input {
        width: 100%;
        border: 1.5px solid #DCECF5;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14.5px;
        background: #F5FBFF;
        outline: none;
        transition: all 0.2s;
        font-family: inherit;
        color: #08224A;
        margin-bottom: 10px;
    }

    .hd-input:focus {
        border-color: #02B8F5;
        background: white;
        box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
    }

    textarea.hd-input {
        resize: vertical;
        min-height: 70px;
        line-height: 1.6;
    }

    .hd-create-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding-top: 12px;
        border-top: 1px solid #f0f0f0;
    }

    .hd-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: #F5FBFF;
        color: #08224A;
        border: 1.5px solid #DCECF5;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13.5px;
        transition: all 0.2s;
    }

    .hd-upload-btn:hover {
        border-color: #02B8F5;
        background: #F5FBFF;
    }

    .hd-upload-btn i {
        color: #E09A00;
        font-size: 16px;
    }

    .hd-upload-preview {
        display: none;
        margin-top: 10px;
        padding: 10px;
        background: #F5FBFF;
        border-radius: 10px;
        border: 1px dashed #B9D6E6;
    }

    .hd-upload-preview img,
    .hd-upload-preview video {
        max-width: 200px;
        max-height: 130px;
        border-radius: 8px;
        display: block;
    }

    .hd-upload-preview .remove-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 8px;
        font-size: 12px;
        color: #e74c3c;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 600;
    }

    .hd-btn-create {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 28px;
        background: #02B8F5;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .hd-btn-create:hover:not(:disabled) {
        background: #0099D6;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(2,184,245,0.25);
    }

    .hd-btn-create:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* ===== Filter Tabs ===== */
    .hd-tabs-row {
        display: flex;
        gap: 10px;
        margin-bottom: 22px;
        flex-wrap: wrap;
        align-items: center;
    }

    .hd-tabs {
        display: flex;
        gap: 6px;
        background: white;
        padding: 5px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .hd-tab {
        padding: 9px 20px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        color: #64748B;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }

    .hd-tab:hover {
        color: #08224A;
        background: #F5FBFF;
    }

    .hd-tab.active {
        background: #02B8F5;
        color: white;
    }

    .hd-tab i {
        font-size: 13px;
    }

    .hd-search {
        flex: 1;
        min-width: 220px;
        display: flex;
        gap: 8px;
    }

    .hd-search input {
        flex: 1;
        border: 1.5px solid #DCECF5;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 14px;
        background: white;
        outline: none;
        transition: all 0.2s;
    }

    .hd-search input:focus {
        border-color: #02B8F5;
        box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
    }

    .hd-search button {
        padding: 10px 16px;
        background: #02B8F5;
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .hd-search button:hover {
        opacity: 0.88;
    }

    /* ===== Post Card ===== */
    .hd-post {
        background: white;
        border-radius: 16px;
        padding: 22px 24px;
        margin-bottom: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.25s;
    }

    .hd-post:hover {
        box-shadow: 0 6px 22px rgba(2,184,245,0.08);
    }

    .hd-post.admin-post {
        border-left: 5px solid #FFB300;
        background: linear-gradient(135deg, #FFFBF0 0%, #FFFFFF 100%);
    }

    .hd-post-header {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .hd-post-header-info {
        flex: 1;
        min-width: 0;
    }

    .hd-post-author {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 3px;
    }

    .hd-post-author .name {
        font-weight: 700;
        color: #08224A;
        font-size: 15px;
    }

    .hd-admin-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #FFB300;
        color: #08224A;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .hd-post-time {
        font-size: 12.5px;
        color: #7A8CA0;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .hd-ack-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #E7DCF5;
        color: #5a3fa0;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .hd-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .hd-status-badge.open {
        background: #fff3cd;
        color: #856404;
    }

    .hd-status-badge.solved {
        background: #d4edda;
        color: #155724;
    }

    .hd-status-badge.closed {
        background: #e2e3e5;
        color: #41464b;
    }

    /* ===== Post Body ===== */
    .hd-post-title {
        font-size: 18px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 8px;
        line-height: 1.4;
    }

    .hd-post-content {
        color: #334155;
        font-size: 14.5px;
        line-height: 1.65;
        margin-bottom: 12px;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .hd-post-media {
        margin-bottom: 14px;
        border-radius: 12px;
        overflow: hidden;
        max-width: 480px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .hd-post-media img {
        width: 100%;
        display: block;
        max-height: 380px;
        object-fit: cover;
    }

    .hd-post-media video {
        width: 100%;
        display: block;
        max-height: 380px;
    }

    /* ===== Post Footer ===== */
    .hd-post-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #f5f5f5;
        gap: 10px;
        flex-wrap: wrap;
    }

    .hd-comment-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #64748B;
        font-size: 13.5px;
        font-weight: 600;
        background: none;
        border: none;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .hd-comment-toggle:hover {
        background: #F5FBFF;
        color: #08224A;
    }

    .hd-post-footer-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .hd-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background: #F5FBFF;
        color: #08224A;
        border: none;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .hd-action-btn:hover {
        background: #DCECF5;
    }

    .hd-action-btn.danger {
        background: white;
        color: #e74c3c;
        border: 1.5px solid #e74c3c;
    }

    .hd-action-btn.danger:hover {
        background: #f8d7da;
    }

    .hd-action-btn.success {
        background: #d4edda;
        color: #155724;
    }

    .hd-action-btn.success:hover {
        background: #b8e6c9;
    }

    /* ===== Comments Section ===== */
    .hd-comments-section {
        display: none;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #DCECF5;
    }

    .hd-comments-section.show {
        display: block;
    }

    .hd-comments-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 16px;
    }

    .hd-comment {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .hd-comment.admin-comment .hd-comment-bubble {
        background: linear-gradient(135deg, #FFFBF0, #FFF7E0);
        border-left: 3px solid #FFB300;
    }

    .hd-comment-body {
        flex: 1;
        min-width: 0;
    }

    .hd-comment-bubble {
        background: #F5FBFF;
        border-radius: 12px;
        padding: 12px 16px;
        border: 1px solid rgba(0,0,0,0.04);
    }

    .hd-comment-head {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }

    .hd-comment-head .name {
        font-weight: 700;
        color: #08224A;
        font-size: 13.5px;
    }

    .hd-comment-head .time {
        font-size: 11.5px;
        color: #7A8CA0;
    }

    .hd-comment-text {
        color: #334155;
        font-size: 14px;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .hd-comment-actions {
        display: flex;
        gap: 10px;
        margin-top: 8px;
        font-size: 12px;
    }

    .hd-comment-actions button {
        background: none;
        border: none;
        color: #64748B;
        font-weight: 600;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .hd-comment-actions button:hover {
        background: #F5FBFF;
        color: #08224A;
    }

    .hd-comment-actions button.danger:hover {
        background: #f8d7da;
        color: #e74c3c;
    }

    /* ===== Replies (Nested) ===== */
    .hd-replies {
        margin-top: 12px;
        margin-left: 30px;
        padding-left: 14px;
        border-left: 2px solid #DCECF5;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* ===== Comment Form ===== */
    .hd-comment-form {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .hd-comment-form .hd-avatar {
        width: 38px;
        height: 38px;
        font-size: 13px;
    }

    .hd-comment-form-input-wrap {
        flex: 1;
        min-width: 0;
    }

    .hd-comment-form textarea {
        width: 100%;
        border: 1.5px solid #DCECF5;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        background: #F5FBFF;
        outline: none;
        resize: vertical;
        min-height: 50px;
        font-family: inherit;
        transition: all 0.2s;
        color: #08224A;
    }

    .hd-comment-form textarea:focus {
        border-color: #02B8F5;
        background: white;
        box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
    }

    .hd-comment-form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 8px;
        gap: 8px;
    }

    .hd-btn-comment {
        padding: 9px 22px;
        background: #02B8F5;
        color: white;
        border: none;
        border-radius: 9px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .hd-btn-comment:hover:not(:disabled) {
        background: #0099D6;
        transform: translateY(-1px);
    }

    .hd-btn-comment:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .hd-btn-cancel-reply {
        padding: 9px 16px;
        background: white;
        color: #64748B;
        border: 1.5px solid #DCECF5;
        border-radius: 9px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .hd-btn-cancel-reply:hover {
        background: #F5FBFF;
        color: #08224A;
    }

    .hd-replying-to {
        background: #FFF7E0;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 12.5px;
        color: #8a5a00;
        margin-bottom: 8px;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .hd-replying-to.show {
        display: flex;
    }

    .hd-replying-to strong {
        color: #08224A;
    }

    /* ===== Empty State ===== */
    .hd-empty {
        text-align: center;
        padding: 70px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .hd-empty i {
        font-size: 60px;
        color: #DCECF5;
        display: block;
        margin-bottom: 16px;
    }

    .hd-empty h4 {
        color: #08224A;
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 8px;
    }

    .hd-empty p {
        color: #7A8CA0;
        font-size: 14px;
        margin: 0;
    }

    /* ===== Loading Spinner ===== */
    .hd-loading {
        text-align: center;
        padding: 24px;
        color: #7A8CA0;
    }

    /* ===== Alert ===== */
    .hd-alert {
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .hd-alert.danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #e74c3c;
    }

    /* ===== Responsive ===== */
    @media (max-width: 640px) {
        .hd-page {
            padding: 20px 12px 60px;
        }

        .hd-page-header h1 {
            font-size: 22px;
        }

        .hd-create-card {
            padding: 16px 16px;
        }

        .hd-post {
            padding: 18px 16px;
        }

        .hd-post-title {
            font-size: 16px;
        }

        .hd-replies {
            margin-left: 12px;
            padding-left: 10px;
        }

        .hd-post-media {
            max-width: 100%;
        }

        .hd-tabs {
            width: 100%;
            overflow-x: auto;
        }

        .hd-search {
            width: 100%;
        }

        .hd-create-top {
            gap: 10px;
        }

        .hd-avatar {
            width: 40px;
            height: 40px;
            font-size: 14px;
        }
    }
</style>

<div class="hd-page">
    <div class="hd-container">

        <!-- ===== Page Header ===== -->
        <div class="hd-page-header">
            <h1>
                <i class="fas fa-headset"></i>
                Help Desk
            </h1>
            <p>আপনার সমস্যা বা প্রশ্ন শেয়ার করুন — Admin টিম সাহায্য করবে</p>
        </div>

        <!-- ===== Create Post Card ===== -->
        <div class="hd-create-card">
            <div class="hd-create-top">
                <div class="hd-avatar">
                    <?php if (!is_blank($userProfileImg)): ?>
                        <img src="<?= e($userProfileImg) ?>" alt="me" />
                    <?php else: ?>
                        <?= e($userInitials) ?>
                    <?php endif; ?>
                </div>
                <div class="hd-create-inputs">
                    <input type="text" id="postTitle" class="hd-input"
                           placeholder="Title — আপনার সমস্যার বিষয় সংক্ষেপে লিখুন" maxlength="300" />
                    <textarea id="postContent" class="hd-input"
                              placeholder="Share or Ask Something to Everyone?" maxlength="5000"></textarea>

                    <div class="hd-upload-preview" id="uploadPreview">
                        <div id="uploadPreviewContent"></div>
                        <button type="button" class="remove-btn" onclick="removeMedia()">
                            <i class="fas fa-times"></i> Remove Media
                        </button>
                    </div>
                </div>
            </div>

            <div class="hd-create-actions">
                <label class="hd-upload-btn" for="mediaInput">
                    <i class="fas fa-photo-video"></i> Photo/Video
                </label>
                <input type="file" id="mediaInput" accept="image/*,video/*" style="display:none;" />

                <button type="button" class="hd-btn-create" id="createPostBtn" onclick="createPost()">
                    <i class="fas fa-paper-plane"></i> Create Post
                </button>
            </div>
        </div>

        <!-- ===== Filter Tabs + Search ===== -->
        <div class="hd-tabs-row">
            <div class="hd-tabs">
                <a href="/Helpdesk?filter=all"
                   class="hd-tab <?= $currentFilter === 'all' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i> All Posts
                </a>
                <a href="/Helpdesk?filter=mine"
                   class="hd-tab <?= $currentFilter === 'mine' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> My Posts
                </a>
                <a href="/Helpdesk?filter=admin"
                   class="hd-tab <?= $currentFilter === 'admin' ? 'active' : '' ?>">
                    <i class="fas fa-user-shield"></i> Admin Posts
                </a>
            </div>

            <form method="get" action="/Helpdesk" class="hd-search">
                <input type="hidden" name="filter" value="<?= e($currentFilter) ?>" />
                <input type="text" name="search" value="<?= e($searchQuery) ?>"
                       placeholder="Search posts..." />
                <button type="submit" title="Search">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- ===== Alerts ===== -->
        <?php if ($TempData['Error'] !== null): ?>
            <div class="hd-alert danger">
                <i class="fas fa-exclamation-circle" style="margin-top:2px;"></i>
                <span><?= e($TempData['Error']) ?></span>
            </div>
        <?php endif; ?>

        <!-- ===== Posts List ===== -->
        <?php if (empty($Model)): ?>
            <div class="hd-empty">
                <i class="fas fa-comments"></i>
                <h4>No posts yet</h4>
                <p>Be the first to share something with the community!</p>
            </div>
        <?php else: ?>
            <?php foreach ($Model as $post):
                $avatarUrl = !is_blank($post->UserProfileImage)
                    ? $post->UserProfileImage
                    : null;
            ?>
                <div class="hd-post <?= $post->IsAdminPost ? 'admin-post' : '' ?>" id="post-<?= e($post->Id) ?>">

                    <!-- Post Header -->
                    <div class="hd-post-header">
                        <div class="hd-avatar">
                            <?php if ($avatarUrl !== null): ?>
                                <img src="<?= e($avatarUrl) ?>" alt="<?= e($post->UserFullName) ?>" />
                            <?php else: ?>
                                <?= e($post->Initials) ?>
                            <?php endif; ?>
                        </div>

                        <div class="hd-post-header-info">
                            <div class="hd-post-author">
                                <span class="name"><?= e($post->UserFullName) ?></span>
                                <?php if ($post->IsAdminPost): ?>
                                    <span class="hd-admin-badge">
                                        <i class="fas fa-shield-alt"></i> Admin
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="hd-post-time">
                                <i class="far fa-clock"></i> <?= e($post->TimeAgo) ?>
                                <?php if ($post->Status !== 'Open'): ?>
                                    <span class="hd-status-badge <?= e(mb_strtolower((string)$post->Status)) ?>"
                                          style="margin-left:8px;">
                                        <?php if ($post->Status === 'Solved'): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php else: ?>
                                            <i class="fas fa-lock"></i>
                                        <?php endif; ?>
                                        <?= e($post->Status) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($post->IsAcknowledged): ?>
                            <span class="hd-ack-badge">
                                <i class="fas fa-check"></i> Acknowledged
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Post Body -->
                    <h3 class="hd-post-title"><?= e($post->Title) ?></h3>
                    <div class="hd-post-content"><?= e($post->Content) ?></div>

                    <?php if (!is_blank($post->MediaUrl)): ?>
                        <div class="hd-post-media">
                            <?php if ($post->MediaType === 'video'): ?>
                                <video controls preload="metadata">
                                    <source src="<?= e($post->MediaUrl) ?>" />
                                </video>
                            <?php else: ?>
                                <img src="<?= e($post->MediaUrl) ?>" alt="<?= e($post->Title) ?>"
                                     onclick="window.open('<?= e($post->MediaUrl) ?>', '_blank')"
                                     style="cursor:pointer;" />
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Post Footer -->
                    <div class="hd-post-footer">
                        <button class="hd-comment-toggle" onclick="toggleComments(<?= (int)$post->Id ?>)">
                            <i class="far fa-comment"></i>
                            <span id="commentCount-<?= e($post->Id) ?>"><?= e($post->CommentCount) ?></span> Comments
                        </button>

                        <div class="hd-post-footer-actions">
                            <?php if ($isAdmin && !$post->IsAcknowledged): ?>
                                <button class="hd-action-btn success"
                                        onclick="acknowledgePost(<?= (int)$post->Id ?>)" title="Mark Acknowledged">
                                    <i class="fas fa-check"></i> Acknowledge
                                </button>
                            <?php endif; ?>

                            <?php if ($isAdmin): ?>
                                <?php if ($post->Status === 'Open'): ?>
                                    <button class="hd-action-btn"
                                            onclick="updateStatus(<?= (int)$post->Id ?>, 'Solved')" title="Mark Solved">
                                        <i class="fas fa-check-circle"></i> Solved
                                    </button>
                                <?php elseif ($post->Status === 'Solved'): ?>
                                    <button class="hd-action-btn"
                                            onclick="updateStatus(<?= (int)$post->Id ?>, 'Open')" title="Reopen">
                                        <i class="fas fa-redo"></i> Reopen
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($isAdmin || (int)$post->UserId === $currentUserId): ?>
                                <button class="hd-action-btn danger"
                                        onclick="deletePost(<?= (int)$post->Id ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="hd-comments-section" id="comments-<?= e($post->Id) ?>">
                        <div class="hd-comments-list" id="commentsList-<?= e($post->Id) ?>">
                            <!-- JS দিয়ে load হবে -->
                        </div>

                        <!-- Comment Form -->
                        <div class="hd-comment-form">
                            <div class="hd-avatar">
                                <?php if (!is_blank($userProfileImg)): ?>
                                    <img src="<?= e($userProfileImg) ?>" alt="me" />
                                <?php else: ?>
                                    <?= e($userInitials) ?>
                                <?php endif; ?>
                            </div>
                            <div class="hd-comment-form-input-wrap">
                                <div class="hd-replying-to" id="replyingTo-<?= e($post->Id) ?>">
                                    <span>
                                        <i class="fas fa-reply me-1"></i>
                                        Replying to <strong id="replyName-<?= e($post->Id) ?>"></strong>
                                    </span>
                                    <button type="button" onclick="cancelReply(<?= (int)$post->Id ?>)"
                                            style="background:none; border:none; color:#8a5a00; cursor:pointer; font-weight:600;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <textarea id="commentInput-<?= e($post->Id) ?>"
                                          placeholder="Write a comment or reply..."
                                          maxlength="2000"></textarea>

                                <div class="hd-comment-form-actions">
                                    <button type="button" class="hd-btn-comment"
                                            id="commentBtn-<?= e($post->Id) ?>"
                                            onclick="addComment(<?= (int)$post->Id ?>)">
                                        <i class="fas fa-paper-plane"></i> Comment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<?php section_start('Scripts'); ?>
    <script>
        // ============================================================
        // ===== STATE =====
        // ============================================================
        let uploadedMediaUrl = null;
        let uploadedMediaType = null;
        const replyParentMap = {};  // postId → parentCommentId

        function getToken() {
            const t = document.querySelector('input[name="__RequestVerificationToken"]');
            return t ? t.value : '';
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

        // ============================================================
        // ===== MEDIA UPLOAD =====
        // ============================================================
        document.getElementById('mediaInput').addEventListener('change', async function () {
            const file = this.files[0];
            if (!file) return;

            const isVideo = file.type.startsWith('video/');
            const isImage = file.type.startsWith('image/');

            if (!isVideo && !isImage) {
                alert('Only images or videos are allowed.');
                this.value = '';
                return;
            }

            const maxSize = isVideo ? 50 * 1024 * 1024 : 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert(`File too large. Max ${isVideo ? '50MB' : '5MB'} allowed.`);
                this.value = '';
                return;
            }

            // Show local preview
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('uploadPreview');
                const content = document.getElementById('uploadPreviewContent');
                if (isVideo) {
                    content.innerHTML = `<video src="${e.target.result}" controls></video>`;
                } else {
                    content.innerHTML = `<img src="${e.target.result}" alt="preview" />`;
                }
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);

            // Upload to server
            const formData = new FormData();
            formData.append('file', file);
            formData.append('__RequestVerificationToken', getToken());

            try {
                const res = await fetch('/Helpdesk/UploadMedia', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    uploadedMediaUrl = data.url;
                    uploadedMediaType = data.mediaType;
                } else {
                    alert(data.message || 'Upload failed.');
                    removeMedia();
                }
            } catch (err) {
                alert('Upload error: ' + err.message);
                removeMedia();
            }
        });

        function removeMedia() {
            uploadedMediaUrl = null;
            uploadedMediaType = null;
            document.getElementById('mediaInput').value = '';
            document.getElementById('uploadPreview').style.display = 'none';
            document.getElementById('uploadPreviewContent').innerHTML = '';
        }

        // ============================================================
        // ===== CREATE POST =====
        // ============================================================
        async function createPost() {
            const title = document.getElementById('postTitle').value.trim();
            const content = document.getElementById('postContent').value.trim();
            const btn = document.getElementById('createPostBtn');

            if (!title) { alert('Title is required.'); return; }
            if (!content) { alert('Content is required.'); return; }

            btn.disabled = true;
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';

            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            if (uploadedMediaUrl) {
                formData.append('mediaUrl', uploadedMediaUrl);
                formData.append('mediaType', uploadedMediaType);
            }
            formData.append('__RequestVerificationToken', getToken());

            try {
                const res = await fetch('/Helpdesk/CreatePost', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to create post.');
                    btn.disabled = false;
                    btn.innerHTML = original;
                }
            } catch (err) {
                alert('Error: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = original;
            }
        }

        // ============================================================
        // ===== TOGGLE COMMENTS =====
        // ============================================================
        async function toggleComments(postId) {
            const section = document.getElementById('comments-' + postId);
            const isOpen = section.classList.contains('show');

            if (isOpen) {
                section.classList.remove('show');
            } else {
                section.classList.add('show');
                await loadComments(postId);
            }
        }

        // ============================================================
        // ===== LOAD COMMENTS =====
        // ============================================================
        async function loadComments(postId) {
            const list = document.getElementById('commentsList-' + postId);
            list.innerHTML = '<div class="hd-loading"><i class="fas fa-spinner fa-spin"></i></div>';

            try {
                const res = await fetch('/Helpdesk/GetPostComments?postId=' + postId);
                const data = await res.json();

                if (!data.success) {
                    list.innerHTML = '<div style="text-align:center; padding:20px; color:#7A8CA0; font-size:13.5px;">Failed to load comments.</div>';
                    return;
                }

                if (!data.comments || data.comments.length === 0) {
                    list.innerHTML = '<div style="text-align:center; padding:20px; color:#7A8CA0; font-size:13.5px;"><i class="far fa-comment-dots" style="font-size:24px; display:block; margin-bottom:8px;"></i>No comments yet — be the first!</div>';
                    return;
                }

                let html = '';
                data.comments.forEach(c => {
                    html += renderComment(c, postId, false);
                });
                list.innerHTML = html;
            } catch (err) {
                list.innerHTML = '<div style="text-align:center; padding:20px; color:#e74c3c; font-size:13.5px;">Error loading comments.</div>';
            }
        }

        // ============================================================
        // ===== RENDER COMMENT =====
        // ============================================================
        function renderComment(c, postId, isReply) {
            const avatarUrl = c.userProfileImage
                ? `<img src="${escapeHtml(c.userProfileImage)}" alt="${escapeHtml(c.userFullName)}" />`
                : c.initials;

            const adminBadge = c.isAdminReply
                ? '<span class="hd-admin-badge" style="font-size:9.5px; padding:2px 8px;"><i class="fas fa-shield-alt"></i> Admin</span>'
                : '';

            const adminClass = c.isAdminReply ? 'admin-comment' : '';

            let repliesHtml = '';
            if (!isReply && c.replies && c.replies.length > 0) {
                repliesHtml = '<div class="hd-replies">';
                c.replies.forEach(r => {
                    repliesHtml += renderComment(r, postId, true);
                });
                repliesHtml += '</div>';
            }

            const replyBtn = !isReply
                ? `<button type="button" onclick="startReply(${postId}, ${c.id}, '${escapeHtml(c.userFullName).replace(/'/g, "\\'")}')">
                       <i class="fas fa-reply"></i> Reply
                   </button>`
                : '';

            return `
                <div class="hd-comment ${adminClass}" data-comment-id="${c.id}">
                    <div class="hd-avatar sm">${avatarUrl}</div>
                    <div class="hd-comment-body">
                        <div class="hd-comment-bubble">
                            <div class="hd-comment-head">
                                <span class="name">${escapeHtml(c.userFullName)}</span>
                                ${adminBadge}
                                <span class="time">${escapeHtml(c.timeAgo)}</span>
                            </div>
                            <div class="hd-comment-text">${escapeHtml(c.content)}</div>
                        </div>
                        <div class="hd-comment-actions">
                            ${replyBtn}
                            <button type="button" class="danger" onclick="deleteComment(${c.id}, ${postId})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        ${repliesHtml}
                    </div>
                </div>
            `;
        }

        // ============================================================
        // ===== START REPLY =====
        // ============================================================
        function startReply(postId, parentCommentId, userName) {
            replyParentMap[postId] = parentCommentId;

            const box = document.getElementById('replyingTo-' + postId);
            const nameSpan = document.getElementById('replyName-' + postId);
            nameSpan.textContent = userName;
            box.classList.add('show');

            const input = document.getElementById('commentInput-' + postId);
            input.focus();
        }

        function cancelReply(postId) {
            delete replyParentMap[postId];
            document.getElementById('replyingTo-' + postId).classList.remove('show');
        }

        // ============================================================
        // ===== ADD COMMENT =====
        // ============================================================
        async function addComment(postId) {
            const input = document.getElementById('commentInput-' + postId);
            const btn = document.getElementById('commentBtn-' + postId);
            const content = input.value.trim();

            if (!content) {
                alert('Comment cannot be empty.');
                input.focus();
                return;
            }

            btn.disabled = true;
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            const formData = new FormData();
            formData.append('postId', postId);
            formData.append('content', content);

            if (replyParentMap[postId]) {
                formData.append('parentCommentId', replyParentMap[postId]);
            }

            formData.append('__RequestVerificationToken', getToken());

            try {
                const res = await fetch('/Helpdesk/AddComment', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    input.value = '';
                    cancelReply(postId);
                    await loadComments(postId);

                    // Update comment count
                    const countEl = document.getElementById('commentCount-' + postId);
                    if (countEl) {
                        const current = parseInt(countEl.textContent) || 0;
                        countEl.textContent = current + 1;
                    }
                } else {
                    alert(data.message || 'Failed to add comment.');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        }

        // ============================================================
        // ===== DELETE POST / COMMENT =====
        // ============================================================
        async function deletePost(postId) {
            if (!confirm('⚠️ Delete this post permanently?\n\nAll comments will also be removed.')) return;

            const formData = new FormData();
            formData.append('id', postId);
            formData.append('__RequestVerificationToken', getToken());

            try {
                const res = await fetch('/Helpdesk/DeletePost', { method: 'POST', body: formData });
                const data = await res.json();
                alert(data.message);
                if (data.success) {
                    document.getElementById('post-' + postId)?.remove();
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function deleteComment(commentId, postId) {
            if (!confirm('Delete this comment?')) return;

            const formData = new FormData();
            formData.append('id', commentId);
            formData.append('__RequestVerificationToken', getToken());

            try {
                const res = await fetch('/Helpdesk/DeleteComment', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    await loadComments(postId);
                    const countEl = document.getElementById('commentCount-' + postId);
                    if (countEl) {
                        const current = parseInt(countEl.textContent) || 0;
                        countEl.textContent = Math.max(0, current - 1);
                    }
                } else {
                    alert(data.message);
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        // ============================================================
        // ===== ACKNOWLEDGE / STATUS =====
        // ============================================================
        async function acknowledgePost(postId) {
            const formData = new FormData();
            formData.append('id', postId);
            formData.append('__RequestVerificationToken', getToken());

            try {
                const res = await fetch('/Helpdesk/AcknowledgePost', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) location.reload();
                else alert(data.message);
            } catch (err) { alert('Error: ' + err.message); }
        }

        async function updateStatus(postId, status) {
            const formData = new FormData();
            formData.append('id', postId);
            formData.append('status', status);
            formData.append('__RequestVerificationToken', getToken());

            try {
                const res = await fetch('/Helpdesk/UpdateStatus', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) location.reload();
                else alert(data.message);
            } catch (err) { alert('Error: ' + err.message); }
        }

        console.log('✅ Helpdesk page loaded');
    </script>
<?php section_end(); ?>
