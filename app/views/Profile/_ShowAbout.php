<?php
/**
 * প্রোফাইলের "My Profile" অংশ — প্রোফাইল এডিট পেজের সেকশনগুলো (দেখার জন্য)।
 * দরকার: $u, $isOwner, $isTeacher, $ti, $canPrivate, $Model
 */
$val = fn($v) => is_blank($v) ? '' : trim((string)$v);
$link = function ($v) { $v = trim((string)$v); return preg_match('#^https?://#i', $v) ? $v : ''; };
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

// ===== মালিকের জন্য এখানেই এডিট ফর্ম (আলাদা পেজে যেতে হয় না) =====
$opt = function (array $list, $cur, string $ph = 'Select') {
    $o = '<option value="">' . e($ph) . '</option>';
    foreach ($list as $v) $o .= '<option value="' . e($v) . '"' . ((string)$cur === $v ? ' selected' : '') . '>' . e($v) . '</option>';
    return $o;
};
$fi = fn($name, $label, $value, $type = 'text', $extra = '') =>
    '<div class="ab-f"><label>' . $label . '</label><input type="' . $type . '" name="' . $name . '" value="' . e((string)$value) . '" ' . $extra . ' /></div>';
$fs = fn($name, $label, $html) => '<div class="ab-f"><label>' . $label . '</label><select name="' . $name . '">' . $html . '</select></div>';
$forms = !$isOwner ? [] : [
    'basic' => ['/Profile/UpdateBasic',
        $fi('FullName', 'Full Name <b>*</b>', $u->FullName, 'text', 'required maxlength="100"')
        . $fi('Email', 'Email <b>*</b>', $u->Email, 'email', 'required maxlength="200"')
        . $fi('MobileNumber', 'Mobile Number', $u->MobileNumber, 'tel', 'maxlength="20" placeholder="+880 1XXX XXXXXX"')],
    'additional' => ['/Profile/UpdateAdditional',
        $fs('Gender', 'Gender', $opt(['Male', 'Female', 'Other'], $u->Gender, 'Select Gender'))
        . $fs('AgeRange', 'Age Range', $opt(['Under 18', '18-24', '25-34', '35-44', '45-54', '55+'], $u->AgeRange, 'Select Age Range'))
        . $fs('PrimaryDeviceType', 'Primary Device', $opt(['Mobile', 'Tablet', 'Laptop', 'Computer'], $u->PrimaryDeviceType, 'Select Device'))
        . $fs('YearsOfExperience', 'Years of Experience', $opt(['0-1 year', '1-2 years', '3-5 years', '5-10 years', '10+ years'], $u->YearsOfExperience, 'Select Experience'))
        . $fs('AreaType', 'Area Type', $opt(['Village', 'Town', 'City'], $u->AreaType, 'Select Area Type'))],
    'address' => ['/Profile/UpdateAddress',
        $fi('Country', 'Country', $u->Country, 'text', 'maxlength="60" placeholder="Bangladesh"')
        . $fi('StreetAddress', 'Present Address', $u->StreetAddress, 'text', 'maxlength="200" placeholder="House, Road, Area"')
        . $fi('PermanentAddress', 'Permanent Address', $u->PermanentAddress, 'text', 'maxlength="200" placeholder="Village, Upazila, District"')],
    'education' => ['/Profile/UpdateEducation',
        $fs('EducationLevel', 'Education Level', $opt(['JSC/JDC/8 pass', 'Secondary', 'Higher Secondary', 'Diploma', 'Bachelor/Honors', 'Masters', 'PhD'], $u->EducationLevel, 'Select Education Level'))
        . $fs('CurrentStudyStatus', 'Currently Studying', str_replace('<option value="">No</option>', '', $opt(['No', 'Yes'], $u->CurrentStudyStatus ?: 'No', 'No')))
        . $fi('ExamDegreeTitle', 'Degree / Exam', $u->ExamDegreeTitle, 'text', 'maxlength="120" placeholder="e.g. BSc in Mathematics"')
        . $fi('InstitutionName', 'Institution', $u->InstitutionName, 'text', 'maxlength="150" placeholder="Institution name"')
        . $fi('PassingYear', 'Passing Year', $u->PassingYear, 'text', 'maxlength="4" inputmode="numeric" placeholder="2020"')
        . $fs('IsCSEStudent', 'CSE Student', '<option value="false"' . ($u->IsCSEStudent ? '' : ' selected') . '>No</option><option value="true"' . ($u->IsCSEStudent ? ' selected' : '') . '>Yes</option>')],
    'links' => ['/Profile/UpdateLinks',
        $fi('CvLink', 'CV Link', $u->CvLink, 'url', 'maxlength="300" placeholder="https://drive.google.com/..."')
        . $fi('GithubProfile', 'GitHub', $u->GithubProfile, 'url', 'maxlength="300" placeholder="https://github.com/username"')
        . $fi('PortfolioLink', 'Portfolio', $u->PortfolioLink, 'url', 'maxlength="300" placeholder="https://yourportfolio.com"')
        . $fi('LinkedInProfile', 'LinkedIn', $u->LinkedInProfile, 'url', 'maxlength="300" placeholder="https://linkedin.com/in/username"')],
];
?>
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
                        <button type="button" class="ab-edit" data-ab-edit="<?= $k ?>"><i class="fas fa-pen"></i> <?= $done ? 'Edit' : 'Add' ?></button>
                    <?php endif; ?>
                </div>
                <?php if (isset($forms[$k])): ?>
                    <form class="ab-form" data-ab-form="<?= e($forms[$k][0]) ?>" data-sec="<?= $k ?>" autocomplete="off" novalidate>
                        <div class="ab-fgrid"><?= $forms[$k][1] ?></div>
                        <?php if ($k === 'basic' || $k === 'address'): ?><p class="ab-note"><i class="fas fa-user-group"></i> <?= $k === 'basic' ? 'ইমেইল ও মোবাইল' : 'বর্তমান ও স্থায়ী ঠিকানা' ?> শুধু আপনার ফ্রেন্ডরা দেখতে পারেন।</p><?php endif; ?>
                        <div class="ab-factions"><button type="button" class="pf-btn" data-ab-cancel>Cancel</button><button type="submit" class="pf-btn primary"><i class="fas fa-check"></i> Save</button></div>
                    </form>
                <?php endif; ?>
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
                    <a href="/Package?for=teacher" class="ps-btn" style="margin-top:10px;background:#fff;border:1.5px solid #DCECF5;color:#1D7FE5;"><i class="fas fa-tags"></i> Subscription প্যাকেজ</a>
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
                        <a href="/Package?for=teacher" class="ps-btn" style="margin:6px 0 0 6px;background:#fff;border:1.5px solid #DCECF5;color:#1D7FE5;"><i class="fas fa-tags"></i> Subscription প্যাকেজ দেখুন</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
