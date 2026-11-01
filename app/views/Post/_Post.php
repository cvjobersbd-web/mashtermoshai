<?php
/**
 * একটা টাইমলাইন পোস্ট। দরকার: $post (Db::hydrateProfilePosts), $viewerId, $viewerIsAdmin
 */
$rx = reaction_meta();
$pv = post_privacy_meta()[$post->Privacy] ?? post_privacy_meta()['Public'];
$isOwner = (int)$viewerId === $post->UserId;
$canDelete = $isOwner || $viewerIsAdmin;
$text = $post->Content;
$lineCount = substr_count($text, "\n") + 1;
$isLong = mb_strlen($text) > 420 || $lineCount > 8;
$isShare = $post->SharedPostId !== null;
$orig = $post->Shared ?? null;           // Obj | false (দেখা যায় না) | null
$ptype = $post->PostType ?? 'post';
$meta = $post->Meta ?? [];
$isBig = !$isShare && $ptype === 'post' && !$post->ImageUrl && $text !== '' && mb_strlen($text) <= 85 && $lineCount <= 2;
$typeMeta = ['study' => ['fa-book-open', 'Study Update', 'st'], 'question' => ['fa-circle-question', 'Question', 'qu'],
             'achievement' => ['fa-trophy', 'Achievement', 'ac'], 'poll' => ['fa-square-poll-vertical', 'Poll', 'po'],
             'material' => ['fa-file-lines', 'Study Material', 'ma'], 'update' => ['fa-chalkboard-user', 'Class Update', 'up'], 'notice' => ['fa-bullhorn', 'Notice', 'no']];
