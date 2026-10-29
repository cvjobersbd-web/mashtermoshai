<?php
/** একজন মানুষের কার্ড। দরকার: $p (Db::mapFriendPerson থেকে) */
$isTeacher = $p->Role === 'Teacher';
$sub = !is_blank($p->Institution) ? $p->Institution : (!is_blank($p->Education) ? $p->Education : '@' . $p->UserName);
?>
<div class="fr-card" data-uid="<?= (int)$p->Id ?>" data-name="<?= e(mb_strtolower($p->FullName . ' ' . $p->UserName . ' ' . $p->Institution)) ?>" data-role="<?= e($p->Role) ?>">
    <a href="/Profile/Show/<?= (int)$p->Id ?>" class="fr-card-link" aria-label="<?= e($p->FullName) ?> এর প্রোফাইল">
        <span class="fr-avatar <?= $isTeacher ? 'is-teacher' : '' ?>">
            <?php if ($p->Image): ?>
                <img src="<?= e($p->Image) ?>" alt="" loading="lazy" onerror="this.remove()" />
            <?php endif; ?>
            <span class="fr-initials"><?= e($p->Initials) ?></span>
        </span>
        <span class="fr-name"><?= e($p->FullName) ?></span>
    </a>
    <span class="fr-role <?= $isTeacher ? 'teacher' : 'student' ?>">
        <i class="fas <?= $isTeacher ? 'fa-chalkboard-teacher' : 'fa-user-graduate' ?>"></i> <?= e($p->RoleLabel) ?>
    </span>
    <span class="fr-sub" title="<?= e($sub) ?>"><?= e($sub) ?></span>
    <span class="fr-meta">
        <?php if (!empty($p->MutualCount)): ?>
            <i class="fas fa-user-group"></i> <?= (int)$p->MutualCount ?> mutual friend<?= $p->MutualCount > 1 ? 's' : '' ?>
        <?php elseif ($p->Relation === 'friends' && $p->TimeAgo !== ''): ?>
            <i class="fas fa-heart"></i> Friends since <?= e(fmt_date($p->Since, 'M Y')) ?>
        <?php elseif ($p->TimeAgo !== ''): ?>
            <i class="far fa-clock"></i> <?= e($p->TimeAgo) ?>
        <?php else: ?>
            &nbsp;
        <?php endif; ?>
    </span>
    <div class="fr-actions" data-uid="<?= (int)$p->Id ?>" data-rel="<?= e($p->Relation) ?>">
        <?= friend_actions_html((int)$p->Id, (string)$p->Relation) ?>
    </div>
</div>
