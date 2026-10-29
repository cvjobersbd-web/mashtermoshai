<?php
/**
 * স্টুডেন্ট / টিচারের প্রোফাইল (লার্নিং প্রোফাইল ডিজাইন)
 * বাম: মেনু · মাঝে: স্ট্যাট কার্ড + পোস্ট · ডান: About Me + My Tutors
 * সেকশন: overview | profile | tutors | friends | circulars | saved | goals | activities | settings
 */
$u = $Model->User;
$rel = $Model->Relation;
$isOwner = $Model->IsOwner;
$isTeacher = $u->Role === 'Teacher';
$name = !is_blank($u->FullName) ? $u->FullName : $u->UserName;
$firstName = explode(' ', trim($name))[0];
$ViewData['Title'] = $name;
$img = is_blank($u->ProfileImageLink) ? null : $u->ProfileImageLink;
$ti = $Model->TeacherInfo;
$canPrivate = $Model->CanSeePrivate;
$tab = $Model->Tab;
$friends = $Model->Friends;
$intro = $Model->Intro;
$streak = $Model->Streak;
$goals = $Model->Goals;
$primaryGoal = $goals[0] ?? null;
if ($primaryGoal && !$primaryGoal->IsPrimary) foreach ($goals as $g) if ($g->IsPrimary) { $primaryGoal = $g; break; }
$connected = $Model->Connected;
$saved = $Model->SavedItems;
$cover = $Model->Extras->CoverImageUrl;
$coverY = $Model->Extras->CoverPosY;
$location = implode(', ', array_filter([trim($intro->City), trim((string)$u->Country)]));
$roleLabel = $isTeacher ? 'Teacher' : 'Student';
$peopleLabel = $isTeacher ? 'My Students' : 'My Tutors';
$isAdminViewer = $Model->IsAdminViewer;

$me = obj(['Id' => (int)current_user_id(), 'Name' => session_get('UserFullName') ?? session_get('UserName') ?? 'User', 'Image' => session_get('ProfileImageLink')]);

// বাম মেনু: [key, লেবেল, আইকন, শুধু-মালিক?]
$nav = [
    ['overview', 'Overview', 'fa-house', false],
    ['profile', $isOwner ? 'My Profile' : 'Profile', 'fa-user', false],
    ['tutors', $peopleLabel, $isTeacher ? 'fa-user-graduate' : 'fa-chalkboard-user', true],
    ['friends', 'Friends', 'fa-user-group', false],
    ['circulars', 'My Circulars', 'fa-file-lines', true],
    ['saved', 'Saved Items', 'fa-bookmark', true],
    ['goals', $isOwner ? 'My Goals' : 'Goals', 'fa-bullseye', false],
    ['activities', $isOwner ? 'My Activities' : 'Activities', 'fa-chart-simple', false],
    ['settings', 'Settings', 'fa-gear', true],
];
$nav = array_values(array_filter($nav, fn($n) => (!$n[3] || $isOwner) && !($n[0] === 'circulars' && $isTeacher)));

$days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$savedThumbs = array_slice(array_values(array_filter($saved, fn($s) => $s->Available && $s->Image)), 0, 3);
$typeLabel = ['post' => ['Post', 'fa-newspaper'], 'tutor' => ['Tutor', 'fa-chalkboard-user'], 'circular' => ['Circular', 'fa-bullhorn'],
              'tuition' => ['Tuition', 'fa-clipboard-list'], 'notice' => ['Notice', 'fa-bell']];
$fmtMin = function (int $m): string { $h = intdiv($m, 60); $r = $m % 60; return $h ? $h . 'h' . ($r ? ' ' . $r . 'm' : '') : $r . 'm'; };
?>
<?= csrf_field() ?>
<?php partial('Friends/_Assets'); ?>
<?php partial('Post/_Assets', ['me' => $me]); ?>

