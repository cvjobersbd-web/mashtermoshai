<?php
/** একটা স্টাডি গোল কার্ড। দরকার: $g (Db::GetGoal), $isOwner */
$catIcon = ['SSC' => 'fa-school', 'HSC' => 'fa-building-columns', 'Admission' => 'fa-door-open', 'University' => 'fa-graduation-cap',
            'IELTS' => 'fa-language', 'Subject' => 'fa-book', 'Skill' => 'fa-laptop-code', 'Custom' => 'fa-bullseye'][$g->Category] ?? 'fa-bullseye';
$done = $g->Progress >= 100;
?>
<div class="gl-card<?= $g->IsPrimary ? ' primary' : '' ?><?= $done ? ' done' : '' ?>" id="goal-<?= $g->Id ?>" data-id="<?= $g->Id ?>"
     data-title="<?= e($g->Title) ?>" data-category="<?= e($g->Category) ?>" data-date="<?= e((string)$g->TargetDate) ?>"
     data-progress="<?= (int)$g->ManualProgress ?>" data-primary="<?= $g->IsPrimary ? '1' : '0' ?>" data-tasks="<?= $g->UsesTasks ? '1' : '0' ?>">
    <div class="gl-top">
        <span class="gl-ico"><i class="fas <?= $catIcon ?>"></i></span>
        <div class="gl-head">
            <div class="gl-tags">
                <span class="gl-cat"><?= e($g->Category) ?></span>
                <?php if ($g->IsPrimary): ?><span class="gl-main"><i class="fas fa-star"></i> Main goal</span><?php endif; ?>
                <?php if ($done): ?><span class="gl-ok"><i class="fas fa-check"></i> Completed</span><?php endif; ?>
            </div>
            <h4><?= e($g->Title) ?></h4>
            <?php if ($g->TargetDate): ?>
                <div class="gl-date"><i class="far fa-calendar"></i> <?= e(fmt_date($g->TargetDate, 'd M Y')) ?>
                    <?php if (!$done && $g->DaysLeft !== null): ?>
                        · <?= $g->DaysLeft > 0 ? '<b>' . $g->DaysLeft . '</b> দিন বাকি' : ($g->DaysLeft === 0 ? '<b>আজই</b>' : 'সময় পার হয়ে গেছে') ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($isOwner): ?>
            <div class="gl-menu-wrap">
                <button type="button" class="gl-menu-btn" data-gl="menu" aria-label="Goal options"><i class="fas fa-ellipsis"></i></button>
                <div class="gl-menu">
                    <button type="button" data-gl="edit"><i class="fas fa-pen"></i> Edit goal</button>
                    <?php if (!$g->IsPrimary): ?><button type="button" data-gl="primary"><i class="fas fa-star"></i> Make main goal</button><?php endif; ?>
                    <button type="button" data-gl="delete" class="danger"><i class="fas fa-trash-can"></i> Delete</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="gl-prog">
        <div class="gl-bar"><span style="width: <?= (int)$g->Progress ?>%"></span></div>
        <b><?= (int)$g->Progress ?>%</b>
    </div>
    <div class="gl-sub">
        <?php if ($g->UsesTasks): ?>
            <?= (int)$g->TaskDone ?>/<?= count($g->Tasks) ?> কাজ শেষ — কাজ টিক দিলে প্রগ্রেস নিজে থেকে বাড়ে
        <?php elseif ($isOwner): ?>
            নিচে কাজ যোগ করুন, অথবা Edit থেকে প্রগ্রেস নিজে সেট করুন
        <?php endif; ?>
    </div>

    <?php if ($g->Tasks || $isOwner): ?>
        <ul class="gl-tasks">
            <?php foreach ($g->Tasks as $t): ?>
                <li class="<?= $t->IsDone ? 'done' : '' ?>" data-task="<?= $t->Id ?>">
                    <?php if ($isOwner): ?>
                        <label><input type="checkbox" data-gl="task" <?= $t->IsDone ? 'checked' : '' ?> /><span><?= e($t->Title) ?></span></label>
                        <button type="button" class="gl-x" data-gl="task-del" aria-label="Delete task"><i class="fas fa-xmark"></i></button>
                    <?php else: ?>
                        <i class="<?= $t->IsDone ? 'fas fa-circle-check' : 'far fa-circle' ?>"></i><span><?= e($t->Title) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($isOwner): ?>
            <form class="gl-add" data-gl="add-task" autocomplete="off">
                <input type="text" maxlength="150" placeholder="নতুন কাজ যোগ করুন (যেমন: Physics 1st paper শেষ)" />
                <button type="submit" aria-label="Add task"><i class="fas fa-plus"></i></button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
