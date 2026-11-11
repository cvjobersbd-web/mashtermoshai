<?php
/**
 * Tutor List এর একটা কার্ড — ছবি, ব্যাজ, নাম, প্রতিষ্ঠান, এলাকা আর "See" (প্রোফাইল)
 * @var Obj $t  GetTutorDirectory() এর একজন টিউটর
 */
$words = preg_split('/\s+/u', trim((string)$t->FullName)) ?: [];
$short = count($words) > 2 ? $words[0] . ' ' . $words[1] . ' ' . implode('. ', array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice($words, 2))) . '.' : (string)$t->FullName;
$city = $t->City !== '' ? $t->City : ($t->Location !== '' ? explode(',', $t->Location)[0] : 'Bangladesh');
// সম্পূর্ণ তথ্য বন্ধ থাকলে (প্যাকেজ নেই) প্রতিষ্ঠানের জায়গায় বিষয়
$inst = $t->Institution !== 'Not specified' ? $t->Institution : (!empty($t->Locked) ? implode(', ', array_slice((array)$t->Subjects, 0, 2)) : 'N/A');
?>
<div class="tl-card">
    <a class="tl-pic" href="/Tutors/Profile/<?= (int)$t->UserId ?>" aria-hidden="true" tabindex="-1">
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
    <a class="tl-rate" href="/Tutors/Profile/<?= (int)$t->UserId ?>#rate" title="<?= $t->TotalReviews ? e(number_format((float)$t->Rating, 1)) . ' / 5 — ' . (int)$t->TotalReviews . ' রেটিং' : 'প্রথম রেটিং দিন' ?>">
        <?php for ($si = 1; $si <= 5; $si++): ?><i class="<?= $si <= round((float)$t->Rating) ? 'fas' : 'far' ?> fa-star"></i><?php endfor; ?>
        <span><?= $t->TotalReviews ? e(number_format((float)$t->Rating, 1)) . ' (' . (int)$t->TotalReviews . ')' : 'Rate' ?></span>
    </a>
    <div class="tl-loc"><i class="fas fa-location-dot"></i> <?= e($city) ?></div>
    <a class="tl-see" href="/Tutors/Profile/<?= (int)$t->UserId ?>">See</a>
</div>
