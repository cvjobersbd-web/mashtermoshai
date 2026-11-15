<?php
/**
 * প্যাকেজ কার্ড — Package পেজ আর হোম পেজের "Our Packages" সেকশনে একই ডিজাইন।
 * @var Obj $p  @var string $cta বাটনের লেখা  @var bool $studentFree
 */
$free = !empty($studentFree) && $p->Audience === 'Student';
?>
    <div class="pk-card<?= $p->IsFeatured ? ' featured' : '' ?>" data-pkg="<?= (int)$p->Id ?>">
        <div class="pk-top">
            <div class="pk-name"><?= e($p->Name) ?></div>
            <?php if (!is_blank($p->Badge)): ?><span class="pk-badge"><?= e($p->Badge) ?></span><?php endif; ?>
        </div>
        <?php if (trim($p->Name) !== $p->DurationLabel): ?><div class="pk-dur"><?= e($p->DurationLabel) ?></div><?php endif; ?>
        <div class="pk-price">
            <?php if ($free): ?><b style="color:#15803D;"><?= t('ফ্রি', 'Free') ?></b><s><?= e(taka($p->Price)) ?></s>
            <?php else: ?><b><?= e(taka($p->Price)) ?></b>
            <?php if ($p->RegularPrice): ?><s><?= e(taka($p->RegularPrice)) ?></s><?php endif; ?><?php endif; ?>
        </div>
        <?php if ($free): ?><span class="pk-pill green"><i class="fas fa-gift" style="margin-right:6px;"></i> <?= t('এখন সম্পূর্ণ ফ্রি', 'Completely free now') ?></span>
        <?php elseif ($p->PerMonth): ?><span class="pk-pill"><?= t('প্রতি মাসে মাত্র', 'Only') ?> <?= e(taka($p->PerMonth)) ?><?= t('', ' per month') ?></span>
        <?php elseif ($p->Audience === 'Teacher'): ?><span class="pk-pill green"><?= t('মাত্র', 'Only') ?> <?= e(taka($p->Price)) ?></span>
        <?php elseif ($p->PostLimit > 1 && $p->Price > 0): ?><span class="pk-pill"><?= t('প্রতি পোস্ট মাত্র', 'Only') ?> <?= e(taka((int)round($p->Price / $p->PostLimit))) ?><?= t('', ' per post') ?></span>
        <?php else: ?><span class="pk-pill green"><?= e($p->DurationLabel) ?></span><?php endif; ?>
        <?php if ($p->FeatureList): ?>
            <ul class="pk-feat">
                <?php foreach ($p->FeatureList as $f): ?><li><i class="fas fa-check"></i><span><?= e($f) ?></span></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($free): ?><a class="pk-btn" href="/Tuition/Circular"><?= t('ফ্রিতে পোস্ট করুন', 'Post for free') ?> <i class="fas fa-arrow-right"></i></a>
        <?php else: ?><a class="pk-btn" href="/Payment/Checkout/<?= (int)$p->Id ?>"><?= e($cta) ?> <i class="fas fa-arrow-right"></i></a><?php endif; ?>
    </div>
