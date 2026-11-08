<?php
/** Help Desk → Contact: মাষ্টার মশাই এর মোবাইল নম্বর, WhatsApp, ইমেইল (Admin → Settings → Contact & Social থেকে বদলানো যায়) */
$c = $ViewBag->Info;
$ViewData['Title'] = 'Contact';
$ViewData['FullTitle'] = 'যোগাযোগ করুন | মাষ্টার মশাই';
$ViewData['Description'] = 'মাষ্টার মশাই এর সাথে যোগাযোগ করুন — মোবাইল: ' . $c['ct_phone'] . ($c['ct_email'] !== '' ? ', ইমেইল: ' . $c['ct_email'] : '');
?>
<style>
    .ct-page { background: linear-gradient(180deg, #F4F9FE, #EEF6FD); min-height: 75vh; padding: 34px 16px 70px; }
    .ct-wrap { max-width: 980px; margin: 0 auto; }
    .ct-nav { display: flex; gap: 6px; background: #fff; padding: 6px; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,.05); width: fit-content; max-width: 100%; margin-bottom: 22px; }
    .ct-nav a { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 14px; color: #334155; text-decoration: none; }
    .ct-nav a.on { background: #08224A; color: #fff; }
    .ct-nav a:not(.on):hover { background: #F1F5F9; }
    .ct-head { text-align: center; margin-bottom: 28px; }
    .ct-head .pill { display: inline-flex; align-items: center; gap: 7px; background: #E0F7FE; color: #0369A1; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 999px; }
    .ct-head h1 { margin: 12px 0 8px; font-family: var(--font-heading); font-size: clamp(28px, 3vw, 40px); font-weight: 800; color: #0B1F44; }
    .ct-head p { margin: 0 auto; max-width: 560px; color: #475569; font-size: 15.5px; line-height: 1.6; }
    .ct-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .ct-card { background: #fff; border-radius: 20px; padding: 26px 22px 22px; box-shadow: 0 10px 30px rgba(15,52,103,.08); border: 1px solid #E3ECF6; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px; min-width: 0; }
    .ct-ic { width: 64px; height: 64px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #fff; background: var(--c); box-shadow: 0 10px 22px color-mix(in srgb, var(--c) 35%, transparent); }
    .ct-card h2 { margin: 6px 0 0; font-size: 17px; font-weight: 800; color: #0F172A; }
    .ct-card .v { font-size: 20px; font-weight: 800; color: #0B1F44; overflow-wrap: anywhere; letter-spacing: .3px; }
    .ct-card .v.em { font-size: 15px; letter-spacing: 0; }
    .ct-card small { color: #64748B; font-size: 13px; }
    .ct-acts { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-top: auto; padding-top: 6px; }
    .ct-btn { display: inline-flex; align-items: center; gap: 7px; height: 40px; padding: 0 16px; border-radius: 10px; font-weight: 700; font-size: 14px; text-decoration: none; border: none; cursor: pointer; font-family: inherit; background: var(--c); color: #fff; }
    .ct-btn.ghost { background: #F1F5F9; color: #334155; }
    .ct-extra { margin-top: 20px; background: #fff; border-radius: 18px; padding: 18px 22px; border: 1px solid #E3ECF6; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .ct-extra div { display: flex; gap: 12px; align-items: flex-start; color: #334155; font-size: 14.5px; line-height: 1.55; }
    .ct-extra i { color: #00B5E0; font-size: 18px; margin-top: 2px; width: 20px; text-align: center; }
    .ct-extra b { display: block; color: #0F172A; font-size: 13px; }
    .ct-help { margin-top: 20px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; background: linear-gradient(120deg, #08224A, #0A5A86); color: #fff; border-radius: 18px; padding: 20px 24px; }
    .ct-help p { margin: 0; flex: 1; min-width: 220px; font-size: 15px; } .ct-help p b { display: block; font-size: 17px; }
    .ct-help a { background: #00CFFF; color: #fff; font-weight: 800; padding: 11px 20px; border-radius: 12px; text-decoration: none; white-space: nowrap; }
    .ct-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; }
    @media (max-width: 860px) { .ct-cards { grid-template-columns: minmax(0, 1fr); } .ct-card { flex-direction: row; text-align: left; flex-wrap: wrap; padding: 18px; } .ct-card .ct-ic { width: 52px; height: 52px; font-size: 22px; } .ct-card .t { flex: 1; min-width: 0; } .ct-acts { width: 100%; justify-content: flex-start; } }
</style>
<div class="ct-page">
    <div class="ct-wrap">
        <nav class="ct-nav">
            <a href="/Helpdesk"><i class="fas fa-headset"></i> Help Desk</a>
            <a href="/Helpdesk/Contact" class="on"><i class="fas fa-address-card"></i> Contact</a>
        </nav>
        <div class="ct-head">
            <span class="pill"><i class="fas fa-phone-volume"></i> Contact Us</span>
            <h1>মাষ্টার মশাই এর সাথে যোগাযোগ করুন</h1>
            <p><?= $c['ct_note'] !== '' ? nl2br(e($c['ct_note'])) : 'যেকোনো প্রশ্ন, সমস্যা বা পরামর্শ থাকলে সরাসরি কল করুন, WhatsApp এ মেসেজ দিন বা ইমেইল করুন — আমরা দ্রুত উত্তর দেওয়ার চেষ্টা করি।' ?></p>
        </div>

        <div class="ct-cards">
            <?php if ($c['ct_phone'] !== ''): ?>
            <div class="ct-card" style="--c:#0EA5E9;">
                <span class="ct-ic"><i class="fas fa-phone"></i></span>
                <div class="t"><h2>মোবাইল নম্বর</h2><div class="v"><?= e($c['ct_phone']) ?></div><small>মাষ্টার মশাই এর মোবাইল নম্বর</small></div>
                <div class="ct-acts"><a class="ct-btn" href="tel:<?= e($c['tel']) ?>"><i class="fas fa-phone"></i> কল করুন</a><button type="button" class="ct-btn ghost" data-copy="<?= e($c['ct_phone']) ?>"><i class="far fa-copy"></i> কপি</button></div>
            </div>
            <?php endif; ?>
            <?php if ($c['wa'] !== ''): ?>
            <div class="ct-card" style="--c:#22C55E;">
                <span class="ct-ic"><i class="fab fa-whatsapp"></i></span>
                <div class="t"><h2>WhatsApp</h2><div class="v"><?= e($c['wa_label']) ?></div><small>মেসেজ দিন — সুবিধামতো উত্তর পাবেন</small></div>
                <div class="ct-acts"><a class="ct-btn" href="<?= e($c['wa']) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> মেসেজ দিন</a></div>
            </div>
            <?php endif; ?>
            <?php if ($c['ct_email'] !== ''): ?>
            <div class="ct-card" style="--c:#F59E0B;">
                <span class="ct-ic"><i class="fas fa-envelope"></i></span>
                <div class="t"><h2>ইমেইল</h2><div class="v em"><?= e($c['ct_email']) ?></div><small>মাষ্টার মশাই এর ইমেইল</small></div>
                <div class="ct-acts"><a class="ct-btn" href="mailto:<?= e($c['ct_email']) ?>"><i class="fas fa-paper-plane"></i> ইমেইল করুন</a><button type="button" class="ct-btn ghost" data-copy="<?= e($c['ct_email']) ?>"><i class="far fa-copy"></i> কপি</button></div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($c['ct_hours'] !== '' || $c['ct_address'] !== ''): ?>
        <div class="ct-extra">
            <?php if ($c['ct_hours'] !== ''): ?><div><i class="far fa-clock"></i><span><b>যোগাযোগের সময়</b><?= e($c['ct_hours']) ?></span></div><?php endif; ?>
            <?php if ($c['ct_address'] !== ''): ?><div><i class="fas fa-location-dot"></i><span><b>ঠিকানা</b><?= e($c['ct_address']) ?></span></div><?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="ct-help">
            <p><b>লিখে জানাতে চান?</b>Help Desk এ পোস্ট করুন — আমাদের টিম আর কমিউনিটি উত্তর দেবে।</p>
            <a href="/Helpdesk"><i class="fas fa-headset"></i> Help Desk এ যান</a>
        </div>
    </div>
</div>
<div class="ct-toast" id="ctToast"></div>
<script>
document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-copy]'); if (!b) return;
    var v = b.getAttribute('data-copy'), t = document.getElementById('ctToast');
    function done() { t.textContent = 'কপি হয়েছে ✓'; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 1800); }
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v).then(done);
    else { var a = document.createElement('textarea'); a.value = v; document.body.appendChild(a); a.select(); try { document.execCommand('copy'); done(); } catch (x) { } a.remove(); }
});
</script>
