<?php
/** Who viewed your profile — শেষ ৯০ দিনে কে কে আপনার প্রোফাইল দেখেছেন (LinkedIn এর মতো) */
$viewers = is_array($Model) ? $Model : [];
$st = (array)$ViewBag->Stats;
$role = (string)$ViewBag->Role;
$page = (int)$ViewBag->Page;
$me = $ViewBag->User;
$diff = (int)$st['views7'] - (int)$st['prev7'];
$mx = max(1, max(array_column($st['series'], 'n') ?: [0]));
$ViewData['Title'] = 'Profile Views';
$ViewData['FullTitle'] = 'Who viewed your profile | মাষ্টার মশাই';
$ViewData['Robots'] = 'noindex, nofollow';
$q = fn(array $o) => '/Profile/Views' . (($a = array_filter(array_merge(['role' => $role], $o))) ? '?' . http_build_query($a) : '');
?>
<style>
    .pvp { background: #F0F3F8; min-height: 80vh; padding: 26px 0 60px; }
    .pvp-wrap { max-width: 860px; margin: 0 auto; padding: 0 16px; }
    .pvp-back { display: inline-flex; gap: 8px; align-items: center; color: #334155; font-weight: 700; text-decoration: none; font-size: 14px; margin-bottom: 12px; }
    .pvp-back:hover { color: #0369A1; }
    .pvp h1 { margin: 0; font-family: var(--font-heading); font-size: clamp(24px, 3vw, 32px); font-weight: 700; color: #08224A; display: flex; gap: 12px; align-items: center; }
    .pvp h1 i { color: #1D63E0; }
    .pvp-sub { color: #64748B; margin: 6px 0 20px; font-size: 14.5px; }
    .pvp-card { background: #fff; border: 1px solid #E3E9F1; border-radius: 16px; box-shadow: 0 2px 12px rgba(15,27,45,.05); }
    .pvp-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 14px; }
    .pvp-stat { padding: 16px 18px; }
    .pvp-stat small { display: block; color: #64748B; font-weight: 600; font-size: 13px; }
    .pvp-stat b { display: block; font-size: 28px; color: #08224A; margin-top: 4px; line-height: 1.1; }
    .pvp-stat span { font-size: 12.5px; color: #64748B; }
    .pvp-stat .up { color: #15803D; font-weight: 700; } .pvp-stat .down { color: #B91C1C; font-weight: 700; }
    .pvp-chart { padding: 16px 18px 12px; margin-bottom: 18px; }
    .pvp-chart h3 { margin: 0 0 12px; font-size: 14.5px; color: #334155; font-weight: 700; }
    .pvp-bars { display: flex; align-items: flex-end; gap: 4px; height: 110px; border-bottom: 1px solid #E2E8F0; }
    .pvp-bars i { flex: 1; background: #BFDBFE; border-radius: 4px 4px 0 0; min-height: 2px; position: relative; }
    .pvp-bars i.on { background: linear-gradient(180deg, #3B82F6, #60A5FA); }
    .pvp-bars i:hover::after { content: attr(data-t); position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: #08224A; color: #fff; font-size: 11.5px; padding: 3px 8px; border-radius: 6px; white-space: nowrap; font-style: normal; z-index: 2; }
    .pvp-axis { display: flex; justify-content: space-between; font-size: 11.5px; color: #94A3B8; margin-top: 6px; }
    .pvp-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
    .pvp-chips a { padding: 8px 15px; border-radius: 999px; background: #fff; border: 1px solid #DCE5EF; color: #334155; font-size: 13.5px; font-weight: 700; text-decoration: none; }
    .pvp-chips a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .pvp-list { overflow: hidden; }
    .pvp-row { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-bottom: 1px solid #EEF2F7; }
    .pvp-row:last-child { border-bottom: none; }
    .pvp-row.new { background: #F5FAFF; }
    .pvp-av { width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0; position: relative; overflow: hidden; background: linear-gradient(135deg, #08224A, #134856); color: #fff;
        font-weight: 700; font-size: 17px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
    .pvp-av img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .pvp-who { flex: 1; min-width: 0; }
    .pvp-who a.n { font-weight: 700; color: #0F172A; font-size: 15.5px; text-decoration: none; }
    .pvp-who a.n:hover { text-decoration: underline; }
    .pvp-role { display: inline-block; font-size: 10.5px; font-weight: 700; padding: 1px 7px; border-radius: 8px; margin-left: 6px; vertical-align: 2px; background: #E0F2FE; color: #0369A1; }
    .pvp-role.t { background: #DCFCE7; color: #15803D; }
    .pvp-newb { display: inline-block; font-size: 10.5px; font-weight: 800; padding: 1px 7px; border-radius: 8px; margin-left: 4px; vertical-align: 2px; background: #EF4444; color: #fff; }
    .pvp-who p { margin: 2px 0 0; color: #475569; font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pvp-who small { color: #94A3B8; font-size: 12.5px; }
    .pvp-btn { flex-shrink: 0; padding: 8px 16px; border-radius: 999px; border: 1.5px solid #1D63E0; color: #1D63E0; font-weight: 700; font-size: 13.5px; text-decoration: none; white-space: nowrap; }
    .pvp-btn:hover { background: #EFF6FF; color: #1D63E0; }
    .pvp-empty { padding: 44px 20px; text-align: center; color: #64748B; }
    .pvp-empty i { font-size: 38px; color: #B9D6E6; display: block; margin-bottom: 12px; }
    .pvp-empty a { color: #0369A1; font-weight: 700; }
    .pvp-more { display: block; text-align: center; margin-top: 14px; color: #1D63E0; font-weight: 700; text-decoration: none; }
    .pvp-note { font-size: 12.5px; color: #94A3B8; margin-top: 14px; display: flex; gap: 7px; }
    @media (max-width: 640px) {
        .pvp { padding-top: 16px; } .pvp-wrap { padding: 0 10px; }
        .pvp-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .pvp-stat { padding: 12px 10px; } .pvp-stat b { font-size: 22px; } .pvp-stat small { font-size: 11.5px; } .pvp-stat span { font-size: 11px; }
        .pvp-row { padding: 12px; gap: 10px; } .pvp-av { width: 44px; height: 44px; font-size: 15px; }
        .pvp-btn { padding: 7px 12px; font-size: 12.5px; }
        .pvp-bars { gap: 2px; height: 90px; }
    }
</style>

<div class="pvp">
    <div class="pvp-wrap">
        <a class="pvp-back" href="/Profile/Show/<?= (int)current_user_id() ?>"><i class="fas fa-arrow-left"></i> আমার প্রোফাইল</a>
        <h1><i class="fas fa-eye"></i> Who viewed your profile</h1>
        <p class="pvp-sub">শেষ ৯০ দিনে যারা আপনার প্রোফাইল দেখেছেন। একজন একই দিনে কয়েকবার দেখলে একবারই গোনা হয়।</p>

        <div class="pvp-stats">
            <div class="pvp-card pvp-stat"><small>Profile views</small><b><?= (int)$st['views90'] ?></b><span>শেষ ৯০ দিন</span></div>
            <div class="pvp-card pvp-stat"><small>This week</small><b><?= (int)$st['views7'] ?></b>
                <span class="<?= $diff >= 0 ? 'up' : 'down' ?>"><i class="fas fa-arrow-<?= $diff >= 0 ? 'up' : 'down' ?>"></i> <?= abs($diff) ?></span> <span>vs আগের সপ্তাহ</span></div>
            <div class="pvp-card pvp-stat"><small>Viewers</small><b><?= (int)$st['unique90'] ?></b><span><?= (int)$st['teachers90'] ?> Teacher · <?= (int)$st['students90'] ?> Student</span></div>
        </div>

        <div class="pvp-card pvp-chart">
            <h3>শেষ ৩০ দিন</h3>
            <div class="pvp-bars"><?php foreach ($st['series'] as $d): ?><i class="<?= $d['n'] ? 'on' : '' ?>" style="height:<?= $d['n'] ? max(6, (int)round($d['n'] / $mx * 100)) : 2 ?>%" data-t="<?= e(date('d M', strtotime($d['date']))) ?>: <?= (int)$d['n'] ?>"></i><?php endforeach; ?></div>
            <div class="pvp-axis"><span><?= e(date('d M', strtotime($st['series'][0]['date']))) ?></span><span>আজ</span></div>
        </div>

        <nav class="pvp-chips" aria-label="Filter">
            <a href="/Profile/Views" class="<?= $role === '' ? 'on' : '' ?>">All</a>
            <a href="/Profile/Views?role=Teacher" class="<?= $role === 'Teacher' ? 'on' : '' ?>">Teachers</a>
            <a href="/Profile/Views?role=User" class="<?= $role === 'User' ? 'on' : '' ?>">Students</a>
        </nav>

        <div class="pvp-card pvp-list">
            <?php if (!$viewers): ?>
                <div class="pvp-empty"><i class="far fa-eye"></i>
                    <?= $role === '' ? 'শেষ ৯০ দিনে এখনো কেউ আপনার প্রোফাইল দেখেননি।<br>প্রোফাইল সম্পূর্ণ করুন আর <a href="/Feed">Educational Feed</a>-এ পোস্ট দিন — বেশি মানুষ আপনাকে খুঁজে পাবে।' : 'এই ফিল্টারে কেউ নেই।' ?></div>
            <?php endif; ?>
            <?php foreach ($viewers as $v): ?>
                <div class="pvp-row<?= $v->IsNew ? ' new' : '' ?>">
                    <a class="pvp-av" href="/Profile/Show/<?= (int)$v->Id ?>" aria-hidden="true" tabindex="-1"><?= e($v->Initials) ?><?php if ($v->Image !== ''): ?><img src="<?= e($v->Image) ?>" alt="" loading="lazy" onerror="this.remove()" /><?php endif; ?></a>
                    <div class="pvp-who">
                        <a class="n" href="/Profile/Show/<?= (int)$v->Id ?>"><?= e($v->Name) ?></a><span class="pvp-role<?= $v->Role === 'Teacher' ? ' t' : '' ?>"><?= $v->Role ?></span><?php if ($v->IsNew): ?><span class="pvp-newb">New</span><?php endif; ?>
                        <p><?= e($v->Sub) ?></p>
                        <small><i class="far fa-clock"></i> Viewed <?= e(time_ago($v->LastViewedAt)) ?><?= $v->Visits > 1 ? ' · ' . (int)$v->Visits . ' বার দেখেছেন' : '' ?></small>
                    </div>
                    <a class="pvp-btn" href="/Profile/Show/<?= (int)$v->Id ?>">View profile</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($ViewBag->HasMore): ?><a class="pvp-more" href="<?= e($q(['page' => $page + 1])) ?>">আরো দেখুন <i class="fas fa-chevron-down"></i></a><?php endif; ?>
        <div class="pvp-note"><i class="fas fa-circle-info"></i><span>আপনি অন্য কারো প্রোফাইল দেখলে তিনিও এভাবে আপনার নাম দেখতে পাবেন।</span></div>
    </div>
</div>
