<?php
$u = $Model->User;
$rel = $Model->Relation;
$isTeacher = $u->Role === 'Teacher';
$name = !is_blank($u->FullName) ? $u->FullName : $u->UserName;
$ViewData['Title'] = $name;
$ti = $Model->TeacherInfo;
$img = is_blank($u->ProfileImageLink) ? null : $u->ProfileImageLink;

$rows = [];
$add = function (string $icon, string $label, $value) use (&$rows) { if (!is_blank($value)) $rows[] = [$icon, $label, (string)$value]; };
$add('fa-graduation-cap', 'Education', $ti && !is_blank($ti->Education) ? $ti->Education : $u->EducationLevel);
$add('fa-certificate', 'Degree', $u->ExamDegreeTitle);
$add('fa-building-columns', 'Institution', $ti && !is_blank($ti->Institution) ? $ti->Institution : $u->InstitutionName);
$add('fa-calendar-check', 'Passing Year', $u->PassingYear);
$add('fa-book-open', 'Status', $u->CurrentStudyStatus);
if ($isTeacher && $ti) {
    $add('fa-book', 'Subjects', $ti->SubjectExpertise);
    $add('fa-briefcase', 'Experience', $ti->Experience);
    $add('fa-person-chalkboard', 'Teaching Style', $ti->TeachingStyle);
    $add('fa-calendar-days', 'Available Days', $ti->AvailableDays);
    $add('fa-clock', 'Preferred Time', $ti->PreferredTime);
}
$add('fa-venus-mars', 'Gender', $u->Gender);
$add('fa-earth-asia', 'Country', $u->Country);

$links = [];
foreach ([['CvLink', 'fa-file-lines', 'CV'], ['LinkedInProfile', 'fa-linkedin', 'LinkedIn'], ['GithubProfile', 'fa-github', 'GitHub'], ['PortfolioLink', 'fa-globe', 'Portfolio']] as [$k, $icon, $label]) {
    $v = trim((string)$u->$k);
    if ($v !== '' && preg_match('#^https?://#i', $v)) $links[] = [$v, $icon, $label];
}
$phone = trim((string)$u->MobileNumber);
$waDigits = preg_replace('/\D/', '', $phone);
if (str_starts_with($waDigits, '01')) $waDigits = '88' . $waDigits;
?>
<?= csrf_field() ?>
<?php partial('Friends/_Assets'); ?>

