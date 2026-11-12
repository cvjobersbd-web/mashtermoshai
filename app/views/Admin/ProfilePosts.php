<?php
/**
 * Admin → Profile Posts: টিচার / শিক্ষার্থী / ইউজার নিজের প্রোফাইলে যা পোস্ট করে — সব এক জায়গায় দেখা, দরকারে মুছে ফেলা।
 */
$ViewData['Title'] = 'Profile Posts';
$posts = is_array($Model) ? $Model : [];
$role = (string)$ViewBag->Role; $q = (string)$ViewBag->Q; $counts = (array)$ViewBag->Counts;
$page = (int)$ViewBag->Page; $pages = (int)$ViewBag->Pages;
$tabs = ['all' => ['fa-list', 'সব'], 'Teacher' => ['fa-chalkboard-user', 'টিচার'], 'User' => ['fa-user-graduate', 'শিক্ষার্থী / ইউজার']];
$typeBn = ['post' => 'পোস্ট', 'study' => 'পড়াশোনা', 'question' => 'প্রশ্ন', 'achievement' => 'অর্জন', 'poll' => 'পোল', 'material' => 'স্টাডি ম্যাটেরিয়াল', 'update' => 'আপডেট', 'notice' => 'নোটিশ'];
$privBn = ['Public' => ['fa-earth-asia', 'Public'], 'Friends' => ['fa-user-group', 'Friends'], 'OnlyMe' => ['fa-lock', 'Only me']];
$url = fn(array $x) => '/Admin/ProfilePosts?' . http_build_query(array_filter(array_merge(['role' => $role !== 'all' ? $role : null, 'q' => $q !== '' ? $q : null], $x), fn($v) => $v !== null && $v !== ''));
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .pp h2 { color: #08224A; margin: 0; font-size: 24px; font-weight: 800; }
    .pp .sub { color: #64748B; margin: 4px 0 16px; font-size: 14px; max-width: 760px; }
    .pp-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 16px; }
    .pp-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .pp-tabs a { display: inline-flex; gap: 8px; align-items: center; padding: 9px 15px; border-radius: 12px; background: #fff; border: 1.5px solid #DCECF5; color: #334155; font-weight: 700; font-size: 14px; text-decoration: none; }
    .pp-tabs a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .pp-tabs a .c { font-size: 11.5px; padding: 1px 8px; border-radius: 9px; background: #F1F5F9; color: #475569; }
    .pp-tabs a.on .c { background: rgba(255,255,255,.2); color: #fff; }
    .pp-search { display: flex; gap: 6px; margin-left: auto; flex: 1; min-width: 240px; max-width: 420px; }
    .pp-search input { flex: 1; height: 42px; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 14px; }
    .pp-search button { height: 42px; padding: 0 14px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; }
    .pp-list { max-width: 760px; display: flex; flex-direction: column; gap: 14px; }
    .pp-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); overflow: hidden; }
    .pp-h { display: flex; gap: 12px; align-items: center; padding: 14px 16px 8px; }
    .pp-av { width: 44px; height: 44px; border-radius: 50%; background: #E0F2FE; color: #0369A1; font-weight: 800; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative; text-decoration: none; }
    .pp-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .pp-who { flex: 1; min-width: 0; }
    .pp-who a { color: #08224A; font-weight: 800; text-decoration: none; font-size: 15px; } .pp-who a:hover { text-decoration: underline; }
    .pp-who small { display: block; color: #64748B; font-size: 12.5px; }
    .pp-tag { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; margin-left: 6px; vertical-align: middle; }
    .pp-tag.t { background: #DBEAFE; color: #1D4ED8; } .pp-tag.s { background: #DCFCE7; color: #166534; } .pp-tag.u { background: #F1F5F9; color: #475569; } .pp-tag.ty { background: #F5F3FF; color: #6D28D9; }
    .pp-text { padding: 2px 16px 10px; white-space: pre-wrap; word-break: break-word; font-size: 14.5px; color: #1E293B; line-height: 1.6; }
    .pp-text a { color: #0369A1; }
    .pp-img img { width: 100%; height: auto; display: block; }
    .pp-file { margin: 0 16px 10px; padding: 10px 12px; border-radius: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; font-size: 13.5px; }
    .pp-poll { margin: 0 16px 10px; } .pp-poll div { padding: 7px 10px; border-radius: 8px; background: #F1F5F9; margin-bottom: 5px; font-size: 13.5px; display: flex; justify-content: space-between; }
    .pp-shared { margin: 0 16px 10px; padding: 8px 12px; border-left: 3px solid #CBD5E1; color: #64748B; font-size: 13px; }
    .pp-foot { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid #EEF2F7; background: #FAFCFE; font-size: 13px; color: #475569; }
    .pp-foot .sp { flex: 1; }
    .pp-foot a, .pp-foot button { display: inline-flex; gap: 6px; align-items: center; padding: 7px 12px; border-radius: 9px; font-weight: 700; font-size: 13px; text-decoration: none; cursor: pointer; font-family: inherit; }
    .pp-view { background: #fff; color: #0369A1; border: 1.5px solid #DCECF5; }
    .pp-del { background: #fff; color: #DC2626; border: 1.5px solid #FBD5D5; }
    .pp-empty { background: #fff; border: 1.5px dashed #CBD5E1; border-radius: 16px; padding: 44px 20px; text-align: center; color: #64748B; max-width: 760px; }
    .pp-empty i { font-size: 36px; color: #B9D6E6; display: block; margin-bottom: 10px; }
    .pp-pg { display: flex; gap: 6px; margin-top: 14px; max-width: 760px; justify-content: center; flex-wrap: wrap; }
    .pp-pg a, .pp-pg span { min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; border: 1.5px solid #DCECF5; color: #08224A; text-decoration: none; font-weight: 700; padding: 0 10px; }
    .pp-pg span { background: #08224A; color: #fff; border-color: #08224A; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .pp-search { margin-left: 0; max-width: none; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content pp">
    <h2><i class="fas fa-address-card" style="color:#0EA5E9;"></i> Profile Posts</h2>
    <p class="sub">টিচার আর শিক্ষার্থী / ইউজার নিজের প্রোফাইলে যা পোস্ট করে, সব এখানে (নতুন আগে)। Only me পোস্টও আপনি দেখতে পাবেন। নামে চাপ দিলে তার প্রোফাইলে যাবেন।</p>
    <?= csrf_field() ?>
    <div class="pp-bar">
        <div class="pp-tabs">
            <?php foreach ($tabs as $k => [$ic, $lb]): ?>
                <a href="<?= e('/Admin/ProfilePosts?' . http_build_query(array_filter(['role' => $k !== 'all' ? $k : null, 'q' => $q !== '' ? $q : null]))) ?>" class="<?= $role === $k ? 'on' : '' ?>"><i class="fas <?= $ic ?>"></i> <?= $lb ?> <span class="c"><?= (int)($counts[$k] ?? 0) ?></span></a>
            <?php endforeach; ?>
        </div>
        <form class="pp-search" method="get" action="/Admin/ProfilePosts">
            <?php if ($role !== 'all'): ?><input type="hidden" name="role" value="<?= e($role) ?>" /><?php endif; ?>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="নাম, ইউজারনেম, ইমেইল, ID বা পোস্টের লেখা..." />
            <button type="submit"><i class="fas fa-magnifying-glass"></i></button>
        </form>
    </div>

    <?php if (!$posts): ?>
        <div class="pp-empty"><i class="fas fa-address-card"></i><?= $q !== '' ? '"' . e($q) . '" দিয়ে কোনো পোস্ট পাওয়া যায়নি।' : 'এখনো কেউ প্রোফাইলে পোস্ট করেনি।' ?></div>
    <?php else: ?>
    <div class="pp-list">
        <?php foreach ($posts as $p):
            $isT = $p->AuthorRole === 'Teacher';
            $lbl = $isT ? 'Teacher' : role_label($p->AuthorRole, $p->UserId);
            $tc = $isT ? 't' : ($lbl === 'Student' ? 's' : 'u');
            [$pic, $plb] = $privBn[$p->Privacy] ?? $privBn['Public'];
            $prof = '/Profile/Show/' . (int)$p->UserId; ?>
            <div class="pp-card" id="pp<?= (int)$p->Id ?>">
                <div class="pp-h">
                    <a class="pp-av" href="<?= $prof ?>"><?= e($p->AuthorInitials) ?><?php if ($p->AuthorImage): ?><img src="<?= e($p->AuthorImage) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                    <div class="pp-who">
                        <a href="<?= $prof ?>"><?= e($p->AuthorName) ?></a><span class="pp-tag <?= $tc ?>"><?= e($lbl) ?></span><?php if ($p->PostType !== 'post'): ?><span class="pp-tag ty"><?= e($typeBn[$p->PostType] ?? $p->PostType) ?></span><?php endif; ?>
                        <small>@<?= e($p->AuthorUserName) ?> · <?= e(date('d M Y, h:i A', strtotime((string)$p->CreatedAt))) ?> (<?= e($p->TimeAgo) ?>) · <i class="fas <?= $pic ?>"></i> <?= e($plb) ?><?= $p->IsEdited ? ' · Edited' : '' ?></small>
                    </div>
                </div>
                <?php if ($p->Content !== ''): ?><div class="pp-text"><?= linkify_text($p->Content) ?></div><?php endif; ?>
                <?php if ($p->SharedPostId): ?><div class="pp-shared"><i class="fas fa-share"></i> একটা পোস্ট শেয়ার করেছেন<?= $p->Shared ? ' — ' . e($p->Shared->AuthorName) . ': ' . e(mb_strimwidth((string)$p->Shared->Content, 0, 120, '…')) : '' ?></div><?php endif; ?>
                <?php if (is_array($p->Poll) && !empty($p->Poll['options'])): ?><div class="pp-poll"><?php foreach ($p->Poll['options'] as $o): ?><div><span><?= e((string)$o->Text) ?></span><b><?= (int)$o->Votes ?> ভোট</b></div><?php endforeach; ?></div><?php endif; ?>
                <?php if (!empty($p->Meta['file']['url'])): ?><div class="pp-file"><i class="fas fa-paperclip"></i> <a href="<?= e((string)$p->Meta['file']['url']) ?>" target="_blank" rel="noopener"><?= e((string)($p->Meta['file']['name'] ?? 'ফাইল')) ?></a></div><?php endif; ?>
                <?php if ($p->ImageUrl): ?><div class="pp-img"><img src="<?= e($p->ImageUrl) ?>" alt="" loading="lazy" /></div><?php endif; ?>
                <div class="pp-foot">
                    <span><i class="fas fa-thumbs-up" style="color:#2563EB;"></i> <?= (int)$p->ReactionTotal ?></span>
                    <span><i class="fas fa-comment" style="color:#64748B;"></i> <?= (int)$p->CommentCount ?></span>
                    <span class="sp"></span>
                    <a class="pp-view" href="<?= $prof ?>" target="_blank"><i class="fas fa-arrow-up-right-from-square"></i> প্রোফাইলে দেখুন</a>
                    <button type="button" class="pp-del" data-del="<?= (int)$p->Id ?>"><i class="fas fa-trash"></i> মুছুন</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
        <div class="pp-pg">
            <?php for ($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++): ?>
                <?php if ($i === $page): ?><span><?= $i ?></span><?php else: ?><a href="<?= e($url(['page' => $i])) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php section_start('Scripts'); ?>
<script>
document.addEventListener('click', async function (e) {
    var b = e.target.closest('[data-del]');
    if (!b || !confirm('এই পোস্টটা মুছে ফেলবেন? পোস্টদাতা নোটিফিকেশন পাবেন।')) return;
    var fd = new FormData(); fd.append('id', b.getAttribute('data-del'));
    fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
    b.disabled = true;
    var r = await (await fetch('/Admin/DeleteProfilePost', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
    if (r.success) { var c = document.getElementById('pp' + b.getAttribute('data-del')); if (c) c.remove(); } else { alert(r.message || 'মুছা যায়নি'); b.disabled = false; }
});
</script>
<?php section_end(); ?>
