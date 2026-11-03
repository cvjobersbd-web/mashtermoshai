<?php
/**
 * Educational Feed এর একটা পোস্ট কার্ড
 * @var Obj $p   পোস্ট (EduFeedData::decorateEduPosts)
 * @var ?int $me দর্শক
 * @var bool $admin অ্যাডমিন রিভিউ পেজে কিনা (তাহলে লাইক/কমেন্টের জায়গায় Approve/Reject)
 * @var bool $open  কমেন্ট খোলা অবস্থায় (একক পোস্ট পেজ)
 * @var array $comments  open হলে কমেন্টগুলো
 */
$admin = !empty($admin);
$open = !empty($open);
$isOwner = $me !== null && $p->UserId === $me;
$canDelete = $isOwner || is_admin();
$approved = $p->Status === 'Approved';
$roleLbl = $p->AuthorRole === 'Teacher' ? 'Teacher' : ($p->AuthorRole === 'Admin' ? 'Admin' : 'Student');
$n = count($p->Images);
$long = mb_strlen($p->Content) > 420 || substr_count($p->Content, "\n") > 6;
?>
<article class="ef-post" data-post="<?= (int)$p->Id ?>" data-status="<?= e($p->Status) ?>">
    <header class="ef-ph">
        <a class="ef-av" href="/Profile/Show/<?= (int)$p->UserId ?>" aria-hidden="true" tabindex="-1"><?= e($p->Initials) ?><?php if ($p->AuthorPhoto !== ''): ?><img src="<?= e($p->AuthorPhoto) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
        <div class="ef-who">
            <a class="n" href="/Profile/Show/<?= (int)$p->UserId ?>"><?= e($p->AuthorName) ?></a><span class="ef-role<?= $roleLbl === 'Teacher' ? ' t' : '' ?>"><?= $roleLbl ?></span>
            <div class="ef-meta">
                <a href="<?= e($p->Url) ?>" title="<?= e(date('d M Y, h:i A', strtotime((string)($p->ApprovedAt ?? $p->CreatedAt)))) ?>"><?= e(time_ago($approved && !$admin ? ($p->ApprovedAt ?? $p->CreatedAt) : $p->CreatedAt)) ?></a>
                <?php if ($p->Subject !== ''): ?>· <?php if ($approved && !$admin): ?><a class="ef-subj" href="/Feed?subject=<?= rawurlencode($p->Subject) ?>"><i class="fas fa-book-open"></i><?= e($p->Subject) ?></a><?php else: ?><span class="ef-subj"><i class="fas fa-book-open"></i><?= e($p->Subject) ?></span><?php endif; ?><?php endif; ?>
                <i class="fas fa-earth-asia" title="Public"></i>
            </div>
        </div>
        <?php if ($canDelete || $approved): ?>
            <div class="ef-menu">
                <button type="button" data-ef="menu" aria-label="More options"><i class="fas fa-ellipsis"></i></button>
                <div class="dd">
                    <?php if ($approved): ?><button type="button" data-ef="copy"><i class="fas fa-link"></i> লিংক কপি</button><?php endif; ?>
                    <?php if ($canDelete): ?><button type="button" class="del" data-ef="delete"><i class="fas fa-trash"></i> পোস্ট মুছুন</button><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <?php if (!$admin && $isOwner && !$approved): ?>
        <div class="ef-status <?= e($p->Status) ?>">
            <?php if ($p->Status === 'Pending'): ?><i class="fas fa-hourglass-half"></i><span>অ্যাডমিনের রিভিউতে আছে — Approve হলে সবাই দেখতে পাবে।</span>
            <?php else: ?><i class="fas fa-circle-xmark"></i><span>পোস্টটি Approve হয়নি।<?= $p->RejectReason !== '' ? ' কারণ: ' . e($p->RejectReason) : '' ?></span><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($p->Content !== ''): ?>
        <div class="ef-text<?= $long && !$open ? ' clamp' : '' ?>"><?= linkify_text($p->Content) ?></div>
        <?php if ($long && !$open): ?><button type="button" class="ef-more" data-ef="more">See more</button><?php endif; ?>
    <?php endif; ?>

    <?php if ($n): ?>
        <div class="ef-imgs n<?= min($n, 4) ?>" data-imgs="<?= e(js($p->Images)) ?>">
            <?php foreach (array_slice($p->Images, 0, 4) as $i => $src): ?>
                <button type="button" data-ef="img" data-i="<?= $i ?>" aria-label="ছবি <?= $i + 1 ?> বড় করে দেখুন"><img src="<?= e($src) ?>" alt="" loading="lazy" /><?php if ($i === 3 && $n > 4): ?><span class="plus">+<?= $n - 4 ?></span><?php endif; ?></button>
            <?php endforeach; ?>
        </div>
    <?php elseif ($p->Video !== ''): ?>
        <div class="ef-video"><video src="<?= e($p->Video) ?>" controls preload="metadata" playsinline></video></div>
    <?php elseif ($p->YouTube !== ''): ?>
        <div class="ef-yt"><button type="button" data-ef="yt" data-yt="<?= e($p->YouTube) ?>" style="background-image:url('https://i.ytimg.com/vi/<?= e($p->YouTube) ?>/hqdefault.jpg')" aria-label="YouTube ভিডিও চালান"></button></div>
    <?php endif; ?>

    <?php if ($approved && !$admin): ?>
        <div class="ef-counts">
            <span data-likes><?php if ($p->LikeCount): ?><?= like_badge_html() ?> <span data-lc><?= (int)$p->LikeCount ?></span><?php endif; ?></span>
            <button type="button" data-ef="comments" data-cc><?= $p->CommentCount ? (int)$p->CommentCount . ' comment' . ($p->CommentCount > 1 ? 's' : '') : '' ?></button>
        </div>
        <div class="ef-acts">
            <button type="button" data-ef="like" class="<?= $p->Liked ? 'liked' : '' ?>" aria-pressed="<?= $p->Liked ? 'true' : 'false' ?>"><i class="<?= $p->Liked ? 'fas' : 'far' ?> fa-thumbs-up"></i> Like</button>
            <button type="button" data-ef="comments"><i class="far fa-comment"></i> Comment</button>
            <button type="button" data-ef="share"><i class="fas fa-share"></i> Share</button>
        </div>
        <div class="ef-cbox"<?= $open ? '' : ' hidden' ?> data-loaded="<?= $open ? '1' : '0' ?>">
            <div class="ef-clist">
                <?php if ($open): ?>
                    <?php foreach ($comments ?? [] as $c) partial('Feed/_Comment', ['c' => $c, 'p' => $p, 'me' => $me]); ?>
                <?php endif; ?>
            </div>
            <?php if ($me !== null): ?>
                <form class="ef-cform" data-ef-cform>
                    <textarea rows="1" maxlength="1000" placeholder="একটা কমেন্ট লিখুন..." aria-label="কমেন্ট"></textarea>
                    <button type="submit" aria-label="কমেন্ট পাঠান"><i class="fas fa-paper-plane"></i></button>
                </form>
            <?php else: ?>
                <div class="ef-clogin"><a href="/Account/Login?returnUrl=<?= rawurlencode($p->Url) ?>">লগইন করুন</a> — তাহলে লাইক ও কমেন্ট করতে পারবেন।</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($admin) echo $adminFooter ?? ''; ?>
</article>
