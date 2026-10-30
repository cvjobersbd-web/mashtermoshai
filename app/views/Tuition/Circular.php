<?php
/**
 * Tuition Circular — নতুন ডিজাইন
 * উপরে ব্যানার (অ্যাডমিন সেটিংস থেকে বদলানো যায়) · বামে ফিল্টার · মাঝে পোস্ট লেখা + Recent Tuition Posts · ডানে Need a Teacher / How It Works / নিরাপদ প্ল্যাটফর্ম
 */
$ViewData['Title'] = 'Tuition Circular';
$isLoggedIn = session_get('UserName') != null;
$userRole = session_get('UserRole');
$isTeacher = $userRole == 'Teacher';
$isAdmin = $userRole === 'Admin';
$userId = session_get('UserId');
$userFullName = session_get('UserFullName') ?? session_get('UserName') ?? 'User';
$userProfileImg = session_get('ProfileImageLink');

$feedPosts = $ViewBag->Feed ?? [];
$total = (int)($ViewBag->FeedTotal ?? 0);
$f = $ViewBag->Filters ?? ['location' => '', 'class' => '', 'subject' => '', 'medium' => [], 'type' => [], 'q' => ''];
$sort = $ViewBag->Sort ?? 'recent';
$page = (int)($ViewBag->Page ?? 1);
$fv = $ViewBag->FilterValues ?? ['location' => [], 'class' => [], 'subject' => []];
$isFiltered = (bool)($ViewBag->IsFiltered ?? false);
$banner = circular_banner();

$savedKeys = ($isLoggedIn && $userId && !$isAdmin) ? db()->SavedKeys((int)$userId) : [];
$canSave = $isLoggedIn && !$isAdmin;
$myApplied = [];
$myPhone = '';
if ($isLoggedIn && $isTeacher && $userId) {
    $myApplied = db()->GetCircularApplicationStatusesByTeacher((int)$userId);
    $myPhone = (string)(db()->GetUserById((int)$userId)?->MobileNumber ?? '');
}

// ড্রপডাউনের অপশন: সাধারণ তালিকা + পোস্টে থাকা মান
$merge = function (array $base, array $extra): array {
    $out = $base;
    foreach ($extra as $v) if (!in_array(mb_strtolower($v), array_map('mb_strtolower', $out), true)) $out[] = $v;
    return $out;
};
$locOptions = $merge(['Dhanmondi', 'Mirpur', 'Uttara', 'Mohammadpur', 'Gulshan', 'Banani', 'Bashundhara', 'Badda', 'Rampura', 'Motijheel', 'Farmgate',
    'Khilgaon', 'Jatrabari', 'Old Dhaka', 'Savar', 'Gazipur', 'Narayanganj', 'Chawkbazar', 'Agrabad', 'Nasirabad', 'Sylhet', 'Rajshahi', 'Khulna',
    'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Online'], $fv['location']);
$classOptions = $merge(['Play / Nursery', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10',
    'SSC', 'Class 11', 'Class 12', 'HSC', 'Admission', 'O Level', 'A Level', 'University'], $fv['class']);
$subjectOptions = $merge(['All Subjects', 'Bangla', 'English', 'Mathematics', 'Higher Math', 'General Math', 'Science', 'Physics', 'Chemistry', 'Biology',
    'ICT', 'Accounting', 'Finance', 'Economics', 'Business Studies', 'Religion', 'Arabic', 'Quran', 'Drawing'], $fv['subject']);
$mediumLabel = ['Bangla' => 'Bangla Medium', 'English' => 'English Medium', 'Both' => 'Bangla & English'];
$typeLabel = ['Home' => 'Home Tuition', 'Online' => 'Online Tuition', 'Both' => 'Home & Online'];

$qs = function (array $over = []) use ($f, $sort): string {
    $q = array_filter(['location' => $f['location'], 'class' => $f['class'], 'subject' => $f['subject'], 'medium' => $f['medium'], 'type' => $f['type'],
        'q' => $f['q'], 'sort' => $sort === 'recent' ? null : $sort], fn($v) => $v !== '' && $v !== null && $v !== []);
    $q = array_merge($q, $over);
    $q = array_filter($q, fn($v) => $v !== null);
    return $q ? '?' . http_build_query($q) : '';
};
$activeCount = ($f['location'] !== '') + ($f['class'] !== '') + ($f['subject'] !== '') + count($f['medium']) + count($f['type']);
$avatar = function (?string $img, ?string $name, string $cls = 'tc-av'): string {
    $ini = e(initials((string)$name, 'U'));
    return '<span class="' . $cls . '">' . $ini . (!is_blank($img) ? '<img src="' . e($img) . '" alt="" loading="lazy" onerror="this.remove()" />' : '') . '</span>';
};
$CommentIso = fn($dt) => ($d = to_dt($dt)) ? gmdate('Y-m-d\TH:i:s', $d->getTimestamp()) . 'Z' : '';
$RenderCommentText = function (?string $text): string {
    $text ??= '';
    if (preg_match('/^@\[([^\]]+)\]\s?/u', $text, $m)) return '<span class="fbc-mention">' . e($m[1]) . '</span> ' . e(substr($text, strlen($m[0])));
    return e($text);
};
$loginUrl = '/Account/Login?returnUrl=' . rawurlencode('/Tuition/Circular');
?>
<?= csrf_field() ?>

