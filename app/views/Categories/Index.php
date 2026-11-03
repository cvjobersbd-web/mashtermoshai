<?php
/** সব টিউশন ক্যাটাগরি — গ্রুপ অনুযায়ী (English Medium, Bangla Medium, Arts & Crafts ...) */
$groups = is_array($Model) ? $Model : [];
$ViewData['Title'] = 'Categories';
$ViewData['FullTitle'] = 'Tuition Categories — Find Tutors By Class, Subject & Skill | মাষ্টার মশাই';
$ViewData['Description'] = 'Browse all tuition categories at মাষ্টার মশাই — English Medium, Bangla Medium, Admission Help, IELTS & international exams, Arts & Crafts, Religious Studies and more.';
$total = array_sum(array_map(fn($g) => count($g->Categories), $groups));
?>
<?php partial('Categories/_CardStyles'); ?>
<style>
    .ct-page { background: #F4F6FA; padding: 34px 0 70px; min-height: 70vh; }
    .ct-wrap { max-width: 1240px; margin: 0 auto; padding: 0 20px; }
    .ct-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 8px; }
    .ct-head h1 { margin: 0; font-family: var(--font-heading); font-size: clamp(26px, 3.2vw, 36px); font-weight: 700; color: #08224A; }
    .ct-head p { margin: 6px 0 0; color: #64748B; font-size: 15px; }
    .ct-search { position: relative; width: 320px; max-width: 100%; }
    .ct-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; }
    .ct-search input { width: 100%; height: 46px; border: 1px solid #DCE5EF; border-radius: 12px; background: #fff; padding: 0 14px 0 42px; font-size: 15px; outline: none; font-family: inherit; color: #08224A; }
    .ct-search input:focus { border-color: #02B8F5; box-shadow: 0 0 0 3px rgba(2,184,245,.12); }
    .ct-chips { display: flex; gap: 8px; flex-wrap: wrap; margin: 18px 0 6px; }
    .ct-chips a { padding: 8px 15px; border-radius: 999px; background: #fff; border: 1px solid #DCE5EF; color: #334155; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: border-color .2s, color .2s; }
    .ct-chips a:hover { border-color: #02B8F5; color: #08224A; }
    .ct-group { margin-top: 38px; scroll-margin-top: 100px; }
    .ct-group h2 { margin: 0 0 16px; font-family: var(--font-heading); font-size: clamp(22px, 2.4vw, 30px); font-weight: 500; color: #1E293B; }
    .ct-none { display: none; text-align: center; color: #64748B; padding: 50px 16px; }
    .ct-empty { text-align: center; padding: 60px 16px; color: #64748B; background: #fff; border-radius: 16px; border: 1px dashed #DCE5EF; margin-top: 24px; }
    @media (max-width: 640px) {
        .ct-page { padding-top: 22px; }
        .ct-wrap { padding: 0 12px; }
        .ct-search { width: 100%; }
        .ct-group { margin-top: 28px; }
    }
</style>

<div class="ct-page">
    <div class="ct-wrap">
        <div class="ct-head">
            <div>
                <h1>All Categories</h1>
                <p>ক্লাস, বিষয় বা দক্ষতা বেছে নিন — সেই ক্যাটাগরির অভিজ্ঞ টিউটর খুঁজে পাবেন। (<?= (int)$total ?>টি ক্যাটাগরি)</p>
            </div>
            <?php if ($total > 0): ?>
                <label class="ct-search"><i class="fas fa-magnifying-glass"></i><input type="search" id="ctSearch" placeholder="ক্যাটাগরি খুঁজুন — যেমন: KG, IELTS, Guitar" autocomplete="off" /></label>
            <?php endif; ?>
        </div>

        <?php if (!$groups): ?>
            <div class="ct-empty"><i class="fas fa-layer-group fa-2x" style="color:#B9D6E6;"></i><p style="margin:10px 0 0;">এখনো কোনো ক্যাটাগরি যোগ করা হয়নি।</p></div>
        <?php else: ?>
            <nav class="ct-chips" aria-label="Category groups">
                <?php foreach ($groups as $g): ?><a href="#grp-<?= (int)$g->Id ?>"><?= e($g->Name) ?></a><?php endforeach; ?>
            </nav>

            <?php foreach ($groups as $g): ?>
                <section class="ct-group" id="grp-<?= (int)$g->Id ?>">
                    <h2><?= e($g->Name) ?></h2>
                    <div class="cc-grid">
                        <?php foreach ($g->Categories as $c) partial('Categories/_Card', ['c' => $c]); ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <div class="ct-none" id="ctNone"><i class="fas fa-magnifying-glass fa-2x" style="color:#B9D6E6;"></i><p style="margin:10px 0 0;">এই নামে কোনো ক্যাটাগরি পাওয়া যায়নি।</p></div>
        <?php endif; ?>
    </div>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var q = document.getElementById('ctSearch'); if (!q) return;
    q.addEventListener('input', function () {
        var v = q.value.trim().toLowerCase(), any = false;
        document.querySelectorAll('.ct-group').forEach(function (g) {
            var shown = 0;
            g.querySelectorAll('.cc-card').forEach(function (c) {
                var ok = !v || (c.getAttribute('data-name') || '').indexOf(v) !== -1;
                c.style.display = ok ? '' : 'none'; if (ok) shown++;
            });
            g.style.display = shown ? '' : 'none'; if (shown) any = true;
        });
        document.querySelector('.ct-chips').style.display = v ? 'none' : '';
        document.getElementById('ctNone').style.display = any ? 'none' : 'block';
    });
})();
</script>
<?php section_end(); ?>
