<?php
/**
 * টিচারের পূর্ণ ডিটেইলস পেজ (Tutor List → See)
 * তথ্য আসে টিচার হওয়ার আবেদন ফর্ম / Teacher → Tutor Details থেকে (Db::GetTeacherDetails)। পুরোনো টিচারের ফাঁকা ঘরে আবেদনের তথ্য দেখায়।
 * ফোন / ইমেইল কখনো দেখায় না; ঠিকানা শুধু টিচার নিজে আর অ্যাডমিন দেখে।
 * @var Obj $Model  GetTutorDirectory() এর টিউটর
 */
$t = $Model; $d = $ViewBag->D; $u = $ViewBag->User;
$isOwner = !empty($ViewBag->IsOwner); $canPrivate = $isOwner || !empty($ViewBag->IsAdmin);
$me = current_user_id(); $role = current_role();
$rs = $ViewBag->RStats; $st = $ViewBag->Stats;
$na = '<span class="tp-na">N/A</span>';
$val = fn($v) => trim((string)$v) !== '' ? e($v) : $na;
$chips = function (array $list) use ($na) { $list = array_values(array_filter($list, fn($x) => trim((string)$x) !== '')); return $list ? implode('', array_map(fn($x) => '<span class="tp-chip">' . e($x) . '</span>', $list)) : $na; };
$tm = fn($v) => $v !== '' ? date('h:i A', strtotime($v)) : '';
// ফাঁকা থাকলে আবেদনের / ডিরেক্টরির তথ্য
$univ = $d['university'] !== '' ? $d['university'] : ($t->Institution !== 'Not specified' ? $t->Institution : '');
$subjects = $d['subjects'] ?: array_values(array_filter($t->Subjects, fn($x) => $x !== 'General'));
$classes = $d['classes'] ?: array_values(array_filter($t->Classes, fn($x) => $x !== 'All Classes'));
$mediums = $d['mediums'] ?: $t->Mediums;
$methods = $d['methods'] ?: ($t->TeachingMode === 'Online' ? ['Online Tutoring'] : ($t->TeachingMode === 'Home' ? ['Home Tutoring'] : ($t->TeachingMode === 'Both' && $t->TutorId !== '' ? ['Home Tutoring', 'Online Tutoring'] : [])));
$city = $d['livingCity'] !== '' ? $d['livingCity'] : $t->City;
$salary = $d['expectedSalary'] !== '' ? $d['expectedSalary'] : ($t->MonthlyFee ? (string)$t->MonthlyFee : '');
$exp = $d['experienceYears'] !== '' ? $d['experienceYears'] : ($t->ExperienceYears ? $t->ExperienceYears . ' Year(s)' : '');
$preface = $d['preface'] !== '' ? $d['preface'] : ($d['whyHire'] === '' ? (string)$t->About : '');
$gender = $d['gender'] !== '' ? $d['gender'] : (in_array($t->Gender, ['Male', 'Female'], true) ? $t->Gender : '');
$reqHref = '/FindTutor?req=' . (int)$t->UserId;
$canRequest = !$isOwner && $role !== 'Teacher' && $role !== 'Admin' && $t->TutorId !== '';
$ViewData['Title'] = $t->FullName;
$ViewData['FullTitle'] = $t->FullName . ' — Tutor Details | মাষ্টার মশাই';
$ViewData['Description'] = $t->FullName . ' — ' . ($univ ?: 'Tutor') . '। ' . ($subjects ? implode(', ', array_slice($subjects, 0, 5)) . ' পড়ান। ' : '') . ($city ? "$city এ।" : '');
if ($t->PhotoUrl !== '') $ViewData['OgImage'] = $t->PhotoUrl;
?>
<?php partial('Tutors/_Styles'); ?>
<style>
    .tp { background: #F3F6FA; padding: 26px 0 60px; }
    .tp-wrap { max-width: 1000px; margin: 0 auto; padding: 0 16px; }
    .tp-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(15,27,45,.05); padding: 26px 28px; margin-bottom: 18px; }
    .tp-ttl { display: flex; align-items: center; gap: 14px; margin: 0 0 18px; font-size: 17px; font-weight: 700; color: #1F2937; }
    .tp-ttl::after { content: ''; flex: 1; height: 1px; background: #E5E7EB; }
    .tp-head { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 30px; }
    .tp-id { text-align: center; }
    .tp-id .tl-pic { width: 92px; height: 92px; margin: 0 auto; font-size: 28px; }
    .tp-id h1 { margin: 8px 0 0; font-size: 20px; font-weight: 700; color: #1F2937; }
    .tp-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; margin-top: 20px; text-align: left; font-size: 13px; }
    .tp-meta b { display: block; color: #1F2937; font-weight: 700; }
    .tp-meta span { color: #6B7280; word-break: break-word; }
    .tp-meta .eye { color: #00B5E0; }
    .tp-about h3 { margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #1F2937; }
    .tp-about p { margin: 0 0 16px; font-size: 13.5px; color: #4B5563; line-height: 1.6; white-space: pre-line; }
    .tp-bar { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
    .tp-bar p { margin: 0; font-size: 14px; color: #374151; }
    .tp-acts { display: flex; gap: 10px; flex-wrap: wrap; }
    .tp-btn { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 18px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; border: 1px solid #DCE5EF; background: #fff; color: #1F2937 !important; cursor: pointer; font-family: inherit; }
    .tp-btn:hover { background: #F1F5F9; }
    .tp-btn.pri { background: #00CFFF; border-color: #00CFFF; color: #fff !important; }
    .tp-btn.pri:hover { background: #00B5E0; }
    .tp-btn.on { background: #E6F9FF; border-color: #00CFFF; color: #0369A1 !important; }
    .tp-rows { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 12px 20px; font-size: 13.5px; align-items: start; }
    .tp-rows > b { color: #1F2937; font-weight: 600; padding-top: 3px; }
    .tp-rows > div { color: #374151; padding-top: 3px; }
    .tp-rows .gap { grid-column: 1 / -1; height: 8px; }
    .tp-chip { display: inline-block; border: 1px solid #00CFFF; color: #0F172A; border-radius: 999px; padding: 3px 12px; font-size: 12.5px; margin: 0 6px 6px 0; background: #fff; }
    .tp-na { color: #9CA3AF; }
    .tp-sub { font-size: 12.5px; color: #6B7280; margin: -8px 0 10px; }
    .tp-tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .tp-tbl th { background: #2B2F36; color: #fff; text-align: left; padding: 11px 12px; font-weight: 600; white-space: nowrap; }
    .tp-tbl td { padding: 11px 12px; background: #F1F3F6; border-bottom: 2px solid #fff; color: #374151; }
    .tp-tbl tr:nth-child(even) td { background: #E7EAEE; }
    .tp-tblw { overflow-x: auto; margin-bottom: 8px; }
    .tp-addr { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .tp-addr b { display: block; font-size: 14px; margin-bottom: 8px; color: #1F2937; }
    .tp-addr div div { border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px 12px; font-size: 13px; color: #4B5563; min-height: 44px; }
    .tp-sech { font-size: 22px; font-weight: 600; color: #1F2937; margin: 34px 0 16px; font-family: var(--font-heading); display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .tp-track { display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - 3 * 18px) / 4); gap: 18px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; scrollbar-width: none; padding: 2px; }
    .tp-track::-webkit-scrollbar { display: none; }
    .tp-track > .tl-card { scroll-snap-align: start; }
    .tp-nav { display: flex; justify-content: center; gap: 10px; margin-top: 14px; }
    .tp-nav button { width: 26px; height: 26px; border-radius: 50%; border: none; background: #9CA3AF; color: #fff; cursor: pointer; font-size: 11px; }
    .tp-nav button:hover { background: #00B5E0; }
    .tp-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 14px; }
    .tp-stat { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(15,27,45,.05); padding: 16px; display: flex; gap: 12px; align-items: center; }
    .tp-stat i { width: 40px; height: 40px; border-radius: 6px; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .tp-stat b { display: block; font-size: 20px; color: #1F2937; line-height: 1.1; }
    .tp-stat span { font-size: 11.5px; color: #6B7280; }
    .tp-rev { display: grid; grid-template-columns: 200px minmax(0, 1fr) auto; gap: 30px; align-items: center; }
    .tp-avg { border: 1px solid #E5E7EB; border-radius: 8px; text-align: center; padding: 18px 10px; box-shadow: 0 2px 10px rgba(15,27,45,.05); }
    .tp-avg b { font-size: 34px; color: #1F2937; display: block; line-height: 1.1; }
    .tp-stars { color: #F59E0B; font-size: 13px; letter-spacing: 1px; }
    .tp-stars .off { color: #D1D5DB; }
    .tp-avg small { display: block; color: #6B7280; font-size: 12px; margin-top: 4px; }
    .tp-dist div { display: flex; align-items: center; gap: 10px; font-size: 12px; color: #6B7280; margin-bottom: 6px; }
    .tp-dist div span { flex: 1; height: 9px; background: #E5E7EB; border-radius: 6px; overflow: hidden; }
    .tp-dist div span i { display: block; height: 100%; background: #00CFFF; border-radius: 6px; }
    .tp-revbtn { background: #1F2328; color: #fff !important; border: none; height: 36px; padding: 0 16px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; font-family: inherit; white-space: nowrap; }
    .tp-revbtn:hover { background: #00B5E0; }
    .tp-revnote { font-size: 12px; color: #6B7280; max-width: 200px; margin-top: 6px; }
    .tp-rlist { margin-top: 22px; display: grid; gap: 12px; }
    .tp-r { border: 1px solid #EEF2F6; border-radius: 10px; padding: 14px 16px; display: flex; gap: 12px; }
    .tp-r .av { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #08224A, #0A5A86); color: #fff; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; flex-shrink: 0; }
    .tp-r .av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .tp-r b { font-size: 14px; color: #1F2937; }
    .tp-r small { color: #9CA3AF; font-size: 12px; margin-left: 6px; }
    .tp-r p { margin: 4px 0 0; font-size: 13.5px; color: #4B5563; white-space: pre-line; }
    .tp-empty { text-align: center; font-weight: 700; color: #1F2937; margin: 26px 0 6px; }
    .tp-hire { text-align: center; }
    .tp-hire .tp-btn.pri { height: 44px; padding: 0 30px; border-radius: 999px; font-size: 14.5px; }
    .tp-hire p { margin: 18px 0 0; padding-top: 16px; border-top: 1px solid #E5E7EB; font-size: 13px; color: #4B5563; }
    .tp-dlg { position: fixed; inset: 0; z-index: 2000; background: rgba(15,23,42,.5); display: none; align-items: center; justify-content: center; padding: 16px; }
    .tp-dlg.open { display: flex; }
    .tp-dlg form { background: #fff; border-radius: 16px; width: min(440px, 100%); padding: 22px; }
    .tp-dlg h3 { margin: 0 0 12px; font-size: 18px; font-weight: 800; color: #0F172A; }
    .tp-pick { display: flex; gap: 6px; font-size: 30px; margin-bottom: 12px; }
    .tp-pick button { border: none; background: none; color: #D1D5DB; cursor: pointer; padding: 0; line-height: 1; }
    .tp-pick button.on { color: #F59E0B; }
    .tp-dlg textarea { width: 100%; border: 1px solid #DCE5EF; border-radius: 10px; padding: 10px 12px; font-family: inherit; font-size: 14px; resize: vertical; min-height: 96px; outline: none; }
    .tp-dlg textarea:focus { border-color: #00CFFF; }
    .tp-dlg .acts { display: flex; gap: 10px; margin-top: 14px; }
    .tp-dlg .acts button { flex: 1; height: 42px; border-radius: 10px; border: none; font-weight: 800; cursor: pointer; font-family: inherit; }
    .tp-dlg .ok { background: #00CFFF; color: #fff; } .tp-dlg .no { background: #F1F5F9; color: #334155; }
    .tp-dlg .err { color: #DC2626; font-size: 13px; margin-top: 8px; min-height: 1px; }
    @media (max-width: 860px) {
        .tp-head { grid-template-columns: minmax(0, 1fr); gap: 22px; }
        .tp-rows { grid-template-columns: minmax(0, 1fr); gap: 2px; }
        .tp-rows > div { margin-bottom: 10px; }
        .tp-rows .gap { display: none; }
        .tp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .tp-rev { grid-template-columns: minmax(0, 1fr); gap: 18px; }
        .tp-addr { grid-template-columns: minmax(0, 1fr); }
        .tp-track { grid-auto-columns: calc((100% - 18px) / 2); }
    }
    @media (max-width: 640px) { .tp-card { padding: 18px 16px; } .tp-wrap { padding: 0 10px; } }
</style>

<div class="tp">
    <div class="tp-wrap">
        <?php if ($isOwner && !empty($TempData['Success'])): ?><div class="alert alert-success"><?= e($TempData['Success']) ?></div><?php endif; ?>

        <!-- ===== পরিচয় ===== -->
        <div class="tp-card">
            <div class="tp-head">
                <div class="tp-id">
                    <span class="tl-pic"><span><?= e($t->Initials) ?></span><?php if ($t->PhotoUrl !== ''): ?><img src="<?= e($t->PhotoUrl) ?>" alt="<?= e($t->FullName) ?>" onerror="this.remove()" /><?php endif; ?></span>
                    <div class="tl-badges">
                        <?php if ($t->IsPremium): ?><i class="fas fa-crown pr" title="Premium Tutor"></i><?php endif; ?>
                        <?php if ($t->IsTopRated): ?><i class="fas fa-award tp" title="Top Rated"></i><?php endif; ?>
                        <i class="fas fa-circle-check vf" title="Verified Tutor"></i>
                    </div>
                    <h1><?= e($t->FullName) ?></h1>
                    <div class="tp-meta">
                        <div><b>University :</b><span><?= $val($univ) ?></span></div>
                        <div><b>Tutor ID :</b><span><?= $val($t->TutorId) ?></span></div>
                        <div><b>Department :</b><span><?= $val($d['department']) ?></span></div>
                        <div><b>Profile Views :</b><span><i class="fas fa-eye eye"></i> <?= number_format((int)$ViewBag->Views) ?></span></div>
                    </div>
                </div>
                <div class="tp-about">
                    <h3>Preface</h3><p><?= $preface !== '' ? e($preface) : $na ?></p>
                    <h3>Tuition Job Experience</h3><p><?= $val($d['tuitionExperience']) ?></p>
                    <h3>Reasons to be Getting Hired</h3><p><?= $val($d['whyHire']) ?></p>
                </div>
            </div>
        </div>

        <!-- ===== অ্যাকশন ===== -->
        <div class="tp-card tp-bar">
            <?php if ($isOwner): ?>
                <p><i class="fas fa-circle-info" style="color:#00B5E0;"></i> এটা আপনার ডিটেইলস পেজ — স্টুডেন্টরা এভাবেই দেখে।</p>
                <div class="tp-acts"><a class="tp-btn pri" href="/Teacher/EditDetails"><i class="fas fa-pen"></i> Edit Details</a><a class="tp-btn" href="/Profile/Show"><i class="fas fa-user"></i> My Profile</a></div>
            <?php else: ?>
                <p>পছন্দ হলে সরাসরি রিকোয়েস্ট পাঠান, অথবা পরে দেখার জন্য সেভ করে রাখুন।</p>
                <div class="tp-acts">
                    <?php if ($canRequest): ?><a class="tp-btn pri" href="<?= e($reqHref) ?>"><i class="fas fa-paper-plane"></i> Direct Request</a><?php endif; ?>
                    <?php if ($me !== null && $role !== 'Admin'): ?>
                        <button type="button" class="tp-btn<?= $ViewBag->Saved ? ' on' : '' ?>" data-save-type="tutor" data-save-id="<?= (int)$t->UserId ?>"><i class="<?= $ViewBag->Saved ? 'fas' : 'far' ?> fa-bookmark"></i> <span><?= $ViewBag->Saved ? 'Saved' : 'Bookmark' ?></span></button>
                    <?php elseif ($me === null): ?>
                        <a class="tp-btn" href="/Account/Login?returnUrl=<?= rawurlencode('/Tutors/Profile/' . (int)$t->UserId) ?>"><i class="far fa-bookmark"></i> Bookmark</a>
                    <?php endif; ?>
                    <a class="tp-btn" href="/Profile/Show/<?= (int)$t->UserId ?>"><i class="fas fa-user"></i> Profile</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== Tutoring Information ===== -->
        <div class="tp-card">
            <h2 class="tp-ttl">Tutoring Information</h2>
            <div class="tp-rows">
                <b>Living Country :</b><div><?= $val($d['livingCountry'] !== '' ? $d['livingCountry'] : 'Bangladesh') ?></div>
                <b>Living City :</b><div><?= $val($city) ?></div>
                <b>Living Location :</b><div><?= $val($d['livingArea']) ?></div>
                <b>Preferred Tutoring Location :</b><div><?= $chips($d['preferredAreas']) ?></div>
                <span class="gap"></span>
                <b>Preferred Tutoring Category :</b><div><?= $chips($mediums) ?></div>
                <b>Preferred Tutoring Classes &amp; Courses :</b><div><?= $chips($classes) ?></div>
                <b>Preferred Tutoring Subjects :</b><div><?= $chips($subjects) ?></div>
                <span class="gap"></span>
                <b>Preferred Tutoring Method :</b><div><?= $chips($methods) ?></div>
                <b>Availability Day :</b><div><?= $chips($d['days']) ?></div>
                <b>Available From :</b><div><?= $d['timeFrom'] !== '' ? e($tm($d['timeFrom'])) : ($d['timeTo'] === '' && $t->AvailableTime !== '' && $t->AvailableTime !== 'Contact for schedule' ? e($t->AvailableTime) : $na) ?></div>
                <b>Available To :</b><div><?= $val($tm($d['timeTo'])) ?></div>
                <span class="gap"></span>
                <b>Tutoring Experience :</b><div><?= $val($exp) ?></div>
                <b>Expected Salary :</b><div><?= $salary !== '' ? e($salary) . ' Tk' : $na ?></div>
            </div>
        </div>

        <!-- ===== শিক্ষা ===== -->
        <div class="tp-card">
            <h2 class="tp-ttl">Educational Information</h2>
            <div class="tp-sub">Undergraduate Info :</div>
            <div class="tp-tblw"><table class="tp-tbl">
                <tr><th>Exam Title</th><th>Institute</th><th>Board</th><th>Group</th><th>Year</th><th>GPA</th></tr>
                <?php foreach (['ssc' => 'SSC / O Level / Dakhil', 'hsc' => 'HSC / A Level / Alim'] as $k => $lbl): ?>
                    <tr><td><b><?= e($d[$k . 'Title'] !== '' ? $d[$k . 'Title'] : $lbl) ?></b></td><td><?= $val($d[$k . 'Institute']) ?></td><td><?= $val($d[$k . 'Board']) ?></td><td><?= $val($d[$k . 'Group']) ?></td><td><?= $val($d[$k . 'Year']) ?></td><td><?= $val($d[$k . 'Result']) ?></td></tr>
                <?php endforeach; ?>
            </table></div>
            <div class="tp-sub" style="margin-top:16px;">Graduation Info :</div>
            <div class="tp-tblw"><table class="tp-tbl">
                <tr><th>Exam Title</th><th>Institute</th><th>Study Type</th><th>Department</th><th>Year</th><th>CGPA</th></tr>
                <?php foreach (['grad' => 'Graduation / Diploma', 'post' => 'Postgraduate / Masters'] as $k => $lbl): ?>
                    <tr><td><b><?= e($d[$k . 'Title'] !== '' ? $d[$k . 'Title'] : $lbl) ?></b></td><td><?= $val($k === 'grad' && $d['gradInstitute'] === '' ? $univ : $d[$k . 'Institute']) ?></td><td><?= $val($d[$k . 'StudyType']) ?></td><td><?= $val($k === 'grad' && $d['gradDepartment'] === '' ? $d['department'] : $d[$k . 'Department']) ?></td><td><?= $val($d[$k . 'Year']) ?></td><td><?= $val($d[$k . 'Result']) ?></td></tr>
                <?php endforeach; ?>
            </table></div>
        </div>

        <!-- ===== ব্যক্তিগত ===== -->
        <div class="tp-card">
            <h2 class="tp-ttl">Personal Information</h2>
            <div class="tp-rows">
                <b>Gender :</b><div><?= $val($gender) ?></div>
                <b>Blood Group :</b><div><?= $val($d['bloodGroup']) ?></div>
                <b>Religion :</b><div><?= $val($d['religion']) ?></div>
                <span class="gap"></span>
                <b>Nationality :</b><div><?= $val($d['nationality']) ?></div>
                <b>Date of Birth :</b><div><?= $d['dob'] !== '' ? e(date('d M, Y', strtotime($d['dob']))) : $na ?></div>
                <span class="gap"></span>
                <b>Member Since :</b><div><?= !empty($u->CreatedAt) ? e(date('d M, Y', strtotime((string)$u->CreatedAt))) : $na ?></div>
                <b>Last Active :</b><div><?= !empty($ViewBag->LastSeen) ? e(date('d M, Y', strtotime((string)$ViewBag->LastSeen))) : $na ?></div>
            </div>
        </div>

        <?php if ($canPrivate): ?>
            <div class="tp-card">
                <div class="tp-addr">
                    <div><b>Present Full Address :</b><div><?= $val($d['presentAddress']) ?></div></div>
                    <div><b>Permanent Full Address :</b><div><?= $val($d['permanentAddress']) ?></div></div>
                </div>
                <div style="font-size:12px; color:#92400E; margin-top:10px;"><i class="fas fa-lock"></i> ঠিকানা শুধু <?= $isOwner ? 'আপনি' : 'টিচার নিজে' ?> আর অ্যাডমিন দেখতে পান — অন্যরা দেখে না।</div>
            </div>
        <?php endif; ?>

        <!-- ===== সম্পর্কিত টিউটর ===== -->
        <?php if ($ViewBag->Related): ?>
            <div class="tp-sech">Related Tutors <a class="tl-btn" href="/Tutors/All<?= $city !== '' && in_array($city, TutorsController::CITIES, true) ? '?city[]=' . rawurlencode($city) : '' ?>">See All <i class="fas fa-caret-right"></i></a></div>
            <div class="tp-track" id="tpRel"><?php foreach ($ViewBag->Related as $x) partial('Tutors/_Card', ['t' => $x]); ?></div>
            <?php if (count($ViewBag->Related) > 2): ?><div class="tp-nav"><button type="button" data-dir="-1" aria-label="আগের"><i class="fas fa-chevron-left"></i></button><button type="button" data-dir="1" aria-label="পরের"><i class="fas fa-chevron-right"></i></button></div><?php endif; ?>
        <?php endif; ?>

        <!-- ===== প্ল্যাটফর্মে কার্যক্রম ===== -->
        <div class="tp-sech">Platform Status</div>
        <div class="tp-stats">
            <div class="tp-stat"><i class="fas fa-file-signature" style="background:#22C55E;"></i><div><b><?= (int)$st['applied'] ?></b><span>Applied</span></div></div>
            <div class="tp-stat"><i class="fas fa-envelope-open-text" style="background:#84CC16;"></i><div><b><?= (int)$st['requests'] ?></b><span>Direct Requests</span></div></div>
            <div class="tp-stat"><i class="fas fa-user-check" style="background:#00B5E0;"></i><div><b><?= (int)$st['selected'] ?></b><span>Selected</span></div></div>
            <div class="tp-stat"><i class="fas fa-user-graduate" style="background:#6366F1;"></i><div><b><?= (int)$st['students'] ?></b><span>Students</span></div></div>
            <div class="tp-stat"><i class="fas fa-circle-xmark" style="background:#EF4444;"></i><div><b><?= (int)$st['notSelected'] ?></b><span>Not Selected</span></div></div>
        </div>

        <?php if ($ViewBag->Cv !== '' && ($me !== null)): ?>
            <div class="tp-card tp-bar" style="margin-top:22px;">
                <p><i class="fas fa-file-pdf" style="color:#DC2626; font-size:22px; vertical-align:middle;"></i> টিচারের CV দেখতে পারেন।</p>
                <a class="tp-btn pri" href="<?= e($ViewBag->Cv) ?>" target="_blank" rel="noopener nofollow"><i class="fas fa-download"></i> View CV</a>
            </div>
        <?php endif; ?>

        <!-- ===== রেটিং ও রিভিউ ===== -->
        <div class="tp-card" style="margin-top:22px;" id="reviews">
            <h2 class="tp-ttl">Ratings and Reviews</h2>
            <div class="tp-rev">
                <div class="tp-avg">
                    <b><?= number_format((float)$rs['avg'], 2) ?></b>
                    <div class="tp-stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star<?= $i <= round($rs['avg']) ? '' : ' off' ?>"></i><?php endfor; ?></div>
                    <small>Reviews(<?= (int)$rs['count'] ?>)</small>
                </div>
                <div class="tp-dist">
                    <?php foreach ([5, 4, 3, 2, 1] as $n): $pct = $rs['count'] ? round($rs['dist'][$n] / $rs['count'] * 100) : 0; ?>
                        <div><?= $n ?> <span><i style="width:<?= $pct ?>%"></i></span> <?= (int)$rs['dist'][$n] ?></div>
                    <?php endforeach; ?>
                </div>
                <div>
                    <?php if (!empty($ViewBag->CanReview)): ?>
                        <button type="button" class="tp-revbtn" id="tpRevBtn"><i class="fas fa-star" style="color:#F59E0B;"></i> <?= $ViewBag->MyReview ? 'আমার রেটিং বদলান' : 'Rate This Tutor' ?></button>
                    <?php elseif ($me === null): ?>
                        <a class="tp-revbtn" style="display:inline-flex; align-items:center; text-decoration:none;" href="/Account/Login?returnUrl=<?= rawurlencode('/Tutors/Profile/' . (int)$t->UserId . '#rate') ?>">Make a Review</a>
                        <div class="tp-revnote">রিভিউ দিতে লগইন করুন।</div>
                    <?php elseif (!$isOwner && $role !== 'Admin'): ?>
                        <button type="button" class="tp-revbtn" disabled style="opacity:.5; cursor:default;">Make a Review</button>
                        <div class="tp-revnote">শুধু স্টুডেন্ট / ইউজার অ্যাকাউন্ট থেকে রেটিং দেওয়া যায়।</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!$ViewBag->Reviews): ?>
                <div class="tp-empty">No Review Available!</div>
            <?php else: ?>
                <div class="tp-rlist">
                    <?php foreach ($ViewBag->Reviews as $r): ?>
                        <div class="tp-r">
                            <span class="av"><?= e($r->Initials) ?><?php if ($r->Image): ?><img src="<?= e($r->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span>
                            <div>
                                <b><?= e($r->Name) ?></b><small><?= e($r->TimeAgo) ?></small>
                                <div class="tp-stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star<?= $i <= $r->Rating ? '' : ' off' ?>"></i><?php endfor; ?></div>
                                <?php if ($r->Comment !== ''): ?><p><?= e($r->Comment) ?></p><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== Hire ===== -->
        <?php if (!$isOwner): ?>
            <div class="tp-card tp-hire">
                <?php if ($canRequest): ?><a class="tp-btn pri" href="<?= e($reqHref) ?>"><i class="fas fa-paper-plane"></i> Request This Tutor</a><?php endif; ?>
                <p>এই টিউটর সম্পর্কে আরো জানতে Tutor ID "<b><?= e($t->TutorId) ?></b>" দিয়ে <a href="/Helpdesk">Helpdesk</a> এ যোগাযোগ করুন।</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($ViewBag->CanReview)): $mr = $ViewBag->MyReview; ?>
    <div class="tp-dlg" id="tpDlg" role="dialog" aria-modal="true" aria-labelledby="tpDlgT">
        <form id="tpRevForm">
            <h3 id="tpDlgT"><?= e($t->FullName) ?> কে রেটিং দিন</h3>
            <div class="tp-pick" id="tpPick"><?php for ($i = 1; $i <= 5; $i++): ?><button type="button" data-v="<?= $i ?>" aria-label="<?= $i ?> star"><i class="fas fa-star"></i></button><?php endfor; ?></div>
            <textarea id="tpComment" maxlength="1000" placeholder="টিচারের পড়ানো কেমন লাগলো? (ঐচ্ছিক)"><?= e($mr->Comment ?? '') ?></textarea>
            <div class="err" id="tpErr"></div>
            <div class="acts"><button type="button" class="no" id="tpNo">বাতিল</button><button type="submit" class="ok">Submit Review</button></div>
        </form>
    </div>
<?php endif; ?>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var tr = document.getElementById('tpRel');
    document.querySelectorAll('.tp-nav button').forEach(function (b) {
        b.addEventListener('click', function () { var dir = +b.getAttribute('data-dir'); if (dir > 0 && tr.scrollLeft + tr.clientWidth >= tr.scrollWidth - 4) tr.scrollTo({ left: 0 }); else tr.scrollBy({ left: dir * tr.clientWidth }); });
    });
    var dlg = document.getElementById('tpDlg'); if (!dlg) return;
    var rating = <?= (int)($ViewBag->MyReview->Rating ?? 0) ?>, pick = document.getElementById('tpPick');
    function paint() { pick.querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', +b.getAttribute('data-v') <= rating); }); }
    pick.addEventListener('click', function (e) { var b = e.target.closest('[data-v]'); if (b) { rating = +b.getAttribute('data-v'); paint(); } });
    paint();
    document.getElementById('tpRevBtn').addEventListener('click', function () { dlg.classList.add('open'); });
    // Tutor List কার্ডের স্টার থেকে এলে সাথে সাথে রেটিং বক্স খোলে
    if (location.hash === '#rate') { document.getElementById('reviews').scrollIntoView({ block: 'center' }); setTimeout(function () { dlg.classList.add('open'); }, 300); }
    document.getElementById('tpNo').addEventListener('click', function () { dlg.classList.remove('open'); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.classList.remove('open'); });
    document.getElementById('tpRevForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        var err = document.getElementById('tpErr');
        if (!rating) { err.textContent = '১ থেকে ৫ স্টার বাছাই করুন।'; return; }
        var fd = new FormData(); fd.append('teacherId', <?= (int)$t->UserId ?>); fd.append('rating', rating); fd.append('comment', document.getElementById('tpComment').value);
        fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
        try {
            var r = await (await fetch('/Tutor/SaveReview', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!r.success) { err.textContent = r.message || 'রিভিউ দেওয়া যায়নি।'; return; }
            location.hash = 'reviews'; location.reload();
        } catch (er) { err.textContent = 'নেটওয়ার্ক সমস্যা — আবার চেষ্টা করুন।'; }
    });
})();
</script>
<?php section_end(); ?>
