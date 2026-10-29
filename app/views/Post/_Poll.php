<?php
/** পোল। দরকার: $poll (Db::GetPolls এর একটা), $postId */
if (!$poll) return;
$total = (int)$poll['total'];
$mine = $poll['mine'];
$voted = $mine !== null;
?>
<div class="pl" data-post="<?= (int)$postId ?>">
    <?php foreach ($poll['options'] as $o):
        $pct = $total ? (int)round($o->Votes / $total * 100) : 0;
        $isMine = $mine === $o->Id;
    ?>
        <button type="button" class="pl-opt<?= $voted ? ' show' : '' ?><?= $isMine ? ' mine' : '' ?>" data-pc="vote" data-opt="<?= $o->Id ?>"
                title="<?= $isMine ? 'আবার চাপলে ভোট তুলে নেওয়া হবে' : 'ভোট দিন' ?>">
            <span class="pl-bar" style="width: <?= $voted ? $pct : 0 ?>%"></span>
            <span class="pl-radio"><?= $isMine ? '<i class="fas fa-check"></i>' : '' ?></span>
            <span class="pl-txt"><?= e($o->Text) ?></span>
            <?php if ($voted): ?><span class="pl-pct"><?= $pct ?>%</span><?php endif; ?>
        </button>
    <?php endforeach; ?>
    <div class="pl-foot"><?= $total ?> vote<?= $total === 1 ? '' : 's' ?><?= $voted ? ' · আপনার ভোট দেওয়া হয়েছে' : ' · ভোট দিলে ফলাফল দেখবেন' ?></div>
</div>
