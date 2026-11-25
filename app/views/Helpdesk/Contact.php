<?php
/** Help Desk → Contact: মাষ্টার মশাই এর মোবাইল নম্বর, WhatsApp, ইমেইল (Admin → Settings → Contact & Social থেকে বদলানো যায়) */
$c = $ViewBag->Info;
$ViewData['Title'] = 'Contact';
$ViewData['FullTitle'] = t('যোগাযোগ করুন — ফোন, ইমেইল ও হেল্পডেস্ক', 'Contact Us — Phone, Email & Helpdesk Support') . ' | ' . seo_brand();
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

        <?php // ===== রিফান্ড আবেদন (Transaction ID সহ, ব্যক্তিগত) =====
        $rPays = $ViewBag->RefundPays ?? []; $myRef = $ViewBag->MyRefunds ?? []; $logged = current_user_id() !== null && !is_admin(); ?>
        <div class="rf-box" id="refundReq">
            <h2><i class="fas fa-rotate-left"></i> <?= t('রিফান্ড আবেদন', 'Refund request') ?></h2>
            <p class="rf-sub"><?= t('পেমেন্টের ৭ দিনের মধ্যে আবেদন করুন। কখন টাকা ফেরত পাবেন জানতে', 'Apply within 7 days of the payment. To see when refunds are given, read the') ?> <a href="/Home/Refund"><?= t('রিফান্ড নীতি', 'Refund Policy') ?></a> <?= t('পড়ুন। আবেদনটা শুধু অ্যাডমিন দেখবেন।', '. Only the admin sees your request.') ?></p>
            <?php if (!$logged): ?>
                <p class="rf-login"><a href="/Account/Login?returnUrl=<?= rawurlencode('/Helpdesk/Contact#refundReq') ?>"><i class="fas fa-right-to-bracket"></i> <?= t('লগইন করুন', 'Log in') ?></a> <?= t('— তাহলে আপনার পেমেন্ট বাছাই করে আবেদন করতে পারবেন। অথবা উপরের WhatsApp / ইমেইলে Transaction ID সহ জানান।', 'to choose your payment and apply, or send the Transaction ID by WhatsApp / email above.') ?></p>
            <?php else: ?>
            <form id="rfForm" autocomplete="off">
                <?= csrf_field() ?>
                <label for="rfPay"><?= t('কোন পেমেন্টের রিফান্ড চান', 'Which payment') ?> *</label>
                <select id="rfPay" name="paymentId">
                    <option value=""><?= t('— পেমেন্ট বাছাই করুন —', '— Choose a payment —') ?></option>
                    <?php foreach ($rPays as $p): ?><option value="<?= (int)$p->Id ?>"><?= e($p->PackageName . ' · ৳' . number_format((int)$p->Amount) . ' · ' . date('d M Y', strtotime((string)$p->CreatedAt)) . ' · TxnID ' . ($p->TxnId ?: '—') . ($p->Status === 'Pending' ? ' · ' . t('যাচাই বাকি', 'pending') : '')) ?></option><?php endforeach; ?>
                    <option value="0"><?= t('তালিকায় নেই — Transaction ID নিজে লিখব', 'Not listed — I will type the Transaction ID') ?></option>
                </select>
                <?php if (!$rPays): ?><small class="rf-note"><?= t('গত ৭ দিনে আপনার কোনো পেমেন্ট পাওয়া যায়নি।', 'No payment found in the last 7 days.') ?></small><?php endif; ?>
                <div id="rfTxnWrap" hidden><label for="rfTxn">Transaction ID *</label><input id="rfTxn" name="txnId" maxlength="60" placeholder="যেমন: 9K7A1B2C3D" /></div>
                <label for="rfReason"><?= t('কারণ', 'Reason') ?> *</label>
                <select id="rfReason" name="reason"><option value=""><?= t('— কারণ বাছাই করুন —', '— Choose a reason —') ?></option><?php foreach (Db::REFUND_REASONS as $k => [$bn, $enR]): ?><option value="<?= e($k) ?>"><?= e(t($bn, $enR)) ?></option><?php endforeach; ?></select>
                <label for="rfDetails"><?= t('বিস্তারিত', 'Details') ?></label>
                <textarea id="rfDetails" name="details" rows="3" maxlength="1500" placeholder="<?= e(t('কী হয়েছে সংক্ষেপে লিখুন', 'Briefly describe what happened')) ?>"></textarea>
                <div class="rf-msg" id="rfMsg"></div>
                <button type="submit" class="ct-btn" id="rfBtn" style="--c:#0EA5E9;"><i class="fas fa-paper-plane"></i> <?= t('আবেদন জমা দিন', 'Submit request') ?></button>
            </form>
            <?php if ($myRef): ?>
                <div class="rf-mine"><b><?= t('আপনার আবেদন', 'Your requests') ?></b>
                    <?php foreach ($myRef as $r): $cls = ['Pending' => 'p', 'Approved' => 'ok', 'Rejected' => 'no'][$r->Status] ?? 'p'; ?>
                        <div><span class="rf-st <?= $cls ?>"><?= e(['Pending' => t('অপেক্ষমাণ', 'Pending'), 'Approved' => t('অনুমোদিত', 'Approved'), 'Rejected' => t('বাতিল', 'Rejected')][$r->Status] ?? $r->Status) ?></span>
                            <?= e(($r->PackageName ?: 'TxnID ' . $r->TxnId) . ' · ' . date('d M Y', strtotime((string)$r->CreatedAt))) ?><?= !is_blank($r->AdminNote) ? '<small>' . e($r->AdminNote) . '</small>' : '' ?></div>
                    <?php endforeach; ?></div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <style>
            .rf-box { margin-top: 20px; background: #fff; border-radius: 18px; padding: 22px; border: 1px solid #E3ECF6; box-shadow: 0 10px 30px rgba(15,52,103,.06); }
            .rf-box h2 { margin: 0 0 4px; font-size: 19px; font-weight: 800; color: #0B1F44; } .rf-box h2 i { color: #0EA5E9; }
            .rf-sub { color: #64748B; font-size: 14px; margin: 0 0 14px; } .rf-sub a, .rf-login a { color: #0284C7; font-weight: 700; }
            .rf-login { margin: 0; font-size: 14.5px; color: #334155; }
            #rfForm { display: grid; gap: 6px; max-width: 620px; }
            #rfForm label { font-weight: 700; font-size: 13.5px; color: #334155; margin-top: 6px; }
            #rfForm select, #rfForm input, #rfForm textarea { width: 100%; border: 1.5px solid #DCE7F0; border-radius: 10px; padding: 10px 12px; font-size: 14px; font-family: inherit; background: #F8FBFF; }
            #rfForm .ct-btn { justify-self: start; margin-top: 8px; }
            .rf-note { color: #94A3B8; font-size: 12.5px; }
            .rf-msg { font-size: 14px; font-weight: 600; } .rf-msg.ok { color: #15803D; } .rf-msg.err { color: #B91C1C; }
            .rf-mine { margin-top: 18px; border-top: 1px solid #EEF2F7; padding-top: 12px; display: grid; gap: 6px; font-size: 13.5px; color: #334155; }
            .rf-mine small { display: block; color: #64748B; margin-top: 2px; }
            .rf-st { display: inline-block; font-size: 11.5px; font-weight: 800; border-radius: 999px; padding: 2px 9px; margin-right: 6px; }
            .rf-st.p { background: #FEF3C7; color: #92400E; } .rf-st.ok { background: #DCFCE7; color: #166534; } .rf-st.no { background: #FEE2E2; color: #991B1B; }
        </style>
        <script>
        (function () {
            var f = document.getElementById('rfForm'); if (!f) return;
            var pay = document.getElementById('rfPay'), tw = document.getElementById('rfTxnWrap'), msg = document.getElementById('rfMsg'), btn = document.getElementById('rfBtn');
            pay.addEventListener('change', function () { tw.hidden = pay.value !== '0'; });
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                if (pay.value === '') { msg.className = 'rf-msg err'; msg.textContent = 'পেমেন্ট বাছাই করুন।'; return; }
                btn.disabled = true; msg.className = 'rf-msg'; msg.textContent = '';
                fetch('/Helpdesk/RefundRequest', { method: 'POST', body: new FormData(f), credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
                    msg.className = 'rf-msg ' + (d.success ? 'ok' : 'err'); msg.textContent = d.message || (d.success ? 'জমা হয়েছে।' : 'জমা দেওয়া যায়নি।');
                    if (d.success) setTimeout(function () { location.reload(); }, 1600); else btn.disabled = false;
                }).catch(function () { msg.className = 'rf-msg err'; msg.textContent = 'জমা দেওয়া যায়নি — আবার চেষ্টা করুন।'; btn.disabled = false; });
            });
            if (location.hash === '#refundReq') document.getElementById('refundReq').scrollIntoView();
        })();
        </script>

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
