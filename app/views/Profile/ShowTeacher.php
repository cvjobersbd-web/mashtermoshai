<?php
/**
 * টিচারের প্রোফাইল (শুধু টিচারদের জন্য)।
 * বাম: মেনু + প্রোমো · মাঝে: স্ট্যাট কার্ড + পোস্ট (Study Material / Class Update / Notice ...) · ডান: My Subjects, Upcoming Classes, Recent Students
 * সেকশন: overview | profile | subjects | students | schedule | tuitionposts | saved | earnings | reviews | certificates | friends | settings
 */
$u = $Model->User;
$rel = $Model->Relation;
$isOwner = $Model->IsOwner;
$isTeacher = true;
$name = !is_blank($u->FullName) ? $u->FullName : $u->UserName;
$firstName = explode(' ', trim($name))[0];
$ViewData['Title'] = $name;
$img = is_blank($u->ProfileImageLink) ? null : $u->ProfileImageLink;
$ti = $Model->TeacherInfo;
$canPrivate = $Model->CanSeePrivate;
$tab = $Model->Tab;
$friends = $Model->Friends;
$intro = $Model->Intro;
$tIntro = $Model->TIntro;
$prefs = db()->GetTeachPrefs((int)$u->Id);
$modeLabel = ['Online' => 'Online', 'Home' => 'Home Visit', 'Both' => 'Online, Home Visit'][$prefs->TeachMode] ?? '';
$feeText = $prefs->FeeMin ? '৳' . number_format($prefs->FeeMin) . ($prefs->FeeMax ? '-' . number_format($prefs->FeeMax) : '+') . ' / month' : '';
$saved = $Model->SavedItems;
$cover = $Model->Extras->CoverImageUrl;
$coverY = $Model->Extras->CoverPosY;
$location = implode(', ', array_filter([trim($intro->City), trim((string)$u->Country)]));
$isAdminViewer = $Model->IsAdminViewer;
$students = $Model->Students;
$rs = $Model->ReviewStats;
$reviews = $Model->Reviews;
$certs = $Model->Certificates;
$earn = $Model->Earnings;
// বিষয় না দিলে Become a Tutor ফর্মের Subject Expertise থেকে নেওয়া
if (!$tIntro->IntroSaved && !$intro->Subjects && !is_blank($ti->SubjectExpertise ?? null)) {
    $intro->Subjects = array_slice(array_values(array_unique(array_filter(array_map(fn($x) => mb_substr(trim($x), 0, 30), preg_split('#[,/|;]+#u', (string)$ti->SubjectExpertise)), fn($x) => $x !== ''))), 0, 12);
}
// হেডলাইন: টিচার নিজে লিখলে সেটা; নিজে মুছে দিলে কিছুই দেখাবে না; কখনো সেট না করলে বিষয় থেকে ("Math Tutor")
$headline = $tIntro->Headline !== '' ? $tIntro->Headline
    : ($tIntro->IntroSaved ? '' : (count($intro->Subjects) ? $intro->Subjects[0] . ' Tutor' : 'Tutor'));

$me = obj(['Id' => (int)current_user_id(), 'Name' => session_get('UserFullName') ?? session_get('UserName') ?? 'User', 'Image' => session_get('ProfileImageLink')]);

// বাম মেনু: [key, লেবেল, আইকন, শুধু-মালিক?]
$nav = $isOwner ? [
    ['overview', 'Dashboard', 'fa-house'], ['profile', 'My Profile', 'fa-user'], ['subjects', 'My Subjects', 'fa-book-open'],
    ['students', 'My Students', 'fa-user-graduate'], ['schedule', 'My Schedule', 'fa-calendar-days'], ['tuitionposts', 'My Tuition Posts', 'fa-file-lines'],
    ['saved', 'Saved Items', 'fa-bookmark'], ['earnings', 'My Earnings', 'fa-wallet'], ['reviews', 'My Reviews', 'fa-star'],
    ['certificates', 'My Certificates', 'fa-award'], ['friends', 'Friends', 'fa-user-group'], ['settings', 'Settings', 'fa-gear'],
] : [
    ['overview', 'Overview', 'fa-house'], ['profile', 'Profile', 'fa-user'], ['subjects', 'Subjects', 'fa-book-open'],
    ['reviews', 'Reviews', 'fa-star'], ['certificates', 'Certificates', 'fa-award'], ['friends', 'Friends', 'fa-user-group'],
];

$days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$typeLabel = ['post' => ['Post', 'fa-newspaper'], 'tutor' => ['Tutor', 'fa-chalkboard-user'], 'circular' => ['Circular', 'fa-bullhorn'],
              'tuition' => ['Tuition', 'fa-clipboard-list'], 'notice' => ['Notice', 'fa-bell']];
