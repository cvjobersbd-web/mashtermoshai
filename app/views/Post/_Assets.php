<?php
/**
 * টাইমলাইন পোস্টের কমন স্টাইল + JS + পোস্ট লেখার মডাল।
 * Profile/Show আর Post/Details এ ব্যবহার হয়। দরকার: $me (Obj: Id, Name, Image)
 */
$rxMeta = reaction_meta();
$pvMeta = post_privacy_meta();
$meInitials = initials($me->Name ?? 'U', 'U');
?>
<style>
    :root {
        --pc-navy: #08224A; --pc-sky: #02B8F5; --pc-sky-d: #0099D6; --pc-ice: #F5FBFF; --pc-line: #E3EEF5;
        --pc-muted: #65676B; --pc-text: #1C1E21; --pc-bubble: #F0F2F5; --pc-blue: #1877F2;
    }
    /* ===== Post card ===== */
    .pc { background: #fff; border-radius: 14px; border: 1px solid var(--pc-line); box-shadow: 0 1px 3px rgba(8,34,74,.06); margin-bottom: 16px; }
    .pc.pc-new { animation: pcIn .45s cubic-bezier(.22,1,.36,1); }
    @keyframes pcIn { from { opacity: 0; transform: translateY(-8px) scale(.99); } to { opacity: 1; transform: none; } }
    .pc-head { display: flex; gap: 10px; align-items: flex-start; padding: 14px 14px 8px; }
    .pc-avatar, .cm-avatar, .me-avatar {
        width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; text-decoration: none;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-weight: 700; font-size: 15px;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .pc-avatar img, .cm-avatar img, .me-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .pc-who { flex: 1; min-width: 0; line-height: 1.25; }
    .pc-name { font-weight: 700; color: var(--pc-text); text-decoration: none; font-size: 15px; }
    .pc-name:hover { text-decoration: underline; color: var(--pc-text); }
    .pc-verified { color: var(--pc-sky); font-size: 13px; margin-left: 3px; }
    .pc-sub { display: flex; align-items: center; gap: 5px; font-size: 12.5px; color: var(--pc-muted); margin-top: 3px; }
    .pc-sub a { color: inherit; text-decoration: none; }
    .pc-sub a:hover { text-decoration: underline; }
    .pc-sub i { font-size: 11px; }
    .pc-menu-wrap { position: relative; }
    .pc-menu-btn { width: 36px; height: 36px; border-radius: 50%; border: none; background: transparent; color: var(--pc-muted); font-size: 17px; cursor: pointer; }
    .pc-menu-btn:hover { background: var(--pc-bubble); }
    .pc-menu {
        position: absolute; right: 0; top: 40px; z-index: 40; min-width: 210px; background: #fff; border-radius: 12px; padding: 6px;
        box-shadow: 0 12px 32px rgba(8,34,74,.18), 0 0 0 1px rgba(8,34,74,.05); display: none;
    }
    .pc-menu.open { display: block; animation: pcIn .18s ease; }
    .pc-menu button {
        display: flex; align-items: center; gap: 12px; width: 100%; padding: 10px 12px; border: none; background: none; border-radius: 8px;
        font-size: 14.5px; font-weight: 600; color: var(--pc-text); cursor: pointer; text-align: left;
    }
    .pc-menu button i { width: 18px; text-align: center; color: var(--pc-muted); }
    .pc-menu button:hover { background: var(--pc-bubble); }
    .pc-menu button.danger, .pc-menu button.danger i { color: #D93025; }

    .pc-body { padding: 0 14px 10px; }
    .pc-text { font-size: 15px; line-height: 1.5; color: var(--pc-text); white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere; }
    .pc-text.big { font-size: 26px; line-height: 1.25; font-weight: 500; padding: 4px 0 6px; }
    .pc-text.clamp { max-height: 9.2em; overflow: hidden; -webkit-mask-image: linear-gradient(#000 70%, transparent); mask-image: linear-gradient(#000 70%, transparent); }
    .pc-text a { color: var(--pc-blue); word-break: break-all; }
    .pc-more { border: none; background: none; padding: 2px 0 0; font-weight: 700; color: var(--pc-text); cursor: pointer; font-size: 14.5px; }
    .pc-more:hover { text-decoration: underline; }
    .pc-media { background: #000; border-top: 1px solid var(--pc-line); border-bottom: 1px solid var(--pc-line); }
    .pc-media img { display: block; width: 100%; max-height: 640px; object-fit: contain; cursor: zoom-in; background: #F0F2F5; }

    .pc-stats { display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; min-height: 18px; font-size: 14px; color: var(--pc-muted); }
    .pc-stats:has(> [hidden]:first-child):has(> [hidden]:last-child) { padding: 4px 14px; }
    .pc-rx-sum, .pc-cm-n { border: none; background: none; color: inherit; padding: 0; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; }
    .pc-rx-sum:hover, .pc-cm-n:hover { text-decoration: underline; }
    .pc-cm-n { margin-left: auto; }
    .pc-stat-right { margin-left: auto; display: inline-flex; gap: 14px; align-items: center; }
    .pc-sh-n { border: none; background: none; color: inherit; padding: 0; cursor: pointer; font-size: 14px; }
    .pc-sh-n:hover { text-decoration: underline; }
    .pc-shared-lbl { color: var(--pc-muted); font-size: 14.5px; font-weight: 400; margin-left: 2px; }
    .pc-avatar.sm { width: 36px; height: 36px; font-size: 13px; }
    .pc-shared { margin: 0 14px 10px; border: 1px solid #DADDE1; border-radius: 10px; overflow: hidden; background: #fff; }
    .pc-shared .pc-media { border-top: 0; }
    .pc-shared .pc-head { padding: 12px 12px 6px; }
    .pc-shared .pc-body { padding: 0 12px 12px; }
    .pc-shared .pc-text { font-size: 14.5px; }
    .pc-unavail { display: flex; gap: 14px; align-items: center; padding: 16px; background: #F7F8FA; color: var(--pc-muted); }
    .pc-unavail > i { font-size: 22px; color: #8C939D; }
    .pc-unavail strong { color: var(--pc-text); font-size: 15px; }
    .pc-unavail p { margin: 2px 0 0; font-size: 13px; }
    .pm-shared { margin-top: 8px; pointer-events: none; }
    .pm-shared .pc-shared { margin: 0; }
    .pm-shared .pc-media img { max-height: 220px; }
    .pm-copy { width: 100%; height: 38px; border: none; border-radius: 8px; background: #E4E6EB; color: var(--pc-text); font-weight: 600; font-size: 14.5px; cursor: pointer; margin-bottom: 8px; }
    .pm-copy:hover { background: #D8DADF; }

    /* ===== @mention ===== */
    .cm-text a.cm-mention { color: var(--pc-blue); font-weight: 600; text-decoration: none; background: #E7F3FF; border-radius: 4px; padding: 0 2px; }
    .cm-text a.cm-mention:hover { text-decoration: underline; }
    .mn-box {
        position: fixed; z-index: 3100; width: 290px; max-width: calc(100vw - 20px); max-height: 300px; overflow-y: auto; background: #fff; border-radius: 10px;
        padding: 6px; box-shadow: 0 12px 32px rgba(8,34,74,.22), 0 0 0 1px rgba(8,34,74,.06); display: none;
    }
    .mn-box.open { display: block; }
    .mn-item { display: flex; align-items: center; gap: 10px; width: 100%; padding: 7px 8px; border: none; background: none; border-radius: 8px; cursor: pointer; text-align: left; }
    .mn-item.active, .mn-item:hover { background: #F0F2F5; }
    .mn-item .pc-avatar { width: 34px; height: 34px; font-size: 12px; }
    .mn-item strong { display: block; font-size: 14px; color: var(--pc-text); line-height: 1.2; }
    .mn-item small { font-size: 12px; color: var(--pc-muted); }
    .mn-empty { padding: 10px; font-size: 13px; color: var(--pc-muted); text-align: center; }
    .pc-rx-icons { display: inline-flex; }
    .pc-rx-icons span, .cm-rx-icons span {
        width: 20px; height: 20px; border-radius: 50%; background: #fff; display: inline-flex; align-items: center; justify-content: center;
        font-size: 13px; line-height: 1; box-shadow: 0 0 0 2px #fff; margin-left: -4px;
    }
    .pc-rx-icons span:first-child, .cm-rx-icons span:first-child { margin-left: 0; }

    .pc-actions { display: flex; gap: 4px; margin: 0 12px; padding: 4px 0; border-top: 1px solid var(--pc-line); }
    .pc-act {
        flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 38px; border: none; background: none;
        border-radius: 8px; color: var(--pc-muted); font-weight: 600; font-size: 14.5px; cursor: pointer; user-select: none; -webkit-user-select: none;
        -webkit-touch-callout: none; touch-action: manipulation;
    }
    .pc-act:hover { background: var(--pc-bubble); }
    .pc-act i { font-size: 17px; }
    .pc-like-ico { font-size: 18px; line-height: 1; display: inline-flex; }
    .pc-like.on .pc-like-lbl { font-weight: 700; }
    <?php foreach ($rxMeta as $k => [$emo, $lbl, $col]): ?>
    .pc-like.rx-<?= $k ?>, .cm-like.rx-<?= $k ?> { color: <?= $col ?>; }
    <?php endforeach; ?>

    /* ===== Comments ===== */
    .pc-comments { padding: 4px 14px 12px; border-top: 1px solid var(--pc-line); margin: 0 0; display: none; }
    .pc.show-comments .pc-comments, .pc.has-comments .pc-comments { display: block; }
    .cm-prev { border: none; background: none; padding: 6px 0; font-weight: 600; font-size: 14px; color: var(--pc-muted); cursor: pointer; }
    .cm-prev:hover { text-decoration: underline; }
    .cm-list { display: flex; flex-direction: column; gap: 10px; padding-top: 8px; }
    .cm { display: flex; gap: 8px; align-items: flex-start; }
    .cm[hidden] { display: none; }
    .cm.cm-new { animation: pcIn .35s ease; }
    .cm-avatar { width: 34px; height: 34px; font-size: 12.5px; }
    .cm-avatar.sm, .cm-form .me-avatar.sm, .me-avatar.sm { width: 30px; height: 30px; font-size: 11px; }
    .cm-main { flex: 1; min-width: 0; }
    .cm-bubble-row { display: flex; align-items: center; gap: 6px; }
    .cm-bubble { position: relative; background: var(--pc-bubble); border-radius: 18px; padding: 8px 12px; max-width: 100%; display: inline-block; min-width: 0; }
    .cm-bubble.has-rx { margin-bottom: 6px; }
    .cm-name { display: block; font-weight: 700; font-size: 13px; color: var(--pc-text); text-decoration: none; line-height: 1.3; }
    .cm-name:hover { text-decoration: underline; color: var(--pc-text); }
    .cm-text { font-size: 14.5px; line-height: 1.38; color: var(--pc-text); white-space: pre-wrap; word-wrap: break-word; overflow-wrap: anywhere; }
    .cm-text a { color: var(--pc-blue); }
    .cm-mention { color: var(--pc-blue); font-weight: 600; }
    .cm-rx {
        position: absolute; right: -8px; bottom: -11px; display: inline-flex; align-items: center; gap: 3px; border: none; cursor: pointer;
        background: #fff; border-radius: 12px; padding: 1px 5px 1px 2px; box-shadow: 0 1px 3px rgba(0,0,0,.22); font-size: 12px; color: var(--pc-muted);
    }
    .cm-rx-icons span { width: 17px; height: 17px; font-size: 11.5px; }
    .cm-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 14px; padding: 4px 12px 0; font-size: 12.5px; color: var(--pc-muted); }
    .cm-meta button { border: none; background: none; padding: 0; font: inherit; font-weight: 700; color: inherit; cursor: pointer;
        user-select: none; -webkit-user-select: none; -webkit-touch-callout: none; touch-action: manipulation; }
    .cm-meta button:hover { text-decoration: underline; }
    .cm-time { font-weight: 400; }
    .cm-edited { font-weight: 400; }
    .cm-view-replies { border: none; background: none; padding: 6px 0 0 12px; font-weight: 700; font-size: 13.5px; color: var(--pc-muted); cursor: pointer; display: inline-flex; gap: 8px; align-items: center; }
    .cm-view-replies:hover { text-decoration: underline; }
    .cm-replies { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; }
    .cm-replies[hidden] { display: none; }

    .cm-form { display: flex; gap: 8px; align-items: flex-start; margin-top: 12px; }
    .cm-form .me-avatar { width: 34px; height: 34px; font-size: 12.5px; }
    .cm-pill { flex: 1; min-width: 0; display: flex; align-items: flex-end; gap: 4px; background: var(--pc-bubble); border-radius: 20px; padding: 4px 4px 4px 14px; }
    .cm-pill textarea {
        flex: 1; min-width: 0; border: none; outline: none; background: transparent; resize: none; font: inherit; font-size: 14.5px;
        line-height: 1.4; padding: 6px 0; max-height: 140px; color: var(--pc-text);
    }
    .cm-send { width: 32px; height: 32px; flex-shrink: 0; border: none; border-radius: 50%; background: transparent; color: var(--pc-blue); cursor: pointer; }
    .cm-send:hover:not(:disabled) { background: #E4E6EB; }
    .cm-send:disabled { color: #BCC0C4; cursor: default; }
    .cm-edit-box textarea { width: 100%; border: 1.5px solid var(--pc-sky); border-radius: 14px; padding: 8px 12px; font: inherit; font-size: 14.5px; resize: none; outline: none; min-height: 40px; }
    .cm-edit-actions { display: flex; gap: 10px; font-size: 12.5px; padding: 4px 6px 0; color: var(--pc-muted); }
    .cm-edit-actions button { border: none; background: none; padding: 0; color: var(--pc-blue); font-weight: 700; cursor: pointer; }

    /* ===== Reaction picker ===== */
    .rx-picker {
        position: fixed; z-index: 3000; display: flex; gap: 2px; padding: 5px 6px; background: #fff; border-radius: 40px;
        box-shadow: 0 6px 22px rgba(8,34,74,.22), 0 0 0 1px rgba(8,34,74,.05); opacity: 0; transform: translateY(8px) scale(.9);
        transform-origin: left bottom; transition: opacity .16s, transform .2s cubic-bezier(.22,1,.36,1); pointer-events: none;
    }
    .rx-picker.open { opacity: 1; transform: none; pointer-events: auto; }
    .rx-picker button {
        width: 42px; height: 42px; border: none; background: none; border-radius: 50%; font-size: 30px; line-height: 1; cursor: pointer;
        transition: transform .15s cubic-bezier(.22,1,.36,1); position: relative; padding: 0;
    }
    .rx-picker button:hover, .rx-picker button.hot { transform: scale(1.32) translateY(-5px); }
    .rx-picker button::after {
        content: attr(data-label); position: absolute; left: 50%; top: -24px; transform: translateX(-50%); background: rgba(0,0,0,.75); color: #fff;
        font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 10px; opacity: 0; transition: opacity .12s; pointer-events: none; white-space: nowrap;
    }
    .rx-picker button:hover::after, .rx-picker button.hot::after { opacity: 1; }
    .rx-picker.sm button { width: 36px; height: 36px; font-size: 24px; }

    /* ===== Composer (create box + modal) ===== */
    .pc-create { background: #fff; border-radius: 14px; border: 1px solid var(--pc-line); padding: 12px 14px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(8,34,74,.06); }
    .pc-create-top { display: flex; gap: 10px; align-items: center; }
    .pc-create-input {
        flex: 1; text-align: left; border: none; background: var(--pc-bubble); border-radius: 22px; height: 42px; padding: 0 16px;
        color: var(--pc-muted); font-size: 15px; cursor: pointer; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
    }
    .pc-create-input:hover { background: #E4E6EB; }
    .pc-create-photo { width: 42px; height: 42px; border: none; border-radius: 50%; background: none; color: #45BD62; font-size: 22px; cursor: pointer; }
    .pc-create-photo:hover { background: var(--pc-bubble); }

    .pm-overlay {
        position: fixed; inset: 0; z-index: 2500; background: rgba(244,246,249,.82); backdrop-filter: blur(2px);
        display: none; align-items: center; justify-content: center; padding: 16px;
    }
    .pm-overlay.open { display: flex; }
    .pm {
        width: 100%; max-width: 520px; max-height: calc(100vh - 32px); display: flex; flex-direction: column; background: #fff; border-radius: 14px;
        box-shadow: 0 12px 40px rgba(8,34,74,.25); animation: pcIn .25s cubic-bezier(.22,1,.36,1);
    }
    .pm-head { position: relative; text-align: center; padding: 16px 52px; border-bottom: 1px solid var(--pc-line); }
    .pm-head h3 { margin: 0; font-size: 19px; font-weight: 700; color: var(--pc-text); }
    .pm-x { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; border-radius: 50%; border: none; background: #E4E6EB; color: #4B4F56; font-size: 17px; cursor: pointer; }
    .pm-x:hover { background: #D8DADF; }
    .pm-body { padding: 14px 16px; overflow-y: auto; }
    .pm-user { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
    .pm-user strong { display: block; font-size: 15px; color: var(--pc-text); }
    .pm-privacy {
        margin-top: 3px; display: inline-flex; align-items: center; gap: 6px; background: #E4E6EB; border-radius: 6px; padding: 3px 8px; position: relative;
        font-size: 13px; font-weight: 600; color: var(--pc-text);
    }
    .pm-privacy select { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; font-size: 16px; }
    .pm-privacy-hint { font-size: 12px; color: var(--pc-muted); margin-top: 3px; }
    .pm textarea.pm-text {
        width: 100%; border: none; outline: none; resize: none; font: inherit; font-size: 19px; line-height: 1.4; min-height: 120px; color: var(--pc-text);
        padding: 4px 0;
    }
    .pm textarea.pm-text.small { font-size: 15px; min-height: 80px; }
    .pm-count { text-align: right; font-size: 12px; color: var(--pc-muted); }
    .pm-preview { position: relative; margin-top: 8px; border-radius: 10px; overflow: hidden; border: 1px solid var(--pc-line); display: none; }
    .pm-preview.show { display: block; }
    .pm-preview img { display: block; width: 100%; max-height: 320px; object-fit: contain; background: #F0F2F5; }
    .pm-preview button { position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; border-radius: 50%; border: none; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.25); cursor: pointer; }
    .pm-add { display: flex; align-items: center; justify-content: space-between; border: 1px solid #CED0D4; border-radius: 10px; padding: 8px 12px; margin-top: 12px; }
    .pm-add span { font-weight: 600; font-size: 14.5px; color: var(--pc-text); }
    .pm-add label { width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #45BD62; font-size: 22px; cursor: pointer; margin: 0; }
    .pm-add label:hover { background: var(--pc-bubble); }
    .pm-foot { padding: 0 16px 16px; }
    .pm-submit {
        width: 100%; height: 40px; border: none; border-radius: 8px; background: var(--pc-sky); color: #fff; font-weight: 700; font-size: 15px; cursor: pointer;
    }
    .pm-submit:hover:not(:disabled) { background: var(--pc-sky-d); }
    .pm-submit:disabled { background: #E4E6EB; color: #BCC0C4; cursor: not-allowed; }

    /* reactors list */
    .rl-tabs { display: flex; gap: 4px; overflow-x: auto; border-bottom: 1px solid var(--pc-line); padding: 0 12px; scrollbar-width: none; }
    .rl-tabs button { border: none; background: none; padding: 12px 10px; font-weight: 600; font-size: 14.5px; color: var(--pc-muted); border-bottom: 3px solid transparent; cursor: pointer; white-space: nowrap; }
    .rl-tabs button.active { color: var(--pc-sky-d); border-bottom-color: var(--pc-sky); }
    .rl-list { padding: 8px 12px 12px; max-height: 60vh; overflow-y: auto; }
    .rl-item { display: flex; align-items: center; gap: 12px; padding: 8px 4px; text-decoration: none; color: var(--pc-text); border-radius: 8px; }
    .rl-item:hover { background: var(--pc-bubble); color: var(--pc-text); }
    .rl-av { position: relative; }
    .rl-av .pc-avatar { width: 44px; height: 44px; }
    .rl-emo { position: absolute; right: -4px; bottom: -4px; width: 20px; height: 20px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,.2); z-index: 2; }
    .rl-item small { display: block; color: var(--pc-muted); font-size: 12px; }

    /* lightbox */
    .pc-lightbox { position: fixed; inset: 0; z-index: 3200; background: rgba(0,0,0,.92); display: none; align-items: center; justify-content: center; padding: 20px; }
    .pc-lightbox.open { display: flex; }
    .pc-lightbox img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px; }
    .pc-lightbox button { position: absolute; top: 14px; right: 14px; width: 42px; height: 42px; border-radius: 50%; border: none; background: rgba(255,255,255,.15); color: #fff; font-size: 20px; cursor: pointer; }

    .pc-toast { position: fixed; left: 50%; bottom: 26px; z-index: 3300; transform: translate(-50%, 20px); opacity: 0; background: #08224A; color: #fff;
        padding: 12px 20px; border-radius: 12px; font-size: 14px; box-shadow: 0 12px 30px rgba(8,34,74,.3); transition: opacity .25s, transform .25s;
        pointer-events: none; max-width: calc(100vw - 32px); text-align: center; }
    .pc-toast.show { opacity: 1; transform: translate(-50%, 0); }
    .pc-toast.err { background: #B42318; }

    .pc-empty { background: #fff; border-radius: 14px; border: 1px solid var(--pc-line); padding: 40px 20px; text-align: center; color: var(--pc-muted); }
    .pc-empty i { font-size: 38px; color: #B9D6E6; margin-bottom: 12px; }
    .pc-empty h5 { color: var(--pc-navy); font-weight: 700; margin: 0 0 6px; }
    .pc-loadmore { width: 100%; height: 44px; border: 1px solid var(--pc-line); border-radius: 12px; background: #fff; font-weight: 700; color: var(--pc-navy); cursor: pointer; }
    .pc-loadmore:hover { background: var(--pc-ice); }

    @media (max-width: 576px) {
        .pc { border-radius: 0; border-left: 0; border-right: 0; margin-left: -12px; margin-right: -12px; margin-bottom: 10px; box-shadow: none; }
        .pc-create { border-radius: 0; border-left: 0; border-right: 0; margin-left: -12px; margin-right: -12px; box-shadow: none; }
        .pc-act { font-size: 13.5px; gap: 6px; }
        .pc-text.big { font-size: 23px; }
        .pm-overlay { padding: 0; align-items: flex-end; }
        .pm { max-width: none; max-height: 92vh; border-radius: 16px 16px 0 0; }
        .pc-empty { border-radius: 0; margin: 0 -12px; border-left: 0; border-right: 0; }
        .rx-picker button { width: 38px; height: 38px; font-size: 27px; }
    }
</style>

<!-- ===== পোস্ট লেখা / এডিট মডাল ===== -->
<div class="pm-overlay" id="pmOverlay" aria-hidden="true">
    <form class="pm" id="pmForm" role="dialog" aria-modal="true" aria-labelledby="pmTitle">
        <div class="pm-head">
            <h3 id="pmTitle">Create post</h3>
            <button type="button" class="pm-x" data-pm="close" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="pm-body">
            <div class="pm-user">
                <span class="pc-avatar me-avatar"></span>
                <div>
                    <strong><?= e($me->Name) ?></strong>
                    <label class="pm-privacy" title="কারা দেখতে পারবে">
                        <i class="fas fa-earth-asia" id="pmPrivIcon"></i>
                        <span id="pmPrivLabel">Public</span>
                        <i class="fas fa-caret-down"></i>
                        <select id="pmPrivacy" aria-label="Audience">
                            <?php foreach ($pvMeta as $k => [$ic, $lb, $bn]): ?>
                                <option value="<?= $k ?>"><?= e($lb . ' — ' . $bn) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="pm-privacy-hint" id="pmPrivHint"></div>
                </div>
            </div>
            <textarea class="pm-text" id="pmText" maxlength="5000" placeholder="আপনার মনে কী আছে, <?= e(explode(' ', trim($me->Name))[0]) ?>?"></textarea>
            <div class="pm-count" id="pmCount"></div>
            <div class="pm-preview" id="pmPreview">
                <img id="pmPreviewImg" alt="Preview" />
                <button type="button" data-pm="remove-image" aria-label="Remove photo"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="pm-shared" id="pmShared" hidden></div>
            <div class="pm-add" id="pmAdd">
                <span>Add to your post</span>
                <label for="pmFile" title="Photo"><i class="fas fa-images"></i></label>
                <input type="file" id="pmFile" accept="image/jpeg,image/png,image/gif,image/webp" hidden />
            </div>
        </div>
        <div class="pm-foot">
            <button type="button" class="pm-copy" id="pmCopy" hidden><i class="fas fa-link"></i> Copy link</button>
            <button type="submit" class="pm-submit" id="pmSubmit" disabled>Post</button>
        </div>
    </form>
</div>

<!-- ===== কে রিয়েক্ট দিয়েছে ===== -->
<div class="pm-overlay" id="rlOverlay" aria-hidden="true">
    <div class="pm" role="dialog" aria-modal="true" aria-labelledby="rlTitle">
        <div class="pm-head">
            <h3 id="rlTitle">Reactions</h3>
            <button type="button" class="pm-x" data-rl="close" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="rl-tabs" id="rlTabs"></div>
        <div class="rl-list" id="rlList"></div>
    </div>
</div>

<div class="pc-lightbox" id="pcLightbox"><button type="button" aria-label="Close"><i class="fas fa-xmark"></i></button><img alt="" /></div>

<script>
(function () {
    if (window.__pcReady) return;
    window.__pcReady = true;

    var RX = <?= js(array_map(fn($m) => ['emoji' => $m[0], 'label' => $m[1], 'color' => $m[2]], $rxMeta)) ?>;
    var PV = <?= js(array_map(fn($m) => ['icon' => $m[0], 'label' => $m[1], 'hint' => $m[2]], $pvMeta)) ?>;
    var ME = <?= js(['id' => (int)$me->Id, 'name' => (string)$me->Name, 'img' => (string)($me->Image ?? ''), 'initials' => $meInitials]) ?>;
    var RX_KEYS = Object.keys(RX);

    function $(s, r) { return (r || document).querySelector(s); }
    function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function token() { var t = $('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }

    // ===== নিজের অবতার সব জায়গায় =====
    function meAvatarHtml() { return esc(ME.initials) + (ME.img ? '<img src="' + esc(ME.img) + '" alt="" onerror="this.remove()" />' : ''); }
    function fillMe(root) { $$('.me-avatar', root).forEach(function (el) { if (!el.dataset.filled) { el.innerHTML = meAvatarHtml(); el.dataset.filled = '1'; } }); }
    // ===== Toast =====
    var toastEl, toastT;
    function toast(msg, err) {
        if (!toastEl) { toastEl = document.createElement('div'); toastEl.className = 'pc-toast'; document.body.appendChild(toastEl); }
        toastEl.textContent = msg; toastEl.classList.toggle('err', !!err); toastEl.classList.add('show');
        clearTimeout(toastT); toastT = setTimeout(function () { toastEl.classList.remove('show'); }, 2600);
    }
    window.pcToast = toast;

    async function post(url, data) {
        var fd = data instanceof FormData ? data : new FormData();
        if (!(data instanceof FormData)) Object.keys(data || {}).forEach(function (k) { if (data[k] !== undefined && data[k] !== null) fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', token());
        var res = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        var json;
        try { json = await res.json(); } catch (e) { throw new Error('সার্ভার থেকে উত্তর পাওয়া যায়নি'); }
        if (json && json.suspended) { location.href = '/Account/Login'; }
        return json;
    }

    function htmlToEl(html) { var t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstElementChild; }
    function autoGrow(ta) { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 220) + 'px'; }

    // ============================================================
    // REACTIONS (পোস্ট + কমেন্ট)
    // ============================================================
    var picker = document.createElement('div');
    picker.className = 'rx-picker';
    picker.innerHTML = RX_KEYS.map(function (k) { return '<button type="button" data-rx="' + k + '" data-label="' + RX[k].label + '" aria-label="' + RX[k].label + '">' + RX[k].emoji + '</button>'; }).join('');
    document.body.appendChild(picker);
    var pickerFor = null, hoverT = null, hideT = null, pressT = null, suppressClick = false;

    function openPicker(btn) {
        clearTimeout(hideT);
        pickerFor = btn;
        picker.classList.toggle('sm', btn.getAttribute('data-rx-target') === 'comment');
        picker.classList.add('open');
        var r = btn.getBoundingClientRect(), pw = picker.offsetWidth, ph = picker.offsetHeight;
        var left = Math.max(8, Math.min(r.left - 6, window.innerWidth - pw - 8));
        var top = r.top - ph - 8;
        if (top < 70) top = r.bottom + 8;
        picker.style.left = left + 'px'; picker.style.top = top + 'px';
    }
    function closePicker() { picker.classList.remove('open'); $$('button.hot', picker).forEach(function (b) { b.classList.remove('hot'); }); pickerFor = null; }
    function scheduleClose() { clearTimeout(hideT); hideT = setTimeout(closePicker, 350); }

    function paintLike(btn, mine) {
        var isPost = btn.getAttribute('data-rx-target') === 'post';
        btn.setAttribute('data-mine', mine || '');
        btn.className = btn.className.replace(/\s?rx-\w+/g, '').replace(/\s?\bon\b/g, '') + (mine ? ' on rx-' + mine : '');
        if (isPost) {
            $('.pc-like-ico', btn).innerHTML = mine ? RX[mine].emoji : '<i class="far fa-thumbs-up"></i>';
            $('.pc-like-lbl', btn).textContent = mine ? RX[mine].label : 'Like';
        } else {
            btn.textContent = mine ? RX[mine].label : 'Like';
        }
    }
    function paintSummary(box, counts, total, isPost) {
        if (!box) return;
        var keys = Object.keys(counts || {}).sort(function (a, b) { return counts[b] - counts[a]; }).slice(0, 3);
        box.hidden = !total;
        $(isPost ? '.pc-rx-icons' : '.cm-rx-icons', box).innerHTML = keys.map(function (k) { return '<span>' + RX[k].emoji + '</span>'; }).join('');
        $(isPost ? '.pc-rx-n' : '.cm-rx-n', box).textContent = isPost ? total : (total > 1 ? total : '');
        if (!isPost) box.closest('.cm-bubble').classList.toggle('has-rx', !!total);
    }

    async function react(btn, reaction) {
        var isPost = btn.getAttribute('data-rx-target') === 'post';
        var id = btn.getAttribute('data-id');
        var prev = btn.getAttribute('data-mine') || '';
        paintLike(btn, reaction); // সাথে সাথে দেখাই, পরে সার্ভারের উত্তরে ঠিক করি
        try {
            var d = await post(isPost ? '/Post/React' : '/Post/ReactComment', isPost ? { postId: id, reaction: reaction } : { commentId: id, reaction: reaction });
            if (!d.success) { paintLike(btn, prev); toast(d.message || 'Failed', true); return; }
            paintLike(btn, d.mine || '');
            if (isPost) paintSummary($('.pc-rx-sum', btn.closest('.pc')), d.counts, d.total, true);
            else paintSummary($('.cm-rx', btn.closest('.cm-main')), d.counts, d.total, false);
        } catch (e) { paintLike(btn, prev); toast('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
    }

    // মাউস: হোভার করলে পিকার
    document.addEventListener('mouseover', function (e) {
        var btn = e.target.closest('[data-rx-target]');
        if (btn && matchMedia('(hover: hover)').matches) {
            clearTimeout(hideT);
            if (pickerFor !== btn) { clearTimeout(hoverT); hoverT = setTimeout(function () { openPicker(btn); }, 450); }
        } else if (e.target.closest('.rx-picker')) {
            clearTimeout(hideT);
        }
    });
    document.addEventListener('mouseout', function (e) {
        var from = e.target.closest('[data-rx-target], .rx-picker');
        if (!from) return;
        var to = e.relatedTarget && e.relatedTarget.closest && e.relatedTarget.closest('[data-rx-target], .rx-picker');
        if (to) return;
        clearTimeout(hoverT);
        if (pickerFor) scheduleClose();
    });

    // টাচ: চেপে ধরলে পিকার, আঙুল সরিয়ে ইমোজি বাছাই
    document.addEventListener('touchstart', function (e) {
        var btn = e.target.closest('[data-rx-target]');
        if (!btn) { if (pickerFor && !e.target.closest('.rx-picker')) closePicker(); return; }
        clearTimeout(pressT);
        pressT = setTimeout(function () { suppressClick = true; openPicker(btn); if (navigator.vibrate) navigator.vibrate(15); }, 420);
    }, { passive: true });
    document.addEventListener('touchmove', function (e) {
        if (!pickerFor) { clearTimeout(pressT); return; }
        var t = e.touches[0], el = document.elementFromPoint(t.clientX, t.clientY);
        $$('button', picker).forEach(function (b) { b.classList.toggle('hot', b === (el && el.closest('.rx-picker button'))); });
        if (e.cancelable) e.preventDefault();
    }, { passive: false });
    document.addEventListener('touchend', function () {
        clearTimeout(pressT);
        var hot = $('button.hot', picker);
        if (hot && pickerFor) { var b = pickerFor; closePicker(); react(b, hot.getAttribute('data-rx')); }
    });
    document.addEventListener('contextmenu', function (e) { if (e.target.closest('[data-rx-target], .rx-picker')) e.preventDefault(); });

    picker.addEventListener('click', function (e) {
        var b = e.target.closest('button[data-rx]');
        if (!b || !pickerFor) return;
        var target = pickerFor; closePicker();
        react(target, b.getAttribute('data-rx'));
    });

    // ============================================================
    // কমেন্ট ফর্ম
    // ============================================================
    // কমেন্ট বক্স — পুরো পেজে একটাই লিসেনার, তাই পরে লোড হওয়া পোস্টেও কাজ করে
    function bindForm() { }
    document.addEventListener('input', function (e) {
        var ta = e.target.closest('.cm-form textarea');
        if (!ta) return;
        autoGrow(ta);
        $('.cm-send', ta.closest('.cm-form')).disabled = !ta.value.trim();
    });
    document.addEventListener('keydown', function (e) {
        var ta = e.target.closest('.cm-form textarea');
        if (!ta) return;
        var form = ta.closest('.cm-form');
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing && matchMedia('(hover: hover)').matches) { e.preventDefault(); if (ta.value.trim()) form.requestSubmit(); }
        if (e.key === 'Escape' && form.classList.contains('cm-reply-form')) form.remove();
    });

    function setCommentCount(card, n) {
        var b = $('.pc-cm-n', card);
        b.hidden = !n;
        b.innerHTML = '<span>' + n + '</span> comment' + (n === 1 ? '' : 's');
    }

    document.addEventListener('submit', async function (e) {
        var form = e.target.closest('.cm-form');
        if (!form) return;
        e.preventDefault();
        var card = form.closest('.pc'), ta = $('textarea', form), send = $('.cm-send', form);
        var text = ta.value.trim();
        if (!text) return;
        var parentId = form.getAttribute('data-parent') || '';
        closeMention();
        ta.disabled = true; send.disabled = true;
        try {
            var d = await post('/Post/Comment', { postId: card.getAttribute('data-id'), content: encodeMentions(ta), parentId: parentId });
            if (!d.success) { toast(d.message || 'Failed', true); return; }
            var el = htmlToEl(d.html); el.classList.add('cm-new');
            if (d.parentId) {
                var root = document.getElementById('cm-' + d.parentId);
                var list = $('.cm-replies', root); list.hidden = false;
                var vr = $('.cm-view-replies', root); if (vr) vr.remove();
                list.appendChild(el);
                form.remove();
            } else {
                $('.cm-list', card).appendChild(el);
                ta.value = ''; setMap(ta, {}); autoGrow(ta);
            }
            fillMe(el);
            setCommentCount(card, d.count);
            card.classList.add('has-comments');
        } catch (err) { toast('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { if (document.body.contains(ta)) { ta.disabled = false; send.disabled = !ta.value.trim(); ta.focus(); } }
    });

    function openReply(cm) {
        var card = cm.closest('.pc');
        var rootId = cm.getAttribute('data-root');
        var root = document.getElementById('cm-' + rootId);
        $$('.cm-reply-form', card).forEach(function (f) { f.remove(); });
        var replies = $('.cm-replies', root); replies.hidden = false;
        var vr = $('.cm-view-replies', root); if (vr) vr.remove();
        var isNested = cm !== root;
        var name = cm.getAttribute('data-name');
        var f = document.createElement('form');
        f.className = 'cm-form cm-reply-form';
        f.setAttribute('autocomplete', 'off');
        f.setAttribute('data-parent', cm.getAttribute('data-id'));
        f.innerHTML = '<span class="cm-avatar sm me-avatar"></span><div class="cm-pill">'
            + '<textarea rows="1" maxlength="1500" placeholder="Reply to ' + esc(name) + '..."></textarea>'
            + '<button type="submit" class="cm-send" aria-label="Send" disabled><i class="fas fa-paper-plane"></i></button></div>';
        replies.appendChild(f);
        fillMe(f);
        var rta = $('textarea', f);
        // রিপ্লাইয়ের রিপ্লাই হলে নামটা আগে থেকেই মেনশন করে দিই (নিজেকে ছাড়া)
        var uid = Number(cm.dataset.uid || 0);
        if (isNested && uid && uid !== ME.id) {
            var mp = {}; mp[name] = uid; setMap(rta, mp);
            rta.value = '@' + name + ' ';
            $('.cm-send', f).disabled = false;
        }
        rta.focus(); rta.setSelectionRange(rta.value.length, rta.value.length);
    }

    // ============================================================
    // ক্লিক হ্যান্ডলার (সব একসাথে)
    // ============================================================
    document.addEventListener('click', async function (e) {
        // Like / React বাটন
        var likeBtn = e.target.closest('[data-rx-target]');
        if (likeBtn) {
            e.preventDefault();
            if (suppressClick) { suppressClick = false; return; }
            clearTimeout(hoverT); closePicker();
            var mine = likeBtn.getAttribute('data-mine');
            react(likeBtn, mine ? '' : 'like');
            return;
        }
        if (pickerFor && !e.target.closest('.rx-picker')) closePicker();

        // মেনু বন্ধ
        if (!e.target.closest('.pc-menu-wrap')) $$('.pc-menu.open').forEach(function (m) { m.classList.remove('open'); });

        var a = e.target.closest('[data-pc]');
        if (!a) return;
        var act = a.getAttribute('data-pc');
        var card = a.closest('.pc');
        var cm = a.closest('.cm');

        switch (act) {
            case 'menu': {
                var m = a.nextElementSibling;
                $$('.pc-menu.open').forEach(function (x) { if (x !== m) x.classList.remove('open'); });
                m.classList.toggle('open');
                break;
            }
            case 'more': a.previousElementSibling.classList.remove('clamp'); a.remove(); break;
            case 'zoom': { var lb = $('#pcLightbox'); $('img', lb).src = a.src; lb.classList.add('open'); break; }
            case 'comment':
            case 'toggle-comments': {
                card.classList.add('show-comments');
                if (act === 'toggle-comments') $$('.cm[hidden]', card).forEach(function (c) { if (!c.closest('.cm-replies')) c.hidden = false; });
                if (act === 'toggle-comments') { var pv = $('.cm-prev', card); if (pv) pv.remove(); }
                var ta = $('.pc-comments > .cm-form textarea', card);
                if (act === 'comment' && ta) ta.focus();
                break;
            }
            case 'prev-comments': $$('.cm-list > .cm[hidden]', card).forEach(function (c) { c.hidden = false; }); a.remove(); break;
            case 'view-replies': a.nextElementSibling.hidden = false; a.remove(); break;
            case 'reply': openReply(cm); break;
            case 'copy': {
                $$('.pc-menu.open').forEach(function (x) { x.classList.remove('open'); });
                var url = location.origin + '/Post/Details/' + card.getAttribute('data-id');
                try { await navigator.clipboard.writeText(url); toast('লিংক কপি হয়েছে।'); }
                catch (err) { window.prompt('লিংকটি কপি করুন:', url); }
                if (card.getAttribute('data-privacy') !== 'Public') toast('লিংক কপি হয়েছে — তবে পোস্টটি শুধু অনুমতি পাওয়া মানুষরাই দেখতে পারবে।');
                break;
            }
            case 'delete': {
                $$('.pc-menu.open').forEach(function (x) { x.classList.remove('open'); });
                if (!confirm('পোস্টটি মুছে ফেলবেন? এটা আর ফেরত আনা যাবে না।')) return;
                var d = await post('/Post/Delete', { postId: card.getAttribute('data-id') });
                if (!d.success) { toast(d.message, true); return; }
                card.style.transition = 'opacity .3s, transform .3s'; card.style.opacity = 0; card.style.transform = 'scale(.98)';
                setTimeout(function () { card.remove(); document.dispatchEvent(new CustomEvent('pc:deleted')); }, 300);
                toast(d.message);
                break;
            }
            case 'edit':
            case 'audience':
                $$('.pc-menu.open').forEach(function (x) { x.classList.remove('open'); });
                openComposer(card, act === 'audience');
                break;
            case 'reactors': openReactors('post', card.getAttribute('data-id')); break;
            case 'sharers': openSharers(card.getAttribute('data-id')); break;
            case 'share': openShare(card); break;
            case 'comment-reactors': openReactors('comment', cm.getAttribute('data-id')); break;
            case 'delete-comment': {
                if (!confirm('কমেন্টটি মুছে ফেলবেন?')) return;
                var d2 = await post('/Post/DeleteComment', { commentId: cm.getAttribute('data-id') });
                if (!d2.success) { toast(d2.message, true); return; }
                cm.remove();
                setCommentCount(card, d2.count);
                break;
            }
            case 'edit-comment': editComment(cm); break;
        }
    });

    // লাইটবক্স
    $('#pcLightbox').addEventListener('click', function () { this.classList.remove('open'); });

    // ===== কমেন্ট এডিট (ইনলাইন) =====
    function editComment(cm) {
        if ($('.cm-edit-box', cm)) return;
        var bubbleRow = $('.cm-bubble-row', cm), meta = $('.cm-meta', cm);
        var box = document.createElement('div');
        box.className = 'cm-edit-box';
        box.innerHTML = '<textarea maxlength="1500"></textarea><div class="cm-edit-actions">Esc চাপলে বাতিল · <button type="button" data-e="save">Save</button><button type="button" data-e="cancel">Cancel</button></div>';
        var ta = $('textarea', box);
        ta.value = cm.getAttribute('data-raw');
        try { setMap(ta, JSON.parse(cm.getAttribute('data-mentions') || '{}')); } catch (e) { setMap(ta, {}); }
        bubbleRow.hidden = true; meta.hidden = true;
        bubbleRow.after(box);
        autoGrow(ta); ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length);
        function done() { box.remove(); bubbleRow.hidden = false; meta.hidden = false; }
        async function save() {
            var text = ta.value.trim();
            if (!text) { toast('কমেন্ট খালি রাখা যাবে না।', true); return; }
            closeMention();
            ta.disabled = true;
            try {
                var d = await post('/Post/EditComment', { commentId: cm.getAttribute('data-id'), content: encodeMentions(ta) });
                if (!d.success) { toast(d.message, true); ta.disabled = false; return; }
                cm.setAttribute('data-raw', d.raw);
                cm.setAttribute('data-mentions', JSON.stringify(d.mentions || {}));
                $('.cm-text', cm).innerHTML = d.contentHtml;
                $('.cm-edited', cm).hidden = !d.isEdited;
                done();
            } catch (err) { ta.disabled = false; toast('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        }
        ta.addEventListener('input', function () { autoGrow(ta); });
        ta.addEventListener('keydown', function (e) {
            if (mnOpen()) return; // মেনশন তালিকা খোলা থাকলে Enter/Esc ওটার জন্য
            if (e.key === 'Escape') done();
            if (e.key === 'Enter' && !e.shiftKey && matchMedia('(hover: hover)').matches) { e.preventDefault(); save(); }
        });
        box.addEventListener('click', function (e) {
            var b = e.target.closest('[data-e]'); if (!b) return;
            if (b.getAttribute('data-e') === 'save') save(); else done();
        });
    }

    // ============================================================
    // কে রিয়েক্ট দিয়েছে
    // ============================================================
    var rl = $('#rlOverlay');
    function closeRl() { rl.classList.remove('open'); document.documentElement.style.overflow = ''; }
    rl.addEventListener('click', function (e) { if (e.target === rl || e.target.closest('[data-rl="close"]')) closeRl(); });
    async function openReactors(type, id) {
        $('#rlTitle').textContent = 'Reactions';
        $('#rlTabs').innerHTML = ''; $('#rlList').innerHTML = '<p style="text-align:center;color:#65676B;padding:20px;"><i class="fas fa-spinner fa-spin"></i></p>';
        rl.classList.add('open'); document.documentElement.style.overflow = 'hidden';
        try {
            var res = await fetch('/Post/Reactors?type=' + type + '&id=' + encodeURIComponent(id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            var d = await res.json();
            if (!d.success) { $('#rlList').innerHTML = '<p style="padding:20px;text-align:center;">' + esc(d.message) + '</p>'; return; }
            var counts = {};
            d.people.forEach(function (p) { counts[p.reaction] = (counts[p.reaction] || 0) + 1; });
            var tabs = [['all', 'All ' + d.people.length]].concat(Object.keys(counts).sort(function (a, b) { return counts[b] - counts[a]; }).map(function (k) { return [k, RX[k].emoji + ' ' + counts[k]]; }));
            $('#rlTabs').innerHTML = tabs.map(function (t, i) { return '<button type="button" data-k="' + t[0] + '"' + (i ? '' : ' class="active"') + '>' + t[1] + '</button>'; }).join('');
            function render(k) {
                var list = d.people.filter(function (p) { return k === 'all' || p.reaction === k; });
                $('#rlList').innerHTML = list.map(function (p) {
                    return '<a class="rl-item" href="/Profile/Show/' + p.id + '"><span class="rl-av"><span class="pc-avatar">' + esc(p.initials)
                        + (p.image ? '<img src="' + esc(p.image) + '" alt="" onerror="this.remove()" />' : '') + '</span><span class="rl-emo">' + p.emoji + '</span></span>'
                        + '<span><strong>' + esc(p.name) + '</strong><small>' + esc(p.role) + '</small></span></a>';
                }).join('') || '<p style="padding:20px;text-align:center;color:#65676B;">এখনো কেউ রিয়েক্ট দেয়নি।</p>';
            }
            render('all');
            $('#rlTabs').onclick = function (e) {
                var b = e.target.closest('button[data-k]'); if (!b) return;
                $$('#rlTabs button').forEach(function (x) { x.classList.toggle('active', x === b); });
                render(b.getAttribute('data-k'));
            };
        } catch (err) { $('#rlList').innerHTML = '<p style="padding:20px;text-align:center;">লোড করা যায়নি।</p>'; }
    }

    async function openSharers(id) {
        $('#rlTitle').textContent = 'People who shared this';
        $('#rlTabs').innerHTML = ''; $('#rlList').innerHTML = '<p style="text-align:center;color:#65676B;padding:20px;"><i class="fas fa-spinner fa-spin"></i></p>';
        rl.classList.add('open'); document.documentElement.style.overflow = 'hidden';
        try {
            var d = await (await fetch('/Post/Sharers?id=' + encodeURIComponent(id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            $('#rlList').innerHTML = (d.people || []).map(function (p) {
                return '<a class="rl-item" href="/Post/Details/' + p.shareId + '"><span class="rl-av"><span class="pc-avatar">' + esc(p.initials)
                    + (p.image ? '<img src="' + esc(p.image) + '" alt="" onerror="this.remove()" />' : '') + '</span><span class="rl-emo">🔁</span></span>'
                    + '<span><strong>' + esc(p.name) + '</strong><small>' + esc(p.role) + '</small></span></a>';
            }).join('') || '<p style="padding:20px;text-align:center;color:#65676B;">দেখানোর মতো কোনো শেয়ার নেই।</p>';
        } catch (err) { $('#rlList').innerHTML = '<p style="padding:20px;text-align:center;">লোড করা যায়নি।</p>'; }
    }

    // ============================================================
    // @MENTION — কমেন্টে @ লিখলে নামের তালিকা
    // ============================================================
    var mnBox = document.createElement('div');
    mnBox.className = 'mn-box'; mnBox.setAttribute('role', 'listbox');
    document.body.appendChild(mnBox);
    var mn = { ta: null, start: 0, items: [], idx: 0, seq: 0, timer: null, cache: {} };

    function getMap(ta) { try { return JSON.parse(ta.dataset.mentions || '{}'); } catch (e) { return {}; } }
    function setMap(ta, m) { ta.dataset.mentions = JSON.stringify(m || {}); }
    /** "@নাম" → "@[নাম|id]" (যেগুলো তালিকা থেকে বাছাই করা হয়েছে শুধু সেগুলো) */
    function encodeMentions(ta) {
        var text = ta.value, map = getMap(ta);
        Object.keys(map).sort(function (a, b) { return b.length - a.length; }).forEach(function (name) {
            var safe = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            text = text.replace(new RegExp('@' + safe + '(?![\\w\\u0980-\\u09FF])', 'g'), '@[' + name.replace(/[\]|]/g, '') + '|' + map[name] + ']');
        });
        return text;
    }
    function mnOpen() { return mnBox.classList.contains('open'); }
    function closeMention() { mnBox.classList.remove('open'); mn.ta = null; mn.items = []; }
    function placeBox(ta) {
        var r = ta.getBoundingClientRect(), h = Math.min(300, mnBox.scrollHeight || 200);
        var top = r.bottom + 6;
        if (top + h > window.innerHeight - 8) top = Math.max(8, r.top - h - 6);
        mnBox.style.top = top + 'px';
        mnBox.style.left = Math.max(10, Math.min(r.left, window.innerWidth - mnBox.offsetWidth - 10)) + 'px';
    }
    function renderMn() {
        if (!mn.items.length) { mnBox.innerHTML = '<div class="mn-empty">কাউকে পাওয়া যায়নি — শুধু ফ্রেন্ড ও এই পোস্টের মানুষদের mention করা যায়।</div>'; }
        else mnBox.innerHTML = mn.items.map(function (p, i) {
            return '<button type="button" class="mn-item' + (i === mn.idx ? ' active' : '') + '" data-i="' + i + '" role="option">'
                + '<span class="pc-avatar">' + esc(p.initials) + (p.image ? '<img src="' + esc(p.image) + '" alt="" onerror="this.remove()" />' : '') + '</span>'
                + '<span><strong>' + esc(p.name) + '</strong><small>' + esc(p.role) + ' · @' + esc(p.username) + '</small></span></button>';
        }).join('');
        mnBox.classList.add('open');
        placeBox(mn.ta);
    }
    function pickMn(i) {
        var p = mn.items[i], ta = mn.ta; if (!p || !ta) return;
        var caret = ta.selectionStart, before = ta.value.slice(0, mn.start), after = ta.value.slice(caret);
        var name = p.name.replace(/[\]|]/g, '');
        ta.value = before + '@' + name + ' ' + after.replace(/^\s+/, '');
        var m = getMap(ta); m[name] = p.id; setMap(ta, m);
        var pos = (before + '@' + name + ' ').length;
        ta.focus(); ta.setSelectionRange(pos, pos);
        closeMention();
        ta.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function checkMention(ta) {
        var caret = ta.selectionStart;
        var upto = ta.value.slice(0, caret);
        var m = upto.match(/(^|[\s(])@([^\s@\[\]]{0,30})$/);
        if (!m) { if (mn.ta === ta) closeMention(); return; }
        var q = m[2];
        mn.ta = ta; mn.start = caret - q.length - 1;
        var card = ta.closest('.pc'), postId = card ? card.getAttribute('data-id') : '';
        var key = postId + '|' + q.toLowerCase();
        clearTimeout(mn.timer);
        var my = ++mn.seq;
        function show(list) { if (my !== mn.seq || mn.ta !== ta) return; mn.items = list; mn.idx = 0; renderMn(); }
        if (mn.cache[key]) { show(mn.cache[key]); return; }
        mn.timer = setTimeout(async function () {
            try {
                var d = await (await fetch('/Post/MentionSuggest?postId=' + encodeURIComponent(postId) + '&q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                mn.cache[key] = d.people || [];
                show(mn.cache[key]);
            } catch (e) { }
        }, 140);
    }
    document.addEventListener('input', function (e) {
        var ta = e.target.closest('.cm-form textarea, .cm-edit-box textarea');
        if (ta) checkMention(ta);
    });
    document.addEventListener('click', function (e) {
        var it = e.target.closest('.mn-item');
        if (it) { e.preventDefault(); pickMn(+it.getAttribute('data-i')); return; }
        if (mnOpen() && !e.target.closest('.mn-box') && e.target !== mn.ta) closeMention();
    });
    mnBox.addEventListener('mousedown', function (e) { e.preventDefault(); }); // টেক্সটবক্স থেকে ফোকাস না সরে
    // কিবোর্ড: ↑ ↓ Enter Tab Esc (capture — যেন কমেন্ট পাঠানোর আগে এটা চলে)
    document.addEventListener('keydown', function (e) {
        if (!mnOpen() || e.target !== mn.ta) return;
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault(); e.stopImmediatePropagation();
            if (!mn.items.length) return;
            mn.idx = (mn.idx + (e.key === 'ArrowDown' ? 1 : -1) + mn.items.length) % mn.items.length; renderMn();
        } else if ((e.key === 'Enter' || e.key === 'Tab') && mn.items.length) {
            e.preventDefault(); e.stopImmediatePropagation(); pickMn(mn.idx);
        } else if (e.key === 'Escape') {
            e.preventDefault(); e.stopImmediatePropagation(); closeMention();
        }
    }, true);
    window.addEventListener('scroll', function () { if (mnOpen() && mn.ta) placeBox(mn.ta); }, { passive: true });

    // ============================================================
    // পোস্ট লেখা / এডিট
    // ============================================================
    var pm = $('#pmOverlay'), pmForm = $('#pmForm'), pmText = $('#pmText'), pmFile = $('#pmFile'), pmPriv = $('#pmPrivacy');
    var pmSubmit = $('#pmSubmit'), pmPreview = $('#pmPreview'), pmImg = $('#pmPreviewImg');
    var editing = null, removeImage = false, hadImage = false, busy = false, sharing = null;
    var pmShared = $('#pmShared'), pmAdd = $('#pmAdd'), pmCopy = $('#pmCopy');
    var PH_DEFAULT = pmText.getAttribute('placeholder');

    function lsGet(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
    function lsSet(k, v) { try { localStorage.setItem(k, v); } catch (e) { } }

    function paintPrivacy() {
        var p = PV[pmPriv.value] || PV.Public;
        $('#pmPrivIcon').className = 'fas ' + p.icon;
        $('#pmPrivLabel').textContent = p.label;
        $('#pmPrivHint').textContent = p.hint;
    }
    function hasImage() { return pmPreview.classList.contains('show'); }
    function refresh() {
        var n = pmText.value.length;
        pmText.classList.toggle('small', n > 120 || hasImage());
        $('#pmCount').textContent = n > 4000 ? n + ' / 5000' : '';
        var shareMode = !!sharing || !!(editing && editing.getAttribute('data-share') === '1');
        pmSubmit.disabled = busy || (!shareMode && !pmText.value.trim() && !hasImage());
    }
    function setPreview(src) {
        if (src) { pmImg.src = src; pmPreview.classList.add('show'); } else { pmImg.removeAttribute('src'); pmPreview.classList.remove('show'); }
        refresh();
    }
    /** শেয়ার মডালে মূল পোস্টের ছোট প্রিভিউ */
    function sharePreview(card) {
        var inner = $('.pc-shared', card);
        if (inner) return inner.cloneNode(true);
        var box = document.createElement('div'); box.className = 'pc-shared';
        var media = $(':scope > .pc-media', card); if (media) box.appendChild(media.cloneNode(true));
        var wrap = document.createElement('div'); wrap.className = 'pc-shared-in';
        var head = $(':scope > .pc-head', card).cloneNode(true);
        var mw = $('.pc-menu-wrap', head); if (mw) mw.remove();
        var lbl = $('.pc-shared-lbl', head); if (lbl) lbl.remove();
        var av = $('.pc-avatar', head); if (av) av.classList.add('sm');
        wrap.appendChild(head);
        var body = $(':scope > .pc-body', card);
        if (body) { var b = body.cloneNode(true); var t = $('.pc-text', b); if (t) t.classList.remove('big'); wrap.appendChild(b); }
        box.appendChild(wrap);
        return box;
    }
    function setShareUi(on, card) {
        pmAdd.hidden = on; pmShared.hidden = !on; pmShared.innerHTML = '';
        if (on && card) pmShared.appendChild(sharePreview(card));
        pmText.setAttribute('placeholder', on ? 'এটা নিয়ে কিছু বলুন... (না লিখলেও চলবে)' : PH_DEFAULT);
    }
    function openShare(card) {
        var target = card.getAttribute('data-share-target');
        if (!target) return;
        sharing = { id: target, card: card };
        editing = null; removeImage = false; pmFile.value = ''; setPreview(null);
        $('#pmTitle').textContent = 'Share post';
        pmSubmit.textContent = 'Share now';
        pmText.value = ''; pmPriv.value = 'Public'; paintPrivacy();
        // শেয়ার করা পোস্ট আবার শেয়ার করলে মূলটার প্রিভিউ
        setShareUi(true, card);
        pmCopy.hidden = false;
        pm.classList.add('open'); document.documentElement.style.overflow = 'hidden';
        refresh();
        setTimeout(function () { autoGrow(pmText); pmText.focus(); }, 30);
    }
    pmCopy.addEventListener('click', async function () {
        if (!sharing) return;
        var url = location.origin + '/Post/Details/' + sharing.id;
        try { await navigator.clipboard.writeText(url); toast('লিংক কপি হয়েছে।'); } catch (err) { window.prompt('লিংকটি কপি করুন:', url); }
    });

    function openComposer(card, focusAudience) {
        sharing = null; pmCopy.hidden = true;
        setShareUi(!!(card && card.getAttribute('data-share') === '1'), card && $('.pc-shared', card) ? card : null);
        editing = card || null; removeImage = false; pmFile.value = '';
        $('#pmTitle').textContent = card ? (focusAudience ? 'Edit audience' : 'Edit post') : 'Create post';
        pmSubmit.textContent = card ? 'Save' : 'Post';
        pmText.value = card ? card.getAttribute('data-content') : (lsGet('pc_draft') || '');
        pmPriv.value = card ? card.getAttribute('data-privacy') : (lsGet('pc_privacy') || 'Public');
        if (!PV[pmPriv.value]) pmPriv.value = 'Public';
        hadImage = !!(card && card.getAttribute('data-image'));
        setPreview(hadImage ? card.getAttribute('data-image') : null);
        paintPrivacy();
        pm.classList.add('open'); document.documentElement.style.overflow = 'hidden';
        setTimeout(function () { autoGrow(pmText); (focusAudience ? pmPriv : pmText).focus(); }, 30);
    }
    window.pcOpenComposer = openComposer;
    function closeComposer() {
        if (!editing && !sharing && pmText.value.trim()) lsSet('pc_draft', pmText.value); else if (!editing && !sharing) lsSet('pc_draft', '');
        pm.classList.remove('open'); document.documentElement.style.overflow = ''; editing = null; sharing = null;
        setShareUi(false); pmCopy.hidden = true;
    }
    pm.addEventListener('click', function (e) { if (e.target === pm || e.target.closest('[data-pm="close"]')) closeComposer(); });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || e.defaultPrevented) return;
        if (pm.classList.contains('open')) closeComposer();
        if (rl.classList.contains('open')) closeRl();
        $('#pcLightbox').classList.remove('open');
    });
    pmPriv.addEventListener('change', paintPrivacy);
    pmText.addEventListener('input', function () { autoGrow(pmText); refresh(); });
    pmFile.addEventListener('change', function () {
        var f = pmFile.files[0]; if (!f) return;
        if (!/^image\/(jpeg|png|gif|webp)$/.test(f.type)) { toast('শুধু JPG, PNG, GIF বা WEBP ছবি দেওয়া যাবে।', true); pmFile.value = ''; return; }
        if (f.size > 5 * 1024 * 1024) { toast('ছবি ৫ MB এর কম হতে হবে।', true); pmFile.value = ''; return; }
        var r = new FileReader(); r.onload = function (ev) { setPreview(ev.target.result); }; r.readAsDataURL(f);
    });
    pmForm.addEventListener('click', function (e) {
        if (e.target.closest('[data-pm="remove-image"]')) { pmFile.value = ''; if (hadImage) removeImage = true; setPreview(null); }
    });
    pmForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (pmSubmit.disabled) return;
        busy = true; refresh();
        var label = pmSubmit.textContent; pmSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        var fd = new FormData();
        fd.append('content', pmText.value);
        fd.append('privacy', pmPriv.value);
        if (sharing) {
            fd.append('postId', sharing.id);
            try {
                var ds = await post('/Post/Share', fd);
                if (!ds.success) { toast(ds.message || 'Failed', true); return; }
                $$('.pc[data-id="' + ds.originalId + '"] .pc-sh-n').forEach(function (b) {
                    b.hidden = !ds.shareCount; b.innerHTML = '<span>' + ds.shareCount + '</span> share' + (ds.shareCount === 1 ? '' : 's');
                });
                var sel = htmlToEl(ds.html); sel.classList.add('pc-new');
                fillMe(sel); initCard(sel);
                pmText.value = '';
                closeComposer();
                if (window.PC_TIMELINE_OWNER === ME.id) document.dispatchEvent(new CustomEvent('pc:created', { detail: { el: sel } }));
                toast(ds.message + ' প্রোফাইলে দেখতে My Profile এ যান।');
            } catch (err) { toast('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
            finally { busy = false; pmSubmit.textContent = label; refresh(); }
            return;
        }
        if (pmFile.files[0]) fd.append('image', pmFile.files[0]);
        if (editing) { fd.append('postId', editing.getAttribute('data-id')); if (removeImage && !pmFile.files[0]) fd.append('removeImage', 'true'); }
        try {
            var d = await post(editing ? '/Post/Update' : '/Post/Create', fd);
            if (!d.success) { toast(d.message || 'Failed', true); return; }
            var el = htmlToEl(d.html); el.classList.add('pc-new');
            if (editing) {
                var wasOpen = editing.classList.contains('show-comments');
                editing.replaceWith(el); if (wasOpen) el.classList.add('show-comments');
            } else {
                lsSet('pc_draft', ''); lsSet('pc_privacy', pmPriv.value);
                document.dispatchEvent(new CustomEvent('pc:created', { detail: { el: el } }));
            }
            fillMe(el); initCard(el);
            editing = null; pmText.value = '';
            closeComposer();
            toast(d.message);
        } catch (err) { toast('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { busy = false; pmSubmit.textContent = label; refresh(); }
    });

    // কমেন্ট থাকলে কমেন্ট অংশ শুরুতেই দেখাই
    function initCard(card) { if ($('.cm-list > .cm', card)) card.classList.add('has-comments'); }
    function initPage() { fillMe(document); $$('.pc').forEach(initCard); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPage); else initPage();
    window.pcInitCards = function (root) { $$('.pc', root).forEach(function (c) { fillMe(c); initCard(c); }); };
})();
</script>
