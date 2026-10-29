<?php
/** একটা টিচার রিভিউ। দরকার: $r (Db::GetTeacherReviews), $me (int), $isAdmin, $stars (callable) */
$canDel = $isAdmin || (int)$me === $r->StudentUserId;
?>
<div class="pf-card rv-item" id="rv-<?= $r->Id ?>">
    <div class="rv-head">
        <a class="tu-av" href="/Profile/Show/<?= $r->StudentUserId ?>"><?= e($r->Initials) ?><?php if ($r->Image): ?><img src="<?= e($r->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
        <div class="tu-who"><b><a href="/Profile/Show/<?= $r->StudentUserId ?>" style="color:inherit;text-decoration:none;"><?= e($r->Name) ?></a></b>
            <small><?= $stars((float)$r->Rating) ?> · <?= e($r->TimeAgo) ?><?= $r->Edited ? ' · Edited' : '' ?><?= $r->Level !== '' ? ' · ' . e($r->Level) : '' ?></small></div>
        <?php if ($canDel): ?><button type="button" class="tp-menu-btn" data-rv-del="<?= $r->Id ?>" title="Delete review" aria-label="Delete review"><i class="fas fa-trash-can"></i></button><?php endif; ?>
    </div>
    <?php if ($r->Comment !== ''): ?><p class="rv-text"><?= e($r->Comment) ?></p><?php endif; ?>
</div>