$money = fn($n) => '৳' . number_format((int)$n);
$t12 = fn($hm) => date('g:i A', strtotime('2000-01-01 ' . $hm));
$stars = function (float $r, string $cls = ''): string {
    $o = '<span class="tp-stars ' . $cls . '" aria-label="' . $r . ' out of 5">';
    for ($i = 1; $i <= 5; $i++) $o .= '<i class="' . ($r >= $i - .25 ? 'fas fa-star' : ($r >= $i - .75 ? 'fas fa-star-half-stroke' : 'far fa-star')) . '"></i>';
    return $o . '</span>';
};
$occLabel = function (Obj $o) use ($t12): string {
    $d = $o->IsToday ? 'Today' : ($o->Date === date('Y-m-d', strtotime('+1 day')) ? 'Tomorrow' : date('D, d M', $o->Start));
    return $d . ', ' . $t12($o->Class->StartTime) . ' - ' . $t12($o->Class->EndTime);
};
$subjColors = ['c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7'];
$waLink = function (string $phone): ?string {
    $d = preg_replace('/\D/', '', $phone);
    if ($d === '') return null;
    if (str_starts_with($d, '01') && strlen($d) === 11) $d = '88' . $d;
    return 'https://wa.me/' . $d;
};
$expLabel = $Model->ExpYears !== null ? $Model->ExpYears . '+' : '—';
?>
<?= csrf_field() ?>
<?php partial('Friends/_Assets'); ?>
<?php partial('Post/_Assets', ['me' => $me, 'composerTypes' => ['post', 'material', 'update', 'notice', 'achievement', 'poll']]); ?>
<?php partial('Profile/_ShowStyles'); ?>
<style>
    /* ===== Teacher profile ===== */
    .ps-online { position: absolute; right: 22px; bottom: 22px; z-index: 4; width: 22px; height: 22px; border-radius: 50%; background: #22C55E; border: 4px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,.15); }
    .ps-online.own { bottom: auto; top: 20px; }
    .tp-headline { margin-top: 6px; font-size: 17px; font-weight: 600; color: #334155; }
    .tp-hright { display: flex; flex-direction: column; align-items: flex-end; gap: 16px; padding-top: 22px; }
    .tp-hstats { display: flex; align-items: stretch; }
    .tp-hstats > div { padding: 0 22px; text-align: center; border-left: 1px solid var(--pf-line); min-width: 92px; }
    .tp-hstats > div:first-child { border-left: none; }
    .tp-hstats b { display: block; font-size: 24px; font-weight: 800; color: var(--pf-text); line-height: 1.15; }
    .tp-hstats b .fa-star { color: #F59E0B; font-size: 18px; margin-left: 4px; }
    .tp-hstats small { font-size: 13px; color: var(--pf-muted); }
    .tp-hright .pf-actions { padding-top: 0; }
    .tp-stars { color: #F59E0B; display: inline-flex; gap: 2px; font-size: 13px; }
    .tp-stars.lg { font-size: 20px; gap: 4px; }
    .st-earn { background: #E7F8EE; color: #16A34A; }
    .st-rate { background: #FFF6DB; color: #F59E0B; }
    .st-class { background: #FFF1E6; color: #F97316; }
    .st-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 13.5px; font-weight: 600; color: var(--pf-blue); }
    .st-up { color: #16A34A; font-size: 13px; font-weight: 600; margin-top: 8px; }
    .st-up.down { color: #E11D48; }
    .st-bars { position: absolute; right: 16px; bottom: 16px; display: flex; align-items: flex-end; gap: 4px; height: 34px; }
    .st-bars i { width: 7px; border-radius: 3px; background: #BBF7D0; }
    .st-bars i:last-child { background: #22C55E; }
    .st-v small { font-size: 14px; color: var(--pf-muted); }
    .tp-page .pf-chips { padding-left: 0; gap: 9px; flex-wrap: wrap; }
    .tp-page .pf-chip { padding: 0 14px; }
    .ch-mat { background: #EAF7EE; } .ch-mat i { color: #22A35A; }
    .ch-upd { background: #E8F3FF; } .ch-upd i { color: #1D63E0; }
    .ch-not { background: #FFF1E6; } .ch-not i { color: #F97316; }
    .tp-ftabs { display: flex; gap: 4px; border-bottom: 1px solid var(--pf-line); margin: 6px 0 14px; overflow-x: auto; scrollbar-width: none; }
    .tp-ftabs::-webkit-scrollbar { display: none; }
    .tp-ftabs button { flex: 0 0 auto; border: none; background: none; padding: 12px 14px; font-size: 15px; font-weight: 600; color: var(--pf-muted); cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -1px; }
    .tp-ftabs button:hover { color: var(--pf-text); }
    .tp-ftabs button.active { color: var(--pf-blue); border-bottom-color: var(--pf-blue); }
    .sj-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .sj-chips span { padding: 7px 14px; border-radius: 10px; font-size: 13.5px; font-weight: 600; }
    .sj-chips .c1 { background: #EAF1FE; color: #1D63E0; } .sj-chips .c2 { background: #F1EAFE; color: #7C3AED; } .sj-chips .c3 { background: #FFF1E6; color: #EA580C; }
    .sj-chips .c4 { background: #E7F8EE; color: #16A34A; } .sj-chips .c5 { background: #FDECEE; color: #E11D48; } .sj-chips .c6 { background: #FFF6DB; color: #B45309; }
    .sj-chips .c7 { background: #E0F7FA; color: #0E7490; }
    .sj-chips .lv { background: #F1F5F9; color: #334155; }
    .uc-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-top: 1px solid #F1F5F9; }
    .uc-item:first-child { border-top: none; padding-top: 0; }
    .uc-ico { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 18px; }
    .uc-ico.a { background: #EAF1FE; color: #1D63E0; } .uc-ico.b { background: #F1EAFE; color: #7C3AED; } .uc-ico.c { background: #E7F8EE; color: #16A34A; }
    .uc-who { flex: 1; min-width: 0; }
    .uc-who b { display: block; font-size: 14.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .uc-who small { color: var(--pf-muted); font-size: 12.5px; }
    .uc-btn { flex-shrink: 0; height: 34px; padding: 0 16px; border-radius: 9px; border: none; font-size: 13.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .uc-btn.join { background: #0F1B2D; color: #fff; } .uc-btn.join:hover { background: #24344d; color: #fff; }
    .uc-btn.start { background: #22C55E; color: #fff; } .uc-btn.start:hover { background: #16A34A; color: #fff; }
    .uc-btn.ghost { background: #F1F5F9; color: #334155; }
    .uc-live { display: inline-block; margin-left: 6px; padding: 1px 7px; border-radius: 8px; background: #DCFCE7; color: #15803D; font-size: 11px; font-weight: 800; }
    .tu-msg { height: 34px; padding: 0 14px; border-radius: 9px; border: 1px solid #C7D9FB; background: #fff; color: var(--pf-blue); font-size: 13.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .tu-msg:hover { background: var(--pf-blue-soft); color: var(--pf-blue); }
    .tp-list { padding: 6px 18px; }
    .tp-row { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-top: 1px solid #F1F5F9; }
    .tp-row:first-child { border-top: none; }
    .tp-row .tu-who small { display: block; }
    .tp-meta { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 13px; color: var(--pf-muted); margin-top: 3px; }
    .tp-badge { display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 11px; font-size: 11.5px; font-weight: 700; background: #DCFCE7; color: #15803D; }
    .tp-badge.off { background: #F1F5F9; color: #64748B; }
    .tp-badge.auto { background: #EAF1FE; color: #1D63E0; }
    .tp-menu-wrap { position: relative; flex-shrink: 0; }
    .tp-menu-btn { width: 34px; height: 34px; border-radius: 50%; border: none; background: none; color: var(--pf-muted); cursor: pointer; }
    .tp-menu-btn:hover { background: #F1F5F9; }
    .tp-menu { display: none; position: absolute; right: 0; top: 38px; z-index: 20; min-width: 160px; background: #fff; border: 1px solid var(--pf-line); border-radius: 12px; box-shadow: 0 12px 30px rgba(15,27,45,.14); padding: 6px; }
    .tp-menu.open { display: block; }
    .tp-menu button { display: flex; width: 100%; align-items: center; gap: 10px; padding: 9px 10px; border: none; background: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; color: var(--pf-text); text-align: left; }
    .tp-menu button:hover { background: #F4F7FB; }
    .tp-menu button.danger { color: #DC2626; }
    .tp-filter { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .tp-filter button { height: 34px; padding: 0 14px; border-radius: 17px; border: 1px solid var(--pf-line); background: #fff; font-size: 13.5px; font-weight: 600; cursor: pointer; color: #334155; }
    .tp-filter button.active { background: var(--pf-blue); border-color: var(--pf-blue); color: #fff; }
    .tp-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; }
    .tp-day { padding: 12px; min-height: 120px; }
    .tp-day h5 { margin: 0 0 10px; font-size: 13px; font-weight: 800; color: var(--pf-muted); text-transform: uppercase; letter-spacing: .4px; }
    .tp-day.today { border-color: #9EC2FA; background: #F7FAFF; }
    .tp-day.today h5 { color: var(--pf-blue); }
    .tp-cls { display: block; width: 100%; text-align: left; border: none; background: #EAF1FE; border-radius: 9px; padding: 8px 9px; margin-bottom: 7px; cursor: pointer; font-size: 12.5px; color: #0F1B2D; }
    .tp-cls b { display: block; font-size: 12.5px; line-height: 1.3; word-break: break-word; }
    .tp-cls small { color: #475569; }
    .tp-cls.once { background: #FFF6DB; }
    .tp-empty-day { color: #CBD5E1; font-size: 12px; }
    .tp-sum { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 16px; }
    .tp-sum .pf-card { padding: 18px; }
    .tp-sum small { display: block; color: var(--pf-muted); font-size: 13.5px; }
    .tp-sum b { display: block; font-size: 26px; font-weight: 800; margin-top: 4px; }
    .tp-chart { padding: 18px 20px; margin-bottom: 16px; }
    .tp-chart h4 { margin: 0 0 16px; font-size: 16px; font-weight: 700; }
    .tp-bars { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 14px; align-items: end; height: 170px; }
    .tp-bar { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 6px; }
    .tp-bar span { width: 100%; max-width: 46px; border-radius: 8px 8px 4px 4px; background: #BFDBFE; min-height: 4px; }
    .tp-bar.cur span { background: #1D63E0; }
    .tp-bar em { font-style: normal; font-size: 12px; color: var(--pf-muted); }
    .tp-bar b { font-size: 11.5px; color: #334155; }
    .rv-top { display: grid; grid-template-columns: 220px minmax(0, 1fr); gap: 24px; padding: 22px; margin-bottom: 16px; align-items: center; }
    .rv-avg { text-align: center; }
    .rv-avg b { display: block; font-size: 48px; font-weight: 800; line-height: 1; }
    .rv-avg small { display: block; color: var(--pf-muted); margin-top: 6px; }
    .rv-dist div { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #475569; margin: 5px 0; }
    .rv-dist .bar { flex: 1; height: 8px; border-radius: 4px; background: #F1F5F9; overflow: hidden; }
    .rv-dist .bar span { display: block; height: 100%; background: #F59E0B; border-radius: 4px; }
    .rv-dist em { font-style: normal; width: 26px; text-align: right; }
    .rv-item { padding: 16px 18px; margin-bottom: 12px; }
    .rv-head { display: flex; align-items: center; gap: 12px; }
    .rv-head .tu-av { width: 44px; height: 44px; font-size: 14px; }
    .rv-head .tu-who b { display: block; font-size: 15px; }
    .rv-head .tu-who small { color: var(--pf-muted); font-size: 12.5px; }
    .rv-text { margin: 10px 0 0; font-size: 14.5px; color: #334155; line-height: 1.6; white-space: pre-line; word-break: break-word; }
    .rv-form { padding: 18px 20px; margin-bottom: 16px; border-color: #C7D9FB; background: #F9FBFF; }
    .rv-form h4 { margin: 0 0 4px; font-size: 16.5px; font-weight: 700; }
    .rv-pick { display: flex; gap: 6px; margin: 12px 0; direction: rtl; justify-content: flex-end; }
    .rv-pick input { display: none; }
    .rv-pick label { font-size: 28px; color: #CBD5E1; cursor: pointer; transition: transform .1s; }
    .rv-pick label:hover, .rv-pick label:hover ~ label, .rv-pick input:checked ~ label { color: #F59E0B; }
    .rv-pick label:active { transform: scale(.9); }
    .rv-form textarea { width: 100%; border: 1.5px solid var(--pf-line); border-radius: 10px; padding: 10px 12px; font-size: 14.5px; min-height: 80px; resize: vertical; font-family: inherit; outline: none; }
    .rv-form textarea:focus { border-color: var(--pf-blue); }
    .ct-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .ct-card { overflow: hidden; display: flex; flex-direction: column; }
    .ct-thumb { height: 150px; background: linear-gradient(135deg, #F7FAFF, #EAF1FE); display: flex; align-items: center; justify-content: center; color: #1D63E0; font-size: 46px; text-decoration: none; overflow: hidden; }
    .ct-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .ct-thumb.pdf { color: #DC2626; background: linear-gradient(135deg, #FFF5F5, #FEE2E2); }
    .ct-body { padding: 14px 16px; display: flex; gap: 8px; align-items: flex-start; }
    .ct-body > div { flex: 1; min-width: 0; }
    .ct-body b { display: block; font-size: 15px; word-break: break-word; }
    .ct-body small { color: var(--pf-muted); font-size: 13px; }
    .tp-info { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 24px; padding: 20px 22px; }
    .tp-info div small { display: block; font-size: 12.5px; color: var(--pf-muted); font-weight: 600; }
    .tp-info div span { font-size: 15px; color: var(--pf-text); word-break: break-word; }
    .tp-tp { padding: 16px 18px; margin-bottom: 12px; }
    .tp-tp p { margin: 0; font-size: 14.5px; color: #334155; white-space: pre-line; word-break: break-word; }
    .tp-tp img { margin-top: 10px; max-height: 220px; border-radius: 10px; max-width: 100%; }
    .tp-tp .ci-foot { margin-top: 10px; }
    .pf-f input[type=time], .pf-f input[type=number], .pf-f input[type=month], .pf-f input[type=url], .pf-f input[type=file] { width: 100%; border: 1.5px solid var(--pf-line); border-radius: 10px; padding: 10px 12px; font-size: 15px; outline: none; background: #fff; font-family: inherit; }
    .dy-pick { display: flex; flex-wrap: wrap; gap: 6px; }
    .dy-pick label { margin: 0 !important; }
    .dy-pick input { display: none; }
    .dy-pick span { display: inline-flex; align-items: center; justify-content: center; min-width: 50px; height: 36px; border-radius: 10px; border: 1.5px solid var(--pf-line); font-size: 13.5px; font-weight: 700; color: #334155; cursor: pointer; }
    .dy-pick input:checked + span { background: var(--pf-blue); border-color: var(--pf-blue); color: #fff; }
    .pf-seg { display: inline-flex; border: 1.5px solid var(--pf-line); border-radius: 10px; overflow: hidden; }
    .pf-seg button { border: none; background: #fff; padding: 8px 14px; font-size: 13.5px; font-weight: 600; cursor: pointer; color: #475569; }
    .pf-seg button.active { background: var(--pf-blue); color: #fff; }
    @media (max-width: 1280px) {
        .tp-week { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .ct-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 992px) {
        .tp-hright { align-items: center; padding-top: 4px; width: 100%; }
        .tp-hstats > div { padding: 0 16px; min-width: 0; }
        .tp-hright .pf-actions { justify-content: center; }
        .tp-headline { text-align: center; }
        .ps-online { right: 16px; bottom: 16px; }
    }
    @media (max-width: 640px) {
        .tp-hstats b { font-size: 20px; }
        .tp-week { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .tp-sum { grid-template-columns: minmax(0, 1fr); gap: 10px; }
        .rv-top { grid-template-columns: minmax(0, 1fr); gap: 14px; }
        .ct-grid { grid-template-columns: minmax(0, 1fr); }
        .tp-info { grid-template-columns: minmax(0, 1fr); }
        .tp-bars { gap: 8px; }
        .st-bars { display: none; }
        .tp-list { padding: 4px 14px; }
    }
</style>

<div class="pf-page tp-page">
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
                        <button type="button" class="cv-btn light" data-cv="menu"><i class="fas fa-camera"></i> <span><?= $cover ? 'Edit Cover Photo' : 'Add Cover Photo' ?></span></button>
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
                        <?= e(initials($name, 'T')) ?>
                        <?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($name) ?>" onerror="this.remove()" /><?php endif; ?>
                    </div>
                    <?php if ($Model->IsOnline): ?><span class="ps-online<?= $isOwner ? ' own' : '' ?>" title="Online"></span><?php endif; ?>
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
                        <i class="fas fa-circle-check" title="Verified Teacher"></i>
                        <?php if ($Model->IsSuspended): ?><span class="pf-warn"><i class="fas fa-ban"></i> Suspended</span><?php endif; ?>
                    </h1>
                    <?php if ($headline !== ''): ?><div class="tp-headline" id="tpHeadline"><?= e($headline) ?></div><?php endif; ?>
                    <div class="pf-meta">
                        <?php if ($location !== ''): ?><span><i class="fas fa-location-dot"></i> <?= e($location) ?></span><?php endif; ?>
                        <span><i class="far fa-calendar"></i> Joined <?= e(fmt_date($u->CreatedAt, 'F Y', '—')) ?></span>
                    </div>
                    <?php if ($intro->Bio !== ''): ?>
                        <div class="pf-bio"><span id="pfBio">“<?= e($intro->Bio) ?>”</span><?php if ($isOwner): ?><button type="button" class="pf-pen" data-intro="bio" aria-label="Edit bio"><i class="fas fa-pen"></i></button><?php endif; ?></div>
                    <?php elseif ($isOwner): ?>
                        <div class="pf-bio empty"><span id="pfBio">যেমন: “Helping students understand, not just memorize.”</span><button type="button" class="pf-pen" data-intro="bio" aria-label="Add bio"><i class="fas fa-pen"></i></button></div>
                    <?php endif; ?>
                </div>

                <div class="tp-hright">
                    <div class="tp-hstats">
                        <div><b><?= (int)$Model->StudentCount ?><?= $Model->StudentCount >= 10 ? '+' : '' ?></b><small>Students</small></div>
                        <div><b><?= $rs['count'] ? number_format($rs['avg'], 1) : '—' ?><i class="fas fa-star"></i></b><small>Rating</small></div>
                        <div><b><?= e($expLabel) ?></b><small>Years Experience</small></div>
                    </div>
                    <div class="pf-actions">
                        <?php if ($isOwner): ?>
                            <a href="?tab=profile#ab-basic" data-ab-go="basic" data-ab-open="1" class="pf-btn"><i class="fas fa-pen"></i> Edit Profile</a>
                        <?php elseif (!$isAdminViewer): ?>
                            <div class="fr-actions" data-uid="<?= (int)$u->Id ?>" data-rel="<?= e($rel) ?>"><?= friend_actions_html((int)$u->Id, $rel) ?></div>
                            <button type="button" class="pf-btn<?= $Model->TutorSaved ? ' on' : '' ?>" data-save-type="tutor" data-save-id="<?= (int)$u->Id ?>">
                                <i class="<?= $Model->TutorSaved ? 'fas' : 'far' ?> fa-bookmark"></i> <span><?= $Model->TutorSaved ? 'Saved' : 'Save' ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-body">
            <!-- ======================= LEFT NAV ======================= -->
            <div class="pf-nav-col">
                <nav class="pf-card pf-nav" aria-label="Profile sections">
                    <?php foreach ($nav as [$k, $label, $icon]): ?>
                        <a href="?tab=<?= $k ?>" data-tab="<?= $k ?>" class="<?= $tab === $k ? 'active' : '' ?>"><i class="fas <?= $icon ?>"></i> <?= e($label) ?>
                            <?php if ($k === 'saved' && $saved): ?><span class="pf-n" data-saved-count><?= count($saved) ?></span><?php endif; ?>
                            <?php if ($k === 'students' && $Model->StudentCount): ?><span class="pf-n"><?= (int)$Model->StudentCount ?></span><?php endif; ?>
                            <?php if ($k === 'reviews' && $rs['count']): ?><span class="pf-n"><?= (int)$rs['count'] ?></span><?php endif; ?>
                            <?php if ($k === 'friends' && $Model->FriendCount): ?><span class="pf-n"><?= (int)$Model->FriendCount ?></span><?php endif; ?>
                        </a>
                        <?php if ($k === 'profile' && $isOwner) partial('Profile/_NavProfileSub', ['u' => $u, 'isTeacher' => $isTeacher, 'Model' => $Model]); ?>
                    <?php endforeach; ?>
                </nav>
                <?php $tp = teacher_promo(); if ($tp['tp_enabled'] !== '0'):
                    $tpLink = !is_blank($tp['tp_link']) ? $tp['tp_link'] : '/FindTutor';
                    $tpExt = preg_match('#^https?://#i', $tpLink); ?>
                    <?php if (!is_blank($tp['tp_image'])): ?>
                        <a class="pf-card pf-promo-img tp-promo" href="<?= e($tpLink) ?>"<?= $tpExt ? ' target="_blank" rel="noopener"' : '' ?> aria-label="Create Tuition Post">
                            <img src="<?= e($tp['tp_image']) ?>" alt="আরও বেশি শিক্ষার্থীর কাছে পৌঁছান" loading="lazy" onerror="this.closest('.pf-promo-img').remove()" />
                        </a>
                    <?php elseif ($isOwner): ?>
                        <div class="pf-promo-img tp-promo" style="padding:0;border:none;box-shadow:none;background:none;"><?php partial('Profile/_TeacherPromoCard', ['href' => $tpLink]); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="pf-main">
                <!-- ======================= OVERVIEW / DASHBOARD ======================= -->
                <section class="pf-sec <?= $tab === 'overview' ? 'active' : '' ?>" id="sec-overview">
                    <div class="pf-stats">
                        <?php if ($isOwner): ?>
                            <a href="?tab=students" data-tab="students" class="pf-card st">
                                <span class="st-ico st-friend"><i class="fas fa-user-group"></i></span>
                                <div class="st-body">
                                    <div class="st-t">My Students</div>
                                    <div class="st-v"><b><?= (int)$Model->StudentCount ?></b><span><?= (int)$Model->ActiveStudents ?> Active</span></div>
                                    <div class="st-faces">
                                        <?php foreach (array_slice($students, 0, 3) as $s): ?>
                                            <span class="fc" title="<?= e($s->Name) ?>"><?= e($s->Initials) ?><?php if ($s->Image): ?><img src="<?= e($s->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($students) > 3): ?><span class="plus" style="font-size:11px;font-weight:700;">+<?= count($students) - 3 ?></span>
                                        <?php else: ?><span class="plus" title="স্টুডেন্ট যোগ করুন"><i class="fas fa-plus"></i></span><?php endif; ?>
                                    </div>
                                </div>
                            </a>
                            <a href="?tab=schedule" data-tab="schedule" class="pf-card st">
                                <span class="st-ico st-class"><i class="fas fa-calendar-check"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Today's Classes</div>
                                    <div class="st-v"><b><?= (int)$Model->TodayClasses ?></b><span><?= $Model->TodayClasses === 1 ? 'Class' : 'Classes' ?></span></div>
                                    <span class="st-link">View Schedule <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                            <a href="?tab=earnings" data-tab="earnings" class="pf-card st">
                                <span class="st-ico st-earn"><i class="fas fa-wallet"></i></span>
                                <div class="st-body">
                                    <div class="st-t">This Month Earnings</div>
                                    <div class="st-v"><b><?= $money($earn['this']) ?></b></div>
                                    <?php if ($earn['pct'] !== null): ?>
                                        <div class="st-up<?= $earn['pct'] < 0 ? ' down' : '' ?>"><i class="fas fa-arrow-<?= $earn['pct'] < 0 ? 'down' : 'up' ?>"></i> <?= abs($earn['pct']) ?>% from last month</div>
                                    <?php else: ?><div class="st-empty">আয় লিখে রাখুন</div><?php endif; ?>
                                </div>
                                <?php $mx = max(1, max($earn['months'])); ?>
                                <span class="st-bars" aria-hidden="true"><?php foreach (array_slice($earn['months'], 1) as $v): ?><i style="height: <?= max(12, (int)round($v / $mx * 100)) ?>%"></i><?php endforeach; ?></span>
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
                                <span class="st-ico st-tut"><i class="fas fa-user-graduate"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Students</div>
                                    <div class="st-v"><b><?= (int)$Model->StudentCount ?></b><span>taught</span></div>
                                    <div class="st-empty"><?= e($expLabel) ?> years experience</div>
                                </div>
                            </div>
                            <a href="?tab=overview" class="pf-card st" data-feed-go="materials">
                                <span class="st-ico st-earn"><i class="fas fa-file-lines"></i></span>
                                <div class="st-body">
                                    <div class="st-t">Study Materials</div>
                                    <div class="st-v"><b><?= (int)$Model->MaterialCount ?></b><span>shared</span></div>
                                    <div class="st-empty"><?= (int)$Model->PostCount ?> total posts</div>
                                </div>
                            </a>
                        <?php endif; ?>
                        <a href="?tab=reviews" data-tab="reviews" class="pf-card st">
                            <span class="st-ico st-rate"><i class="fas fa-star"></i></span>
                            <div class="st-body">
                                <div class="st-t">Average Rating</div>
                                <div class="st-v"><b><?= $rs['count'] ? number_format($rs['avg'], 1) : '—' ?></b><small>(<?= (int)$rs['count'] ?> review<?= $rs['count'] === 1 ? '' : 's' ?>)</small></div>
                                <div style="margin-top:8px;"><?= $stars((float)$rs['avg']) ?></div>
                            </div>
                        </a>
                    </div>

                    <div class="ov-grid">
                        <div class="ov-main">
                            <?php if ($isOwner): ?>
                                <div class="pf-card pf-compose">
                                    <div class="pf-compose-top">
                                        <span class="pc-avatar me-avatar"></span>
                                        <button type="button" class="pf-compose-in" data-compose="post">Share an update with your students...</button>
                                    </div>
                                    <div class="pf-chips">
                                        <button type="button" class="pf-chip ch-mat" data-compose="material"><i class="fas fa-file-lines"></i> Study Material</button>
                                        <button type="button" class="pf-chip ch-upd" data-compose="update"><i class="fas fa-chalkboard-user"></i> Class Update</button>
                                        <button type="button" class="pf-chip ch-not" data-compose="notice"><i class="fas fa-bullhorn"></i> Notice</button>
                                        <button type="button" class="pf-chip ch-ach" data-compose="achievement"><i class="fas fa-trophy"></i> Achievement</button>
                                        <button type="button" class="pf-chip ch-poll" data-compose="poll"><i class="fas fa-chart-column"></i> Poll</button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="tp-ftabs" id="feedTabs" role="tablist">
                                <button type="button" data-feed="updates" class="active">Recent Updates</button>
                                <button type="button" data-feed="materials">Class Materials</button>
                                <button type="button" data-feed="achievements">Achievements</button>
                                <button type="button" data-feed="reviews" data-pane>Reviews</button>
                            </div>
                            <span id="psPostCount" hidden><?= (int)$Model->PostCount ?></span>
                            <div id="psFeed" data-tab="updates">
                                <?php foreach ($Model->Posts as $p) partial('Post/_Post', ['post' => $p, 'viewerId' => $me->Id, 'viewerIsAdmin' => $isAdminViewer]); ?>
                            </div>
                            <div class="pf-card pf-empty" id="psEmpty" <?= $Model->Posts ? 'hidden' : '' ?>>
                                <div class="ei"><i class="far fa-newspaper"></i></div>
                                <h5><?= $isOwner ? 'স্টুডেন্টদের সাথে প্রথম আপডেট শেয়ার করুন' : 'দেখানোর মতো কোনো পোস্ট নেই' ?></h5>
                                <p><?= $isOwner ? 'নোট/PDF শেয়ার করতে Study Material, ক্লাসের খবর জানাতে Class Update বা Notice দিন।' : ($rel === 'friends' ? e($firstName) . ' এখনো কিছু পোস্ট করেননি।' : 'ফ্রেন্ড হলে শুধু-ফ্রেন্ডদের পোস্টও দেখতে পাবেন।') ?></p>
                            </div>
                            <?php if ($Model->HasMore): ?>
                                <button type="button" class="pc-loadmore" id="psMore"><i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন</button>
                            <?php endif; ?>
                            <div data-feed-pane="reviews" hidden>
                                <?php if (!$reviews): ?>
                                    <div class="pf-card pf-empty"><div class="ei"><i class="far fa-star"></i></div><h5>এখনো কোনো রিভিউ নেই</h5>
                                        <p>এই টিচারের কাছে পড়া স্টুডেন্টরা রিভিউ দিতে পারেন।</p></div>
                                <?php else: ?>
                                    <?php foreach (array_slice($reviews, 0, 5) as $r) partial('Profile/_TeacherReview', ['r' => $r, 'me' => $me->Id, 'isAdmin' => $isAdminViewer, 'stars' => $stars]); ?>
                                    <?php if (count($reviews) > 5): ?><a href="?tab=reviews" data-tab="reviews" class="pc-loadmore" style="display:block;text-align:center;text-decoration:none;">সব <?= count($reviews) ?>টি রিভিউ দেখুন</a><?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <aside class="ov-right">
                            <!-- My Subjects -->
                            <div class="pf-card rc">
                                <div class="rc-h"><h4><?= $isOwner ? 'My Subjects' : 'Subjects' ?></h4><?php if ($isOwner): ?><button type="button" data-intro="subjects">Edit</button><?php endif; ?></div>
                                <div class="sj-chips">
                                    <?php foreach ($intro->Subjects as $i => $sj): ?><span class="<?= $subjColors[$i % count($subjColors)] ?>"><?= e($sj) ?></span><?php endforeach; ?>
                                    <?php foreach ($tIntro->Levels as $lv): ?><span class="lv"><?= e($lv) ?></span><?php endforeach; ?>
                                    <?php if (!$intro->Subjects && !$tIntro->Levels): ?>
                                        <p class="rc-empty" style="margin:0;"><?= $isOwner ? 'Edit চেপে কোন কোন বিষয় ও ক্লাস পড়ান যোগ করুন।' : 'যোগ করা হয়নি।' ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($isOwner): ?>
                                <!-- Upcoming Classes -->
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4>Upcoming Classes</h4><a href="?tab=schedule" data-tab="schedule">View all</a></div>
                                    <?php $up = array_slice($Model->Upcoming, 0, 3); if (!$up): ?>
                                        <p class="rc-empty">কোনো ক্লাস নেই। <a href="?tab=schedule" data-tab="schedule">রুটিনে ক্লাস যোগ করুন</a> — আজকের ক্লাস এখানে দেখাবে।</p>
                                    <?php else: ?>
                                        <?php foreach ($up as $i => $o): $c = $o->Class; ?>
                                            <div class="uc-item">
                                                <span class="uc-ico <?= ['a', 'b', 'c'][$i % 3] ?>"><i class="fas <?= ['fa-square-root-variable', 'fa-atom', 'fa-book'][$i % 3] ?>"></i></span>
                                                <div class="uc-who"><b><?= e($c->Title) ?><?= $o->IsLive ? '<span class="uc-live">LIVE</span>' : '' ?></b><small><?= e($occLabel($o)) ?></small></div>
                                                <?php if ($c->MeetLink !== ''): ?>
                                                    <a class="uc-btn <?= $o->IsLive ? 'start' : 'join' ?>" href="<?= e($c->MeetLink) ?>" target="_blank" rel="noopener"><?= $o->IsLive ? 'Start' : 'Join' ?></a>
                                                <?php else: ?>
                                                    <a class="uc-btn ghost" href="?tab=schedule" data-tab="schedule">View</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Recent Students -->
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4>Recent Students</h4><?php if ($students): ?><a href="?tab=students" data-tab="students">View all</a><?php endif; ?></div>
                                    <?php if (!$students): ?>
                                        <p class="rc-empty">স্টুডেন্টরা আপনার অ্যাপ্লিকেশন Accept করলে নিজে থেকেই এখানে আসবে। নিজেও <a href="?tab=students" data-tab="students">যোগ করতে পারেন</a>।</p>
                                    <?php else: ?>
                                        <div class="tu-list">
                                            <?php foreach (array_slice($students, 0, 3) as $s): $wa = $s->Phone !== '' ? $waLink($s->Phone) : null; ?>
                                                <div class="tu-item">
                                                    <?php if ($s->StudentUserId): ?><a class="tu-av" href="/Profile/Show/<?= $s->StudentUserId ?>"><?php else: ?><span class="tu-av"><?php endif; ?>
                                                        <?= e($s->Initials) ?><?php if ($s->Image): ?><img src="<?= e($s->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?>
                                                    <?= $s->StudentUserId ? '</a>' : '</span>' ?>
                                                    <div class="tu-who"><?= $s->StudentUserId ? '<a href="/Profile/Show/' . $s->StudentUserId . '">' . e($s->Name) . '</a>' : '<b style="font-size:15px;">' . e($s->Name) . '</b>' ?>
                                                        <small><?= e(implode(' · ', array_filter([$s->ClassLevel, $s->Subject]))) ?: '—' ?></small></div>
                                                    <?php if ($wa): ?><a class="tu-msg" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> Message</a>
                                                    <?php elseif ($s->StudentUserId): ?><a class="tu-msg" href="/Profile/Show/<?= $s->StudentUserId ?>">View</a><?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <!-- About (visitor) -->
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4>About</h4></div>
                                    <ul class="am-list">
                                        <li><i class="fas fa-chalkboard-user"></i> <?= e($headline !== '' ? $headline : 'Tutor') ?></li>
                                        <?php $inst = !is_blank($ti->Institution ?? null) ? $ti->Institution : $u->InstitutionName; if (!is_blank($inst)): ?><li><i class="fas fa-building-columns"></i> <?= e($inst) ?></li><?php endif; ?>
                                        <?php if ($location !== ''): ?><li><i class="fas fa-location-dot"></i> <?= e($location) ?></li><?php endif; ?>
                                        <?php if ($Model->ExpText !== ''): ?><li><i class="fas fa-briefcase"></i> <?= e($Model->ExpText) ?> experience</li><?php endif; ?>
                                        <?php if ($modeLabel !== ''): ?><li><i class="fas fa-laptop-house"></i> <?= e($modeLabel) ?></li><?php endif; ?>
                                        <?php if ($feeText !== ''): ?><li><i class="fas fa-bangladeshi-taka-sign"></i> <?= e($feeText) ?></li><?php elseif (!is_blank($ti->HourlyRate ?? null)): ?><li><i class="fas fa-money-bill-wave"></i> Salary: <?= e(preg_match('/^\d+$/', trim((string)$ti->HourlyRate)) ? '৳' . number_format((int)$ti->HourlyRate) : $ti->HourlyRate) ?></li><?php endif; ?>
                                        <li><i class="far fa-calendar"></i> Joined <?= e(fmt_date($u->CreatedAt, 'F Y', '—')) ?></li>
                                    </ul>
                                    <?php if ($intro->Quote !== ''): ?><div class="am-quote"><?= e($intro->Quote) ?></div><?php endif; ?>
                                </div>
                                <div class="pf-card rc">
                                    <div class="rc-h"><h4>Reviews</h4><a href="?tab=reviews" data-tab="reviews"><?= $Model->CanReview ? ($Model->MyReview ? 'Edit my review' : 'Write a review') : 'View all' ?></a></div>
                                    <div style="display:flex;align-items:center;gap:12px;"><b style="font-size:34px;line-height:1;"><?= $rs['count'] ? number_format($rs['avg'], 1) : '—' ?></b>
                                        <div><?= $stars((float)$rs['avg']) ?><div style="font-size:13px;color:var(--pf-muted);"><?= (int)$rs['count'] ?> review<?= $rs['count'] === 1 ? '' : 's' ?></div></div></div>
                                </div>
                            <?php endif; ?>
                        </aside>
                    </div>
                </section>

                <!-- ======================= PROFILE (About) ======================= -->
                <section class="pf-sec <?= $tab === 'profile' ? 'active' : '' ?>" id="sec-profile">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Profile' : 'Profile' ?></h2><p><?= $isOwner ? 'আপনার সব তথ্য — অন্যরা যা দেখতে পায়। ফোন, ইমেইল ও ঠিকানা শুধু ফ্রেন্ডরা দেখে।' : 'ফোন, ইমেইল ও ঠিকানা শুধু ফ্রেন্ডরা দেখতে পারেন।' ?></p></div>
                        <?php if ($isOwner): ?><a href="?tab=profile#ab-basic" data-ab-go="basic" data-ab-open="1" class="pf-btn"><i class="fas fa-pen"></i> Edit Profile</a><?php endif; ?></div>
                    <?php partial('Profile/_ShowAbout', ['u' => $u, 'isOwner' => $isOwner, 'isTeacher' => true, 'ti' => $ti, 'canPrivate' => $canPrivate, 'Model' => $Model]); ?>
                </section>

                <!-- ======================= SUBJECTS ======================= -->
                <section class="pf-sec <?= $tab === 'subjects' ? 'active' : '' ?>" id="sec-subjects">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Subjects' : 'Subjects' ?></h2><p>কোন বিষয় আর কোন ক্লাস/লেভেলে পড়ানো হয়।</p></div>
                        <?php if ($isOwner): ?><button type="button" class="pf-btn primary" data-intro="subjects"><i class="fas fa-pen"></i> Edit subjects</button><?php endif; ?></div>
                    <div class="pf-card rc" style="margin-bottom:16px;">
                        <div class="rc-h"><h4>Subjects</h4></div>
                        <div class="sj-chips">
                            <?php foreach ($intro->Subjects as $i => $sj): ?><span class="<?= $subjColors[$i % count($subjColors)] ?>"><?= e($sj) ?></span><?php endforeach; ?>
                            <?php if (!$intro->Subjects): ?><p class="rc-empty" style="margin:0;">যোগ করা হয়নি।</p><?php endif; ?>
                        </div>
                        <div class="rc-h" style="margin-top:20px;"><h4>Classes / Levels</h4></div>
                        <div class="sj-chips">
                            <?php foreach ($tIntro->Levels as $lv): ?><span class="lv"><?= e($lv) ?></span><?php endforeach; ?>
                            <?php if (!$tIntro->Levels): ?><p class="rc-empty" style="margin:0;">যোগ করা হয়নি।</p><?php endif; ?>
                        </div>
                    </div>
                    <?php if ($ti): ?>
                        <div class="pf-card tp-info">
                            <?php foreach ([['Subject Expertise', $ti->SubjectExpertise ?? ''], ['Education', $ti->Education ?? ''], ['Institution', $ti->Institution ?? ''],
                                            ['Experience', $ti->Experience ?? ''], ['Teaching Style', $ti->TeachingStyle ?? ''], ['Available Days', $ti->AvailableDays ?? ''],
                                            ['Preferred Time', $ti->PreferredTime ?? ''], ['Expected Salary / Rate', $ti->HourlyRate ?? '']] as [$lb, $v]): if (is_blank($v)) continue; ?>
                                <div><small><?= $lb ?></small><span><?= e($v) ?></span></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ======================= REVIEWS ======================= -->
                <section class="pf-sec <?= $tab === 'reviews' ? 'active' : '' ?>" id="sec-reviews">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Reviews' : 'Reviews' ?></h2><p><?= $isOwner ? 'আপনার স্টুডেন্টরা যা বলেছেন।' : 'শুধু এই টিচারের কাছে পড়া স্টুডেন্টরা রিভিউ দিতে পারেন।' ?></p></div></div>
                    <div class="pf-card rv-top">
                        <div class="rv-avg"><b><?= $rs['count'] ? number_format($rs['avg'], 1) : '0.0' ?></b><div style="margin-top:8px;"><?= $stars((float)$rs['avg'], 'lg') ?></div><small><?= (int)$rs['count'] ?> review<?= $rs['count'] === 1 ? '' : 's' ?></small></div>
                        <div class="rv-dist">
                            <?php foreach ($rs['dist'] as $k => $n): ?>
                                <div><span><?= $k ?> <i class="fas fa-star" style="color:#F59E0B;font-size:11px;"></i></span><span class="bar"><span style="width: <?= $rs['count'] ? round($n / $rs['count'] * 100) : 0 ?>%"></span></span><em><?= $n ?></em></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if ($Model->CanReview): $mr = $Model->MyReview; ?>
                        <form class="pf-card rv-form" id="rvForm" autocomplete="off">
                            <h4><?= $mr ? 'আপনার রিভিউ বদলান' : $firstName . ' কে রিভিউ দিন' ?></h4>
                            <small style="color:var(--pf-muted);">আপনার অভিজ্ঞতা অন্য স্টুডেন্টদের টিচার বাছাইয়ে সাহায্য করবে।</small>
                            <div class="rv-pick">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rvRating" id="rv<?= $i ?>" value="<?= $i ?>" <?= $mr && (int)$mr->Rating === $i ? 'checked' : '' ?> /><label for="rv<?= $i ?>" title="<?= $i ?> star"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                            <textarea id="rvComment" maxlength="1000" placeholder="কেমন পড়ান, বুঝিয়ে বলেন কিনা, সময়মতো আসেন কিনা..."><?= e((string)($mr->Comment ?? '')) ?></textarea>
                            <div style="display:flex;justify-content:flex-end;margin-top:10px;"><button type="submit" class="pf-btn primary" id="rvSave"><i class="fas fa-paper-plane"></i> <?= $mr ? 'Update review' : 'Submit review' ?></button></div>
                        </form>
                    <?php elseif (!$isOwner && !$isAdminViewer && current_role() !== 'Teacher'): ?>
                        <div class="pf-card" style="padding:14px 18px;margin-bottom:16px;font-size:14px;color:var(--pf-muted);"><i class="fas fa-circle-info" style="color:var(--pf-blue);"></i>
                            এই টিচারের কাছে পড়লে (Tuition Circular এ তার অ্যাপ্লিকেশন Accept করলে) রিভিউ দিতে পারবেন।</div>
                    <?php endif; ?>
                    <div id="rvList">
                        <?php foreach ($reviews as $r) partial('Profile/_TeacherReview', ['r' => $r, 'me' => $me->Id, 'isAdmin' => $isAdminViewer, 'stars' => $stars]); ?>
                    </div>
                    <?php if (!$reviews): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="far fa-star"></i></div><h5>এখনো কোনো রিভিউ নেই</h5>
                            <p><?= $isOwner ? 'স্টুডেন্টরা আপনার প্রোফাইলে এসে রিভিউ দিতে পারবেন।' : '' ?></p></div>
                    <?php endif; ?>
                </section>

                <!-- ======================= CERTIFICATES ======================= -->
                <section class="pf-sec <?= $tab === 'certificates' ? 'active' : '' ?>" id="sec-certificates">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Certificates' : 'Certificates' ?></h2><p>ডিগ্রি, ট্রেনিং আর অর্জনের সার্টিফিকেট।</p></div>
                        <?php if ($isOwner): ?><button type="button" class="pf-btn primary" data-open="certOv"><i class="fas fa-plus"></i> Add certificate</button><?php endif; ?></div>
                    <?php if (!$certs): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-award"></i></div><h5>কোনো সার্টিফিকেট নেই</h5>
                            <p><?= $isOwner ? 'সার্টিফিকেটের ছবি বা PDF যোগ করুন — স্টুডেন্টদের আস্থা বাড়বে।' : '' ?></p></div>
                    <?php else: ?>
                        <div class="ct-grid">
                            <?php foreach ($certs as $c): ?>
                                <div class="pf-card ct-card">
                                    <?php if ($c->FileUrl): ?>
                                        <a class="ct-thumb<?= $c->IsPdf ? ' pdf' : '' ?>" href="<?= e($c->FileUrl) ?>" target="_blank" rel="noopener"<?= $c->IsPdf ? '' : ' data-zoom="' . e($c->FileUrl) . '"' ?>>
                                            <?= $c->IsPdf ? '<i class="fas fa-file-pdf"></i>' : '<img src="' . e($c->FileUrl) . '" alt="' . e($c->Title) . '" loading="lazy" />' ?></a>
                                    <?php else: ?><span class="ct-thumb"><i class="fas fa-award"></i></span><?php endif; ?>
                                    <div class="ct-body">
                                        <div><b><?= e($c->Title) ?></b><small><?= e(implode(' · ', array_filter([$c->Issuer, $c->Year]))) ?></small></div>
                                        <?php if ($isOwner): ?><button type="button" class="tp-menu-btn" data-tp-del="/Tutor/DeleteCertificate" data-id="<?= $c->Id ?>" data-confirm="সার্টিফিকেটটি মুছে ফেলবেন?" aria-label="Delete"><i class="fas fa-trash-can"></i></button><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ======================= FRIENDS ======================= -->
                <section class="pf-sec <?= $tab === 'friends' ? 'active' : '' ?>" id="sec-friends">
                    <div class="pf-sec-h"><div><h2><?= $isOwner ? 'My Friends' : e($firstName) . "'s Friends" ?></h2><p><?= (int)$Model->FriendCount ?> friend<?= $Model->FriendCount === 1 ? '' : 's' ?></p></div>
                        <?php if ($isOwner): ?><a href="/Friends?tab=find" class="pf-btn"><i class="fas fa-user-plus"></i> Find People</a><?php endif; ?></div>
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

                <?php if ($isOwner): ?>
                <!-- ======================= STUDENTS ======================= -->
                <section class="pf-sec <?= $tab === 'students' ? 'active' : '' ?>" id="sec-students">
                    <div class="pf-sec-h"><div><h2>My Students</h2><p>Accept হওয়া অ্যাপ্লিকেশনের স্টুডেন্টরা নিজে থেকেই আসে। অন্য স্টুডেন্টদেরও যোগ করে রাখতে পারেন — শুধু আপনি দেখবেন।</p></div>
                        <button type="button" class="pf-btn primary" data-open="stuOv"><i class="fas fa-plus"></i> Add student</button></div>
                    <?php if (!$students): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-user-graduate"></i></div><h5>এখনো কোনো স্টুডেন্ট নেই</h5>
                            <p>Tuition Circular এ Apply করুন — স্টুডেন্ট Accept করলে এখানে দেখাবে। অথবা নিজে যোগ করুন।</p>
                            <a href="/Tuition/Circular" class="pf-btn primary"><i class="fas fa-magnifying-glass"></i> Tuition খুঁজুন</a></div>
                    <?php else: ?>
                        <div class="tp-filter" id="stuFilter">
                            <button type="button" class="active" data-f="all">All (<?= count($students) ?>)</button>
                            <button type="button" data-f="Active">Active (<?= (int)$Model->ActiveStudents ?>)</button>
                            <button type="button" data-f="Inactive">Inactive (<?= count($students) - (int)$Model->ActiveStudents ?>)</button>
                        </div>
                        <div class="pf-card tp-list" id="stuList">
                            <?php foreach ($students as $s): $wa = $s->Phone !== '' ? $waLink($s->Phone) : null; ?>
                                <div class="tp-row" data-status="<?= $s->Status ?>">
                                    <?php if ($s->StudentUserId): ?><a class="tu-av" href="/Profile/Show/<?= $s->StudentUserId ?>"><?php else: ?><span class="tu-av"><?php endif; ?>
                                        <?= e($s->Initials) ?><?php if ($s->Image): ?><img src="<?= e($s->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?>
                                    <?= $s->StudentUserId ? '</a>' : '</span>' ?>
                                    <div class="tu-who">
                                        <b style="font-size:15px;"><?= e($s->Name) ?></b>
                                        <span class="tp-badge<?= $s->Status === 'Inactive' ? ' off' : '' ?>"><?= $s->Status ?></span>
                                        <?php if ($s->StudentUserId): ?><span class="tp-badge auto" title="Tuition Circular থেকে">Circular</span><?php endif; ?>
                                        <div class="tp-meta">
                                            <?php if ($s->ClassLevel !== '' || $s->Subject !== ''): ?><span><i class="fas fa-graduation-cap"></i> <?= e(implode(' · ', array_filter([$s->ClassLevel, $s->Subject]))) ?></span><?php endif; ?>
                                            <?php if ($s->Phone !== ''): ?><span><i class="fas fa-phone"></i> <?= e($s->Phone) ?></span><?php endif; ?>
                                            <?php if ($s->MonthlyFee !== null): ?><span><i class="fas fa-money-bill-wave"></i> <?= $money($s->MonthlyFee) ?>/month</span><?php endif; ?>
                                            <?php if ($s->Note !== ''): ?><span><i class="far fa-note-sticky"></i> <?= e($s->Note) ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($wa): ?><a class="tu-msg" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> <span class="d-none d-sm-inline">Message</span></a><?php endif; ?>
                                    <div class="tp-menu-wrap">
                                        <button type="button" class="tp-menu-btn" data-tp="menu" aria-label="Options"><i class="fas fa-ellipsis"></i></button>
                                        <div class="tp-menu">
                                            <button type="button" data-tp-edit="stuOv" data-json="<?= e(json_encode(['id' => $s->Id, 'name' => $s->Name, 'classLevel' => $s->ClassLevel, 'subject' => $s->Subject, 'phone' => $s->Phone, 'monthlyFee' => $s->MonthlyFee, 'status' => $s->Status, 'note' => $s->Note], JSON_UNESCAPED_UNICODE)) ?>"><i class="fas fa-pen"></i> Edit</button>
                                            <button type="button" data-tp-pay="<?= $s->Id ?>" data-fee="<?= (int)$s->MonthlyFee ?>"><i class="fas fa-wallet"></i> Add payment</button>
                                            <button type="button" class="danger" data-tp-del="/Tutor/DeleteStudent" data-id="<?= $s->Id ?>" data-confirm="<?= e($s->Name) ?> কে তালিকা থেকে সরাবেন?"><i class="fas fa-trash-can"></i> Remove</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ======================= SCHEDULE ======================= -->
                <section class="pf-sec <?= $tab === 'schedule' ? 'active' : '' ?>" id="sec-schedule">
                    <div class="pf-sec-h"><div><h2>My Schedule</h2><p>সাপ্তাহিক ক্লাস রুটিন। Google Meet / Zoom লিংক দিলে ক্লাসের সময় <b>Start</b> বাটন আসবে।</p></div>
                        <button type="button" class="pf-btn primary" data-open="clsOv"><i class="fas fa-plus"></i> Add class</button></div>
                    <div class="tp-week">
                        <?php $todayW = (int)date('w'); foreach ([6, 0, 1, 2, 3, 4, 5] as $d): ?>
                            <div class="pf-card tp-day<?= $d === $todayW ? ' today' : '' ?>">
                                <h5><?= $days[$d] ?><?= $d === $todayW ? ' · Today' : '' ?></h5>
                                <?php $any = false; foreach ($Model->Classes as $c): if ($c->ClassDate || !in_array($d, $c->Days, true)) continue; $any = true; ?>
                                    <button type="button" class="tp-cls" data-tp-edit="clsOv" data-json="<?= e(json_encode(['id' => $c->Id, 'title' => $c->Title, 'days' => $c->Days, 'classDate' => '', 'startTime' => $c->StartTime, 'endTime' => $c->EndTime, 'meetLink' => $c->MeetLink, 'location' => $c->Location], JSON_UNESCAPED_UNICODE)) ?>">
                                        <b><?= e($c->Title) ?></b><small><?= e($t12($c->StartTime)) ?> - <?= e($t12($c->EndTime)) ?></small></button>
                                <?php endforeach; ?>
                                <?php if (!$any): ?><span class="tp-empty-day">—</span><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php $once = array_filter($Model->Classes, fn($c) => $c->ClassDate && $c->ClassDate >= date('Y-m-d')); if ($once): ?>
                        <h4 style="margin:22px 0 10px;font-size:16px;font-weight:700;">নির্দিষ্ট তারিখের ক্লাস</h4>
                        <div class="pf-card tp-list">
                            <?php foreach ($once as $c): ?>
                                <div class="tp-row"><span class="uc-ico b"><i class="far fa-calendar"></i></span>
                                    <div class="tu-who"><b style="font-size:15px;"><?= e($c->Title) ?></b><div class="tp-meta"><span><?= e(fmt_date($c->ClassDate, 'D, d M Y')) ?> · <?= e($t12($c->StartTime)) ?> - <?= e($t12($c->EndTime)) ?></span><?php if ($c->Location !== ''): ?><span><i class="fas fa-location-dot"></i> <?= e($c->Location) ?></span><?php endif; ?></div></div>
                                    <button type="button" class="tu-msg" data-tp-edit="clsOv" data-json="<?= e(json_encode(['id' => $c->Id, 'title' => $c->Title, 'days' => [], 'classDate' => $c->ClassDate, 'startTime' => $c->StartTime, 'endTime' => $c->EndTime, 'meetLink' => $c->MeetLink, 'location' => $c->Location], JSON_UNESCAPED_UNICODE)) ?>">Edit</button></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <h4 style="margin:22px 0 10px;font-size:16px;font-weight:700;">আগামী ৭ দিন</h4>
                    <?php if (!$Model->Upcoming): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-calendar-days"></i></div><h5>কোনো ক্লাস নেই</h5><p><b>Add class</b> চেপে সপ্তাহের কোন দিন কখন ক্লাস তা লিখে রাখুন।</p></div>
                    <?php else: ?>
                        <div class="pf-card tp-list">
                            <?php foreach ($Model->Upcoming as $i => $o): $c = $o->Class; ?>
                                <div class="tp-row">
                                    <span class="uc-ico <?= ['a', 'b', 'c'][$i % 3] ?>"><i class="fas fa-chalkboard"></i></span>
                                    <div class="tu-who"><b style="font-size:15px;"><?= e($c->Title) ?></b><?= $o->IsLive ? '<span class="uc-live">LIVE</span>' : '' ?>
                                        <div class="tp-meta"><span><?= e($occLabel($o)) ?></span><?php if ($c->Location !== ''): ?><span><i class="fas fa-location-dot"></i> <?= e($c->Location) ?></span><?php endif; ?></div></div>
                                    <?php if ($c->MeetLink !== ''): ?><a class="uc-btn <?= $o->IsLive ? 'start' : 'join' ?>" href="<?= e($c->MeetLink) ?>" target="_blank" rel="noopener"><?= $o->IsLive ? 'Start' : 'Join' ?></a><?php endif; ?>
                                    <div class="tp-menu-wrap">
                                        <button type="button" class="tp-menu-btn" data-tp="menu" aria-label="Options"><i class="fas fa-ellipsis"></i></button>
                                        <div class="tp-menu">
                                            <button type="button" data-tp-edit="clsOv" data-json="<?= e(json_encode(['id' => $c->Id, 'title' => $c->Title, 'days' => $c->Days, 'classDate' => (string)$c->ClassDate, 'startTime' => $c->StartTime, 'endTime' => $c->EndTime, 'meetLink' => $c->MeetLink, 'location' => $c->Location], JSON_UNESCAPED_UNICODE)) ?>"><i class="fas fa-pen"></i> Edit class</button>
                                            <button type="button" class="danger" data-tp-del="/Tutor/DeleteClass" data-id="<?= $c->Id ?>" data-confirm="ক্লাসটি রুটিন থেকে মুছে ফেলবেন?"><i class="fas fa-trash-can"></i> Delete</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ======================= TUITION POSTS ======================= -->
                <section class="pf-sec <?= $tab === 'tuitionposts' ? 'active' : '' ?>" id="sec-tuitionposts">
                    <div class="pf-sec-h"><div><h2>My Tuition Posts</h2><p>Find Tutor পেজে আপনার দেওয়া "টিউশন চাই" পোস্ট — স্টুডেন্টরা এগুলো দেখে যোগাযোগ করে।</p></div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;"><a href="/Teacher/TeacherDashboard?section=applications" class="pf-btn"><i class="fas fa-list-check"></i> Applications</a>
                        <a href="/FindTutor" class="pf-btn primary"><i class="fas fa-plus"></i> Create Tuition Post</a></div></div>
                    <?php if (!$Model->TeachPosts): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-file-lines"></i></div><h5>এখনো কোনো টিউশন পোস্ট নেই</h5>
                            <p>কোন বিষয়ে, কোন ক্লাসে, কোন এলাকায় পড়াতে চান লিখে পোস্ট দিন — আরও বেশি শিক্ষার্থীর কাছে পৌঁছান।</p>
                            <a href="/FindTutor" class="pf-btn primary"><i class="fas fa-plus"></i> Create Tuition Post</a></div>
                    <?php else: ?>
                        <?php foreach ($Model->TeachPosts as $tpst): ?>
                            <div class="pf-card tp-tp">
                                <p><?= e(str_preview($tpst->Content, 500)) ?></p>
                                <?php if ($tpst->Image): ?><img src="<?= e($tpst->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?>
                                <div class="ci-foot">
                                    <span><i class="far fa-clock"></i> <?= e(time_ago($tpst->CreatedAt)) ?></span>
                                    <span><i class="far fa-heart"></i> <?= (int)$tpst->Likes ?></span>
                                    <span><i class="far fa-comment"></i> <?= (int)$tpst->Comments ?></span>
                                    <a href="/FindTutor#post-<?= $tpst->Id ?>" class="tu-view">দেখুন</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

                <!-- ======================= SAVED ======================= -->
                <section class="pf-sec <?= $tab === 'saved' ? 'active' : '' ?>" id="sec-saved">
                    <div class="pf-sec-h"><div><h2>Saved Items</h2><p>পরে দেখার জন্য যেগুলো Save করেছেন — পোস্ট, টিউটর, সার্কুলার, টিউশন, নোটিশ।</p></div></div>
                    <?php if (!$saved): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="far fa-bookmark"></i></div><h5>এখনো কিছু Save করেননি</h5>
                            <p>পোস্ট, Tuition Circular, টিউশন বা নোটিশে <b>Save</b> বাটন চাপলে এখানে জমা থাকবে।</p></div>
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

                <!-- ======================= EARNINGS ======================= -->
                <section class="pf-sec <?= $tab === 'earnings' ? 'active' : '' ?>" id="sec-earnings">
                    <div class="pf-sec-h"><div><h2>My Earnings</h2><p>টিউশন থেকে পাওয়া টাকা লিখে রাখুন — মাস শেষে হিসাব সহজ। শুধু আপনি দেখতে পান।</p></div>
                        <button type="button" class="pf-btn primary" data-open="payOv"><i class="fas fa-plus"></i> Add payment</button></div>
                    <div class="tp-sum">
                        <div class="pf-card"><small>This month (<?= date('F') ?>)</small><b style="color:#16A34A;"><?= $money($earn['this']) ?></b>
                            <?php if ($earn['pct'] !== null): ?><div class="st-up<?= $earn['pct'] < 0 ? ' down' : '' ?>"><i class="fas fa-arrow-<?= $earn['pct'] < 0 ? 'down' : 'up' ?>"></i> <?= abs($earn['pct']) ?>% from last month</div><?php endif; ?></div>
                        <div class="pf-card"><small>Last month</small><b><?= $money($earn['last']) ?></b></div>
                        <div class="pf-card"><small>Total (all time)</small><b><?= $money($earn['total']) ?></b></div>
                    </div>
                    <div class="pf-card tp-chart">
                        <h4>শেষ ৬ মাস</h4>
                        <div class="tp-bars">
                            <?php $mx = max(1, max($earn['months'])); $keys = array_keys($earn['months']); foreach ($earn['months'] as $m => $v): ?>
                                <div class="tp-bar<?= $m === end($keys) ? ' cur' : '' ?>"><b><?= $v ? $money($v) : '' ?></b><span style="height: <?= max(2, (int)round($v / $mx * 100)) ?>%"></span><em><?= date('M', strtotime($m . '-01')) ?></em></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (!$Model->Payments): ?>
                        <div class="pf-card pf-empty"><div class="ei"><i class="fas fa-wallet"></i></div><h5>এখনো কোনো আয় লেখা হয়নি</h5><p>কোন স্টুডেন্ট কত টাকা দিলেন <b>Add payment</b> দিয়ে লিখে রাখুন।</p></div>
                    <?php else: ?>
                        <div class="pf-card tp-list">
                            <?php foreach ($Model->Payments as $pay): ?>
                                <div class="tp-row">
                                    <span class="uc-ico c"><i class="fas fa-bangladeshi-taka-sign"></i></span>
                                    <div class="tu-who"><b style="font-size:15px;"><?= $money($pay->Amount) ?></b>
                                        <div class="tp-meta"><span><?= e(date('F Y', strtotime($pay->Month . '-01'))) ?></span><?php if ($pay->StudentName !== ''): ?><span><i class="fas fa-user"></i> <?= e($pay->StudentName) ?></span><?php endif; ?>
                                            <?php if ($pay->PaidOn): ?><span><i class="far fa-calendar"></i> <?= e(fmt_date($pay->PaidOn, 'd M')) ?></span><?php endif; ?><?php if ($pay->Note !== ''): ?><span><?= e($pay->Note) ?></span><?php endif; ?></div></div>
                                    <div class="tp-menu-wrap">
                                        <button type="button" class="tp-menu-btn" data-tp="menu" aria-label="Options"><i class="fas fa-ellipsis"></i></button>
                                        <div class="tp-menu">
                                            <button type="button" data-tp-edit="payOv" data-json="<?= e(json_encode(['id' => $pay->Id, 'studentId' => $pay->StudentId, 'amount' => $pay->Amount, 'month' => $pay->Month, 'paidOn' => (string)$pay->PaidOn, 'note' => $pay->Note], JSON_UNESCAPED_UNICODE)) ?>"><i class="fas fa-pen"></i> Edit</button>
                                            <button type="button" class="danger" data-tp-del="/Tutor/DeletePayment" data-id="<?= $pay->Id ?>" data-confirm="এই এন্ট্রি মুছে ফেলবেন?"><i class="fas fa-trash-can"></i> Delete</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ======================= SETTINGS ======================= -->
                <section class="pf-sec <?= $tab === 'settings' ? 'active' : '' ?>" id="sec-settings">
                    <div class="pf-sec-h"><div><h2>Settings</h2><p>প্রোফাইল, পাসওয়ার্ড আর অ্যাকাউন্ট।</p></div></div>
                    <div class="set-grid">
                        <div class="pf-card set-card">
                            <h4><i class="fas fa-user-pen"></i> Profile</h4><p>অন্যরা আপনার প্রোফাইলে যা দেখে।</p>
                            <div class="set-links">
                                <button type="button" data-intro="all"><i class="fas fa-quote-left"></i> Headline, Bio, শহর, বিষয় ও ক্লাস <i class="fas fa-chevron-right chev"></i></button>
                                <a href="?tab=profile#ab-basic" data-ab-go="basic" data-ab-open="1"><i class="fas fa-id-card"></i> নাম, ইমেইল, মোবাইল <i class="fas fa-chevron-right chev"></i></a>
                                <a href="?tab=profile#ab-education" data-ab-go="education" data-ab-open="1"><i class="fas fa-graduation-cap"></i> Education <i class="fas fa-chevron-right chev"></i></a>
                                <a href="?tab=profile#ab-address" data-ab-go="address" data-ab-open="1"><i class="fas fa-location-dot"></i> Address <i class="fas fa-chevron-right chev"></i></a>
                                <a href="?tab=profile#ab-links" data-ab-go="links" data-ab-open="1"><i class="fas fa-link"></i> Important Links <i class="fas fa-chevron-right chev"></i></a>
                                <a href="/Teacher/TeacherDashboard"><i class="fas fa-gauge"></i> Teacher Dashboard <i class="fas fa-chevron-right chev"></i></a>
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
<!-- ===== Intro (Headline / Bio / শহর / বিষয় / ক্লাস / উক্তি) ===== -->
<div class="pf-ov" id="introOv" aria-hidden="true">
    <form class="pf-modal" id="introForm" autocomplete="off">
        <div class="pf-modal-h"><h3>Edit intro</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <div class="pf-f"><label for="inHeadline">Headline <small>— নামের নিচে দেখাবে</small></label><input type="text" id="inHeadline" maxlength="60" value="<?= e($tIntro->Headline) ?>" placeholder="যেমন: Mathematics Tutor" /></div>
            <div class="pf-f"><label for="inBio">Bio</label>
                <textarea id="inBio" maxlength="160" rows="2" placeholder="যেমন: Helping students understand, not just memorize."><?= e($intro->Bio) ?></textarea><div class="cnt"><span id="inBioN"><?= mb_strlen($intro->Bio) ?></span>/160</div></div>
            <div class="pf-f"><label for="inCity">শহর / জেলা</label><input type="text" id="inCity" maxlength="80" value="<?= e($intro->City) ?>" placeholder="যেমন: Dhaka" list="inCityList" />
                <datalist id="inCityList"><?php foreach (['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Gazipur', 'Narayanganj', 'Bogura', "Cox's Bazar"] as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist></div>
            <div class="pf-f"><label>যে বিষয়গুলো পড়ান <small>— Enter বা কমা দিয়ে আলাদা করুন</small></label>
                <div class="sj-box" id="sjBox" data-max="12"><input type="text" id="sjIn" maxlength="30" placeholder="Mathematics, Physics..." list="pmSubjList" /></div></div>
            <div class="pf-f"><label>ক্লাস / লেভেল</label>
                <div class="pf-quick" id="lvQuick" style="margin-bottom:8px;"><?php foreach (['Class 1-5', 'Class 6-8', 'Class 9-10', 'SSC', 'HSC', 'Admission', 'O Level', 'A Level', 'University', 'Bangla Medium', 'English Medium'] as $q): ?><button type="button" data-lv="<?= e($q) ?>"><?= e($q) ?></button><?php endforeach; ?></div>
                <div class="sj-box" id="lvBox" data-max="10"><input type="text" id="lvIn" maxlength="30" placeholder="SSC, HSC, Class 9-10..." /></div></div>
            <div class="pf-2">
                <div class="pf-f"><label for="inMode">কীভাবে পড়ান</label>
                    <select id="inMode"><option value="">— বাছাই করুন —</option>
                        <?php foreach (['Online' => 'Online', 'Home' => 'Home Visit (বাসায় গিয়ে)', 'Both' => 'Online + Home Visit'] as $k => $l): ?><option value="<?= $k ?>"<?= $prefs->TeachMode === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
                    </select></div>
                <div class="pf-f"><label>মাসিক বেতন (৳) <small>— Find Tutor পেজে দেখাবে</small></label>
                    <div style="display:flex;gap:8px;"><input type="number" id="inFeeMin" min="0" max="1000000" placeholder="Min" value="<?= $prefs->FeeMin ?? '' ?>" style="width:100%;border:1.5px solid var(--pf-line);border-radius:10px;padding:10px 12px;font-size:15px;" />
                    <input type="number" id="inFeeMax" min="0" max="1000000" placeholder="Max" value="<?= $prefs->FeeMax ?? '' ?>" style="width:100%;border:1.5px solid var(--pf-line);border-radius:10px;padding:10px 12px;font-size:15px;" /></div></div>
            </div>
            <div class="pf-f"><label for="inQuote">প্রিয় উক্তি <small>(ঐচ্ছিক)</small></label><input type="text" id="inQuote" maxlength="160" value="<?= e($intro->Quote) ?>" placeholder="Education is the most powerful weapon..." /></div>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary" id="introSave">Save</button></div>
    </form>
</div>

<!-- ===== Student ===== -->
<div class="pf-ov" id="stuOv" aria-hidden="true">
    <form class="pf-modal" data-tp-form="/Tutor/SaveStudent" data-tab="students" autocomplete="off">
        <div class="pf-modal-h"><h3 data-title-new="Add student" data-title-edit="Edit student">Add student</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <input type="hidden" name="id" />
            <div class="pf-f"><label>নাম <b style="color:#e74c3c">*</b></label><input type="text" name="name" maxlength="80" required placeholder="স্টুডেন্টের নাম" /></div>
            <div class="pf-2">
                <div class="pf-f"><label>ক্লাস</label><input type="text" name="classLevel" maxlength="60" placeholder="যেমন: Class 9" /></div>
                <div class="pf-f"><label>বিষয়</label><input type="text" name="subject" maxlength="80" placeholder="যেমন: Math" list="pmSubjList" /></div>
            </div>
            <div class="pf-2">
                <div class="pf-f"><label>মোবাইল <small>(WhatsApp মেসেজের জন্য)</small></label><input type="text" name="phone" maxlength="20" inputmode="tel" placeholder="01XXXXXXXXX" /></div>
                <div class="pf-f"><label>মাসিক বেতন (৳)</label><input type="number" name="monthlyFee" min="0" max="1000000" placeholder="যেমন: 3000" /></div>
            </div>
            <div class="pf-2">
                <div class="pf-f"><label>অবস্থা</label><select name="status"><option value="Active">Active (পড়ছে)</option><option value="Inactive">Inactive (বন্ধ)</option></select></div>
                <div class="pf-f"><label>নোট</label><input type="text" name="note" maxlength="200" placeholder="ঐচ্ছিক" /></div>
            </div>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary">Save</button></div>
    </form>
</div>

<!-- ===== Class ===== -->
<div class="pf-ov" id="clsOv" aria-hidden="true">
    <form class="pf-modal" data-tp-form="/Tutor/SaveClass" data-tab="schedule" autocomplete="off">
        <div class="pf-modal-h"><h3 data-title-new="Add class" data-title-edit="Edit class">Add class</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <input type="hidden" name="id" />
            <div class="pf-f"><label>ক্লাসের নাম <b style="color:#e74c3c">*</b></label><input type="text" name="title" maxlength="100" required placeholder="যেমন: Class 9 - Mathematics" /></div>
            <div class="pf-f"><label>কবে?</label>
                <div class="pf-seg" id="clsMode"><button type="button" class="active" data-m="weekly">প্রতি সপ্তাহে</button><button type="button" data-m="once">নির্দিষ্ট তারিখে</button></div></div>
            <div class="pf-f" id="clsDays"><div class="dy-pick">
                <?php foreach ([6, 0, 1, 2, 3, 4, 5] as $d): ?><label><input type="checkbox" name="days[]" value="<?= $d ?>" /><span><?= $days[$d] ?></span></label><?php endforeach; ?>
            </div></div>
            <div class="pf-f" id="clsDate" hidden><input type="date" name="classDate" min="<?= date('Y-m-d') ?>" /></div>
            <div class="pf-2">
                <div class="pf-f"><label>শুরু <b style="color:#e74c3c">*</b></label><input type="time" name="startTime" required value="17:00" /></div>
                <div class="pf-f"><label>শেষ <b style="color:#e74c3c">*</b></label><input type="time" name="endTime" required value="18:00" /></div>
            </div>
            <div class="pf-f"><label>অনলাইন ক্লাস লিংক <small>(Google Meet / Zoom — ঐচ্ছিক)</small></label><input type="url" name="meetLink" maxlength="300" placeholder="https://meet.google.com/..." /></div>
            <div class="pf-f"><label>জায়গা <small>(ঐচ্ছিক)</small></label><input type="text" name="location" maxlength="100" placeholder="যেমন: স্টুডেন্টের বাসা, Mirpur" /></div>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn danger-btn" data-tp-modal-del="/Tutor/DeleteClass" hidden style="margin-right:auto;color:#DC2626;"><i class="fas fa-trash-can"></i> Delete</button>
            <button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary">Save</button></div>
    </form>
</div>

<!-- ===== Payment ===== -->
<div class="pf-ov" id="payOv" aria-hidden="true">
    <form class="pf-modal" data-tp-form="/Tutor/SavePayment" data-tab="earnings" autocomplete="off">
        <div class="pf-modal-h"><h3 data-title-new="Add payment" data-title-edit="Edit payment">Add payment</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <input type="hidden" name="id" />
            <div class="pf-2">
                <div class="pf-f"><label>টাকা (৳) <b style="color:#e74c3c">*</b></label><input type="number" name="amount" min="1" required placeholder="যেমন: 3000" /></div>
                <div class="pf-f"><label>কোন মাসের</label><input type="month" name="month" value="<?= date('Y-m') ?>" required /></div>
            </div>
            <div class="pf-f"><label>স্টুডেন্ট <small>(ঐচ্ছিক)</small></label><select name="studentId"><option value="">— বাছাই করুন —</option>
                <?php foreach ($students as $s): ?><option value="<?= $s->Id ?>"><?= e($s->Name) ?><?= $s->ClassLevel !== '' ? ' (' . e($s->ClassLevel) . ')' : '' ?></option><?php endforeach; ?></select></div>
            <div class="pf-2">
                <div class="pf-f"><label>কবে পেলেন</label><input type="date" name="paidOn" value="<?= date('Y-m-d') ?>" /></div>
                <div class="pf-f"><label>নোট</label><input type="text" name="note" maxlength="150" placeholder="ঐচ্ছিক" /></div>
            </div>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary">Save</button></div>
    </form>
</div>

<!-- ===== Certificate ===== -->
<div class="pf-ov" id="certOv" aria-hidden="true">
    <form class="pf-modal" data-tp-form="/Tutor/SaveCertificate" data-tab="certificates" autocomplete="off">
        <div class="pf-modal-h"><h3 data-title-new="Add certificate">Add certificate</h3><button type="button" class="pf-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="pf-modal-b">
            <div class="pf-f"><label>সার্টিফিকেটের নাম <b style="color:#e74c3c">*</b></label><input type="text" name="title" maxlength="120" required placeholder="যেমন: B.Sc in Mathematics" /></div>
            <div class="pf-2">
                <div class="pf-f"><label>প্রতিষ্ঠান</label><input type="text" name="issuer" maxlength="120" placeholder="যেমন: University of Dhaka" /></div>
                <div class="pf-f"><label>সাল</label><input type="number" name="year" min="1950" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') ?>" /></div>
            </div>
            <div class="pf-f"><label>ছবি বা PDF <small>(ঐচ্ছিক, সর্বোচ্চ 5 MB)</small></label><input type="file" name="file" accept="image/jpeg,image/png,image/webp,application/pdf" /></div>
        </div>
        <div class="pf-modal-f"><button type="button" class="pf-btn" data-close>Cancel</button><button type="submit" class="pf-btn primary">Save</button></div>
    </form>
</div>
<?php endif; ?>

<?php section_start('Scripts'); ?>
<?php partial('Profile/_ShowCoreJs', ['u' => $u, 'isOwner' => $isOwner, 'tab' => $tab, 'rel' => $rel, 'coverY' => $coverY]); ?>
<script>
(function () {
    var PF = window.PF; if (!PF) return;
    var $ = PF.$, $$ = PF.$$, postForm = PF.postForm, say = PF.say, hardReload = PF.hardReload, openOv = PF.openOv, closeOv = PF.closeOv, closeMenus = PF.closeMenus;
    var TEACHER = <?= (int)$u->Id ?>;
    function reloadTab(tab) {
        var u = new URL(location.href); u.searchParams.set('tab', tab); u.searchParams.set('_r', Date.now().toString(36)); location.replace(u.pathname + u.search);
    }

    // ভিজিটরের "Study Materials" কার্ড → ম্যাটেরিয়াল ট্যাব
    document.addEventListener('click', function (e) {
        var g = e.target.closest('[data-feed-go]'); if (!g) return;
        e.preventDefault(); PF.showTab('overview');
        var b = $('#feedTabs button[data-feed="' + g.getAttribute('data-feed-go') + '"]'); if (b) { b.click(); $('#feedTabs').scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
    // সার্টিফিকেটের ছবি বড় করে
    document.addEventListener('click', function (e) {
        var z = e.target.closest('[data-zoom]'); if (!z) return;
        e.preventDefault(); PF.lightbox(z.getAttribute('data-zoom'));
    });

    // ===== রিভিউ (স্টুডেন্ট) =====
    var rv = $('#rvForm');
    if (rv) rv.addEventListener('submit', async function (e) {
        e.preventDefault();
        var r = $('input[name="rvRating"]:checked', rv);
        if (!r) { say('১ থেকে ৫ স্টার বাছাই করুন।', true); return; }
        var btn = $('#rvSave'); btn.disabled = true;
        try {
            var d = await postForm('/Tutor/SaveReview', { teacherId: TEACHER, rating: r.value, comment: $('#rvComment').value });
            say(d.message, !d.success);
            if (d.success) setTimeout(function () { reloadTab('reviews'); }, 500);
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; }
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-rv-del]'); if (!b) return;
        if (!confirm('রিভিউটি মুছে ফেলবেন?')) return;
        var d = await postForm('/Tutor/DeleteReview', { id: b.getAttribute('data-rv-del') });
        say(d.message, !d.success);
        if (d.success) setTimeout(function () { reloadTab('reviews'); }, 400);
    });

    if (!PF.IS_OWNER) return;

    // ===== মেনু (⋯) =====
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-tp="menu"]'); if (!b) return;
        var m = b.nextElementSibling; closeMenus(m); m.classList.toggle('open');
    });

    // ===== স্টুডেন্ট ফিল্টার =====
    var sf = $('#stuFilter');
    if (sf) sf.addEventListener('click', function (e) {
        var b = e.target.closest('button[data-f]'); if (!b) return;
        $$('button', sf).forEach(function (x) { x.classList.toggle('active', x === b); });
        var f = b.getAttribute('data-f');
        $$('#stuList .tp-row').forEach(function (r) { r.style.display = f === 'all' || r.getAttribute('data-status') === f ? '' : 'none'; });
    });

    // ===== মডাল ফর্ম (স্টুডেন্ট / ক্লাস / আয় / সার্টিফিকেট) =====
    function setClsMode(m) {
        $$('#clsMode button').forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-m') === m); });
        $('#clsDays').hidden = m !== 'weekly'; $('#clsDate').hidden = m === 'weekly';
        if (m === 'weekly') $('#clsOv [name="classDate"]').value = '';
        else $$('#clsOv [name="days[]"]').forEach(function (c) { c.checked = false; });
    }
    $('#clsMode').addEventListener('click', function (e) { var b = e.target.closest('button[data-m]'); if (b) setClsMode(b.getAttribute('data-m')); });

    function fillForm(ov, data) {
        var f = $('form', ov); f.reset();
        var h = $('.pf-modal-h h3', ov);
        h.textContent = data ? (h.getAttribute('data-title-edit') || h.textContent) : h.getAttribute('data-title-new');
        $$('[name="id"]', f).forEach(function (i) { i.value = data ? data.id : ''; });
        if (data) Object.keys(data).forEach(function (k) {
            if (k === 'id') return;
            if (k === 'days') { $$('[name="days[]"]', f).forEach(function (c) { c.checked = data.days.indexOf(+c.value) > -1; }); return; }
            var el = f.elements[k]; if (el && el.type !== 'file') el.value = data[k] === null || data[k] === undefined ? '' : data[k];
        });
        if (ov.id === 'clsOv') {
            setClsMode(data && data.classDate ? 'once' : 'weekly');
            var del = $('[data-tp-modal-del]', ov); del.hidden = !data; del.setAttribute('data-id', data ? data.id : '');
        }
        openOv(ov);
        setTimeout(function () { var first = $('input:not([type=hidden]), select', f); if (first) first.focus(); }, 40);
    }
    document.addEventListener('click', function (e) {
        var o = e.target.closest('[data-open]');
        if (o) { fillForm(document.getElementById(o.getAttribute('data-open')), null); return; }
        var ed = e.target.closest('[data-tp-edit]');
        if (ed) { closeMenus(); fillForm(document.getElementById(ed.getAttribute('data-tp-edit')), JSON.parse(ed.getAttribute('data-json'))); return; }
        var pay = e.target.closest('[data-tp-pay]');
        if (pay) {
            closeMenus(); fillForm($('#payOv'), null);
            $('#payOv [name="studentId"]').value = pay.getAttribute('data-tp-pay');
            if (+pay.getAttribute('data-fee')) $('#payOv [name="amount"]').value = pay.getAttribute('data-fee');
        }
    });
    document.addEventListener('submit', async function (e) {
        var f = e.target.closest('[data-tp-form]'); if (!f) return;
        e.preventDefault();
        var btn = $('button[type=submit]', f), old = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        var fd = new FormData(f);
        fd.append('__RequestVerificationToken', $('input[name="__RequestVerificationToken"]').value);
        try {
            var d = await (await fetch(f.getAttribute('data-tp-form'), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            say(d.message, !d.success);
            if (d.success) { closeOv(f.closest('.pf-ov')); setTimeout(function () { reloadTab(f.getAttribute('data-tab')); }, 400); }
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; btn.innerHTML = old; }
    });
    document.addEventListener('click', async function (e) {
        var b = e.target.closest('[data-tp-del], [data-tp-modal-del]'); if (!b) return;
        closeMenus();
        if (!confirm(b.getAttribute('data-confirm') || 'মুছে ফেলবেন?')) return;
        var d = await postForm(b.getAttribute('data-tp-del') || b.getAttribute('data-tp-modal-del'), { id: b.getAttribute('data-id') });
        say(d.message, !d.success);
        if (d.success) { var sec = b.closest('.pf-sec'); var ov = b.closest('.pf-ov'); setTimeout(function () { reloadTab(ov ? 'schedule' : sec.id.replace('sec-', '')); }, 400); }
    });

    // ===== Intro (Headline, Bio, বিষয়, ক্লাস) =====
    function chipBox(box, input, initial) {
        var list = initial.slice(), max = +box.getAttribute('data-max') || 10;
        function paint() {
            $$('span', box).forEach(function (s) { s.remove(); });
            list.forEach(function (s, i) {
                var el = document.createElement('span'); el.textContent = s;
                var x = document.createElement('button'); x.type = 'button'; x.innerHTML = '<i class="fas fa-xmark"></i>'; x.setAttribute('aria-label', 'Remove');
                x.addEventListener('click', function (ev) { ev.stopPropagation(); list.splice(i, 1); paint(); });
                el.appendChild(x); box.insertBefore(el, input);
            });
        }
        function add(v) {
            v = v.replace(/,/g, ' ').trim(); if (!v) return;
            if (list.length >= max) { say('সর্বোচ্চ ' + max + 'টা।', true); return; }
            if (!list.some(function (s) { return s.toLowerCase() === v.toLowerCase(); })) list.push(v.slice(0, 30));
            input.value = ''; paint();
        }
        box.addEventListener('click', function () { input.focus(); });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); add(input.value); }
            else if (e.key === 'Backspace' && !input.value && list.length) { list.pop(); paint(); }
        });
        input.addEventListener('change', function () { if (input.value.trim()) add(input.value); });
        paint();
        return { list: function () { if (input.value.trim()) add(input.value); return list; }, add: add };
    }
    var subj = chipBox($('#sjBox'), $('#sjIn'), <?= js($intro->Subjects) ?>);
    var lv = chipBox($('#lvBox'), $('#lvIn'), <?= js($tIntro->Levels) ?>);
    $('#lvQuick').addEventListener('click', function (e) { var b = e.target.closest('[data-lv]'); if (b) lv.add(b.getAttribute('data-lv')); });
    $('#inBio').addEventListener('input', function () { $('#inBioN').textContent = this.value.length; });
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-intro]'); if (!b) return;
        openOv($('#introOv'));
        var w = b.getAttribute('data-intro');
        setTimeout(function () { (w === 'subjects' ? $('#sjIn') : w === 'bio' ? $('#inBio') : $('#inHeadline')).focus(); }, 40);
    });
    $('#introForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn = $('#introSave'); btn.disabled = true;
        try {
            var d = await postForm('/Tutor/SaveIntro', { headline: $('#inHeadline').value, bio: $('#inBio').value, city: $('#inCity').value,
                subjects: subj.list().join(','), levels: lv.list().join(','), quote: $('#inQuote').value,
                teachMode: $('#inMode').value, feeMin: $('#inFeeMin').value, feeMax: $('#inFeeMax').value });
            say(d.message, !d.success);
            if (d.success) { closeOv($('#introOv')); setTimeout(function () { hardReload(); }, 400); }
        } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।', true); }
        finally { btn.disabled = false; }
    });
})();
</script>
<?php section_end(); ?>
