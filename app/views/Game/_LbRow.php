<?php
/** লিডারবোর্ডের এক লাইন — $r (Rank, UserId, FullName, UserName, ProfileImageLink, Role, Pts, Solves), $me */
$medal = [1 => '🥇', 2 => '🥈', 3 => '🥉'][$r->Rank] ?? null;
$isT = ($r->Role ?? '') === 'Teacher';
$nm = trim((string)($r->FullName ?: $r->UserName));
?>
<div class="gm-lb-row<?= $me !== null && (int)$me === (int)$r->UserId ? ' gm-me-row' : '' ?>">
    <div class="gm-rank r<?= (int)$r->Rank ?>"><?= $medal ?? bn_num($r->Rank) ?></div>
    <a class="gm-av" href="/Profile/Show/<?= (int)$r->UserId ?>"><?= e(mb_strtoupper(mb_substr($nm, 0, 1)) ?: 'U') ?><?php if (!is_blank($r->ProfileImageLink)): ?><img src="<?= e($r->ProfileImageLink) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
    <div class="gm-nm"><b><?= e($nm) ?><span class="gm-role <?= $isT ? 't' : 's' ?>"><?= $isT ? 'টিচার' : 'শিক্ষার্থী' ?></span></b><small><?= bn_num($r->Solves) ?>টা পাজল সমাধান</small></div>
    <div class="gm-pts"><?= bn_num($r->Pts) ?> <small>পয়েন্ট</small></div>
</div>
