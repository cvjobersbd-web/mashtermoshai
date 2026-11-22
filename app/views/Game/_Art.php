<?php
/** গেম কার্ডের ছোট ছবি (SVG) — $g = zip | sudoku | patches */
if ($g === 'zip'): ?>
<svg viewBox="0 0 120 120" width="110" height="110" aria-hidden="true">
    <rect x="2" y="2" width="116" height="116" rx="14" fill="#1E293B"/>
    <path d="M24 24 H96 V48 H24 V72 H96 V96 H24" fill="none" stroke="url(#zg)" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
    <defs><linearGradient id="zg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FB923C"/><stop offset="1" stop-color="#EF4444"/></linearGradient></defs>
    <?php foreach ([[24, 24, 1], [96, 48, 2], [24, 72, 3], [24, 96, 4]] as [$x, $y, $n]): ?>
        <circle cx="<?= $x ?>" cy="<?= $y ?>" r="11" fill="#fff"/><text x="<?= $x ?>" y="<?= $y + 4.5 ?>" text-anchor="middle" font-size="13" font-weight="800" fill="#0F172A" font-family="sans-serif"><?= $n ?></text>
    <?php endforeach; ?>
</svg>
<?php elseif ($g === 'sudoku'): ?>
<svg viewBox="0 0 120 120" width="110" height="110" aria-hidden="true">
    <rect x="2" y="2" width="116" height="116" rx="14" fill="#fff" stroke="#93C5FD" stroke-width="3"/>
    <?php for ($i = 1; $i < 6; $i++): $p = 2 + $i * 116 / 6; ?>
        <line x1="<?= $p ?>" y1="4" x2="<?= $p ?>" y2="116" stroke="#BFDBFE" stroke-width="<?= $i === 3 ? 3 : 1 ?>"/>
        <line x1="4" y1="<?= $p ?>" x2="116" y2="<?= $p ?>" stroke="#BFDBFE" stroke-width="<?= $i % 2 === 0 ? 3 : 1 ?>"/>
    <?php endfor; ?>
    <?php foreach ([[0, 1, 2], [0, 4, 5], [1, 0, 6], [2, 3, 1], [3, 5, 3], [4, 2, 4], [5, 1, 1], [5, 4, 2]] as [$r, $c, $v]): ?>
        <text x="<?= 2 + $c * 116 / 6 + 116 / 12 ?>" y="<?= 2 + $r * 116 / 6 + 116 / 12 + 5 ?>" text-anchor="middle" font-size="14" font-weight="800" fill="#1D4ED8" font-family="sans-serif"><?= $v ?></text>
    <?php endforeach; ?>
</svg>
<?php else: ?>
<svg viewBox="0 0 120 120" width="110" height="110" aria-hidden="true">
    <rect x="2" y="2" width="116" height="116" rx="14" fill="#1E293B"/>
    <rect x="10" y="10" width="44" height="64" rx="8" fill="#0F766E" stroke="#2DD4BF" stroke-width="3"/>
    <rect x="62" y="10" width="48" height="30" rx="8" fill="#9A3412" stroke="#FB923C" stroke-width="3"/>
    <rect x="62" y="48" width="48" height="62" rx="8" fill="#5B21B6" stroke="#A78BFA" stroke-width="3"/>
    <rect x="10" y="82" width="44" height="28" rx="8" fill="#854D0E" stroke="#FACC15" stroke-width="3"/>
    <text x="32" y="47" text-anchor="middle" font-size="18" font-weight="800" fill="#fff" font-family="sans-serif">8</text>
    <text x="86" y="31" text-anchor="middle" font-size="15" font-weight="800" fill="#fff" font-family="sans-serif">4</text>
    <text x="86" y="85" text-anchor="middle" font-size="18" font-weight="800" fill="#fff" font-family="sans-serif">9</text>
</svg>
<?php endif;
