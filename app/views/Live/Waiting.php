<?php
/** লাইভ ক্লাস — অপেক্ষা (শুরু হয়নি) বা সমস্যা; শুরু হলে নিজে থেকেই ঢুকে যায় */
$err = $ViewBag->Error ?? null;
$ViewData['Title'] = $err ? 'Live Class' : 'Live: ' . ($ViewBag->Title ?? '');
$ViewData['Robots'] = 'noindex, nofollow';
$back = (string)($ViewBag->Back ?? '/');
?>
<style>
    .lw { min-height: 72vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px; background: linear-gradient(160deg, #0B1220, #0A2A4A); }
    .lw-box { background: #fff; border-radius: 22px; max-width: 520px; width: 100%; padding: 32px 28px; text-align: center; box-shadow: 0 30px 60px rgba(0,0,0,.35); }
    .lw-ic { width: 84px; height: 84px; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 34px; color: #fff; background: linear-gradient(135deg, #EF4444, #B91C1C); position: relative; }
    .lw-ic.err { background: linear-gradient(135deg, #F59E0B, #B45309); }
    .lw-ic::after { content: ''; position: absolute; inset: -8px; border-radius: 50%; border: 3px solid rgba(239,68,68,.35); animation: lwP 1.6s ease-out infinite; }
    .lw-ic.err::after { display: none; }
    @keyframes lwP { from { transform: scale(.9); opacity: 1; } to { transform: scale(1.25); opacity: 0; } }
    .lw-box h1 { font-size: 22px; font-weight: 800; color: #0B1F44; margin: 0 0 6px; }
    .lw-box p { color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 18px; }
    .lw-st { display: inline-flex; align-items: center; gap: 8px; background: #F1F5F9; color: #334155; font-weight: 700; padding: 8px 14px; border-radius: 999px; font-size: 13.5px; margin-bottom: 18px; }
    .lw-st i { color: #F59E0B; }
    .lw-a { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    .lw-a a { display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 18px; border-radius: 11px; font-weight: 700; text-decoration: none; font-size: 14.5px; }
    .lw-a .p { background: #08224A; color: #fff; } .lw-a .g { background: #F1F5F9; color: #334155; }
</style>
<div class="lw">
    <div class="lw-box">
        <?php if ($err): ?>
            <div class="lw-ic err"><i class="fas fa-triangle-exclamation"></i></div>
            <h1>লাইভ ক্লাস খোলা যায়নি</h1>
            <p><?= e($err) ?></p>
            <div class="lw-a"><a class="p" href="<?= e($back) ?>"><i class="fas fa-arrow-left"></i> ফিরে যান</a></div>
        <?php else: ?>
            <div class="lw-ic"><i class="fas fa-tower-broadcast"></i></div>
            <h1><?= e($ViewBag->Title) ?></h1>
            <p><?= e($ViewBag->Sub ?? '') ?><?= !empty($ViewBag->Starts) ? ' · ' . e(fmt_date($ViewBag->Starts, 'd M Y, h:i A')) : '' ?></p>
            <div class="lw-st" id="lwSt"><i class="fas fa-hourglass-half fa-spin"></i> শিক্ষক এখনো ক্লাস শুরু করেননি — শুরু হলেই নিজে থেকে ঢুকে যাবেন</div>
            <div class="lw-a"><a class="g" href="<?= e($back) ?>"><i class="fas fa-arrow-left"></i> ফিরে যান</a></div>
            <script>
            (function () {
                var url = '/Live/Status?t=<?= e($ViewBag->Type) ?>&id=<?= (int)$ViewBag->Id ?>';
                async function check() {
                    try { var d = await (await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); if (d.running) { document.getElementById('lwSt').innerHTML = '<i class="fas fa-circle" style="color:#22C55E;"></i> ক্লাস শুরু হয়েছে — ঢুকছেন...'; location.reload(); return; } } catch (e) { }
                    setTimeout(check, 10000);
                }
                setTimeout(check, 8000);
            })();
            </script>
        <?php endif; ?>
    </div>
</div>
