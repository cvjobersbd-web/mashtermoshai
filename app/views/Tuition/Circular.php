<?php
$ViewData['Title'] = 'Tuition Circular';
$isLoggedIn = session_get('UserName') != null;
$userRole = session_get('UserRole');
$isTeacher = $userRole == 'Teacher';

$userId = session_get('UserId');
$userFullName = session_get('UserFullName') ?? session_get('UserName') ?? 'User';
$userProfileImg = session_get('ProfileImageLink');

$feedPosts = [];
try
{
    $feedPosts = db()->GetAllTuitionCircularPosts($userId);
}
catch (Throwable $ex) { error_log($ex->getMessage()); }

// ✅ টিচার কোন কোন পোস্টে আগেই Apply করেছে (বাটনে "Applied" দেখানোর জন্য)
$myApplied = [];
$myPhone = '';
if ($isLoggedIn && $isTeacher && $userId) {
    $myApplied = db()->GetCircularApplicationStatusesByTeacher((int)$userId);
    $myPhone = (string)(db()->GetUserById((int)$userId)?->MobileNumber ?? '');
}

$RenderCommentText = function (?string $text): string {
    $text ??= '';
    if (preg_match('/^@\[([^\]]+)\]\s?/u', $text, $m)) {
        $rest = substr($text, strlen($m[0]));
        return '<span class="fbc-mention">' . e($m[1]) . '</span> '
            . e($rest);
    }
    return e($text);
};

// DB stores local server time; original stored UTC and appended "Z"
$CommentIso = fn($dt) => ($d = to_dt($dt)) ? gmdate('Y-m-d\TH:i:s', $d->getTimestamp()) . 'Z' : '';
?>
<?= csrf_field() ?>

