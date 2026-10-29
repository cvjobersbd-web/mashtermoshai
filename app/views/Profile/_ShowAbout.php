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

    </div>
</div>