<style>
    .pp-page { background: #F5FBFF; padding: 26px 16px 70px; min-height: 80vh; }
    .pp-wrap { max-width: 900px; margin: 0 auto; }
    .pp-back { display: inline-flex; align-items: center; gap: 6px; color: #64748B; font-size: 13.5px; text-decoration: none; padding: 7px 12px; border-radius: 8px; margin-bottom: 12px; }
    .pp-back:hover { background: #DCECF5; color: #08224A; }

    .pp-card { background: #fff; border-radius: 22px; overflow: hidden; box-shadow: 0 8px 30px rgba(8,34,74,.07); border: 1px solid #E6F1F8; }
    .pp-cover { height: 150px; background: linear-gradient(120deg, #08224A 0%, #134856 55%, #0F8FB3 100%); position: relative; }
    .pp-cover::after {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 85% 20%, rgba(2,184,245,.45) 0%, transparent 45%), radial-gradient(circle at 10% 110%, rgba(255,179,0,.25) 0%, transparent 40%);
    }
    .pp-head { padding: 0 28px 24px; display: flex; gap: 22px; align-items: flex-start; flex-wrap: wrap; margin-top: -62px; position: relative; z-index: 1; }
    .pp-avatar {
        width: 132px; height: 132px; border-radius: 50%; border: 5px solid #fff; overflow: hidden; flex-shrink: 0; position: relative;
        background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-size: 42px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(8,34,74,.2);
    }
    .pp-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .pp-id { flex: 1; min-width: 220px; padding-top: 74px; }
    .pp-name { font-family: var(--font-heading); font-size: 26px; font-weight: 700; color: #08224A; margin: 0; line-height: 1.25; word-break: break-word; }
    .pp-handle { font-size: 13.5px; color: #64748B; margin-top: 2px; }
    .pp-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; align-items: center; }
    .pp-pill { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px; background: #EEF5FA; color: #08224A; }
    .pp-pill.teacher { background: #E7F8EF; color: #157347; }
    .pp-pill.student { background: #E3F2FD; color: #1565C0; }
    .pp-pill.warn { background: #FDECEA; color: #B42318; }
    .pp-head .fr-actions { width: auto; margin: 84px 0 0; min-width: 260px; }
    .pp-head .fr-actions .fr-btn { height: 42px; font-size: 14px; }

    .pp-body { display: grid; grid-template-columns: 1fr 300px; gap: 20px; margin-top: 20px; }
    .pp-box { background: #fff; border-radius: 18px; padding: 22px 22px; border: 1px solid #E6F1F8; box-shadow: 0 2px 12px rgba(8,34,74,.04); }
    .pp-box + .pp-box { margin-top: 20px; }
    .pp-box h4 { font-size: 15.5px; font-weight: 700; color: #08224A; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
    .pp-box h4 i { color: #028EA5; }
    .pp-rows { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; }
    .pp-row { display: flex; gap: 10px; align-items: flex-start; min-width: 0; }
    .pp-row > i { width: 34px; height: 34px; border-radius: 10px; background: #F5FBFF; color: #028EA5; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; }
    .pp-row small { display: block; font-size: 11px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: #7A8CA0; }
    .pp-row span { display: block; font-size: 14px; color: #08224A; font-weight: 600; word-break: break-word; }
    .pp-muted { color: #7A8CA0; font-size: 14px; margin: 0; }

    .pp-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .pp-stat { background: #F5FBFF; border-radius: 14px; padding: 14px; text-align: center; }
    .pp-stat b { display: block; font-size: 22px; color: #08224A; font-weight: 800; }
    .pp-stat small { font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }

    .pp-contact { display: flex; flex-direction: column; gap: 8px; }
    .pp-contact a {
        display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; text-decoration: none;
        font-size: 14px; font-weight: 600; color: #08224A; background: #F5FBFF; border: 1px solid #E6F1F8; word-break: break-all;
    }
    .pp-contact a:hover { background: #EAF6FC; }
    .pp-contact a i { width: 20px; text-align: center; }
    .pp-lock { text-align: center; padding: 8px 4px 2px; }
    .pp-lock i { font-size: 28px; color: #9FC3D6; }
    .pp-lock p { font-size: 13.5px; color: #64748B; margin: 10px 0 0; line-height: 1.6; }
    .pp-links { display: flex; flex-wrap: wrap; gap: 8px; }
    .pp-links a { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px; background: #EEF5FA; color: #08224A; font-size: 13px; font-weight: 600; text-decoration: none; }
    .pp-links a:hover { background: #DCECF5; }

    @media (max-width: 820px) {
        .pp-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 576px) {
        .pp-page { padding: 14px 10px 60px; }
        .pp-cover { height: 110px; }
        .pp-head { padding: 0 16px 18px; margin-top: -54px; gap: 12px; justify-content: center; text-align: center; align-items: center; }
        .pp-avatar { width: 108px; height: 108px; font-size: 34px; }
        .pp-id { min-width: 100%; padding-top: 0; }
        .pp-head .fr-actions { margin-top: 4px; }
        .pp-name { font-size: 21px; }
        .pp-badges { justify-content: center; }
        .pp-head .fr-actions { min-width: 0; width: 100%; flex-direction: row; }
        .pp-head .fr-actions > * { flex: 1 1 0; width: auto; }
        .pp-head .fr-icon-only { flex: 0 0 42px !important; width: 42px; }
        .pp-head .fr-icon-only span { display: none; }
        .pp-rows { grid-template-columns: 1fr; }
        .pp-box { padding: 18px 16px; }
    }
</style>

<div class="pp-page">
    <div class="pp-wrap">
        <a href="/Friends" class="pp-back"><i class="fas fa-arrow-left"></i> Friends</a>

        <div class="pp-card">
            <div class="pp-cover"></div>
            <div class="pp-head">
                <div class="pp-avatar">
                    <?= e(initials($name, 'U')) ?>
                    <?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($name) ?>" onerror="this.remove()" /><?php endif; ?>
                </div>
                <div class="pp-id">
                    <h1 class="pp-name"><?= e($name) ?></h1>
                    <div class="pp-handle">@<?= e($u->UserName) ?></div>
                    <div class="pp-badges">
                        <span class="pp-pill <?= $isTeacher ? 'teacher' : 'student' ?>">
                            <i class="fas <?= $isTeacher ? 'fa-chalkboard-teacher' : 'fa-user-graduate' ?>"></i> <?= $isTeacher ? 'Teacher' : 'Student' ?>
                        </span>
                        <?php if ($Model->MutualCount > 0): ?>
                            <span class="pp-pill"><i class="fas fa-user-group"></i> <?= (int)$Model->MutualCount ?> mutual</span>
                        <?php endif; ?>
                        <?php if ($rel === 'received'): ?>
                            <span class="pp-pill" style="background:#FFF7E0; color:#8a5a00;"><i class="fas fa-hand"></i> আপনাকে রিকোয়েস্ট পাঠিয়েছেন</span>
                        <?php endif; ?>
                        <?php if (!$u->IsActive): ?>
                            <span class="pp-pill warn"><i class="fas fa-ban"></i> Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$Model->IsAdminViewer): ?>
                    <div class="fr-actions" data-uid="<?= (int)$u->Id ?>" data-rel="<?= e($rel) ?>">
                        <?= friend_actions_html((int)$u->Id, $rel) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="pp-body">
            <div>
                <div class="pp-box">
                    <h4><i class="fas fa-id-card"></i> About</h4>
                    <?php if (!$rows): ?>
                        <p class="pp-muted">এখনো প্রোফাইলে তেমন কিছু যোগ করা হয়নি।</p>
                    <?php else: ?>
                        <div class="pp-rows">
                            <?php foreach ($rows as [$icon, $label, $value]): ?>
                                <div class="pp-row"><i class="fas <?= $icon ?>"></i><div><small><?= e($label) ?></small><span><?= e($value) ?></span></div></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($links): ?>
                    <div class="pp-box">
                        <h4><i class="fas fa-link"></i> Links</h4>
                        <div class="pp-links">
                            <?php foreach ($links as [$href, $icon, $label]): ?>
                                <a href="<?= e($href) ?>" target="_blank" rel="noopener nofollow"><i class="<?= in_array($icon, ['fa-linkedin', 'fa-github'], true) ? 'fab' : 'fas' ?> <?= $icon ?>"></i> <?= e($label) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="pp-box">
                    <div class="pp-stats">
                        <div class="pp-stat"><b><?= (int)$Model->FriendCount ?></b><small>Friends</small></div>
                        <div class="pp-stat"><b><?= e(fmt_date($u->CreatedAt, 'M Y', '—')) ?></b><small>Joined</small></div>
                    </div>
                </div>

                <div class="pp-box" id="contactBox">
                    <h4><i class="fas fa-address-book"></i> Contact</h4>
                    <?php if ($Model->CanSeeContact): ?>
                        <div class="pp-contact">
                            <?php if ($phone !== ''): ?>
                                <a href="tel:<?= e($phone) ?>"><i class="fas fa-phone" style="color:#028EA5;"></i> <?= e($phone) ?></a>
                                <?php if (strlen($waDigits) >= 11): ?>
                                    <a href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!is_blank($u->Email)): ?>
                                <a href="mailto:<?= e($u->Email) ?>"><i class="fas fa-envelope" style="color:#64748B;"></i> <?= e($u->Email) ?></a>
                            <?php endif; ?>
                            <?php if ($phone === '' && is_blank($u->Email)): ?>
                                <p class="pp-muted">কোনো যোগাযোগের তথ্য দেওয়া নেই।</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="pp-lock">
                            <i class="fas fa-lock"></i>
                            <p>ফোন নম্বর ও ইমেইল শুধু ফ্রেন্ডরা দেখতে পারেন।<br />ফ্রেন্ড রিকোয়েস্ট Accept হলে এখানে দেখাবে।</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
    // ফ্রেন্ড হয়ে গেলে / আনফ্রেন্ড করলে কন্টাক্ট দেখানো বা লুকানোর জন্য পেজ রিফ্রেশ
    document.addEventListener('fr:relation', function (e) {
        var was = <?= js($rel === 'friends') ?>;
        if ((e.detail.rel === 'friends') !== was) setTimeout(function () { location.reload(); }, 900);
    });
</script>
<?php section_end(); ?>
