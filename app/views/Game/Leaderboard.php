<?php
$ViewData['Title'] = t('গেম লিডারবোর্ড', 'Game Leaderboard');
$ViewData['FullTitle'] = t('আজকের গেম লিডারবোর্ড — কে সবার আগে?', 'Today\'s Game Leaderboard') . ' | ' . seo_brand();
$ViewData['Description'] = t('মাষ্টার মশাই ব্রেইন গেমের আজকের লিডারবোর্ড — শিক্ষার্থী আর টিচারদের র‍্যাঙ্ক। প্রতিদিন রাত ১২টায় নতুন করে শুরু।', 'Today\'s leaderboard for Mashter Moshai brain games. Resets every midnight.');
$rows = is_array($Model) ? $Model : [];
$tab = $ViewBag->Tab; $me = $ViewBag->Me; $mine = $ViewBag->Mine; $rs = (int)$ViewBag->ResetIn;
$tabs = ['all' => ['সবাই', 'fa-users'], 'student' => ['শিক্ষার্থী', 'fa-user-graduate'], 'teacher' => ['টিচার', 'fa-chalkboard-user']];
partial('Game/_Styles');
?>
<style>
    .lb-tabs { display: flex; gap: 8px; margin: 18px 0 14px; flex-wrap: wrap; }
    .lb-tabs a { flex: 1; min-width: 100px; text-align: center; padding: 11px 10px; border-radius: 12px; background: #fff; border: 1.5px solid #E2E8F0; color: #334155; font-weight: 800; text-decoration: none; font-size: 14.5px; }
    .lb-tabs a.on { background: #08224A; border-color: #08224A; color: #fff; }
    .lb-me { display: flex; align-items: center; gap: 14px; background: linear-gradient(135deg, #FEF3C7, #FDE68A); border-radius: 16px; padding: 14px 16px; margin-bottom: 14px; }
    .lb-me .big { font-size: 28px; font-weight: 800; color: #78350F; }
    .lb-me small { color: #92400E; font-weight: 600; }
    .lb-wrap { max-width: 860px; margin: 0 auto; }
    .lb-y { display: flex; gap: 10px; flex-wrap: wrap; }
    .lb-y span { background: #F8FAFC; border-radius: 10px; padding: 7px 11px; font-size: 13.5px; color: #334155; font-weight: 600; }
</style>
<div class="gm-page lb-wrap">
    <section class="gm-hero" style="padding:24px 24px;">
        <h1><i class="fas fa-trophy" style="color:#FACC15;"></i> <?= t('আজকের লিডারবোর্ড', 'Today\'s leaderboard') ?></h1>
        <p><?= t('আজ রাত ১২টা থেকে যত পাজল সমাধান করেছেন তার মোট পয়েন্ট। দ্রুত শেষ করলে আর কঠিন লেভেলে বেশি পয়েন্ট।', 'Total points from puzzles solved since midnight. Faster and harder = more points.') ?></p>
        <div class="gm-guest" style="margin-top:14px;"><i class="far fa-clock"></i> <?= t('রিসেট হবে', 'Resets in') ?> <b data-gm-reset="<?= $rs ?>"></b> <?= t('পরে', '') ?> · <a href="/Game" style="background:#fff;color:#08224A;"><i class="fas fa-gamepad"></i> <?= t('খেলতে যান', 'Play now') ?></a></div>
    </section>

    <div class="lb-tabs">
        <?php foreach ($tabs as $k => [$lb, $ic]): ?><a href="/Game/Leaderboard?tab=<?= $k ?>" class="<?= $tab === $k ? 'on' : '' ?>"><i class="fas <?= $ic ?>"></i> <?= $lb ?></a><?php endforeach; ?>
    </div>

    <?php if ($mine && $mine->Solves > 0): ?>
        <div class="lb-me">
            <div class="big">#<?= bn_num($mine->Rank) ?></div>
            <div style="flex:1;"><b style="color:#78350F;"><?= t('আপনার অবস্থান', 'Your position') ?></b><br /><small><?= bn_num($mine->Players) ?> <?= t('জনের মধ্যে', 'players') ?> · <?= bn_num($mine->Pts) ?> <?= t('পয়েন্ট', 'points') ?> · <?= bn_num($mine->Solves) ?>টা সমাধান</small></div>
        </div>
    <?php elseif ($me !== null && $mine): ?>
        <div class="lb-me"><div class="big">🎯</div><div><b style="color:#78350F;"><?= t('আজ এখনো খেলেননি', 'You haven\'t played today') ?></b><br /><small><?= t('একটা পাজল সমাধান করলেই লিডারবোর্ডে নাম উঠবে!', 'Solve one puzzle to join!') ?></small></div></div>
    <?php elseif ($me === null): ?>
        <div class="lb-me"><div class="big">🔒</div><div style="flex:1;"><b style="color:#78350F;"><?= t('লিডারবোর্ডে নাম তুলতে লগইন করুন', 'Log in to join the leaderboard') ?></b></div><a href="/Account/Login?returnUrl=%2FGame%2FLeaderboard" style="background:#78350F;color:#fff;padding:8px 14px;border-radius:10px;font-weight:800;text-decoration:none;"><?= t('লগইন', 'Log in') ?></a></div>
    <?php endif; ?>

    <div class="gm-row2">
        <div class="gm-box">
            <h3><i class="fas fa-ranking-star" style="color:#F59E0B;"></i> <?= $tabs[$tab][0] ?> — <?= t('টপ ১০০', 'Top 100') ?></h3>
            <?php if (!$rows): ?>
                <div class="gm-empty">🏁 <?= t('আজ এখনো কেউ খেলেনি — প্রথম হওয়ার সুযোগ আপনার!', 'No one has played today — be the first!') ?></div>
            <?php else: foreach ($rows as $r): partial('Game/_LbRow', ['r' => $r, 'me' => $me]); endforeach; endif; ?>
        </div>
        <div>
            <div class="gm-box"><?php partial('Game/_Vs', ['vs' => $ViewBag->Vs]); ?></div>
            <?php if (!empty($ViewBag->Yesterday)): ?>
                <div class="gm-box" style="margin-top:18px;">
                    <h3><i class="fas fa-crown" style="color:#F59E0B;"></i> <?= t('গতকালের চ্যাম্পিয়ন', 'Yesterday\'s champions') ?></h3>
                    <div class="lb-y"><?php foreach ($ViewBag->Yesterday as $y): ?><span><?= [1 => '🥇', 2 => '🥈', 3 => '🥉'][$y->Rank] ?? '' ?> <?= e($y->FullName ?: $y->UserName) ?> · <?= bn_num($y->Pts) ?></span><?php endforeach; ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php partial('Game/_Countdown'); ?>