<style>
    .tc2 { --tc-navy: #0F1B2D; --tc-teal: #0F4C5C; --tc-teal-d: #0B3B48; --tc-line: #E6EDF5; --tc-muted: #64748B; --tc-soft: #94A3B8;
        --tc-bg: #F4F8FC; --tc-yellow: #FFC21A; --tc-shadow: 0 1px 2px rgba(15,27,45,.04), 0 6px 20px rgba(15,27,45,.05);
        background: var(--tc-bg); padding: 22px 0 70px; min-height: 80vh; color: var(--tc-navy); }
    .tc2 *, .tc2 *::before, .tc2 *::after { box-sizing: border-box; }
    .tc2 h1, .tc2 h2, .tc2 h3, .tc2 h4, .tc2 h5 { color: var(--tc-navy); font-family: inherit; }
    .tc-wrap { max-width: 1440px; margin: 0 auto; padding: 0 24px; }
    .tc-card { background: #fff; border: 1px solid var(--tc-line); border-radius: 16px; box-shadow: var(--tc-shadow); }

    /* ===== Banner ===== */
    .tc-banner { position: relative; margin-bottom: 24px; border-radius: 20px; overflow: hidden; background: linear-gradient(120deg, #EAF4FF, #F6FBFF 55%, #FFF6E5); box-shadow: var(--tc-shadow); }
    .tc-banner img { display: block; width: 100%; height: auto; }
    .tc-banner-text { padding: 34px 40px; }
    .tc-banner-text h1 { margin: 0; display: flex; align-items: center; gap: 16px; font-size: 44px; font-weight: 800; }
    .tc-banner-text p { margin: 10px 0 0; font-size: 19px; color: #334155; max-width: 640px; line-height: 1.6; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

    .tc-grid { display: grid; grid-template-columns: 290px minmax(0, 1fr) 300px; gap: 24px; align-items: start; }

    /* ===== Filter ===== */
    .tc-filter { padding: 22px 20px; position: sticky; top: 96px; }
    .tc-filter h3 { margin: 0 0 16px; padding-bottom: 14px; border-bottom: 1px solid var(--tc-line); font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .tc-fg { margin-bottom: 16px; }
    .tc-fg > label, .tc-fg > .tc-fl { display: block; font-size: 15px; font-weight: 700; margin-bottom: 8px; color: var(--tc-navy); }
    .tc-sel { position: relative; }
    .tc-sel i.lead { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #475569; font-size: 14px; pointer-events: none; }
    .tc-sel select { width: 100%; height: 44px; border: 1.5px solid var(--tc-line); border-radius: 10px; background: #fff; padding: 0 34px 0 38px; font-size: 14.5px;
        color: var(--tc-navy); appearance: none; -webkit-appearance: none; cursor: pointer; outline: none; font-family: inherit;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23475569' stroke-width='1.8' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 14px center; }
    .tc-sel select:focus { border-color: var(--tc-teal); box-shadow: 0 0 0 3px rgba(15,76,92,.12); }
    .tc-sel select.empty { color: #64748B; }
    .tc2 .tc-chk { display: flex; align-items: center; gap: 10px; padding: 5px 0; font-size: 14.5px; font-weight: 400; color: #334155; cursor: pointer; margin: 0; }
    .tc2 .tc-chk input { appearance: auto; -webkit-appearance: checkbox; position: static; width: 17px; height: 17px; accent-color: var(--tc-teal); cursor: pointer; margin: 0; flex-shrink: 0; }
    .tc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; height: 46px; padding: 0 20px; border-radius: 10px; border: none;
        font-weight: 600; font-size: 15px; cursor: pointer; text-decoration: none; transition: background .15s, transform .15s; font-family: inherit; }
    .tc-btn.dark { background: var(--tc-teal); color: #fff; }
    .tc-btn.dark:hover { background: var(--tc-teal-d); color: #fff; }
    .tc-btn.ghost { background: #fff; color: #334155; border: 1.5px solid var(--tc-line); }
    .tc-btn.ghost:hover { background: #F8FAFC; color: #334155; }
    .tc-btn.yellow { background: var(--tc-yellow); color: var(--tc-navy); font-weight: 700; box-shadow: 0 6px 16px rgba(255,194,26,.35); }
    .tc-btn.yellow:hover { background: #F5B500; color: var(--tc-navy); transform: translateY(-1px); }
    .tc-btn.block { width: 100%; }
    .tc-filter .tc-btn + .tc-btn { margin-top: 10px; }
    .tc-ftoggle { display: none; }

    /* ===== Composer ===== */
    .tc-compose { padding: 18px 20px; margin-bottom: 22px; }
    .tc-compose-top { display: flex; gap: 14px; align-items: center; }
    .tc-av { width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #0B2447, #1D63E0); color: #fff; font-weight: 700; font-size: 15px; text-decoration: none; }
    .tc-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .tc-compose-in { flex: 1; min-width: 0; height: 50px; border-radius: 14px; border: 1px solid var(--tc-line); background: #F4F7FB; padding: 0 18px;
        font-size: 15px; color: var(--tc-navy); outline: none; font-family: inherit; cursor: text; }
    .tc-compose-in::placeholder { color: #64748B; }
    .tc-compose-in:focus { background: #fff; border-color: #B9CBE0; }
    .tc-compose-row { display: flex; align-items: center; gap: 12px; margin-top: 16px; flex-wrap: wrap; }
    .tc-chip { display: inline-flex; align-items: center; gap: 9px; height: 42px; padding: 0 18px; border-radius: 10px; border: none; cursor: pointer; font-size: 14.5px;
        font-weight: 500; color: #334155; transition: filter .15s, transform .15s; font-family: inherit; }
    .tc-chip:hover { filter: brightness(.96); transform: translateY(-1px); }
    .tc-chip i { font-size: 17px; }
    .ch-ph { background: #EAF7EE; } .ch-ph i { color: #22A35A; }
    .ch-vd { background: #FDECEE; } .ch-vd i { color: #E5383B; }
    .ch-lc { background: #F3EEFF; } .ch-lc i { color: #8B5CF6; }
    .ch-sb { background: #EAF1FE; } .ch-sb i { color: #2563EB; }
    .tc-compose-row .tc-btn.dark { margin-left: auto; height: 42px; padding: 0 22px; }
    .tc-login-note { padding: 14px 18px; border-radius: 12px; background: #FFF8E6; color: #8A5A00; font-size: 14px; margin-top: 14px; }
    .tc-login-note a { color: var(--tc-teal); font-weight: 700; }

    /* ===== Feed ===== */
    .tc-feed-h { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 4px 2px 14px; flex-wrap: wrap; }
    .tc-feed-h h2 { margin: 0; font-size: 19px; font-weight: 700; display: flex; align-items: center; gap: 12px; }
    .tc-sort { display: inline-flex; align-items: center; gap: 6px; font-size: 14.5px; color: #475569; }
    .tc-sort select { border: none; background: transparent; font: inherit; color: var(--tc-navy); cursor: pointer; padding: 4px 2px; outline: none; }
    .tc-active { display: flex; flex-wrap: wrap; gap: 8px; margin: -4px 0 14px; }
    .tc-active a { display: inline-flex; align-items: center; gap: 7px; height: 30px; padding: 0 12px; border-radius: 15px; background: #E8F1FB; color: #1E3A5F;
        font-size: 13px; font-weight: 600; text-decoration: none; }
    .tc-active a:hover { background: #D6E6F8; color: #1E3A5F; }
    .tc-post { margin-bottom: 14px; scroll-margin-top: 110px; transition: box-shadow .3s; }
    .tc-post.flash { box-shadow: 0 0 0 3px #9EC2FA, var(--tc-shadow); }
    .tc-post-in { display: flex; gap: 16px; padding: 18px 20px; }
    .tc-post-in > .tc-av { width: 56px; height: 56px; font-size: 18px; }
    .tc-post-body { flex: 1; min-width: 0; }
    .tc-post-top { display: flex; align-items: flex-start; gap: 10px; }
    .tc-who { flex: 1; min-width: 0; }
    .tc-who a.nm { font-size: 15px; font-weight: 700; color: var(--tc-navy); text-decoration: none; }
    .tc-who a.nm:hover { text-decoration: underline; }
    .tc-who .tch { display: inline-block; margin-left: 6px; font-size: 10.5px; font-weight: 700; padding: 1px 7px; border-radius: 8px; background: #E7F8EE; color: #15803D; vertical-align: 2px; }
    .tc-time { font-size: 13px; color: var(--tc-muted); margin-top: 2px; display: flex; align-items: center; gap: 6px; }
    .tc-ic { width: 34px; height: 34px; border-radius: 50%; border: none; background: none; color: #334155; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .tc-ic:hover { background: #F1F5F9; }
    .tc-ic.on { color: var(--tc-teal); }
    .tc-menu-wrap { position: relative; }
    .tc-menu { display: none; position: absolute; right: 0; top: 38px; z-index: 30; min-width: 190px; background: #fff; border: 1px solid var(--tc-line); border-radius: 12px; box-shadow: 0 14px 34px rgba(15,27,45,.15); padding: 6px; }
    .tc-menu.open { display: block; }
    .tc-menu button, .tc-menu a { display: flex; width: 100%; align-items: center; gap: 10px; padding: 9px 10px; border: none; background: none; border-radius: 8px; font-size: 14px;
        font-weight: 600; color: var(--tc-navy); text-decoration: none; cursor: pointer; text-align: left; font-family: inherit; }
    .tc-menu button:hover, .tc-menu a:hover { background: #F4F7FB; color: var(--tc-navy); }
    .tc-menu .danger { color: #DC2626; }
    .tc-ptitle { margin: 10px 0 4px; font-size: 18px; font-weight: 800; line-height: 1.4; word-break: break-word; }
    .tc-desc { font-size: 15px; color: #334155; line-height: 1.65; white-space: pre-line; word-break: break-word; }
    .tc-desc.clamp { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .tc-more { border: none; background: none; padding: 0; margin-top: 2px; color: var(--tc-teal); font-weight: 700; font-size: 14px; cursor: pointer; }
    .tc-media { margin-top: 12px; border-radius: 12px; overflow: hidden; background: #0F1B2D; }
    .tc-media img { display: block; width: 100%; max-height: 460px; object-fit: contain; background: #F4F7FB; cursor: zoom-in; }
    .tc-media video { display: block; width: 100%; max-height: 460px; background: #000; }
    .tc-foot { display: flex; align-items: center; gap: 12px; margin-top: 14px; flex-wrap: wrap; }
    .tc-tags { display: flex; flex-wrap: wrap; gap: 8px; flex: 1; min-width: 0; }
    .tc-tag { display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 13px; border-radius: 9px; background: #EEF3F9; color: #1E293B; font-size: 13.5px; font-weight: 600; }
    .tc-tag i { font-size: 12.5px; color: #1E293B; }
    .tc-tag.md { background: #E7F8EE; color: #166534; } .tc-tag.md i { color: #16A34A; }
    .tc-tag.ty { background: #E8F6EE; color: #166534; } .tc-tag.ty i { color: #16A34A; }
    .tc-tag.bd { background: #FFF6DB; color: #92400E; } .tc-tag.bd i { color: #D97706; }
    .tc-stats { display: flex; align-items: center; gap: 18px; color: #475569; font-size: 14px; margin-left: auto; }
    .tc-stats span, .tc-stats button { display: inline-flex; align-items: center; gap: 7px; border: none; background: none; padding: 0; color: inherit; font: inherit; }
    .tc-stats button { cursor: pointer; }
    .tc-stats button:hover { color: var(--tc-teal); }
    .tc-stats i { font-size: 16px; }
    .tc-apply { height: 36px; padding: 0 16px; border-radius: 9px; border: none; background: var(--tc-teal); color: #fff; font-weight: 700; font-size: 13.5px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px; font-family: inherit; }
    .tc-apply:hover { background: var(--tc-teal-d); }
    .tc-apply.applied { background: #E2F3F7; color: #0F4C5C; cursor: default; }
    .tc-post .fb-comments-area { border-top: 1px solid var(--tc-line); border-radius: 0 0 16px 16px; padding: 14px 20px 16px; }
    .tc-empty { padding: 50px 20px; text-align: center; color: var(--tc-muted); }
    .tc-empty i { font-size: 44px; color: #CBD5E1; display: block; margin-bottom: 14px; }
    .tc-empty h4 { margin: 0 0 6px; font-size: 18px; font-weight: 700; }
    .tc-loadmore { display: block; width: 100%; text-align: center; padding: 13px; border-radius: 12px; background: #fff; border: 1px solid var(--tc-line);
        color: var(--tc-teal); font-weight: 700; text-decoration: none; }
    .tc-loadmore:hover { background: #F4F8FC; color: var(--tc-teal); }
    .tc-off-h { margin: 30px 2px 14px; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .tc-off { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
    .tc-off .tc-card { padding: 16px 18px; display: flex; flex-direction: column; gap: 8px; }
    .tc-off h4 { margin: 0; font-size: 16px; font-weight: 700; }
    .tc-off p { margin: 0; font-size: 13.5px; color: var(--tc-muted); line-height: 1.6; }
    .tc-off .code { font-family: monospace; font-size: 12px; background: #0F1B2D; color: #FFC21A; padding: 2px 8px; border-radius: 6px; }
    .tc-off .tc-btn { height: 38px; font-size: 14px; margin-top: auto; }

    /* ===== Right column ===== */
    .tc-side { display: flex; flex-direction: column; gap: 18px; position: sticky; top: 96px; }
    .tc-need { padding: 22px 20px; background: radial-gradient(140% 90% at 100% 0%, #E3EEFF 0%, rgba(227,238,255,0) 55%), linear-gradient(170deg, #F4F8FF 0%, #EEF4FC 100%); border-color: #E1EAF6; position: relative; overflow: hidden; }
    .tc-need h3 { margin: 0; display: flex; align-items: center; gap: 12px; font-size: 19px; font-weight: 800; }
    .tc-need h3 i { font-size: 24px; }
    .tc-need p { position: relative; z-index: 1; margin: 10px 56px 18px 40px; font-size: 14.5px; color: #334155; line-height: 1.6; }
    .tc-need .plane { position: absolute; right: 18px; top: 58px; color: #F5B500; font-size: 26px; transform: rotate(-12deg); }
    .tc-need .trail { position: absolute; right: 30px; top: 88px; width: 34px; height: 44px; border-left: 2px dashed #F5C04A; border-radius: 0 0 0 40px; opacity: .7; }
    .tc-how { padding: 20px; }
    .tc-how h3 { margin: 0 0 16px; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .tc-how h3 i { color: #F5B500; font-size: 22px; }
    .tc-steps { list-style: none; margin: 0; padding: 0; }
    .tc-steps li { position: relative; display: flex; gap: 14px; padding-bottom: 18px; font-size: 14.5px; color: #334155; line-height: 1.5; }
    .tc-steps li:last-child { padding-bottom: 0; }
    .tc-steps li::before { content: ''; position: absolute; left: 15px; top: 34px; bottom: 2px; border-left: 2px dotted #CBD5E1; }
    .tc-steps li:last-child::before { display: none; }
    .tc-steps b { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 15px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .tc-steps li:nth-child(1) b { background: #2F80ED; } .tc-steps li:nth-child(2) b { background: #22B35E; }
    .tc-steps li:nth-child(3) b { background: #F2792B; } .tc-steps li:nth-child(4) b { background: #8B5CF6; }
    .tc-steps span { padding-top: 5px; }
    .tc-safe { padding: 20px; background: #EEF9F1; border-color: #D5F0DE; }
    .tc-safe h3 { margin: 0 0 14px; font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 12px; color: #14532D; }
    .tc-safe h3 i { font-size: 26px; color: #16A34A; }
    .tc-safe ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
    .tc-safe li { display: flex; align-items: center; gap: 12px; font-size: 14.5px; color: #1F2937; }
    .tc-safe li i { color: #16A34A; font-size: 16px; }

    /* ===== Post modal ===== */
    .tcm-ov { position: fixed; inset: 0; z-index: 2600; background: rgba(15,27,45,.5); display: none; align-items: center; justify-content: center; padding: 16px; }
    .tcm-ov.open { display: flex; }
    .tcm { width: 100%; max-width: 620px; max-height: calc(100vh - 32px); overflow-y: auto; background: #fff; border-radius: 18px; box-shadow: 0 24px 60px rgba(15,27,45,.3); animation: tcmIn .2s ease; }
    @keyframes tcmIn { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
    .tcm-h { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--tc-line); position: sticky; top: 0; background: #fff; z-index: 2; }
    .tcm-h h3 { margin: 0; font-size: 19px; font-weight: 800; }
    .tcm-x { width: 36px; height: 36px; border-radius: 50%; border: none; background: #F1F5F9; cursor: pointer; font-size: 16px; color: #334155; }
    .tcm-b { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; }
    .tcm-f label { display: block; font-size: 13.5px; font-weight: 700; margin-bottom: 6px; color: #1E293B; }
    .tcm-f label b { color: #E5383B; }
    .tcm-f label small { font-weight: 500; color: var(--tc-soft); }
    .tcm-f input[type=text], .tcm-f input[type=number], .tcm-f textarea, .tcm-f select { width: 100%; border: 1.5px solid var(--tc-line); border-radius: 10px; padding: 10px 12px; font-size: 15px;
        outline: none; background: #fff; font-family: inherit; color: var(--tc-navy); }
    .tcm-f textarea { min-height: 120px; resize: vertical; line-height: 1.6; }
    .tcm-f input:focus, .tcm-f textarea:focus, .tcm-f select:focus { border-color: var(--tc-teal); box-shadow: 0 0 0 3px rgba(15,76,92,.12); }
    .tcm-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .tcm-seg { display: flex; flex-wrap: wrap; gap: 8px; }
    .tcm-seg label { margin: 0; cursor: pointer; }
    .tcm-seg input { position: absolute; opacity: 0; pointer-events: none; }
    .tcm-seg span { display: inline-flex; align-items: center; height: 36px; padding: 0 14px; border-radius: 18px; border: 1.5px solid var(--tc-line); font-size: 13.5px; font-weight: 600; color: #334155; }
    .tcm-seg input:checked + span { background: var(--tc-teal); border-color: var(--tc-teal); color: #fff; }
    .tcm-seg input:focus-visible + span { outline: 2px solid #9EC2FA; outline-offset: 2px; }
    .tcm-media { display: flex; gap: 10px; flex-wrap: wrap; }
    .tcm-media label { margin: 0; }
    .tcm-prev { position: relative; border-radius: 12px; overflow: hidden; background: #F4F7FB; display: none; }
    .tcm-prev.show { display: block; }
    .tcm-prev img, .tcm-prev video { display: block; width: 100%; max-height: 260px; object-fit: contain; }
    .tcm-prev .nm { display: block; padding: 10px 12px; font-size: 13.5px; color: #334155; }
    .tcm-prev button { position: absolute; top: 8px; right: 8px; width: 32px; height: 32px; border-radius: 50%; border: none; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.2); cursor: pointer; }
    .tcm-err { display: none; padding: 10px 12px; border-radius: 10px; background: #FEF2F2; color: #B91C1C; font-size: 14px; }
    .tcm-err.show { display: block; }
    .tcm-f-foot { padding: 0 20px 20px; display: flex; gap: 10px; justify-content: flex-end; }

    /* ===== Responsive ===== */
    @media (max-width: 1280px) {
        .tc-grid { grid-template-columns: 260px minmax(0, 1fr); }
        .tc-side { grid-column: 1 / -1; position: static; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    }
    @media (max-width: 992px) {
        .tc-wrap { padding: 0 14px; }
        .tc-grid { grid-template-columns: minmax(0, 1fr); gap: 16px; }
        .tc-filter { position: static; padding: 0; }
        .tc-filter h3 { display: none; }
        .tc-ftoggle { display: flex; width: 100%; align-items: center; gap: 10px; padding: 14px 18px; border: none; background: none; font-size: 16px; font-weight: 700; color: var(--tc-navy); cursor: pointer; font-family: inherit; }
        .tc-ftoggle em { font-style: normal; background: var(--tc-teal); color: #fff; font-size: 12px; padding: 1px 8px; border-radius: 10px; }
        .tc-ftoggle .chev { margin-left: auto; transition: transform .2s; }
        .tc-filter.open .tc-ftoggle .chev { transform: rotate(180deg); }
        .tc-fbody { display: none; padding: 4px 18px 18px; }
        .tc-filter.open .tc-fbody { display: block; }
        .tc-side { grid-template-columns: minmax(0, 1fr); }
        .tc-banner-text { padding: 24px 22px; }
        .tc-banner-text h1 { font-size: 30px; }
        .tc-banner-text p { font-size: 15.5px; }
    }
    @media (max-width: 640px) {
        .tc2 { padding-top: 12px; }
        .tc-wrap { padding: 0 10px; }
        .tc-banner { border-radius: 14px; margin-bottom: 14px; }
        .tc-banner img { height: 140px; object-fit: cover; object-position: left center; }
        .tc-compose { padding: 14px; }
        .tc-compose-row { gap: 8px; }
        .tc-chip { height: 38px; padding: 0 12px; font-size: 13.5px; }
        .tc-compose-row .tc-btn.dark { width: 100%; margin-left: 0; }
        .tc-post-in { padding: 14px; gap: 12px; }
        .tc-post-in > .tc-av { width: 44px; height: 44px; font-size: 15px; }
        .tc-ptitle { font-size: 16.5px; }
        .tc-desc { font-size: 14.5px; }
        .tc-stats { margin-left: 0; width: 100%; justify-content: flex-start; }
        .tc-apply { margin-left: auto; }
        .tc-post .fb-comments-area { padding: 12px 14px; }
        .tcm-ov { padding: 0; align-items: flex-end; }
        .tcm { max-width: none; border-radius: 18px 18px 0 0; max-height: 94vh; }
        .tcm-2 { grid-template-columns: 1fr; }
    }

    /* ===== কমেন্ট (আগের মতো) ===== */
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

</style>

<div class="tc2">
    <div class="tc-wrap">
        <!-- ======================= BANNER ======================= -->
        <?php if ($banner['tc_banner_enabled'] !== '0' && !is_blank($banner['tc_banner'])): ?>
            <div class="tc-banner">
                <h1 class="sr-only">Tuition Circular</h1>
                <img src="<?= e($banner['tc_banner']) ?>" alt="Tuition Circular — আপনার টিউশনের প্রয়োজন পোস্ট করুন এবং একজন উপযুক্ত শিক্ষক খুঁজে নিন খুব সহজে!"
                     onerror="var fb = this.parentNode.querySelector('.tc-banner-fallback'); if (fb) fb.hidden = false; this.remove();" />
                <div class="tc-banner-text tc-banner-fallback" hidden>
                    <h1><i class="fas fa-bullhorn"></i> Tuition Circular</h1>
                    <p>আপনার টিউশনের প্রয়োজন পোস্ট করুন এবং একজন উপযুক্ত শিক্ষক খুঁজে নিন খুব সহজে!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="tc-banner">
                <div class="tc-banner-text">
                    <h1><i class="fas fa-bullhorn"></i> Tuition Circular</h1>
                    <p>আপনার টিউশনের প্রয়োজন পোস্ট করুন এবং একজন উপযুক্ত শিক্ষক খুঁজে নিন খুব সহজে!</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="tc-grid">
            <!-- ======================= FILTER ======================= -->
            <aside class="tc-card tc-filter<?= $activeCount ? '' : '' ?>" id="tcFilter">
                <button type="button" class="tc-ftoggle" id="tcFToggle" aria-expanded="false" aria-controls="tcFBody">
                    <i class="fas fa-filter"></i> Filter Tuition Post <?php if ($activeCount): ?><em><?= $activeCount ?></em><?php endif; ?> <i class="fas fa-chevron-down chev"></i>
                </button>
                <h3><i class="fas fa-filter"></i> Filter Tuition Post</h3>
                <form class="tc-fbody" id="tcFBody" method="get" action="/Tuition/Circular">
                    <?php if ($sort !== 'recent'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>" /><?php endif; ?>
                    <?php foreach ([['location', 'Location', 'fa-location-dot', 'Select location', $locOptions], ['class', 'Class/Level', 'fa-graduation-cap', 'Select class/level', $classOptions],
                                    ['subject', 'Subject', 'fa-book', 'Select subject', $subjectOptions]] as [$key, $lbl, $ic, $ph, $opts]): ?>
                        <div class="tc-fg">
                            <label for="f-<?= $key ?>"><?= $lbl ?></label>
                            <div class="tc-sel"><i class="fas <?= $ic ?> lead"></i>
                                <select name="<?= $key ?>" id="f-<?= $key ?>" class="<?= $f[$key] === '' ? 'empty' : '' ?>">
                                    <option value=""><?= $ph ?></option>
                                    <?php foreach ($opts as $o): ?><option value="<?= e($o) ?>"<?= strcasecmp($f[$key], $o) === 0 ? ' selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="tc-fg">
                        <span class="tc-fl">Medium</span>
                        <?php foreach (['Bangla' => 'Bangla Medium', 'English' => 'English Medium', 'Both' => 'Both'] as $v => $l): ?>
                            <label class="tc-chk"><input type="checkbox" name="medium[]" value="<?= $v ?>"<?= in_array($v, $f['medium'], true) ? ' checked' : '' ?> /> <?= $l ?></label>
                        <?php endforeach; ?>
                    </div>
                    <div class="tc-fg">
                        <span class="tc-fl">Tuition Type</span>
                        <?php foreach (['Home' => 'Home Tuition', 'Online' => 'Online Tuition', 'Both' => 'Both'] as $v => $l): ?>
                            <label class="tc-chk"><input type="checkbox" name="type[]" value="<?= $v ?>"<?= in_array($v, $f['type'], true) ? ' checked' : '' ?> /> <?= $l ?></label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="tc-btn dark block"><i class="fas fa-magnifying-glass"></i> Apply Filter</button>
                    <a href="/Tuition/Circular<?= $sort !== 'recent' ? '?sort=' . e($sort) : '' ?>" class="tc-btn ghost block"><i class="fas fa-rotate"></i> Reset Filter</a>
                </form>
            </aside>

            <!-- ======================= MAIN ======================= -->
            <main class="tc-main">
                <div class="tc-card tc-compose">
                    <div class="tc-compose-top">
                        <?= $avatar($isLoggedIn ? $userProfileImg : null, $isLoggedIn ? $userFullName : 'U') ?>
                        <input type="text" class="tc-compose-in" id="tcQuick" maxlength="3000" autocomplete="off"
                               placeholder="আপনার টিউশনের প্রয়োজন সম্পর্কে লিখুন..." <?= $isLoggedIn ? '' : 'readonly' ?> data-tc-open="text" />
                    </div>
                    <div class="tc-compose-row">
                        <button type="button" class="tc-chip ch-ph" data-tc-open="photo"><i class="fas fa-image"></i> Photo</button>
                        <button type="button" class="tc-chip ch-vd" data-tc-open="video"><i class="fas fa-video"></i> Video</button>
                        <button type="button" class="tc-chip ch-lc" data-tc-open="location"><i class="fas fa-location-dot"></i> Location</button>
                        <button type="button" class="tc-chip ch-sb" data-tc-open="subject"><i class="fas fa-book-open"></i> Subject</button>
                        <button type="button" class="tc-btn dark" data-tc-open="post"><i class="fas fa-paper-plane"></i> Post Now</button>
                    </div>
                    <?php if (!$isLoggedIn): ?>
                        <div class="tc-login-note"><i class="fas fa-circle-info"></i> পোস্ট বা কমেন্ট করতে <a href="<?= e($loginUrl) ?>">লগইন করুন</a> — অ্যাকাউন্ট না থাকলে <a href="/Account/Register">রেজিস্টার করুন</a>।</div>
                    <?php endif; ?>
                </div>

                <div class="tc-feed-h">
                    <h2><i class="fas fa-list-ul"></i> <?= $isFiltered ? 'Filtered Tuition Posts' : 'Recent Tuition Posts' ?>
                        <?php if ($isFiltered): ?><small style="font-size:14px;color:#64748B;font-weight:500;">(<?= $total ?>টি)</small><?php endif; ?></h2>
                    <label class="tc-sort">Sort by:
                        <select id="tcSort" aria-label="Sort posts">
                            <?php foreach (['recent' => 'Recent', 'views' => 'Most viewed', 'comments' => 'Most discussed'] as $k => $l): ?>
                                <option value="<?= e($qs(['sort' => $k === 'recent' ? null : $k, 'page' => null])) ?>"<?= $sort === $k ? ' selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <?php if ($isFiltered): ?>
                    <div class="tc-active">
                        <?php foreach (['location' => 'fa-location-dot', 'class' => 'fa-graduation-cap', 'subject' => 'fa-book'] as $k => $ic): if ($f[$k] === '') continue; ?>
                            <a href="<?= e('/Tuition/Circular' . $qs([$k => null, 'page' => null])) ?>" title="সরান"><i class="fas <?= $ic ?>"></i> <?= e($f[$k]) ?> <i class="fas fa-xmark"></i></a>
                        <?php endforeach; ?>
                        <?php foreach (['medium' => $mediumLabel, 'type' => $typeLabel] as $k => $labels): foreach ($f[$k] as $v):
                            $rest = array_values(array_diff($f[$k], [$v])); ?>
                            <a href="<?= e('/Tuition/Circular' . $qs([$k => $rest ?: null, 'page' => null])) ?>" title="সরান"><?= e($labels[$v]) ?> <i class="fas fa-xmark"></i></a>
                        <?php endforeach; endforeach; ?>
                        <?php if ($f['q'] !== ''): ?><a href="<?= e('/Tuition/Circular' . $qs(['q' => null, 'page' => null])) ?>">"<?= e($f['q']) ?>" <i class="fas fa-xmark"></i></a><?php endif; ?>
                    </div>
                <?php endif; ?>

                <div id="circularFeed">
                    <?php if (!$feedPosts): ?>
                        <div class="tc-card tc-empty">
                            <i class="fas fa-inbox"></i>
                            <h4><?= $isFiltered ? 'এই ফিল্টারে কোনো পোস্ট নেই' : 'এখনো কোনো পোস্ট নেই' ?></h4>
                            <p><?= $isFiltered ? 'ফিল্টার একটু বদলে দেখুন, অথবা Reset Filter চাপুন।' : 'আপনার টিউশনের প্রয়োজন লিখে প্রথম পোস্টটি করুন।' ?></p>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($feedPosts as $post):
                        $pid = (int)$post->Id;
                        $isOwn = $userId && (int)$post->UserId === (int)$userId;
                        $text = (string)$post->Content;
                        $long = mb_strlen($text) > 230 || substr_count($text, "\n") > 2;
                        $isSv = isset($savedKeys['circular:' . $pid]);
                    ?>
                        <article class="tc-card tc-post" id="circular-post-<?= $pid ?>" data-id="<?= $pid ?>">
                            <div class="tc-post-in">
                                <a href="/Profile/Show/<?= (int)$post->UserId ?>" class="tc-av" aria-label="<?= e($post->UserFullName) ?>"><?= e(initials((string)$post->UserFullName, 'S')) ?><?php if (!is_blank($post->UserProfileImage)): ?><img src="<?= e($post->UserProfileImage) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                                <div class="tc-post-body">
                                    <div class="tc-post-top">
                                        <div class="tc-who">
                                            <a class="nm" href="/Profile/Show/<?= (int)$post->UserId ?>"><?= e($post->UserFullName) ?></a><?php if (($post->AuthorRole ?? '') === 'Teacher'): ?><span class="tch">Teacher</span><?php endif; ?>
                                            <div class="tc-time"><i class="far fa-clock"></i> <?= e(fmt_date($post->CreatedAt, 'd M Y, h:i A')) ?></div>
                                        </div>
                                        <?php if ($canSave): ?>
                                            <button type="button" class="tc-ic<?= $isSv ? ' on' : '' ?>" data-save-type="circular" data-save-id="<?= $pid ?>" title="Save" aria-label="Save post" aria-pressed="<?= $isSv ? 'true' : 'false' ?>">
                                                <i class="<?= $isSv ? 'fas' : 'far' ?> fa-bookmark"></i></button>
                                        <?php elseif (!$isLoggedIn): ?>
                                            <a class="tc-ic" href="<?= e($loginUrl) ?>" title="Save করতে লগইন করুন" aria-label="Save"><i class="far fa-bookmark"></i></a>
                                        <?php endif; ?>
                                        <div class="tc-menu-wrap">
                                            <button type="button" class="tc-ic" data-tc-menu aria-label="More options"><i class="fas fa-ellipsis-vertical"></i></button>
                                            <div class="tc-menu">
                                                <button type="button" data-tc-copy="<?= $pid ?>"><i class="fas fa-link"></i> Copy link</button>
                                                <?php if ($isOwn || $isAdmin): ?>
                                                    <button type="button" class="danger" data-tc-del="<?= $pid ?>"><i class="fas fa-trash-can"></i> Delete post</button>
                                                <?php else: ?>
                                                    <a href="/Helpdesk"><i class="fas fa-flag"></i> Report to admin</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!is_blank($post->Title ?? null)): ?><h3 class="tc-ptitle"><?= e($post->Title) ?></h3><?php else: ?><div style="height:8px"></div><?php endif; ?>
                                    <?php if ($text !== ''): ?>
                                        <div class="tc-desc<?= $long ? ' clamp' : '' ?>"><?= e($text) ?></div>
                                        <?php if ($long): ?><button type="button" class="tc-more" data-tc-more>আরো দেখুন</button><?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (!is_blank($post->VideoUrl ?? null)): ?>
                                        <div class="tc-media"><video src="<?= e($post->VideoUrl) ?>" controls preload="metadata" playsinline></video></div>
                                    <?php elseif (!is_blank($post->ImageUrl)): ?>
                                        <div class="tc-media"><img src="<?= e($post->ImageUrl) ?>" alt="Post photo" loading="lazy" data-tc-zoom /></div>
                                    <?php endif; ?>

                                    <div class="tc-foot">
                                        <div class="tc-tags">
                                            <?php if (!is_blank($post->PreferredArea)): ?><span class="tc-tag"><i class="fas fa-location-dot"></i> <?= e($post->PreferredArea) ?></span><?php endif; ?>
                                            <?php if (!is_blank($post->ClassLevel)): ?><span class="tc-tag"><i class="fas fa-graduation-cap"></i> <?= e($post->ClassLevel) ?></span><?php endif; ?>
                                            <?php if (!is_blank($post->Subjects)): ?><span class="tc-tag"><i class="fas fa-book"></i> <?= e($post->Subjects) ?></span><?php endif; ?>
                                            <?php if (!is_blank($post->Medium ?? null) && isset($mediumLabel[$post->Medium])): ?><span class="tc-tag md"><i class="far fa-rectangle-list"></i> <?= $mediumLabel[$post->Medium] ?></span><?php endif; ?>
                                            <?php if (!is_blank($post->TuitionType ?? null) && isset($typeLabel[$post->TuitionType])): ?><span class="tc-tag ty"><i class="fas fa-<?= $post->TuitionType === 'Online' ? 'circle-dot' : 'house' ?>"></i> <?= $typeLabel[$post->TuitionType] ?></span><?php endif; ?>
                                            <?php if (!is_blank($post->Budget)): ?><span class="tc-tag bd"><i class="fas fa-money-bill-wave"></i> ৳<?= e($post->Budget) ?></span><?php endif; ?>
                                        </div>
                                        <div class="tc-stats">
                                            <span title="কতবার দেখা হয়েছে"><i class="far fa-eye"></i> <?= (int)$post->ViewCount ?></span>
                                            <button type="button" onclick="toggleCircularComments(<?= $pid ?>)" title="Comments"><i class="far fa-comment"></i> <span id="comment-count-<?= $pid ?>"><?= (int)$post->CommentCount ?></span></button>
                                            <?php if ($isLoggedIn && $isTeacher && !$isOwn): ?>
                                                <?php if (isset($myApplied[$pid])): ?>
                                                    <button type="button" class="tc-apply applied" disabled><i class="fas fa-check-circle"></i> Applied<?= $myApplied[$pid] !== 'Pending' ? ' · ' . e($myApplied[$pid]) : '' ?></button>
                                                <?php else: ?>
                                                    <button type="button" class="tc-apply" id="apply-btn-<?= $pid ?>" onclick="applyForCircular(<?= $pid ?>)"><i class="fas fa-paper-plane"></i> Apply</button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments -->
                            <div class="fb-comments-area" id="circular-comments-<?= $pid ?>">
                                <div class="fb-comment-list" id="circular-comment-list-<?= $pid ?>">
                                    <?php foreach ($post->Comments ?? [] as $comment): ?>
                                        <div class="fbc" id="cfbc-<?= e($comment->Id) ?>" data-id="<?= e($comment->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($comment->UserFullName) ?>" data-raw="<?= e($comment->Content) ?>">
                                            <div class="fbc-avatar"><?php if (!is_blank($comment->UserProfileImage)): ?><img src="<?= e($comment->UserProfileImage) ?>" alt="user" /><?php else: ?><?= e(mb_strtoupper(mb_substr((string)$comment->UserFullName, 0, 1)) ?: 'U') ?><?php endif; ?></div>
                                            <div class="fbc-main">
                                                <div class="fbc-bubble <?= $comment->LikeCount > 0 ? 'has-likes' : '' ?>">
                                                    <span class="fbc-name"><?= e($comment->UserFullName) ?></span>
                                                    <div class="fbc-text"><?= $RenderCommentText($comment->Content) ?></div>
                                                    <span class="fbc-like-badge <?= $comment->LikeCount > 0 ? '' : 'zero' ?>"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n"><?= (int)$comment->LikeCount ?></span></span>
                                                </div>
                                                <div class="fbc-meta">
                                                    <?php if ($isLoggedIn): ?>
                                                        <button type="button" class="fbc-like-btn <?= $comment->IsLikedByMe ? 'liked' : '' ?>" onclick="cfbcToggleLike(this, <?= e($comment->Id) ?>)">Like</button>
                                                        <button type="button" onclick="cfbcOpenReply(<?= $pid ?>, <?= e($comment->Id) ?>, <?= e($comment->Id) ?>)">Reply</button><?= feed_comment_buttons($comment, (int)$post->UserId) ?>
                                                    <?php endif; ?>
                                                    <span class="fbc-time" data-utc="<?= e($CommentIso($comment->CreatedAt)) ?>"><?= e(fmt_date($comment->CreatedAt, 'd M')) ?></span>
                                                </div>
                                                <div class="fbc-replies-wrap">
                                                    <?php if (!empty($comment->Replies)): ?>
                                                        <button type="button" class="fbc-view-replies" id="cfbc-vr-<?= e($comment->Id) ?>" onclick="cfbcShowReplies(<?= e($comment->Id) ?>)"><i class="fas fa-share fa-flip-vertical"></i> View <?= count($comment->Replies) ?> replies</button>
                                                    <?php endif; ?>
                                                    <div class="fbc-replies" id="cfbc-replies-<?= e($comment->Id) ?>">
                                                        <?php foreach ($comment->Replies ?? [] as $reply): ?>
                                                            <div class="fbc is-reply" id="cfbc-<?= e($reply->Id) ?>" data-id="<?= e($reply->Id) ?>" data-root="<?= e($comment->Id) ?>" data-name="<?= e($reply->UserFullName) ?>" data-raw="<?= e($reply->Content) ?>">
                                                                <div class="fbc-avatar"><?php if (!is_blank($reply->UserProfileImage)): ?><img src="<?= e($reply->UserProfileImage) ?>" alt="user" /><?php else: ?><?= e(mb_strtoupper(mb_substr((string)$reply->UserFullName, 0, 1)) ?: 'U') ?><?php endif; ?></div>
                                                                <div class="fbc-main">
                                                                    <div class="fbc-bubble <?= $reply->LikeCount > 0 ? 'has-likes' : '' ?>">
                                                                        <span class="fbc-name"><?= e($reply->UserFullName) ?></span>
                                                                        <div class="fbc-text"><?= $RenderCommentText($reply->Content) ?></div>
                                                                        <span class="fbc-like-badge <?= $reply->LikeCount > 0 ? '' : 'zero' ?>"><i class="fas fa-thumbs-up"></i><span class="fbc-like-n"><?= (int)$reply->LikeCount ?></span></span>
                                                                    </div>
                                                                    <div class="fbc-meta">
                                                                        <?php if ($isLoggedIn): ?>
                                                                            <button type="button" class="fbc-like-btn <?= $reply->IsLikedByMe ? 'liked' : '' ?>" onclick="cfbcToggleLike(this, <?= e($reply->Id) ?>)">Like</button>
                                                                            <button type="button" onclick="cfbcOpenReply(<?= $pid ?>, <?= e($comment->Id) ?>, <?= e($reply->Id) ?>)">Reply</button><?= feed_comment_buttons($reply, (int)$post->UserId) ?>
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
                                        <div class="fbc-avatar"><?php if (!is_blank($userProfileImg)): ?><img src="<?= e($userProfileImg) ?>" alt="me" /><?php else: ?><?= e(mb_strtoupper(mb_substr((string)$userFullName, 0, 1)) ?: 'U') ?><?php endif; ?></div>
                                        <div class="fbc-pill">
                                            <input type="text" id="circular-comment-input-<?= $pid ?>" placeholder="Write a comment..." maxlength="2000" onkeydown="handleCircularCommentEnter(event, <?= $pid ?>)" />
                                            <button type="button" class="fbc-send" onclick="submitCircularComment(<?= $pid ?>)" title="Send"><i class="fas fa-paper-plane"></i></button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p style="margin:0;font-size:13.5px;color:#64748B;"><a href="<?= e($loginUrl) ?>" style="font-weight:700;color:#0F4C5C;">লগইন করুন</a> কমেন্ট করতে।</p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($total > count($feedPosts)): ?>
                    <a class="tc-loadmore" href="<?= e('/Tuition/Circular' . $qs(['page' => $page + 1])) ?>#circular-post-<?= (int)end($feedPosts)->Id ?>">
                        <i class="fas fa-angles-down"></i> আরো পোস্ট দেখুন (<?= $total - count($feedPosts) ?>টি বাকি)</a>
                <?php endif; ?>

                <?php if (!empty($Model) && !$isFiltered): ?>
                    <h3 class="tc-off-h"><i class="fas fa-clipboard-list" style="color:#0F4C5C;"></i> অফিসিয়াল টিউশন সার্কুলার</h3>
                    <div class="tc-off">
                        <?php foreach ($Model as $ap): ?>
                            <div class="tc-card">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                    <?php if (!is_blank($ap->TuitionCode)): ?><span class="code"><?= e($ap->TuitionCode) ?></span><?php else: ?><span></span><?php endif; ?>
                                    <?php if ($canSave): $sv2 = isset($savedKeys['tuition:' . (int)$ap->Id]); ?>
                                        <button type="button" class="tc-ic<?= $sv2 ? ' on' : '' ?>" data-save-type="tuition" data-save-id="<?= (int)$ap->Id ?>" title="Save" aria-label="Save tuition"><i class="<?= $sv2 ? 'fas' : 'far' ?> fa-bookmark"></i></button>
                                    <?php endif; ?>
                                </div>
                                <h4><?= e($ap->Title) ?></h4>
                                <p>
                                    <?php if (!is_blank($ap->StudentClass)): ?><b>Class:</b> <?= e($ap->StudentClass) ?><br /><?php endif; ?>
                                    <?php if (!is_blank($ap->Subjects)): ?><b>Subjects:</b> <?= e($ap->Subjects) ?><br /><?php endif; ?>
                                    <?php if (!is_blank($ap->Area)): ?><b>Area:</b> <?= e($ap->Area) ?><br /><?php endif; ?>
                                    <?php if (!is_blank($ap->Salary)): ?><b style="color:#15803D;">Salary: ৳<?= e($ap->Salary) ?></b><?php endif; ?>
                                </p>
                                <a href="<?= $isLoggedIn ? '/Tuition/Details/' . (int)$ap->Id : e($loginUrl) ?>" class="tc-btn <?= $isLoggedIn ? 'dark' : 'ghost' ?>"><?= $isLoggedIn ? 'View Details' : 'Login to View' ?></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>

            <!-- ======================= RIGHT ======================= -->
            <aside class="tc-side">
                <div class="tc-card tc-need">
                    <h3><i class="fas fa-graduation-cap"></i> Need a Teacher?</h3>
                    <span class="trail" aria-hidden="true"></span><i class="fas fa-paper-plane plane" aria-hidden="true"></i>
                    <p>আপনার প্রয়োজন অনুযায়ী সেরা শিক্ষক খুঁজে পেতে এখনই পোস্ট করুন।</p>
                    <button type="button" class="tc-btn yellow block" data-tc-open="post"><i class="fas fa-paper-plane"></i> Post Tuition Request</button>
                </div>
                <div class="tc-card tc-how">
                    <h3><i class="fas fa-lightbulb"></i> How It Works?</h3>
                    <ol class="tc-steps">
                        <li><b>1</b><span>আপনার টিউশনের প্রয়োজন বিস্তারিত লিখুন</span></li>
                        <li><b>2</b><span>লোকেশন, ক্লাস, বিষয় ইত্যাদি দিন</span></li>
                        <li><b>3</b><span>পোস্ট করুন</span></li>
                        <li><b>4</b><span>আগ্রহী শিক্ষকরা আপনার সাথে যোগাযোগ করবেন</span></li>
                    </ol>
                </div>
                <div class="tc-card tc-safe">
                    <h3><i class="fas fa-shield-halved"></i> নিরাপদ ও সহজ প্ল্যাটফর্ম</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> যাচাইকৃত শিক্ষক প্রোফাইল</li>
                        <li><i class="fas fa-check"></i> সুরক্ষিত যোগাযোগ ব্যবস্থা</li>
                        <li><i class="fas fa-check"></i> সহজ পোস্টিং ও ম্যানেজমেন্ট</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</div>

<!-- ======================= POST MODAL ======================= -->
<?php if ($isLoggedIn): ?>
<div class="tcm-ov" id="tcmOv" aria-hidden="true">
    <form class="tcm" id="tcmForm" autocomplete="off" novalidate>
        <div class="tcm-h"><h3>টিউশন পোস্ট করুন</h3><button type="button" class="tcm-x" data-tcm-close aria-label="Close"><i class="fas fa-xmark"></i></button></div>
        <div class="tcm-b">
            <div style="display:flex;align-items:center;gap:12px;">
                <?= $avatar($userProfileImg, $userFullName) ?>
                <div><b style="font-size:15px;"><?= e($userFullName) ?></b><div style="font-size:12.5px;color:#64748B;">সবাই এই পোস্ট দেখতে পাবে</div></div>
            </div>
            <div class="tcm-err" id="tcmErr"></div>
            <div class="tcm-f"><label for="tcmTitle">শিরোনাম <b>*</b></label>
                <input type="text" id="tcmTitle" name="title" maxlength="150" placeholder="যেমন: HSC এর জন্য Mathematics টিউটর প্রয়োজন" /></div>
            <div class="tcm-f"><label for="tcmContent">বিস্তারিত <b>*</b></label>
                <textarea id="tcmContent" name="content" maxlength="3000" placeholder="সপ্তাহে কয়দিন, কোন সময়, শিক্ষকের কাছে কী আশা করেন ইত্যাদি লিখুন..."></textarea></div>
            <div class="tcm-2">
                <div class="tcm-f"><label for="tcmLoc"><i class="fas fa-location-dot" style="color:#8B5CF6;"></i> লোকেশন</label>
                    <input type="text" id="tcmLoc" name="preferredArea" maxlength="100" list="tcLocList" placeholder="যেমন: Mirpur 10, Dhaka" /></div>
                <div class="tcm-f"><label for="tcmClass"><i class="fas fa-graduation-cap" style="color:#0F4C5C;"></i> ক্লাস / লেভেল</label>
                    <input type="text" id="tcmClass" name="classLevel" maxlength="60" list="tcClassList" placeholder="যেমন: HSC" /></div>
            </div>
            <div class="tcm-2">
                <div class="tcm-f"><label for="tcmSubj"><i class="fas fa-book-open" style="color:#2563EB;"></i> বিষয়</label>
                    <input type="text" id="tcmSubj" name="subjects" maxlength="120" list="tcSubjList" placeholder="যেমন: Mathematics, Physics" /></div>
                <div class="tcm-f"><label for="tcmBudget">বাজেট (৳/মাস) <small>ঐচ্ছিক</small></label>
                    <input type="number" id="tcmBudget" name="budget" min="0" max="9999999" inputmode="numeric" placeholder="যেমন: 5000" /></div>
            </div>
            <div class="tcm-f"><label>মিডিয়াম</label>
                <div class="tcm-seg"><?php foreach (['Bangla' => 'Bangla Medium', 'English' => 'English Medium', 'Both' => 'Both'] as $v => $l): ?><label><input type="radio" name="medium" value="<?= $v ?>" /><span><?= $l ?></span></label><?php endforeach; ?></div></div>
            <div class="tcm-f"><label>টিউশনের ধরন</label>
                <div class="tcm-seg"><?php foreach (['Home' => 'Home Tuition', 'Online' => 'Online Tuition', 'Both' => 'Both'] as $v => $l): ?><label><input type="radio" name="tuitionType" value="<?= $v ?>" /><span><?= $l ?></span></label><?php endforeach; ?></div></div>
            <div class="tcm-f"><label>ছবি বা ভিডিও <small>(ঐচ্ছিক — ছবি ৫ MB, ভিডিও ২৫ MB পর্যন্ত)</small></label>
                <div class="tcm-media">
                    <label class="tc-chip ch-ph" for="tcmImg"><i class="fas fa-image"></i> Photo</label>
                    <label class="tc-chip ch-vd" for="tcmVid"><i class="fas fa-video"></i> Video</label>
                </div>
                <input type="file" id="tcmImg" accept="image/jpeg,image/png,image/gif,image/webp" hidden />
                <input type="file" id="tcmVid" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v" hidden />
                <div class="tcm-prev" id="tcmPrev" style="margin-top:10px;"><button type="button" id="tcmPrevX" aria-label="Remove"><i class="fas fa-xmark"></i></button><div id="tcmPrevIn"></div></div>
            </div>
        </div>
        <div class="tcm-f-foot">
            <button type="button" class="tc-btn ghost" data-tcm-close>Cancel</button>
            <button type="submit" class="tc-btn dark" id="tcmSubmit"><i class="fas fa-paper-plane"></i> Post Now</button>
        </div>
    </form>
</div>
<datalist id="tcLocList"><?php foreach ($locOptions as $o): ?><option value="<?= e($o) ?>"><?php endforeach; ?></datalist>
<datalist id="tcClassList"><?php foreach ($classOptions as $o): ?><option value="<?= e($o) ?>"><?php endforeach; ?></datalist>
<datalist id="tcSubjList"><?php foreach ($subjectOptions as $o): ?><option value="<?= e($o) ?>"><?php endforeach; ?></datalist>
<?php endif; ?>

<!-- ছবি বড় করে দেখা -->
<div class="tcm-ov" id="tcZoom" style="background:rgba(0,0,0,.9);cursor:zoom-out;"><img alt="" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:6px;" /></div>

<!-- ======================= APPLY MODAL (Teacher → Student) ======================= -->
<?php if ($isLoggedIn && $isTeacher): ?>
<div class="modal fade" id="applyCircularModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center"><i class="fas fa-paper-plane me-1" style="color:#0F4C5C;"></i> এই টিউশনে Apply করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:13.5px; color:#64748B; margin-bottom:14px;">
                    <i class="fas fa-info-circle" style="color:#0F4C5C;"></i>
                    আপনার অ্যাপ্লিকেশন সরাসরি পোস্টদাতার ড্যাশবোর্ডে যাবে। আপনার প্রোফাইল ও নম্বর দেখে তিনি আপনার সাথে যোগাযোগ করবেন।
                </p>
                <input type="hidden" id="applyPostId" />
                <label class="form-label fw-bold" style="font-size:13px; color:#0F1B2D;">মোবাইল নম্বর <span style="color:#e74c3c;">*</span></label>
                <input type="tel" id="applyPhone" class="form-control mb-3" maxlength="11" inputmode="numeric" placeholder="01XXXXXXXXX" value="<?= e(preg_replace('/\D/', '', $myPhone)) ?>" />
                <label class="form-label fw-bold" style="font-size:13px; color:#0F1B2D;">কিছু বলুন <span style="color:#7A8CA0; font-weight:400;">(ঐচ্ছিক)</span></label>
                <textarea id="applyMessage" class="form-control" rows="4" maxlength="1000" placeholder="আপনার অভিজ্ঞতা, কোন সময়ে পড়াতে পারবেন, বেতন ইত্যাদি..."></textarea>
                <button class="btn-post" id="applySubmitBtn" onclick="submitCircularApply()" style="background:#0F4C5C;"><i class="fas fa-paper-plane me-1"></i> Send Application</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php section_start('Scripts'); ?>
    <script>
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
                + '<div class="fbc' + (isReply ? ' is-reply' : '') + '" id="cfbc-' + c.id + '" data-id="' + c.id + '" data-root="' + rootId + '" data-name="' + cfbcEsc(c.userFullName) + '" data-raw="' + cfbcEsc(c.content) + '">'
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
                + '      <button type="button" data-cm-edit>Edit</button><button type="button" data-cm-del class="fbc-del">Delete</button>'
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
            el.textContent = n + 1;
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
        // ============================================================
        // নতুন ডিজাইন: পোস্ট লেখা, ফিল্টার, মেনু, ভিউ গণনা
        // ============================================================
        (function () {
            var LOGGED_IN = <?= js($isLoggedIn) ?>, LOGIN_URL = <?= js($loginUrl) ?>;
            function $(s, r) { return (r || document).querySelector(s); }
            function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
            function token() { return $('input[name="__RequestVerificationToken"]').value; }
            function say(m) { if (window.pcToast) window.pcToast(m); else alert(m); }

            // ----- মোবাইলে ফিল্টার খোলা/বন্ধ -----
            var fl = $('#tcFilter'), ft = $('#tcFToggle');
            if (ft) ft.addEventListener('click', function () { var o = fl.classList.toggle('open'); ft.setAttribute('aria-expanded', o ? 'true' : 'false'); });
            $$('.tc-sel select').forEach(function (s) { s.addEventListener('change', function () { s.classList.toggle('empty', !s.value); }); });

            // ----- Sort -----
            var so = $('#tcSort');
            if (so) so.addEventListener('change', function () { location.href = '/Tuition/Circular' + so.value; });

            // ----- আরো দেখুন -----
            document.addEventListener('click', function (e) {
                var m = e.target.closest('[data-tc-more]'); if (!m) return;
                var d = m.previousElementSibling; var open = d.classList.toggle('clamp');
                m.textContent = open ? 'আরো দেখুন' : 'কম দেখুন';
            });

            // ----- ⋯ মেনু -----
            function closeMenus(ex) { $$('.tc-menu.open').forEach(function (m) { if (m !== ex) m.classList.remove('open'); }); }
            document.addEventListener('click', function (e) {
                var b = e.target.closest('[data-tc-menu]');
                if (b) { var m = b.nextElementSibling; closeMenus(m); m.classList.toggle('open'); return; }
                if (!e.target.closest('.tc-menu')) closeMenus();
            });
            document.addEventListener('click', async function (e) {
                var c = e.target.closest('[data-tc-copy]');
                if (c) {
                    closeMenus();
                    var url = location.origin + '/Tuition/Circular#circular-post-' + c.getAttribute('data-tc-copy');
                    try { await navigator.clipboard.writeText(url); say('লিংক কপি হয়েছে।'); } catch (err) { window.prompt('লিংকটি কপি করুন:', url); }
                    return;
                }
                var d = e.target.closest('[data-tc-del]');
                if (d) {
                    closeMenus();
                    if (!confirm('পোস্টটি মুছে ফেলবেন? সব কমেন্ট ও অ্যাপ্লিকেশনও মুছে যাবে।')) return;
                    var fd = new FormData(); fd.append('postId', d.getAttribute('data-tc-del')); fd.append('__RequestVerificationToken', token());
                    try {
                        var r = await (await fetch('/Tuition/DeleteCircularPost', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                        say(r.message || (r.success ? 'Deleted' : 'Failed'));
                        if (r.success) { var card = document.getElementById('circular-post-' + d.getAttribute('data-tc-del')); if (card) card.remove(); }
                    } catch (err) { say('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'); }
                }
            });

            // ----- ছবি বড় করে দেখা -----
            var zoom = $('#tcZoom');
            document.addEventListener('click', function (e) {
                var im = e.target.closest('[data-tc-zoom]');
                if (im) { $('img', zoom).src = im.src; zoom.classList.add('open'); return; }
                if (e.target.closest('#tcZoom')) zoom.classList.remove('open');
            });

            // ----- লিংকে আসা পোস্ট হাইলাইট -----
            var m = /^#circular-post-(\d+)$/.exec(location.hash);
            if (m) {
                var card = document.getElementById('circular-post-' + m[1]);
                if (card) setTimeout(function () { card.scrollIntoView({ behavior: 'smooth', block: 'start' }); card.classList.add('flash'); setTimeout(function () { card.classList.remove('flash'); }, 2200); }, 200);
            }

            // ----- ভিউ গণনা: পোস্টটা স্ক্রিনে ভালোভাবে দেখা গেলে (একই সেশনে একবার) -----
            if ('IntersectionObserver' in window) {
                var queue = [], timer = null, done = {};
                function flush() {
                    if (!queue.length) return;
                    var fd = new FormData(); fd.append('ids', queue.join(',')); fd.append('__RequestVerificationToken', token());
                    queue = [];
                    fetch('/Tuition/ViewCircular', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }, keepalive: true }).catch(function () { });
                }
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (en) {
                        var id = en.target.getAttribute('data-id');
                        if (en.isIntersecting && !done[id]) { done[id] = 1; queue.push(id); io.unobserve(en.target); }
                    });
                    clearTimeout(timer); timer = setTimeout(flush, 1500);
                }, { threshold: 0.5 });
                $$('.tc-post[data-id]').forEach(function (p) { io.observe(p); });
                window.addEventListener('pagehide', flush);
            }

            // ----- পোস্ট লেখা -----
            var ov = $('#tcmOv'), form = $('#tcmForm'), quick = $('#tcQuick');
            function openComposer(what) {
                if (!LOGGED_IN) { location.href = LOGIN_URL; return; }
                if (!ov.classList.contains('open')) {
                    if (quick.value.trim() && !$('#tcmContent').value.trim()) $('#tcmContent').value = quick.value.trim();
                    $('#tcmErr').classList.remove('show');
                    ov.classList.add('open'); document.documentElement.style.overflow = 'hidden';
                }
                var target = { photo: null, video: null, location: '#tcmLoc', subject: '#tcmSubj' }[what];
                if (what === 'photo') $('#tcmImg').click();
                else if (what === 'video') $('#tcmVid').click();
                setTimeout(function () {
                    var el = target ? $(target) : (!$('#tcmTitle').value.trim() ? $('#tcmTitle') : $('#tcmContent'));
                    if (el && what !== 'photo' && what !== 'video') el.focus();
                }, 60);
            }
            function closeComposer() { ov.classList.remove('open'); document.documentElement.style.overflow = ''; }
            document.addEventListener('click', function (e) {
                var o = e.target.closest('[data-tc-open]');
                if (o && o.tagName !== 'INPUT') { e.preventDefault(); openComposer(o.getAttribute('data-tc-open')); }
            });
            if (quick) {
                quick.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); openComposer('text'); } });
                if (!LOGGED_IN) quick.addEventListener('click', function () { location.href = LOGIN_URL; });
            }
            if (!ov) return;
            ov.addEventListener('click', function (e) { if (e.target === ov || e.target.closest('[data-tcm-close]')) closeComposer(); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeComposer(); zoom.classList.remove('open'); } });

            // ছবি / ভিডিও (একটাই)
            var img = $('#tcmImg'), vid = $('#tcmVid'), prev = $('#tcmPrev'), prevIn = $('#tcmPrevIn'), objUrl = null;
            function clearMedia() { img.value = ''; vid.value = ''; prev.classList.remove('show'); prevIn.innerHTML = ''; if (objUrl) { URL.revokeObjectURL(objUrl); objUrl = null; } }
            function showMedia(file, isVideo) {
                if (objUrl) URL.revokeObjectURL(objUrl);
                objUrl = URL.createObjectURL(file);
                prevIn.innerHTML = isVideo ? '<video controls preload="metadata"></video><span class="nm"></span>' : '<img alt="Preview" />';
                if (isVideo) { $('video', prevIn).src = objUrl; $('.nm', prevIn).textContent = file.name + ' (' + (file.size / 1048576).toFixed(1) + ' MB)'; }
                else $('img', prevIn).src = objUrl;
                prev.classList.add('show');
            }
            img.addEventListener('change', function () {
                var f = img.files[0]; if (!f) return;
                if (!/^image\/(jpeg|png|gif|webp)$/.test(f.type)) { say('শুধু JPG, PNG, GIF বা WEBP ছবি।'); img.value = ''; return; }
                if (f.size > 5 * 1024 * 1024) { say('ছবি ৫ MB এর কম হতে হবে।'); img.value = ''; return; }
                vid.value = ''; showMedia(f, false);
            });
            vid.addEventListener('change', function () {
                var f = vid.files[0]; if (!f) return;
                if (!/\.(mp4|webm|mov|m4v)$/i.test(f.name)) { say('শুধু MP4, WEBM বা MOV ভিডিও।'); vid.value = ''; return; }
                if (f.size > 25 * 1024 * 1024) { say('ভিডিও ২৫ MB এর কম হতে হবে।'); vid.value = ''; return; }
                img.value = ''; showMedia(f, true);
            });
            $('#tcmPrevX').addEventListener('click', clearMedia);

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                var err = $('#tcmErr'), btn = $('#tcmSubmit');
                err.classList.remove('show');
                if (!$('#tcmTitle').value.trim()) { err.textContent = 'শিরোনাম লিখুন।'; err.classList.add('show'); $('#tcmTitle').focus(); return; }
                if (!$('#tcmContent').value.trim()) { err.textContent = 'টিউশনের বিস্তারিত লিখুন।'; err.classList.add('show'); $('#tcmContent').focus(); return; }
                var fd = new FormData(form);
                if (img.files[0]) fd.append('imageFile', img.files[0]);
                if (vid.files[0]) fd.append('videoFile', vid.files[0]);
                fd.append('__RequestVerificationToken', token());
                var old = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                try {
                    var res = await fetch('/Tuition/CreateCircularPost', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    var d = null; try { d = await res.json(); } catch (x) { }
                    if (!d) { err.textContent = res.status === 413 ? 'ফাইল অনেক বড় — ছোট ফাইল দিন।' : 'সার্ভার থেকে উত্তর পাওয়া যায়নি। ফাইল বড় হলে ছোট ফাইল দিয়ে চেষ্টা করুন।'; err.classList.add('show'); return; }
                    if (!d.success) { err.textContent = d.message || 'পোস্ট করা যায়নি।'; err.classList.add('show'); err.scrollIntoView({ block: 'nearest' }); return; }
                    form.reset(); clearMedia(); if (quick) quick.value = '';
                    closeComposer();
                    location.href = '/Tuition/Circular?_r=' + Date.now().toString(36) + '#circular-post-' + d.id;
                } catch (x) { err.textContent = 'ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'; err.classList.add('show'); }
                finally { btn.disabled = false; btn.innerHTML = old; }
            });
        })();
    </script>
<?php partial('Shared/_FeedCommentEdit', ['prefix' => 'cfbc-', 'editUrl' => '/Tuition/EditCircularComment', 'delUrl' => '/Tuition/DeleteCircularComment', 'countPrefix' => 'comment-count-', 'countSuffix' => '', 'postPrefix' => 'circular-post-']); ?>
<?php section_end(); ?>
