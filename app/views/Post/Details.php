<?php
/** একটা পোস্ট আলাদা পেজে (নোটিফিকেশন / শেয়ার লিংক থেকে আসে) */
$post = $Model;
$ViewData['Title'] = $post->AuthorName . ' — Post';
$me = obj(['Id' => (int)current_user_id(), 'Name' => session_get('UserFullName') ?? session_get('UserName') ?? 'User', 'Image' => session_get('ProfileImageLink')]);
?>
<?= csrf_field() ?>
<?php partial('Friends/_Assets'); ?>
<?php partial('Post/_Assets', ['me' => $me]); ?>
<div style="background:#F0F4F8; padding:20px 12px 60px; min-height:80vh;">
    <div style="max-width:620px; margin:0 auto;">
        <a href="/Profile/Show/<?= $post->UserId ?>" style="display:inline-flex;align-items:center;gap:8px;color:#65676B;text-decoration:none;font-weight:600;font-size:14px;margin-bottom:12px;">
            <i class="fas fa-arrow-left"></i> <?= e($post->AuthorName) ?> এর প্রোফাইল
        </a>
        <div id="pdFeed">
            <?php partial('Post/_Post', ['post' => $post, 'viewerId' => $me->Id, 'viewerIsAdmin' => (bool)$ViewBag->ViewerIsAdmin]); ?>
        </div>
    </div>
</div>
<?php section_start('Scripts'); ?>
<script>
    // এই পেজে কমেন্টগুলো শুরুতেই খোলা থাকে
    document.querySelectorAll('#pdFeed .pc').forEach(function (c) {
        c.classList.add('show-comments');
        c.querySelectorAll('.cm[hidden]').forEach(function (x) { if (!x.closest('.cm-replies')) x.hidden = false; });
        var pv = c.querySelector('.cm-prev'); if (pv) pv.remove();
        c.querySelectorAll('.cm-replies[hidden]').forEach(function (x) { x.hidden = false; });
        c.querySelectorAll('.cm-view-replies').forEach(function (x) { x.remove(); });
    });
    document.addEventListener('pc:deleted', function () { location.href = '/Profile/Show/<?= (int)$post->UserId ?>'; });
</script>
<?php section_end(); ?>
