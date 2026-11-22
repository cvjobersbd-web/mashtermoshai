<?php
/** প্রোফাইলে ব্রেইন স্কোর — $uid, $isOwner */
$bs = null;
try { $bs = db()->GameBrainScore((int)$uid); } catch (Throwable $e) { $bs = null; }
if ($bs && $bs->Pct !== null): ?>
    <a href="/Game" title="<?= e('গেমে ' . bn_num($bs->Pct) . '% প্লেয়ারের চেয়ে ভালো (শেষ ৬০ দিন, ' . bn_num($bs->Games) . 'টা পাজল)') ?>" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#FEF3C7,#FDE68A);color:#78350F;font-weight:800;padding:3px 11px;border-radius:20px;text-decoration:none;font-size:13.5px;">🧠 ব্রেইন স্কোর <?= bn_num($bs->Pct) ?>%</a>
<?php elseif ($isOwner): ?>
    <a href="/Game" style="display:inline-flex;align-items:center;gap:6px;background:#F1F5F9;color:#334155;font-weight:700;padding:3px 11px;border-radius:20px;text-decoration:none;font-size:13px;"><i class="fas fa-gamepad" style="color:#F97316;"></i> <?= $bs ? 'আর ' . bn_num($bs->Need) . 'টা গেম খেলে ব্রেইন স্কোর পান' : 'গেম খেলে ব্রেইন স্কোর পান' ?></a>
<?php endif;
