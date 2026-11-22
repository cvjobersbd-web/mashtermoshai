<?php
/** টিচার বনাম শিক্ষার্থী — $vs (TeacherPlayers, TeacherAvg, StudentPlayers, StudentAvg) */
$sa = (float)($vs->StudentAvg ?? 0); $ta = (float)($vs->TeacherAvg ?? 0); $tot = $sa + $ta;
$sp = $tot > 0 ? max(18, min(82, round(100 * $sa / $tot))) : 50;
$msg = ($vs->StudentPlayers ?? 0) + ($vs->TeacherPlayers ?? 0) === 0 ? 'আজ এখনো কেউ খেলেনি — আপনার দলকে এগিয়ে নিন! 💪'
    : ($sa > $ta ? 'আজ শিক্ষার্থীরা এগিয়ে! 🎉 টিচাররা, পারবেন ধরতে?' : ($ta > $sa ? 'আজ টিচাররা এগিয়ে! 🎉 শিক্ষার্থীরা, পারবে হারাতে?' : 'আজ দুই দল সমান সমান! ⚖️'));
?>
<h3><i class="fas fa-people-arrows" style="color:#7C3AED;"></i> শিক্ষার্থী বনাম টিচার</h3>
<div class="gm-vs">
    <div class="gm-vs-bar"><div class="s" style="width:<?= $sp ?>%;">🎓 <?= bn_num($sa) ?></div><div class="t" style="width:<?= 100 - $sp ?>%;"><?= bn_num($ta) ?> 👨‍🏫</div></div>
    <div class="gm-vs-legend"><span>শিক্ষার্থী · <?= bn_num($vs->StudentPlayers ?? 0) ?> জন</span><span>টিচার · <?= bn_num($vs->TeacherPlayers ?? 0) ?> জন</span></div>
    <div class="gm-vs-msg"><?= $msg ?></div>
    <div class="gm-reset" style="justify-content:center;">প্রতি জনের আজকের গড় পয়েন্ট</div>
</div>
