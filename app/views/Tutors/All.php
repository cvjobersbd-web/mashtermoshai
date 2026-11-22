<?php
/**
 * সব টিউটর (See All) — জেলা অনুযায়ী ফিল্টার, ১৬ জন করে পেজ
 * @var Obj[] $Model  এই পেজের টিউটর
 */
$tutors = is_array($Model) ? $Model : [];
$ViewData['SchemaType'] = 'CollectionPage';
$ViewData['Crumbs'] = [[t('টিউটর লিস্ট', 'Tutor List'), '/Tutors']];
seo_schema(seo_item_list(t('যাচাই করা টিউটর', 'Verified tutors'), array_map(fn($t) => ['name' => (string)$t->FullName, 'url' => '/Tutors/Profile/' . (int)$t->UserId, 'item' => ['@type' => 'Person', 'jobTitle' => 'Tutor']], $tutors)));
$type = (string)$ViewBag->Type;
$F = $ViewBag->F;
$gender = $F['gender'];
$page = (int)$ViewBag->Page; $pages = (int)$ViewBag->Pages;
$ViewData['Title'] = (string)$ViewBag->Title;
$ViewData['Description'] = t('মাষ্টার মশাই এর যাচাই করা টিউটর — ', 'Verified tutors on Mashter Moshai — ') . $ViewBag->Title . t('। জেলা আর প্রতিষ্ঠান দেখে পছন্দের টিউটর বেছে নিন।', '. Choose your tutor by district and institution.');
$ViewData['FullTitle'] = 'Showing ' . $ViewBag->Title . ($page > 1 ? " — Page $page" : '') . ' | মাষ্টার মশাই';
// এখনকার ফিল্টার রেখে URL (পেজ বদল / একটা ফিল্টার সরানো)
$qs = function (array $over = [], ?array $drop = null) use ($type, $F) {
    $q = ['type' => $type !== 'all' ? $type : null];
    foreach (['dist', 'area', 'cls', 'sub', 'med', 'sal', 'slot', 'day', 'uni', 'grp', 'rel'] as $k) $q[$k] = $F[$k] ?: null;
    foreach (['aq', 'uq', 'gender', 'exp', 'mode'] as $k) $q[$k] = $F[$k] !== '' ? $F[$k] : null;
    $q['prem'] = $F['prem'] ? 1 : null; $q['sort'] = $F['sort'] !== 'rec' ? $F['sort'] : null;
    if ($drop) { [$dk, $dv] = $drop; if (is_array($q[$dk] ?? null)) { $q[$dk] = array_values(array_diff($q[$dk], [$dv])) ?: null; } else $q[$dk] = null; }
    $q = array_merge($q, $over);
    $q = array_filter($q, fn($v) => $v !== null && $v !== '');
    if (($q['page'] ?? 0) === 1) unset($q['page']);
    return '/Tutors/All' . ($q ? '?' . preg_replace('/%5B\d+%5D/', '%5B%5D', http_build_query($q)) : '');
};
$url = fn(array $over) => $qs($over);
// চালু ফিল্টারের চিপ
$pills = [];
$lbl = ['cls' => array_map(fn($x) => $x[0], tf_class_groups()), 'med' => TF_MEDIUMS, 'sal' => array_map(fn($x) => $x[0], TF_SALARY), 'slot' => array_map(fn($x) => $x[0], TF_SLOTS),
    'day' => TF_DAYS, 'uni' => array_map(fn($x) => $x[0], tf_unis()), 'grp' => TF_GROUPS, 'rel' => TF_RELIGION];
foreach (['dist' => '📍', 'area' => '🏘️', 'cls' => '🎓', 'sub' => '📚', 'med' => '🏫', 'sal' => '💰', 'slot' => '⏰', 'day' => '📅', 'uni' => '🏛️', 'grp' => '🧪', 'rel' => '🛐'] as $k => $ic)
    foreach ($F[$k] as $v) $pills[] = [$ic . ' ' . ($lbl[$k][$v] ?? $v), $qs([], [$k, $v])];
