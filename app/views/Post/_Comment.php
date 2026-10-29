<?php
/**
 * একটা কমেন্ট (বা রিপ্লাই)। দরকার: $c, $post, $viewerId, $viewerIsAdmin, $isReply, (ঐচ্ছিক) $hidden
 */
$rx = reaction_meta();
$mine = $c->MyReaction;
$isMine = (int)$viewerId === $c->UserId;
$canDelete = $isMine || (int)$viewerId === $post->UserId || $viewerIsAdmin;
$text = $c->Content;
$mention = null;
if (preg_match('/^@\[([^\]]{1,80})\]\s?/u', $text, $m)) { $mention = $m[1]; $text = substr($text, strlen($m[0])); }
$replies = $c->Replies ?? [];
?>
<div class="cm<?= $isReply ? ' is-reply' : '' ?>" id="cm-<?= $c->Id ?>" data-id="<?= $c->Id ?>" data-root="<?= $c->ParentId ?? $c->Id ?>"
     data-uid="<?= $c->UserId ?>" data-name="<?= e($c->AuthorName) ?>" data-raw="<?= e($text) ?>" data-mention="<?= e((string)$mention) ?>"<?= !empty($hidden) ? ' hidden' : '' ?>>
    <a href="/Profile/Show/<?= $c->UserId ?>" class="cm-avatar<?= $isReply ? ' sm' : '' ?>" aria-label="<?= e($c->AuthorName) ?>">
        <?= e($c->AuthorInitials) ?>
        <?php if ($c->AuthorImage): ?><img src="<?= e($c->AuthorImage) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?>
    </a>
    <div class="cm-main">
        <div class="cm-bubble-row">
            <div class="cm-bubble<?= $c->ReactionTotal ? ' has-rx' : '' ?>">
                <a href="/Profile/Show/<?= $c->UserId ?>" class="cm-name"><?= e($c->AuthorName) ?></a>
                <div class="cm-text"><?php if ($mention !== null): ?><span class="cm-mention"><?= e($mention) ?></span> <?php endif; ?><?= linkify_text($text) ?></div>
                <button type="button" class="cm-rx" data-pc="comment-reactors" <?= $c->ReactionTotal ? '' : 'hidden' ?>>
                    <span class="cm-rx-icons"><?php foreach (array_slice(array_keys($c->Reactions), 0, 3) as $k): ?><span><?= $rx[$k][0] ?></span><?php endforeach; ?></span>
                    <span class="cm-rx-n"><?= $c->ReactionTotal > 1 ? (int)$c->ReactionTotal : '' ?></span>
                </button>
            </div>
        </div>
        <div class="cm-meta">
            <button type="button" class="cm-like<?= $mine ? ' on rx-' . e($mine) : '' ?>" data-rx-target="comment" data-id="<?= $c->Id ?>" data-mine="<?= e((string)$mine) ?>"><?= $mine ? $rx[$mine][1] : 'Like' ?></button>
            <button type="button" data-pc="reply">Reply</button>
            <?php if ($isMine): ?><button type="button" data-pc="edit-comment">Edit</button><?php endif; ?>
            <?php if ($canDelete): ?><button type="button" data-pc="delete-comment">Delete</button><?php endif; ?>
            <span class="cm-time" title="<?= e(fmt_date($c->CreatedAt, 'd M Y, h:i A')) ?>"><?= e($c->TimeAgo) ?></span>
            <span class="cm-edited"<?= $c->IsEdited ? '' : ' hidden' ?>>Edited</span>
        </div>
        <?php if (!$isReply): ?>
            <div class="cm-replies-wrap">
                <?php if ($replies): ?>
                    <button type="button" class="cm-view-replies" data-pc="view-replies">
                        <i class="fas fa-reply fa-rotate-180"></i> View <?= count($replies) ?> repl<?= count($replies) > 1 ? 'ies' : 'y' ?>
                    </button>
                <?php endif; ?>
                <div class="cm-replies"<?= $replies ? ' hidden' : '' ?>>
                    <?php foreach ($replies as $r): ?>
                        <?php partial('Post/_Comment', ['c' => $r, 'post' => $post, 'viewerId' => $viewerId, 'viewerIsAdmin' => $viewerIsAdmin, 'isReply' => true]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
