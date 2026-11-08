<?php
/** এজেন্সির রিপোর্ট (পাবলিক, টোকেন দিয়ে) — শুধু সংখ্যা */
$ViewData['Title'] = 'Partner Report';
$ViewData['Robots'] = 'noindex, nofollow';
$nf = !empty($ViewBag->NotFound);
?>
<style>
    .rp { max-width: 1180px; margin: 0 auto; padding: 28px 16px 50px; }
    .rp-head { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; margin-bottom: 18px; }
    .rp-av { width: 54px; height: 54px; border-radius: 14px; background: linear-gradient(135deg, #7C3AED, #0EA5E9); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .rp-head h1 { font-size: 24px; margin: 0; color: #08224A; font-weight: 800; overflow-wrap: anywhere; }
    .rp-head p { margin: 3px 0 0; color: #64748B; font-size: 14px; }
    .rp-code { background: #FEF9C3; color: #854D0E; font-weight: 800; padding: 2px 10px; border-radius: 999px; letter-spacing: .5px; }
    .rp-flt { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 14px 16px; margin-bottom: 18px; }
    .rp-flt label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .rp-flt input { height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 10px; font-size: 14px; font-family: inherit; }
    .rp-btn { height: 40px; padding: 0 16px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; }
    .rp-btn.ghost { background: #F1F5F9; color: #334155; }
    .rp-nf { max-width: 520px; margin: 60px auto; text-align: center; background: #fff; border-radius: 18px; padding: 36px 24px; box-shadow: 0 4px 16px rgba(0,0,0,.06); color: #475569; }
    .rp-nf i { font-size: 40px; color: #CBD5E1; margin-bottom: 12px; display: block; }
    .rp-note { color: #94A3B8; font-size: 12.5px; text-align: center; margin-top: 8px; }
</style>
<?php if ($nf): ?>
    <div class="rp"><div class="rp-nf"><i class="fas fa-link-slash"></i><h2 style="font-size:20px;color:#0F172A;">রিপোর্ট লিংকটি কাজ করছে না</h2><p>লিংকটি ভুল অথবা পরিবর্তন করা হয়েছে। নতুন লিংকের জন্য মাষ্টার মশাই এর সাথে যোগাযোগ করুন।</p></div></div>
<?php else: $p = $Model; $from = $ViewBag->From; $to = $ViewBag->To; $tk = rawurlencode((string)$ViewBag->Token); ?>
<div class="rp">
    <div class="rp-head">
        <div class="rp-av"><i class="fas fa-handshake"></i></div>
        <div style="min-width:0;">
            <h1><?= e($p->Name) ?> — রেফারেল রিপোর্ট</h1>
            <p>পার্টনার ID: P-<?= str_pad((string)(int)$p->Id, 4, '0', STR_PAD_LEFT) ?> · কোড: <span class="rp-code"><?= e($p->Code) ?></span><?= (int)$p->IsActive ? '' : ' · <b style="color:#B91C1C;">এখন বন্ধ</b>' ?></p>
        </div>
    </div>
    <form class="rp-flt" method="get" action="/Partner/Report">
        <input type="hidden" name="t" value="<?= e((string)$ViewBag->Token) ?>" />
        <div><label for="rFrom">শুরু</label><input type="date" id="rFrom" name="from" value="<?= e($from ?? '') ?>" max="<?= date('Y-m-d') ?>" /></div>
        <div><label for="rTo">শেষ</label><input type="date" id="rTo" name="to" value="<?= e($to ?? '') ?>" max="<?= date('Y-m-d') ?>" /></div>
        <button type="submit" class="rp-btn"><i class="fas fa-magnifying-glass"></i> দেখুন</button>
        <a class="rp-btn ghost" href="/Partner/Report?t=<?= $tk ?>">সব সময়</a>
        <a class="rp-btn ghost" href="/Partner/Report?t=<?= $tk ?>&from=<?= date('Y-m-d', strtotime('-6 days')) ?>&to=<?= date('Y-m-d') ?>">গত ৭ দিন</a>
        <a class="rp-btn ghost" href="/Partner/Report?t=<?= $tk ?>&from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>">এই মাস</a>
    </form>
    <?php partial('Partner/_Stats', ['R' => $ViewBag->Report]); ?>
    <p class="rp-note">সংখ্যাগুলো সাথে সাথে আপডেট হয়। "কিনেছে" = যাদের পেমেন্ট মাষ্টার মশাই Approve করেছে। ইউজারদের ব্যক্তিগত তথ্য এখানে দেখানো হয় না।</p>
</div>
<?php endif; ?>