if ($F['aq'] !== '') $pills[] = ['🏘️ “' . $F['aq'] . '”', $qs([], ['aq', ''])];
if ($F['uq'] !== '') $pills[] = ['🏛️ “' . $F['uq'] . '”', $qs([], ['uq', ''])];
if ($F['gender'] !== '') $pills[] = [$F['gender'] === 'male' ? '👨 পুরুষ' : '👩 মহিলা', $qs([], ['gender', ''])];
if ($F['exp'] !== '') $pills[] = ['⭐ ' . TF_EXP[$F['exp']], $qs([], ['exp', ''])];
if ($F['mode'] !== '') $pills[] = [$F['mode'] === 'home' ? '🏠 বাসায় গিয়ে' : '💻 অনলাইনে', $qs([], ['mode', ''])];
if ($F['prem']) $pills[] = ['👑 Premium', $qs([], ['prem', ''])];

// পেজ নম্বর: 1 2 3 4 5 … শেষ
$nums = [];
if ($pages <= 7) $nums = range(1, $pages);
else {
    $nums = [1];
    $from = max(2, min($page - 1, $pages - 4)); $to = min($pages - 1, max($page + 1, 5));
    if ($from > 2) $nums[] = '…';
    for ($i = $from; $i <= $to; $i++) $nums[] = $i;
    if ($to < $pages - 1) $nums[] = '…';
    $nums[] = $pages;
}
?>
<?php partial('Tutors/_Styles'); ?>
<style>
    .tl-filters { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px 22px; margin-bottom: 26px; }
    .tl-filters label { display: flex; align-items: center; gap: 10px; border: 1px solid #9CA3AF; border-radius: 6px; padding: 7px 14px; font-size: 13px; color: #1F2937; cursor: pointer; background: transparent; margin: 0; user-select: none; }
    .tl-filters label:hover { border-color: #00CFFF; }
    .tl-filters input { width: 15px; height: 15px; accent-color: #00CFFF; margin: 0; cursor: pointer; }
    .tl-filters label.on { border-color: #00CFFF; background: #E6F9FF; }
    .tl-acts { display: flex; gap: 10px; }
    .tl-gen { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
    .tl-gen label { display: inline-flex; align-items: center; gap: 8px; border: 1.5px solid #CBD5E1; border-radius: 999px; padding: 8px 18px; font-size: 14px; font-weight: 700; color: #1F2937; cursor: pointer; background: #fff; }
    .tl-gen label i { color: #00B5E0; }
    .tl-gen label.on { border-color: #00CFFF; background: #00CFFF; color: #fff; } .tl-gen label.on i { color: #fff; }
    .tl-gen input { display: none; }
    .tl-count { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 14px; color: #475569; margin: 0 0 18px; }
    .tl-count b { color: #0F172A; font-size: 16px; }
    .tl-quick { border: 1.5px dashed #7DD3FC; background: #F0F9FF; color: #0369A1; font-weight: 700; font-size: 13px; padding: 8px 14px; border-radius: 999px; cursor: pointer; }
    .tl-fn { background: #fff; color: #0284C7; border-radius: 9px; padding: 0 7px; font-size: 12px; margin-left: 3px; }
    .tl-pills { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 14px; }
    .tl-pill { display: inline-flex; align-items: center; gap: 7px; padding: 7px 12px; border-radius: 999px; background: #E6F9FF; border: 1px solid #A5E9FB; color: #075985; font-size: 13px; font-weight: 700; text-decoration: none; }
    .tl-pill i { font-size: 11px; opacity: .7; } .tl-pill:hover { background: #CFF4FF; color: #075985; }
    .tl-pill.clr { background: #fff; border-color: #E2E8F0; color: #64748B; }
    .tl-pg { display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 36px; flex-wrap: wrap; }
    .tl-pg a, .tl-pg span { min-width: 40px; height: 40px; padding: 0 12px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; text-decoration: none; background: #1F2328; color: #fff; }
    .tl-pg a:hover { background: #00B5E0; color: #fff; }
    .tl-pg .on { background: #00CFFF; }
    .tl-pg .dots { background: none; color: #1F2328; min-width: 20px; padding: 0; }
    .tl-pg .off { background: #6B7280; opacity: .6; }
    .tl-empty { background: #fff; border-radius: 12px; padding: 50px 20px; text-align: center; color: #64748B; }
    .tl-empty i { font-size: 42px; color: #B9E9F7; display: block; margin-bottom: 12px; }
    @media (max-width: 991px) { .tl-filters { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (max-width: 700px) { .tl-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; } .tl-pg a, .tl-pg span { min-width: 36px; height: 36px; padding: 0 9px; } }
</style>

<div class="tl">
    <div class="tl-wrap">
        <div class="tl-h">
            <h1 class="ta-h1">Showing <?= e($ViewBag->Title) ?></h1>
            <div class="tl-acts">
                <a class="tl-btn light" href="/Tutors">Go Back</a>
                <button type="button" class="tl-btn" data-tf-open><i class="fas fa-sliders"></i> ফিল্টার<?php if ($ViewBag->Active): ?> <b class="tl-fn"><?= bn_num($ViewBag->Active) ?></b><?php endif; ?></button>
            </div>
        </div>
        <?php if ($pills): ?>
            <div class="tl-pills">
                <?php foreach ($pills as [$pl, $pu]): ?><a href="<?= e($pu) ?>" class="tl-pill" title="সরান"><?= e($pl) ?> <i class="fas fa-xmark"></i></a><?php endforeach; ?>
                <a href="<?= e('/Tutors/All' . ($type !== 'all' ? '?type=' . $type : '')) ?>" class="tl-pill clr"><i class="fas fa-rotate-left"></i> সব মুছুন</a>
            </div>
        <?php endif; ?>
        <div class="tl-count">
            <span><b><?= bn_num((int)$ViewBag->Total) ?></b> জন টিউটর পাওয়া গেছে<?= $ViewBag->Active ? ' (মোট ' . bn_num((int)$ViewBag->All) . ' জনের মধ্যে)' : '' ?></span>
            <button type="button" class="tl-quick" data-tf-open><i class="fas fa-sliders"></i> আপনার দরকার অনুযায়ী খুঁজুন — এলাকা, ক্লাস, বেতন, সময়, বিশ্ববিদ্যালয়</button>
        </div>

        <?php if (!$tutors): ?>
            <div class="tl-empty"><i class="fas fa-user-slash"></i>এই ফিল্টারে কোনো টিউটর পাওয়া যায়নি।<br />কিছু ফিল্টার সরিয়ে দেখুন — অথবা <a href="/Tuition/Circular">টিউশন সার্কুলারে "টিচার লাগবে" পোস্ট</a> দিন, টিচাররা নিজেই Apply করবে।
                <div style="margin-top:14px;"><button type="button" class="tl-btn" data-tf-open><i class="fas fa-sliders"></i> ফিল্টার বদলান</button></div></div>
        <?php else: ?>
            <div class="tl-grid">
                <?php foreach ($tutors as $t) partial('Tutors/_Card', ['t' => $t]); ?>
            </div>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
            <nav class="tl-pg" aria-label="Pages">
                <?php if ($page > 1): ?><a href="<?= e($url(['page' => $page - 1])) ?>" aria-label="আগের পেজ"><i class="fas fa-caret-left"></i></a><?php else: ?><span class="off"><i class="fas fa-caret-left"></i></span><?php endif; ?>
                <?php foreach ($nums as $n): ?>
                    <?php if ($n === '…'): ?><span class="dots">…</span>
                    <?php elseif ($n === $page): ?><span class="on" aria-current="page"><?= $n ?></span>
                    <?php else: ?><a href="<?= e($url(['page' => $n])) ?>"><?= $n ?></a><?php endif; ?>
                <?php endforeach; ?>
                <?php if ($page < $pages): ?><a href="<?= e($url(['page' => $page + 1])) ?>" aria-label="পরের পেজ"><i class="fas fa-caret-right"></i></a><?php else: ?><span class="off"><i class="fas fa-caret-right"></i></span><?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php partial('Tutors/_FilterPanel', ['f' => $F, 'counts' => $ViewBag->Counts, 'areaCounts' => $ViewBag->AreaCounts ?: [], 'type' => $type, 'total' => (int)$ViewBag->Total]); ?>
