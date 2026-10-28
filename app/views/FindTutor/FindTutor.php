<?php
$ViewData['Title'] = 'Find Tutor - Smart Tutor Hub';

$isLoggedIn = session_get('UserName') !== null;
$userRole = session_get('UserRole');
$isTeacher = $userRole === 'Teacher';
$userId = session_get('UserId');
$userFullName = session_get('UserFullName') ?? session_get('UserName') ?? 'User';
$userProfileImg = session_get('ProfileImageLink');

$posts = [];
try {
    $posts = db()->GetAllTeachWantedPosts($userId);
} catch (Throwable $ex) { error_log($ex->getMessage()); }

// নামের প্রথম অক্ষর (বড় হাতের), খালি হলে ডিফল্ট
$firstUpper = fn($s, string $d) => mb_strlen((string)$s) > 0 ? mb_strtoupper(mb_substr((string)$s, 0, 1)) : $d;

// "@[Name] text" ফরম্যাটের মেনশনকে নীল রঙে দেখায়, বাকি সব HTML-encode করে
$RenderCommentText = function (?string $text): string {
    $text = $text ?? '';
    if (preg_match('/^@\[([^\]]+)\]\s?/u', $text, $m)) {
        $rest = substr($text, strlen($m[0]));
        return '<span class="fbc-mention">' . e($m[1]) . '</span> ' . e($rest);
    }
    return e($text);
};

// C# এ CreatedAt UTC ছিল; PHP DB তে লোকাল টাইম (config timezone) সেভ হয় — তাই UTC তে কনভার্ট করে "Z" দেওয়া হলো
$CommentIso = function ($dt): string {
    $d = to_dt($dt);
    if (!$d) return '';
    return (clone $d)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s') . 'Z';
};
?>
<?= csrf_field() ?>

