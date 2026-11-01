<?php
$ViewData['Title'] = 'Find Tutor';

$isLoggedIn = session_get('UserName') !== null;
$userRole = session_get('UserRole');
$isTeacher = $userRole === 'Teacher';
$userId = session_get('UserId');
$userFullName = session_get('UserFullName') ?? session_get('UserName') ?? 'User';
$userProfileImg = session_get('ProfileImageLink');
// হোম পেজের Subject / Class / Location কার্ড থেকে এলে (?subject=mathematics ইত্যাদি) ফিল্টার আগে থেকে বাছাই থাকে
$ftPreset = find_tutor_preset();

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

<style>
    .ft2 { --ft-navy: #0F1B2D; --ft-blue: #1D7FE5; --ft-blue-d: #1468C2; --ft-line: #E6EDF5; --ft-muted: #64748B; --ft-soft: #94A3B8;
        --ft-shadow: 0 1px 2px rgba(15,27,45,.04), 0 6px 20px rgba(15,27,45,.05); background: #F4F8FC; padding: 22px 0 70px; min-height: 80vh; color: var(--ft-navy); }
    .ft2 *, .ft2 *::before, .ft2 *::after { box-sizing: border-box; }
    .ft2 h1, .ft2 h2, .ft2 h3, .ft2 h4 { color: var(--ft-navy); font-family: inherit; }
    .ft2-wrap { max-width: 1240px; margin: 0 auto; padding: 0 24px; }
    .ft2-card { background: #fff; border: 1px solid var(--ft-line); border-radius: 16px; box-shadow: var(--ft-shadow); }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

    .ft2-banner { position: relative; margin-bottom: 24px; border-radius: 20px; overflow: hidden; background: linear-gradient(120deg, #EAF4FF, #F6FBFF 55%, #FFF6E5); box-shadow: var(--ft-shadow); }
    .ft2-banner img { display: block; width: 100%; height: auto; }
    .ft2-banner-text { padding: 36px 48px; }
    .ft2-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 16px; background: #DCEBFF; color: #1E3A8A; font-weight: 700; font-size: 14px; }
    .ft2-banner-text h1 { margin: 14px 0 0; font-size: 46px; font-weight: 800; letter-spacing: -.5px; }
    .ft2-banner-text p { margin: 8px 0 0; font-size: 19px; color: #334155; max-width: 560px; }

    .ft2-grid { display: grid; grid-template-columns: minmax(0, 1fr) 312px; gap: 24px; align-items: start; }
    .ft2-av { width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; display: inline-flex; align-items: center; justify-content: center;
        background: #0F2D4A; color: #fff; font-weight: 700; font-size: 16px; }
    .ft2-av.lg { width: 50px; height: 50px; font-size: 18px; }
    .ft2-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ft2-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; height: 44px; padding: 0 22px; border-radius: 10px; border: none; font-weight: 600;
        font-size: 15px; cursor: pointer; text-decoration: none; transition: background .15s, transform .15s; font-family: inherit; }
    .ft2-btn.blue { background: var(--ft-blue); color: #fff; }
    .ft2-btn.blue:hover { background: var(--ft-blue-d); color: #fff; }
    .ft2-btn.outline { background: #fff; color: var(--ft-blue); border: 1.5px solid var(--ft-blue); }
    .ft2-btn.outline:hover { background: #EEF5FE; color: var(--ft-blue); }
    .ft2-btn.yellow { background: #FFC21A; color: var(--ft-navy); font-weight: 700; }
    .ft2-btn.yellow:hover { background: #F5B500; color: var(--ft-navy); }
    .ft2-btn.block { width: 100%; }

    /* composer */
    .ft2-compose { padding: 20px 22px; margin-bottom: 18px; }
    .ft2-compose-h { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
    .ft2-compose-h h2 { margin: 0; font-size: 20px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
    .ft2-free { font-size: 12.5px; font-weight: 700; padding: 3px 10px; border-radius: 8px; background: #DCEBFF; color: var(--ft-blue); }
    .ft2-rules-btn { border: none; background: #F1F6FC; color: #334155; font-size: 13px; padding: 6px 12px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-family: inherit; }
    .ft2-rules-btn i { color: var(--ft-blue); }
    .ft2-compose-top { display: flex; align-items: center; gap: 14px; }
    .ft2-compose-in { flex: 1; min-width: 0; text-align: left; min-height: 52px; border-radius: 14px; border: 1px solid #E8EEF6; background: #F4F8FC; padding: 12px 18px;
        font-size: 15px; color: #64748B; cursor: pointer; font-family: inherit; }
    .ft2-compose-in:hover { background: #EEF3F9; }
    .ft2-compose-row { display: flex; align-items: center; gap: 10px; margin-top: 16px; padding-left: 64px; }
    .ft2-mbtn { border: none; background: none; display: inline-flex; align-items: center; gap: 10px; padding: 10px 18px; border-radius: 10px; font-size: 15px; color: #334155; cursor: pointer; font-family: inherit; }
    .ft2-mbtn:hover { background: #F4F8FC; }
    .ft2-mbtn i { font-size: 19px; }
    .ft2-compose-row .ft2-btn { margin-left: auto; min-width: 180px; height: 46px; }
    .ft2-note { margin-top: 14px; padding: 11px 14px; border-radius: 10px; background: #FFF8E6; color: #8A5A00; font-size: 13.5px; }
    .ft2-note a { color: var(--ft-blue); font-weight: 700; }

    /* search */
    .ft2-search { padding: 12px; display: flex; align-items: center; gap: 16px; margin-bottom: 22px; }
    .ft2-search-in { flex: 1; min-width: 0; position: relative; margin: 0; }
    .ft2-search-in i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748B; font-size: 16px; }
    .ft2-search-in input { width: 100%; height: 46px; border: 1px solid #DCE5EF; border-radius: 12px; background: #F7FAFD; padding: 0 16px 0 46px; font-size: 15px; outline: none; font-family: inherit; color: var(--ft-navy); }
    .ft2-search-in input:focus { border-color: var(--ft-blue); background: #fff; box-shadow: 0 0 0 3px rgba(29,127,229,.12); }
    .ft2-fjump { display: none; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 16px; border-radius: 10px; border: 1px solid #DCE5EF; background: #fff; color: var(--ft-navy); font-weight: 600; cursor: pointer; font-family: inherit; }
    .ft2-sort { display: inline-flex; align-items: center; gap: 10px; font-size: 15px; color: #475569; white-space: nowrap; margin: 0; }
    .ft2-sort select { height: 46px; border: 1px solid #DCE5EF; border-radius: 10px; padding: 0 36px 0 14px; font-size: 14.5px; background-color: #fff; color: var(--ft-navy); font-family: inherit; cursor: pointer; }

    /* list */
    .ft2-list-h { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 0 2px 14px; flex-wrap: wrap; }
    .ft2-list-h h2 { margin: 0; font-size: 21px; font-weight: 800; display: flex; align-items: center; gap: 12px; }
    .ft2-count { font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 12px; background: #E8F1FB; color: #1E3A5F; }
    .ft2-clear { border: none; background: #FEE2E2; color: #B91C1C; font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-family: inherit; }
    .ft2-tutor { display: grid; grid-template-columns: 110px minmax(0, 1.25fr) minmax(0, 1fr) 160px; gap: 22px; align-items: start; padding: 20px 22px; margin-bottom: 16px; position: relative; }
    .ft2-photo { position: relative; width: 104px; height: 104px; }
    .ft2-photo .pic { width: 104px; height: 104px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #0B2447, #1D63E0); color: #fff; font-size: 32px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 6px 18px rgba(15,27,45,.12); }
    .ft2-photo .pic img { width: 100%; height: 100%; object-fit: cover; }
    .ft2-photo .vt { position: absolute; right: 2px; bottom: 6px; width: 26px; height: 26px; border-radius: 50%; background: #22C55E; color: #fff; border: 3px solid #fff;
        display: flex; align-items: center; justify-content: center; font-size: 11px; }
    .ft2-name { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .ft2-name a { font-size: 19px; font-weight: 800; color: var(--ft-navy); text-decoration: none; }
    .ft2-name a:hover { color: var(--ft-blue); }
    .ft2-top { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; background: #E8A317; color: #fff; font-size: 10.5px; font-weight: 800; text-transform: uppercase; }
    .ft2-rate { display: flex; align-items: center; gap: 6px; margin-top: 4px; font-size: 15px; }
    .ft2-rate .fa-star { color: #F5A623; }
    .ft2-rate b { font-weight: 700; }
    .ft2-rate span { color: var(--ft-muted); }
    .ft2-rate .new { font-size: 12.5px; font-weight: 700; color: #0E7490; background: #E0F7FA; padding: 2px 8px; border-radius: 8px; }
    .ft2-li { display: flex; align-items: flex-start; gap: 10px; margin-top: 7px; font-size: 14.5px; color: #334155; line-height: 1.4; }
    .ft2-li i { width: 16px; text-align: center; color: #1E3A5F; margin-top: 2px; flex-shrink: 0; }
    .ft2-li.loc i { color: var(--ft-blue); }
    .ft2-li b { font-weight: 700; color: var(--ft-navy); }
    .ft2-tags { grid-column: 2 / 4; display: flex; flex-wrap: wrap; gap: 8px; margin-top: -6px; }
    .ft2-tag { padding: 5px 13px; border-radius: 14px; background: #F1F5F9; color: #334155; font-size: 13px; }
    .ft2-acts { display: flex; flex-direction: column; gap: 10px; padding-top: 30px; }
    .ft2-acts .ft2-btn { height: 40px; font-size: 14.5px; padding: 0 12px; }
    .ft2-heart { position: absolute; top: 14px; right: 16px; width: 36px; height: 36px; border-radius: 50%; border: none; background: none; color: #64748B; font-size: 19px; cursor: pointer; }
    .ft2-heart:hover { background: #F1F5F9; }
    .ft2-heart.on { color: #E5383B; }
    .ft2-empty { padding: 44px 20px; text-align: center; color: var(--ft-muted); }
    .ft2-empty i.big { font-size: 42px; color: #CBD5E1; display: block; margin-bottom: 12px; }
    .ft2-empty h4 { margin: 0 0 6px; font-weight: 700; }
    .ft2-more { display: block; width: 100%; padding: 13px; border-radius: 12px; background: #fff; border: 1px solid var(--ft-line); color: var(--ft-blue); font-weight: 700; cursor: pointer; font-family: inherit; }

    /* sidebar */
    .ft2-side { display: flex; flex-direction: column; gap: 18px; position: sticky; top: 96px; }
    .ft2-area { padding: 24px 22px; background: radial-gradient(120% 90% at 100% 0%, #1E4A7A 0%, rgba(30,74,122,0) 60%), linear-gradient(160deg, #0F2D4A 0%, #0B2238 100%); border: none; color: #fff; }
    .ft2-pin { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,.1); display: inline-flex; align-items: center; justify-content: center; color: #FFC21A; font-size: 20px; }
    .ft2-area h3 { color: #fff !important; margin: 16px 0 10px; font-size: 20px; font-weight: 800; }
    .ft2-area p { color: #D3DFEC; font-size: 14.5px; line-height: 1.6; margin: 0 0 18px; }
    .ft2-qf { padding: 20px; }
    .ft2-qf h3, .ft2-why h3 { margin: 0 0 16px; font-size: 19px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
    .ft2-qf h3 i { color: var(--ft-blue); }
    .ft2-fg { margin-bottom: 12px; }
    .ft2-fg label { display: block; font-size: 14px; color: #334155; margin-bottom: 6px; }
    .ft2-fg select, .ft2-fg input { width: 100%; height: 42px; border: 1px solid #DCE5EF; border-radius: 8px; padding: 0 12px; font-size: 14px; background-color: #fff; color: var(--ft-navy); font-family: inherit; outline: none; }
    .ft2-fg select:focus, .ft2-fg input:focus { border-color: var(--ft-blue); }
    .ft2-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ft2-why { padding: 20px; }
    .ft2-why h3 i { color: #7C3AED; }
    .ft2-why ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
    .ft2-why li { display: flex; align-items: center; gap: 12px; font-size: 15px; color: #1F2937; }
    .ft2-why li i { color: #22A35A; font-size: 19px; }
    .ft2-tid { padding: 16px 20px; }
    .ft2-tid h4 { margin: 0 0 10px; font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .ft2-tid-in { display: flex; gap: 8px; }
    .ft2-tid-in input { flex: 1; min-width: 0; height: 40px; border: 1px solid #DCE5EF; border-radius: 8px; padding: 0 12px; font-family: monospace; }
    .ft2-tid-in button { width: 44px; border: none; border-radius: 8px; background: var(--ft-navy); color: #fff; cursor: pointer; }
    .ft2-rules { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
    .ft2-rules li { display: flex; gap: 10px; font-size: 14.5px; line-height: 1.55; color: #334155; }
    .ft2-rules li i { color: #22A35A; margin-top: 4px; }

    /* টিচারদের পোস্ট — নতুন কার্ড স্টাইলে */
    .ft2 .post-feed .fb-post { border: 1px solid var(--ft-line); border-radius: 16px; box-shadow: var(--ft-shadow); }
    .ft2 .fb-post-media video { display: block; width: 100%; max-height: 460px; background: #000; }

    @media (max-width: 1100px) {
        .ft2-tutor { grid-template-columns: 96px minmax(0, 1fr) 150px; }
        .ft2-tutor .ft2-mid { grid-column: 2; }
        .ft2-tutor .ft2-acts { grid-row: 1 / span 2; grid-column: 3; }
        .ft2-tags { grid-column: 2 / 3; margin-top: 0; }
        .ft2-photo, .ft2-photo .pic { width: 92px; height: 92px; }
    }
    @media (max-width: 992px) {
        .ft2-wrap { padding: 0 14px; }
        .ft2-grid { grid-template-columns: minmax(0, 1fr); }
        .ft2-side { position: static; display: grid; grid-template-columns: 1fr 1fr; }
        .ft2-fjump { display: inline-flex !important; }
        .ft2-why, .ft2-tid { display: none; }
        .ft2-banner-text h1 { font-size: 32px; }
    }
    @media (max-width: 640px) {
        .ft2 { padding-top: 12px; }
        .ft2-wrap { padding: 0 10px; }
        .ft2-banner { border-radius: 14px; margin-bottom: 14px; }
        .ft2-banner img { height: 150px; object-fit: cover; object-position: left center; }
        .ft2-banner-text { padding: 24px 20px; }
        .ft2-side { grid-template-columns: minmax(0, 1fr); }
        .ft2-compose { padding: 16px; }
        .ft2-compose-h h2 { font-size: 17px; }
        .ft2-compose-row { padding-left: 0; flex-wrap: wrap; }
        .ft2-compose-row .ft2-btn { width: 100%; margin-left: 0; }
        .ft2-search { flex-direction: column; align-items: stretch; gap: 10px; }
        .ft2-sort select { flex: 1; }
        .ft2-list-h h2 { font-size: 18px; }
        .ft2-tutor { grid-template-columns: 72px minmax(0, 1fr); gap: 12px 14px; padding: 16px; }
        .ft2-photo, .ft2-photo .pic { width: 72px; height: 72px; font-size: 24px; }
        .ft2-photo .vt { width: 22px; height: 22px; font-size: 9px; right: 0; bottom: 2px; }
        .ft2-tutor .ft2-mid { grid-column: 1 / -1; }
        .ft2-tags { grid-column: 1 / -1; }
        .ft2-tutor .ft2-acts { grid-column: 1 / -1; grid-row: auto; flex-direction: row; padding-top: 0; }
        .ft2-tutor .ft2-acts .ft2-btn { flex: 1; }
        .ft2-name a { font-size: 17px; }
    }
</style>

<?php
$ftBanner = findtutor_banner();
$savedTutors = $ViewBag->SavedTutors ?? [];
$ftLogin = '/Account/Login?returnUrl=' . rawurlencode('/FindTutor');
?>
<section class="ft2">
    <div class="ft2-wrap">
        <!-- ======================= BANNER ======================= -->
        <div class="ft2-banner">
            <?php if ($ftBanner['ft_banner_enabled'] !== '0' && !is_blank($ftBanner['ft_banner'])): ?>
                <h1 class="sr-only">Find Your Perfect Tutor</h1>
                <img src="<?= e($ftBanner['ft_banner']) ?>" alt="Find Your Perfect Tutor — আপনার শেখার লক্ষ্যে পৌঁছাতে, সঠিক শিক্ষক খুঁজে নিন মাষ্টারমশাইয়ের সাথে।"
                     onerror="var fb = this.parentNode.querySelector('.ft2-banner-text'); if (fb) fb.hidden = false; this.remove();" />
            <?php endif; ?>
            <div class="ft2-banner-text"<?= $ftBanner['ft_banner_enabled'] !== '0' && !is_blank($ftBanner['ft_banner']) ? ' hidden' : '' ?>>
                <span class="ft2-pill"><i class="fas fa-shield-halved"></i> Find Tutor</span>
                <h1>Find Your Perfect Tutor</h1>
                <p>আপনার শেখার লক্ষ্যে পৌঁছাতে, সঠিক শিক্ষক খুঁজে নিন মাষ্টারমশাইয়ের সাথে।</p>
            </div>
        </div>

        <div class="ft2-grid">
            <main class="ft2-main">
                <!-- ===== পড়াতে চাই (Teach Wanted) ===== -->
                <div class="ft2-card ft2-compose">
                    <div class="ft2-compose-h">
                        <h2><i class="fas fa-pen"></i> পড়াতে চাই (Teacher Wanted) <span class="ft2-free">Free</span></h2>
                        <button type="button" class="ft2-rules-btn" data-bs-toggle="modal" data-bs-target="#postRulesModal"><i class="far fa-clock"></i> পোস্ট করার নিয়ম</button>
                    </div>
                    <div class="ft2-compose-top">
                        <span class="ft2-av lg"><?= e($isLoggedIn ? $firstUpper($userFullName, 'T') : 'M') ?><?php if ($isLoggedIn && !is_blank($userProfileImg)): ?><img src="<?= e($userProfileImg) ?>" alt="" onerror="this.remove()" /><?php endif; ?></span>
                        <button type="button" class="ft2-compose-in" data-ft-compose="text"><?= $isTeacher || !$isLoggedIn ? 'আপনার টিউশনের প্রয়োজনের বিস্তারিত লিখুন...' : 'শুধু টিচাররা এখানে পোস্ট করতে পারবেন' ?></button>
                    </div>
                    <div class="ft2-compose-row">
                        <button type="button" class="ft2-mbtn" data-ft-compose="photo"><i class="fas fa-image" style="color:#22A35A;"></i> Photo</button>
                        <button type="button" class="ft2-mbtn" data-ft-compose="video"><i class="fas fa-video" style="color:#E5383B;"></i> Video</button>
                        <button type="button" class="ft2-btn blue" data-ft-compose="post"><i class="fas fa-paper-plane"></i> পোস্ট করুন</button>
                    </div>
                    <?php if ($isLoggedIn && !$isTeacher && $userRole !== 'Admin'): ?>
                        <div class="ft2-note"><i class="fas fa-circle-info"></i> শুধু টিচাররা এখানে পোস্ট করতে পারবেন। টিচার হতে <a href="/Profile?section=become-tutor">এখানে আবেদন করুন</a>।</div>
                    <?php elseif (!$isLoggedIn): ?>
                        <div class="ft2-note"><i class="fas fa-circle-info"></i> পোস্ট করতে <a href="<?= e($ftLogin) ?>">লগইন করুন</a>।</div>
                    <?php endif; ?>
                </div>

                <!-- ===== সার্চ + সর্ট ===== -->
                <div class="ft2-card ft2-search">
                    <label class="ft2-search-in"><i class="fas fa-magnifying-glass"></i>
                        <input type="search" id="ftSearch" placeholder="বিষয়, ক্লাস, বা টিউটরের নাম দিয়ে খুঁজুন..." autocomplete="off" />
                    </label>
                    <button type="button" class="ft2-fjump" onclick="document.querySelector('.ft2-qf').scrollIntoView({ behavior: 'smooth', block: 'start' })"><i class="fas fa-filter"></i> Filters</button>
                    <label class="ft2-sort">Sort by:
                        <select id="sortSelect">
                            <option value="recommended">প্রস্তাবিত</option>
                            <option value="rating">সেরা রেটিং</option>
                            <option value="experience">বেশি অভিজ্ঞতা</option>
                            <option value="fee-low">কম বেতন</option>
                            <option value="fee-high">বেশি বেতন</option>
                            <option value="new">নতুন টিউটর</option>
                        </select>
                    </label>
                </div>

                <!-- ===== টিউটর তালিকা ===== -->
                <div class="ft2-list-h">
                    <h2><i class="fas fa-user-group"></i> আমাদের টিউটর (Our Tutors) <span class="ft2-count" id="countBadge">0 জন</span></h2>
                    <button type="button" class="ft2-clear" id="ftClear" hidden><i class="fas fa-xmark"></i> ফিল্টার মুছুন</button>
                </div>
                <div id="tutorList"></div>
                <button type="button" class="ft2-more" id="ftMore" hidden><i class="fas fa-angles-down"></i> আরো টিউটর দেখুন</button>

                <!-- ===== টিচারদের পোস্ট ===== -->
                <div class="ft2-list-h" style="margin-top:30px;">
                    <h2><i class="fas fa-chalkboard-user"></i> টিচারদের পোস্ট <span class="ft2-count" id="postCountBadge"><?= count($posts) ?>টি</span></h2>
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
                                <?php if (!is_blank($post->VideoUrl ?? null)): ?>
                                    <video src="<?= e($post->VideoUrl) ?>" controls preload="metadata" playsinline></video>
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
                                        <div class="fbc" id="fbc-<?= e($comment->Id) ?>" data-id="<?= e($comment->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($comment->UserFullName) ?>" data-raw="<?= e($comment->Content) ?>">
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
                                                        <button type="button" onclick="fbcOpenReply(<?= (int)$post->Id ?>, <?= (int)$comment->Id ?>, <?= (int)$comment->Id ?>)">Reply</button><?= feed_comment_buttons($comment, (int)$post->UserId) ?>
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
                                                            <div class="fbc is-reply" id="fbc-<?= e($reply->Id) ?>" data-id="<?= e($reply->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($reply->UserFullName) ?>" data-raw="<?= e($reply->Content) ?>">
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
                                                                            <button type="button" onclick="fbcOpenReply(<?= (int)$post->Id ?>, <?= (int)$comment->Id ?>, <?= (int)$reply->Id ?>)">Reply</button><?= feed_comment_buttons($reply, (int)$post->UserId) ?>
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
            </main>

            <!-- ======================= RIGHT ======================= -->
            <aside class="ft2-side">
                <div class="ft2-card ft2-area">
                    <span class="ft2-pin"><i class="fas fa-location-dot"></i></span>
                    <h3>Need a Tutor in Your Area?</h3>
                    <p>Can't find the right tutor? Tell us your requirements and our admin will help you find a suitable tutor.</p>
                    <button type="button" class="ft2-btn yellow block" onclick="openAreaTuitionModal()"><i class="fas fa-headset"></i> Contact Admin</button>
                </div>

                <div class="ft2-card ft2-qf" id="ftFilters" style="scroll-margin-top:100px;">
                    <h3><i class="fas fa-filter"></i> Quick Filters</h3>
                    <div class="ft2-fg"><label for="qfClass">Class / Level</label><select id="qfClass"><option value="">Select class</option><?php if (isset($ftPreset['class'])): ?><option value="<?= e($ftPreset['class'][0]) ?>" selected><?= e($ftPreset['class'][1]) ?></option><?php endif; ?><?php foreach (array_merge(tuition_options()['classes'], ['Bangla Medium', 'English Medium']) as $o): ?><option value="<?= e($o) ?>"><?= e($o) ?></option><?php endforeach; ?></select></div>
                    <div class="ft2-fg"><label for="qfSubject">Subject</label><select id="qfSubject"><option value="">Select subject</option><?php if (isset($ftPreset['subject'])): ?><option value="<?= e($ftPreset['subject'][0]) ?>" selected><?= e($ftPreset['subject'][1]) ?></option><?php endif; ?><?php foreach (tuition_options()['subjects'] as $o): ?><option value="<?= e($o) ?>"><?= e($o) ?></option><?php endforeach; ?></select></div>
                    <div class="ft2-fg"><label for="qfLocation">Location</label><select id="qfLocation"><option value="">Select location</option><?= location_select_options($ftPreset['location'][0] ?? '') ?></select></div>
                    <div class="ft2-fg"><label for="qfMode">Teaching Mode</label>
                        <select id="qfMode"><option value="">Select mode</option><option value="Online">Online</option><option value="Home">Home Visit</option></select></div>
                    <div class="ft2-fg"><label>Budget (৳)</label>
                        <div class="ft2-2"><input type="number" id="qfMin" min="0" placeholder="Min" inputmode="numeric" /><input type="number" id="qfMax" min="0" placeholder="Max" inputmode="numeric" /></div></div>
                    <button type="button" class="ft2-btn blue block" id="qfApply"><i class="fas fa-magnifying-glass"></i> Apply Filters</button>
                </div>

                <div class="ft2-card ft2-why">
                    <h3><i class="fas fa-shield-halved"></i> Why Choose Mashter Moshai?</h3>
                    <ul>
                        <li><i class="fas fa-circle-check"></i> Verified Tutors</li>
                        <li><i class="fas fa-circle-check"></i> Safe &amp; Secure</li>
                        <li><i class="fas fa-circle-check"></i> Wide Subject Coverage</li>
                        <li><i class="fas fa-circle-check"></i> Dedicated Support</li>
                    </ul>
                </div>

                <div class="ft2-card ft2-tid">
                    <h4><i class="fas fa-id-badge"></i> Tutor ID আছে?</h4>
                    <div class="ft2-tid-in"><input type="text" id="tidInput" placeholder="TCH-1024" /><button type="button" onclick="findByTutorId()" aria-label="Find"><i class="fas fa-search"></i></button></div>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- পোস্ট করার নিয়ম -->
<div class="modal fade" id="postRulesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="far fa-clock me-1" style="color:#1D7FE5;"></i> পোস্ট করার নিয়ম</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <ul class="ft2-rules">
                    <li><i class="fas fa-check"></i> শুধু যাচাইকৃত টিচাররা পোস্ট করতে পারবেন — পোস্ট করা সম্পূর্ণ ফ্রি।</li>
                    <li><i class="fas fa-check"></i> কোন বিষয়, কোন ক্লাস, কোন এলাকায় ও কীভাবে (অনলাইন/বাসায়) পড়াতে চান তা পরিষ্কার লিখুন।</li>
                    <li><i class="fas fa-check"></i> ছবি ৫ MB আর ভিডিও ২৫ MB পর্যন্ত দেওয়া যাবে।</li>
                    <li><i class="fas fa-xmark" style="color:#E5383B;"></i> ফোন নম্বর, অশালীন কথা বা অন্য সাইটের বিজ্ঞাপন দেওয়া যাবে না — অ্যাডমিন সরিয়ে দেবেন।</li>
                    <li><i class="fas fa-check"></i> স্টুডেন্টরা "Request Tutor" দিয়ে অ্যাডমিনের মাধ্যমে আপনার সাথে যোগাযোগ করবেন।</li>
                </ul>
            </div>
        </div>
    </div>
</div>


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
                <div class="modal-preview" id="videoPreviewBox">
                    <button type="button" class="remove-preview" onclick="removePostVideo()" style="z-index:2;"><i class="fas fa-times"></i></button>
                    <video id="videoPreviewEl" controls preload="metadata" style="width:100%;max-height:260px;border-radius:12px;background:#000;"></video>
                </div>

                <div class="modal-actions-bottom">
                    <span style="font-size:14px; font-weight:600; color:#08224A;">Add to your post</span>
                    <div>
                        <label for="postImageInput" class="icon-btn" style="cursor:pointer; margin:0;">
                            <i class="fas fa-images" style="color:#2ecc71;"></i>
                        </label>
                        <input type="file" id="postImageInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="previewPostImage(this)" />
                        <label for="postVideoInput" class="icon-btn" style="cursor:pointer; margin:0;" title="Video (25 MB)">
                            <i class="fas fa-video" style="color:#E5383B;"></i>
                        </label>
                        <input type="file" id="postVideoInput" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v" style="display:none;" onchange="previewPostVideo(this)" />
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
            removePostImage(); removePostVideo();
            checkPostBtn();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('createPostModal')).show();
        }

        function checkPostBtn() {
            var content = document.getElementById('postContentInput').value.trim();
            var btn = document.getElementById('submitPostBtn');
            var hasMedia = document.getElementById('postImageInput').files.length > 0 || document.getElementById('postVideoInput').files.length > 0;
            btn.disabled = (content.length === 0 && !hasMedia);
        }

        var postVideoObj = null;
        function previewPostVideo(input) {
            var f = input.files && input.files[0]; if (!f) return;
            if (!/\.(mp4|webm|mov|m4v)$/i.test(f.name)) { alert('শুধু MP4, WEBM বা MOV ভিডিও।'); input.value = ''; return; }
            if (f.size > 25 * 1024 * 1024) { alert('ভিডিও ২৫ MB এর কম হতে হবে।'); input.value = ''; return; }
            removePostImage();
            if (postVideoObj) URL.revokeObjectURL(postVideoObj);
            postVideoObj = URL.createObjectURL(f);
            document.getElementById('videoPreviewEl').src = postVideoObj;
            document.getElementById('videoPreviewBox').style.display = 'block';
            checkPostBtn();
        }
        function removePostVideo() {
            var v = document.getElementById('postVideoInput'); if (v) v.value = '';
            var b = document.getElementById('videoPreviewBox'); if (b) b.style.display = 'none';
            var el = document.getElementById('videoPreviewEl'); if (el) el.removeAttribute('src');
            if (postVideoObj) { URL.revokeObjectURL(postVideoObj); postVideoObj = null; }
            if (document.getElementById('submitPostBtn')) checkPostBtn();
        }

        // "পড়াতে চাই" বক্সের বাটন
        document.addEventListener('click', function (e) {
            var b = e.target.closest('[data-ft-compose]'); if (!b) return;
            var IS_LOGGED = <?= js($isLoggedIn) ?>, IS_TEACHER = <?= js($isTeacher) ?>;
            if (!IS_LOGGED) { location.href = '/Account/Login?returnUrl=%2FFindTutor'; return; }
            if (!IS_TEACHER) { if (confirm('শুধু টিচাররা এখানে পোস্ট করতে পারবেন। টিচার হিসেবে আবেদন করতে চান?')) location.href = '/Profile?section=become-tutor'; return; }
            var what = b.getAttribute('data-ft-compose');
            openPostModal();
            if (what === 'photo') setTimeout(function () { document.getElementById('postImageInput').click(); }, 250);
            else if (what === 'video') setTimeout(function () { document.getElementById('postVideoInput').click(); }, 250);
            else setTimeout(function () { document.getElementById('postContentInput').focus(); }, 350);
        });

        function previewPostImage(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                if (file.size > 5 * 1024 * 1024) { alert('Image must be less than 5MB'); return; }

                var reader = new FileReader();
                removePostVideo();
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
            var vInput = document.getElementById('postVideoInput');
            if (vInput && vInput.files.length > 0) formData.append('videoFile', vInput.files[0]);

            var token = document.querySelector('input[name="__RequestVerificationToken"]').value;
            formData.append('__RequestVerificationToken', token);

            try {
                var res = await fetch('/FindTutor/CreateTeachPost', { method: 'POST', body: formData });
                var data = null; try { data = await res.json(); } catch (x) { data = { success: false, message: res.status === 413 ? 'ফাইল অনেক বড় — ছোট ফাইল দিন।' : 'সার্ভার থেকে উত্তর পাওয়া যায়নি।' }; }

                if (data.success) {
                    location.reload(); // Reload to show the new post
                } else if (data.needPackage) {
                    if (confirm(data.message + '\n\nএখনই প্যাকেজ পেজে যাবেন?')) { location.href = data.needPackage; return; }
                    btn.innerHTML = originalText;
                    btn.disabled = false;
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
                + '<div class="fbc' + (isReply ? ' is-reply' : '') + '" id="fbc-' + c.id + '" data-id="' + c.id + '" data-root="' + rootId + '" data-name="' + fbcEsc(c.userFullName) + '" data-raw="' + fbcEsc(c.content) + '">'
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
                + '      <button type="button" data-cm-edit>Edit</button><button type="button" data-cm-del class="fbc-del">Delete</button>'
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

            // ===== টিউটর কার্ড (নতুন ডিজাইন) =====
            var SAVED = <?= js(array_map(fn($k) => (int)substr($k, 6), array_keys($savedTutors))) ?>;
            var CAN_SAVE = <?= js($isLoggedIn && $userRole !== 'Admin') ?>;
            var MY_ID = <?= js((int)($userId ?? 0)) ?>;
            var BLUE_TICK = <?= js(blue_tick_html()) ?>;
            var MODE_LBL = { Online: 'Online', Home: 'Home Visit', Both: 'Online, Home Visit' };
            function money(n) { return '৳' + Number(n).toLocaleString('en-US'); }
            function feeText(t) {
                if (!t.monthlyFee) return 'আলোচনা সাপেক্ষে';
                return money(t.monthlyFee) + (t.feeMax ? '-' + Number(t.feeMax).toLocaleString('en-US') : '+') + ' / month';
            }
            function createRow(t) {
                var avatar = (t.photoUrl && t.photoUrl.trim() !== '')
                    ? '<img src="' + escapeHtml(t.photoUrl) + '" alt="' + escapeHtml(t.fullName) + '" loading="lazy" onerror="this.remove()" />' : escapeHtml(t.initials);
                var subj = (t.subjects || []).slice(0, 2).join(', ');
                var lv = (t.levels || []).slice(0, 2).join(', ');
                var rating = t.totalReviews
                    ? '<i class="fas fa-star"></i> <b>' + Number(t.rating).toFixed(1) + '</b> <span>(' + t.totalReviews + ' review' + (t.totalReviews === 1 ? '' : 's') + ')</span>'
                    : '<span class="new">নতুন টিউটর</span>';
                var tags = (t.levels || []).concat(t.mediums || []).slice(0, 5).map(function (x) { return '<span class="ft2-tag">' + escapeHtml(x) + '</span>'; }).join('');
                var saved = SAVED.indexOf(t.userId) > -1;
                var heart = CAN_SAVE && t.userId !== MY_ID
                    ? '<button type="button" class="ft2-heart' + (saved ? ' on' : '') + '" data-save-type="tutor" data-save-id="' + t.userId + '" title="Save tutor" aria-label="Save tutor"><i class="' + (saved ? 'fas' : 'far') + ' fa-heart"></i></button>'
                    : (!isLoggedIn ? '<a class="ft2-heart" href="/Account/Login?returnUrl=%2FFindTutor" title="Save করতে লগইন করুন" aria-label="Save tutor"><i class="far fa-heart"></i></a>' : '');
                return ''
                    + '<div class="ft2-card ft2-tutor" data-uid="' + t.userId + '">'
                    + heart
                    + '  <div class="ft2-photo"><a class="pic" href="/Profile/Show/' + t.userId + '">' + avatar + '</a>' + (t.isVerified ? '<span class="vt" title="Verified Tutor"><i class="fas fa-check"></i></span>' : '') + '</div>'
                    + '  <div class="ft2-info">'
                    + '    <div class="ft2-name"><a href="/Profile/Show/' + t.userId + '">' + escapeHtml(t.fullName) + (t.isPremium ? BLUE_TICK : '') + '</a>' + (t.isTopRated ? '<span class="ft2-top"><i class="fas fa-crown"></i> Top</span>' : '') + '</div>'
                    + '    <div class="ft2-rate">' + rating + '</div>'
                    + '    <div class="ft2-li"><i class="fas fa-graduation-cap"></i><span>' + escapeHtml(subj || 'General') + (lv ? ' (' + escapeHtml(lv) + ')' : '') + '</span></div>'
                    + '    <div class="ft2-li"><i class="fas fa-briefcase"></i><span>' + (t.experienceYears ? t.experienceYears + '+ years experience' : 'নতুন শিক্ষক') + '</span></div>'
                    + '  </div>'
                    + '  <div class="ft2-mid">'
                    + '    <div class="ft2-li loc"><i class="fas fa-location-dot"></i><span>' + escapeHtml(t.location) + '</span></div>'
                    + '    <div class="ft2-li loc"><i class="fas fa-laptop-house"></i><span>' + escapeHtml(MODE_LBL[t.teachingMode] || 'Online, Home Visit') + '</span></div>'
                    + '    <div class="ft2-li"><i class="fas fa-bangladeshi-taka-sign"></i><b>' + feeText(t) + '</b></div>'
                    + '  </div>'
                    + '  <div class="ft2-acts">'
                    + '    <a class="ft2-btn outline" href="/Profile/Show/' + t.userId + '">View Profile</a>'
                    + '    <button type="button" class="ft2-btn blue" onclick="openRequestTutorModal(' + t.id + ')"><i class="fas fa-paper-plane"></i> Request Tutor</button>'
                    + '  </div>'
                    + (tags ? '  <div class="ft2-tags">' + tags + '</div>' : '')
                    + '</div>';
            }

            var PAGE = 10, shown = PAGE, lastList = [];
            function renderList(tutors) {
                var wrap = document.getElementById('tutorList');
                document.getElementById('countBadge').textContent = tutors.length + ' জন';
                lastList = tutors;
                var more = document.getElementById('ftMore');
                if (!tutors || tutors.length === 0) {
                    more.hidden = true;
                    wrap.innerHTML = '<div class="ft2-card ft2-empty"><i class="fas fa-user-slash big"></i><h4>কোনো টিউটর পাওয়া যায়নি</h4>'
                        + '<p>অন্যভাবে খুঁজে দেখুন, অথবা অ্যাডমিনকে বলুন — তিনি আপনার জন্য টিউটর খুঁজে দেবেন।</p>'
                        + '<button type="button" class="ft2-btn blue" onclick="openNeedTutorModal()" style="margin-top:12px;"><i class="fas fa-hands-helping"></i> Request a Tutor</button></div>';
                    return;
                }
                wrap.innerHTML = tutors.slice(0, shown).map(createRow).join('');
                more.hidden = tutors.length <= shown;
            }

            // ===== ফিল্টার (সার্চ + Quick Filters) =====
            function norm(s) { return String(s || '').toLowerCase(); }
            function uniq(arr) { var seen = {}, out = []; arr.forEach(function (x) { var k = norm(x); if (x && !seen[k]) { seen[k] = 1; out.push(x); } }); return out.sort(function (a, b) { return a.localeCompare(b); }); }
            // তালিকায় নেই এমন মান (টিউটরের প্রোফাইল থেকে) শেষে যোগ হয়
            function fillSelect(id, values) {
                var sel = document.getElementById(id); if (!sel) return;
                var have = {}; Array.prototype.forEach.call(sel.options, function (o) { have[norm(o.value)] = 1; });
                values.forEach(function (v) { if (!v || have[norm(v)]) return; have[norm(v)] = 1; var o = document.createElement('option'); o.value = v; o.textContent = v; sel.appendChild(o); });
            }
            fillSelect('qfClass', uniq([].concat.apply([], allTutors.map(function (t) { return (t.levels || []).concat(t.mediums || []); }))));
            fillSelect('qfSubject', uniq([].concat.apply([], allTutors.map(function (t) { return t.subjects || []; }))).filter(function (x) { return x !== 'General'; }));


            var QF = {};
            var LOC_AREAS = <?= js((object)tuition_options()['locations']) ?>;
            // একটা ফিল্টারে কয়েকটা মান থাকতে পারে ("SSC|Class 9|Class 10") — যেকোনোটা মিললেই হবে
            function anyIn(list, val) { var vs = String(val).split('|').map(norm); return list.some(function (x) { return vs.indexOf(norm(x)) !== -1; }); }
            function readQF() {
                QF = {
                    cls: document.getElementById('qfClass').value, subject: document.getElementById('qfSubject').value,
                    location: document.getElementById('qfLocation').value, mode: document.getElementById('qfMode').value,
                    min: parseInt(document.getElementById('qfMin').value, 10) || 0, max: parseInt(document.getElementById('qfMax').value, 10) || 0
                };
            }
            function matches(t) {
                var q = norm(document.getElementById('ftSearch').value).trim();
                if (q) {
                    var hay = norm([t.fullName, t.tutorId, t.headline, t.location, (t.subjects || []).join(' '), (t.levels || []).join(' '), (t.mediums || []).join(' '), t.institution].join(' '));
                    if (q.split(/\s+/).some(function (w) { return hay.indexOf(w) === -1; })) return false;
                }
                if (QF.cls && !anyIn((t.levels || []).concat(t.mediums || []), QF.cls)) return false;
                if (QF.subject && !anyIn(t.subjects || [], QF.subject)) return false;
                if (QF.location) {
                    // জেলা বাছাই করলে ওই জেলার সব এলাকাও মিলবে
                    var lv = [QF.location].concat(LOC_AREAS[QF.location] || []).map(norm), tl = norm(t.location);
                    if (!lv.some(function (x) { return x && tl.indexOf(x) !== -1; })) return false;
                }
                if (QF.mode && t.teachingMode !== QF.mode && t.teachingMode !== 'Both') return false;
                var lo = t.monthlyFee || 0, hi = t.feeMax || t.monthlyFee || 0;
                if ((QF.min || QF.max) && !lo) return false;
                if (QF.max && lo > QF.max) return false;
                if (QF.min && hi < QF.min) return false;
                return true;
            }
            function applySearch(keepShown) {
                var sort = document.getElementById('sortSelect').value;
                var list = allTutors.filter(matches);
                switch (sort) {
                    case 'recommended': list.sort(function (a, b) { return ((b.isPremium ? 1 : 0) - (a.isPremium ? 1 : 0)) || (b.rating - a.rating) || (b.totalReviews - a.totalReviews) || (b.id - a.id); }); break;
                    case 'rating': list.sort(function (a, b) { return (b.rating - a.rating) || (b.totalReviews - a.totalReviews); }); break;
                    case 'fee-low': list.sort(function (a, b) { return (a.monthlyFee || 1e9) - (b.monthlyFee || 1e9); }); break;
                    case 'fee-high': list.sort(function (a, b) { return (b.monthlyFee || 0) - (a.monthlyFee || 0); }); break;
                    case 'experience': list.sort(function (a, b) { return b.experienceYears - a.experienceYears; }); break;
                    case 'new': list.sort(function (a, b) { return b.id - a.id; }); break;
                }
                if (keepShown !== true) shown = PAGE;
                var active = !!(document.getElementById('ftSearch').value.trim() || QF.cls || QF.subject || QF.location || QF.mode || QF.min || QF.max);
                document.getElementById('ftClear').hidden = !active;
                renderList(list);
            }
            document.getElementById('ftMore').addEventListener('click', function () { shown += PAGE; renderList(lastList); });
            var stT = null;
            document.getElementById('ftSearch').addEventListener('input', function () { clearTimeout(stT); stT = setTimeout(function () { applySearch(); }, 200); });
            document.getElementById('sortSelect').addEventListener('change', function () { applySearch(); });
            document.getElementById('qfApply').addEventListener('click', function () {
                readQF(); applySearch();
                document.getElementById('countBadge').closest('.ft2-list-h').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            document.getElementById('ftClear').addEventListener('click', function () {
                document.getElementById('ftSearch').value = '';
                ['qfClass', 'qfSubject', 'qfLocation', 'qfMode', 'qfMin', 'qfMax'].forEach(function (id) { document.getElementById(id).value = ''; });
                readQF(); applySearch();
            });
            // ❤️ সেভ হলে আইকন বদলায় (সেভ করার কাজ লেআউটের কমন হ্যান্ডলার করে)
            document.addEventListener('saved:changed', function (e) {
                if (e.detail.type !== 'tutor') return;
                var id = parseInt(e.detail.id, 10), i = SAVED.indexOf(id);
                if (e.detail.saved && i === -1) SAVED.push(id); else if (!e.detail.saved && i > -1) SAVED.splice(i, 1);
                document.querySelectorAll('.ft2-heart[data-save-id="' + id + '"]').forEach(function (b) {
                    b.classList.toggle('on', e.detail.saved); var ic = b.querySelector('i'); if (ic) ic.className = (e.detail.saved ? 'fas' : 'far') + ' fa-heart';
                });
            });
            readQF();

            function viewProfile(id) {
                var t = allTutors.find(function (x) { return x.id === id; });
                if (!t) return;

                var verifiedIcon = t.isPremium ? BLUE_TICK : (t.isVerified
                    ? '<i class="fas fa-check-circle" style="color:#2ecc71;"></i>' : '');
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

                    // ✅ পুরো প্রোফাইল + Add Friend (+ টিউটর Save)
                    if (u.canSave) html += `
                                        <button type="button" data-save-type="tutor" data-save-id="${encodeURIComponent(u.id)}" class="${u.isSaved ? 'on' : ''}"
                                            style="display:flex; align-items:center; justify-content:center; gap:8px; width:100%; margin-top:16px; padding:11px; border-radius:12px; border:1px solid #DCE6F0; background:#fff; color:#08224A; font-weight:700; cursor:pointer;">
                                            <i class="${u.isSaved ? 'fas' : 'far'} fa-bookmark"></i> <span>${u.isSaved ? 'Saved' : 'Save'}</span>
                                        </button>`;
                    html += `
                                        <a href="/Profile/Show/${encodeURIComponent(u.id)}" style="display:flex; align-items:center; justify-content:center; gap:8px; margin-top:16px; padding:12px; border-radius:12px; background:#02B8F5; color:#fff; font-weight:700; text-decoration:none;">
                                            <i class="fas fa-user-plus"></i> Full Profile &amp; Add Friend
                                        </a>`;

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
<?php partial('Shared/_FeedCommentEdit', ['prefix' => 'fbc-', 'editUrl' => '/FindTutor/EditTeachComment', 'delUrl' => '/FindTutor/DeleteTeachComment', 'countPrefix' => 'comment-count-', 'countSuffix' => ' Comments', 'postPrefix' => 'post-']); ?>
<?php section_end(); ?>
