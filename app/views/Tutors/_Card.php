<?php
/**
 * Tutor List এর একটা কার্ড — ছবি, ব্যাজ, নাম, প্রতিষ্ঠান, এলাকা আর "See" (প্রোফাইল)
 * @var Obj $t  GetTutorDirectory() এর একজন টিউটর
 */
$words = preg_split('/\s+/u', trim((string)$t->FullName)) ?: [];
$short = count($words) > 2 ? $words[0] . ' ' . $words[1] . ' ' . implode('. ', array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice($words, 2))) . '.' : (string)$t->FullName;
$city = $t->City !== '' ? $t->City : ($t->Location !== '' ? explode(',', $t->Location)[0] : 'Bangladesh');
$inst = $t->Institution !== 'Not specified' ? $t->Institution : 'N/A';
?>
<div class="tl-card">
    <a class="tl-pic" href="/Profile/Show/<?= (int)$t->UserId ?>" aria-hidden="true" tabindex="-1">
        <span><?= e($t->Initials) ?></span>
        <?php if ($t->PhotoUrl !== ''): ?><img src="<?= e($t->PhotoUrl) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?>
    </a>
    <div class="tl-badges">
        <?php if ($t->IsPremium): ?><i class="fas fa-crown pr" title="Premium Tutor"></i><?php endif; ?>
        <?php if ($t->IsTopRated): ?><i class="fas fa-award tp" title="Top Rated"></i><?php endif; ?>
        <i class="fas fa-circle-check vf" title="Verified Tutor"></i>
    </div>
    <h3 class="tl-name" title="<?= e($t->FullName) ?>"><?= e($short) ?></h3>
    <div class="tl-inst" title="<?= e($inst) ?>"><?= e($inst) ?></div>
    <div class="tl-loc"><i class="fas fa-location-dot"></i> <?= e($city) ?></div>
    <a class="tl-see" href="/Profile/Show/<?= (int)$t->UserId ?>">See</a>
</div>