<style>
    /* ======================
                               PAGE WRAPPER
                               ====================== */
    .ft-page {
        background: #F5FBFF;
        padding: 40px 20px 70px;
        min-height: 80vh;
    }

    .ft-layout {
        max-width: 1280px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 30px;
        align-items: start;
    }

    .ft-header {
        text-align: center;
        margin-bottom: 30px;
        grid-column: 1 / -1;
    }

        .ft-header h1 {
            font-family: var(--font-heading);
            font-size: 36px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 10px;
        }

        .ft-header p {
            color: #475569;
            font-size: 15.5px;
            margin: 0 auto;
            max-width: 640px;
            line-height: 1.6;
        }

    /* ====== POST CREATE BOX ====== */
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

    /* ====== POST FEED ====== */
    .post-feed {
        display: flex;
        flex-direction: column;
        gap: 20px;
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

    /* ====== POST MENU (Edit / Delete) ====== */
    .fb-post-menu {
        position: relative;
    }

    .fb-post-menu-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: #64748B;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

        .fb-post-menu-btn:hover {
            background: #F5FBFF;
        }

    .fb-post-menu-list {
        display: none;
        position: absolute;
        right: 0;
        top: 40px;
        min-width: 170px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 18px rgba(0,0,0,.18);
        padding: 6px;
        z-index: 20;
    }

        .fb-post-menu-list.show {
            display: block;
        }

        .fb-post-menu-list button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            background: none;
            border: none;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 14px;
            font-weight: 600;
            color: #08224A;
            cursor: pointer;
            text-align: left;
        }

            .fb-post-menu-list button:hover {
                background: #F5FBFF;
            }

            .fb-post-menu-list button.danger {
                color: #d93025;
            }

            .fb-post-menu-list button i {
                width: 16px;
                text-align: center;
            }

    /* ====== COMMENTS SECTION (Facebook style) ====== */
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

    /* Reply input + main input (pill) */
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

    .fbc-replybox-hint {
        font-size: 12px;
        color: #65676b;
        padding: 2px 0 0 36px;
    }

        .fbc-replybox-hint a {
            color: #1877F2;
            cursor: pointer;
        }

    /* ====== MODAL ====== */
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

    /* ======================
                                   LIST HEADER & SORT
                                   ====================== */
    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 10px;
    }

        .list-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #08224A;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .list-header .count-badge {
            background: #02B8F5;
            color: white;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .list-header .sort-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
        }

            .list-header .sort-wrap select {
                border: 1.5px solid #DCECF5;
                background: white;
                padding: 7px 12px;
                border-radius: 8px;
                font-size: 13px;
                color: #08224A;
                outline: none;
                font-weight: 500;
                cursor: pointer;
            }

    .tutor-row {
        background: white;
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 16px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s;
        display: grid;
        grid-template-columns: 90px 1fr auto;
        gap: 20px;
        align-items: center;
    }

        .tutor-row:hover {
            transform: translateX(3px);
            box-shadow: 0 8px 28px rgba(2,184,245,0.1);
            border-color: rgba(2,184,245,0.15);
        }

    .tr-avatar {
        position: relative;
        width: 90px;
        height: 90px;
    }

        .tr-avatar .avatar-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #08224A, #134856);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 700;
            border: 3px solid #F5FBFF;
            box-shadow: 0 4px 14px rgba(2,184,245,0.2);
            overflow: hidden;
        }

            .tr-avatar .avatar-circle img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

        .tr-avatar .v-tick {
            position: absolute;
            bottom: 3px;
            right: 3px;
            background: #2ecc71;
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 3px solid white;
        }

    .tr-info {
        min-width: 0;
    }

    .tr-name-line {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }

    .tr-name {
        font-size: 19px;
        font-weight: 700;
        color: #08224A;
        margin: 0;
    }

    .tr-id {
        font-family: 'Courier New', monospace;
        background: #F5FBFF;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        letter-spacing: 0.4px;
    }

    .tr-top-badge {
        background: linear-gradient(135deg, #E09A00, #B8760F);
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .tr-rating-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 13px;
    }

        .tr-rating-line .stars {
            color: #E09A00;
            font-weight: 600;
        }

        .tr-rating-line .count {
            color: #7A8CA0;
            font-size: 12.5px;
        }

    .tr-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px 18px;
        font-size: 13px;
        color: #475569;
        margin-bottom: 10px;
    }

        .tr-meta .m-item {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
        }

            .tr-meta .m-item i {
                color: #028EA5;
                font-size: 12px;
                width: 14px;
                text-align: center;
                flex-shrink: 0;
            }

            .tr-meta .m-item span {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

    .tr-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .tr-tag {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 20px;
        background: #F5FBFF;
        color: #08224A;
        font-weight: 500;
    }

        .tr-tag.mode {
            background: #d1ecf1;
            color: #0c6b7c;
        }

    .tr-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
        min-width: 150px;
    }

    .tr-fee {
        text-align: right;
        margin-bottom: 4px;
    }

        .tr-fee .fee-val {
            font-size: 19px;
            font-weight: 800;
            color: #08224A;
            line-height: 1;
        }

        .tr-fee .fee-lbl {
            font-size: 10.5px;
            color: #7A8CA0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
        }

    .btn-view-tr {
        background: white;
        color: #08224A;
        border: 1.5px solid #02B8F5;
        padding: 9px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

        .btn-view-tr:hover {
            background: #F5FBFF;
        }

    .btn-request-tr {
        background: #02B8F5;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: opacity 0.25s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

        .btn-request-tr:hover {
            opacity: 0.88;
        }

    .ft-sidebar {
        position: sticky;
        top: 100px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .sidebar-card {
        background: white;
        border-radius: 18px;
        padding: 26px 24px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    .area-tuition-card {
        background: linear-gradient(145deg, #08224A 0%, #134856 100%);
        color: white;
        border-radius: 18px;
        padding: 28px 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 34px rgba(2,184,245,0.25);
    }

        .area-tuition-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(244,199,68,0.18) 0%, transparent 65%);
            border-radius: 50%;
        }

        .area-tuition-card .atc-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: rgba(244,199,68,0.15);
            color: #FFB300;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
            position: relative;
        }

        .area-tuition-card h3 {
            font-family: var(--font-heading);
            font-size: 22px;
            font-weight: 700;
            color: white;
            margin: 0 0 10px;
            line-height: 1.25;
            position: relative;
        }

        .area-tuition-card p {
            font-size: 13.5px;
            line-height: 1.65;
            margin: 0 0 18px;
            color: #D6DED5;
            position: relative;
        }

        .area-tuition-card .atc-features {
            list-style: none;
            padding: 0;
            margin: 0 0 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
        }

            .area-tuition-card .atc-features li {
                display: flex;
                align-items: flex-start;
                gap: 9px;
                font-size: 13px;
                color: #E8EDE7;
                line-height: 1.5;
            }

                .area-tuition-card .atc-features li i {
                    color: #FFB300;
                    font-size: 13px;
                    margin-top: 3px;
                    flex-shrink: 0;
                }

    .btn-area-contact {
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
        position: relative;
    }

        .btn-area-contact:hover {
            background: #E6A100;
            transform: translateY(-1px);
        }

    .helper-card h4 {
        font-family: var(--font-heading);
        font-size: 18px;
        color: #08224A;
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

        .helper-card h4 i {
            color: #E09A00;
            font-size: 17px;
        }

    .helper-card p {
        font-size: 13.5px;
        color: #64748B;
        line-height: 1.6;
        margin: 0 0 16px;
    }

    .btn-helper {
        width: 100%;
        background: white;
        color: #08224A;
        border: 1.5px solid #02B8F5;
        padding: 11px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13.5px;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

        .btn-helper:hover {
            background: #02B8F5;
            color: white;
        }

    .tid-card {
        background: #F5FBFF;
        border-radius: 16px;
        padding: 20px 22px;
        border: 1.5px dashed #B9D6E6;
    }

        .tid-card h5 {
            font-size: 14.5px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

            .tid-card h5 i {
                color: #028EA5;
            }

        .tid-card p {
            font-size: 12.5px;
            color: #64748B;
            margin: 0 0 12px;
            line-height: 1.5;
        }

        .tid-card .tid-input {
            display: flex;
            gap: 6px;
        }

        .tid-card input {
            flex: 1;
            border: 1.5px solid #DCECF5;
            background: white;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.3px;
            min-width: 0;
        }

            .tid-card input:focus {
                border-color: #02B8F5;
            }

        .tid-card button {
            background: #02B8F5;
            color: white;
            border: none;
            padding: 9px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12.5px;
            cursor: pointer;
            transition: opacity 0.25s;
            flex-shrink: 0;
        }

            .tid-card button:hover {
                opacity: 0.88;
            }

    .empty-state {
        background: white;
        border-radius: 16px;
        padding: 50px 26px;
        text-align: center;
        border: 1px solid rgba(0,0,0,0.05);
    }

        .empty-state .es-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #F5FBFF;
            color: #7A8CA0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 14px;
        }

        .empty-state h4 {
            font-size: 17px;
            font-weight: 700;
            color: #08224A;
            margin: 0 0 6px;
        }

        .empty-state p {
            color: #64748B;
            font-size: 13.5px;
            margin: 0 0 18px;
        }

    .ft-modal .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(0,0,0,0.2);
    }

    .ft-modal .modal-header {
        background: #134856;
        color: white;
        border: none;
        padding: 20px 26px;
    }

        .ft-modal .modal-header .modal-title {
            font-size: 17px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ft-modal .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.9;
        }

    .ft-modal .modal-body {
        padding: 26px;
        max-height: 72vh;
        overflow-y: auto;
        background: #fff;
    }

    .tp-header {
        display: flex;
        gap: 20px;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
        margin-bottom: 22px;
    }

    .tp-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, #08224A, #134856);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 30px;
        flex-shrink: 0;
        position: relative;
        border: 3px solid white;
        box-shadow: 0 6px 20px rgba(2,184,245,0.2);
        overflow: hidden;
    }

        .tp-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tp-avatar .v-tick {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #2ecc71;
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 3px solid white;
        }

    .tp-name {
        font-size: 22px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tp-id {
        display: inline-block;
        background: #F5FBFF;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .tp-rating-line {
        color: #E09A00;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

        .tp-rating-line .count {
            color: #7A8CA0;
            font-size: 12.5px;
            font-weight: 400;
        }

    .tp-stat-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 22px;
    }

    .tp-stat {
        background: #F5FBFF;
        border-radius: 12px;
        padding: 14px;
        text-align: center;
    }

        .tp-stat .num {
            font-size: 20px;
            font-weight: 800;
            color: #08224A;
            line-height: 1.2;
        }

        .tp-stat .lbl {
            font-size: 11px;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
            margin-top: 3px;
        }

    .tp-block {
        margin-bottom: 22px;
    }

    .tp-block-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #08224A;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

        .tp-block-title i {
            color: #028EA5;
            font-size: 13px;
        }

    .tp-block p {
        font-size: 14px;
        color: #334155;
        line-height: 1.7;
        margin: 0;
    }

    .tp-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .tp-info-item {
        background: #F5FBFF;
        border-radius: 10px;
        padding: 12px 14px;
    }

        .tp-info-item .label {
            font-size: 10.5px;
            text-transform: uppercase;
            color: #7A8CA0;
            letter-spacing: 0.4px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .tp-info-item .value {
            font-size: 13.5px;
            font-weight: 600;
            color: #08224A;
        }

    .tp-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .tp-chip {
        font-size: 12px;
        padding: 5px 12px;
        border-radius: 20px;
        background: #F5FBFF;
        color: #08224A;
        font-weight: 500;
    }

    .tp-cta {
        background: #F5FBFF;
        border-radius: 12px;
        padding: 18px;
        text-align: center;
        border: 1.5px solid #DCECF5;
        margin-top: 22px;
    }

        .tp-cta p {
            font-size: 13px;
            color: #64748B;
            margin: 0 0 12px;
            line-height: 1.5;
        }

    .ft-form .form-intro {
        background: #F5FBFF;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 22px;
        border-left: 3px solid #02B8F5;
    }

        .ft-form .form-intro p {
            font-size: 13px;
            color: #475569;
            margin: 0;
            line-height: 1.6;
        }

        .ft-form .form-intro strong {
            color: #08224A;
        }

    .ft-form .form-section-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #7A8CA0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin: 22px 0 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

        .ft-form .form-section-label:first-child {
            margin-top: 0;
        }

        .ft-form .form-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eee;
        }

    .ft-form .field {
        margin-bottom: 14px;
    }

        .ft-form .field label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #08224A;
            margin-bottom: 6px;
        }

            .ft-form .field label .req {
                color: #e74c3c;
            }

        .ft-form .field input,
        .ft-form .field select,
        .ft-form .field textarea {
            width: 100%;
            border-radius: 10px;
            border: 1.5px solid #DCECF5;
            padding: 11px 14px;
            font-size: 14px;
            background: #F5FBFF;
            outline: none;
            transition: all 0.25s;
            font-family: inherit;
            color: #08224A;
        }

            .ft-form .field input:focus,
            .ft-form .field select:focus,
            .ft-form .field textarea:focus {
                border-color: #02B8F5;
                background: white;
                box-shadow: 0 0 0 3px rgba(2,184,245,0.08);
            }

        .ft-form .field textarea {
            resize: vertical;
            min-height: 78px;
        }

    .ft-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .ft-form .btn-submit {
        width: 100%;
        background: #02B8F5;
        color: white;
        border: none;
        padding: 15px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: opacity 0.25s;
        margin-top: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
    }

        .ft-form .btn-submit:hover {
            opacity: 0.88;
        }

        .ft-form .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

    /* ===== WhatsApp Number Field ===== */
    .whatsapp-icon-input {
        position: relative;
    }

        .whatsapp-icon-input .wa-icon {
            position: absolute;
            left: 14px;
            top: 38px;
            color: #25D366;
            font-size: 16px;
            pointer-events: none;
            z-index: 2;
        }

        .whatsapp-icon-input input {
            padding-left: 42px !important;
        }

    .whatsapp-same-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
        font-size: 12px;
        color: #64748B;
        cursor: pointer;
    }

        .whatsapp-same-toggle input[type="checkbox"] {
            width: auto;
            transform: scale(1.15);
            accent-color: #08224A;
            cursor: pointer;
        }

        .whatsapp-same-toggle label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
        }

    .success-panel {
        text-align: center;
        padding: 20px 10px;
    }

        .success-panel .success-icon {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: #d4edda;
            color: #2ecc71;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 18px;
            animation: successPop 0.5s ease;
        }

    @keyframes successPop {
        0% {
            transform: scale(0.6);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .success-panel h4 {
        font-size: 20px;
        font-weight: 700;
        color: #08224A;
        margin: 0 0 10px;
    }

    .success-panel p {
        font-size: 14px;
        color: #64748B;
        line-height: 1.65;
        margin: 0 auto 22px;
        max-width: 420px;
    }

    .success-req-id {
        background: #F5FBFF;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: inline-block;
    }

        .success-req-id .req-label {
            font-size: 11px;
            color: #7A8CA0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }

        .success-req-id .req-value {
            font-size: 18px;
            font-weight: 800;
            color: #08224A;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

    .success-panel .btn-ok {
        background: #02B8F5;
        color: white;
        border: none;
        padding: 12px 42px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: opacity 0.25s;
    }

        .success-panel .btn-ok:hover {
            opacity: 0.88;
        }

    @media (max-width: 992px) {
        .ft-layout {
            grid-template-columns: 1fr;
        }

        .ft-sidebar {
            position: static;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .area-tuition-card {
            grid-column: 1 / -1;
        }

        .tutor-row {
            grid-template-columns: 80px 1fr;
        }

            .tutor-row .tr-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                align-items: center;
            }

        .tr-fee {
            text-align: left;
            flex: 1;
        }

        .tr-actions .btn-view-tr,
        .tr-actions .btn-request-tr {
            flex: 1;
        }
    }

    @media (max-width: 640px) {
        .ft-page {
            padding: 26px 14px 60px;
        }

        .ft-header h1 {
            font-size: 26px;
        }

        .ft-header p {
            font-size: 14px;
        }

        .ft-sidebar {
            grid-template-columns: 1fr;
        }

        .tutor-row {
            grid-template-columns: 1fr;
            text-align: center;
            padding: 20px;
        }

        .tr-avatar {
            margin: 0 auto;
        }

        .tr-name-line {
            justify-content: center;
        }

        .tr-rating-line {
            justify-content: center;
        }

        .tr-meta {
            grid-template-columns: 1fr;
            text-align: left;
        }

        .tr-tags {
            justify-content: center;
        }

        .tr-actions {
            flex-direction: column;
        }

        .list-header {
            flex-direction: column;
            align-items: stretch;
        }

            .list-header .sort-wrap {
                justify-content: flex-end;
            }

        .tp-header {
            flex-direction: column;
            text-align: center;
        }

        .tp-stat-row, .tp-info-grid {
            grid-template-columns: 1fr;
        }

        .ft-form .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="ft-page">
    <div class="ft-layout">

        <div class="ft-header">
            <h1>Find Your Perfect Tutor</h1>
            <p>
                Browse verified tutors — name, qualification, experience, and skills at a glance.
                All communication goes through our admin team to keep your privacy safe.
            </p>
        </div>

        <div class="ft-list-wrap">

            <!-- ========================================== -->
            <!-- CREATE POST BOX (Only for Teachers) -->
            <!-- ========================================== -->
            <?php if ($isLoggedIn && $isTeacher): ?>
                <div class="post-create-card">
                    <div class="post-create-top">
                        <div class="pc-avatar">
                            <?php if (!is_blank($userProfileImg)): ?>
                                <img src="<?= e($userProfileImg) ?>" alt="me" />
                            <?php else: ?>
                                <?= e($firstUpper($userFullName, 'T')) ?>
                            <?php endif; ?>
                        </div>
                        <div class="pc-input" onclick="openPostModal()">
                            পোস্ট করুন, আপনি কি পড়াতে চান?
                        </div>
                    </div>
                    <div class="post-create-actions">
                        <button class="pc-action-btn" onclick="openPostModal()">
                            <i class="fas fa-images fa-lg"></i> Photo
                        </button>
                        <button class="pc-action-btn" onclick="openPostModal()">
                            <i class="fas fa-video fa-lg"></i> Video
                        </button>
                    </div>
                </div>
            <?php elseif ($isLoggedIn && !$isTeacher): ?>
                <div class="post-create-card" style="text-align: center; padding: 15px; background: #FFF7E0; border-color: #FFB300;">
                    <p style="margin: 0; font-size: 14px; color: #8a5a00;">
                        <i class="fas fa-info-circle me-1"></i> শুধুমাত্র টিচার (Teacher) রা এখানে পোস্ট করতে পারবেন। আপনি টিচার হতে <a href="/Teacher/RequestForm" style="font-weight:bold; text-decoration:underline; color:#08224A;">এখানে ক্লিক করুন</a>।
                    </p>
                </div>
            <?php else: ?>
                <div class="post-create-card" style="text-align: center; padding: 15px; background: #FFF7E0; border-color: #FFB300;">
                    <p style="margin: 0; font-size: 14px; color: #8a5a00;">
                        <i class="fas fa-info-circle me-1"></i> পোস্ট করতে অনুগ্রহ করে <a href="/Account/Login" style="font-weight:bold; text-decoration:underline; color:#08224A;">লগইন করুন</a>।
                    </p>
                </div>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- POST FEED (Teach Wanted) -->
            <!-- ========================================== -->
            <div class="list-header" style="margin-top: 30px;">
                <h3>
                    <i class="fas fa-chalkboard-teacher"></i> পড়াতে চাই (Teach Wanted)
                    <span class="count-badge" id="postCountBadge"><?= count($posts) ?></span>
                </h3>
            </div>

            <div class="post-feed" id="postFeed">
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <div class="es-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h4>কোনো পোস্ট পাওয়া যায়নি</h4>
                        <p>বর্তমানে কোনো টিচার পোস্ট করেননি।</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="fb-post" id="post-<?= e($post->Id) ?>">
                            <!-- Header -->
                            <div class="fb-post-header">
                                <div class="fb-post-user">
                                    <div class="pc-avatar">
                                        <?php if (!is_blank($post->UserProfileImage)): ?>
                                            <img src="<?= e($post->UserProfileImage) ?>" alt="<?= e($post->UserName) ?>" />
                                        <?php else: ?>
                                            <?= e($firstUpper($post->UserFullName, 'T')) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fb-post-user-info">
                                        <h6>
                                            <span onclick="viewTeacherProfile(<?= (int)$post->UserId ?>)" style="cursor: pointer; color: #08224A; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
                                                <?= e($post->UserFullName) ?>
                                            </span>
                                            <span class="badge bg-success" style="font-size:10px;">Teacher</span>
                                        </h6>
                                        <div class="time">
                                            <i class="far fa-clock"></i> <?= e(fmt_date($post->CreatedAt, 'd M Y, h:i A')) ?>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                    $isPostOwner = $isLoggedIn && (string)$post->UserId === $userId;
                                    $canDeletePost = $isPostOwner || ($isLoggedIn && $userRole === 'Admin');
                                ?>
                                <?php if ($canDeletePost): ?>
                                    <div class="fb-post-menu">
                                        <button type="button" class="fb-post-menu-btn" onclick="togglePostMenu(event, <?= (int)$post->Id ?>)" title="More options">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <div class="fb-post-menu-list" id="post-menu-<?= e($post->Id) ?>">
                                            <?php if ($isPostOwner): ?>
                                                <button type="button" onclick="openEditPost(<?= (int)$post->Id ?>)"><i class="fas fa-pen"></i> Edit post</button>
                                            <?php endif; ?>
                                            <button type="button" class="danger" onclick="openDeletePost(<?= (int)$post->Id ?>)"><i class="fas fa-trash"></i> Delete post</button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="fb-post-content" id="post-content-<?= e($post->Id) ?>"><?= e($post->Content) ?></div>

                            <!-- Image -->
                            <div class="fb-post-media" id="post-media-<?= e($post->Id) ?>">
                                <?php if (!is_blank($post->ImageUrl)): ?>
                                    <img src="<?= e($post->ImageUrl) ?>" alt="Post Image" />
                                <?php endif; ?>
                            </div>

                            <!-- Stats -->
                            <div class="fb-post-stats">
                                <span><i class="fas fa-thumbs-up text-primary"></i> <span id="like-count-<?= e($post->Id) ?>"><?= e($post->LikeCount) ?></span> Likes</span>
                                <span id="comment-count-<?= e($post->Id) ?>"><?= array_sum(array_map(fn($c) => 1 + count($c->Replies ?? []), $post->Comments ?? [])) ?> Comments</span>
                            </div>

                            <!-- Actions -->
                            <div class="fb-post-actions">
                                <?php if ($isLoggedIn): ?>
                                    <button class="fb-action-btn" onclick="toggleLike(<?= (int)$post->Id ?>)">
                                        <i class="far fa-thumbs-up" id="like-icon-<?= e($post->Id) ?>" style="color:#08224A;"></i> Like
                                    </button>
                                <?php else: ?>
                                    <button class="fb-action-btn" onclick="alert('লাইক করতে লগইন করুন!')">
                                        <i class="far fa-thumbs-up"></i> Like
                                    </button>
                                <?php endif; ?>
                                <button class="fb-action-btn" onclick="toggleComments(<?= (int)$post->Id ?>)">
                                    <i class="far fa-comment"></i> Comment
                                </button>
                            </div>

                            <!-- Comments Area (Facebook style) -->
                            <div class="fb-comments-area" id="comments-<?= e($post->Id) ?>">
                                <div class="fb-comment-list" id="comment-list-<?= e($post->Id) ?>">
                                    <?php foreach (($post->Comments ?? []) as $comment): ?>
                                        <div class="fbc" id="fbc-<?= e($comment->Id) ?>" data-id="<?= e($comment->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($comment->UserFullName) ?>">
                                            <div class="fbc-avatar">
                                                <?php if (!is_blank($comment->UserProfileImage)): ?>
                                                    <img src="<?= e($comment->UserProfileImage) ?>" alt="user" />
                                                <?php else: ?>
                                                    <?= e($firstUpper($comment->UserFullName, 'U')) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="fbc-main">
                                                <div class="fbc-bubble <?= $comment->LikeCount > 0 ? 'has-likes' : '' ?>">
                                                    <span class="fbc-name"><?= e($comment->UserFullName) ?></span>
                                                    <div class="fbc-text"><?= $RenderCommentText($comment->Content) ?></div>
                                                    <span class="fbc-like-badge <?= $comment->LikeCount > 0 ? '' : 'zero' ?>"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n"><?= e($comment->LikeCount) ?></span></span>
                                                </div>
                                                <div class="fbc-meta">
                                                    <?php if ($isLoggedIn): ?>
                                                        <button type="button" class="fbc-like-btn <?= $comment->IsLikedByMe ? 'liked' : '' ?>" onclick="fbcToggleLike(this, <?= (int)$comment->Id ?>)">Like</button>
                                                        <button type="button" onclick="fbcOpenReply(<?= (int)$post->Id ?>, <?= (int)$comment->Id ?>, <?= (int)$comment->Id ?>)">Reply</button>
                                                    <?php else: ?>
                                                        <button type="button" onclick="alert('লাইক করতে লগইন করুন!')">Like</button>
                                                        <button type="button" onclick="alert('রিপ্লাই করতে লগইন করুন!')">Reply</button>
                                                    <?php endif; ?>
                                                    <span class="fbc-time" data-utc="<?= e($CommentIso($comment->CreatedAt)) ?>" title="<?= e(fmt_date($comment->CreatedAt, 'd M Y, h:i A')) ?>"><?= e(fmt_date($comment->CreatedAt, 'd M')) ?></span>
                                                </div>

                                                <div class="fbc-replies-wrap">
                                                    <?php if (!empty($comment->Replies)): ?>
                                                        <button type="button" class="fbc-view-replies" id="fbc-vr-<?= e($comment->Id) ?>" onclick="fbcShowReplies(<?= (int)$comment->Id ?>)">
                                                            <i class="fas fa-share fa-flip-vertical"></i>
                                                            View <?= count($comment->Replies) ?> <?= count($comment->Replies) == 1 ? 'reply' : 'replies' ?>
                                                        </button>
                                                    <?php endif; ?>
                                                    <div class="fbc-replies" id="fbc-replies-<?= e($comment->Id) ?>">
                                                        <?php foreach (($comment->Replies ?? []) as $reply): ?>
                                                            <div class="fbc is-reply" id="fbc-<?= e($reply->Id) ?>" data-id="<?= e($reply->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($reply->UserFullName) ?>">
                                                                <div class="fbc-avatar">
                                                                    <?php if (!is_blank($reply->UserProfileImage)): ?>
                                                                        <img src="<?= e($reply->UserProfileImage) ?>" alt="user" />
                                                                    <?php else: ?>
                                                                        <?= e($firstUpper($reply->UserFullName, 'U')) ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="fbc-main">
                                                                    <div class="fbc-bubble <?= $reply->LikeCount > 0 ? 'has-likes' : '' ?>">
                                                                        <span class="fbc-name"><?= e($reply->UserFullName) ?></span>
                                                                        <div class="fbc-text"><?= $RenderCommentText($reply->Content) ?></div>
                                                                        <span class="fbc-like-badge <?= $reply->LikeCount > 0 ? '' : 'zero' ?>"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n"><?= e($reply->LikeCount) ?></span></span>
                                                                    </div>
                                                                    <div class="fbc-meta">
                                                                        <?php if ($isLoggedIn): ?>
                                                                            <button type="button" class="fbc-like-btn <?= $reply->IsLikedByMe ? 'liked' : '' ?>" onclick="fbcToggleLike(this, <?= (int)$reply->Id ?>)">Like</button>
                                                                            <button type="button" onclick="fbcOpenReply(<?= (int)$post->Id ?>, <?= (int)$comment->Id ?>, <?= (int)$reply->Id ?>)">Reply</button>
                                                                        <?php else: ?>
                                                                            <button type="button" onclick="alert('লাইক করতে লগইন করুন!')">Like</button>
                                                                            <button type="button" onclick="alert('রিপ্লাই করতে লগইন করুন!')">Reply</button>
                                                                        <?php endif; ?>
                                                                        <span class="fbc-time" data-utc="<?= e($CommentIso($reply->CreatedAt)) ?>" title="<?= e(fmt_date($reply->CreatedAt, 'd M Y, h:i A')) ?>"><?= e(fmt_date($reply->CreatedAt, 'd M')) ?></span>
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
                                                <?= e($firstUpper($userFullName, 'U')) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fbc-pill">
                                            <input type="text" id="comment-input-<?= e($post->Id) ?>" placeholder="Write a comment..." maxlength="2000" onkeydown="handleCommentEnter(event, <?= (int)$post->Id ?>)" />
                                            <button type="button" class="fbc-send" id="comment-send-<?= e($post->Id) ?>" onclick="submitComment(<?= (int)$post->Id ?>)" title="Send">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div style="text-align:center; font-size:13px; color:#7A8CA0;">
                                        কমেন্ট করতে <a href="/Account/Login" style="color:#08224A; font-weight:bold;">লগইন করুন</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <hr style="margin: 40px 0;" />

            <!-- ========================================== -->
            <!-- TUTOR LIST -->
            <!-- ========================================== -->
            <div class="list-header">
                <h3>
                    <i class="fas fa-user-graduate"></i> Our Tutors
                    <span class="count-badge" id="countBadge">0</span>
                </h3>
                <div class="sort-wrap">
                    <span>Sort:</span>
                    <select id="sortSelect" onchange="applySearch()">
                        <option value="rating">Top Rated</option>
                        <option value="fee-low">Fee: Low to High</option>
                        <option value="fee-high">Fee: High to Low</option>
                        <option value="experience">Most Experienced</option>
                    </select>
                </div>
            </div>

            <div id="tutorList"></div>

        </div>

        <aside class="ft-sidebar">
            <div class="area-tuition-card">
                <div class="atc-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h3>Get Tuition in Your Area</h3>
                <p>Want a tutor in your own area? Contact our admin and we'll help you find the right tutor near you — quickly and safely.</p>

                <ul class="atc-features">
                    <li><i class="fas fa-check-circle"></i> Verified tutors in your locality</li>
                    <li><i class="fas fa-check-circle"></i> Match by class, subject &amp; budget</li>
                    <li><i class="fas fa-check-circle"></i> Admin handles everything for you</li>
                    <li><i class="fas fa-check-circle"></i> 100% privacy protected</li>
                </ul>

                <button class="btn-area-contact" onclick="openAreaTuitionModal()">
                    <i class="fas fa-headset"></i> Contact Admin
                </button>
            </div>

            <div class="sidebar-card helper-card">
                <h4><i class="fas fa-hands-helping"></i> Need a Tutor?</h4>
                <p>Can't find the right tutor? Send us your requirements and our admin will match you with the perfect tutor.</p>
                <button class="btn-helper" onclick="openNeedTutorModal()">
                    <i class="fas fa-paper-plane"></i> Send Request to Admin
                </button>
            </div>

            <div class="tid-card">
                <h5><i class="fas fa-id-badge"></i> Have a Tutor ID?</h5>
                <p>Enter the Tutor ID you received to open the profile instantly.</p>
                <div class="tid-input">
                    <input type="text" id="tidInput" placeholder="TCH-1024" />
                    <button onclick="findByTutorId()"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </aside>

    </div>
</section>

<!-- ========================================== -->
<!-- CREATE POST MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">পোস্ট তৈরি করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 align-items-center mb-3">
                    <div class="pc-avatar">
                        <?php if (!is_blank($userProfileImg)): ?>
                            <img src="<?= e($userProfileImg) ?>" alt="me" />
                        <?php else: ?>
                            <?= e($firstUpper($userFullName, 'T')) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= e($userFullName) ?></h6>
                        <span class="badge bg-success" style="font-size:10px;">Teacher</span>
                    </div>
                </div>

                <textarea id="postContentInput" class="modal-textarea" placeholder="পড়াতে চাই... (আপনার অভিজ্ঞতা ও বিষয় সম্পর্কে লিখুন)" oninput="checkPostBtn()"></textarea>

                <div class="modal-preview" id="imagePreviewBox">
                    <button type="button" class="remove-preview" onclick="removePostImage()"><i class="fas fa-times"></i></button>
                    <img id="imagePreviewImg" src="" alt="Preview" />
                </div>

                <div class="modal-actions-bottom">
                    <span style="font-size:14px; font-weight:600; color:#08224A;">Add to your post</span>
                    <div>
                        <label for="postImageInput" class="icon-btn" style="cursor:pointer; margin:0;">
                            <i class="fas fa-images" style="color:#2ecc71;"></i>
                        </label>
                        <input type="file" id="postImageInput" accept="image/*" style="display:none;" onchange="previewPostImage(this)" />

                        <button class="icon-btn"><i class="fas fa-smile" style="color:#f39c12;"></i></button>
                    </div>
                </div>

                <button class="btn-post" id="submitPostBtn" onclick="submitPost()" disabled>Post</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- EDIT POST MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="editPostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">পোস্ট এডিট করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="editPostContent" class="modal-textarea" placeholder="পড়াতে চাই..." oninput="checkEditBtn()"></textarea>

                <div class="modal-preview" id="editImgBox">
                    <button type="button" class="remove-preview" onclick="removeEditImage()"><i class="fas fa-times"></i></button>
                    <img id="editImgPreview" src="" alt="Preview" />
                </div>

                <div class="modal-actions-bottom">
                    <span style="font-size:14px; font-weight:600; color:#08224A;">Photo</span>
                    <div>
                        <label for="editImageInput" class="icon-btn" style="cursor:pointer; margin:0;">
                            <i class="fas fa-images" style="color:#2ecc71;"></i>
                        </label>
                        <input type="file" id="editImageInput" accept="image/*" style="display:none;" onchange="previewEditImage(this)" />
                    </div>
                </div>

                <button class="btn-post" id="saveEditBtn" onclick="submitEditPost()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- DELETE POST MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="deletePostModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div style="font-size:42px; color:#d93025; margin-bottom:8px;"><i class="fas fa-trash-alt"></i></div>
                <h5 style="font-weight:700; color:#08224A;">পোস্টটি ডিলিট করবেন?</h5>
                <p style="color:#64748B; font-size:14px; margin-bottom:20px;">পোস্ট, এর ছবি এবং সব কমেন্ট স্থায়ীভাবে মুছে যাবে। এটি আর ফেরত আনা যাবে না।</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal" style="font-weight:600;">Cancel</button>
                    <button type="button" class="btn flex-fill" id="confirmDeleteBtn" onclick="confirmDeletePost()" style="background:#d93025; color:#fff; font-weight:600;">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALS for Tutor Profile & Requests -->
<div class="modal fade ft-modal" id="tutorProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-tie"></i> Tutor Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="tutorProfileBody"></div>
        </div>
    </div>
</div>

<div class="modal fade ft-modal" id="requestTutorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Request This Tutor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestTutorBody"></div>
        </div>
    </div>
</div>

<div class="modal fade ft-modal" id="needTutorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-hands-helping"></i> I Need a Tutor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="needTutorBody"></div>
        </div>
    </div>
</div>

<div class="modal fade ft-modal" id="areaTuitionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-map-marked-alt"></i> Get Tuition in Your Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="areaTuitionBody"></div>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
<?php
        $tutorsJson = js(camel_keys(array_values($Model ?? [])));
    ?>

    <script>
        window.allTutors = <?= $tutorsJson ?>;

        // ============================================================
        // POST SYSTEM JS
        // ============================================================
        var postImageUrl = null;

        function openPostModal() {
            document.getElementById('postContentInput').value = '';
            removePostImage();
            checkPostBtn();
            new bootstrap.Modal(document.getElementById('createPostModal')).show();
        }

        function checkPostBtn() {
            var content = document.getElementById('postContentInput').value.trim();
            var btn = document.getElementById('submitPostBtn');
            btn.disabled = (content.length === 0 && !postImageUrl);
        }

        function previewPostImage(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                if (file.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); return; }

                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('imagePreviewImg').src = e.target.result;
                    document.getElementById('imagePreviewBox').style.display = 'block';
                    checkPostBtn();
                }
                reader.readAsDataURL(file);
            }
        }

        function removePostImage() {
            document.getElementById('postImageInput').value = '';
            document.getElementById('imagePreviewBox').style.display = 'none';
            document.getElementById('imagePreviewImg').src = '';
            checkPostBtn();
        }

        async function submitPost() {
            var content = document.getElementById('postContentInput').value.trim();
            var btn = document.getElementById('submitPostBtn');
            var originalText = btn.innerText;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            btn.disabled = true;

            var formData = new FormData();
            formData.append('content', content);

            var fileInput = document.getElementById('postImageInput');
            if (fileInput.files.length > 0) {
                formData.append('imageFile', fileInput.files[0]);
            }

            var token = document.querySelector('input[name="__RequestVerificationToken"]').value;
            formData.append('__RequestVerificationToken', token);

            try {
                var res = await fetch('/FindTutor/CreateTeachPost', { method: 'POST', body: formData });
                var data = await res.json();

                if (data.success) {
                    location.reload(); // Reload to show the new post
                } else {
                    alert(data.message || 'Failed to post.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                alert('Error: ' + err.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // ============================================================
        // POST EDIT / DELETE
        // ============================================================
        var editPostId = null, editRemoveImg = false, deletePostId = null;

        window.togglePostMenu = function (e, postId) {
            e.stopPropagation();
            var menu = document.getElementById('post-menu-' + postId);
            var wasOpen = menu.classList.contains('show');
            closePostMenus();
            if (!wasOpen) menu.classList.add('show');
        };

        function closePostMenus() {
            document.querySelectorAll('.fb-post-menu-list.show').forEach(function (m) { m.classList.remove('show'); });
        }
        document.addEventListener('click', closePostMenus);

        window.openEditPost = function (postId) {
            closePostMenus();
            editPostId = postId;
            editRemoveImg = false;

            document.getElementById('editPostContent').value =
                document.getElementById('post-content-' + postId).textContent.trim();
            document.getElementById('editImageInput').value = '';

            var img = document.querySelector('#post-media-' + postId + ' img');
            var box = document.getElementById('editImgBox');
            if (img) {
                document.getElementById('editImgPreview').src = img.getAttribute('src');
                box.style.display = 'block';
            } else {
                document.getElementById('editImgPreview').src = '';
                box.style.display = 'none';
            }

            checkEditBtn();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editPostModal')).show();
        };

        window.checkEditBtn = function () {
            var hasText = document.getElementById('editPostContent').value.trim().length > 0;
            var hasImg = document.getElementById('editImgBox').style.display === 'block';
            document.getElementById('saveEditBtn').disabled = !(hasText || hasImg);
        };

        window.previewEditImage = function (input) {
            if (!input.files || !input.files[0]) return;
            var file = input.files[0];
            if (file.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); input.value = ''; return; }
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('editImgPreview').src = e.target.result;
                document.getElementById('editImgBox').style.display = 'block';
                editRemoveImg = false;
                checkEditBtn();
            };
            reader.readAsDataURL(file);
        };

        window.removeEditImage = function () {
            document.getElementById('editImageInput').value = '';
            document.getElementById('editImgPreview').src = '';
            document.getElementById('editImgBox').style.display = 'none';
            editRemoveImg = true;
            checkEditBtn();
        };

        window.submitEditPost = async function () {
            var btn = document.getElementById('saveEditBtn');
            var original = btn.innerText;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            var fd = new FormData();
            fd.append('postId', editPostId);
            fd.append('content', document.getElementById('editPostContent').value.trim());
            fd.append('removeImage', editRemoveImg ? 'true' : 'false');
            var f = document.getElementById('editImageInput');
            if (f.files.length > 0) fd.append('imageFile', f.files[0]);
            fd.append('__RequestVerificationToken', fbcToken());

            try {
                var res = await fetch('/FindTutor/EditTeachPost', { method: 'POST', body: fd });
                var data = await res.json();
                if (data.success) {
                    document.getElementById('post-content-' + editPostId).textContent = data.content;
                    var media = document.getElementById('post-media-' + editPostId);
                    media.innerHTML = data.imageUrl ? '<img src="' + fbcEsc(data.imageUrl) + '" alt="Post Image" />' : '';
                    bootstrap.Modal.getInstance(document.getElementById('editPostModal')).hide();
                } else {
                    alert(data.message || 'Failed to update post.');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
            btn.innerHTML = original;
            checkEditBtn();
        };

        window.openDeletePost = function (postId) {
            closePostMenus();
            deletePostId = postId;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deletePostModal')).show();
        };

        window.confirmDeletePost = async function () {
            var btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            var fd = new FormData();
            fd.append('postId', deletePostId);
            fd.append('__RequestVerificationToken', fbcToken());

            try {
                var res = await fetch('/FindTutor/DeleteTeachPost', { method: 'POST', body: fd });
                var data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('deletePostModal')).hide();
                    var el = document.getElementById('post-' + deletePostId);
                    if (el) {
                        el.style.transition = 'opacity .3s, transform .3s';
                        el.style.opacity = '0';
                        el.style.transform = 'scale(.97)';
                        setTimeout(function () { el.remove(); }, 300);
                    }
                } else {
                    alert(data.message || 'Failed to delete post.');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
            btn.disabled = false;
            btn.innerText = 'Delete';
        };

        // ============================================================
        // FACEBOOK-STYLE COMMENTS & REPLIES
        // ============================================================
        window.fbcMe = <?= js(['name' => $userFullName, 'img' => $userProfileImg ?? '']) ?>;

        function toggleComments(postId) {
            var area = document.getElementById('comments-' + postId);
            if (area.classList.contains('show')) {
                area.classList.remove('show');
            } else {
                area.classList.add('show');
                var inp = document.getElementById('comment-input-' + postId);
                if (inp) inp.focus();
            }
        }

        function handleCommentEnter(e, postId) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitComment(postId);
            }
        }

        function fbcEsc(t) {
            return String(t == null ? '' : t)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function fbcToken() {
            return document.querySelector('input[name="__RequestVerificationToken"]').value;
        }

        function fbcAvatarHtml(img, name) {
            if (img) return '<img src="' + fbcEsc(img) + '" alt="user" />';
            return fbcEsc((name || 'U').charAt(0).toUpperCase());
        }

        // "@[Name] text" -> নীল মেনশন
        function fbcRenderText(content) {
            var s = fbcEsc(content);
            var m = s.match(/^@\[([^\]]+)\]\s?/);
            if (m) return '<span class="fbc-mention">' + m[1] + '</span> ' + s.slice(m[0].length);
            return s;
        }

        function fbcRelTime(iso) {
            var t = new Date(iso).getTime();
            if (isNaN(t)) return null;
            var sec = Math.max(0, Math.floor((Date.now() - t) / 1000));
            if (sec < 60) return 'Just now';
            var min = Math.floor(sec / 60); if (min < 60) return min + 'm';
            var hr = Math.floor(min / 60); if (hr < 24) return hr + 'h';
            var day = Math.floor(hr / 24); if (day < 7) return day + 'd';
            var wk = Math.floor(day / 7); if (day < 365) return wk + 'w';
            return Math.floor(day / 365) + 'y';
        }

        function fbcRefreshTimes() {
            document.querySelectorAll('.fbc-time[data-utc]').forEach(function (el) {
                var r = fbcRelTime(el.getAttribute('data-utc'));
                if (r) el.textContent = r;
            });
        }

        function fbcBuild(c, isReply, postId, rootId) {
            var liked = c.isLiked ? ' liked' : '';
            var zero = (c.likeCount > 0) ? '' : ' zero';
            var h = ''
                + '<div class="fbc' + (isReply ? ' is-reply' : '') + '" id="fbc-' + c.id + '" data-id="' + c.id + '" data-root="' + rootId + '" data-name="' + fbcEsc(c.userFullName) + '">'
                + '  <div class="fbc-avatar">' + fbcAvatarHtml(c.userProfileImage, c.userFullName) + '</div>'
                + '  <div class="fbc-main">'
                + '    <div class="fbc-bubble' + (c.likeCount > 0 ? ' has-likes' : '') + '">'
                + '      <span class="fbc-name">' + fbcEsc(c.userFullName) + '</span>'
                + '      <div class="fbc-text">' + fbcRenderText(c.content) + '</div>'
                + '      <span class="fbc-like-badge' + zero + '"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n">' + (c.likeCount || 0) + '</span></span>'
                + '    </div>'
                + '    <div class="fbc-meta">'
                + '      <button type="button" class="fbc-like-btn' + liked + '" onclick="fbcToggleLike(this, ' + c.id + ')">Like</button>'
                + '      <button type="button" onclick="fbcOpenReply(' + postId + ', ' + rootId + ', ' + c.id + ')">Reply</button>'
                + '      <span class="fbc-time" data-utc="' + fbcEsc(c.createdAtUtc) + '">Just now</span>'
                + '    </div>';
            if (!isReply) {
                h += '    <div class="fbc-replies-wrap"><div class="fbc-replies" id="fbc-replies-' + c.id + '"></div></div>';
            }
            h += '  </div></div>';
            return h;
        }

        function fbcBumpCount(postId) {
            var el = document.getElementById('comment-count-' + postId);
            if (!el) return;
            var n = parseInt(el.textContent, 10) || 0;
            el.textContent = (n + 1) + ' Comments';
        }

        async function fbcSend(postId, content, parentId) {
            var fd = new FormData();
            fd.append('postId', postId);
            fd.append('content', content);
            if (parentId) fd.append('parentId', parentId);
            fd.append('__RequestVerificationToken', fbcToken());
            var res = await fetch('/FindTutor/AddTeachComment', { method: 'POST', body: fd });
            return await res.json();
        }

        // ---------- মেইন কমেন্ট ----------
        window.submitComment = async function (postId) {
            var input = document.getElementById('comment-input-' + postId);
            var btn = document.getElementById('comment-send-' + postId);
            var content = input.value.trim();
            if (!content) return;

            input.disabled = true; if (btn) btn.disabled = true;
            try {
                var data = await fbcSend(postId, content, null);
                if (data.success) {
                    var list = document.getElementById('comment-list-' + postId);
                    list.insertAdjacentHTML('beforeend', fbcBuild(data.comment, false, postId, data.comment.id));
                    input.value = '';
                    fbcBumpCount(postId);
                    fbcRefreshTimes();
                } else {
                    alert(data.message || 'Failed to comment.');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
            input.disabled = false; if (btn) btn.disabled = false;
            input.focus();
        };

        // ---------- রিপ্লাই ----------
        window.fbcShowReplies = function (rootId) {
            var list = document.getElementById('fbc-replies-' + rootId);
            if (list) list.style.display = 'flex';
            var vr = document.getElementById('fbc-vr-' + rootId);
            if (vr) vr.style.display = 'none';
        };

        window.fbcCloseReplyBoxes = function (postId) {
            document.querySelectorAll('#post-' + postId + ' .fbc-replybox').forEach(function (b) { b.remove(); });
        };

        // postId, rootId (মেইন কমেন্ট), targetId (যে কমেন্ট/রিপ্লাইয়ে Reply চাপা হয়েছে)
        window.fbcOpenReply = function (postId, rootId, targetId) {
            var target = document.getElementById('fbc-' + targetId);
            if (!target) return;
            var targetName = target.getAttribute('data-name') || '';
            var isNested = (targetId !== rootId);

            fbcCloseReplyBoxes(postId);
            fbcShowReplies(rootId);

            var box = document.createElement('div');
            box.className = 'fbc-input-row fbc-replybox';
            box.innerHTML = ''
                + '<div class="fbc-avatar">' + fbcAvatarHtml(window.fbcMe.img, window.fbcMe.name) + '</div>'
                + '<div class="fbc-pill">'
                + (isNested ? '<span class="fbc-mention-tag">' + fbcEsc(targetName) + '</span>' : '')
                + '  <input type="text" maxlength="2000" placeholder="' + (isNested ? '' : 'Reply to ' + fbcEsc(targetName) + '...') + '" />'
                + '  <button type="button" class="fbc-send" title="Send"><i class="fas fa-paper-plane"></i></button>'
                + '</div>';

            var list = document.getElementById('fbc-replies-' + rootId);
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
                    var data = await fbcSend(postId, content, targetId);
                    if (data.success) {
                        box.insertAdjacentHTML('beforebegin', fbcBuild(data.comment, true, postId, rootId));
                        box.remove();
                        fbcBumpCount(postId);
                        fbcRefreshTimes();
                        return;
                    }
                    alert(data.message || 'Failed to reply.');
                } catch (err) {
                    alert('Error: ' + err.message);
                }
                input.disabled = false; send.disabled = false; input.focus();
            }

            send.addEventListener('click', doSend);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); doSend(); }
                else if (e.key === 'Escape') { box.remove(); }
            });
            input.focus();
        };

        // ---------- কমেন্ট লাইক ----------
        window.fbcToggleLike = async function (btn, commentId) {
            var fd = new FormData();
            fd.append('commentId', commentId);
            fd.append('__RequestVerificationToken', fbcToken());
            btn.disabled = true;
            try {
                var res = await fetch('/FindTutor/ToggleTeachCommentLike', { method: 'POST', body: fd });
                var data = await res.json();
                if (data.success) {
                    btn.classList.toggle('liked', data.isLiked);
                    var el = document.getElementById('fbc-' + commentId);
                    var bubble = el.querySelector('.fbc-bubble');
                    var badge = bubble.querySelector('.fbc-like-badge');
                    badge.querySelector('.fbc-like-n').textContent = data.count;
                    badge.classList.toggle('zero', data.count < 1);
                    bubble.classList.toggle('has-likes', data.count > 0);
                } else {
                    alert(data.message || 'Failed.');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
            btn.disabled = false;
        };

        document.addEventListener('DOMContentLoaded', function () {
            fbcRefreshTimes();
            setInterval(fbcRefreshTimes, 60000);
        });

        async function toggleLike(postId) {
            var token = document.querySelector('input[name="__RequestVerificationToken"]').value;
            var formData = new FormData();
            formData.append('postId', postId);
            formData.append('__RequestVerificationToken', token);

            try {
                var res = await fetch('/FindTutor/ToggleTeachLike', { method: 'POST', body: formData });
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
            } catch (err) {
                console.log(err);
            }
        }

        // ============================================================
        // EXISTING TUTOR LOGIC
        // ============================================================
        (function () {
            'use strict';

            // ✅ Check Login Status in JS
            var isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

            var allTutors = window.allTutors || [];

            function stars(rating) {
                var full = Math.floor(rating);
                var half = rating - full >= 0.5;
                var s = '';
                for (var i = 0; i < full; i++) s += '★';
                if (half) s += '⯨';
                return s;
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

            function generateRequestId() {
                var d = new Date();
                var datePart = d.getFullYear().toString()
                    + String(d.getMonth() + 1).padStart(2, '0')
                    + String(d.getDate()).padStart(2, '0');
                var seq = String(Math.floor(Math.random() * 900) + 100);
                return 'REQ-' + datePart + '-' + seq;
            }

            function closeModal(modalId) {
                var m = bootstrap.Modal.getInstance(document.getElementById(modalId));
                if (m) m.hide();
            }

            function getToken() {
                var t = document.querySelector('input[name="__RequestVerificationToken"]');
                return t ? t.value : '';
            }

            function isValidBdPhone(num) {
                return /^01[3-9]\d{8}$/.test(String(num || '').trim());
            }

            window.toggleSameWhatsApp = function (phoneId, waId, checkbox) {
                var phone = document.getElementById(phoneId);
                var wa = document.getElementById(waId);
                if (!phone || !wa) return;

                if (checkbox.checked) {
                    wa.value = phone.value;
                    wa.readOnly = true;
                    wa.style.background = '#F5FBFF';
                } else {
                    wa.readOnly = false;
                    wa.style.background = '';
                }
            };

            document.addEventListener('input', function (e) {
                var id = e.target.id || '';
                if (id === 'rtPhone' || id === 'ntPhone' || id === 'atPhone') {
                    var waId = id.replace('Phone', 'WhatsApp');
                    var cbId = id.replace('Phone', 'SameWhatsApp');
                    var cb = document.getElementById(cbId);
                    var wa = document.getElementById(waId);
                    if (cb && cb.checked && wa) {
                        wa.value = e.target.value;
                    }
                }
            });

            function createRow(t) {
                var verifiedTick = t.isVerified
                    ? '<div class="v-tick" title="Verified Tutor"><i class="fas fa-check"></i></div>'
                    : '';
                var topBadge = t.isTopRated
                    ? '<span class="tr-top-badge"><i class="fas fa-crown me-1"></i>Top</span>'
                    : '';

                var classTags = (t.classes || []).slice(0, 3).map(function (c) {
                    return '<span class="tr-tag">' + escapeHtml(c) + '</span>';
                }).join('');

                var moreClasses = (t.classes || []).length > 3
                    ? '<span class="tr-tag" style="background:#DCECF5;">+' + (t.classes.length - 3) + '</span>'
                    : '';

                var modeIcon = t.teachingMode === 'Online' ? 'fa-laptop'
                    : (t.teachingMode === 'Offline' ? 'fa-building' : 'fa-globe');

                var avatarContent = (t.photoUrl && t.photoUrl.trim() !== '')
                    ? '<img src="' + escapeHtml(t.photoUrl) + '" alt="' + escapeHtml(t.fullName) + '" />'
                    : escapeHtml(t.initials);

                return ''
                    + '<div class="tutor-row">'
                    + '  <div class="tr-avatar">'
                    + '    <div class="avatar-circle">' + avatarContent + '</div>'
                    + verifiedTick
                    + '  </div>'
                    + '  <div class="tr-info">'
                    + '    <div class="tr-name-line">'
                    + '      <h4 class="tr-name">' + escapeHtml(t.fullName) + '</h4>'
                    + '      <span class="tr-id">' + escapeHtml(t.tutorId) + '</span>'
                    + topBadge
                    + '    </div>'
                    + '    <div class="tr-rating-line">'
                    + '      <span class="stars">' + stars(t.rating) + '</span>'
                    + '      <span><strong>' + t.rating + '</strong></span>'
                    + '      <span class="count">(' + t.totalReviews + ' reviews)</span>'
                    + '    </div>'
                    + '    <div class="tr-meta">'
                    + '      <div class="m-item"><i class="fas fa-graduation-cap"></i><span>' + escapeHtml(t.qualification) + '</span></div>'
                    + '      <div class="m-item"><i class="fas fa-university"></i><span>' + escapeHtml(t.institution) + '</span></div>'
                    + '      <div class="m-item"><i class="fas fa-briefcase"></i><span>' + t.experienceYears + '+ years experience</span></div>'
                    + '      <div class="m-item"><i class="fas fa-map-marker-alt"></i><span>' + escapeHtml(t.location) + '</span></div>'
                    + '    </div>'
                    + '    <div class="tr-tags">'
                    + '      <span class="tr-tag mode"><i class="fas ' + modeIcon + ' me-1"></i>' + escapeHtml(t.teachingMode) + '</span>'
                    + classTags
                    + moreClasses
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="tr-actions">'
                    + '    <div class="tr-fee">'
                    + '      <div class="fee-val">৳' + Number(t.monthlyFee).toLocaleString() + '</div>'
                    + '      <div class="fee-lbl">Monthly Fee</div>'
                    + '    </div>'
                    + '    <button class="btn-view-tr" onclick="viewProfile(' + t.id + ')">'
                    + '      <i class="fas fa-eye"></i> View Profile'
                    + '    </button>'
                    + '    <button class="btn-request-tr" onclick="openRequestTutorModal(' + t.id + ')">'
                    + '      <i class="fas fa-paper-plane"></i> Request'
                    + '    </button>'
                    + '  </div>'
                    + '</div>';
            }

            function renderList(tutors) {
                var wrap = document.getElementById('tutorList');
                var badge = document.getElementById('countBadge');
                badge.textContent = tutors.length;

                if (!tutors || tutors.length === 0) {
                    wrap.innerHTML = ''
                        + '<div class="empty-state">'
                        + '  <div class="es-icon"><i class="fas fa-search"></i></div>'
                        + '  <h4>No tutors found</h4>'
                        + '  <p>Try a different search, or let our admin team find a tutor for you.</p>'
                        + '  <button class="btn-helper" onclick="openNeedTutorModal()" style="max-width:280px; margin:0 auto;">'
                        + '    <i class="fas fa-hands-helping"></i> Request a Tutor'
                        + '  </button>'
                        + '</div>';
                    return;
                }

                wrap.innerHTML = tutors.map(createRow).join('');
            }

            function applySearch() {
                var sort = document.getElementById('sortSelect').value;

                var list = allTutors.slice();

                switch (sort) {
                    case 'rating': list.sort(function (a, b) { return b.rating - a.rating; }); break;
                    case 'fee-low': list.sort(function (a, b) { return a.monthlyFee - b.monthlyFee; }); break;
                    case 'fee-high': list.sort(function (a, b) { return b.monthlyFee - a.monthlyFee; }); break;
                    case 'experience': list.sort(function (a, b) { return b.experienceYears - a.experienceYears; }); break;
                }

                renderList(list);
            }

            function viewProfile(id) {
                var t = allTutors.find(function (x) { return x.id === id; });
                if (!t) return;

                var verifiedIcon = t.isVerified
                    ? '<i class="fas fa-check-circle" style="color:#2ecc71;"></i>' : '';
                var topBadge = t.isTopRated
                    ? '<span style="background:linear-gradient(135deg,#E09A00,#B8760F);color:#fff;font-size:10px;font-weight:700;padding:4px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;"><i class="fas fa-crown me-1"></i>Top Rated</span>'
                    : '';
                var verifiedTick = t.isVerified
                    ? '<div class="v-tick"><i class="fas fa-check"></i></div>' : '';

                var classChips = (t.classes || []).map(function (c) { return '<span class="tp-chip">' + escapeHtml(c) + '</span>'; }).join('');
                var subjectChips = (t.subjects || []).map(function (s) { return '<span class="tp-chip">' + escapeHtml(s) + '</span>'; }).join('');

                var avatarContent = (t.photoUrl && t.photoUrl.trim() !== '')
                    ? '<img src="' + escapeHtml(t.photoUrl) + '" alt="' + escapeHtml(t.fullName) + '" />'
                    : escapeHtml(t.initials);

                document.getElementById('tutorProfileBody').innerHTML = ''
                    + '<div class="tp-header">'
                    + '  <div class="tp-avatar">' + avatarContent + verifiedTick + '</div>'
                    + '  <div style="flex:1; min-width:0;">'
                    + '    <h4 class="tp-name">' + escapeHtml(t.fullName) + ' ' + verifiedIcon + '</h4>'
                    + '    <span class="tp-id">' + escapeHtml(t.tutorId) + '</span>'
                    + '    <div class="tp-rating-line">'
                    + '      <span>' + stars(t.rating) + '</span>'
                    + '      <span>' + t.rating + '</span>'
                    + '      <span class="count">· ' + t.totalReviews + ' reviews</span>'
                    + '    </div>'
                    + '    <div style="margin-top:8px;">' + topBadge + '</div>'
                    + '  </div>'
                    + '</div>'
                    + '<div class="tp-stat-row">'
                    + '  <div class="tp-stat"><div class="num">৳' + Number(t.monthlyFee).toLocaleString() + '</div><div class="lbl">Monthly Fee</div></div>'
                    + '  <div class="tp-stat"><div class="num">' + t.experienceYears + '+</div><div class="lbl">Years Exp.</div></div>'
                    + '  <div class="tp-stat"><div class="num">' + escapeHtml(t.gender) + '</div><div class="lbl">Gender</div></div>'
                    + '</div>'
                    + '<div class="tp-block"><div class="tp-block-title"><i class="fas fa-user"></i>About Tutor</div><p>' + escapeHtml(t.about) + '</p></div>'
                    + '<div class="tp-block"><div class="tp-block-title"><i class="fas fa-graduation-cap"></i>Education</div>'
                    + '  <div class="tp-info-grid">'
                    + '    <div class="tp-info-item"><div class="label">Qualification</div><div class="value">' + escapeHtml(t.qualification) + '</div></div>'
                    + '    <div class="tp-info-item"><div class="label">Institution</div><div class="value">' + escapeHtml(t.institution) + '</div></div>'
                    + '  </div>'
                    + '</div>'
                    + '<div class="tp-block"><div class="tp-block-title"><i class="fas fa-book"></i>Subjects</div><div class="tp-chips">' + subjectChips + '</div></div>'
                    + '<div class="tp-block"><div class="tp-block-title"><i class="fas fa-users"></i>Classes Taught</div><div class="tp-chips">' + classChips + '</div></div>'
                    + '<div class="tp-block"><div class="tp-block-title"><i class="fas fa-info-circle"></i>Details</div>'
                    + '  <div class="tp-info-grid">'
                    + '    <div class="tp-info-item"><div class="label">Location</div><div class="value">' + escapeHtml(t.location) + '</div></div>'
                    + '    <div class="tp-info-item"><div class="label">Teaching Mode</div><div class="value">' + escapeHtml(t.teachingMode) + '</div></div>'
                    + '    <div class="tp-info-item"><div class="label">Available Time</div><div class="value">' + escapeHtml(t.availableTime) + '</div></div>'
                    + '    <div class="tp-info-item"><div class="label">Experience</div><div class="value">' + t.experienceYears + '+ years</div></div>'
                    + '  </div>'
                    + '</div>'
                    + '<div class="tp-cta">'
                    + '  <p><i class="fas fa-shield-alt me-1"></i> For privacy, we never share tutor contact details. All requests go through our admin team.</p>'
                    + '  <button class="btn-request-tr" style="width:100%; padding:14px;" onclick="switchToRequest(' + t.id + ')">'
                    + '    <i class="fas fa-paper-plane"></i> Request This Tutor'
                    + '  </button>'
                    + '</div>';

                new bootstrap.Modal(document.getElementById('tutorProfileModal')).show();
            }

            function switchToRequest(id) {
                var m = bootstrap.Modal.getInstance(document.getElementById('tutorProfileModal'));
                if (m) m.hide();
                setTimeout(function () { openRequestTutorModal(id); }, 260);
            }

            function openRequestTutorModal(tutorId) {
                // ✅ লগইন চেক
                if (!isLoggedIn) {
                    alert('রিকোয়েস্ট পাঠাতে অনুগ্রহ করে প্রথমে লগইন করুন।');
                    window.location.href = '/Account/Login?returnUrl=/FindTutor/Index';
                    return;
                }

                var t = tutorId ? allTutors.find(function (x) { return x.id === tutorId; }) : null;

                var headerInfo = t
                    ? '<div class="form-intro"><p>Requesting <strong>' + escapeHtml(t.fullName) + '</strong> '
                    + '(<span style="font-family:monospace;">' + escapeHtml(t.tutorId) + '</span>) — '
                    + escapeHtml((t.subjects || []).join(', ')) + '. Your request goes to our admin team.</p></div>'
                    : '<div class="form-intro"><p>Already know your tutor? Select them below and submit your request. Our admin team will forward it.</p></div>';

                var tutorOptions = allTutors.map(function (x) {
                    var sel = (t && t.id === x.id) ? ' selected' : '';
                    return '<option value="' + x.id + '"' + sel + '>'
                        + escapeHtml(x.tutorId) + ' — ' + escapeHtml(x.fullName)
                        + ' (' + escapeHtml((x.subjects || []).join(', ')) + ')</option>';
                }).join('');

                document.getElementById('requestTutorBody').innerHTML = ''
                    + '<form class="ft-form" onsubmit="submitTutorRequest(event)">'
                    + headerInfo
                    + '  <div class="form-section-label">Tutor Selection</div>'
                    + '  <div class="field"><label>Tutor <span class="req">*</span></label>'
                    + '    <select id="rtTutor" required>'
                    + '      <option value="">— Select a Tutor —</option>'
                    + tutorOptions
                    + '    </select>'
                    + '  </div>'
                    + '  <div class="form-section-label">Student Information</div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Student Name <span class="req">*</span></label><input type="text" id="rtName" required /></div>'
                    + '    <div class="field"><label>Phone Number <span class="req">*</span></label><input type="tel" id="rtPhone" required placeholder="01XXXXXXXXX" maxlength="11" /></div>'
                    + '  </div>'
                    + '  <div class="field whatsapp-icon-input">'
                    + '    <label>WhatsApp Number <span class="req">*</span></label>'
                    + '    <i class="fab fa-whatsapp wa-icon"></i>'
                    + '    <input type="tel" id="rtWhatsApp" placeholder="01XXXXXXXXX" required maxlength="11" />'
                    + '    <div class="whatsapp-same-toggle">'
                    + '      <input type="checkbox" id="rtSameWhatsApp" checked onchange="toggleSameWhatsApp(\'rtPhone\',\'rtWhatsApp\',this)" />'
                    + '      <label for="rtSameWhatsApp">Same as phone number</label>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="field"><label>Email</label><input type="email" id="rtEmail" /></div>'
                    + '  <div class="form-section-label">Requirement Details</div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Student Class <span class="req">*</span></label>'
                    + '      <select id="rtClass" required>'
                    + '        <option value="">Select Class</option>'
                    + '        <option>Class 6-8</option><option>Class 9-10</option>'
                    + '        <option>HSC</option><option>Admission</option>'
                    + '        <option>University</option><option>Professional</option>'
                    + '      </select>'
                    + '    </div>'
                    + '    <div class="field"><label>Required Subject <span class="req">*</span></label><input type="text" id="rtSubject" required /></div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Preferred Area</label><input type="text" id="rtArea" /></div>'
                    + '    <div class="field"><label>Teaching Mode</label>'
                    + '      <select id="rtMode"><option>Online</option><option>Offline</option><option selected>Both</option></select>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Preferred Days</label><input type="text" id="rtDays" /></div>'
                    + '    <div class="field"><label>Preferred Time</label><input type="text" id="rtTime" /></div>'
                    + '  </div>'
                    + '  <div class="field"><label>Monthly Budget (৳)</label><input type="text" id="rtBudget" /></div>'
                    + '  <div class="form-section-label">Additional</div>'
                    + '  <div class="field"><label>Additional Requirements</label><textarea id="rtNotes"></textarea></div>'
                    + '  <button type="submit" class="btn-submit" id="rtSubmitBtn"><i class="fas fa-paper-plane"></i> Send Tutor Request</button>'
                    + '</form>';

                new bootstrap.Modal(document.getElementById('requestTutorModal')).show();
            }

            async function submitTutorRequest(e) {
                e.preventDefault();

                var btn = document.getElementById('rtSubmitBtn');
                var originalHtml = btn.innerHTML;

                var tutorId = document.getElementById('rtTutor').value;
                var tutor = allTutors.find(function (x) { return String(x.id) === String(tutorId); });

                var name = document.getElementById('rtName').value.trim();
                var phone = document.getElementById('rtPhone').value.trim();
                var wa = document.getElementById('rtWhatsApp').value.trim();

                if (!name) { alert('Please enter student name.'); return; }
                if (!isValidBdPhone(phone)) { alert('Please enter a valid phone number (e.g., 01712345678).'); return; }
                if (!isValidBdPhone(wa)) { alert('Please enter a valid WhatsApp number.'); return; }

                var reqId = generateRequestId();

                var fd = new FormData();
                // এখানে নিশ্চিত করা হলো যে TutorId এবং TutorName সঠিকভাবে যাচ্ছে
                fd.append('tutorId', tutor ? tutor.tutorId : '');
                fd.append('tutorName', tutor ? tutor.fullName : '');
                fd.append('studentName', name);
                fd.append('phoneNumber', phone);
                fd.append('whatsAppNumber', wa);
                fd.append('email', document.getElementById('rtEmail').value.trim());
                fd.append('studentClass', document.getElementById('rtClass').value);
                fd.append('requiredSubject', document.getElementById('rtSubject').value.trim());
                fd.append('preferredArea', document.getElementById('rtArea').value.trim());
                fd.append('teachingMode', document.getElementById('rtMode').value);
                fd.append('preferredDays', document.getElementById('rtDays').value.trim());
                fd.append('preferredTime', document.getElementById('rtTime').value.trim());
                fd.append('monthlyBudget', document.getElementById('rtBudget').value.trim());
                fd.append('additionalNotes', document.getElementById('rtNotes').value.trim());
                fd.append('requestType', 'SpecificTutor');
                fd.append('requestId', reqId);
                fd.append('__RequestVerificationToken', getToken());

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

                try {
                    var res = await fetch('/FindTutor/SubmitTutorRequest', { method: 'POST', body: fd });
                    var data = await res.json();

                    if (!data.success) {
                        alert('❌ ' + (data.message || 'Failed to submit.'));
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                        return;
                    }

                    document.getElementById('requestTutorBody').innerHTML = ''
                        + '<div class="success-panel">'
                        + '  <div class="success-icon"><i class="fas fa-check"></i></div>'
                        + '  <h4>Request Submitted Successfully!</h4>'
                        + '  <p>Your request for <strong>' + escapeHtml(tutor ? tutor.fullName : '') + '</strong> has been received. Our admin team will contact you on WhatsApp shortly.</p>'
                        + '  <div class="success-req-id"><span class="req-label">Request ID</span><span class="req-value">' + escapeHtml(data.requestId || reqId) + '</span></div>'
                        + '  <button class="btn-ok" onclick="closeModal(\'requestTutorModal\')">OK</button>'
                        + '</div>';
                } catch (err) {
                    alert('❌ Network error: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }

            function openNeedTutorModal() {
                // ✅ লগইন চেক
                if (!isLoggedIn) {
                    alert('রিকোয়েস্ট পাঠাতে অনুগ্রহ করে প্রথমে লগইন করুন।');
                    window.location.href = '/Account/Login?returnUrl=/FindTutor/Index';
                    return;
                }

                document.getElementById('needTutorBody').innerHTML = ''
                    + '<form class="ft-form" onsubmit="submitNeedTutor(event)">'
                    + '  <div class="form-intro"><p>Don\'t have a specific tutor in mind? Tell us your requirements and our <strong>admin team</strong> will find the best tutor for you.</p></div>'
                    + '  <div class="form-section-label">Your Information</div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Student Name <span class="req">*</span></label><input type="text" id="ntName" required /></div>'
                    + '    <div class="field"><label>Phone Number <span class="req">*</span></label><input type="tel" id="ntPhone" required placeholder="01XXXXXXXXX" maxlength="11" /></div>'
                    + '  </div>'
                    + '  <div class="field whatsapp-icon-input">'
                    + '    <label>WhatsApp Number <span class="req">*</span></label>'
                    + '    <i class="fab fa-whatsapp wa-icon"></i>'
                    + '    <input type="tel" id="ntWhatsApp" placeholder="01XXXXXXXXX" required maxlength="11" />'
                    + '    <div class="whatsapp-same-toggle">'
                    + '      <input type="checkbox" id="ntSameWhatsApp" checked onchange="toggleSameWhatsApp(\'ntPhone\',\'ntWhatsApp\',this)" />'
                    + '      <label for="ntSameWhatsApp">Same as phone number</label>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="form-section-label">Requirement</div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Student Class <span class="req">*</span></label>'
                    + '      <select id="ntClass" required>'
                    + '        <option value="">Select Class</option>'
                    + '        <option>Class 6-8</option><option>Class 9-10</option>'
                    + '        <option>HSC</option><option>Admission</option>'
                    + '        <option>University</option><option>Professional</option>'
                    + '      </select>'
                    + '    </div>'
                    + '    <div class="field"><label>Subject <span class="req">*</span></label><input type="text" id="ntSubject" required /></div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Preferred Area</label><input type="text" id="ntArea" /></div>'
                    + '    <div class="field"><label>Preferred Tutor Gender</label>'
                    + '      <select id="ntGender"><option>Any</option><option>Male</option><option>Female</option></select>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Teaching Mode</label>'
                    + '      <select id="ntMode"><option>Online</option><option>Offline</option><option selected>Both</option></select>'
                    + '    </div>'
                    + '    <div class="field"><label>Monthly Budget (৳)</label><input type="text" id="ntBudget" /></div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Preferred Days</label><input type="text" id="ntDays" /></div>'
                    + '    <div class="field"><label>Preferred Time</label><input type="text" id="ntTime" /></div>'
                    + '  </div>'
                    + '  <div class="field"><label>Additional Requirements</label><textarea id="ntNotes"></textarea></div>'
                    + '  <button type="submit" class="btn-submit" id="ntSubmitBtn"><i class="fas fa-hands-helping"></i> Send Request to Admin</button>'
                    + '</form>';

                new bootstrap.Modal(document.getElementById('needTutorModal')).show();
            }

            async function submitNeedTutor(e) {
                e.preventDefault();

                var btn = document.getElementById('ntSubmitBtn');
                var originalHtml = btn.innerHTML;

                var name = document.getElementById('ntName').value.trim();
                var phone = document.getElementById('ntPhone').value.trim();
                var wa = document.getElementById('ntWhatsApp').value.trim();

                if (!name) { alert('Please enter student name.'); return; }
                if (!isValidBdPhone(phone)) { alert('Please enter a valid phone number.'); return; }
                if (!isValidBdPhone(wa)) { alert('Please enter a valid WhatsApp number.'); return; }

                var reqId = generateRequestId();

                var fd = new FormData();
                fd.append('tutorId', '');
                fd.append('tutorName', '');
                fd.append('studentName', name);
                fd.append('phoneNumber', phone);
                fd.append('whatsAppNumber', wa);
                fd.append('email', '');
                fd.append('studentClass', document.getElementById('ntClass').value);
                fd.append('requiredSubject', document.getElementById('ntSubject').value.trim());
                fd.append('preferredArea', document.getElementById('ntArea').value.trim());
                fd.append('teachingMode', document.getElementById('ntMode').value);
                fd.append('preferredDays', document.getElementById('ntDays').value.trim());
                fd.append('preferredTime', document.getElementById('ntTime').value.trim());
                fd.append('monthlyBudget', document.getElementById('ntBudget').value.trim());
                fd.append('additionalNotes', document.getElementById('ntNotes').value.trim() +
                    '\nPreferred Tutor Gender: ' + document.getElementById('ntGender').value);
                fd.append('requestType', 'NeedTutor');
                fd.append('requestId', reqId);
                fd.append('__RequestVerificationToken', getToken());

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

                try {
                    var res = await fetch('/FindTutor/SubmitTutorRequest', { method: 'POST', body: fd });
                    var data = await res.json();

                    if (!data.success) {
                        alert('❌ ' + (data.message || 'Failed to send.'));
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                        return;
                    }

                    document.getElementById('needTutorBody').innerHTML = ''
                        + '<div class="success-panel">'
                        + '  <div class="success-icon"><i class="fas fa-check"></i></div>'
                        + '  <h4>Request Sent to Admin!</h4>'
                        + '  <p>Our admin team will review your requirements and find the best tutor for you. We\'ll contact you shortly.</p>'
                        + '  <div class="success-req-id"><span class="req-label">Request ID</span><span class="req-value">' + escapeHtml(data.requestId || reqId) + '</span></div>'
                        + '  <button class="btn-ok" onclick="closeModal(\'needTutorModal\')">OK</button>'
                        + '</div>';
                } catch (err) {
                    alert('❌ Network error: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }

            function openAreaTuitionModal() {
                // ✅ লগইন চেক
                if (!isLoggedIn) {
                    alert('রিকোয়েস্ট পাঠাতে অনুগ্রহ করে প্রথমে লগইন করুন।');
                    window.location.href = '/Account/Login?returnUrl=/FindTutor/Index';
                    return;
                }

                document.getElementById('areaTuitionBody').innerHTML = ''
                    + '<form class="ft-form" onsubmit="submitAreaTuition(event)">'
                    + '  <div class="form-intro"><p>Want a tutor <strong>in your own area</strong>? Fill in the details below and our <strong>admin team</strong> will find suitable tutors near you.</p></div>'
                    + '  <div class="form-section-label">Your Information</div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Your Name <span class="req">*</span></label><input type="text" id="atName" required /></div>'
                    + '    <div class="field"><label>Phone Number <span class="req">*</span></label><input type="tel" id="atPhone" required placeholder="01XXXXXXXXX" maxlength="11" /></div>'
                    + '  </div>'
                    + '  <div class="field whatsapp-icon-input">'
                    + '    <label>WhatsApp Number <span class="req">*</span></label>'
                    + '    <i class="fab fa-whatsapp wa-icon"></i>'
                    + '    <input type="tel" id="atWhatsApp" placeholder="01XXXXXXXXX" required maxlength="11" />'
                    + '    <div class="whatsapp-same-toggle">'
                    + '      <input type="checkbox" id="atSameWhatsApp" checked onchange="toggleSameWhatsApp(\'atPhone\',\'atWhatsApp\',this)" />'
                    + '      <label for="atSameWhatsApp">Same as phone number</label>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="form-section-label">Tuition Requirement</div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Your Area / Locality <span class="req">*</span></label><input type="text" id="atArea" required /></div>'
                    + '    <div class="field"><label>City / District</label><input type="text" id="atCity" /></div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Student Class <span class="req">*</span></label>'
                    + '      <select id="atClass" required>'
                    + '        <option value="">Select Class</option>'
                    + '        <option>Class 6-8</option><option>Class 9-10</option>'
                    + '        <option>HSC</option><option>Admission</option>'
                    + '        <option>University</option>'
                    + '      </select>'
                    + '    </div>'
                    + '    <div class="field"><label>Subject(s) <span class="req">*</span></label><input type="text" id="atSubject" required /></div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Tutor Preference</label>'
                    + '      <select id="atGender"><option>Any</option><option>Male</option><option>Female</option></select>'
                    + '    </div>'
                    + '    <div class="field"><label>Teaching Mode</label>'
                    + '      <select id="atMode"><option>Offline (Home)</option><option>Online</option><option selected>Both</option></select>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="form-row">'
                    + '    <div class="field"><label>Preferred Days</label><input type="text" id="atDays" /></div>'
                    + '    <div class="field"><label>Preferred Time</label><input type="text" id="atTime" /></div>'
                    + '  </div>'
                    + '  <div class="field"><label>Monthly Budget (৳)</label><input type="text" id="atBudget" /></div>'
                    + '  <div class="field"><label>Additional Requirements</label><textarea id="atNotes"></textarea></div>'
                    + '  <button type="submit" class="btn-submit" id="atSubmitBtn"><i class="fas fa-headset"></i> Send to Admin</button>'
                    + '  <p style="font-size:12px; color:#7A8CA0; text-align:center; margin:12px 0 0;"><i class="fas fa-shield-alt me-1"></i> Our admin team will contact you within 24 hours.</p>'
                    + '</form>';

                new bootstrap.Modal(document.getElementById('areaTuitionModal')).show();
            }

            async function submitAreaTuition(e) {
                e.preventDefault();

                var btn = document.getElementById('atSubmitBtn');
                var originalHtml = btn.innerHTML;

                var name = document.getElementById('atName').value.trim();
                var phone = document.getElementById('atPhone').value.trim();
                var wa = document.getElementById('atWhatsApp').value.trim();

                if (!name) { alert('Please enter your name.'); return; }
                if (!isValidBdPhone(phone)) { alert('Please enter a valid phone number.'); return; }
                if (!isValidBdPhone(wa)) { alert('Please enter a valid WhatsApp number.'); return; }

                var area = document.getElementById('atArea').value.trim();
                var city = document.getElementById('atCity').value.trim();
                var fullArea = area + (city ? ', ' + city : '');

                var reqId = generateRequestId();

                var fd = new FormData();
                fd.append('tutorId', '');
                fd.append('tutorName', '');
                fd.append('studentName', name);
                fd.append('phoneNumber', phone);
                fd.append('whatsAppNumber', wa);
                fd.append('email', '');
                fd.append('studentClass', document.getElementById('atClass').value);
                fd.append('requiredSubject', document.getElementById('atSubject').value.trim());
                fd.append('preferredArea', fullArea);
                fd.append('teachingMode', document.getElementById('atMode').value);
                fd.append('preferredDays', document.getElementById('atDays').value.trim());
                fd.append('preferredTime', document.getElementById('atTime').value.trim());
                fd.append('monthlyBudget', document.getElementById('atBudget').value.trim());
                fd.append('additionalNotes', document.getElementById('atNotes').value.trim() +
                    '\nTutor Preference: ' + document.getElementById('atGender').value);
                fd.append('requestType', 'AreaTuition');
                fd.append('requestId', reqId);
                fd.append('__RequestVerificationToken', getToken());

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

                try {
                    var res = await fetch('/FindTutor/SubmitTutorRequest', { method: 'POST', body: fd });
                    var data = await res.json();

                    if (!data.success) {
                        alert('❌ ' + (data.message || 'Failed to send.'));
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                        return;
                    }

                    document.getElementById('areaTuitionBody').innerHTML = ''
                        + '<div class="success-panel">'
                        + '  <div class="success-icon"><i class="fas fa-check"></i></div>'
                        + '  <h4>Request Sent Successfully!</h4>'
                        + '  <p>Our admin team will find tutors in your area and contact you soon.</p>'
                        + '  <div class="success-req-id"><span class="req-label">Request ID</span><span class="req-value">' + escapeHtml(data.requestId || reqId) + '</span></div>'
                        + '  <button class="btn-ok" onclick="closeModal(\'areaTuitionModal\')">OK</button>'
                        + '</div>';
                } catch (err) {
                    alert('❌ Network error: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }

            function findByTutorId() {
                var input = document.getElementById('tidInput');
                var id = input.value.trim().toUpperCase();
                if (!id) { alert('Please enter a Tutor ID'); return; }
                var tutor = allTutors.find(function (t) { return t.tutorId.toUpperCase() === id; });
                if (tutor) viewProfile(tutor.id);
                else alert('No tutor found with Tutor ID: ' + id);
            }

            // ============================================================
            // ===== TEACHER PROFILE FROM POST =====
            // ============================================================
            window.viewTeacherProfile = async function (userId) {
                const modalBody = document.getElementById('tutorProfileBody');
                modalBody.innerHTML = `
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading teacher profile...</p>
                                    </div>`;

                new bootstrap.Modal(document.getElementById('tutorProfileModal')).show();

                try {
                    const res = await fetch('/FindTutor/GetTeacherProfile?userId=' + userId);
                    const data = await res.json();

                    if (!data.success) {
                        modalBody.innerHTML = `<div class="alert alert-danger text-center">${data.message || 'Failed to load profile'}</div>`;
                        return;
                    }

                    const u = data.user;

                    const names = (u.fullName || 'T').split(' ');
                    const initials = names.length >= 2
                        ? (names[0][0] + names[1][0]).toUpperCase()
                        : (names[0][0] || 'T').toUpperCase();

                    const avatarContent = u.profileImageLink
                        ? `<img src="${escapeHtml(u.profileImageLink)}" alt="${escapeHtml(u.fullName)}" />`
                        : initials;

                    let html = `
                                        <div class="tp-header">
                                            <div class="tp-avatar">${avatarContent}<div class="v-tick"><i class="fas fa-check"></i></div></div>
                                            <div style="flex:1; min-width:0;">
                                                <h4 class="tp-name">${escapeHtml(u.fullName)} <i class="fas fa-check-circle" style="color:#2ecc71;"></i></h4>
                                                <div class="tp-rating-line" style="color:#64748B; font-size:13px;">
                                                    <span><i class="fas fa-envelope me-1"></i>${escapeHtml(u.email || 'Not provided')}</span>
                                                </div>
                                                <div style="margin-top:8px;">
                                                    <span style="background:#d4edda; color:#155724; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase;">Verified Teacher</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tp-stat-row">
                                            <div class="tp-stat">
                                                <div class="num" style="font-size:16px;">${escapeHtml(u.yearsOfExperience || 'N/A')}</div>
                                                <div class="lbl">Experience</div>
                                            </div>
                                            <div class="tp-stat">
                                                <div class="num" style="font-size:16px;">${escapeHtml(u.gender || 'N/A')}</div>
                                                <div class="lbl">Gender</div>
                                            </div>
                                            <div class="tp-stat">
                                                <div class="num" style="font-size:16px;">${escapeHtml(u.ageRange || 'N/A')}</div>
                                                <div class="lbl">Age</div>
                                            </div>
                                        </div>

                                        <div class="tp-block">
                                            <div class="tp-block-title"><i class="fas fa-graduation-cap"></i>Education</div>
                                            <div class="tp-info-grid">
                                                <div class="tp-info-item"><div class="label">Qualification</div><div class="value">${escapeHtml(u.educationLevel || 'Not provided')}</div></div>
                                                <div class="tp-info-item"><div class="label">Degree</div><div class="value">${escapeHtml(u.examDegreeTitle || 'Not provided')}</div></div>
                                                <div class="tp-info-item"><div class="label">Institution</div><div class="value">${escapeHtml(u.institutionName || 'Not provided')}</div></div>
                                                <div class="tp-info-item"><div class="label">Passing Year</div><div class="value">${escapeHtml(u.passingYear || 'Not provided')}</div></div>
                                            </div>
                                        </div>`;

                    if (u.mobileNumber || u.country || u.streetAddress) {
                        html += `
                                        <div class="tp-block">
                                            <div class="tp-block-title"><i class="fas fa-map-marker-alt"></i>Contact & Address</div>
                                            <div class="tp-info-grid">
                                                ${u.mobileNumber ? `<div class="tp-info-item"><div class="label">Mobile</div><div class="value">${escapeHtml(u.mobileNumber)}</div></div>` : ''}
                                                ${u.country ? `<div class="tp-info-item"><div class="label">Country</div><div class="value">${escapeHtml(u.country)}</div></div>` : ''}
                                                ${u.permanentAddress ? `<div class="tp-info-item" style="grid-column: 1/-1;"><div class="label">Permanent Address</div><div class="value">${escapeHtml(u.permanentAddress)}</div></div>` : ''}
                                            </div>
                                        </div>`;
                    }

                    let linksHtml = '';
                    if (u.cvLink) linksHtml += `<a href="${escapeHtml(u.cvLink)}" target="_blank" class="tp-chip" style="background:#e3f2fd; color:#1565c0; text-decoration:none;"><i class="fas fa-file-pdf"></i> View CV</a>`;
                    if (u.githubProfile) linksHtml += `<a href="${escapeHtml(u.githubProfile)}" target="_blank" class="tp-chip" style="background:#f5f5f5; color:#333; text-decoration:none;"><i class="fab fa-github"></i> GitHub</a>`;
                    if (u.linkedInProfile) linksHtml += `<a href="${escapeHtml(u.linkedInProfile)}" target="_blank" class="tp-chip" style="background:#e3f2fd; color:#0a66c2; text-decoration:none;"><i class="fab fa-linkedin"></i> LinkedIn</a>`;
                    if (u.portfolioLink) linksHtml += `<a href="${escapeHtml(u.portfolioLink)}" target="_blank" class="tp-chip" style="background:#fce4ec; color:#c2185b; text-decoration:none;"><i class="fas fa-globe"></i> Portfolio</a>`;

                    if (linksHtml) {
                        html += `
                                        <div class="tp-block">
                                            <div class="tp-block-title"><i class="fas fa-link"></i>Professional Links</div>
                                            <div class="tp-chips">${linksHtml}</div>
                                        </div>`;
                    }

                    // Contact CTA removed as requested

                    modalBody.innerHTML = html;

                } catch (err) {
                    modalBody.innerHTML = `<div class="alert alert-danger text-center">Error loading profile: ${err.message}</div>`;
                }
            };

            window.viewProfile = viewProfile;
            window.openRequestTutorModal = openRequestTutorModal;
            window.openNeedTutorModal = openNeedTutorModal;
            window.openAreaTuitionModal = openAreaTuitionModal;
            window.switchToRequest = switchToRequest;
            window.submitTutorRequest = submitTutorRequest;
            window.submitNeedTutor = submitNeedTutor;
            window.submitAreaTuition = submitAreaTuition;
            window.applySearch = applySearch;
            window.findByTutorId = findByTutorId;
            window.closeModal = closeModal;

            document.addEventListener('DOMContentLoaded', function () {
                applySearch();

                var tidInput = document.getElementById('tidInput');
                if (tidInput) {
                    tidInput.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') findByTutorId();
                    });
                }
            });
        })();
    </script>
<?php section_end(); ?>
