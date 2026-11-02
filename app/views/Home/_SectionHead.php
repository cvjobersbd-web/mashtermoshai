<?php
/** হোম পেজের নতুন সেকশনের শিরোনাম + ডান পাশে "View More" বাটন (মোবাইলে বাটন নিচে) */
/** @var string $title @var string $sub @var string $href @var string $btn @var string $hl শিরোনামের রঙিন অংশ */
?>
<div class="hs-head">
    <div>
        <h2 class="hs-h2"><?= e($title) ?><?php if (!empty($hl)): ?> <span class="hl"><?= e($hl) ?></span><?php endif; ?></h2>
        <?php if (!empty($sub)): ?><p class="hs-sub"><?= e($sub) ?></p><?php endif; ?>
    </div>
    <a class="hs-more" href="<?= e($href) ?>"><?= e($btn) ?> <i class="fas fa-arrow-right"></i></a>
</div>