<style>
    /* ======================
           PAGE WRAPPER & LAYOUT
           ====================== */
    .tc-page {
        background: #F5FBFF;
        padding: 40px 20px 70px;
        min-height: 80vh;
    }

    .tc-layout {
        max-width: 1150px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 30px;
        align-items: start;
    }

    /* ======================
           HEADER
           ====================== */
    .tc-header {
        text-align: center;
        margin-bottom: 30px;
        grid-column: 1 / -1;
    }

        .tc-header h1 {
            font-family: var(--font-heading);
            font-size: 38px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .tc-header p {
            color: #475569;
            font-size: 15.5px;
            max-width: 640px;
            margin: 0 auto;
            line-height: 1.6;
        }

    /* ======================
           POST CREATE BOX (FindTutor Style)
           ====================== */
    .post-create-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
        margin-bottom: 24px;
    }

    .post-create-top {
        display: flex;
        gap: 14px;
        align-items: center;
    }

    .pc-avatar {
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

        .pc-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

    .pc-input {
        flex: 1;
        background: #F5FBFF;
        border: none;
        border-radius: 30px;
        padding: 14px 20px;
        font-size: 14.5px;
        cursor: pointer;
        transition: all 0.2s;
        color: #475569;
        text-align: left;
    }

        .pc-input:hover {
            background: #DCECF5;
        }

    .post-create-actions {
        display: flex;
        justify-content: space-around;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f0f0f0;
    }

    .pc-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: none;
        border: none;
        color: #64748B;
        font-weight: 600;
        font-size: 13.5px;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 8px;
        transition: background 0.2s;
    }

        .pc-action-btn:hover {
            background: #F5FBFF;
        }

        .pc-action-btn i.fa-images {
            color: #2ecc71;
        }

        .pc-action-btn i.fa-video {
            color: #e74c3c;
        }

    /* ======================
           POST FEED
           ====================== */
    .post-feed {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 40px;
    }

    .fb-post {
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .fb-post-header {
        padding: 16px 20px 12px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .fb-post-user {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .fb-post-user-info h6 {
        margin: 0 0 2px;
        font-weight: 700;
        color: #08224A;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fb-author-link { color: inherit; text-decoration: none; }
    .fb-author-link:hover { color: #028EA5; text-decoration: underline; }

    .fb-post-user-info .time {
        font-size: 12.5px;
        color: #7A8CA0;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .fb-post-content {
        padding: 0 20px 16px;
        font-size: 14.5px;
        color: #08224A;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .fb-post-media img {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
    }

    .fb-post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 0 20px 12px;
    }

    .fb-tag {
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

    .fb-post-stats {
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #64748B;
        border-bottom: 1px solid #f0f0f0;
    }

    .fb-post-actions {
        display: flex;
        padding: 4px;
        flex-wrap: wrap;
    }

    .fb-action-btn {
        flex: 1;
        background: none;
        border: none;
        padding: 8px;
        border-radius: 8px;
        color: #64748B;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.2s;
    }

        .fb-action-btn:hover {
            background: #F5FBFF;
        }

    .apply-btn {
        flex: 1;
        background: #02B8F5;
        color: white;
        margin: 0 8px 8px 8px;
        border-radius: 8px;
        padding: 10px;
        font-weight: 700;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

        .apply-btn.applied,
        .apply-btn.applied:hover {
            background: #DCECF5;
            color: #028EA5;
            cursor: default;
            transform: none;
        }

        .apply-btn:hover {
            background: #0099D6;
            color: white;
            transform: translateY(-1px);
        }

    /* ======================
           COMMENTS AREA (Facebook Style)
           ====================== */
    .fb-comments-area {
        padding: 10px 16px 14px;
        background: #fff;
        display: none;
        border-top: 1px solid #eceae0;
    }

        .fb-comments-area.show {
            display: block;
        }

    .fb-comment-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 14px;
    }

    .fbc {
        display: flex;
        gap: 8px;
        align-items: flex-start;
    }

    .fbc-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #02B8F5;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }

        .fbc-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

    .fbc.is-reply .fbc-avatar,
    .fbc-replybox .fbc-avatar {
        width: 28px;
        height: 28px;
        font-size: 11px;
    }

    .fbc-main {
        flex: 1;
        min-width: 0;
    }

    .fbc-bubble {
        position: relative;
        display: inline-block;
        max-width: 100%;
        background: #F0F2F5;
        border-radius: 18px;
        padding: 8px 12px;
        box-sizing: border-box;
    }

        .fbc-bubble.has-likes {
            margin-bottom: 8px;
        }

    .fbc-name {
        display: block;
        font-weight: 600;
        font-size: 13px;
        color: #1c1e21;
        line-height: 1.3;
    }

    .fbc-text {
        font-size: 14.5px;
        line-height: 1.35;
        color: #1c1e21;
        word-wrap: break-word;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
    }

    .fbc-mention {
        color: #1877F2;
        font-weight: 500;
    }

    .fbc-like-badge {
        position: absolute;
        right: -10px;
        bottom: -10px;
        display: flex;
        align-items: center;
        gap: 3px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.3);
        padding: 1px 7px 1px 2px;
        font-size: 12px;
        color: #65676b;
        line-height: 1.4;
    }

        .fbc-like-badge.zero {
            display: none;
        }

        .fbc-like-badge i {
            background: #1877F2;
            color: #fff;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            font-size: 8.5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

    .fbc-meta {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 2px 12px 0;
        font-size: 12px;
        font-weight: 600;
        color: #65676b;
    }

        .fbc-meta button {
            background: none;
            border: none;
            padding: 0;
            font: inherit;
            color: inherit;
            cursor: pointer;
        }

            .fbc-meta button:hover {
                text-decoration: underline;
            }

        .fbc-meta .fbc-like-btn.liked {
            color: #1877F2;
        }

        .fbc-meta .fbc-time {
            font-weight: 400;
            cursor: default;
        }

    .fbc-view-replies {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: none;
        border: none;
        padding: 6px 12px 2px;
        font-size: 13px;
        font-weight: 600;
        color: #65676b;
        cursor: pointer;
    }

        .fbc-view-replies:hover {
            text-decoration: underline;
        }

        .fbc-view-replies i {
            font-size: 11px;
        }

    .fbc-replies {
        display: none;
        flex-direction: column;
        gap: 10px;
        margin-top: 8px;
    }

    .fbc-input-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .fbc-pill {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 6px;
        background: #F0F2F5;
        border-radius: 20px;
        padding: 4px 6px 4px 14px;
    }

        .fbc-pill input {
            flex: 1;
            min-width: 0;
            border: none;
            background: transparent;
            outline: none;
            font-size: 14px;
            padding: 6px 0;
            color: #1c1e21;
        }

    .fbc-mention-tag {
        color: #1877F2;
        font-weight: 500;
        font-size: 14px;
        white-space: nowrap;
    }

    .fbc-send {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: #1877F2;
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

        .fbc-send:hover {
            background: #e4e6eb;
        }

        .fbc-send:disabled {
            color: #bcc0c4;
            cursor: default;
        }

    /* ======================
           MODAL STYLES (FindTutor Style)
           ====================== */
    .modal-content {
        border-radius: 16px;
        border: none;
    }

    .modal-header {
        border-bottom: 1px solid #f0f0f0;
    }

    .modal-title {
        font-weight: 700;
        color: #08224A;
        font-size: 18px;
    }

    .modal-textarea {
        width: 100%;
        border: none;
        outline: none;
        font-size: 18px;
        resize: none;
        min-height: 100px;
        margin-top: 10px;
        color: #08224A;
    }

        .modal-textarea::placeholder {
            color: #7A8CA0;
        }

    .modal-preview {
        margin-top: 15px;
        display: none;
        position: relative;
    }

        .modal-preview img {
            width: 100%;
            border-radius: 12px;
            max-height: 300px;
            object-fit: cover;
        }

    .remove-preview {
        position: absolute;
        top: 10px;
        right: 10px;
        background: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        cursor: pointer;
    }

    .modal-actions-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding: 10px;
        border: 1px solid #DCECF5;
        border-radius: 12px;
    }

    .icon-btn {
        background: none;
        border: none;
        font-size: 18px;
        color: #7A8CA0;
        cursor: pointer;
        padding: 5px;
    }

        .icon-btn:hover {
            color: #08224A;
        }

    .btn-post {
        background: #02B8F5;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        width: 100%;
        margin-top: 15px;
        transition: opacity 0.2s;
    }

        .btn-post:hover {
            opacity: 0.9;
        }

        .btn-post:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

    /* Admin Circular section (Old Style) */
    .admin-circular-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 20px;
    }

    .ac-card {
        background: white;
        border-radius: 14px;
        padding: 22px;
        border: 1px solid #eee;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }

    /* ======================
           RIGHT SIDEBAR (Promo)
           ====================== */
    .tc-sidebar {
        position: sticky;
        top: 100px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar-card {
        background: white;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.04);
    }

        .sidebar-card h3 {
            font-family: var(--font-heading);
            font-size: 20px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-card p {
            font-size: 13.5px;
            color: #64748B;
            line-height: 1.6;
            margin: 0 0 16px;
        }

    .sidebar-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

        .sidebar-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13.5px;
            color: #334155;
            line-height: 1.5;
        }

            .sidebar-list li i {
                color: #2ecc71;
                font-size: 14px;
                margin-top: 3px;
            }

    .sidebar-btn {
        width: 100%;
        background: #02B8F5;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

        .sidebar-btn:hover {
            background: #0099D6;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2,184,245,0.2);
        }

    .promo-card {
        background: linear-gradient(145deg, #08224A 0%, #134856 100%);
        color: white;
        border-radius: 18px;
        padding: 28px 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 34px rgba(2,184,245,0.25);
    }

        .promo-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(244,199,68,0.18) 0%, transparent 65%);
            border-radius: 50%;
        }

        .promo-card h3 {
            color: white;
            margin-bottom: 10px;
        }

        .promo-card p {
            color: #D6DED5;
            margin-bottom: 20px;
        }

    .promo-btn {
        width: 100%;
        background: #FFB300;
        color: #08224A;
        border: none;
        padding: 13px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

        .promo-btn:hover {
            background: #E6A100;
            transform: translateY(-1px);
        }

    /* ======================
           RESPONSIVE
           ====================== */
    @media (max-width: 992px) {
        .tc-layout {
            grid-template-columns: 1fr;
        }

        .tc-sidebar {
            position: static;
            order: 0; /* মোবাইলে ফিডের পরে দেখাবে */
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .promo-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 640px) {
        .fb-post-actions .apply-btn {
            flex: 1 0 100%;
            margin: 4px 8px 8px;
            padding: 12px;
        }

        .fb-post-header, .fb-post-content, .fb-post-tags { padding-left: 14px; padding-right: 14px; }
        .fb-post-stats { padding: 10px 14px; }

        .tc-page {
            padding: 26px 14px 60px;
        }

        .tc-header h1 {
            font-size: 26px;
        }

        .admin-circular-grid {
            grid-template-columns: 1fr;
        }

        .tc-sidebar {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="tc-page">
    <div class="tc-layout">

        <!-- ====================== HEADER ====================== -->
        <div class="tc-header">
            <h1>
                <!-- SVG Megaphone Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#08224A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 11 18-5v12L3 14v-3z"></path>
                    <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
                </svg>
                Tuition Circular
            </h1>
            <p>
                স্টুডেন্টরা তাদের প্রয়োজন পোস্ট করতে পারবে। টিচাররা পোস্টে Apply করতে পারবে এবং একে অপরের সাথে কমেন্টে কথা বলতে পারবে।
            </p>
        </div>

        <!-- ====================== MAIN CONTENT (LEFT) ====================== -->
        <div class="tc-main-content">

            <!-- POST CREATE BOX -->
            <?php if ($isLoggedIn): ?>
                <div class="post-create-card">
                    <div class="post-create-top">
                        <div class="pc-avatar">
                            <?php if (!is_blank($userProfileImg)): ?>
                                <img src="<?= e($userProfileImg) ?>" alt="me" />
                            <?php else: ?>

                                <?= e(mb_strlen((string)$userFullName) > 0 ? mb_strtoupper(mb_substr($userFullName, 0, 1)) : 'U') ?>
                            <?php endif; ?>
                        </div>
                        <div class="pc-input" onclick="openCircularPostModal()">
                            পোস্ট করুন, আপনার টিউশনের প্রয়োজনীয়তা...
                        </div>
                    </div>
                    <div class="post-create-actions">
                        <button class="pc-action-btn" onclick="openCircularPostModal()">
                            <i class="fas fa-images fa-lg"></i> Photo
                        </button>
                        <button class="pc-action-btn" onclick="openCircularPostModal()">
                            <i class="fas fa-video fa-lg"></i> Video
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="post-create-card" style="text-align: center; padding: 15px; background: #FFF7E0; border-color: #FFB300;">
                    <p style="margin: 0; font-size: 14px; color: #8a5a00;">
                        <i class="fas fa-info-circle me-1"></i> পোস্ট করতে এবং কমেন্ট করতে অনুগ্রহ করে <a href="/Account/Login" style="font-weight:bold; text-decoration:underline; color:#08224A;">লগইন করুন</a>।
                    </p>
                </div>
            <?php endif; ?>

            <!-- SOCIAL POST FEED -->
            <div class="post-feed" id="circularFeed">
                <?php if (empty($feedPosts)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox" style="font-size: 48px; color:#DCECF5; margin-bottom:15px; display:block;"></i>
                        <h4>কোনো পোস্ট নেই</h4>
                        <p>স্টুডেন্টরা পোস্ট করলে এখানে দেখা যাবে।</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedPosts ?? [] as $post): ?>
                        <div class="fb-post" id="circular-post-<?= e($post->Id) ?>">
                            <!-- Header -->
                            <div class="fb-post-header">
                                <div class="fb-post-user">
                                    <div class="pc-avatar">
                                        <?php if (!is_blank($post->UserProfileImage)): ?>
                                            <img src="<?= e($post->UserProfileImage) ?>" alt="<?= e($post->UserName) ?>" />
                                        <?php else: ?>

                                            <?= e(mb_strlen((string)$post->UserFullName) > 0 ? mb_strtoupper(mb_substr($post->UserFullName, 0, 1)) : 'S') ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fb-post-user-info">
                                        <h6><a href="/Profile/Show/<?= (int)$post->UserId ?>" class="fb-author-link"><?= e($post->UserFullName) ?></a></h6>
                                        <div class="time"><i class="far fa-clock"></i> <?= e(fmt_date($post->CreatedAt, 'd M Y, h:i A')) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="fb-post-content"><?= e($post->Content) ?></div>

                            <!-- Tags -->
                            <?php if (!is_blank($post->PreferredArea) || !is_blank($post->ClassLevel) || !is_blank($post->Subjects)): ?>
                                <div class="fb-post-tags">
                                    <?php if (!is_blank($post->PreferredArea)): ?>
                                        <span class="fb-tag"><i class="fas fa-map-marker-alt"></i> <?= e($post->PreferredArea) ?></span>
                                    <?php endif; ?>
                                    <?php if (!is_blank($post->ClassLevel)): ?>
                                        <span class="fb-tag"><i class="fas fa-graduation-cap"></i> <?= e($post->ClassLevel) ?></span>
                                    <?php endif; ?>
                                    <?php if (!is_blank($post->Subjects)): ?>
                                        <span class="fb-tag"><i class="fas fa-book"></i> <?= e($post->Subjects) ?></span>
                                    <?php endif; ?>
                                    <?php if (!is_blank($post->Budget)): ?>
                                        <span class="fb-tag" style="background:#d4edda; color:#155724;"><i class="fas fa-money-bill-wave"></i> ৳<?= e($post->Budget) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Image -->
                            <?php if (!is_blank($post->ImageUrl)): ?>
                                <div class="fb-post-media"><img src="<?= e($post->ImageUrl) ?>" alt="Post Image" /></div>
                            <?php endif; ?>

                            <!-- Stats -->
                            <div class="fb-post-stats">
                                <span><i class="fas fa-thumbs-up text-primary"></i> <span id="like-count-<?= e($post->Id) ?>"><?= e($post->LikeCount) ?></span> Likes</span>
                                <span id="comment-count-<?= e($post->Id) ?>"><?= e(array_sum(array_map(fn($c) => 1 + count($c->Replies ?? []), $post->Comments ?? []))) ?> Comments</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="fb-post-actions" style="flex-wrap:wrap; padding-bottom:8px;">
                                <?php if ($isLoggedIn): ?>
                                    <button class="fb-action-btn" onclick="toggleCircularLike(<?= e($post->Id) ?>)">
                                        <i class="far fa-thumbs-up" id="like-icon-<?= e($post->Id) ?>"></i> Like
                                    </button>
                                <?php else: ?>
                                    <button class="fb-action-btn" onclick="alert('লাইক করতে লগইন করুন!')">
                                        <i class="far fa-thumbs-up"></i> Like
                                    </button>
                                <?php endif; ?>
                                <button class="fb-action-btn" onclick="toggleCircularComments(<?= e($post->Id) ?>)">
                                    <i class="far fa-comment"></i> Comment
                                </button>

                                <?php if ($isLoggedIn && $isTeacher && (int)$post->UserId !== (int)$userId): ?>
                                    <?php if (isset($myApplied[$post->Id])): ?>
                                        <button class="apply-btn applied" disabled>
                                            <i class="fas fa-check-circle"></i> Applied<?= $myApplied[$post->Id] !== 'Pending' ? ' · ' . e($myApplied[$post->Id]) : '' ?>
                                        </button>
                                    <?php else: ?>
                                        <button class="apply-btn" id="apply-btn-<?= e($post->Id) ?>" onclick="applyForCircular(<?= e($post->Id) ?>)">
                                            <i class="fas fa-paper-plane"></i> Apply Now
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Comments Area -->
                            <div class="fb-comments-area" id="circular-comments-<?= e($post->Id) ?>">
                                <div class="fb-comment-list" id="circular-comment-list-<?= e($post->Id) ?>">
                                    <?php foreach ($post->Comments ?? [] as $comment): ?>
                                        <div class="fbc" id="cfbc-<?= e($comment->Id) ?>" data-id="<?= e($comment->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($comment->UserFullName) ?>">
                                            <div class="fbc-avatar">
                                                <?php if (!is_blank($comment->UserProfileImage)): ?>
                                                    <img src="<?= e($comment->UserProfileImage) ?>" alt="user" />
                                                <?php else: ?>

                                                    <?= e(mb_strlen((string)$comment->UserFullName) > 0 ? mb_strtoupper(mb_substr($comment->UserFullName, 0, 1)) : 'U') ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="fbc-main">
                                                <div class="fbc-bubble <?= e($comment->LikeCount > 0 ? 'has-likes' : '') ?>">
                                                    <span class="fbc-name"><?= e($comment->UserFullName) ?></span>
                                                    <div class="fbc-text"><?= $RenderCommentText($comment->Content) ?></div>
                                                    <span class="fbc-like-badge <?= e($comment->LikeCount > 0 ? '' : 'zero') ?>"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n"><?= e($comment->LikeCount) ?></span></span>
                                                </div>
                                                <div class="fbc-meta">
                                                    <?php if ($isLoggedIn): ?>
                                                        <button type="button" class="fbc-like-btn <?= e($comment->IsLikedByMe ? 'liked' : '') ?>" onclick="cfbcToggleLike(this, <?= e($comment->Id) ?>)">Like</button>
                                                        <button type="button" onclick="cfbcOpenReply(<?= e($post->Id) ?>, <?= e($comment->Id) ?>, <?= e($comment->Id) ?>)">Reply</button>
                                                    <?php endif; ?>
                                                    <span class="fbc-time" data-utc="<?= e($CommentIso($comment->CreatedAt)) ?>"><?= e(fmt_date($comment->CreatedAt, 'd M')) ?></span>
                                                </div>
                                                <div class="fbc-replies-wrap">
                                                    <?php if (!empty($comment->Replies)): ?>
                                                        <button type="button" class="fbc-view-replies" id="cfbc-vr-<?= e($comment->Id) ?>" onclick="cfbcShowReplies(<?= e($comment->Id) ?>)">
                                                            <i class="fas fa-share fa-flip-vertical"></i> View <?= e(count($comment->Replies ?? [])) ?> replies
                                                        </button>
                                                    <?php endif; ?>
                                                    <div class="fbc-replies" id="cfbc-replies-<?= e($comment->Id) ?>">
                                                        <?php foreach ($comment->Replies ?? [] as $reply): ?>
                                                            <div class="fbc is-reply" id="cfbc-<?= e($reply->Id) ?>" data-id="<?= e($reply->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($reply->UserFullName) ?>">
                                                                <div class="fbc-avatar">
                                                                    <?php if (!is_blank($reply->UserProfileImage)): ?>
                                                                        <img src="<?= e($reply->UserProfileImage) ?>" alt="user" />
                                                                    <?php else: ?>

                                                                        <?= e(mb_strlen((string)$reply->UserFullName) > 0 ? mb_strtoupper(mb_substr($reply->UserFullName, 0, 1)) : 'U') ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="fbc-main">
                                                                    <div class="fbc-bubble <?= e($reply->LikeCount > 0 ? 'has-likes' : '') ?>">
                                                                        <span class="fbc-name"><?= e($reply->UserFullName) ?></span>
                                                                        <div class="fbc-text"><?= $RenderCommentText($reply->Content) ?></div>
                                                                        <span class="fbc-like-badge <?= e($reply->LikeCount > 0 ? '' : 'zero') ?>"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n"><?= e($reply->LikeCount) ?></span></span>
                                                                    </div>
                                                                    <div class="fbc-meta">
                                                                        <?php if ($isLoggedIn): ?>
                                                                            <button type="button" class="fbc-like-btn <?= e($reply->IsLikedByMe ? 'liked' : '') ?>" onclick="cfbcToggleLike(this, <?= e($reply->Id) ?>)">Like</button>
                                                                            <button type="button" onclick="cfbcOpenReply(<?= e($post->Id) ?>, <?= e($comment->Id) ?>, <?= e($reply->Id) ?>)">Reply</button>
                                                                        <?php endif; ?>
                                                                        <span class="fbc-time" data-utc="<?= e($CommentIso($reply->CreatedAt)) ?>"><?= e(fmt_date($reply->CreatedAt, 'd M')) ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($isLoggedIn): ?>
                                    <div class="fbc-input-row">
                                        <div class="fbc-avatar">
                                            <?php if (!is_blank($userProfileImg)): ?>
                                                <img src="<?= e($userProfileImg) ?>" alt="me" />
                                            <?php else: ?>

                                                <?= e(mb_strlen((string)$userFullName) > 0 ? mb_strtoupper(mb_substr($userFullName, 0, 1)) : 'U') ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fbc-pill">
                                            <input type="text" id="circular-comment-input-<?= e($post->Id) ?>" placeholder="Write a comment..." maxlength="2000" onkeydown="handleCircularCommentEnter(event, <?= e($post->Id) ?>)" />
                                            <button type="button" class="fbc-send" onclick="submitCircularComment(<?= e($post->Id) ?>)" title="Send"><i class="fas fa-paper-plane"></i></button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <hr style="margin: 40px 0;" />

            <!-- ADMIN POSTS (OLD SYSTEM) -->
            <div class="list-header">
                <h3 style="font-family: var(--font-heading); font-weight:700; color:#08224A; margin-bottom:20px;"><i class="fas fa-clipboard-list"></i> Admin Circulars</h3>
            </div>

            <div class="admin-circular-grid">
                <?php if (empty($Model)): ?>
                    <p class="text-muted">কোনো অ্যাডমিন সার্কুলার নেই।</p>
                <?php else: ?>
                    <?php foreach ($Model ?? [] as $post): ?>
                        <div class="ac-card">
                            <div class="d-flex justify-content-between mb-2">
                                <?php if (!is_blank($post->TuitionCode)): ?>
                                    <span class="badge" style="background:#08224A; color:#FFB300; padding:5px 10px; border-radius:10px; font-family:monospace;"><?= e($post->TuitionCode) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 style="font-size:18px; color:#08224A; font-weight:700; font-family: var(--font-heading);"><?= e($post->Title) ?></h3>
                            <p style="font-size:13.5px; color:#64748B; margin-bottom:15px; line-height:1.6;">
                                <?php if (!is_blank($post->StudentClass)): ?>
                                    <span><strong>Class:</strong> <?= e($post->StudentClass) ?></span> <br />
                                <?php endif; ?>
                                <?php if (!is_blank($post->Subjects)): ?>
                                    <span><strong>Subjects:</strong> <?= e($post->Subjects) ?></span> <br />
                                <?php endif; ?>
                                <?php if (!is_blank($post->Area)): ?>
                                    <span><strong>Area:</strong> <?= e($post->Area) ?></span> <br />
                                <?php endif; ?>
                                <?php if (!is_blank($post->Salary)): ?>
                                    <span style="color:#155724; font-weight:700;">Salary: ৳<?= e($post->Salary) ?></span>
                                <?php endif; ?>
                            </p>
                            <?php if ($isLoggedIn): ?>
                                <a href="/Tuition/Details/<?= e($post->Id) ?>" class="btn" style="background:#02B8F5; color:white; width:100%; border-radius:8px; padding:10px; font-weight:600; margin-top:auto;">View Details</a>
                            <?php else: ?>
                                <a href="/Account/Login" class="btn" style="background:#7A8CA0; color:white; width:100%; border-radius:8px; padding:10px; font-weight:600; margin-top:auto;">Login to View</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- ====================== RIGHT SIDEBAR (PROMO) ====================== -->
        <div class="tc-sidebar">

            <!-- Promo Card -->
            <div class="promo-card">
                <h3>
                    <i class="fas fa-bullhorn" style="color:#FFB300; margin-right:8px;"></i> Need a Teacher?
                </h3>
                <p>আপনার এলাকায় বা আপনার পছন্দের শিক্ষক খুঁজে পাচ্ছেন না? আমাদের অ্যাডমিন টিম আপনাকে সাহায্য করবে। সরাসরি রিকোয়েস্ট পাঠান।</p>
                <button class="promo-btn" onclick="window.location.href='/FindTutor/Index'">
                    <i class="fas fa-search"></i> Find a Tutor
                </button>
            </div>

            <!-- How it works -->
            <div class="sidebar-card">
                <h3><i class="fas fa-lightbulb" style="color:#E09A00;"></i> How It Works</h3>
                <ul class="sidebar-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>স্টুডেন্টরা তাদের টিউশনের প্রয়োজনীয়তা এখানে পোস্ট করে।</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>টিচাররা পোস্ট দেখে Apply Now বাটনে ক্লিক করে।</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>অ্যাডমিন সব রিকোয়েস্ট যাচাই করে টিচার ও স্টুডেন্টের মধ্যে যোগাযোগ করিয়ে দেন।</span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>কোনো টাকা লেনদেন ছাড়াই নিরাপদে টিউশন শুরু হয়।</span>
                    </li>
                </ul>
            </div>

            <!-- Admin Contact -->
            <div class="sidebar-card">
                <h3><i class="fas fa-headset" style="color:#2ecc71;"></i> Admin Support</h3>
                <p>কোনো সমস্যা হলে বা জানতে চাইলে সরাসরি অ্যাডমিনের সাথে যোগাযোগ করুন।</p>
                <a href="/Helpdesk" class="sidebar-btn">
                    <i class="fas fa-comments"></i> Go to Helpdesk
                </a>
            </div>

        </div>

    </div>
</div>

<!-- ====================== APPLY MODAL (Teacher → Student) ====================== -->
<?php if ($isLoggedIn && $isTeacher): ?>
<div class="modal fade" id="applyCircularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center"><i class="fas fa-paper-plane me-1" style="color:#028EA5;"></i> এই টিউশনে Apply করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:13.5px; color:#64748B; margin-bottom:14px;">
                    <i class="fas fa-info-circle" style="color:#028EA5;"></i>
                    আপনার অ্যাপ্লিকেশন সরাসরি পোস্টদাতা স্টুডেন্টের ড্যাশবোর্ডে যাবে। আপনার প্রোফাইল ও নম্বর দেখে স্টুডেন্ট আপনার সাথে যোগাযোগ করবেন।
                </p>
                <input type="hidden" id="applyPostId" />
                <label class="form-label fw-bold" style="font-size:13px; color:#08224A;">মোবাইল নম্বর <span style="color:#e74c3c;">*</span></label>
                <input type="tel" id="applyPhone" class="form-control mb-3" maxlength="11" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e(preg_replace('/\D/', '', $myPhone)) ?>" />
                <label class="form-label fw-bold" style="font-size:13px; color:#08224A;">স্টুডেন্টকে কিছু বলুন <span style="color:#7A8CA0; font-weight:400;">(ঐচ্ছিক)</span></label>
                <textarea id="applyMessage" class="form-control" rows="4" maxlength="1000" placeholder="আপনার অভিজ্ঞতা, কোন সময়ে পড়াতে পারবেন, বেতন ইত্যাদি..."></textarea>
                <button class="btn-post" id="applySubmitBtn" onclick="submitCircularApply()"><i class="fas fa-paper-plane me-1"></i> Send Application</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ====================== CREATE POST MODAL (FindTutor Style) ====================== -->
<div class="modal fade" id="createCircularPostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">পোস্ট তৈরি করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- User Info -->
                <div class="d-flex gap-2 align-items-center mb-3">
                    <div class="pc-avatar">
                        <?php if (!is_blank($userProfileImg)): ?>
                            <img src="<?= e($userProfileImg) ?>" alt="me" />
                        <?php else: ?>

                            <?= e(mb_strlen((string)$userFullName) > 0 ? mb_strtoupper(mb_substr($userFullName, 0, 1)) : 'U') ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= e($userFullName) ?></h6>
                        <span class="badge bg-success" style="font-size:10px;"><?= e($isTeacher ? 'Teacher' : 'Student') ?></span>
                    </div>
                </div>

                <textarea id="circularPostContent" class="modal-textarea" placeholder="আপনার টিউশনের প্রয়োজনীয়তা লিখুন..." oninput="checkCircularPostBtn()"></textarea>

                <div class="row g-2 mt-2">
                    <div class="col-6"><input type="text" id="cpArea" class="form-control" placeholder="এলাকা (Area)" style="font-size:14px;" /></div>
                    <div class="col-6"><input type="text" id="cpClass" class="form-control" placeholder="ক্লাস (Class)" style="font-size:14px;" /></div>
                    <div class="col-6"><input type="text" id="cpSubjects" class="form-control" placeholder="বিষয় (Subjects)" style="font-size:14px;" /></div>
                    <div class="col-6"><input type="text" id="cpBudget" class="form-control" placeholder="বাজেট (Budget ৳)" style="font-size:14px;" /></div>
                </div>

                <div class="modal-preview" id="cpImageBox">
                    <button type="button" class="remove-preview" onclick="removeCpImage()"><i class="fas fa-times"></i></button>
                    <img id="cpImagePreview" src="" alt="Preview" />
                </div>

                <div class="modal-actions-bottom mt-3">
                    <span style="font-size:14px; font-weight:600; color:#08224A;">Add to your post</span>
                    <div>
                        <label for="cpImageInput" class="icon-btn" style="cursor:pointer; margin:0;">
                            <i class="fas fa-images" style="color:#2ecc71;"></i>
                        </label>
                        <input type="file" id="cpImageInput" accept="image/*" style="display:none;" onchange="previewCpImage(this)" />

                        <button class="icon-btn" type="button"><i class="fas fa-smile" style="color:#f39c12;"></i></button>
                    </div>
                </div>

                <button class="btn-post" id="submitCircularPostBtn" onclick="submitCircularPost()" disabled>Post</button>
            </div>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
    <script>
        // ============================================================
        // POST SYSTEM JS
        // ============================================================
        let cpImageUrl = null;

        function openCircularPostModal() {
            document.getElementById('circularPostContent').value = '';
            document.getElementById('cpArea').value = '';
            document.getElementById('cpClass').value = '';
            document.getElementById('cpSubjects').value = '';
            document.getElementById('cpBudget').value = '';
            removeCpImage();
            checkCircularPostBtn();
            new bootstrap.Modal(document.getElementById('createCircularPostModal')).show();
        }

        function checkCircularPostBtn() {
            var content = document.getElementById('circularPostContent').value.trim();
            var btn = document.getElementById('submitCircularPostBtn');
            btn.disabled = (content.length === 0 && !cpImageUrl);
        }

        function previewCpImage(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                if (file.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); return; }
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('cpImagePreview').src = e.target.result;
                    document.getElementById('cpImageBox').style.display = 'block';
                    checkCircularPostBtn();
                }
                reader.readAsDataURL(file);
            }
        }

        function removeCpImage() {
            document.getElementById('cpImageInput').value = '';
            document.getElementById('cpImageBox').style.display = 'none';
            document.getElementById('cpImagePreview').src = '';
            checkCircularPostBtn();
        }

        async function submitCircularPost() {
            var content = document.getElementById('circularPostContent').value.trim();
            var area = document.getElementById('cpArea').value.trim();
            var classLevel = document.getElementById('cpClass').value.trim();
            var subjects = document.getElementById('cpSubjects').value.trim();
            var budget = document.getElementById('cpBudget').value.trim();
            var btn = document.getElementById('submitCircularPostBtn');

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            btn.disabled = true;

            var formData = new FormData();
            formData.append('content', content);
            formData.append('preferredArea', area);
            formData.append('classLevel', classLevel);
            formData.append('subjects', subjects);
            formData.append('budget', budget);

            var fileInput = document.getElementById('cpImageInput');
            if (fileInput.files.length > 0) { formData.append('imageFile', fileInput.files[0]); }

            var token = document.querySelector('input[name="__RequestVerificationToken"]').value;
            formData.append('__RequestVerificationToken', token);

            try {
                var res = await fetch('/Tuition/CreateCircularPost', { method: 'POST', body: formData });
                var data = await res.json();
                if (data.success) { location.reload(); }
                else { alert(data.message || 'Failed to post.'); btn.innerHTML = 'Post'; btn.disabled = false; }
            } catch (err) {
                alert('Error: ' + err.message); btn.innerHTML = 'Post'; btn.disabled = false;
            }
        }

        // ============================================================
        // COMMENTS & LIKES JS
        // ============================================================
        window.cfbcMe = <?= js(['name' => $userFullName, 'img' => $userProfileImg ?? '']) ?>;

        function toggleCircularComments(postId) {
            var area = document.getElementById('circular-comments-' + postId);
            if (area.classList.contains('show')) { area.classList.remove('show'); }
            else {
                area.classList.add('show');
                var inp = document.getElementById('circular-comment-input-' + postId);
                if (inp) inp.focus();
            }
        }

        function handleCircularCommentEnter(e, postId) {
            if (e.key === 'Enter') { e.preventDefault(); submitCircularComment(postId); }
        }

        function cfbcEsc(t) {
            return String(t == null ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
        function cfbcToken() { return document.querySelector('input[name="__RequestVerificationToken"]').value; }
        function cfbcAvatarHtml(img, name) {
            if (img) return '<img src="' + cfbcEsc(img) + '" alt="user" />';
            return cfbcEsc((name || 'U').charAt(0).toUpperCase());
        }
        function cfbcRenderText(content) {
            var s = cfbcEsc(content);
            var m = s.match(/^@\[([^\]]+)\]\s?/);
            if (m) return '<span class="fbc-mention">' + m[1] + '</span> ' + s.slice(m[0].length);
            return s;
        }
        function cfbcBuild(c, isReply, postId, rootId) {
            var liked = c.isLiked ? ' liked' : '';
            var zero = (c.likeCount > 0) ? '' : ' zero';
            var h = ''
                + '<div class="fbc' + (isReply ? ' is-reply' : '') + '" id="cfbc-' + c.id + '" data-id="' + c.id + '" data-root="' + rootId + '" data-name="' + cfbcEsc(c.userFullName) + '">'
                + '  <div class="fbc-avatar">' + cfbcAvatarHtml(c.userProfileImage, c.userFullName) + '</div>'
                + '  <div class="fbc-main">'
                + '    <div class="fbc-bubble' + (c.likeCount > 0 ? ' has-likes' : '') + '">'
                + '      <span class="fbc-name">' + cfbcEsc(c.userFullName) + '</span>'
                + '      <div class="fbc-text">' + cfbcRenderText(c.content) + '</div>'
                + '      <span class="fbc-like-badge' + zero + '"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n">' + (c.likeCount || 0) + '</span></span>'
                + '    </div>'
                + '    <div class="fbc-meta">'
                + '      <button type="button" class="fbc-like-btn' + liked + '" onclick="cfbcToggleLike(this, ' + c.id + ')">Like</button>'
                + '      <button type="button" onclick="cfbcOpenReply(' + postId + ', ' + rootId + ', ' + c.id + ')">Reply</button>'
                + '      <span class="fbc-time" data-utc="' + cfbcEsc(c.createdAtUtc) + '">Just now</span>'
                + '    </div>';
            if (!isReply) { h += '    <div class="fbc-replies-wrap"><div class="fbc-replies" id="cfbc-replies-' + c.id + '"></div></div>'; }
            h += '  </div></div>';
            return h;
        }

        function cfbcBumpCount(postId) {
            var el = document.getElementById('comment-count-' + postId);
            if (!el) return;
            var n = parseInt(el.textContent, 10) || 0;
            el.textContent = (n + 1) + ' Comments';
        }

        async function cfbcSend(postId, content, parentId) {
            var fd = new FormData();
            fd.append('postId', postId);
            fd.append('content', content);
            if (parentId) fd.append('parentId', parentId);
            fd.append('__RequestVerificationToken', cfbcToken());
            var res = await fetch('/Tuition/AddCircularComment', { method: 'POST', body: fd });
            return await res.json();
        }

        window.submitCircularComment = async function (postId) {
            var input = document.getElementById('circular-comment-input-' + postId);
            var content = input.value.trim();
            if (!content) return;

            input.disabled = true;
            try {
                var data = await cfbcSend(postId, content, null);
                if (data.success) {
                    var list = document.getElementById('circular-comment-list-' + postId);
                    list.insertAdjacentHTML('beforeend', cfbcBuild(data.comment, false, postId, data.comment.id));
                    input.value = '';
                    cfbcBumpCount(postId);
                } else { alert(data.message || 'Failed to comment.'); }
            } catch (err) { alert('Error: ' + err.message); }
            input.disabled = false;
            input.focus();
        };

        window.cfbcShowReplies = function (rootId) {
            var list = document.getElementById('cfbc-replies-' + rootId);
            if (list) list.style.display = 'flex';
            var vr = document.getElementById('cfbc-vr-' + rootId);
            if (vr) vr.style.display = 'none';
        };

        window.cfbcCloseReplyBoxes = function (postId) {
            document.querySelectorAll('#circular-post-' + postId + ' .fbc-replybox').forEach(function (b) { b.remove(); });
        };

        window.cfbcOpenReply = function (postId, rootId, targetId) {
            var target = document.getElementById('cfbc-' + targetId);
            if (!target) return;
            var targetName = target.getAttribute('data-name') || '';
            var isNested = (targetId !== rootId);

            cfbcCloseReplyBoxes(postId);
            cfbcShowReplies(rootId);

            var box = document.createElement('div');
            box.className = 'fbc-input-row fbc-replybox';
            box.innerHTML = ''
                + '<div class="fbc-avatar">' + cfbcAvatarHtml(window.cfbcMe.img, window.cfbcMe.name) + '</div>'
                + '<div class="fbc-pill">'
                + (isNested ? '<span class="fbc-mention-tag">' + cfbcEsc(targetName) + '</span>' : '')
                + '  <input type="text" maxlength="2000" placeholder="Reply..." />'
                + '  <button type="button" class="fbc-send" title="Send"><i class="fas fa-paper-plane"></i></button>'
                + '</div>';

            var list = document.getElementById('cfbc-replies-' + rootId);
            if (isNested) target.insertAdjacentElement('afterend', box);
            else list.appendChild(box);

            var input = box.querySelector('input');
            var send = box.querySelector('.fbc-send');

            async function doSend() {
                var text = input.value.trim();
                if (!text) return;
                var content = (isNested ? '@[' + targetName + '] ' : '') + text;
                input.disabled = true; send.disabled = true;
                try {
                    var data = await cfbcSend(postId, content, targetId);
                    if (data.success) {
                        box.insertAdjacentHTML('beforebegin', cfbcBuild(data.comment, true, postId, rootId));
                        box.remove();
                        cfbcBumpCount(postId);
                        return;
                    }
                    alert(data.message || 'Failed to reply.');
                } catch (err) { alert('Error: ' + err.message); }
                input.disabled = false; send.disabled = false; input.focus();
            }

            send.addEventListener('click', doSend);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); doSend(); }
                else if (e.key === 'Escape') { box.remove(); }
            });
            input.focus();
        };

        window.cfbcToggleLike = async function (btn, commentId) {
            var fd = new FormData();
            fd.append('commentId', commentId);
            fd.append('__RequestVerificationToken', cfbcToken());
            btn.disabled = true;
            try {
                var res = await fetch('/Tuition/ToggleCircularCommentLike', { method: 'POST', body: fd });
                var data = await res.json();
                if (data.success) {
                    btn.classList.toggle('liked', data.isLiked);
                    var el = document.getElementById('cfbc-' + commentId);
                    var bubble = el.querySelector('.fbc-bubble');
                    var badge = bubble.querySelector('.fbc-like-badge');
                    badge.querySelector('.fbc-like-n').textContent = data.count;
                    badge.classList.toggle('zero', data.count < 1);
                    bubble.classList.toggle('has-likes', data.count > 0);
                } else { alert(data.message || 'Failed.'); }
            } catch (err) { alert('Error: ' + err.message); }
            btn.disabled = false;
        };

        window.toggleCircularLike = async function (postId) {
            var token = document.querySelector('input[name="__RequestVerificationToken"]').value;
            var formData = new FormData();
            formData.append('postId', postId);
            formData.append('__RequestVerificationToken', token);

            try {
                var res = await fetch('/Tuition/ToggleCircularLike', { method: 'POST', body: formData });
                var data = await res.json();
                if (data.success) {
                    var countEl = document.getElementById('like-count-' + postId);
                    var iconEl = document.getElementById('like-icon-' + postId);
                    countEl.innerText = data.count;
                    if (data.isLiked) {
                        iconEl.className = 'fas fa-thumbs-up';
                        iconEl.style.color = '#1877F2';
                    } else {
                        iconEl.className = 'far fa-thumbs-up';
                        iconEl.style.color = '#08224A';
                    }
                }
            } catch (err) { console.log(err); }
        };

        // Apply Now → অ্যাপ্লিকেশন সরাসরি পোস্টদাতা স্টুডেন্টের ড্যাশবোর্ডে যায়
        window.applyForCircular = function (postId) {
            document.getElementById('applyPostId').value = postId;
            document.getElementById('applyMessage').value = '';
            new bootstrap.Modal(document.getElementById('applyCircularModal')).show();
        };

        window.submitCircularApply = async function () {
            var postId = document.getElementById('applyPostId').value;
            var phone = document.getElementById('applyPhone').value.replace(/\D/g, '');
            var message = document.getElementById('applyMessage').value.trim();
            if (!/^01[3-9]\d{8}$/.test(phone)) { alert('সঠিক মোবাইল নম্বর দিন (যেমন 01712345678)'); return; }

            var btn = document.getElementById('applySubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            var fd = new FormData();
            fd.append('postId', postId);
            fd.append('phone', phone);
            fd.append('message', message);
            fd.append('__RequestVerificationToken', cfbcToken());
            try {
                var res = await fetch('/Tuition/ApplyCircular', { method: 'POST', body: fd });
                var data = await res.json();
                alert(data.message || (data.success ? 'Applied!' : 'Failed.'));
                if (data.success || /আগেই/.test(data.message || '')) {
                    bootstrap.Modal.getInstance(document.getElementById('applyCircularModal')).hide();
                    var b = document.getElementById('apply-btn-' + postId);
                    if (b) { b.classList.add('applied'); b.disabled = true; b.innerHTML = '<i class="fas fa-check-circle"></i> Applied'; }
                }
            } catch (err) { alert('Error: ' + err.message); }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Application';
        };
    </script>
<?php section_end(); ?>
