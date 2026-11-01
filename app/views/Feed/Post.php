<?php
/** Educational Feed — একটা পোস্ট (কমেন্টসহ) */
$p = $Model;
$me = current_user_id();
$title = $p->Content !== '' ? str_preview(preg_replace('/\s+/u', ' ', $p->Content), 60, '') : ($p->Subject !== '' ? $p->Subject : 'Post');
$ViewData['Title'] = 'Educational Feed';
$ViewData['FullTitle'] = $title . ' — ' . $p->AuthorName . ' | Educational Feed | মাষ্টার মশাই';
$ViewData['Description'] = str_preview(preg_replace('/\s+/u', ' ', $p->Content), 200, 'Educational post by ' . $p->AuthorName . ' on মাষ্টার মশাই Educational Feed.');
$ViewData['Canonical'] = 'https://mashtermoshai.com' . $p->Url;
if ($p->Images) $ViewData['OgImage'] = $p->Images[0];
elseif ($p->YouTube !== '') $ViewData['OgImage'] = 'https://i.ytimg.com/vi/' . $p->YouTube . '/hqdefault.jpg';
if ($p->Status !== 'Approved') $ViewData['Robots'] = 'noindex, nofollow';
?>
<?php partial('Feed/_Styles'); ?>
<style>
    .efd { background: #F0F3F8; min-height: 80vh; padding: 26px 0 60px; }
    .efd-wrap { max-width: 680px; margin: 0 auto; padding: 0 16px; }
    .efd-back { display: inline-flex; gap: 8px; align-items: center; color: #334155; font-weight: 700; text-decoration: none; margin-bottom: 14px; font-size: 14.5px; }
    .efd-back:hover { color: #0369A1; }
    @media (max-width: 640px) { .efd { padding-top: 16px; } .efd-wrap { padding: 0 10px; } }
</style>
<div class="efd">
    <div class="efd-wrap">
        <a class="efd-back" href="/Feed<?= $p->Status !== 'Approved' && $me === $p->UserId ? '?tab=mine' : '' ?>"><i class="fas fa-arrow-left"></i> Educational Feed</a>
        <?php if (is_admin() && $p->Status !== 'Approved'): ?>
            <div class="ef-status <?= e($p->Status) ?>" style="margin:0 0 12px;"><i class="fas fa-user-shield"></i><span>এই পোস্টটি এখন <?= e($p->Status) ?> — <a href="/Admin/EduFeed?status=<?= e($p->Status) ?>" style="color:inherit;font-weight:800;">Admin → Educational Feed</a> থেকে রিভিউ করুন।</span></div>
        <?php endif; ?>
        <?php partial('Feed/_Post', ['p' => $p, 'me' => $me, 'open' => true, 'comments' => is_array($ViewBag->Comments) ? $ViewBag->Comments : []]); ?>
    </div>
</div>
<?php partial('Feed/_Js'); ?>
<?php section_start('Scripts'); ?>
<script>
document.addEventListener('ef:removed', function () { location.href = '/Feed'; });
(function () { var l = document.querySelector('.ef-clist'); if (l && !l.children.length) l.innerHTML = '<div class="ef-cempty">এখনো কোনো কমেন্ট নেই — প্রথম কমেন্টটা আপনিই করুন।</div>'; })();
</script>
<?php section_end(); ?>
