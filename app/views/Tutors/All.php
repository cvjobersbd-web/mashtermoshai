<?php
/**
 * সব টিউটর (See All) — জেলা অনুযায়ী ফিল্টার, ১৬ জন করে পেজ
 * @var Obj[] $Model  এই পেজের টিউটর
 */
$tutors = is_array($Model) ? $Model : [];
$type = (string)$ViewBag->Type;
$sel = is_array($ViewBag->Cities) ? $ViewBag->Cities : [];
$page = (int)$ViewBag->Page; $pages = (int)$ViewBag->Pages;
$ViewData['Title'] = (string)$ViewBag->Title;
$ViewData['FullTitle'] = 'Showing ' . $ViewBag->Title . ($page > 1 ? " — Page $page" : '') . ' | মাষ্টার মশাই';
$url = function (array $over) use ($type, $sel) {
    $q = array_merge(['type' => $type !== 'all' ? $type : null, 'city' => $sel ?: null], $over);
    $q = array_filter($q, fn($v) => $v !== null && $v !== '' && $v !== 1);
    return '/Tutors/All' . ($q ? '?' . preg_replace('/%5B\d+%5D/', '%5B%5D', http_build_query($q)) : '');
};
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
    .tl-count { font-size: 13.5px; color: #64748B; margin: -8px 0 18px; }
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
            <h2>Showing <?= e($ViewBag->Title) ?></h2>
            <div class="tl-acts">
                <a class="tl-btn light" href="/Tutors">Go Back</a>
                <button type="submit" form="tlFilter" class="tl-btn"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>
        <form id="tlFilter" action="/Tutors/All" method="get">
            <?php if ($type !== 'all'): ?><input type="hidden" name="type" value="<?= e($type) ?>" /><?php endif; ?>
            <div class="tl-filters">
                <label class="<?= !$sel ? 'on' : '' ?>"><input type="checkbox" id="tlAll" <?= !$sel ? 'checked' : '' ?> /> All</label>
                <?php foreach (TutorsController::CITIES as $c): ?>
                    <label class="<?= in_array($c, $sel, true) ? 'on' : '' ?>"><input type="checkbox" name="city[]" value="<?= e($c) ?>" <?= in_array($c, $sel, true) ? 'checked' : '' ?> /> <?= e($c) ?></label>
                <?php endforeach; ?>
            </div>
        </form>
        <div class="tl-count"><?= (int)$ViewBag->Total ?> জন টিউটর<?= $sel ? ' — ' . e(implode(', ', $sel)) : '' ?></div>

        <?php if (!$tutors): ?>
            <div class="tl-empty"><i class="fas fa-user-slash"></i>এই ফিল্টারে কোনো টিউটর পাওয়া যায়নি। অন্য জেলা বেছে দেখুন।</div>
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

<?php section_start('Scripts'); ?>
<script>
(function () {
    // "All" চাপলে বাকিগুলো খালি; কোনো জেলা চাপলে "All" খালি — সাথে সাথে ফিল্টার
    var f = document.getElementById('tlFilter'), all = document.getElementById('tlAll');
    var boxes = f.querySelectorAll('input[name="city[]"]');
    function paint() { f.querySelectorAll('label').forEach(function (l) { l.classList.toggle('on', l.querySelector('input').checked); }); }
    all.addEventListener('change', function () { if (all.checked) boxes.forEach(function (b) { b.checked = false; }); else all.checked = true; paint(); f.submit(); });
    boxes.forEach(function (b) { b.addEventListener('change', function () { var any = Array.prototype.some.call(boxes, function (x) { return x.checked; }); all.checked = !any; paint(); f.submit(); }); });
})();
</script>
<?php section_end(); ?>
