<?php
/** ফেসবুকের মতো পাবলিক প্রোফাইল: Posts | About | Friends */
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

$me = obj(['Id' => (int)current_user_id(), 'Name' => session_get('UserFullName') ?? session_get('UserName') ?? 'User', 'Image' => session_get('ProfileImageLink')]);

$val = fn($v) => is_blank($v) ? '' : trim((string)$v);
$link = function ($v) { $v = trim((string)$v); return preg_match('#^https?://#i', $v) ? $v : ''; };

// ===== About সেকশনগুলো (প্রোফাইল এডিট পেজের মতো) =====
// প্রতি সারি: [আইকন, লেবেল, মান, শুধু-ফ্রেন্ড?, লিংক?]
$about = [
    'basic' => ['Basic Information', 'fa-id-card', $val($u->FullName) !== '', [
        ['fa-user', 'Full Name', $val($u->FullName), false],
        ['fa-at', 'Username', '@' . $u->UserName, false],
        ['fa-user-tag', 'Account Type', $isTeacher ? 'Teacher' : 'Student', false],
        ['fa-envelope', 'Email', $val($u->Email), true],
        ['fa-phone', 'Mobile Number', $val($u->MobileNumber), true],
    ]],
    'additional' => ['Additional Information', 'fa-circle-info', $val($u->Gender) !== '', [
        ['fa-venus-mars', 'Gender', $val($u->Gender), false],
        ['fa-cake-candles', 'Age Range', $val($u->AgeRange), false],
        ['fa-laptop', 'Primary Device', $val($u->PrimaryDeviceType), false],
        ['fa-briefcase', 'Years of Experience', $val($u->YearsOfExperience), false],
        ['fa-city', 'Area Type', $val($u->AreaType), false],
    ]],
    'address' => ['Address', 'fa-location-dot', $val($u->Country) !== '', [
        ['fa-earth-asia', 'Country', $val($u->Country), false],
        ['fa-house', 'Present Address', $val($u->StreetAddress), true],
        ['fa-map-location-dot', 'Permanent Address', $val($u->PermanentAddress), true],
    ]],
    'education' => ['Education', 'fa-graduation-cap', $val($u->EducationLevel) !== '', [
        ['fa-graduation-cap', 'Education Level', $val($u->EducationLevel), false],
        ['fa-book-open-reader', 'Currently Studying', $val($u->CurrentStudyStatus), false],
        ['fa-certificate', 'Degree / Exam', $val($u->ExamDegreeTitle), false],
        ['fa-building-columns', 'Institution', $val($u->InstitutionName), false],
        ['fa-calendar-check', 'Passing Year', $val($u->PassingYear), false],
        ['fa-laptop-code', 'CSE Student', $u->IsCSEStudent === null ? '' : ($u->IsCSEStudent ? 'Yes' : 'No'), false],
    ]],
    'links' => ['Important Links', 'fa-link', $val($u->CvLink) !== '', [
        ['fa-file-lines', 'CV', $link($u->CvLink), false, true],
        ['fa-github', 'GitHub', $link($u->GithubProfile), false, true],
        ['fa-globe', 'Portfolio', $link($u->PortfolioLink), false, true],
        ['fa-linkedin', 'LinkedIn', $link($u->LinkedInProfile), false, true],
    ]],
];
$brands = ['fa-github', 'fa-linkedin'];

// Intro (Posts ট্যাবের বাম পাশে)
$intro = [];
if ($isTeacher) $intro[] = ['fa-chalkboard-teacher', 'Teacher' . ($ti && $val($ti->SubjectExpertise) ? ' · ' . $ti->SubjectExpertise : '')];
if ($val($u->InstitutionName)) $intro[] = ['fa-building-columns', ($u->CurrentStudyStatus === 'Yes' ? 'Studies at ' : 'Studied at ') . $u->InstitutionName];
if ($val($u->EducationLevel)) $intro[] = ['fa-graduation-cap', $u->EducationLevel . ($val($u->ExamDegreeTitle) ? ' — ' . $u->ExamDegreeTitle : '')];
if ($val($u->Country)) $intro[] = ['fa-location-dot', 'From ' . $u->Country];
if ($val($u->Gender)) $intro[] = ['fa-venus-mars', $u->Gender];
$intro[] = ['fa-clock', 'Joined ' . fmt_date($u->CreatedAt, 'F Y', '—')];

$tabs = ['posts' => 'Posts', 'about' => 'About', 'friends' => 'Friends'];
?>
<?= csrf_field() ?>
<?php partial('Friends/_Assets'); ?>
<?php partial('Post/_Assets', ['me' => $me]); ?>

