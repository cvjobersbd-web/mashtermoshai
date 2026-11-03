<?php
/**
 * নিজের প্রোফাইলের Overview তে "Profile views" কার্ড (LinkedIn এর মতো) — শুধু মালিক দেখেন।
 * @var array $pv ['stats' => GetProfileViewStats, 'recent' => GetProfileViewers(…, 5)]
 */
$st = $pv['stats']; $recent = $pv['recent'];
$diff = $st['views7'] - $st['prev7'];
$newCount = count(array_filter($recent, fn($r) => $r->IsNew));
$mx = max(1, max(array_column($st['series'], 'n') ?: [0]));
$names = array_map(fn($r) => explode(' ', trim($r->Name))[0], array_slice($recent, 0, 2));
?>
<style>
    .pvc { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 16px; align-items: center; padding: 16px 18px; margin-bottom: 16px; text-decoration: none; color: inherit; }
    .pvc:hover { box-shadow: 0 10px 26px rgba(8,34,74,.09); }
    .pvc-ic { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #E0F2FE, #DBEAFE); color: #1D63E0; display: flex; align-items: center; justify-content: center; font-size: 22px; position: relative; }
    .pvc-ic em { position: absolute; top: -6px; right: -6px; background: #EF4444; color: #fff; font-style: normal; font-size: 11px; font-weight: 800; min-width: 20px; height: 20px; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 0 5px; border: 2px solid #fff; }
    .pvc-t { font-size: 13px; color: #64748B; font-weight: 600; }
    .pvc-v { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; margin-top: 2px; }
    .pvc-v b { font-size: 24px; color: #08224A; line-height: 1.1; }
    .pvc-v span { font-size: 13px; color: #64748B; }
    .pvc-v .up { color: #15803D; font-weight: 700; } .pvc-v .down { color: #B91C1C; font-weight: 700; }
    .pvc-who { display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 13px; color: #334155; min-width: 0; }
    .pvc-faces { display: flex; flex-shrink: 0; }
    .pvc-faces span { width: 28px; height: 28px; border-radius: 50%; border: 2px solid #fff; margin-left: -8px; background: linear-gradient(135deg, #08224A, #134856); color: #fff; font-size: 10.5px; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
    .pvc-faces span:first-child { margin-left: 0; }
    .pvc-faces img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .pvc-who p { margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pvc-r { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
    .pvc-bars { display: flex; align-items: flex-end; gap: 2px; height: 34px; }
    .pvc-bars i { width: 4px; border-radius: 2px; background: #BFDBFE; min-height: 3px; }
    .pvc-bars i.on { background: #3B82F6; }
    .pvc-go { display: inline-flex; gap: 6px; align-items: center; font-weight: 700; font-size: 13.5px; color: #1D63E0; white-space: nowrap; }
    @media (max-width: 640px) {
        .pvc { grid-template-columns: auto minmax(0, 1fr); padding: 14px; gap: 12px; }
        .pvc-r { grid-column: 1 / -1; flex-direction: row; justify-content: space-between; align-items: center; }
        .pvc-ic { width: 44px; height: 44px; font-size: 19px; }
    }
</style>
<a class="pf-card pvc" href="/Profile/Views" aria-label="Who viewed your profile">
    <span class="pvc-ic"><i class="fas fa-eye"></i><?php if ($newCount): ?><em><?= $newCount ?></em><?php endif; ?></span>
    <div style="min-width:0;">
        <div class="pvc-t">Profile views · last 90 days</div>
        <div class="pvc-v"><b><?= (int)$st['views90'] ?></b>
            <span><?= (int)$st['unique90'] ?> জন দেখেছেন</span>
            <?php if ($st['views7'] || $st['prev7']): ?><span class="<?= $diff >= 0 ? 'up' : 'down' ?>"><i class="fas fa-arrow-<?= $diff >= 0 ? 'up' : 'down' ?>"></i> <?= abs($diff) ?> this week</span><?php endif; ?>
        </div>
        <?php if ($recent): ?>
            <div class="pvc-who">
                <span class="pvc-faces"><?php foreach (array_slice($recent, 0, 4) as $r): ?><span title="<?= e($r->Name) ?>"><?= e($r->Initials) ?><?php if ($r->Image !== ''): ?><img src="<?= e($r->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></span><?php endforeach; ?></span>
                <p><b><?= e(implode(', ', $names)) ?></b><?= $st['unique90'] > count($names) ? ' এবং আরো ' . ((int)$st['unique90'] - count($names)) . ' জন' : '' ?> আপনার প্রোফাইল দেখেছেন</p>
            </div>
        <?php else: ?>
            <div class="pvc-who"><p>এখনো কেউ আপনার প্রোফাইল দেখেননি — প্রোফাইল সম্পূর্ণ করুন, পোস্ট দিন।</p></div>
        <?php endif; ?>
    </div>
    <div class="pvc-r">
        <span class="pvc-bars" aria-hidden="true" title="শেষ ৩০ দিন"><?php foreach ($st['series'] as $d): ?><i class="<?= $d['n'] ? 'on' : '' ?>" style="height:<?= $d['n'] ? max(15, (int)round($d['n'] / $mx * 100)) : 9 ?>%"></i><?php endforeach; ?></span>
        <span class="pvc-go">See who viewed <i class="fas fa-arrow-right"></i></span>
    </div>
</a>
