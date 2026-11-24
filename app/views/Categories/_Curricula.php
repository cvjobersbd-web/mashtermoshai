<?php
/**
 * ব্রিটিশ কারিকুলাম আর জাতীয় শিক্ষাক্রম — অন্য ক্যাটাগরির মতো একই কার্ড (Categories/_Card), চাপলে সেই মিডিয়াম / ক্লাসের টিউটর তালিকা
 */
foreach (tf_curriculum_groups() as $g): ?>
    <section class="ct-group" id="grp-<?= e($g['id']) ?>">
        <h2><?= e($g['name']) ?></h2>
        <div class="cc-grid">
            <?php foreach ($g['items'] as $it) { [$name, $icon, $q] = $it; partial('Categories/_Card', ['c' => obj(['Name' => $name, 'Url' => tf_link($q), 'ImageUrl' => '', 'Icon' => $icon, 'GroupName' => $g['name'] . ' ' . ($it[3] ?? '')])]); } ?>
        </div>
    </section>
<?php endforeach;