<style>
    .ps-page { background: #F0F4F8; padding-bottom: 60px; min-height: 80vh; }
    /* ===== Header ===== */
    .ps-header { background: #fff; box-shadow: 0 1px 2px rgba(8,34,74,.08); }
    .ps-header-in { max-width: 1050px; margin: 0 auto; padding: 0 16px; }
    .ps-cover {
        height: 300px; border-radius: 0 0 14px 14px; position: relative; overflow: hidden;
        background: linear-gradient(120deg, #08224A 0%, #134856 50%, #0F8FB3 100%);
    }
    .ps-cover::before { content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 82% 22%, rgba(2,184,245,.5) 0%, transparent 42%), radial-gradient(circle at 8% 115%, rgba(255,179,0,.3) 0%, transparent 38%); }
    .ps-cover::after { content: ''; position: absolute; inset: 0; opacity: .12;
        background-image: radial-gradient(#fff 1px, transparent 1.2px); background-size: 22px 22px; }
    .ps-top { display: flex; align-items: flex-start; gap: 18px; padding: 0 16px 16px; margin-top: -84px; position: relative; z-index: 2; flex-wrap: wrap; }
    .ps-avatar {
        width: 168px; height: 168px; border-radius: 50%; border: 5px solid #fff; flex-shrink: 0; position: relative; overflow: hidden;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-size: 54px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 18px rgba(8,34,74,.2);
    }
    .ps-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ps-avatar-edit { position: absolute; right: 8px; bottom: 8px; z-index: 3; width: 38px; height: 38px; border-radius: 50%; background: #E4E6EB;
        color: #1C1E21; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 3px solid #fff; text-decoration: none; }
    .ps-avatar-wrap { position: relative; }
    .ps-id { flex: 1; min-width: 240px; padding-top: 96px; }
    .ps-name { font-family: var(--font-heading); font-size: 32px; font-weight: 700; color: #1C1E21; margin: 0; line-height: 1.15; word-break: break-word; }
    .ps-name .fa-circle-check { color: #02B8F5; font-size: 22px; vertical-align: 3px; }
    .ps-meta { color: #65676B; font-size: 15px; font-weight: 600; margin-top: 6px; }
    .ps-meta a { color: inherit; text-decoration: none; }
    .ps-meta a:hover { text-decoration: underline; }
    .ps-faces { display: flex; margin-top: 8px; }
    .ps-faces a { width: 32px; height: 32px; border-radius: 50%; border: 2px solid #fff; margin-left: -8px; overflow: hidden; position: relative;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .ps-faces a:first-child { margin-left: 0; }
    .ps-faces img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ps-actions { display: flex; gap: 8px; padding-bottom: 10px; flex-wrap: wrap; align-self: flex-end; }
    .ps-actions .fr-actions { margin: 0; width: auto; min-width: 230px; }
    .ps-actions .fr-btn, .ps-btn { height: 38px; font-size: 14.5px; }
    .ps-btn { display: inline-flex; align-items: center; gap: 7px; padding: 0 14px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; text-decoration: none; }
    .ps-btn.primary { background: #02B8F5; color: #fff; }
    .ps-btn.primary:hover { background: #0099D6; color: #fff; }
    .ps-btn.gray { background: #E4E6EB; color: #1C1E21; }
    .ps-btn.gray:hover { background: #D8DADF; color: #1C1E21; }
    .ps-pill-warn { display: inline-flex; gap: 6px; align-items: center; background: #FDECEA; color: #B42318; font-size: 12.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; margin-left: 8px; vertical-align: middle; }
    .ps-tabs { display: flex; gap: 4px; border-top: 1px solid #E4E6EB; margin: 0 16px; padding: 4px 0; overflow-x: auto; scrollbar-width: none; }
    .ps-tabs::-webkit-scrollbar { display: none; }
    .ps-tab { position: relative; padding: 0 16px; height: 50px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 15px; color: #65676B; text-decoration: none; border-radius: 8px; white-space: nowrap; }
    .ps-tab:hover { background: #F0F2F5; color: #65676B; }
    .ps-tab.active { color: #0099D6; }
    .ps-tab.active::after { content: ''; position: absolute; left: 0; right: 0; bottom: -4px; height: 3px; background: #02B8F5; border-radius: 3px 3px 0 0; }
    .ps-tab small { font-size: 13px; color: inherit; opacity: .8; }

    /* ===== Body ===== */
    .ps-body { max-width: 1050px; margin: 16px auto 0; padding: 0 16px; }
    .ps-panel { display: none; }
    .ps-panel.active { display: block; }
    .ps-grid { display: grid; grid-template-columns: 2fr 3fr; gap: 16px; align-items: start; }
    .ps-side { position: sticky; top: 96px; display: flex; flex-direction: column; gap: 16px; }
    .ps-card { background: #fff; border-radius: 14px; border: 1px solid #E3EEF5; padding: 16px; box-shadow: 0 1px 3px rgba(8,34,74,.06); }
    .ps-card h4 { font-size: 19px; font-weight: 700; color: #1C1E21; margin: 0 0 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .ps-card h4 a { font-size: 14.5px; font-weight: 500; color: #0099D6; text-decoration: none; }
    .ps-card h4 a:hover { text-decoration: underline; }
    .ps-intro { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
    .ps-intro li { display: flex; gap: 12px; align-items: flex-start; font-size: 15px; color: #1C1E21; line-height: 1.35; }
    .ps-intro li i { width: 20px; text-align: center; color: #8C939D; font-size: 17px; margin-top: 1px; }
    .ps-fgrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .ps-fgrid a { text-decoration: none; color: #1C1E21; min-width: 0; }
    .ps-fgrid .ph { aspect-ratio: 1; border-radius: 10px; overflow: hidden; position: relative; background: linear-gradient(135deg, #08224A, #134856);
        color: #fff; font-weight: 700; font-size: 22px; display: flex; align-items: center; justify-content: center; }
    .ps-fgrid .ph img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ps-fgrid span { display: block; font-size: 13px; font-weight: 600; margin-top: 5px; line-height: 1.25; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .ps-feed-head { display: flex; align-items: center; justify-content: space-between; background: #fff; border-radius: 14px; border: 1px solid #E3EEF5;
        padding: 12px 16px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(8,34,74,.06); }
    .ps-feed-head h4 { margin: 0; font-size: 19px; font-weight: 700; color: #1C1E21; }
    .ps-feed-head span { color: #65676B; font-size: 14px; }

    /* ===== About ===== */
    .ab-wrap { display: grid; grid-template-columns: 260px 1fr; gap: 16px; align-items: start; }
    .ab-nav { position: sticky; top: 96px; background: #fff; border-radius: 14px; border: 1px solid #E3EEF5; padding: 10px; }
    .ab-nav h4 { font-size: 19px; font-weight: 700; margin: 4px 8px 10px; color: #1C1E21; }
    .ab-nav a { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; color: #1C1E21; text-decoration: none; font-weight: 600; font-size: 14.5px; }
    .ab-nav a:hover { background: #F0F2F5; }
    .ab-nav a.active { background: #E7F6FD; color: #0099D6; }
    .ab-nav a i { width: 20px; text-align: center; color: #65676B; }
    .ab-nav a.active i { color: #0099D6; }
    .ab-nav .ab-st { margin-left: auto; font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; letter-spacing: .3px; }
    .ab-st.ok { background: #E7F8EF; color: #157347; }
    .ab-st.no { background: #FFF4E5; color: #B45309; }
    .ab-sections { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
    .ab-card { background: #fff; border-radius: 14px; border: 1px solid #E3EEF5; padding: 18px 20px; scroll-margin-top: 100px; box-shadow: 0 1px 3px rgba(8,34,74,.06); }
    .ab-card-h { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .ab-card-h h3 { font-size: 17px; font-weight: 700; color: #1C1E21; margin: 0; display: flex; align-items: center; gap: 10px; }
    .ab-card-h h3 i { color: #028EA5; }
    .ab-card-h .ab-st { font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; }
    .ab-edit { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 600; color: #0099D6; text-decoration: none;
        padding: 6px 12px; border-radius: 8px; background: #E7F6FD; border: none; cursor: pointer; }
    .ab-edit:hover { background: #D4EEFA; color: #0099D6; }
    .ab-rows { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 22px; }
    .ab-row { display: flex; gap: 12px; align-items: flex-start; min-width: 0; }
    .ab-row > i { width: 36px; height: 36px; border-radius: 10px; background: #F5FBFF; color: #028EA5; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 15px; }
    .ab-row small { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; color: #8C939D; }
    .ab-row span, .ab-row a { display: block; font-size: 15px; color: #1C1E21; font-weight: 600; word-break: break-word; }
    .ab-row a { color: #0099D6; text-decoration: none; }
    .ab-row a:hover { text-decoration: underline; }
    .ab-row .lock { color: #8C939D; font-weight: 500; font-size: 13.5px; }
    .ab-row .empty { color: #B0B6BE; font-weight: 500; font-style: italic; font-size: 14px; }
    .ab-none { color: #8C939D; font-size: 14px; margin: 0; }
    .ab-tutor { display: flex; gap: 14px; align-items: center; padding: 14px; border-radius: 12px; background: #F5FBFF; margin-bottom: 14px; }
    .ab-tutor i { font-size: 28px; }
    .ab-tutor strong { display: block; color: #08224A; font-size: 15.5px; }
    .ab-tutor p { margin: 2px 0 0; color: #64748B; font-size: 13.5px; }
    .pw-form { display: grid; gap: 12px; max-width: 420px; }
    .pw-field { position: relative; }
    .pw-field label { display: block; font-size: 13px; font-weight: 700; color: #08224A; margin-bottom: 5px; }
    .pw-field input { width: 100%; height: 44px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 44px 0 14px; font-size: 15px; outline: none; background: #fff; }
    .pw-field input:focus { border-color: #02B8F5; box-shadow: 0 0 0 3px rgba(2,184,245,.12); }
    .pw-eye { position: absolute; right: 6px; bottom: 4px; width: 36px; height: 36px; border: none; background: none; color: #8C939D; cursor: pointer; }
    .pw-msg { font-size: 13.5px; font-weight: 600; min-height: 18px; }
    .pw-msg.ok { color: #157347; } .pw-msg.err { color: #B42318; }

    @media (max-width: 900px) {
        .ps-grid { grid-template-columns: 1fr; }
        .ps-side { position: static; }
        .ab-wrap { grid-template-columns: 1fr; }
        .ab-nav { position: static; display: flex; gap: 6px; overflow-x: auto; padding: 8px; scrollbar-width: none; }
        .ab-nav h4 { display: none; }
        .ab-nav a { flex: 0 0 auto; padding: 8px 12px; background: #F0F2F5; }
        .ab-nav .ab-st { display: none; }
    }
    @media (max-width: 640px) {
        .ps-header-in { padding: 0; }
        .ps-cover { height: 170px; border-radius: 0; }
        .ps-top { flex-direction: column; align-items: center; text-align: center; margin-top: -78px; gap: 10px; padding: 0 12px 12px; }
        .ps-avatar { width: 146px; height: 146px; font-size: 46px; }
        .ps-id { min-width: 0; width: 100%; padding: 0; }
        .ps-actions { align-self: stretch; }
        .ps-name { font-size: 25px; }
        .ps-faces { justify-content: center; }
        .ps-actions { width: 100%; padding: 0; }
        .ps-actions > * { flex: 1 1 0; justify-content: center; }
        .ps-actions .fr-actions { min-width: 0; flex-direction: row; }
        .ps-actions .fr-actions > * { flex: 1 1 0; width: auto; }
        .ps-actions .fr-icon-only { flex: 0 0 42px !important; width: 42px; }
        .ps-actions .fr-icon-only span { display: none; }
        .ps-tabs { margin: 0 4px; }
        .ps-body { padding: 0 12px; margin-top: 10px; }
        .ps-card { border-radius: 0; margin: 0 -12px; border-left: 0; border-right: 0; box-shadow: none; }
        .ps-feed-head { border-radius: 0; margin: 0 -12px 10px; border-left: 0; border-right: 0; box-shadow: none; }
        .ab-rows { grid-template-columns: 1fr; }
        .ab-card { padding: 16px; }
    }
</style>

<div class="ps-page">
    <!-- ======================= HEADER ======================= -->
    <div class="ps-header">
        <div class="ps-header-in">
            <div class="ps-cover"></div>
            <div class="ps-top">
                <div class="ps-avatar-wrap">
                    <div class="ps-avatar">
                        <?= e(initials($name, 'U')) ?>
                        <?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($name) ?>" onerror="this.remove()" /><?php endif; ?>
                    </div>
                    <?php if ($isOwner): ?><a href="/Profile?section=basic" class="ps-avatar-edit" title="ছবি বদলান"><i class="fas fa-camera"></i></a><?php endif; ?>
                </div>
                <div class="ps-id">
                    <h1 class="ps-name"><?= e($name) ?><?php if ($isTeacher): ?> <i class="fas fa-circle-check" title="Verified Teacher"></i><?php endif; ?>
                        <?php if ($Model->IsSuspended): ?><span class="ps-pill-warn"><i class="fas fa-ban"></i> Suspended</span><?php endif; ?>
                    </h1>
                    <div class="ps-meta">
                        <a href="?tab=friends" data-ps-tab="friends"><?= (int)$Model->FriendCount ?> friend<?= $Model->FriendCount === 1 ? '' : 's' ?></a>
                        <?php if ($Model->MutualCount > 0): ?> · <?= (int)$Model->MutualCount ?> mutual<?php endif; ?>
                        · <?= $isTeacher ? 'Teacher' : 'Student' ?>
                    </div>
                    <?php if ($friends): ?>
                        <div class="ps-faces">
                            <?php foreach (array_slice($friends, 0, 7) as $f): ?>
                                <a href="/Profile/Show/<?= $f->Id ?>" title="<?= e($f->FullName) ?>"><?= e($f->Initials) ?><?php if ($f->Image): ?><img src="<?= e($f->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ps-actions">
                    <?php if ($isOwner): ?>
                        <button type="button" class="ps-btn primary" onclick="pcOpenComposer()"><i class="fas fa-plus"></i> Create post</button>
                        <a href="?tab=about" class="ps-btn gray" data-ps-tab="about"><i class="fas fa-pen"></i> Edit profile</a>
                    <?php elseif (!$Model->IsAdminViewer): ?>
                        <div class="fr-actions" data-uid="<?= (int)$u->Id ?>" data-rel="<?= e($rel) ?>"><?= friend_actions_html((int)$u->Id, $rel) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="ps-tabs">
                <?php foreach ($tabs as $k => $lbl): ?>
                    <a href="?tab=<?= $k ?>" class="ps-tab <?= $tab === $k ? 'active' : '' ?>" data-ps-tab="<?= $k ?>">
                        <?= $lbl ?><?php if ($k === 'friends'): ?> <small><?= (int)$Model->FriendCount ?></small><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <div class="ps-body">
        <!-- ======================= POSTS ======================= -->
        <section class="ps-panel <?= $tab === 'posts' ? 'active' : '' ?>" id="ps-posts">
            <div class="ps-grid">
                <aside class="ps-side">
                    <div class="ps-card">
                        <h4>Intro <?php if ($isOwner): ?><a href="?tab=about" data-ps-tab="about">Edit details</a><?php endif; ?></h4>
                        <ul class="ps-intro">
                            <?php foreach ($intro as [$ic, $tx]): ?><li><i class="fas <?= $ic ?>"></i><span><?= e($tx) ?></span></li><?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="ps-card">
                        <h4><span>Friends <small style="font-size:14px;color:#65676B;font-weight:500;display:block;"><?= (int)$Model->FriendCount ?> friend<?= $Model->FriendCount === 1 ? '' : 's' ?></small></span>
                            <?php if ($friends): ?><a href="?tab=friends" data-ps-tab="friends">See all</a><?php endif; ?></h4>
                        <?php if (!$friends): ?>
                            <p class="ab-none"><?= $isOwner ? 'এখনো কোনো ফ্রেন্ড নেই। ' : 'কোনো ফ্রেন্ড নেই।' ?><?php if ($isOwner): ?><a href="/Friends?tab=find">মানুষ খুঁজুন</a><?php endif; ?></p>
                        <?php else: ?>
                            <div class="ps-fgrid">
                                <?php foreach (array_slice($friends, 0, 9) as $f): ?>
                                    <a href="/Profile/Show/<?= $f->Id ?>"><div class="ph"><?= e($f->Initials) ?><?php if ($f->Image): ?><img src="<?= e($f->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></div><span><?= e($f->FullName) ?></span></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>

                <div class="ps-main">
                    <?php if ($isOwner): ?>
                        <div class="pc-create">
                            <div class="pc-create-top">
                                <span class="pc-avatar me-avatar"></span>
                                <button type="button" class="pc-create-input" onclick="pcOpenComposer()">আপনার মনে কী আছে, <?= e($firstName) ?>?</button>
                                <button type="button" class="pc-create-photo" onclick="pcOpenComposer(); setTimeout(function(){ document.getElementById('pmFile').click(); }, 60);" title="Photo"><i class="fas fa-images"></i></button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="ps-feed-head">
                        <h4>Posts</h4>
                        <span id="psPostCount"><?= (int)$Model->PostCount ?> post<?= $Model->PostCount === 1 ? '' : 's' ?></span>
                    </div>

                    <div id="psFeed">
                        <?php foreach ($Model->Posts as $p) partial('Post/_Post', ['post' => $p, 'viewerId' => $me->Id, 'viewerIsAdmin' => $Model->IsAdminViewer]); ?>
                    </div>
                    <div class="pc-empty" id="psEmpty" <?= $Model->Posts ? 'hidden' : '' ?>>
                        <i class="far fa-newspaper"></i>
                        <h5><?= $isOwner ? 'এখনো কোনো পোস্ট নেই' : 'দেখানোর মতো কোনো পোস্ট নেই' ?></h5>
                        <p style="margin:0;"><?= $isOwner ? 'উপরের বক্সে লিখে প্রথম পোস্টটি করুন।' : ($rel === 'friends' ? e($firstName) . ' এখনো কিছু পোস্ট করেননি।' : 'ফ্রেন্ড হলে শুধু-ফ্রেন্ডদের পোস্টও দেখতে পাবেন।') ?></p>
                    </div>
                    <?php if ($Model->HasMore): ?>
                        <button type="button" class="pc-loadmore" id="psMore"><i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন</button>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ======================= ABOUT ======================= -->
        <section class="ps-panel <?= $tab === 'about' ? 'active' : '' ?>" id="ps-about">
            <div class="ab-wrap">
                <nav class="ab-nav">
                    <h4>About</h4>
                    <?php foreach ($about as $k => [$title, $icon, $done]): ?>
                        <a href="#ab-<?= $k ?>" data-ab="<?= $k ?>"><i class="fas <?= $icon ?>"></i> <?= $title ?>
                            <?php if ($isOwner): ?><span class="ab-st <?= $done ? 'ok' : 'no' ?>"><?= $done ? 'Complete' : 'Incomplete' ?></span><?php endif; ?></a>
                    <?php endforeach; ?>
                    <?php if ($isTeacher || $isOwner): ?>
                        <a href="#ab-tutor" data-ab="tutor"><i class="fas fa-chalkboard-teacher"></i> <?= $isTeacher ? 'Teaching' : 'Become a Tutor' ?>
                            <?php if ($isOwner): ?><span class="ab-st <?= $isTeacher ? 'ok' : 'no' ?>"><?= $isTeacher ? 'Teacher' : e($Model->TeacherStatus === 'None' ? 'Check' : $Model->TeacherStatus) ?></span><?php endif; ?></a>
                    <?php endif; ?>
                    <?php if ($isOwner): ?>
                        <a href="#ab-password" data-ab="password"><i class="fas fa-key"></i> Change Password</a>
                    <?php endif; ?>
                </nav>

                <div class="ab-sections">
                    <?php foreach ($about as $k => [$title, $icon, $done, $rows]):
                        $visible = array_filter($rows, fn($r) => $r[2] !== '' || $isOwner);
                    ?>
                        <div class="ab-card" id="ab-<?= $k ?>">
                            <div class="ab-card-h">
                                <h3><i class="fas <?= $icon ?>"></i> <?= $title ?></h3>
                                <?php if ($isOwner): ?>
                                    <span class="ab-st <?= $done ? 'ok' : 'no' ?>"><?= $done ? 'Complete' : 'Incomplete' ?></span>
                                    <a class="ab-edit" href="/Profile?section=<?= $k ?>"><i class="fas fa-pen"></i> <?= $done ? 'Edit' : 'Add' ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if (!$visible): ?>
                                <p class="ab-none">কোনো তথ্য দেওয়া নেই।</p>
                            <?php else: ?>
                                <div class="ab-rows">
                                    <?php foreach ($visible as $r):
                                        [$ic, $lb, $v] = $r; $priv = $r[3]; $isLink = $r[4] ?? false;
                                        $fa = in_array($ic, $brands, true) ? 'fab' : 'fas';
                                    ?>
                                        <div class="ab-row">
                                            <i class="<?= $fa ?> <?= $ic ?>"></i>
                                            <div>
                                                <small><?= e($lb) ?><?php if ($priv && $isOwner): ?> · <i class="fas fa-user-group" title="শুধু ফ্রেন্ডরা দেখতে পারে"></i><?php endif; ?></small>
                                                <?php if ($v === ''): ?>
                                                    <span class="empty">যোগ করা হয়নি</span>
                                                <?php elseif ($priv && !$canPrivate): ?>
                                                    <span class="lock"><i class="fas fa-lock"></i> শুধু ফ্রেন্ডরা দেখতে পারে</span>
                                                <?php elseif ($isLink): ?>
                                                    <a href="<?= e($v) ?>" target="_blank" rel="noopener nofollow"><?= e(preg_replace('#^https?://(www\.)?#i', '', rtrim($v, '/'))) ?></a>
                                                <?php elseif ($lb === 'Email'): ?>
                                                    <a href="mailto:<?= e($v) ?>"><?= e($v) ?></a>
                                                <?php elseif ($lb === 'Mobile Number'): ?>
                                                    <a href="tel:<?= e($v) ?>"><?= e($v) ?></a>
                                                <?php else: ?>
                                                    <span><?= e($v) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($isTeacher || $isOwner): ?>
                        <div class="ab-card" id="ab-tutor">
                            <div class="ab-card-h">
                                <h3><i class="fas fa-chalkboard-teacher"></i> <?= $isTeacher ? 'Teaching' : 'Become a Tutor' ?></h3>
                            </div>
                            <?php if ($isTeacher): ?>
                                <div class="ab-tutor"><i class="fas fa-circle-check" style="color:#2ecc71;"></i><div><strong>Verified Teacher</strong><p>অ্যাডমিন যাচাই করে টিচার হিসেবে অনুমোদন দিয়েছেন।</p></div></div>
                                <?php if ($ti): ?>
                                    <div class="ab-rows">
                                        <?php foreach ([['fa-book', 'Subjects', $ti->SubjectExpertise], ['fa-briefcase', 'Experience', $ti->Experience],
                                                        ['fa-person-chalkboard', 'Teaching Style', $ti->TeachingStyle], ['fa-calendar-days', 'Available Days', $ti->AvailableDays],
                                                        ['fa-clock', 'Preferred Time', $ti->PreferredTime], ['fa-money-bill-wave', 'Hourly Rate', is_blank($ti->HourlyRate) ? '' : '৳' . $ti->HourlyRate]] as [$ic, $lb, $v]):
                                            if (is_blank($v)) continue; ?>
                                            <div class="ab-row"><i class="fas <?= $ic ?>"></i><div><small><?= e($lb) ?></small><span><?= e($v) ?></span></div></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: /* নিজের প্রোফাইল, এখনো টিচার না */ ?>
                                <?php $st = $Model->TeacherStatus; ?>
                                <?php if ($st === 'Pending'): ?>
                                    <div class="ab-tutor"><i class="fas fa-hourglass-half" style="color:#f39c12;"></i><div><strong>আবেদন যাচাই হচ্ছে</strong><p>অ্যাডমিন Approve করলে আপনি টিচার হয়ে যাবেন এবং নোটিফিকেশন পাবেন।</p></div></div>
                                <?php elseif ($st === 'Rejected'): ?>
                                    <div class="ab-tutor"><i class="fas fa-circle-xmark" style="color:#e74c3c;"></i><div><strong>আগের আবেদন গ্রহণ হয়নি</strong><p>তথ্য ঠিক করে আবার আবেদন করতে পারেন।</p></div></div>
                                    <a href="/Teacher/RequestForm" class="ps-btn primary"><i class="fas fa-paper-plane"></i> আবার আবেদন করুন</a>
                                <?php else: ?>
                                    <div class="ab-tutor"><i class="fas fa-chalkboard-teacher" style="color:#028EA5;"></i><div><strong>টিচার হতে চান?</strong><p>আবেদন করুন — অ্যাডমিন Approve করলে টিউশনে Apply করতে পারবেন।</p></div></div>
                                    <a href="/Teacher/RequestForm" class="ps-btn primary"><i class="fas fa-paper-plane"></i> Become a Tutor</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isOwner): ?>
                        <div class="ab-card" id="ab-password">
                            <div class="ab-card-h"><h3><i class="fas fa-key"></i> Change Password</h3></div>
                            <form class="pw-form" id="pwForm" autocomplete="off">
                                <div class="pw-field"><label for="pwCur">বর্তমান পাসওয়ার্ড</label><input type="password" id="pwCur" autocomplete="current-password" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-field"><label for="pwNew">নতুন পাসওয়ার্ড (অন্তত ৬ অক্ষর)</label><input type="password" id="pwNew" autocomplete="new-password" minlength="6" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-field"><label for="pwConf">নতুন পাসওয়ার্ড আবার লিখুন</label><input type="password" id="pwConf" autocomplete="new-password" minlength="6" required /><button type="button" class="pw-eye" tabindex="-1" aria-label="Show"><i class="far fa-eye"></i></button></div>
                                <div class="pw-msg" id="pwMsg"></div>
                                <div><button type="submit" class="ps-btn primary" id="pwBtn"><i class="fas fa-check"></i> পাসওয়ার্ড বদলান</button>
                                    <a href="/Account/ForgotPassword" style="margin-left:12px;font-size:13.5px;">বর্তমান পাসওয়ার্ড মনে নেই?</a></div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ======================= FRIENDS ======================= -->
        <section class="ps-panel <?= $tab === 'friends' ? 'active' : '' ?>" id="ps-friends">
            <div class="ps-card" style="margin-bottom:16px;">
                <h4><?= $isOwner ? 'My Friends' : e($firstName) . "'s Friends" ?> <small style="font-size:14px;color:#65676B;font-weight:500;"><?= (int)$Model->FriendCount ?></small></h4>
                <?php if ($friends): ?>
                    <label class="fp-search" style="position:relative;display:block;max-width:420px;">
                        <i class="fas fa-magnifying-glass" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#8C939D;"></i>
                        <input type="search" id="psFriendFilter" placeholder="ফ্রেন্ডদের মধ্যে খুঁজুন..." autocomplete="off"
                               style="width:100%;height:42px;border:1.5px solid #DCECF5;border-radius:10px;padding:0 14px 0 40px;font-size:14.5px;outline:none;" />
                    </label>
                <?php endif; ?>
            </div>
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
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var OWNER = <?= (int)$u->Id ?>;

    // ===== ট্যাব =====
    function showTab(key) {
        document.querySelectorAll('.ps-panel').forEach(function (p) { p.classList.toggle('active', p.id === 'ps-' + key); });
        document.querySelectorAll('.ps-tab').forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-ps-tab') === key); });
        var u = new URL(location.href); u.searchParams.set('tab', key); u.hash = '';
        history.replaceState(null, '', u.pathname + u.search);
    }
    document.querySelectorAll('[data-ps-tab]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault(); showTab(a.getAttribute('data-ps-tab'));
            var top = document.querySelector('.ps-tabs').getBoundingClientRect().top + window.scrollY - 90;
            if (window.scrollY > top) window.scrollTo({ top: top, behavior: 'smooth' });
        });
    });

    // ===== About: বাম পাশের মেনু =====
    document.querySelectorAll('[data-ab]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('[data-ab]').forEach(function (x) { x.classList.toggle('active', x === a); });
            var el = document.getElementById('ab-' + a.getAttribute('data-ab'));
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    if (location.hash && location.hash.indexOf('#ab-') === 0) {
        showTab('about');
        setTimeout(function () { var el = document.querySelector(location.hash); if (el) el.scrollIntoView({ block: 'start' }); }, 50);
    }

    // ===== নতুন পোস্ট ফিডের উপরে =====
    var feed = document.getElementById('psFeed'), empty = document.getElementById('psEmpty'), countEl = document.getElementById('psPostCount');
    function bumpCount(d) {
        var n = Math.max(0, (parseInt(countEl.textContent, 10) || 0) + d);
        countEl.textContent = n + ' post' + (n === 1 ? '' : 's');
        empty.hidden = !!feed.querySelector('.pc');
    }
    document.addEventListener('pc:created', function (e) {
        showTab('posts');
        feed.prepend(e.detail.el);
        bumpCount(1);
        e.detail.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    document.addEventListener('pc:deleted', function () { bumpCount(-1); });

    // ===== আরো পোস্ট =====
    var more = document.getElementById('psMore');
    if (more) more.addEventListener('click', async function () {
        var last = feed.querySelector('.pc:last-child');
        more.disabled = true; more.innerHTML = '<i class="fas fa-spinner fa-spin"></i> লোড হচ্ছে...';
        try {
            var res = await fetch('/Post/Feed?userId=' + OWNER + '&before=' + (last ? last.getAttribute('data-id') : 0));
            var html = await res.text();
            var t = document.createElement('template'); t.innerHTML = html;
            var frag = t.content; window.pcInitCards(frag);
            feed.appendChild(frag);
            if (res.headers.get('X-Has-More') !== '1') more.remove();
            else { more.disabled = false; more.innerHTML = '<i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন'; }
        } catch (e) { more.disabled = false; more.innerHTML = 'আবার চেষ্টা করুন'; }
    });

    // ===== ফ্রেন্ড ফিল্টার =====
    var ff = document.getElementById('psFriendFilter');
    if (ff) ff.addEventListener('input', function () {
        var q = ff.value.trim().toLowerCase();
        document.querySelectorAll('#psFriendsGrid .fr-card').forEach(function (c) { c.style.display = !q || (c.getAttribute('data-name') || '').indexOf(q) !== -1 ? '' : 'none'; });
    });

    // ফ্রেন্ড হলে/আনফ্রেন্ড করলে প্রাইভেট তথ্য ও পোস্ট বদলায় — পেজ রিফ্রেশ
    document.addEventListener('fr:relation', function (e) {
        if (String(e.detail.uid) !== String(OWNER)) return;
        var was = <?= js($rel === 'friends') ?>;
        if ((e.detail.rel === 'friends') !== was) setTimeout(function () { location.reload(); }, 900);
    });

    // ===== পাসওয়ার্ড বদলানো =====
    var pw = document.getElementById('pwForm');
    if (pw) {
        pw.querySelectorAll('.pw-eye').forEach(function (b) {
            b.addEventListener('click', function () {
                var i = b.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password';
                b.innerHTML = i.type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
            });
        });
        pw.addEventListener('submit', async function (e) {
            e.preventDefault();
            var msg = document.getElementById('pwMsg'), btn = document.getElementById('pwBtn');
            var cur = document.getElementById('pwCur').value, nw = document.getElementById('pwNew').value, cf = document.getElementById('pwConf').value;
            msg.className = 'pw-msg';
            if (nw.length < 6) { msg.className = 'pw-msg err'; msg.textContent = 'নতুন পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।'; return; }
            if (nw !== cf) { msg.className = 'pw-msg err'; msg.textContent = 'নতুন পাসওয়ার্ড দুইবার একই হয়নি।'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('currentPassword', cur); fd.append('newPassword', nw); fd.append('confirmPassword', cf);
            fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
            try {
                var d = await (await fetch('/Profile/ChangePassword', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                msg.className = 'pw-msg ' + (d.success ? 'ok' : 'err'); msg.textContent = d.message;
                if (d.success) pw.reset();
            } catch (err) { msg.className = 'pw-msg err'; msg.textContent = 'ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'; }
            btn.disabled = false;
        });
    }
})();
</script>
<?php section_end(); ?>