/** Study Material এর ফাইল কার্ড */
$fileCard = function (array $m): void {
    $f = $m['file'] ?? null;
    if (!is_array($f) || empty($f['url']) || !str_starts_with((string)$f['url'], '/uploads/')) return;
    $ext = strtolower((string)($f['ext'] ?? pathinfo((string)$f['url'], PATHINFO_EXTENSION)));
    $ico = ['pdf' => ['fa-file-pdf', 'pdf'], 'doc' => ['fa-file-word', 'doc'], 'docx' => ['fa-file-word', 'doc'], 'ppt' => ['fa-file-powerpoint', 'ppt'],
            'pptx' => ['fa-file-powerpoint', 'ppt'], 'xls' => ['fa-file-excel', 'xls'], 'xlsx' => ['fa-file-excel', 'xls'], 'zip' => ['fa-file-zipper', 'zip'],
            'jpg' => ['fa-file-image', 'img'], 'jpeg' => ['fa-file-image', 'img'], 'png' => ['fa-file-image', 'img'], 'webp' => ['fa-file-image', 'img']][$ext] ?? ['fa-file-lines', 'txt'];
    $size = (int)($f['size'] ?? 0);
    $sz = $size >= 1048576 ? round($size / 1048576, 1) . ' MB' : max(1, (int)round($size / 1024)) . ' KB';
    $name = (string)($f['name'] ?? basename((string)$f['url']));
    ?>
    <a class="pc-file pc-file-<?= $ico[1] ?>" href="<?= e($f['url']) ?>" target="_blank" rel="noopener" download="<?= e($name) ?>">
        <span class="pc-file-ico"><i class="fas <?= $ico[0] ?>"></i></span>
        <span class="pc-file-txt"><strong><?= e($name) ?></strong><small><?= strtoupper(e($ext)) ?> · <?= $sz ?></small></span>
        <span class="pc-file-dl" title="Download"><i class="fas fa-download"></i></span>
    </a>
<?php };
$fmtMin = function (int $m): string { $h = intdiv($m, 60); $r = $m % 60; return ($h ? $h . 'h ' : '') . ($r || !$h ? $r . 'm' : ''); };
// কোন পোস্টটা শেয়ার হবে (শেয়ার করা পোস্ট আবার শেয়ার করলে মূলটা)
$shareTarget = $isShare ? ($orig ? $orig : null) : $post;
$canShare = !$viewerIsAdmin && $shareTarget && $shareTarget->Privacy === 'Public';
$roots = $post->Comments;
$hiddenRoots = max(0, count($roots) - 2);
$mine = $post->MyReaction;
?>
<article class="pc" id="post-<?= $post->Id ?>" data-id="<?= $post->Id ?>" data-privacy="<?= e($post->Privacy) ?>"
         data-owner="<?= $isOwner ? '1' : '0' ?>" data-content="<?= e($text) ?>" data-image="<?= e((string)$post->ImageUrl) ?>"
         data-share="<?= $isShare ? '1' : '0' ?>" data-share-target="<?= $canShare ? $shareTarget->Id : '' ?>"
         data-type="<?= e($ptype) ?>" data-meta="<?= e(json_encode((object)$meta, JSON_UNESCAPED_UNICODE)) ?>">
    <header class="pc-head">
        <a href="/Profile/Show/<?= $post->UserId ?>" class="pc-avatar" aria-label="<?= e($post->AuthorName) ?>">
            <?= e($post->AuthorInitials) ?>
            <?php if ($post->AuthorImage): ?><img src="<?= e($post->AuthorImage) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?>
        </a>
        <div class="pc-who">
            <a href="/Profile/Show/<?= $post->UserId ?>" class="pc-name"><?= e($post->AuthorName) ?></a>
            <?php if ($post->AuthorRole === 'Teacher'): ?><i class="fas fa-circle-check pc-verified" title="Teacher"></i><?php endif; ?>
            <?php if ($isShare): ?><span class="pc-shared-lbl">shared a post</span><?php endif; ?>
            <div class="pc-sub">
                <a href="/Post/Details/<?= $post->Id ?>" title="<?= e(fmt_date($post->CreatedAt, 'l, d F Y \a\t h:i A')) ?>"><?= e($post->TimeAgo) ?></a>
                <span>·</span>
                <i class="fas <?= $pv[0] ?>" title="<?= e($pv[1] . ' — ' . $pv[2]) ?>"></i>
                <?php if ($post->IsEdited): ?><span>·</span><span class="pc-edited" title="<?= e(fmt_date($post->UpdatedAt, 'd M Y, h:i A')) ?>">Edited</span><?php endif; ?>
            </div>
        </div>
        <div class="pc-menu-wrap">
            <button type="button" class="pc-menu-btn" data-pc="menu" aria-label="Post options"><i class="fas fa-ellipsis"></i></button>
            <div class="pc-menu" role="menu">
                <?php if ($isOwner): ?>
                    <button type="button" data-pc="edit"><i class="fas fa-pen"></i> Edit post</button>
                    <button type="button" data-pc="audience"><i class="fas <?= $pv[0] ?>"></i> Edit audience</button>
                <?php endif; ?>
                <button type="button" data-pc="copy"><i class="fas fa-link"></i> Copy link</button>
                <?php if ($canDelete): ?>
                    <button type="button" data-pc="delete" class="danger"><i class="fas fa-trash-can"></i> Delete post</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if (isset($typeMeta[$ptype])): [$tIcon, $tLabel, $tCls] = $typeMeta[$ptype]; ?>
        <div class="pc-type pc-type-<?= $tCls ?>">
            <span class="pc-type-pill"><i class="fas <?= $tIcon ?>"></i> <?= $tLabel ?></span>
            <?php if (!empty($meta['subject'])): ?><span class="pc-type-chip"><i class="fas fa-book"></i> <?= e($meta['subject']) ?></span><?php endif; ?>
            <?php if (!empty($meta['minutes'])): ?><span class="pc-type-chip"><i class="far fa-clock"></i> <?= e($fmtMin((int)$meta['minutes'])) ?> পড়েছি</span><?php endif; ?>
        </div>
        <?php if ($ptype === 'achievement' && !empty($meta['title'])): ?>
            <div class="pc-achieve"><span class="pc-achieve-ico">🏆</span><div><small>Achievement unlocked</small><strong><?= e($meta['title']) ?></strong></div></div>
        <?php endif; ?>
        <?php if (in_array($ptype, ['material', 'notice'], true) && !empty($meta['title'])): ?>
            <h4 class="pc-title"><?= e($meta['title']) ?></h4>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($text !== ''): ?>
        <div class="pc-body<?= $ptype === 'question' ? ' pc-q' : '' ?>">
            <div class="pc-text<?= $isBig ? ' big' : '' ?><?= $isLong ? ' clamp' : '' ?>"><?= linkify_text($text) ?></div>
            <?php if ($isLong): ?><button type="button" class="pc-more" data-pc="more">See more</button><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($ptype === 'material') $fileCard($meta); ?>

    <?php if ($ptype === 'poll'): ?>
        <div class="pc-poll-wrap"><?php partial('Post/_Poll', ['poll' => $post->Poll, 'postId' => $post->Id]); ?></div>
    <?php endif; ?>

    <?php if ($post->ImageUrl): ?>
        <div class="pc-media"><img src="<?= e($post->ImageUrl) ?>" alt="Post photo" loading="lazy" data-pc="zoom" /></div>
    <?php endif; ?>

    <?php if ($isShare): ?>
        <?php if ($orig):
            $opv = post_privacy_meta()[$orig->Privacy] ?? post_privacy_meta()['Public'];
            $oLong = mb_strlen($orig->Content) > 300 || substr_count($orig->Content, "\n") > 6;
        ?>
            <div class="pc-shared">
                <?php if ($orig->ImageUrl): ?>
                    <div class="pc-media"><img src="<?= e($orig->ImageUrl) ?>" alt="Post photo" loading="lazy" data-pc="zoom" /></div>
                <?php endif; ?>
                <div class="pc-shared-in">
                    <div class="pc-head">
                        <a href="/Profile/Show/<?= $orig->UserId ?>" class="pc-avatar sm"><?= e($orig->AuthorInitials) ?><?php if ($orig->AuthorImage): ?><img src="<?= e($orig->AuthorImage) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                        <div class="pc-who">
                            <a href="/Profile/Show/<?= $orig->UserId ?>" class="pc-name"><?= e($orig->AuthorName) ?></a>
                            <?php if ($orig->AuthorRole === 'Teacher'): ?><i class="fas fa-circle-check pc-verified"></i><?php endif; ?>
                            <div class="pc-sub"><a href="/Post/Details/<?= $orig->Id ?>"><?= e($orig->TimeAgo) ?></a><span>·</span><i class="fas <?= $opv[0] ?>" title="<?= e($opv[1]) ?>"></i></div>
                        </div>
                    </div>
                    <?php if ($orig->Content !== ''): ?>
                        <div class="pc-body"><div class="pc-text<?= $oLong ? ' clamp' : '' ?>"><?= linkify_text($orig->Content) ?></div>
                            <?php if ($oLong): ?><button type="button" class="pc-more" data-pc="more">See more</button><?php endif; ?></div>
                    <?php endif; ?>
                    <?php if (($orig->PostType ?? '') === 'material') { if (!empty($orig->Meta['title'])) echo '<h4 class="pc-title">' . e($orig->Meta['title']) . '</h4>'; $fileCard($orig->Meta ?? []); } ?>
                </div>
            </div>
        <?php else: ?>
            <div class="pc-shared pc-unavail">
                <i class="fas fa-lock"></i>
                <div><strong>This content isn't available right now</strong>
                    <p>পোস্টটি মুছে ফেলা হয়েছে, অথবা এখন শুধু নির্দিষ্ট মানুষ দেখতে পারে।</p></div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="pc-stats">
        <button type="button" class="pc-rx-sum" data-pc="reactors" <?= $post->ReactionTotal ? '' : 'hidden' ?>>
            <span class="pc-rx-icons"><span><?= like_badge_html() ?></span></span>
            <span class="pc-rx-n"><?= (int)$post->ReactionTotal ?></span>
        </button>
        <span class="pc-stat-right">
            <button type="button" class="pc-cm-n" data-pc="toggle-comments" <?= $post->CommentCount ? '' : 'hidden' ?>>
                <span><?= (int)$post->CommentCount ?></span> comment<?= $post->CommentCount === 1 ? '' : 's' ?>
            </button>
            <button type="button" class="pc-sh-n" data-pc="sharers" <?= $post->ShareCount ? '' : 'hidden' ?>><span><?= (int)$post->ShareCount ?></span> share<?= $post->ShareCount === 1 ? '' : 's' ?></button>
        </span>
    </div>

    <div class="pc-actions">
        <button type="button" class="pc-act pc-like<?= $mine ? ' on rx-like' : '' ?>" data-rx-target="post" data-id="<?= $post->Id ?>" data-mine="<?= $mine ? 'like' : '' ?>" aria-pressed="<?= $mine ? 'true' : 'false' ?>">
            <span class="pc-like-ico"><?= like_icon_html((bool)$mine) ?></span>
            <span class="pc-like-lbl"><?= $mine ? 'Liked' : 'Like' ?></span>
        </button>
        <button type="button" class="pc-act" data-pc="comment"><i class="far fa-comment"></i> <?= $ptype === 'question' ? 'Answer' : 'Comment' ?></button>
        <?php if ($canShare): ?>
            <button type="button" class="pc-act" data-pc="share"><i class="far fa-share-from-square"></i> Share</button>
        <?php endif; ?>
        <?php if (!$viewerIsAdmin): ?>
            <button type="button" class="pc-act pc-save<?= $post->IsSaved ? ' on' : '' ?>" data-save-type="post" data-save-id="<?= $post->Id ?>">
                <i class="<?= $post->IsSaved ? 'fas' : 'far' ?> fa-bookmark"></i> <span><?= $post->IsSaved ? 'Saved' : 'Save' ?></span>
            </button>
        <?php endif; ?>
    </div>

    <div class="pc-comments">
        <?php if ($hiddenRoots > 0): ?>
            <button type="button" class="cm-prev" data-pc="prev-comments">View <?= $hiddenRoots ?> previous comment<?= $hiddenRoots > 1 ? 's' : '' ?></button>
        <?php endif; ?>
        <div class="cm-list">
            <?php foreach ($roots as $i => $c): ?>
                <?php partial('Post/_Comment', ['c' => $c, 'post' => $post, 'viewerId' => $viewerId, 'viewerIsAdmin' => $viewerIsAdmin,
                    'isReply' => false, 'hidden' => $i < $hiddenRoots]); ?>
            <?php endforeach; ?>
        </div>
        <form class="cm-form" data-pc="comment-form" autocomplete="off">
            <span class="cm-avatar sm me-avatar"></span>
            <div class="cm-pill">
                <textarea rows="1" maxlength="1500" placeholder="Write a comment..." aria-label="Write a comment"></textarea>
                <button type="submit" class="cm-send" aria-label="Send" disabled><i class="fas fa-paper-plane"></i></button>
            </div>
        </form>
    </div>
</article>
