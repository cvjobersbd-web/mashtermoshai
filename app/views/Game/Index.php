<?php
$ViewData['Title'] = t('গেম', 'Games');
$ViewData['FullTitle'] = t('ব্রেইন গেম — Zip, Mini Sudoku, Patches', 'Brain Games — Zip, Mini Sudoku, Patches') . ' | ' . seo_brand();
$ViewData['Description'] = t('মাষ্টার মশাই এর ব্রেইন গেম — Zip, Mini Sudoku আর Patches খেলুন, সময় আর পয়েন্ট দেখুন, লিডারবোর্ডে নিজের র‍্যাঙ্ক দেখুন।', 'Play Zip, Mini Sudoku and Patches on Mashter Moshai — beat the clock, earn points and climb the daily leaderboard.');
$meta = $ViewBag->Meta; $me = $ViewBag->Me; $today = $ViewBag->Today; $brain = $ViewBag->Brain; $streak = $ViewBag->Streak; $vs = $ViewBag->Vs;
$best = $ViewBag->Best ?: [];
$lvBn = ['easy' => 'সহজ', 'medium' => 'মাঝারি', 'hard' => 'কঠিন'];
$lvSize = ['zip' => ['৫×৫', '৬×৬', '৭×৭'], 'sudoku' => ['১৮টা দেওয়া', '১৪টা দেওয়া', '১১টা দেওয়া'], 'patches' => ['৫×৫', '৬×৬', '৭×৭']];
$fmt = fn(int $s) => intdiv($s, 60) . ':' . str_pad((string)($s % 60), 2, '0', STR_PAD_LEFT);
$rs = (int)$ViewBag->ResetIn;
partial('Game/_Styles');
?>
<div class="gm-page">
    <section class="gm-hero">
        <h1><i class="fas fa-gamepad"></i> <?= t('ব্রেইন গেম', 'Brain Games') ?></h1>
        <p><?= t('মাথা খাটান, সময় বাঁচান, পয়েন্ট জিতুন! যতবার খুশি খেলুন — প্রতিবার নতুন পাজল। আজকের লিডারবোর্ডে নিজের নাম তুলুন।', 'Think fast, save time, win points! Play as often as you like — a fresh puzzle every time.') ?></p>
        <div class="gm-deco" aria-hidden="true"><span>🧩</span><span>🔢</span><span>🟧</span></div>
        <?php if ($me !== null && $today): ?>
            <div class="gm-stats">
                <div class="gm-stat"><b><?= bn_num($today->Pts) ?></b><small><?= t('আজকের পয়েন্ট', 'Points today') ?></small></div>
                <div class="gm-stat"><b><?= $today->Rank ? '#' . bn_num($today->Rank) : '—' ?></b><small><?= t('আজকের র‍্যাঙ্ক', 'Rank today') ?><?= $today->Players ? ' (' . bn_num($today->Players) . ' জনের মধ্যে)' : '' ?></small></div>
                <div class="gm-stat"><b>🔥 <?= bn_num($streak->Streak ?? 0) ?></b><small><?= t('দিন টানা খেলা', 'day streak') ?></small></div>
                <div class="gm-stat"><b>🧠 <?= $brain && $brain->Pct !== null ? bn_num($brain->Pct) . '%' : '—' ?></b><small><?= $brain && $brain->Pct !== null ? t('ব্রেইন স্কোর', 'Brain score') : t('ব্রেইন স্কোর — ৩টা খেলার পর', 'Brain score — after 3 games') ?></small></div>
            </div>
        <?php else: ?>
            <div class="gm-guest"><i class="fas fa-lock"></i> <?= t('গেম খেলতে লগইন করুন — পয়েন্ট, লিডারবোর্ড আর ব্রেইন স্কোর সব আপনার অ্যাকাউন্টে জমা হবে।', 'Log in to play — your points, rank and brain score are saved to your account.') ?>
                <a href="/Account/Login?returnUrl=%2FGame"><?= t('লগইন করুন', 'Log in') ?></a></div>
        <?php endif; ?>
    </section>

    <div class="gm-sec-h"><h2><i class="fas fa-puzzle-piece" style="color:#F97316;"></i> <?= t('একটা গেম বাছাই করুন', 'Pick a game') ?></h2></div>
    <div class="gm-cards">
        <?php foreach ($meta as $g => $m): ?>
            <div class="gm-card">
                <div class="gm-art" style="background:<?= e($m['bg']) ?>;"><?php partial('Game/_Art', ['g' => $g]); ?></div>
                <h3><i class="fas <?= e($m['icon']) ?>" style="color:<?= e($m['color']) ?>;"></i> <?= e($m['name']) ?></h3>
                <p><?= e($m['tag']) ?></p>
                <div class="gm-lv">
                    <?php foreach (['easy', 'medium', 'hard'] as $i => $lv): ?>
                        <a href="<?= $me !== null ? '/Game/Play/' . $g . '?level=' . $lv : '/Account/Login?returnUrl=' . rawurlencode('/Game/Play/' . $g . '?level=' . $lv) ?>" style="color:<?= e($m['color']) ?>;border-color:<?= e($m['color']) ?>33;background:<?= e($m['bg']) ?>;"><?= $me === null ? '<i class="fas fa-lock" style="font-size:11px;"></i> ' : '' ?><?= $lvBn[$lv] ?><small><?= $lvSize[$g][$i] ?></small></a>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($best[$g])): ?>
                    <div class="gm-best"><?php foreach (['easy', 'medium', 'hard'] as $lv): if (empty($best[$g][$lv])) continue; ?>
                        <span>🏅 <?= $lvBn[$lv] ?>: <?= $fmt($best[$g][$lv]['best']) ?></span><?php endforeach; ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="gm-sec-h"><h2><i class="fas fa-trophy" style="color:#F59E0B;"></i> <?= t('আজকের লিডারবোর্ড', 'Today\'s leaderboard') ?></h2><a href="/Game/Leaderboard"><?= t('পুরো লিডারবোর্ড', 'Full leaderboard') ?> →</a></div>
    <div class="gm-row2">
        <div class="gm-box">
            <?php if (empty($ViewBag->Top)): ?>
                <div class="gm-empty">🏁 <?= t('আজ এখনো কেউ খেলেনি — প্রথম হওয়ার সুযোগ আপনার!', 'No one has played today — be the first!') ?></div>
            <?php else: foreach ($ViewBag->Top as $r): partial('Game/_LbRow', ['r' => $r, 'me' => $me]); endforeach; endif; ?>
            <div class="gm-reset" style="margin-top:10px;"><i class="far fa-clock"></i> <?= t('লিডারবোর্ড রিসেট হবে', 'Resets in') ?> <b data-gm-reset="<?= $rs ?>"><?= bn_num(intdiv($rs, 3600)) ?> ঘণ্টা <?= bn_num(intdiv($rs % 3600, 60)) ?> মিনিট</b> <?= t('পরে (রাত ১২টায়)', '(at midnight)') ?></div>
        </div>
        <div class="gm-box">
            <?php partial('Game/_Vs', ['vs' => $vs]); ?>
            <?php if ($me !== null && $brain): ?>
                <h3 style="margin-top:18px;"><i class="fas fa-brain" style="color:#F59E0B;"></i> <?= t('আমার ব্রেইন স্কোর', 'My brain score') ?></h3>
                <div class="gm-brain">
                    <div class="gm-ring" style="--p:<?= (int)($brain->Pct ?? 0) ?>;"><span><?= $brain->Pct !== null ? bn_num($brain->Pct) . '%' : '—' ?></span></div>
                    <div style="font-size:14px;color:#475569;line-height:1.6;">
                        <?php if ($brain->Pct !== null): ?><?= t('আপনি', 'You beat') ?> <b><?= bn_num($brain->Pct) ?>%</b> <?= t('প্লেয়ারের চেয়ে ভালো খেলছেন (শেষ ৬০ দিনের গড়)। প্রোফাইলেও দেখা যাবে।', 'of players (60-day average). Shown on your profile.') ?>
                        <?php else: ?><?= t('আর', 'Play') ?> <b><?= bn_num($brain->Need) ?>টা</b> <?= t('পাজল সমাধান করলেই আপনার ব্রেইন স্কোর তৈরি হবে।', 'more puzzles to unlock your brain score.') ?><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php partial('Game/_Countdown'); ?>
