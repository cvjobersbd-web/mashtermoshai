<?php
/**
 * Admin → পড়াতে চাই ও সার্কুলার: টিচারদের "পড়াতে চাই" আর শিক্ষার্থীদের Tuition Circular পোস্ট — সব এক জায়গায় দেখা, খোঁজা আর মুছে ফেলা।
 * যোগাযোগের তথ্য (মোবাইল / ইমেইল / ফেসবুক / ঠিকানা) থাকা পোস্টে লাল চিহ্ন — নতুন পোস্টে এগুলো দেওয়া যায় না, আগের পোস্টে অন্যদের কাছে ঢাকা থাকে।
 */
$ViewData['Title'] = 'পড়াতে চাই ও সার্কুলার পোস্ট';
$posts = is_array($Model) ? $Model : [];
$k = (string)$ViewBag->K; $q = (string)$ViewBag->Q; $c = (bool)$ViewBag->C; $counts = (array)$ViewBag->Counts;
$page = (int)$ViewBag->Page; $pages = (int)$ViewBag->Pages;
$tabs = ['all' => ['fa-list', 'সব'], 'teach' => ['fa-chalkboard-user', 'পড়াতে চাই (টিচার)'], 'circular' => ['fa-bullhorn', 'Tuition Circular (শিক্ষার্থী)']];
$url = fn(array $x) => '/Admin/PublicPosts?' . http_build_query(array_filter(array_merge(['k' => $k !== 'all' ? $k : null, 'q' => $q !== '' ? $q : null, 'c' => $c ? 1 : null], $x), fn($v) => $v !== null && $v !== ''));
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .pp h2 { color: #08224A; margin: 0; font-size: 24px; font-weight: 800; }
    .pp .sub { color: #64748B; margin: 4px 0 16px; font-size: 14px; max-width: 820px; }
    .pp-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 16px; }
    .pp-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .pp-tabs a { display: inline-flex; gap: 8px; align-items: center; padding: 9px 15px; border-radius: 12px; background: #fff; border: 1.5px solid #DCECF5; color: #334155; font-weight: 700; font-size: 14px; text-decoration: none; }
    .pp-tabs a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .pp-tabs a .c { font-size: 11.5px; padding: 1px 8px; border-radius: 9px; background: #F1F5F9; color: #475569; }
    .pp-tabs a.on .c { background: rgba(255,255,255,.2); color: #fff; }
    .pp-tabs a.red { border-color: #FBD5D5; color: #B91C1C; } .pp-tabs a.red.on { background: #DC2626; border-color: #DC2626; color: #fff; }
    .pp-search { display: flex; gap: 6px; flex: 1 1 100%; max-width: 520px; }
    .pp-search input { flex: 1; height: 42px; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 14px; }
    .pp-search button { height: 42px; padding: 0 14px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; }
    .pp-list { max-width: 820px; display: flex; flex-direction: column; gap: 14px; }
    .pp-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); overflow: hidden; }
    .pp-card.has-ct { box-shadow: 0 0 0 2px #FCA5A5, 0 4px 16px rgba(0,0,0,.05); }
    .pp-h { display: flex; gap: 12px; align-items: center; padding: 14px 16px 8px; }
    .pp-av { width: 44px; height: 44px; border-radius: 50%; background: #E0F2FE; color: #0369A1; font-weight: 800; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative; text-decoration: none; }
    .pp-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .pp-who { flex: 1; min-width: 0; }
    .pp-who a { color: #08224A; font-weight: 800; text-decoration: none; font-size: 15px; } .pp-who a:hover { text-decoration: underline; }
    .pp-who small { display: block; color: #64748B; font-size: 12.5px; }
    .pp-tag { display: inline-block; font-size: 11.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; margin-left: 6px; vertical-align: middle; }
    .pp-tag.t { background: #DBEAFE; color: #1D4ED8; } .pp-tag.s { background: #DCFCE7; color: #166534; } .pp-tag.ct { background: #FEE2E2; color: #B91C1C; }
    .pp-title { padding: 0 16px 4px; font-weight: 800; color: #08224A; font-size: 15px; }
    .pp-text { padding: 2px 16px 10px; white-space: pre-wrap; word-break: break-word; font-size: 14.5px; color: #1E293B; line-height: 1.6; }
    .pp-chips { display: flex; gap: 6px; flex-wrap: wrap; padding: 0 16px 10px; } .pp-chips span { font-size: 12.5px; padding: 3px 10px; border-radius: 999px; background: #F1F5F9; color: #334155; }
    .pp-img img, .pp-img video { width: 100%; max-height: 420px; object-fit: contain; background: #F8FAFC; display: block; }
    .pp-foot { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid #EEF2F7; background: #FAFCFE; font-size: 13px; color: #475569; }
    .pp-foot .sp { flex: 1; }
    .pp-foot a, .pp-foot button { display: inline-flex; gap: 6px; align-items: center; padding: 7px 12px; border-radius: 9px; font-weight: 700; font-size: 13px; text-decoration: none; cursor: pointer; font-family: inherit; }
    .pp-view { background: #fff; color: #0369A1; border: 1.5px solid #DCECF5; }
    .pp-del { background: #fff; color: #DC2626; border: 1.5px solid #FBD5D5; }
    .pp-empty { background: #fff; border: 1.5px dashed #CBD5E1; border-radius: 16px; padding: 44px 20px; text-align: center; color: #64748B; max-width: 820px; }
    .pp-empty i { font-size: 36px; color: #B9D6E6; display: block; margin-bottom: 10px; }
    .pp-pg { display: flex; gap: 6px; margin-top: 14px; max-width: 820px; justify-content: center; flex-wrap: wrap; }
    .pp-pg a, .pp-pg span { min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; border: 1.5px solid #DCECF5; color: #08224A; text-decoration: none; font-weight: 700; padding: 0 10px; }
    .pp-pg span { background: #08224A; color: #fff; border-color: #08224A; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .pp-search { margin-left: 0; max-width: none; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content pp">
    <h2><i class="fas fa-chalkboard-user" style="color:#0EA5E9;"></i> পড়াতে চাই ও সার্কুলার পোস্ট</h2>
    <p class="sub">টিচারদের “পড়াতে চাই” আর শিক্ষার্থীদের Tuition Circular — সব পোস্ট এখানে (নতুন আগে)। দরকার হলে মুছে ফেলুন, পোস্টদাতা নোটিফিকেশন পাবেন। লাল চিহ্ন দেওয়া পোস্টে মোবাইল নম্বর / ইমেইল / ফেসবুক / ঠিকানা আছে — অন্যরা সেগুলো ঢাকা (•••) দেখে।</p>
    <?= csrf_field() ?>
    <div class="pp-bar">
        <div class="pp-tabs">
            <?php foreach ($tabs as $kk => [$ic, $lb]): ?>
                <a href="<?= e($url(['k' => $kk !== 'all' ? $kk : null, 'page' => null])) ?>" class="<?= $k === $kk ? 'on' : '' ?>"><i class="fas <?= $ic ?>"></i> <?= $lb ?> <span class="c"><?= (int)($counts[$kk] ?? 0) ?></span></a>
            <?php endforeach; ?>
            <a href="<?= e($url(['c' => $c ? null : 1, 'page' => null])) ?>" class="red<?= $c ? ' on' : '' ?>"><i class="fas fa-phone-slash"></i> যোগাযোগের তথ্য আছে <span class="c"><?= (int)($counts['contact'] ?? 0) ?></span></a>
        </div>
        <form class="pp-search" method="get" action="/Admin/PublicPosts">
            <?php if ($k !== 'all'): ?><input type="hidden" name="k" value="<?= e($k) ?>" /><?php endif; ?>
            <?php if ($c): ?><input type="hidden" name="c" value="1" /><?php endif; ?>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="নাম, ইউজারনেম, ID, এলাকা বা পোস্টের লেখা..." />
            <button type="submit"><i class="fas fa-magnifying-glass"></i></button>
        </form>
    </div>

    <?php if (!$posts): ?>
        <div class="pp-empty"><i class="fas fa-chalkboard-user"></i><?= $q !== '' ? '"' . e($q) . '" দিয়ে কোনো পোস্ট পাওয়া যায়নি।' : 'কোনো পোস্ট নেই।' ?></div>
    <?php else: ?>
    <div class="pp-list">
        <?php foreach ($posts as $p):
            $isT = $p->Kind === 'teach';
            $prof = '/Profile/Show/' . (int)$p->UserId;
            $name = !is_blank($p->FullName) ? (string)$p->FullName : ((string)$p->UserName ?: 'Deleted user');
            $link = $isT ? '/FindTutor#post-' . (int)$p->Id : '/Tuition/Circular#circular-post-' . (int)$p->Id;
            $cid = $p->Kind . '-' . (int)$p->Id; ?>
            <div class="pp-card<?= $p->Contact ? ' has-ct' : '' ?>" id="pp-<?= e($cid) ?>">
                <div class="pp-h">
                    <a class="pp-av" href="<?= $prof ?>"><?= e(initials($name, 'U')) ?><?php if (!is_blank($p->ProfileImageLink)): ?><img src="<?= e($p->ProfileImageLink) ?>" alt="<?= e($name . ' এর প্রোফাইল ছবি') ?>" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                    <div class="pp-who">
                        <a href="<?= $prof ?>"><?= e($name) ?></a><span class="pp-tag <?= $isT ? 't' : 's' ?>"><?= $isT ? 'পড়াতে চাই' : 'Tuition Circular' ?></span><?php if ($p->Contact): ?><span class="pp-tag ct"><i class="fas fa-phone-slash"></i> যোগাযোগের তথ্য</span><?php endif; ?>
                        <small>@<?= e((string)$p->UserName) ?> · #<?= (int)$p->UserId ?> · <?= e(date('d M Y, h:i A', strtotime((string)$p->CreatedAt))) ?></small>
                    </div>
                </div>
                <?php if (!$isT && !is_blank($p->Title ?? null)): ?><div class="pp-title"><?= e($p->Title) ?></div><?php endif; ?>
                <?php if ((string)$p->Content !== ''): ?><div class="pp-text"><?= e($p->Content) ?></div><?php endif; ?>
                <?php if ($p->Tags): ?><div class="pp-chips"><?php foreach ($p->Tags as $tg): ?><span><?= e($tg) ?></span><?php endforeach; ?></div><?php endif; ?>
                <?php if (!is_blank($p->VideoUrl)): ?><div class="pp-img"><video src="<?= e($p->VideoUrl) ?>" controls preload="metadata"></video></div>
                <?php elseif (!is_blank($p->ImageUrl)): ?><div class="pp-img"><img src="<?= e($p->ImageUrl) ?>" alt="<?= e('পোস্টের ছবি — ' . $name) ?>" loading="lazy" /></div><?php endif; ?>
                <div class="pp-foot">
                    <span><i class="fas fa-thumbs-up" style="color:#2563EB;"></i> <?= (int)$p->LikeCount ?></span>
                    <span class="sp"></span>
                    <a class="pp-view" href="<?= e($link) ?>" target="_blank"><i class="fas fa-arrow-up-right-from-square"></i> পেজে দেখুন</a>
                    <button type="button" class="pp-del" data-kind="<?= e($p->Kind) ?>" data-id="<?= (int)$p->Id ?>"><i class="fas fa-trash"></i> মুছুন</button>
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
    var b = e.target.closest('.pp-del');
    if (!b || !confirm('এই পোস্টটা মুছে ফেলবেন? পোস্টদাতা নোটিফিকেশন পাবেন।')) return;
    var fd = new FormData(); fd.append('kind', b.getAttribute('data-kind')); fd.append('id', b.getAttribute('data-id'));
    fd.append('__RequestVerificationToken', document.querySelector('input[name="__RequestVerificationToken"]').value);
    b.disabled = true;
    try {
        var r = await (await fetch('/Admin/DeletePublicPost', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
        if (r.success) { var c = document.getElementById('pp-' + b.getAttribute('data-kind') + '-' + b.getAttribute('data-id')); if (c) c.remove(); }
        else { alert(r.message || 'মুছা যায়নি'); b.disabled = false; }
    } catch (err) { alert('মুছা যায়নি — আবার চেষ্টা করুন।'); b.disabled = false; }
});
</script>
<?php section_end(); ?>
