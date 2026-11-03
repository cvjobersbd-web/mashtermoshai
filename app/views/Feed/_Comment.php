<?php
/** @var Obj $c কমেন্ট, Obj $p পোস্ট, ?int $me */
$canDel = $me !== null && ($c->UserId === $me || $p->UserId === $me || is_admin());
?>
<div class="ef-c" data-comment="<?= (int)$c->Id ?>">
    <a class="ef-av sm" href="/Profile/Show/<?= (int)$c->UserId ?>" aria-hidden="true" tabindex="-1"><?= e($c->Initials) ?><?php if ($c->AuthorPhoto !== ''): ?><img src="<?= e($c->AuthorPhoto) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
    <div style="min-width:0;flex:1;">
        <div class="ef-cb"><a class="n" href="/Profile/Show/<?= (int)$c->UserId ?>"><?= e($c->AuthorName) ?></a><?php if ($c->AuthorRole === 'Teacher'): ?><span class="ef-role t">Teacher</span><?php endif; ?>
            <p><?= linkify_text($c->Content) ?></p></div>
        <div class="ef-cm"><span><?= e(time_ago($c->CreatedAt)) ?></span><?php if ($canDel): ?><button type="button" data-ef="cdel">Delete</button><?php endif; ?></div>
    </div>
</div>