<style>
    :root {
        --pf-bg: #F4F7FB; --pf-card: #fff; --pf-line: #E8EEF5; --pf-text: #0F1B2D; --pf-muted: #64748B; --pf-soft: #94A3B8;
        --pf-blue: #1D63E0; --pf-blue-soft: #EAF1FE; --pf-shadow: 0 1px 2px rgba(15,27,45,.04), 0 4px 16px rgba(15,27,45,.04);
    }
    .pf-page { background: var(--pf-bg); padding: 20px 0 60px; min-height: 80vh; color: var(--pf-text); }
    .pf-page h1, .pf-page h2, .pf-page h3, .pf-page h4, .pf-page h5 { color: var(--pf-text); font-family: inherit; }
    .pf-wrap { max-width: 1460px; margin: 0 auto; padding: 0 20px; }
    .pf-card { background: var(--pf-card); border: 1px solid var(--pf-line); border-radius: 16px; box-shadow: var(--pf-shadow); }

    /* ================= HEADER ================= */
    .pf-head { position: relative; overflow: visible; }
    .ps-cover {
        height: 178px; border-radius: 16px 16px 0 0; position: relative; overflow: hidden;
        background: linear-gradient(115deg, #0B2447 0%, #19376D 45%, #1D63E0 100%);
    }
    .ps-cover::before { content: ''; position: absolute; inset: 0; z-index: 0;
        background: radial-gradient(circle at 85% 30%, rgba(255,214,102,.35) 0%, transparent 35%), radial-gradient(circle at 15% 120%, rgba(2,184,245,.45) 0%, transparent 40%); }
    .ps-cover::after { content: ''; position: absolute; inset: 0; opacity: .1; z-index: 0; background-image: radial-gradient(#fff 1px, transparent 1.2px); background-size: 20px 20px; }
    .ps-cover > img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; user-select: none; -webkit-user-drag: none; cursor: zoom-in; }
    .ps-cover.has-img::before, .ps-cover.has-img::after { display: none; }
    .ps-cover.repo > img { cursor: grab; touch-action: none; }
    .ps-cover.repo.dragging > img { cursor: grabbing; }
    .cv-edit-wrap { position: absolute; right: 22px; bottom: 16px; z-index: 4; }
    .cv-btn { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 10px; border: none; font-weight: 600; font-size: 14.5px; cursor: pointer; }
    .cv-btn.light { background: #fff; color: var(--pf-text); box-shadow: 0 4px 14px rgba(0,0,0,.18); }
    .cv-btn.light:hover { background: #F4F7FB; }
    .cv-btn.primary { background: var(--pf-blue); color: #fff; }
    .cv-btn.ghost { background: rgba(255,255,255,.2); color: #fff; border: 1px solid rgba(255,255,255,.45); }
    .cv-btn:disabled { opacity: .6; }
    .cv-bar { position: absolute; z-index: 5; left: 0; right: 0; top: 0; display: none; justify-content: flex-end; gap: 8px; padding: 12px 16px; background: linear-gradient(rgba(0,0,0,.55), transparent); }
    .cv-hint { position: absolute; z-index: 5; left: 50%; top: 50%; transform: translate(-50%, -50%); display: none; pointer-events: none;
        background: rgba(0,0,0,.6); color: #fff; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; white-space: nowrap; }
    .ps-cover.repo .cv-bar { display: flex; } .ps-cover.repo .cv-hint { display: block; } .ps-cover.repo .cv-edit-wrap { display: none; }
    .av-menu, .cv-menu { position: absolute; z-index: 30; min-width: 230px; background: #fff; border-radius: 12px; padding: 6px; display: none; text-align: left;
        box-shadow: 0 14px 34px rgba(15,27,45,.2), 0 0 0 1px rgba(15,27,45,.05); }
    .cv-menu { right: 0; bottom: calc(100% + 6px); }
    .av-menu { left: 0; top: calc(100% + 6px); }
    .av-menu.open, .cv-menu.open { display: block; }
    .av-menu button, .cv-menu button { display: flex; gap: 12px; align-items: center; width: 100%; padding: 10px 12px; border: none; background: none; border-radius: 8px;
        font-weight: 600; font-size: 14.5px; color: var(--pf-text); cursor: pointer; text-align: left; }
    .av-menu button[hidden], .cv-menu button[hidden] { display: none; }
    .av-menu button:hover, .cv-menu button:hover { background: #F1F5F9; }
    .av-menu button i, .cv-menu button i { width: 18px; color: var(--pf-muted); }
    .cv-menu button.danger, .cv-menu button.danger i { color: #DC2626; }

    .pf-info { display: flex; gap: 26px; align-items: flex-start; padding: 0 28px 22px 32px; }
    .ps-avatar-wrap { position: relative; margin-top: -96px; flex-shrink: 0; z-index: 3; }
    .ps-avatar { width: 184px; height: 184px; border-radius: 50%; border: 6px solid #fff; position: relative; overflow: hidden; cursor: pointer;
        background: linear-gradient(135deg, #0B2447, #1D63E0); color: #fff; font-size: 58px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 28px rgba(15,27,45,.18); }
    .ps-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ps-avatar.uploading::after { content: ''; position: absolute; inset: 0; z-index: 2; background: rgba(255,255,255,.6); }
    .ps-avatar-edit { position: absolute; right: 10px; bottom: 14px; z-index: 3; width: 42px; height: 42px; border-radius: 50%; background: #fff; border: none;
        color: var(--pf-text); display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; box-shadow: 0 3px 10px rgba(15,27,45,.2); }
    .ps-avatar-edit:hover { background: #F1F5F9; }
    .pf-id { flex: 1; min-width: 0; padding-top: 16px; }
    .pf-name { font-size: 30px; font-weight: 700; margin: 0; line-height: 1.2; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; word-break: break-word; }
    .pf-name .fa-circle-check { color: var(--pf-blue); font-size: 22px; }
    .pf-warn { display: inline-flex; gap: 6px; align-items: center; background: #FDECEC; color: #B91C1C; font-size: 12.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }
    .pf-meta { display: flex; flex-wrap: wrap; gap: 8px 26px; margin-top: 10px; color: var(--pf-muted); font-size: 14.5px; }
    .pf-meta span { display: inline-flex; align-items: center; gap: 8px; }
    .pf-meta i { color: #7C8798; }
    .pf-bio { margin-top: 10px; font-size: 15px; color: #334155; display: flex; align-items: center; gap: 10px; }
    .pf-bio.empty { color: var(--pf-soft); font-style: italic; }
    .pf-pen { border: none; background: none; color: var(--pf-muted); cursor: pointer; padding: 4px; border-radius: 6px; }
    .pf-pen:hover { background: #F1F5F9; color: var(--pf-text); }
    .pf-actions { padding-top: 22px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .pf-actions .fr-actions { margin: 0; width: auto; min-width: 220px; }
    .pf-actions .fr-btn { height: 42px; font-size: 14.5px; }
    .pf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 44px; padding: 0 20px; border-radius: 12px; border: 1px solid var(--pf-line);
        background: #F8FAFC; color: var(--pf-text); font-weight: 600; font-size: 15px; cursor: pointer; text-decoration: none; white-space: nowrap; }
    .pf-btn:hover { background: #EEF2F7; color: var(--pf-text); }
    .pf-btn.primary { background: var(--pf-blue); border-color: var(--pf-blue); color: #fff; }
    .pf-btn.primary:hover { background: #1853BF; color: #fff; }
    .pf-btn.on { background: var(--pf-blue-soft); border-color: #C7D9FB; color: var(--pf-blue); }

    /* ================= BODY ================= */
    .pf-body { display: grid; grid-template-columns: 250px minmax(0, 1fr); gap: 18px; margin-top: 18px; align-items: start; }
    .pf-nav-col { position: sticky; top: 96px; display: flex; flex-direction: column; gap: 18px; }
    .pf-nav { padding: 12px; }
    .pf-nav a { display: flex; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 12px; color: #334155; text-decoration: none; font-size: 15.5px; font-weight: 500; }
    .pf-nav a i { width: 22px; text-align: center; font-size: 17px; color: #64748B; }
    .pf-nav a:hover { background: #F4F7FB; color: var(--pf-text); }
    .pf-nav a.active { background: var(--pf-blue-soft); color: var(--pf-blue); font-weight: 600; }
    .pf-nav a.active i { color: var(--pf-blue); }
    .pf-nav a .pf-n { margin-left: auto; font-size: 12px; font-weight: 700; background: #F1F5F9; color: #475569; padding: 1px 8px; border-radius: 10px; }
    .pf-nav a.active .pf-n { background: #fff; color: var(--pf-blue); }
    .pf-promo { position: relative; overflow: hidden; padding: 20px 18px 18px; min-height: 196px; display: flex; flex-direction: column; justify-content: space-between;
        background: linear-gradient(160deg, #FFF7E8 0%, #EAF6FF 100%); border-color: #F2E7D0; }
    .pf-promo h5 { position: relative; z-index: 1; margin: 0; font-size: 22px; line-height: 1.35; font-weight: 800; color: #0B3B8C; text-align: right; }
    .pf-promo-art { position: absolute; left: 14px; bottom: 54px; font-size: 64px; line-height: 1; filter: drop-shadow(0 6px 10px rgba(29,99,224,.18)); }
    .pf-promo-art span { position: absolute; font-size: 30px; }
    .pf-promo a { position: relative; z-index: 1; align-self: flex-end; display: inline-flex; align-items: center; gap: 8px; height: 36px; padding: 0 14px; border-radius: 10px;
        background: var(--pf-blue); color: #fff; font-size: 13.5px; font-weight: 600; text-decoration: none; }
    .pf-promo a:hover { background: #1853BF; color: #fff; }

    .pf-sec { display: none; }
    .pf-sec.active { display: block; animation: pfIn .25s ease; }
    @keyframes pfIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .pf-sec-h { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .pf-sec-h h2 { margin: 0; font-size: 22px; font-weight: 700; }
    .pf-sec-h p { margin: 2px 0 0; color: var(--pf-muted); font-size: 14px; }

    /* stats */
    .pf-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
    .st { display: flex; gap: 14px; padding: 18px; min-height: 120px; text-decoration: none; color: inherit; position: relative; transition: transform .15s, box-shadow .15s; }
    a.st:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15,27,45,.08); color: inherit; }
    .st-ico { width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 21px; }
    .st-body { flex: 1; min-width: 0; }
    .st-t { font-size: 14.5px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .st-t i.info { color: var(--pf-soft); font-size: 13px; cursor: help; }
    .st-v { display: flex; align-items: baseline; gap: 8px; margin-top: 8px; }
    .st-v b { font-size: 28px; line-height: 1; font-weight: 700; }
    .st-v span { color: var(--pf-muted); font-size: 15px; }
    .st-fire { background: #FFF1E6; color: #F97316; }
    .st-goal { background: #E7F8EE; color: #16A34A; }
    .st-tut { background: #F1EAFE; color: #7C3AED; }
    .st-save { background: #FDECEE; color: #E11D48; }
    .st-friend { background: #E8F3FF; color: var(--pf-blue); }
    .st-post { background: #FFF6DB; color: #D97706; }
    .st.fire-card { background: linear-gradient(135deg, #FFF8F1 0%, #fff 60%); }
    .st-dots { display: flex; gap: 7px; margin-top: 12px; }
    .st-dots i { width: 13px; height: 13px; border-radius: 50%; background: #E5E7EB; display: block; }
    .st-dots i.on { background: #F59E0B; box-shadow: 0 0 0 2px #FFEDD5; }
    .st-dots i.today { outline: 2px solid #FDBA74; outline-offset: 1px; }
    .st-goal-t { font-size: 18px; font-weight: 700; margin-top: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .st-prog { display: flex; align-items: center; gap: 10px; margin-top: 12px; }
    .st-prog .bar { flex: 1; height: 8px; background: #E8EEF5; border-radius: 4px; overflow: hidden; }
    .st-prog .bar span { display: block; height: 100%; background: linear-gradient(90deg, #2F7BF5, #1D63E0); border-radius: 4px; transition: width .6s; }
    .st-prog b { color: var(--pf-blue); font-size: 14.5px; }
    .st-empty { font-size: 13.5px; color: var(--pf-muted); margin-top: 8px; }
    .st-empty b { color: var(--pf-blue); }
    .st-faces { display: flex; align-items: center; margin-top: 10px; }
    .st-faces .fc, .st-faces .plus { width: 34px; height: 34px; border-radius: 50%; border: 2px solid #fff; margin-left: -8px; overflow: hidden; position: relative;
        background: linear-gradient(135deg, #0B2447, #1D63E0); color: #fff; font-size: 11.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .st-faces .fc:first-child { margin-left: 0; }
    .st-faces .fc img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .st-faces .plus { background: #F1F5F9; color: #475569; font-size: 14px; border-color: #fff; }
    .st-thumbs { position: absolute; right: 44px; bottom: 18px; display: flex; }
    .st-thumbs span { width: 46px; height: 56px; border-radius: 8px; overflow: hidden; margin-left: -26px; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(15,27,45,.15); background: #E2E8F0; }
    .st-thumbs span:first-child { margin-left: 0; }
    .st-thumbs img { width: 100%; height: 100%; object-fit: cover; }
    .st-arrow { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--pf-line);
        background: #fff; display: flex; align-items: center; justify-content: center; color: #475569; font-size: 12px; }

    .ov-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }
    .ov-right { display: flex; flex-direction: column; gap: 18px; position: sticky; top: 96px; }

    /* composer */
    .pf-compose { padding: 16px 18px; margin-bottom: 18px; }
    .pf-compose-top { display: flex; gap: 12px; align-items: center; }
    .pf-compose-top .me-avatar { width: 46px; height: 46px; }
    .pf-compose-in { flex: 1; text-align: left; height: 48px; border-radius: 24px; border: 1px solid var(--pf-line); background: #F4F7FB; padding: 0 20px; color: #7C8798;
        font-size: 15.5px; cursor: pointer; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .pf-compose-in:hover { background: #EEF2F7; }
    .pf-chips { display: flex; gap: 12px; margin-top: 14px; padding-left: 58px; overflow-x: auto; scrollbar-width: none; }
    .pf-chips::-webkit-scrollbar { display: none; }
    .pf-chip { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 9px; height: 40px; padding: 0 18px; border-radius: 12px; border: none; cursor: pointer;
        font-size: 14px; font-weight: 500; color: #334155; transition: transform .12s, filter .12s; }
    .pf-chip:hover { filter: brightness(.97); transform: translateY(-1px); }
    .pf-chip i { font-size: 17px; }
    .ch-photo { background: #EAF7EE; } .ch-photo i { color: #22A35A; }
    .ch-study { background: #F2EEFF; } .ch-study i { color: #7C3AED; }
    .ch-q { background: #F2EEFF; } .ch-q i { color: #6D28D9; }
    .ch-ach { background: #FFF4E2; } .ch-ach i { color: #F59E0B; }
    .ch-poll { background: #FDECEE; } .ch-poll i { color: #E11D48; }

    .pf-feed-h { display: flex; align-items: center; justify-content: space-between; margin: 0 2px 12px; }
    .pf-feed-h h3 { margin: 0; font-size: 18px; font-weight: 700; }
    .pf-feed-h span { color: var(--pf-muted); font-size: 14px; }
    #psFeed .pc { border-radius: 16px; border-color: var(--pf-line); box-shadow: var(--pf-shadow); }

    /* right column cards */
    .rc { padding: 18px; }
    .rc-h { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .rc-h h4 { margin: 0; font-size: 17px; font-weight: 700; }
    .rc-h a, .rc-h button { font-size: 14px; color: var(--pf-blue); text-decoration: none; border: none; background: none; cursor: pointer; font-weight: 500; padding: 0; }
    .rc-h a:hover, .rc-h button:hover { text-decoration: underline; }
    .am-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 13px; }
    .am-list li { display: flex; gap: 12px; align-items: center; font-size: 14.5px; color: #334155; }
    .am-list li i { width: 20px; text-align: center; color: #7C8798; font-size: 15px; }
    .am-list li.muted { color: var(--pf-soft); font-style: italic; }
    .am-subj { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0 0 32px; }
    .am-subj span { padding: 5px 12px; border-radius: 16px; background: #F1F5F9; font-size: 12.5px; color: #334155; border: 1px solid #E8EEF5; }
    .am-quote { margin-top: 16px; padding: 14px 44px 14px 46px; border-radius: 12px; background: #F4F7FB; color: #475569; font-size: 14.5px; position: relative; line-height: 1.5; }
    .am-quote::before, .am-quote::after { position: absolute; font-size: 34px; color: #C9D3E1; font-family: Georgia, serif; line-height: 1; }
    .am-quote::before { content: '\201C'; left: 14px; top: 8px; }
    .am-quote::after { content: '\201D'; right: 14px; bottom: -4px; }
    .tu-list { display: flex; flex-direction: column; }
    .tu-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-top: 1px solid #F1F5F9; }
    .tu-item:first-child { border-top: none; padding-top: 0; }
    .tu-av { width: 50px; height: 50px; border-radius: 50%; overflow: hidden; position: relative; flex-shrink: 0; background: linear-gradient(135deg, #0B2447, #1D63E0);
        color: #fff; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .tu-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .tu-who { flex: 1; min-width: 0; }
    .tu-who a { display: block; font-weight: 600; font-size: 15px; color: var(--pf-text); text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tu-who a:hover { text-decoration: underline; }
    .tu-who small { color: var(--pf-muted); font-size: 13.5px; }
    .tu-view { height: 38px; padding: 0 18px; border-radius: 10px; background: var(--pf-blue-soft); color: var(--pf-blue); font-weight: 600; font-size: 14px;
        display: inline-flex; align-items: center; text-decoration: none; flex-shrink: 0; }
    .tu-view:hover { background: #DCE8FD; color: var(--pf-blue); }
    .rc-empty { color: var(--pf-muted); font-size: 14px; margin: 0; line-height: 1.6; }
    .rc-empty a { color: var(--pf-blue); font-weight: 600; }

    /* empty */
    .pf-empty { padding: 44px 20px; text-align: center; color: var(--pf-muted); }
    .pf-empty .ei { width: 70px; height: 70px; border-radius: 50%; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; font-size: 28px; background: #F1F5F9; color: #94A3B8; }
    .pf-empty h5 { color: var(--pf-text); font-weight: 700; margin: 0 0 6px; font-size: 17px; }
    .pf-empty p { margin: 0 auto; max-width: 420px; font-size: 14px; }
    .pf-empty .pf-btn { margin-top: 16px; }

    /* about (profile section) */
    .ab-sections { display: flex; flex-direction: column; gap: 16px; }
    .ab-wrap { display: block; }
    .ab-nav { display: none; }
    .ab-card { background: #fff; border-radius: 16px; border: 1px solid var(--pf-line); padding: 20px 22px; box-shadow: var(--pf-shadow); scroll-margin-top: 100px; }
    .ab-card-h { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .ab-card-h h3 { font-size: 17px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
    .ab-card-h h3 i { color: var(--pf-blue); }
    .ab-st { font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; }
    .ab-st.ok { background: #E7F8EE; color: #15803D; } .ab-st.no { background: #FFF4E5; color: #B45309; }
    .ab-edit { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 600; color: var(--pf-blue); text-decoration: none;
        padding: 7px 14px; border-radius: 10px; background: var(--pf-blue-soft); }
    .ab-edit:hover { background: #DCE8FD; color: var(--pf-blue); }
    .ab-rows { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 24px; }
    .ab-row { display: flex; gap: 12px; align-items: flex-start; min-width: 0; }
    .ab-row > i { width: 38px; height: 38px; border-radius: 10px; background: #F4F7FB; color: var(--pf-blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ab-row small { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; color: var(--pf-soft); }
    .ab-row span, .ab-row a { display: block; font-size: 15px; font-weight: 600; color: var(--pf-text); word-break: break-word; }
    .ab-row a { color: var(--pf-blue); text-decoration: none; }
    .ab-row .lock { color: var(--pf-soft); font-weight: 500; font-size: 13.5px; }
    .ab-row .empty { color: #B6C0CD; font-weight: 500; font-style: italic; font-size: 14px; }
    .ab-none { color: var(--pf-soft); font-size: 14px; margin: 0; }
    .ab-tutor { display: flex; gap: 14px; align-items: center; padding: 14px; border-radius: 12px; background: #F4F7FB; margin-bottom: 14px; }
    .ab-tutor i { font-size: 28px; }
    .ab-tutor strong { display: block; font-size: 15.5px; }
    .ab-tutor p { margin: 2px 0 0; color: var(--pf-muted); font-size: 13.5px; }
    .ps-btn { display: inline-flex; align-items: center; gap: 7px; height: 40px; padding: 0 16px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; text-decoration: none; }
    .ps-btn.primary { background: var(--pf-blue); color: #fff; }

    /* goals */
    .gl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
    .gl-card { background: #fff; border: 1px solid var(--pf-line); border-radius: 16px; padding: 18px; box-shadow: var(--pf-shadow); }
    .gl-card.primary { border-color: #BFD3F7; box-shadow: 0 0 0 3px #EAF1FE, var(--pf-shadow); }
    .gl-top { display: flex; gap: 14px; align-items: flex-start; }
    .gl-ico { width: 46px; height: 46px; border-radius: 14px; background: #E7F8EE; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
    .gl-card.done .gl-ico { background: #DCFCE7; }
    .gl-head { flex: 1; min-width: 0; }
    .gl-head h4 { margin: 6px 0 0; font-size: 18px; font-weight: 700; word-break: break-word; }
    .gl-tags { display: flex; gap: 6px; flex-wrap: wrap; }
    .gl-cat, .gl-main, .gl-ok { font-size: 11px; font-weight: 800; padding: 2px 9px; border-radius: 10px; text-transform: uppercase; letter-spacing: .3px; }
    .gl-cat { background: #F1F5F9; color: #475569; } .gl-main { background: #FEF3C7; color: #B45309; } .gl-ok { background: #DCFCE7; color: #15803D; }
    .gl-date { font-size: 13px; color: var(--pf-muted); margin-top: 4px; }
    .gl-date b { color: #EA580C; }
    .gl-menu-wrap { position: relative; }
    .gl-menu-btn { width: 34px; height: 34px; border-radius: 50%; border: none; background: none; color: var(--pf-muted); cursor: pointer; }
    .gl-menu-btn:hover { background: #F1F5F9; }
    .gl-menu { position: absolute; right: 0; top: 38px; z-index: 20; min-width: 190px; background: #fff; border-radius: 12px; padding: 6px; display: none;
        box-shadow: 0 14px 34px rgba(15,27,45,.18), 0 0 0 1px rgba(15,27,45,.05); }
    .gl-menu.open { display: block; }
    .gl-menu button { display: flex; gap: 10px; align-items: center; width: 100%; padding: 9px 12px; border: none; background: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; text-align: left; color: var(--pf-text); }
    .gl-menu button:hover { background: #F1F5F9; }
    .gl-menu button.danger { color: #DC2626; }
    .gl-prog { display: flex; align-items: center; gap: 12px; margin-top: 16px; }
    .gl-bar { flex: 1; height: 10px; background: #E8EEF5; border-radius: 5px; overflow: hidden; }
    .gl-bar span { display: block; height: 100%; background: linear-gradient(90deg, #2F7BF5, #1D63E0); border-radius: 5px; transition: width .6s; }
    .gl-card.done .gl-bar span { background: linear-gradient(90deg, #22C55E, #16A34A); }
    .gl-prog b { font-size: 15px; color: var(--pf-blue); min-width: 42px; text-align: right; }
    .gl-sub { font-size: 12.5px; color: var(--pf-soft); margin-top: 6px; }
    .gl-tasks { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
    .gl-tasks li { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 8px; font-size: 14px; }
    .gl-tasks li:hover { background: #F8FAFC; }
    .gl-tasks label { flex: 1; display: flex; gap: 10px; align-items: center; margin: 0; cursor: pointer; min-width: 0; }
    .gl-tasks input { width: 18px; height: 18px; accent-color: #16A34A; flex-shrink: 0; }
    .gl-tasks li.done span { text-decoration: line-through; color: var(--pf-soft); }
    .gl-tasks li > i { color: #16A34A; }
    .gl-x { border: none; background: none; color: #CBD5E1; cursor: pointer; width: 26px; height: 26px; border-radius: 50%; }
    .gl-x:hover { background: #FEE2E2; color: #DC2626; }
    .gl-add { display: flex; gap: 8px; margin-top: 10px; }
    .gl-add input { flex: 1; min-width: 0; height: 38px; border: 1.5px dashed #CBD5E1; border-radius: 10px; padding: 0 12px; font-size: 13.5px; outline: none; background: #fff; }
    .gl-add input:focus { border-style: solid; border-color: var(--pf-blue); }
    .gl-add button { width: 38px; height: 38px; border-radius: 10px; border: none; background: var(--pf-blue-soft); color: var(--pf-blue); cursor: pointer; }

    /* activities */
    .act-top { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 16px; }
    .act-big { padding: 20px; display: flex; gap: 16px; align-items: center; }
    .act-big .st-ico { width: 56px; height: 56px; font-size: 26px; border-radius: 16px; }
    .act-big b { font-size: 30px; display: block; line-height: 1.1; }
    .act-big small { color: var(--pf-muted); font-size: 13.5px; }
    .hm { padding: 20px; margin-bottom: 16px; }
    .hm h4 { margin: 0 0 14px; font-size: 16px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
    .hm h4 small { font-weight: 500; color: var(--pf-muted); font-size: 13px; }
    .hm-scroll { overflow-x: auto; padding-bottom: 4px; }
    .hm-grid { display: grid; grid-auto-flow: column; grid-template-rows: repeat(7, 15px); gap: 4px; width: max-content; }
    .hm-grid i { width: 15px; height: 15px; border-radius: 4px; background: #EEF2F7; display: block; }
    .hm-grid i.l1 { background: #CDE2FF; } .hm-grid i.l2 { background: #8DBBFB; } .hm-grid i.l3 { background: #4A8DF2; } .hm-grid i.l4 { background: #1D63E0; }
    .hm-grid i.fut { visibility: hidden; }
    .hm-legend { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--pf-muted); margin-top: 10px; justify-content: flex-end; }
    .hm-legend i { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }
    .act-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 14px; }
    .act-tile { padding: 16px; display: flex; gap: 12px; align-items: center; }
    .act-tile .st-ico { width: 42px; height: 42px; font-size: 17px; border-radius: 12px; }
    .act-tile b { display: block; font-size: 21px; line-height: 1.1; }
    .act-tile small { color: var(--pf-muted); font-size: 12.5px; }

    /* saved */
    .sv-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .sv-filter { height: 36px; padding: 0 16px; border-radius: 18px; border: 1px solid var(--pf-line); background: #fff; font-size: 13.5px; font-weight: 600; color: #475569; cursor: pointer; }
    .sv-filter.active { background: var(--pf-text); border-color: var(--pf-text); color: #fff; }
    .sv-list { display: flex; flex-direction: column; gap: 12px; }
    .sv-item { display: flex; gap: 14px; align-items: center; padding: 14px; transition: opacity .3s, transform .3s; }
    .sv-item.gone { opacity: 0; transform: scale(.98); }
    .sv-thumb { width: 72px; height: 72px; border-radius: 12px; overflow: hidden; flex-shrink: 0; background: #F1F5F9; color: #94A3B8; display: flex; align-items: center; justify-content: center; font-size: 24px; text-decoration: none; }
    .sv-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .sv-body { flex: 1; min-width: 0; }
    .sv-type { font-size: 11px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; color: var(--pf-blue); }
    .sv-body a.sv-title { display: block; font-weight: 600; font-size: 15px; color: var(--pf-text); text-decoration: none; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sv-body a.sv-title:hover { text-decoration: underline; }
    .sv-body small { color: var(--pf-muted); font-size: 13px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sv-un { height: 38px; padding: 0 14px; border-radius: 10px; border: 1px solid var(--pf-line); background: #fff; color: #475569; font-weight: 600; font-size: 13.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; flex-shrink: 0; }
    .sv-un:hover { background: #FEF2F2; color: #DC2626; border-color: #FECACA; }
    .sv-item.unavail .sv-title { color: var(--pf-soft); pointer-events: none; }

    /* circulars */
    .ci-item { padding: 16px 18px; }
    .ci-item + .ci-item { margin-top: 12px; }
    .ci-text { font-size: 15px; white-space: pre-wrap; word-break: break-word; margin: 0 0 10px; }
    .ci-tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .ci-tags span { font-size: 12.5px; background: #F1F5F9; padding: 4px 10px; border-radius: 12px; color: #334155; }
    .ci-foot { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin-top: 12px; padding-top: 12px; border-top: 1px solid #F1F5F9; font-size: 13.5px; color: var(--pf-muted); }
    .ci-foot a { margin-left: auto; }
    .ci-pend { background: #FEF3C7; color: #B45309; font-weight: 700; padding: 2px 9px; border-radius: 10px; font-size: 12px; }

    /* settings */
    .set-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    .set-card { padding: 20px 22px; }
    .set-card h4 { margin: 0 0 4px; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .set-card h4 i { color: var(--pf-blue); }
    .set-card > p { color: var(--pf-muted); font-size: 13.5px; margin: 0 0 14px; }
    .set-links { display: flex; flex-direction: column; gap: 4px; }
    .set-links a, .set-links button { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 10px; color: var(--pf-text); text-decoration: none; font-weight: 500; font-size: 14.5px;
        border: none; background: none; cursor: pointer; text-align: left; width: 100%; }
    .set-links a:hover, .set-links button:hover { background: #F4F7FB; color: var(--pf-text); }
    .set-links i { width: 20px; color: var(--pf-muted); text-align: center; }
    .set-links .chev { margin-left: auto; font-size: 12px; color: var(--pf-soft); }
    .pw-form { display: grid; gap: 12px; }
    .pw-field { position: relative; }
    .pw-field label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 5px; color: #334155; }
    .pw-field input { width: 100%; height: 44px; border: 1.5px solid var(--pf-line); border-radius: 10px; padding: 0 44px 0 14px; font-size: 15px; outline: none; }
    .pw-field input:focus { border-color: var(--pf-blue); box-shadow: 0 0 0 3px var(--pf-blue-soft); }
    .pw-eye { position: absolute; right: 6px; bottom: 4px; width: 36px; height: 36px; border: none; background: none; color: var(--pf-soft); cursor: pointer; }
    .pw-msg { font-size: 13.5px; font-weight: 600; min-height: 18px; }
    .pw-msg.ok { color: #15803D; } .pw-msg.err { color: #B91C1C; }

    /* friends section card */
    .fr-wrap-h { padding: 16px 18px; margin-bottom: 16px; display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; }

    /* modals (intro + goal) */
    .pf-ov { position: fixed; inset: 0; z-index: 2500; background: rgba(15,27,45,.45); display: none; align-items: center; justify-content: center; padding: 16px; }
    .pf-ov.open { display: flex; }
    .pf-modal { width: 100%; max-width: 520px; max-height: calc(100vh - 32px); overflow-y: auto; background: #fff; border-radius: 18px; box-shadow: 0 24px 60px rgba(15,27,45,.3); animation: pfIn .2s ease; }
    .pf-modal-h { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid var(--pf-line); }
    .pf-modal-h h3 { margin: 0; font-size: 19px; font-weight: 700; }
    .pf-x { width: 36px; height: 36px; border-radius: 50%; border: none; background: #F1F5F9; cursor: pointer; font-size: 16px; color: #475569; }
    .pf-modal-b { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; }
    .pf-f label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px; }
    .pf-f label small { font-weight: 500; color: var(--pf-soft); }
    .pf-f input[type=text], .pf-f input[type=date], .pf-f select, .pf-f textarea { width: 100%; border: 1.5px solid var(--pf-line); border-radius: 10px; padding: 10px 12px; font-size: 15px; outline: none; background: #fff; font-family: inherit; }
    .pf-f textarea { resize: vertical; min-height: 70px; }
    .pf-f input:focus, .pf-f select:focus, .pf-f textarea:focus { border-color: var(--pf-blue); box-shadow: 0 0 0 3px var(--pf-blue-soft); }
    .pf-f .cnt { font-size: 12px; color: var(--pf-soft); text-align: right; margin-top: 3px; }
    .pf-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pf-quick { display: flex; flex-wrap: wrap; gap: 6px; }
    .pf-quick button { height: 30px; padding: 0 12px; border-radius: 15px; border: 1px solid var(--pf-line); background: #F8FAFC; font-size: 12.5px; font-weight: 600; cursor: pointer; color: #334155; }
    .pf-quick button:hover { border-color: var(--pf-blue); color: var(--pf-blue); }
    .pf-range { display: flex; align-items: center; gap: 12px; }
    .pf-range input { flex: 1; accent-color: var(--pf-blue); }
    .pf-range b { min-width: 46px; text-align: right; color: var(--pf-blue); }
    .pf-check { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; }
    .pf-check input { width: 18px; height: 18px; accent-color: var(--pf-blue); }
    .pf-modal-f { padding: 0 20px 20px; display: flex; gap: 10px; justify-content: flex-end; }
    .sj-box { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px; border: 1.5px solid var(--pf-line); border-radius: 10px; min-height: 46px; align-items: center; cursor: text; }
    .sj-box:focus-within { border-color: var(--pf-blue); box-shadow: 0 0 0 3px var(--pf-blue-soft); }
    .sj-box span { display: inline-flex; align-items: center; gap: 6px; background: var(--pf-blue-soft); color: var(--pf-blue); font-size: 13px; font-weight: 600; padding: 4px 6px 4px 10px; border-radius: 14px; }
    .sj-box span button { border: none; background: none; color: inherit; cursor: pointer; padding: 0 2px; font-size: 12px; }
    .sj-box input { flex: 1; min-width: 120px; border: none !important; outline: none; box-shadow: none !important; padding: 4px !important; font-size: 14.5px; }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 1280px) {
        .ov-grid { grid-template-columns: minmax(0, 1fr); }
        .ov-right { position: static; display: grid; grid-template-columns: 1fr 1fr; order: -1; }
        .pf-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 992px) {
        .pf-wrap { padding: 0 12px; }
        .pf-body { grid-template-columns: minmax(0, 1fr); }
        .pf-nav-col { position: sticky; top: 72px; z-index: 30; }
        .pf-nav { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; padding: 8px; border-radius: 14px; }
        .pf-nav::-webkit-scrollbar { display: none; }
        .pf-nav a { flex: 0 0 auto; padding: 9px 14px; font-size: 14px; gap: 8px; }
        .pf-nav a i { width: auto; font-size: 15px; }
        .pf-promo { display: none; }
        .pf-info { flex-direction: column; align-items: center; text-align: center; gap: 8px; padding: 0 16px 18px; }
        .ps-avatar-wrap { margin-top: -84px; }
        .ps-avatar { width: 156px; height: 156px; font-size: 48px; }
        .pf-id { padding-top: 0; width: 100%; }
        .pf-name { justify-content: center; font-size: 25px; }
        .pf-meta { justify-content: center; gap: 6px 16px; font-size: 13.5px; }
        .pf-bio { justify-content: center; }
        .pf-actions { padding-top: 6px; justify-content: center; width: 100%; }
        .pf-actions .fr-actions { flex-direction: row; min-width: 0; flex: 1; }
        .pf-actions .fr-actions > * { flex: 1 1 0; width: auto; }
        .pf-actions .fr-icon-only { flex: 0 0 42px !important; width: 42px; }
        .pf-actions .fr-icon-only span { display: none; }
        .set-grid { grid-template-columns: minmax(0, 1fr); }
        .act-top { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .pf-page { padding-top: 0; }
        .pf-wrap { padding: 0; }
        .pf-head { border-radius: 0; border-left: 0; border-right: 0; }
        .ps-cover { height: 150px; border-radius: 0; }
        .cv-edit-wrap { right: 10px; bottom: 10px; }
        .cv-edit-wrap .cv-btn.light span { display: none; }
        .cv-edit-wrap .cv-btn.light { width: 40px; padding: 0; justify-content: center; }
        .cv-hint { font-size: 12px; white-space: normal; text-align: center; width: 80%; }
        .av-menu { left: 50%; transform: translateX(-50%); }
        .pf-body { padding: 0 12px; margin-top: 12px; gap: 12px; }
        .pf-nav-col { top: 64px; margin: 0 -12px; }
        .pf-nav { border-radius: 0; border-left: 0; border-right: 0; }
        .pf-stats { gap: 10px; margin-bottom: 12px; }
        .st { padding: 14px; gap: 10px; min-height: 0; flex-direction: column; }
        .st-ico { width: 40px; height: 40px; font-size: 17px; border-radius: 12px; }
        .st-v b { font-size: 24px; }
        .st-goal-t { font-size: 16px; }
        .st-thumbs { display: none; }
        .ov-right { grid-template-columns: minmax(0, 1fr); gap: 12px; }
        .pf-chips { padding-left: 0; gap: 8px; }
        .pf-chip { height: 36px; padding: 0 12px; font-size: 13px; }
        .ab-rows { grid-template-columns: 1fr; }
        .gl-grid { grid-template-columns: minmax(0, 1fr); }
        .pf-2 { grid-template-columns: 1fr; }
        .pf-ov { padding: 0; align-items: flex-end; }
        .pf-modal { max-width: none; border-radius: 18px 18px 0 0; max-height: 92vh; }
        .sv-thumb { width: 56px; height: 56px; }
        .sv-un span { display: none; }
        #psFeed .pc { border-radius: 0; }
    }
</style>

<div class="pf-page">
    <div class="pf-wrap">
        <!-- ======================= HEADER ======================= -->
        <div class="pf-card pf-head">
            <div class="ps-cover<?= $cover ? ' has-img' : '' ?>" id="psCover">
                <img id="psCoverImg" alt="Cover photo" <?= $cover ? 'src="' . e($cover) . '"' : 'hidden' ?> style="object-position: 50% <?= (int)$coverY ?>%;" draggable="false" />
                <?php if ($isOwner): ?>
                    <div class="cv-hint" id="cvHint"><i class="fas fa-up-down"></i> উপরে-নিচে টেনে ছবিটা ঠিক জায়গায় বসান</div>
                    <div class="cv-bar" id="cvBar">
                        <button type="button" class="cv-btn ghost" data-cv="cancel">Cancel</button>
                        <button type="button" class="cv-btn primary" data-cv="save"><i class="fas fa-check"></i> Save changes</button>
                    </div>
                    <div class="cv-edit-wrap" id="cvEditWrap">
                        <button type="button" class="cv-btn light" data-cv="menu"><i class="fas fa-camera"></i> <span><?= $cover ? 'Edit Cover' : 'Add Cover' ?></span></button>
                        <div class="cv-menu" id="cvMenu">
                            <button type="button" data-cv="upload"><i class="fas fa-upload"></i> Upload photo</button>
                            <button type="button" data-cv="reposition" <?= $cover ? '' : 'hidden' ?>><i class="fas fa-arrows-up-down"></i> Reposition</button>
                            <button type="button" data-cv="remove" class="danger" <?= $cover ? '' : 'hidden' ?>><i class="fas fa-trash-can"></i> Remove</button>
                        </div>
                    </div>
                    <input type="file" id="cvFile" accept="image/jpeg,image/png,image/webp" hidden />
                <?php endif; ?>
            </div>

            <div class="pf-info">
                <div class="ps-avatar-wrap">
                    <div class="ps-avatar" id="psAvatar" role="button" tabindex="0" aria-label="Profile picture">
                        <?= e(initials($name, 'U')) ?>
                        <?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($name) ?>" onerror="this.remove()" /><?php endif; ?>
                    </div>
                    <?php if ($isOwner): ?>
                        <button type="button" class="ps-avatar-edit" id="avEditBtn" title="প্রোফাইল ছবি বদলান" aria-label="Update profile picture"><i class="fas fa-camera"></i></button>
                        <div class="av-menu" id="avMenu">
                            <button type="button" data-av="view" <?= $img ? '' : 'hidden' ?>><i class="fas fa-user-circle"></i> See profile picture</button>
                            <button type="button" data-av="upload"><i class="fas fa-images"></i> Update profile picture</button>
                        </div>
                        <input type="file" id="avFile" accept="image/jpeg,image/png,image/gif,image/webp" hidden />
                    <?php endif; ?>
                </div>

                <div class="pf-id">
                    <h1 class="pf-name"><?= e($name) ?>
                        <?php if ($isTeacher): ?><i class="fas fa-circle-check" title="Verified Teacher"></i><?php endif; ?>
                        <?php if ($Model->IsSuspended): ?><span class="pf-warn"><i class="fas fa-ban"></i> Suspended</span><?php endif; ?>
                    </h1>
                    <div class="pf-meta">
                        <span><i class="fas <?= $isTeacher ? 'fa-chalkboard-user' : 'fa-graduation-cap' ?>"></i> <?= $roleLabel ?></span>
                        <?php if ($location !== ''): ?><span><i class="fas fa-location-dot"></i> <span id="pfLoc"><?= e($location) ?></span></span><?php endif; ?>
                        <span><i class="far fa-calendar"></i> Joined <?= e(fmt_date($u->CreatedAt, 'F Y', '—')) ?></span>
                    </div>
                    <?php if ($intro->Bio !== ''): ?>
                        <div class="pf-bio"><span id="pfBio">“<?= e($intro->Bio) ?>”</span><?php if ($isOwner): ?><button type="button" class="pf-pen" data-intro="bio" aria-label="Edit bio"><i class="fas fa-pen"></i></button><?php endif; ?></div>
                    <?php elseif ($isOwner): ?>
                        <div class="pf-bio empty"><span id="pfBio">নিজের সম্পর্কে এক লাইন লিখুন...</span><button type="button" class="pf-pen" data-intro="bio" aria-label="Add bio"><i class="fas fa-pen"></i></button></div>
                    <?php endif; ?>
                </div>

                <div class="pf-actions">
                    <?php if ($isOwner): ?>
                        <a href="/Profile?section=basic" class="pf-btn"><i class="fas fa-pen"></i> Edit Profile</a>
                    <?php elseif (!$isAdminViewer): ?>
                        <div class="fr-actions" data-uid="<?= (int)$u->Id ?>" data-rel="<?= e($rel) ?>"><?= friend_actions_html((int)$u->Id, $rel) ?></div>
                        <?php if ($isTeacher): ?>
                            <button type="button" class="pf-btn<?= $Model->TutorSaved ? ' on' : '' ?>" data-save-type="tutor" data-save-id="<?= (int)$u->Id ?>">
                                <i class="<?= $Model->TutorSaved ? 'fas' : 'far' ?> fa-bookmark"></i> <span><?= $Model->TutorSaved ? 'Saved' : 'Save' ?></span>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="pf-body">
            <!-- ======================= LEFT NAV ======================= -->
            <div class="pf-nav-col">
                <nav class="pf-card pf-nav" aria-label="Profile sections">
                    <?php foreach ($nav as [$k, $label, $icon]): ?>
                        <a href="?tab=<?= $k ?>" data-tab="<?= $k ?>" class="<?= $tab === $k ? 'active' : '' ?>"><i class="fas <?= $icon ?>"></i> <?= e($label) ?>
                            <?php if ($k === 'saved' && $Model->SavedItems): ?><span class="pf-n" data-saved-count><?= count($saved) ?></span><?php endif; ?>
                            <?php if ($k === 'friends' && $Model->FriendCount): ?><span class="pf-n"><?= (int)$Model->FriendCount ?></span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="pf-card pf-promo">
                    <h5>সঠিক Tutor<br />সঠিক ভবিষ্যৎ</h5>
                    <div class="pf-promo-art" aria-hidden="true">🧑‍💻<span style="left:58px; top:-18px;">📚</span><span style="left:70px; top:24px;">🌱</span></div>
                    <?php if ($isTeacher): ?>
                        <a href="/Tuition/Circular"><i class="fas fa-bullhorn"></i> Tuition খুঁজুন</a>
                    <?php else: ?>
                        <a href="/FindTutor"><i class="fas fa-magnifying-glass"></i> Tutor খুঁজুন</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pf-main">
                <!-- ======================= OVERVIEW ======================= -->
                <section class="pf-sec <?= $tab === 'overview' ? 'active' : '' ?>" id="sec-overview">
                    <div class="pf-stats">
                        <!-- Learning streak -->
                        <a href="?tab=activities" data-tab="activities" class="pf-card st fire-card">
                            <span class="st-ico st-fire"><i class="fas fa-fire"></i></span>
                            <div class="st-body">
                                <div class="st-t">Learning Streak <i class="fas fa-circle-info info" title="টানা কতদিন প্ল্যাটফর্মে লার্নিং অ্যাক্টিভিটি (ভিজিট, পোস্ট, কমেন্ট, গোলের কাজ) হয়েছে। একদিন বাদ গেলে আবার ১ থেকে শুরু।"></i></div>
                                <div class="st-v"><b><?= (int)$streak['current'] ?></b><span><?= $streak['current'] === 1 ? 'day' : 'days' ?></span></div>
                                <div class="st-dots" title="শেষ ৭ দিন">
                                    <?php foreach ($streak['last7'] as $i => $on): ?>
                                        <i class="<?= $on ? 'on' : '' ?><?= $i === 6 ? ' today' : '' ?>" title="<?= $days[(int)date('w', strtotime('-' . (6 - $i) . ' days'))] ?><?= $on ? ' ✓' : '' ?>"></i>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </a>
                        <!-- Goal -->
                        <a href="?tab=goals" data-tab="goals" class="pf-card st" id="stGoal">
                            <span class="st-ico st-goal"><i class="fas fa-bullseye"></i></span>
                            <div class="st-body">
                                <div class="st-t"><?= $isOwner ? 'My Goal' : 'Goal' ?></div>
                                <?php if ($primaryGoal): ?>
                                    <div class="st-goal-t" id="stGoalTitle"><?= e($primaryGoal->Title) ?></div>
                                    <div class="st-prog"><div class="bar"><span id="stGoalBar" style="width: <?= (int)$primaryGoal->Progress ?>%"></span></div><b id="stGoalPct"><?= (int)$primaryGoal->Progress ?>%</b></div>
                                <?php else: ?>
                                    <div class="st-empty"><?= $isOwner ? 'একটা লক্ষ্য ঠিক করুন — <b>Set goal +</b>' : 'এখনো কোনো গোল নেই' ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php if ($isOwner): ?>
                            <!-- My Tutors / Students -->
                            <a href="?tab=tutors" data-tab="tutors" class="pf-card st">
                                <span class="st-ico st-tut"><i class="fas fa-user-group"></i></span>
                                <div class="st-body">
                                    <div class="st-t"><?= $peopleLabel ?></div>
                                    <div class="st-v"><b><?= count($connected) ?></b><span>Active</span></div>
                                    <div class="st-faces">
                                        <?php foreach (array_slice($connected, 0, 3) as $c): ?>
                                            <span class="fc" title="<?= e($c->Name) ?>"><?= e($c->Initials) ?><?php if ($c->Image): ?><img src="<?= e($c->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                                        <?php endforeach; ?>
                                        <span class="plus" title="<?= $isTeacher ? 'Tuition খুঁজুন' : 'Tutor খুঁজুন' ?>"><i class="fas fa-plus"></i></span>
                                    </div>
                                </div>
                            </a>
                            <!-- Saved -->
                            <a href="?tab=saved" data-tab="saved" class="pf-card st">
                                <span class="st-ico st-save"><i class="fas fa-bookmark"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Saved Items</div>
                                    <div class="st-v"><b data-saved-count><?= count($saved) ?></b><span>items</span></div>
                                </div>
                                <?php if ($savedThumbs): ?>
                                    <div class="st-thumbs"><?php foreach ($savedThumbs as $t): ?><span><img src="<?= e($t->Image) ?>" alt="" loading="lazy" onerror="this.parentNode.remove()" /></span><?php endforeach; ?></div>
                                <?php endif; ?>
                                <span class="st-arrow"><i class="fas fa-chevron-right"></i></span>
                            </a>
                        <?php else: ?>
                            <a href="?tab=friends" data-tab="friends" class="pf-card st">
                                <span class="st-ico st-friend"><i class="fas fa-user-group"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Friends</div>
                                    <div class="st-v"><b><?= (int)$Model->FriendCount ?></b><span><?= $Model->MutualCount ? (int)$Model->MutualCount . ' mutual' : 'friends' ?></span></div>
                                    <div class="st-faces">
                                        <?php foreach (array_slice($friends, 0, 4) as $f): ?>
                                            <span class="fc" title="<?= e($f->FullName) ?>"><?= e($f->Initials) ?><?php if ($f->Image): ?><img src="<?= e($f->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </a>
                            <div class="pf-card st">
                                <span class="st-ico st-post"><i class="fas fa-pen-to-square"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Posts</div>
                                    <div class="st-v"><b><?= (int)$Model->PostCount ?></b><span>posts</span></div>
                                    <div class="st-empty"><?= (int)$Model->ActivityStats['study'] ?> study updates</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="ov-grid">
                        <div class="ov-main">
                            <?php if ($isOwner): ?>
                                <div class="pf-card pf-compose">
                                    <div class="pf-compose-top">
                                        <span class="pc-avatar me-avatar"></span>
                                        <button type="button" class="pf-compose-in" data-compose="post">Share your learning journey...</button>
                                    </div>
                                    <div class="pf-chips">
                                        <button type="button" class="pf-chip ch-photo" data-compose="photo"><i class="fas fa-image"></i> Photo</button>
                                        <button type="button" class="pf-chip ch-study" data-compose="study"><i class="fas fa-file-lines"></i> Study Update</button>
                                        <button type="button" class="pf-chip ch-q" data-compose="question"><i class="fas fa-circle-question"></i> Question</button>
                                        <button type="button" class="pf-chip ch-ach" data-compose="achievement"><i class="fas fa-trophy"></i> Achievement</button>
                                        <button type="button" class="pf-chip ch-poll" data-compose="poll"><i class="fas fa-chart-column"></i> Poll</button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="pf-feed-h"><h3>Posts</h3><span id="psPostCount"><?= (int)$Model->PostCount ?> post<?= $Model->PostCount === 1 ? '' : 's' ?></span></div>
                            <div id="psFeed">
                                <?php foreach ($Model->Posts as $p) partial('Post/_Post', ['post' => $p, 'viewerId' => $me->Id, 'viewerIsAdmin' => $isAdminViewer]); ?>
                            </div>
                            <div class="pf-card pf-empty" id="psEmpty" <?= $Model->Posts ? 'hidden' : '' ?>>
                                <div class="ei"><i class="far fa-newspaper"></i></div>
                                <h5><?= $isOwner ? 'আপনার learning journey শুরু করুন' : 'দেখানোর মতো কোনো পোস্ট নেই' ?></h5>
                                <p><?= $isOwner ? 'আজ কী পড়লেন, কী শিখলেন — Study Update দিয়ে শেয়ার করুন।' : ($rel === 'friends' ? e($firstName) . ' এখনো কিছু পোস্ট করেননি।' : 'ফ্রেন্ড হলে শুধু-ফ্রেন্ডদের পোস্টও দেখতে পাবেন।') ?></p>
                            </div>
                            <?php if ($Model->HasMore): ?>
                                <button type="button" class="pc-loadmore" id="psMore"><i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন</button>
                            <?php endif; ?>
                        </div>

                        <aside class="ov-right">
                            <!-- About Me -->
                            <div class="pf-card rc">
                                <div class="rc-h"><h4><?= $isOwner ? 'About Me' : 'About' ?></h4><?php if ($isOwner): ?><button type="button" data-intro="all">Edit</button><?php endif; ?></div>
                                <ul class="am-list">
                                    <li><i class="fas <?= $isTeacher ? 'fa-chalkboard-user' : 'fa-graduation-cap' ?>"></i> <?= $roleLabel ?><?= !is_blank($u->InstitutionName) ? ' · ' . e($u->InstitutionName) : '' ?></li>
                                    <?php if ($location !== ''): ?><li><i class="fas fa-location-dot"></i> <?= e($location) ?></li>
                                    <?php elseif ($isOwner): ?><li class="muted"><i class="fas fa-location-dot"></i> শহর যোগ করুন</li><?php endif; ?>
                                    <li><i class="far fa-calendar"></i> Joined <?= e(fmt_date($u->CreatedAt, 'F Y', '—')) ?></li>
                                    <li><i class="fas fa-book-open"></i> Interested Subjects</li>
                                </ul>
                                <div class="am-subj">
                                    <?php foreach ($intro->Subjects as $sj): ?><span><?= e($sj) ?></span><?php endforeach; ?>
                                    <?php if (!$intro->Subjects): ?><span style="background:none;border:none;color:#94A3B8;padding-left:0;font-style:italic;"><?= $isOwner ? 'Edit চেপে বিষয় যোগ করুন' : 'যোগ করা হয়নি' ?></span><?php endif; ?>
                                </div>
                                <?php if ($intro->Quote !== ''): ?>
                                    <div class="am-quote"><?= e($intro->Quote) ?></div>
                                <?php elseif ($isOwner): ?>
                                    <div class="am-quote" style="font-style:italic;color:#94A3B8;">একটা প্রিয় উক্তি যোগ করুন — যেমন "Learning today for a brighter tomorrow."</div>
                                <?php endif; ?>
                            </div>

                            <?php if ($isOwner): ?>
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4><?= $peopleLabel ?></h4><?php if ($connected): ?><a href="?tab=tutors" data-tab="tutors">View all</a><?php endif; ?></div>
                                    <?php if (!$connected): ?>
                                        <p class="rc-empty"><?= $isTeacher
                                            ? 'স্টুডেন্টরা আপনার অ্যাপ্লিকেশন Accept করলে এখানে দেখাবে। <a href="/Tuition/Circular">Tuition Circular</a> দেখুন।'
                                            : 'টিচারের অ্যাপ্লিকেশন Accept করলে এখানে দেখাবে। <a href="/FindTutor">Tutor খুঁজুন</a> অথবা <a href="/Tuition/Circular">পোস্ট দিন</a>।' ?></p>
                                    <?php else: ?>
                                        <div class="tu-list">
                                            <?php foreach (array_slice($connected, 0, 3) as $c): ?>
                                                <div class="tu-item">
                                                    <a class="tu-av" href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Initials) ?><?php if ($c->Image): ?><img src="<?= e($c->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                                                    <div class="tu-who"><a href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Name) ?></a><small><?= e($c->Sub) ?></small></div>
                                                    <a class="tu-view" href="/Profile/Show/<?= $c->Id ?>">View</a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4>Friends</h4><?php if ($friends): ?><a href="?tab=friends" data-tab="friends">View all</a><?php endif; ?></div>
                                    <?php if (!$friends): ?>
                                        <p class="rc-empty">এখনো কোনো ফ্রেন্ড নেই।</p>
                                    <?php else: ?>
                                        <div class="tu-list">
                                            <?php foreach (array_slice($friends, 0, 3) as $f): ?>
                                                <div class="tu-item">
                                                    <a class="tu-av" href="/Profile/Show/<?= $f->Id ?>"><?= e($f->Initials) ?><?php if ($f->Image): ?><img src="<?= e($f->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                                                    <div class="tu-who"><a href="/Profile/Show/<?= $f->Id ?>"><?= e($f->FullName) ?></a><small><?= e($f->RoleLabel) ?></small></div>
                                                    <a class="tu-view" href="/Profile/Show/<?= $f->Id ?>">View</a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </aside>
                    </div>
                </section>

                <!-- ======================= PROFILE (About) ======================= -->
                <section class="pf-sec <?= $tab === 'profile' ? 'active' : '' ?>" id="sec-profile">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Profile' : 'Profile' ?></h2><p><?= $isOwner ? 'আপনার সব তথ্য — অন্যরা যা দেখতে পায়। ফোন, ইমেইল ও ঠিকানা শুধু ফ্রেন্ডরা দেখে।' : 'ফোন, ইমেইল ও ঠিকানা শুধু ফ্রেন্ডরা দেখতে পারেন।' ?></p></div>
                        <?php if ($isOwner): ?><a href="/Profile?section=basic" class="pf-btn"><i class="fas fa-pen"></i> Edit Profile</a><?php endif; ?></div>
                    <?php partial('Profile/_ShowAbout', ['u' => $u, 'isOwner' => $isOwner, 'isTeacher' => $isTeacher, 'ti' => $ti, 'canPrivate' => $canPrivate, 'Model' => $Model]); ?>
                </section>

                <?php if ($isOwner): ?>
                <!-- ======================= TUTORS / STUDENTS ======================= -->
                <section class="pf-sec <?= $tab === 'tutors' ? 'active' : '' ?>" id="sec-tutors">
                    <div class="pf-sec-h"><div><h2><?= $peopleLabel ?></h2><p><?= $isTeacher ? 'যেসব স্টুডেন্ট আপনার অ্যাপ্লিকেশন Accept করেছেন।' : 'যেসব টিচারের অ্যাপ্লিকেশন আপনি Accept করেছেন।' ?></p></div>
                        <a href="<?= $isTeacher ? '/Teacher/TeacherDashboard?section=applications' : '/User/Dashboard?section=applications' ?>" class="pf-btn"><i class="fas fa-list-check"></i> Applications</a></div>
                    <?php if (!$connected): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-chalkboard-user"></i></div>
                            <h5><?= $isTeacher ? 'এখনো কোনো স্টুডেন্ট নেই' : 'এখনো কোনো টিউটর নেই' ?></h5>
                            <p><?= $isTeacher ? 'Tuition Circular এ স্টুডেন্টদের পোস্টে Apply করুন — Accept হলে এখানে দেখাবে।' : 'Tuition Circular এ পোস্ট দিন, টিচাররা Apply করবেন। পছন্দের টিচারকে Accept করলে এখানে দেখাবে।' ?></p>
                            <a href="<?= $isTeacher ? '/Tuition/Circular' : '/FindTutor' ?>" class="pf-btn primary"><i class="fas fa-magnifying-glass"></i> <?= $isTeacher ? 'Tuition খুঁজুন' : 'Tutor খুঁজুন' ?></a></div>
                    <?php else: ?>
                        <div class="pf-card rc"><div class="tu-list">
                            <?php foreach ($connected as $c): ?>
                                <div class="tu-item">
                                    <a class="tu-av" href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Initials) ?><?php if ($c->Image): ?><img src="<?= e($c->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                                    <div class="tu-who"><a href="/Profile/Show/<?= $c->Id ?>"><?= e($c->Name) ?></a><small><?= e($c->Sub) ?><?= $c->Institution !== '' ? ' · ' . e($c->Institution) : '' ?></small></div>
                                    <a class="tu-view" href="/Profile/Show/<?= $c->Id ?>">View</a>
                                </div>
                            <?php endforeach; ?>
                        </div></div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- ======================= FRIENDS ======================= -->
                <section class="pf-sec <?= $tab === 'friends' ? 'active' : '' ?>" id="sec-friends">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Friends' : e($firstName) . "'s Friends" ?></h2><p><?= (int)$Model->FriendCount ?> friend<?= $Model->FriendCount === 1 ? '' : 's' ?></p></div>
                        <?php if ($isOwner): ?><a href="/Friends?tab=find" class="pf-btn"><i class="fas fa-user-plus"></i> Find People</a><?php endif; ?></div>
                    <?php if ($friends): ?>
                        <div class="pf-card fr-wrap-h">
                            <label style="position:relative;display:block;flex:1;max-width:420px;margin:0;">
                                <i class="fas fa-magnifying-glass" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94A3B8;"></i>
                                <input type="search" id="psFriendFilter" placeholder="ফ্রেন্ডদের মধ্যে খুঁজুন..." autocomplete="off"
                                       style="width:100%;height:42px;border:1.5px solid #E8EEF5;border-radius:10px;padding:0 14px 0 40px;font-size:14.5px;outline:none;" />
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="fr-grid" id="psFriendsGrid">
                        <?php if (!$friends): ?>
                            <?php partial('Friends/_Empty', ['icon' => 'fa-user-group', 'title' => 'কোনো ফ্রেন্ড নেই',
                                'text' => $isOwner ? 'স্টুডেন্ট বা টিচার খুঁজে ফ্রেন্ড রিকোয়েস্ট পাঠান।' : $firstName . ' এর এখনো কোনো ফ্রেন্ড নেই।',
                                'btnText' => $isOwner ? 'Find People' : null, 'btnHref' => '/Friends?tab=find']); ?>
                        <?php else: ?>
                            <?php foreach ($friends as $f) partial('Friends/_PersonCard', ['p' => $f]); ?>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if ($isOwner && !$isTeacher): ?>
                <!-- ======================= MY CIRCULARS ======================= -->
                <section class="pf-sec <?= $tab === 'circulars' ? 'active' : '' ?>" id="sec-circulars">
                    <div class="pf-sec-h"><div><h2>My Circulars</h2><p>Tuition Circular এ আপনার দেওয়া পোস্ট আর কতজন টিচার Apply করেছেন।</p></div>
                        <a href="/Tuition/Circular" class="pf-btn primary"><i class="fas fa-plus"></i> নতুন পোস্ট</a></div>
                    <?php if (!$Model->MyCirculars): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-file-lines"></i></div><h5>এখনো কোনো সার্কুলার পোস্ট নেই</h5>
                            <p>কোন বিষয়ে, কোন এলাকায় টিউটর লাগবে লিখে পোস্ট দিন — টিচাররা Apply করবেন।</p>
                            <a href="/Tuition/Circular" class="pf-btn primary"><i class="fas fa-plus"></i> পোস্ট দিন</a></div>
                    <?php else: ?>
                        <?php foreach ($Model->MyCirculars as $c): ?>
                            <div class="pf-card ci-item">
                                <p class="ci-text"><?= e(str_preview($c->Content, 280)) ?></p>
                                <div class="ci-tags">
                                    <?php foreach (array_filter([['fa-location-dot', $c->Area], ['fa-graduation-cap', $c->Class], ['fa-book', $c->Subjects], ['fa-money-bill-wave', is_blank($c->Budget) ? null : '৳' . $c->Budget]], fn($x) => !is_blank($x[1])) as [$ic, $v]): ?>
                                        <span><i class="fas <?= $ic ?>"></i> <?= e($v) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="ci-foot">
                                    <span><i class="far fa-clock"></i> <?= e(time_ago($c->CreatedAt)) ?></span>
                                    <span><i class="fas fa-chalkboard-user"></i> <?= $c->Apps ?> জন Apply করেছেন</span>
                                    <?php if ($c->PendingApps): ?><span class="ci-pend"><?= $c->PendingApps ?> নতুন</span><?php endif; ?>
                                    <a href="/User/Dashboard?section=applications" class="tu-view">Applications দেখুন</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <?php if ($isOwner): ?>
                <!-- ======================= SAVED ======================= -->
                <section class="pf-sec <?= $tab === 'saved' ? 'active' : '' ?>" id="sec-saved">
                    <div class="pf-sec-h"><div><h2>Saved Items</h2><p>পরে দেখার জন্য যেগুলো Save করেছেন — পোস্ট, টিউটর, সার্কুলার, টিউশন, নোটিশ।</p></div></div>
                    <?php if (!$saved): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="far fa-bookmark"></i></div><h5>এখনো কিছু Save করেননি</h5>
                            <p>পোস্ট, টিউটরের প্রোফাইল, Tuition Circular, টিউশন বা নোটিশে <b>Save</b> বাটন চাপলে এখানে জমা থাকবে।</p></div>
                    <?php else:
                        $typeCounts = array_count_values(array_map(fn($s) => $s->Type, $saved)); ?>
                        <div class="sv-filters" id="svFilters">
                            <button type="button" class="sv-filter active" data-f="all">All (<?= count($saved) ?>)</button>
                            <?php foreach ($typeLabel as $k => [$lb]): if (empty($typeCounts[$k])) continue; ?>
                                <button type="button" class="sv-filter" data-f="<?= $k ?>"><?= $lb ?>s (<?= $typeCounts[$k] ?>)</button>
                            <?php endforeach; ?>
                        </div>
                        <div class="sv-list" id="svList">
                            <?php foreach ($saved as $s): [$tl, $ti2] = $typeLabel[$s->Type] ?? ['Item', 'fa-bookmark']; ?>
                                <div class="pf-card sv-item<?= $s->Available ? '' : ' unavail' ?>" data-type="<?= e($s->Type) ?>" data-key="<?= e($s->Type . ':' . $s->ItemId) ?>">
                                    <a class="sv-thumb" href="<?= e($s->Link) ?>"><?php if ($s->Available && $s->Image): ?><img src="<?= e($s->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php else: ?><i class="fas <?= $ti2 ?>"></i><?php endif; ?></a>
                                    <div class="sv-body">
                                        <span class="sv-type"><?= $tl ?></span>
                                        <a class="sv-title" href="<?= e($s->Link) ?>"><?= $s->Available ? e($s->Title) : 'This item is no longer available' ?></a>
                                        <small><?= $s->Available ? e($s->Sub) : 'মুছে ফেলা হয়েছে বা দেখার অনুমতি নেই' ?> · Saved <?= e(time_ago($s->SavedAt)) ?></small>
                                    </div>
                                    <button type="button" class="sv-un" data-save-type="<?= e($s->Type) ?>" data-save-id="<?= $s->ItemId ?>"><i class="fas fa-bookmark"></i> <span>Unsave</span></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- ======================= GOALS ======================= -->
                <section class="pf-sec <?= $tab === 'goals' ? 'active' : '' ?>" id="sec-goals">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Goals' : 'Goals' ?></h2><p><?= $isOwner ? 'HSC, SSC, Admission, IELTS — লক্ষ্য ঠিক করুন, কাজে ভাগ করুন, টিক দিয়ে এগিয়ে যান।' : e($firstName) . ' এর পড়াশোনার লক্ষ্য।' ?></p></div>
                        <?php if ($isOwner): ?><button type="button" class="pf-btn primary" data-goal="new"><i class="fas fa-plus"></i> Add goal</button><?php endif; ?></div>
                    <div class="gl-grid" id="glGrid">
                        <?php foreach ($goals as $g) partial('Profile/_GoalCard', ['g' => $g, 'isOwner' => $isOwner]); ?>
                    </div>
                    <div class="pf-card pf-empty" id="glEmpty" <?= $goals ? 'hidden' : '' ?>><div class="ei"><i class="fas fa-bullseye"></i></div>
                        <h5><?= $isOwner ? 'প্রথম লক্ষ্যটা ঠিক করুন' : 'এখনো কোনো গোল নেই' ?></h5>
                        <p><?= $isOwner ? 'যেমন "HSC 2027" — তারপর ছোট ছোট কাজ যোগ করুন। কাজ টিক দিলে প্রগ্রেস নিজে থেকে বাড়বে।' : '' ?></p>
                        <?php if ($isOwner): ?><button type="button" class="pf-btn primary" data-goal="new"><i class="fas fa-plus"></i> Add goal</button><?php endif; ?></div>
                </section>

                <!-- ======================= ACTIVITIES ======================= -->
                <section class="pf-sec <?= $tab === 'activities' ? 'active' : '' ?>" id="sec-activities">
                    <?php $as = $Model->ActivityStats; $ad = $Model->ActivityDays; ?>
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Activities' : 'Activities' ?></h2><p>প্রতিদিন একটু একটু — streak ধরে রাখুন! 🔥</p></div></div>
                    <div class="act-top">
                        <div class="pf-card act-big"><span class="st-ico st-fire"><i class="fas fa-fire"></i></span><div><b><?= (int)$streak['current'] ?> <small>days</small></b><small>Current streak<?= $isOwner && !$streak['activeToday'] ? ' — আজ এখনো অ্যাক্টিভ হননি' : '' ?></small></div></div>
                        <div class="pf-card act-big"><span class="st-ico st-post"><i class="fas fa-trophy"></i></span><div><b><?= (int)$streak['best'] ?> <small>days</small></b><small>Best streak</small></div></div>
                        <div class="pf-card act-big"><span class="st-ico st-goal"><i class="fas fa-calendar-check"></i></span><div><b><?= (int)$streak['totalDays'] ?></b><small>Active days (total)</small></div></div>
                    </div>
                    <div class="pf-card hm">
                        <h4>Last 17 weeks <small><?= count($ad) ?> active days</small></h4>
                        <div class="hm-scroll"><div class="hm-grid" aria-label="Activity heatmap">
                            <?php
                            $start = new DateTime('today'); $start->modify('-' . (16 * 7 + (int)$start->format('w')) . ' days');
                            $today = date('Y-m-d');
                            for ($i = 0; $i < 17 * 7; $i++) {
                                $d = $start->format('Y-m-d'); $pts = $ad[$d] ?? 0;
                                $lv = $pts <= 0 ? '' : ($pts <= 2 ? 'l1' : ($pts <= 5 ? 'l2' : ($pts <= 10 ? 'l3' : 'l4')));
                                echo '<i class="' . ($d > $today ? 'fut' : $lv) . '" title="' . e($start->format('d M') . ($pts ? " · $pts points" : '')) . '"></i>';
                                $start->modify('+1 day');
                            } ?>
                        </div></div>
                        <div class="hm-legend">Less <i style="background:#EEF2F7"></i><i style="background:#CDE2FF"></i><i style="background:#8DBBFB"></i><i style="background:#4A8DF2"></i><i style="background:#1D63E0"></i> More</div>
                    </div>
                    <div class="act-tiles">
                        <?php foreach ([['fa-file-lines', 'st-tut', $as['study'], 'Study updates'], ['fa-clock', 'st-goal', $fmtMin((int)$as['minutes']), 'Study time'],
                                        ['fa-circle-question', 'st-save', $as['questions'], 'Questions asked'], ['fa-trophy', 'st-post', $as['achievements'], 'Achievements'],
                                        ['fa-pen-to-square', 'st-friend', $as['posts'], 'Total posts'], ['fa-comment', 'st-fire', $as['comments'], 'Comments']] as [$ic, $cls, $v, $lb]): ?>
                            <div class="pf-card act-tile"><span class="st-ico <?= $cls ?>"><i class="fas <?= $ic ?>"></i></span><div><b><?= e((string)$v) ?></b><small><?= $lb ?></small></div></div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php if ($isOwner): ?>
                <!-- ======================= SETTINGS ======================= -->
                <section class="pf-sec <?= $tab === 'settings' ? 'active' : '' ?>" id="sec-settings">
                    <div class="pf-sec-h"><div><h2>Settings</h2><p>প্রোফাইল, পাসওয়ার্ড আর অ্যাকাউন্ট।</p></div></div>
                    <div class="set-grid">
                        <div class="pf-card set-card">
                            <h4><i class="fas fa-user-pen"></i> Profile</h4><p>অন্যরা আপনার প্রোফাইলে যা দেখে।</p>
                            <div class="set-links">
                                <button type="button" data-intro="all"><i class="fas fa-quote-left"></i> Bio, শহর, বিষয় ও উক্তি <i class="fas fa-chevron-right chev"></i></button>
                                <a href="/Profile?section=basic"><i class="fas fa-id-card"></i> নাম, ইমেইল, মোবাইল <i class="fas fa-chevron-right chev"></i></a>
                                <a href="/Profile?section=education"><i class="fas fa-graduation-cap"></i> Education <i class="fas fa-chevron-right chev"></i></a>
                                <a href="/Profile?section=address"><i class="fas fa-location-dot"></i> Address <i class="fas fa-chevron-right chev"></i></a>
                                <a href="/Profile?section=links"><i class="fas fa-link"></i> Important Links <i class="fas fa-chevron-right chev"></i></a>
                                <?php if (!$isTeacher): ?><a href="/Profile?section=become-tutor"><i class="fas fa-chalkboard-user"></i> Become a Tutor <i class="fas fa-chevron-right chev"></i></a><?php endif; ?>
                                <a href="/Friends"><i class="fas fa-user-group"></i> Friends & Requests <i class="fas fa-chevron-right chev"></i></a>
                            </div>
                        </div>
                        <div class="pf-card set-card" id="ab-password">
                            <h4><i class="fas fa-key"></i> Change Password</h4><p>বর্তমান পাসওয়ার্ড দিয়ে নতুন পাসওয়ার্ড সেট করুন।</p>
                            <form class="pw-form" id="pwForm" autocomplete="off">
                                <div class="pw-field"><label for="pwCur">বর্তমান পাসওয়ার্ড</label><input type="password" id="pwCur" autocomplete="current-password" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-field"><label for="pwNew">নতুন পাসওয়ার্ড (অন্তত ৬ অক্ষর)</label><input type="password" id="pwNew" autocomplete="new-password" minlength="6" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-field"><label for="pwConf">নতুন পাসওয়ার্ড আবার লিখুন</label><input type="password" id="pwConf" autocomplete="new-password" minlength="6" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-msg" id="pwMsg"></div>
                                <div><button type="submit" class="ps-btn primary" id="pwBtn"><i class="fas fa-check"></i> পাসওয়ার্ড বদলান</button>
                                    <a href="/Account/ForgotPassword" style="margin-left:12px;font-size:13.5px;">বর্তমান পাসওয়ার্ড মনে নেই?</a></div>
                            </form>
                        </div>
                    </div>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($isOwner): ?>
<!-- ===== Intro (Bio / শহর / বিষয় / উক্তি) ===== -->
<div class="pf-ov" id="introOv" aria-hidden="true">
    <form class="pf-modal" id="introForm" autocomplete="off">
        <div class="pf-modal-h"><h3>Edit intro</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <div class="pf-f"><label for="inBio">Bio <small>— নামের নিচে দেখাবে</small></label>
                <textarea id="inBio" maxlength="160" rows="2" placeholder="যেমন: শিখছি, এগিয়ে যাচ্ছি, আরও ভালো একটা আগামির জন্য।"><?= e($intro->Bio) ?></textarea><div class="cnt"><span id="inBioN"><?= mb_strlen($intro->Bio) ?></span>/160</div></div>
            <div class="pf-f"><label for="inCity">শহর / জেলা</label><input type="text" id="inCity" maxlength="80" value="<?= e($intro->City) ?>" placeholder="যেমন: Chattogram" list="inCityList" />
                <datalist id="inCityList"><?php foreach (['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Gazipur', 'Narayanganj', 'Bogura', "Cox's Bazar"] as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist></div>
            <div class="pf-f"><label>Interested Subjects <small>— Enter বা কমা দিয়ে আলাদা করুন</small></label>
                <div class="sj-box" id="sjBox"><input type="text" id="sjIn" maxlength="30" placeholder="Mathematics, Physics..." list="pmSubjList" /></div></div>
            <div class="pf-f"><label for="inQuote">প্রিয় উক্তি</label><input type="text" id="inQuote" maxlength="160" value="<?= e($intro->Quote) ?>" placeholder="Learning today for a brighter tomorrow." /></div>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary" id="introSave">Save</button></div>
    </form>
</div>

<!-- ===== Goal add / edit ===== -->
<div class="pf-ov" id="goalOv" aria-hidden="true">
    <form class="pf-modal" id="goalForm" autocomplete="off">
        <div class="pf-modal-h"><h3 id="goalTitleH">Add goal</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <input type="hidden" id="glId" />
            <div class="pf-f" id="glQuickWrap"><label>দ্রুত বাছাই</label><div class="pf-quick">
                <?php $y = (int)date('Y'); foreach ([["HSC $y", 'HSC'], ['HSC ' . ($y + 1), 'HSC'], ["SSC $y", 'SSC'], ['SSC ' . ($y + 1), 'SSC'], ['University Admission', 'Admission'], ['Medical Admission', 'Admission'], ['IELTS 7.0', 'IELTS'], ['Semester Final', 'University'], ['Math এ A+', 'Subject']] as [$qt, $qc]): ?>
                    <button type="button" data-q="<?= e($qt) ?>" data-c="<?= $qc ?>"><?= e($qt) ?></button>
                <?php endforeach; ?></div></div>
            <div class="pf-f"><label for="glTitle">গোলের নাম <b style="color:#e74c3c">*</b></label><input type="text" id="glTitle" maxlength="120" placeholder="যেমন: HSC 2027" required /></div>
            <div class="pf-2">
                <div class="pf-f"><label for="glCat">ধরন</label><select id="glCat"><?php foreach (Db::GOAL_CATEGORIES as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?></select></div>
                <div class="pf-f"><label for="glDate">টার্গেট তারিখ <small>(ঐচ্ছিক)</small></label><input type="date" id="glDate" /></div>
            </div>
            <div class="pf-f" id="glProgWrap"><label>প্রগ্রেস <small>— কাজ যোগ করলে নিজে থেকে হিসাব হবে</small></label>
                <div class="pf-range"><input type="range" id="glProg" min="0" max="100" step="5" value="0" /><b id="glProgV">0%</b></div></div>
            <label class="pf-check"><input type="checkbox" id="glPrimary" /> এটাই আমার Main goal (প্রোফাইলের উপরে দেখাবে)</label>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary" id="glSave">Save goal</button></div>
    </form>
</div>
<?php endif; ?>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var OWNER = <?= (int)$u->Id ?>;
    var IS_OWNER = <?= js($isOwner) ?>;
    window.PC_TIMELINE_OWNER = OWNER;
    function $(s, r) { return (r || document).querySelector(s); }
    function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
    function tokenVal() { var t = $('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    // সার্ভার থেকে নতুন করে পেজ আনা (ক্যাশ থেকে পুরোনো কপি না দেখায়)
    function hardReload() { var u = new URL(location.href); u.searchParams.set('_r', Date.now().toString(36)); location.replace(u.pathname + u.search + location.hash); }
    function say(m, err) { if (window.pcToast) pcToast(m, err); else alert(m); }
    async function postForm(url, data) {
        var fd = new FormData();
        Object.keys(data).forEach(function (k) { if (data[k] !== undefined && data[k] !== null) fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', tokenVal());
        return (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
    }

    // ===== সেকশন বদলানো =====
    var navBar = $('.pf-nav');
    function showTab(key, scroll) {
        if (!document.getElementById('sec-' + key)) return;
        $$('.pf-sec').forEach(function (s) { s.classList.toggle('active', s.id === 'sec-' + key); });
        $$('.pf-nav a').forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-tab') === key); });
        var u = new URL(location.href); u.searchParams.set('tab', key); u.searchParams.delete('_r'); u.hash = '';
        history.replaceState(null, '', u.pathname + u.search);
        var act = $('.pf-nav a.active');
        if (act && navBar.scrollWidth > navBar.clientWidth) navBar.scrollLeft = act.offsetLeft - (navBar.clientWidth - act.offsetWidth) / 2;
        if (scroll) {
            var top = $('.pf-body').getBoundingClientRect().top + window.scrollY - 90;
            if (window.scrollY > top) window.scrollTo({ top: top, behavior: 'smooth' });
        }
    }
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[data-tab]');
        if (!a) return;
        e.preventDefault(); showTab(a.getAttribute('data-tab'), true);
    });
    showTab(<?= js($tab) ?>, false);
    if (location.hash === '#ab-password') showTab('settings');

    // ===== নতুন পোস্ট =====
    var feed = $('#psFeed'), empty = $('#psEmpty'), countEl = $('#psPostCount');
    function bumpCount(d) {
        var n = Math.max(0, (parseInt(countEl.textContent, 10) || 0) + d);
        countEl.textContent = n + ' post' + (n === 1 ? '' : 's');
        empty.hidden = !!feed.querySelector('.pc');
    }
    document.addEventListener('pc:created', function (e) {
        showTab('overview');
        feed.prepend(e.detail.el); bumpCount(1);
        e.detail.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    document.addEventListener('pc:deleted', function () { bumpCount(-1); });
    document.addEventListener('click', function (e) {
        var c = e.target.closest('[data-compose]'); if (!c) return;
        var t = c.getAttribute('data-compose');
        if (t === 'photo') { window.pcOpenComposer(null, false, 'post'); setTimeout(function () { $('#pmFile').click(); }, 60); }
        else window.pcOpenComposer(null, false, t);
    });

    var more = $('#psMore');
    if (more) more.addEventListener('click', async function () {
        var last = feed.querySelector('.pc:last-child');
        more.disabled = true; more.innerHTML = '<i class="fas fa-spinner fa-spin"></i> লোড হচ্ছে...';
        try {
            var res = await fetch('/Post/Feed?userId=' + OWNER + '&before=' + (last ? last.getAttribute('data-id') : 0));
            var t = document.createElement('template'); t.innerHTML = await res.text();
            window.pcInitCards(t.content); feed.appendChild(t.content);
            if (res.headers.get('X-Has-More') !== '1') more.remove();
            else { more.disabled = false; more.innerHTML = '<i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন'; }
        } catch (e) { more.disabled = false; more.innerHTML = 'আবার চেষ্টা করুন'; }
    });

    var ff = $('#psFriendFilter');
    if (ff) ff.addEventListener('input', function () {
        var q = ff.value.trim().toLowerCase();
        $$('#psFriendsGrid .fr-card').forEach(function (c) { c.style.display = !q || (c.getAttribute('data-name') || '').indexOf(q) !== -1 ? '' : 'none'; });
    });
    document.addEventListener('fr:relation', function (e) {
        if (String(e.detail.uid) !== String(OWNER)) return;
        var was = <?= js($rel === 'friends') ?>;
        if ((e.detail.rel === 'friends') !== was) setTimeout(function () { hardReload(); }, 900);
    });

    // ===== Saved সেকশন: ফিল্টার + আনসেভ করলে সরে যায় =====
    var svF = $('#svFilters');
    if (svF) svF.addEventListener('click', function (e) {
        var b = e.target.closest('.sv-filter'); if (!b) return;
        $$('.sv-filter', svF).forEach(function (x) { x.classList.toggle('active', x === b); });
        var f = b.getAttribute('data-f');
        $$('#svList .sv-item').forEach(function (it) { it.style.display = f === 'all' || it.getAttribute('data-type') === f ? '' : 'none'; });
    });
    document.addEventListener('saved:changed', function (e) {
        if (e.detail.saved) return;
        var it = $('#svList .sv-item[data-key="' + e.detail.type + ':' + e.detail.id + '"]');
        if (it) { it.classList.add('gone'); setTimeout(function () { it.remove(); }, 300); }
    });

    // ===== মেনু / লাইটবক্স =====
    function lightbox(src) { var lb = $('#pcLightbox'); if (!lb || !src) return; $('img', lb).src = src; lb.classList.add('open'); }
    function closeMenus(except) { $$('.av-menu.open, .cv-menu.open, .gl-menu.open').forEach(function (m) { if (m !== except) m.classList.remove('open'); }); }
    document.addEventListener('click', function (e) { if (!e.target.closest('.av-menu, .cv-menu, .gl-menu, #psAvatar, #avEditBtn, [data-cv="menu"], [data-gl="menu"]')) closeMenus(); });

    // ===== প্রোফাইল ছবি =====
    var av = $('#psAvatar'), avMenu = $('#avMenu'), avFile = $('#avFile');
    function avImg() { return $('img', av); }
    av.addEventListener('click', function () {
        if (IS_OWNER) { closeMenus(avMenu); avMenu.classList.toggle('open'); }
        else if (avImg()) lightbox(avImg().src);
    });
    av.addEventListener('keydown', function (e) { if (e.key === 'Enter') av.click(); });
    if (IS_OWNER) {
        $('#avEditBtn').addEventListener('click', function () { closeMenus(); avFile.click(); });
        avMenu.addEventListener('click', function (e) {
            var b = e.target.closest('[data-av]'); if (!b) return;
            avMenu.classList.remove('open');
            if (b.getAttribute('data-av') === 'view') lightbox(avImg() && avImg().src); else avFile.click();
        });
        avFile.addEventListener('change', async function () {
            var f = avFile.files[0]; avFile.value = '';
            if (!f) return;
            if (!/^image\/(jpeg|png|gif|webp)$/.test(f.type)) { say('শুধু JPG, PNG, GIF বা WEBP ছবি দেওয়া যাবে।', true); return; }
            if (f.size > 5 * 1024 * 1024) { say('ছবি ৫ MB এর কম হতে হবে।', true); return; }
            var fd = new FormData(); fd.append('profileImage', f); fd.append('__RequestVerificationToken', tokenVal());
            av.classList.add('uploading');
            try {
                var d = await (await fetch('/Profile/UploadProfileImage', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                if (!d.success) { say(d.message || 'Upload failed', true); return; }
                var url = d.imageUrl + '?t=' + Date.now();
                [av].concat($$('.me-avatar'), $$('.pc[data-owner="1"] .pc-head .pc-avatar')).forEach(function (m) {
                    var i = $('img', m); if (!i) { i = document.createElement('img'); i.alt = ''; m.appendChild(i); } i.src = url;
                });
                $('[data-av="view"]', avMenu).hidden = false;
                say('প্রোফাইল ছবি আপডেট হয়েছে।');
            } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
            finally { av.classList.remove('uploading'); }
        });
    }

    // ===== কভার ফটো =====
    var cover = $('#psCover'), cImg = $('#psCoverImg');
    cImg.addEventListener('click', function () { if (!cover.classList.contains('repo') && !cImg.hidden) lightbox(cImg.src); });
    if (IS_OWNER) {
        var cvMenu = $('#cvMenu'), cvFile = $('#cvFile');
        var saved = { src: cImg.hidden ? '' : cImg.getAttribute('src'), y: <?= (int)$coverY ?> };
        var pending = null, posY = saved.y, drag = null;
        function setPos(y) { posY = Math.max(0, Math.min(100, y)); cImg.style.objectPosition = '50% ' + posY + '%'; }
        function enterRepo() { cover.classList.add('repo'); closeMenus(); }
        function exitRepo() { cover.classList.remove('repo', 'dragging'); pending = null; }
        function paintMenu() {
            var has = !!saved.src;
            cover.classList.toggle('has-img', has || !cImg.hidden);
            $('[data-cv="reposition"]', cvMenu).hidden = !has;
            $('[data-cv="remove"]', cvMenu).hidden = !has;
            $('[data-cv="menu"] span', cover).textContent = has ? 'Edit Cover' : 'Add Cover';
        }
        cover.addEventListener('click', async function (e) {
            var b = e.target.closest('[data-cv]'); if (!b) return;
            var act = b.getAttribute('data-cv');
            if (act === 'menu') { closeMenus(cvMenu); cvMenu.classList.toggle('open'); return; }
            cvMenu.classList.remove('open');
            if (act === 'upload') cvFile.click();
            else if (act === 'reposition') { setPos(saved.y); enterRepo(); }
            else if (act === 'cancel') {
                exitRepo();
                if (saved.src) { cImg.src = saved.src; cImg.hidden = false; } else { cImg.hidden = true; cImg.removeAttribute('src'); }
                setPos(saved.y); paintMenu();
            } else if (act === 'save') {
                b.disabled = true; var old = b.innerHTML; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                var fd = new FormData(); fd.append('posY', Math.round(posY)); fd.append('__RequestVerificationToken', tokenVal());
                if (pending) fd.append('cover', pending);
                try {
                    var d = await (await fetch('/Profile/SaveCover', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                    if (!d.success) { say(d.message || 'Failed', true); return; }
                    saved = { src: d.url, y: d.posY }; cImg.src = d.url; exitRepo(); paintMenu(); say(d.message);
                } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
                finally { b.disabled = false; b.innerHTML = old; }
            } else if (act === 'remove') {
                if (!confirm('কভার ফটো সরিয়ে ফেলবেন?')) return;
                var d2 = await postForm('/Profile/RemoveCover', {});
                if (!d2.success) { say(d2.message, true); return; }
                saved = { src: '', y: 50 }; cImg.hidden = true; cImg.removeAttribute('src'); setPos(50); paintMenu(); say(d2.message);
            }
        });
        cvFile.addEventListener('change', function () {
            var f = cvFile.files[0]; cvFile.value = '';
            if (!f) return;
            if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { say('শুধু JPG, PNG বা WEBP ছবি দেওয়া যাবে।', true); return; }
            if (f.size > 8 * 1024 * 1024) { say('কভার ছবি ৮ MB এর কম হতে হবে।', true); return; }
            pending = f; cImg.src = URL.createObjectURL(f); cImg.hidden = false; cover.classList.add('has-img');
            setPos(50); enterRepo();
        });
        cImg.addEventListener('pointerdown', function (e) {
            if (!cover.classList.contains('repo')) return;
            e.preventDefault();
            var rect = cover.getBoundingClientRect();
            var shown = rect.width * (cImg.naturalHeight / Math.max(1, cImg.naturalWidth));
            drag = { y: e.clientY, start: posY, overflow: Math.max(1, shown - rect.height) };
            cover.classList.add('dragging'); cImg.setPointerCapture(e.pointerId);
        });
        cImg.addEventListener('pointermove', function (e) { if (drag) setPos(drag.start - (e.clientY - drag.y) / drag.overflow * 100); });
        function endDrag() { drag = null; cover.classList.remove('dragging'); }
        cImg.addEventListener('pointerup', endDrag); cImg.addEventListener('pointercancel', endDrag);
    }

    if (!IS_OWNER) return;

    // ===== মডাল (Intro / Goal) =====
    function openOv(ov) { ov.classList.add('open'); document.documentElement.style.overflow = 'hidden'; }
    function closeOv(ov) { ov.classList.remove('open'); document.documentElement.style.overflow = ''; }
    $$('.pf-ov').forEach(function (ov) {
        ov.addEventListener('click', function (e) { if (e.target === ov || e.target.closest('[data-close]')) closeOv(ov); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') $$('.pf-ov.open').forEach(closeOv); });

    // ----- Intro -----
    var introOv = $('#introOv'), sjBox = $('#sjBox'), sjIn = $('#sjIn');
    var subjects = <?= js($intro->Subjects) ?>;
    function paintSubj() {
        $$('span', sjBox).forEach(function (s) { s.remove(); });
        subjects.forEach(function (s, i) {
            var el = document.createElement('span');
            el.textContent = s;
            var x = document.createElement('button'); x.type = 'button'; x.innerHTML = '<i class="fas fa-xmark"></i>'; x.setAttribute('aria-label', 'Remove');
            x.addEventListener('click', function () { subjects.splice(i, 1); paintSubj(); });
            el.appendChild(x); sjBox.insertBefore(el, sjIn);
        });
    }
    function addSubj(v) {
        v = v.replace(/,/g, ' ').trim(); if (!v) return;
        if (subjects.length >= 10) { say('সর্বোচ্চ ১০টা বিষয়।', true); return; }
        if (!subjects.some(function (s) { return s.toLowerCase() === v.toLowerCase(); })) subjects.push(v.slice(0, 30));
        sjIn.value = ''; paintSubj();
    }
    sjBox.addEventListener('click', function () { sjIn.focus(); });
    sjIn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addSubj(sjIn.value); }
        else if (e.key === 'Backspace' && !sjIn.value && subjects.length) { subjects.pop(); paintSubj(); }
    });
    sjIn.addEventListener('change', function () { if (sjIn.value.trim()) addSubj(sjIn.value); });
    $('#inBio').addEventListener('input', function () { $('#inBioN').textContent = this.value.length; });
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-intro]'); if (!b) return;
        paintSubj(); openOv(introOv);
        setTimeout(function () { (b.getAttribute('data-intro') === 'bio' ? $('#inBio') : $('#inBio')).focus(); }, 40);
    });
    $('#introForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (sjIn.value.trim()) addSubj(sjIn.value);
        var btn = $('#introSave'); btn.disabled = true;
        try {
            var d = await postForm('/Profile/SaveIntro', { bio: $('#inBio').value, city: $('#inCity').value, subjects: subjects.join(','), quote: $('#inQuote').value });
            if (!d.success) { say(d.message, true); return; }
            say(d.message); closeOv(introOv);
            setTimeout(function () { hardReload(); }, 400);
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; }
    });

    // ----- Goals -----
    var goalOv = $('#goalOv'), grid = $('#glGrid');
    function paintPrimary(p) {
        var card = $('#stGoal .st-body');
        if (!p) { card.innerHTML = '<div class="st-t">My Goal</div><div class="st-empty">একটা লক্ষ্য ঠিক করুন — <b>Set goal +</b></div>'; return; }
        card.innerHTML = '<div class="st-t">My Goal</div><div class="st-goal-t" id="stGoalTitle"></div><div class="st-prog"><div class="bar"><span id="stGoalBar"></span></div><b id="stGoalPct"></b></div>';
        $('#stGoalTitle').textContent = p.title; $('#stGoalBar').style.width = p.progress + '%'; $('#stGoalPct').textContent = p.progress + '%';
    }
    function applyGoal(d, goalId) {
        if (d.html) {
            var t = document.createElement('template'); t.innerHTML = d.html.trim();
            var el = t.content.firstElementChild, old = document.getElementById('goal-' + goalId);
            if (old) old.replaceWith(el); else grid.prepend(el);
        }
        paintPrimary(d.primary);
        // main goal বদলালে অন্য কার্ডের ব্যাজ ঠিক করতে পুরো তালিকা রিফ্রেশ দরকার — সহজ রাখতে পেজের বাকিটা ঠিক রাখি
        $('#glEmpty').hidden = !!$('.gl-card', grid);
    }
    function openGoal(card) {
        $('#glId').value = card ? card.getAttribute('data-id') : '';
        $('#goalTitleH').textContent = card ? 'Edit goal' : 'Add goal';
        $('#glQuickWrap').hidden = !!card;
        $('#glTitle').value = card ? card.getAttribute('data-title') : '';
        $('#glCat').value = card ? card.getAttribute('data-category') : 'HSC';
        $('#glDate').value = card ? card.getAttribute('data-date') : '';
        $('#glProg').value = card ? card.getAttribute('data-progress') : 0; $('#glProgV').textContent = $('#glProg').value + '%';
        $('#glProgWrap').hidden = !!(card && card.getAttribute('data-tasks') === '1');
        $('#glPrimary').checked = card ? card.getAttribute('data-primary') === '1' : !$('.gl-card', grid);
        openOv(goalOv); setTimeout(function () { $('#glTitle').focus(); }, 40);
    }
    $('#glProg').addEventListener('input', function () { $('#glProgV').textContent = this.value + '%'; });
    $('#glQuickWrap').addEventListener('click', function (e) {
        var b = e.target.closest('button[data-q]'); if (!b) return;
        $('#glTitle').value = b.getAttribute('data-q'); $('#glCat').value = b.getAttribute('data-c');
    });
    $('#goalForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn = $('#glSave'); btn.disabled = true;
        var id = $('#glId').value;
        try {
            var d = await postForm('/Goal/Save', { goalId: id, title: $('#glTitle').value, category: $('#glCat').value, targetDate: $('#glDate').value,
                progress: $('#glProg').value, primary: $('#glPrimary').checked ? 'true' : 'false' });
            if (!d.success) { say(d.message, true); return; }
            closeOv(goalOv); say(d.message);
            // অন্য কার্ডে "Main goal" ব্যাজ ছিল, এখন এটা main হলো → পুরো তালিকা নতুন করে দেখাই
            var oldMain = $('.gl-card[data-primary="1"]', grid);
            if ($('#glPrimary').checked && oldMain && oldMain.getAttribute('data-id') !== String(id)) { setTimeout(function () { hardReload(); }, 400); return; }
            var t = document.createElement('template'); t.innerHTML = d.html.trim();
            applyGoal(d, t.content.firstElementChild.getAttribute('data-id'));
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; }
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-goal="new"]'); if (b) { openGoal(null); return; }
        var g = e.target.closest('[data-gl]'); if (!g || g.tagName === 'INPUT' || g.tagName === 'FORM') return;
        var card = g.closest('.gl-card'), act = g.getAttribute('data-gl'), id = card.getAttribute('data-id');
        if (act === 'menu') { var m = g.nextElementSibling; closeMenus(m); m.classList.toggle('open'); return; }
        closeMenus();
        if (act === 'edit') openGoal(card);
        else if (act === 'primary') {
            var d = await postForm('/Goal/Save', { goalId: id, title: card.getAttribute('data-title'), category: card.getAttribute('data-category'),
                targetDate: card.getAttribute('data-date'), progress: card.getAttribute('data-progress'), primary: 'true' });
            if (d.success) { say('Main goal বদলানো হয়েছে।'); setTimeout(function () { hardReload(); }, 300); } else say(d.message, true);
        } else if (act === 'delete') {
            if (!confirm('গোলটি আর এর সব কাজ মুছে ফেলবেন?')) return;
            var d2 = await postForm('/Goal/Delete', { goalId: id });
            if (!d2.success) { say(d2.message, true); return; }
            card.remove(); applyGoal(d2, null); say(d2.message);
        } else if (act === 'task-del') {
            var li = g.closest('li');
            var d3 = await postForm('/Goal/Task', { taskId: li.getAttribute('data-task'), delete: 'true' });
            if (d3.success) applyGoal(d3, id); else say(d3.message, true);
        }
    });
    document.addEventListener('change', async function (e) {
        var cb = e.target.closest('[data-gl="task"]'); if (!cb) return;
        var card = cb.closest('.gl-card'), li = cb.closest('li');
        li.classList.toggle('done', cb.checked);
        var d = await postForm('/Goal/Task', { taskId: li.getAttribute('data-task'), done: cb.checked ? 'true' : 'false' });
        if (d.success) { applyGoal(d, card.getAttribute('data-id')); if (cb.checked && d.html && d.html.indexOf('gl-ok') > -1) say('🎉 অভিনন্দন! গোল শেষ!'); }
        else { cb.checked = !cb.checked; li.classList.toggle('done', cb.checked); say(d.message, true); }
    });
    document.addEventListener('submit', async function (e) {
        var f = e.target.closest('[data-gl="add-task"]'); if (!f) return;
        e.preventDefault();
        var inp = $('input', f), v = inp.value.trim(); if (!v) return;
        var card = f.closest('.gl-card'); inp.disabled = true;
        try {
            var d = await postForm('/Goal/AddTask', { goalId: card.getAttribute('data-id'), title: v });
            if (!d.success) { say(d.message, true); return; }
            applyGoal(d, card.getAttribute('data-id'));
            var ni = $('#goal-' + card.getAttribute('data-id') + ' .gl-add input'); if (ni) ni.focus();
        } finally { inp.disabled = false; }
    });

    // ===== পাসওয়ার্ড =====
    var pw = $('#pwForm');
    if (pw) {
        $$('.pw-eye', pw).forEach(function (b) {
            b.addEventListener('click', function () {
                var i = b.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password';
                b.innerHTML = i.type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
            });
        });
        pw.addEventListener('submit', async function (e) {
            e.preventDefault();
            var msg = $('#pwMsg'), btn = $('#pwBtn');
            var cur = $('#pwCur').value, nw = $('#pwNew').value, cf = $('#pwConf').value;
            msg.className = 'pw-msg';
            if (nw.length < 6) { msg.className = 'pw-msg err'; msg.textContent = 'নতুন পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।'; return; }
            if (nw !== cf) { msg.className = 'pw-msg err'; msg.textContent = 'নতুন পাসওয়ার্ড দুইবার একই হয়নি।'; return; }
            btn.disabled = true;
            try {
                var d = await postForm('/Profile/ChangePassword', { currentPassword: cur, newPassword: nw, confirmPassword: cf });
                msg.className = 'pw-msg ' + (d.success ? 'ok' : 'err'); msg.textContent = d.message;
                if (d.success) pw.reset();
            } catch (err) { msg.className = 'pw-msg err'; msg.textContent = 'ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'; }
            btn.disabled = false;
        });
    }
})();
</script>
<?php section_end(); ?>
