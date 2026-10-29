<?php
/** খালি অবস্থা। দরকার: $icon, $title, $text, (ঐচ্ছিক) $btnText, $btnHref */
?>
<div class="fr-empty">
    <span class="fr-empty-icon"><i class="fas <?= e($icon ?? 'fa-user-group') ?>"></i></span>
    <h5><?= e($title ?? '') ?></h5>
    <p><?= e($text ?? '') ?></p>
    <?php if (!empty($btnText)): ?>
        <a href="<?= e($btnHref ?? '/Friends?tab=find') ?>" class="fr-btn fr-primary fr-empty-btn"><i class="fas fa-magnifying-glass"></i><span><?= e($btnText) ?></span></a>
    <?php endif; ?>
</div>
