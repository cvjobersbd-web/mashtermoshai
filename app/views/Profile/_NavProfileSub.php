<?php
/**
 * বাম মেনুতে "My Profile" এর নিচের অংশ (শুধু নিজের প্রোফাইলে) — আগের আলাদা Profile পেজের সব সেকশন এখানে।
 * দরকার: $u, $isTeacher, $Model (TeacherStatus)
 */
$blank = fn($v) => is_blank($v);
$items = [
    ['basic', 'Basic Information', 'fa-id-card', !$blank($u->FullName)],
    ['additional', 'Additional Information', 'fa-circle-info', !$blank($u->Gender)],
    ['address', 'Address', 'fa-location-dot', !$blank($u->Country)],
    ['education', 'Education', 'fa-graduation-cap', !$blank($u->EducationLevel)],
    ['links', 'Important Links', 'fa-link', !$blank($u->CvLink)],
];
$tStatus = $isTeacher ? 'Teacher' : ($Model->TeacherStatus === 'None' ? 'Check' : $Model->TeacherStatus);
?>
<div class="pf-sub" aria-label="Profile details">
    <?php foreach ($items as [$k, $label, $icon, $done]): ?>
        <a href="?tab=profile#ab-<?= $k ?>" data-ab-go="<?= $k ?>"><i class="fas <?= $icon ?>"></i> <span><?= $label ?></span>
            <em class="<?= $done ? 'ok' : 'no' ?>" title="<?= $done ? 'Complete' : 'Incomplete' ?>"><i class="fas <?= $done ? 'fa-check' : 'fa-exclamation' ?>"></i></em></a>
    <?php endforeach; ?>
    <a href="?tab=profile#ab-tutor" data-ab-go="tutor"><i class="fas fa-chalkboard-user"></i> <span><?= $isTeacher ? 'Teaching' : 'Become a Tutor' ?></span>
        <em class="<?= $isTeacher ? 'ok' : 'no' ?>" title="<?= e($tStatus) ?>"><i class="fas <?= $isTeacher ? 'fa-check' : ($tStatus === 'Pending' ? 'fa-hourglass-half' : 'fa-arrow-right') ?>"></i></em></a>
    <a href="?tab=settings#ab-password" data-ab-go="password" class="pw"><i class="fas fa-key"></i> <span>Change Password</span></a>
</div>
