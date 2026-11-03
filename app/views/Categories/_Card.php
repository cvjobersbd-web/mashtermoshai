<?php /** একটা ক্যাটাগরি কার্ড। দরকার: $c (decorateCategory) */ [$__bg, $__fg] = category_tint($c->Name); ?>
<a class="cc-card" href="<?= e($c->Url) ?>" data-name="<?= e(mb_strtolower($c->Name . ' ' . ($c->GroupName ?? ''))) ?>" title="<?= e($c->Name) ?>">
    <span class="cc-img">
        <?php if ($c->ImageUrl !== ''): ?>
            <img src="<?= e($c->ImageUrl) ?>" alt="<?= e($c->Name) ?> tutor" loading="lazy" />
        <?php else: ?>
            <span class="cc-ic" style="background:<?= $__bg ?>;color:<?= $__fg ?>;"><i class="fas <?= e($c->Icon) ?>"></i></span>
        <?php endif; ?>
    </span>
    <span class="cc-name"><?= e($c->Name) ?></span>
</a>
