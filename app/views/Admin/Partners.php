<?php
/** Admin → Partners: এজেন্সি / পার্টনারদের রেফারেল লিংক + কোড, আর কে কত জন এনেছে */
$ViewData['Title'] = 'Partner Referrals';
$list = is_array($Model) ? $Model : [];
$sum = ['clicks' => 0, 'signups' => 0, 'buyers' => 0, 'revenue' => 0];
foreach ($list as $p) { $sum['clicks'] += $p->Clicks; $sum['signups'] += $p->Signups; $sum['buyers'] += $p->Buyers; $sum['revenue'] += $p->Revenue; }
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .pt h2 { color: #08224A; margin: 0; font-size: 24px; }
    .pt .sub { color: #64748B; margin: 4px 0 18px; font-size: 14px; max-width: 900px; }
    .pt-sum { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .pt-sum > div { background: #fff; border-radius: 14px; padding: 14px 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); border-left: 4px solid var(--c); }
    .pt-sum small { display: block; color: #64748B; font-size: 12px; font-weight: 700; } .pt-sum b { font-size: 22px; color: #0F172A; }
    .pt-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 18px; margin-bottom: 18px; }
    .pt-card h3 { margin: 0 0 4px; font-size: 16.5px; font-weight: 800; color: #08224A; display: flex; gap: 8px; align-items: center; }
    .pt-card .hint { color: #64748B; font-size: 13px; margin: 0 0 12px; }
    .pt-form { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .pt-form label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .pt-form input, .pt-form select { width: 100%; height: 42px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 0 12px; font-size: 14px; font-family: inherit; background: #fff; color: #0F172A; outline: none; }
    .pt-form input:focus, .pt-form select:focus { border-color: #02B8F5; }
    .pt-form .wide { grid-column: span 2; }
    .pt-form .full { grid-column: 1 / -1; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .pt-code { text-transform: uppercase; font-weight: 800; letter-spacing: .5px; }
    .pt-btn { height: 42px; padding: 0 18px; border-radius: 10px; border: none; background: #08224A; color: #fff; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; white-space: nowrap; }
    .pt-btn.sm { height: 34px; padding: 0 12px; font-size: 13px; border-radius: 9px; }
    .pt-btn.ghost { background: #F1F5F9; color: #334155; } .pt-btn.ok { background: #15803D; } .pt-btn.warn { background: #FEF3C7; color: #92400E; }
    #ptMsg { font-size: 13.5px; font-weight: 600; } #ptMsg.ok { color: #15803D; } #ptMsg.err { color: #B91C1C; }
    .pt-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .pt-p { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.05); padding: 16px 18px; border: 1px solid #EEF2F7; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
    .pt-p.off { opacity: .72; }
    .pt-top { display: flex; align-items: flex-start; gap: 12px; }
    .pt-av { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, #7C3AED, #0EA5E9); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
    .pt-top h4 { margin: 0; font-size: 16.5px; font-weight: 800; color: #0F172A; overflow-wrap: anywhere; }
    .pt-top h4 a { color: inherit; text-decoration: none; }
    .pt-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 5px; }
    .pt-chip { font-size: 11.5px; font-weight: 800; padding: 3px 9px; border-radius: 999px; background: #F1F5F9; color: #475569; white-space: nowrap; }
    .pt-chip.code { background: #FEF9C3; color: #854D0E; letter-spacing: .4px; } .pt-chip.on { background: #DCFCE7; color: #166534; } .pt-chip.no { background: #FEE2E2; color: #991B1B; }
    .pt-link { display: flex; align-items: center; gap: 8px; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 10px; padding: 7px 8px 7px 12px; min-width: 0; }
    .pt-link code { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #0369A1; font-size: 13px; background: none; }
    .pt-nums { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .pt-nums > div { background: #F8FAFC; border-radius: 10px; padding: 8px 10px; }
    .pt-nums small { display: block; color: #64748B; font-size: 11.5px; font-weight: 700; } .pt-nums b { font-size: 17px; color: #0F172A; } .pt-nums b.m { color: #15803D; }
    .pt-acts { display: flex; gap: 8px; flex-wrap: wrap; }
    .pt-empty { text-align: center; color: #94A3B8; padding: 34px 10px; background: #fff; border-radius: 16px; }
    .pt-empty i { font-size: 34px; display: block; margin-bottom: 8px; color: #CBD5E1; }
    .pt-toast { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); background: #0F172A; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; z-index: 9999; display: none; }
    @media (max-width: 1200px) { .pt-grid { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px; } .pt-form { grid-template-columns: minmax(0, 1fr); } .pt-form .wide { grid-column: auto; } .pt-sum { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 480px) { .pt-nums { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content pt">
    <?= csrf_field() ?>
    <h2><i class="fas fa-handshake" style="color:#7C3AED;"></i> Partner Referrals</h2>
    <p class="sub">প্রতিটা এজেন্সি / পার্টনারের জন্য একটা স্পেশাল আইডি, একটা রেফারেল লিংক আর একটা রেফারেল কোড। লিংকে কত জন ক্লিক করেছে (কোন প্ল্যাটফর্ম থেকে), কত জন সাইনআপ করেছে আর কত জন প্যাকেজ কিনেছে — সব এখানে দেখা যাবে। ইউজারদের নিজের রেফারেল (বোনাস) থেকে এটা আলাদা।</p>

    <?php if ($TempData['Error'] !== null): ?><div class="alert alert-danger"><?= e($TempData['Error']) ?></div><?php endif; ?>

    <div class="pt-sum">
        <div style="--c:#0EA5E9;"><small>মোট ক্লিক</small><b><?= number_format($sum['clicks']) ?></b></div>
        <div style="--c:#6366F1;"><small>মোট সাইনআপ</small><b><?= number_format($sum['signups']) ?></b></div>
        <div style="--c:#16A34A;"><small>প্যাকেজ কিনেছে</small><b><?= number_format($sum['buyers']) ?> জন</b></div>
        <div style="--c:#15803D;"><small>মোট বিক্রি</small><b>৳<?= number_format($sum['revenue']) ?></b></div>
    </div>

    <div class="pt-card">
        <h3><i class="fas fa-plus-circle" style="color:#16A34A;"></i> নতুন পার্টনার / এজেন্সি</h3>
        <p class="hint">কোড খালি রাখলে নাম থেকে নিজে বানিয়ে দেবে। কোড সবসময় ইংরেজি বড় হাতের অক্ষর আর সংখ্যায় থাকে।</p>
        <form class="pt-form" id="ptNew" autocomplete="off">
            <div class="wide"><label for="ptName">এজেন্সি / পার্টনারের নাম *</label><input id="ptName" name="name" maxlength="100" placeholder="যেমন: ABC Marketing Agency" required /></div>
            <div><label for="ptCode">রেফারেল কোড</label><input id="ptCode" name="code" class="pt-code" maxlength="20" placeholder="যেমন: <?= e($ViewBag->Suggest) ?>" /></div>
            <div><label for="ptContact">যোগাযোগ (ঐচ্ছিক)</label><input id="ptContact" name="contact" maxlength="150" placeholder="ফোন / ইমেইল / ব্যক্তির নাম" /></div>
            <div><label for="ptLanding">লিংকে ক্লিক করলে যাবে</label>
                <select id="ptLanding" name="landing"><option value="register">সাইনআপ পেজে (Create Account)</option><option value="home">হোম পেজে</option></select></div>
            <div><label for="ptNote">নোট (ঐচ্ছিক)</label><input id="ptNote" name="note" maxlength="300" placeholder="যেমন: ফেসবুক ক্যাম্পেইন, নভেম্বর" /></div>
            <div class="full"><button type="submit" class="pt-btn ok"><i class="fas fa-plus"></i> পার্টনার তৈরি করুন</button><span id="ptMsg"></span></div>
        </form>
    </div>

    <?php if (!$list): ?>
        <div class="pt-empty"><i class="fas fa-handshake"></i>এখনো কোনো পার্টনার নেই — উপরে থেকে প্রথম পার্টনার তৈরি করুন।</div>
    <?php else: ?>
    <div class="pt-grid">
        <?php foreach ($list as $p): $link = partner_link((string)$p->Code); ?>
            <div class="pt-p<?= (int)$p->IsActive ? '' : ' off' ?>">
                <div class="pt-top">
                    <div class="pt-av"><i class="fas fa-building"></i></div>
                    <div style="min-width:0;flex:1;">
                        <h4><a href="/Admin/Partner/<?= (int)$p->Id ?>"><?= e($p->Name) ?></a></h4>
                        <div class="pt-meta">
                            <span class="pt-chip">ID: P-<?= str_pad((string)(int)$p->Id, 4, '0', STR_PAD_LEFT) ?></span>
                            <span class="pt-chip code"><i class="fas fa-ticket"></i> <?= e($p->Code) ?></span>
                            <span class="pt-chip <?= (int)$p->IsActive ? 'on' : 'no' ?>"><?= (int)$p->IsActive ? 'চালু' : 'বন্ধ' ?></span>
                            <?php if (!is_blank($p->Contact)): ?><span class="pt-chip"><i class="fas fa-phone"></i> <?= e($p->Contact) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="pt-link"><code><?= e($link) ?></code><button type="button" class="pt-btn sm ghost" data-copy="<?= e($link) ?>"><i class="far fa-copy"></i> কপি</button></div>
                <div class="pt-nums">
                    <div><small>ক্লিক</small><b><?= number_format($p->Clicks) ?></b></div>
                    <div><small>সাইনআপ</small><b><?= number_format($p->Signups) ?></b></div>
                    <div><small>কিনেছে</small><b><?= number_format($p->Buyers) ?></b></div>
                    <div><small>বিক্রি</small><b class="m">৳<?= number_format($p->Revenue) ?></b></div>
                </div>
                <div class="pt-acts">
                    <a class="pt-btn sm" href="/Admin/Partner/<?= (int)$p->Id ?>"><i class="fas fa-chart-column"></i> রিপোর্ট ও শেয়ার</a>
                    <button type="button" class="pt-btn sm ghost" data-copy="<?= e($p->Code) ?>"><i class="fas fa-ticket"></i> কোড কপি</button>
                    <button type="button" class="pt-btn sm <?= (int)$p->IsActive ? 'warn' : 'ok' ?>" data-toggle="<?= (int)$p->Id ?>" data-on="<?= (int)$p->IsActive ? 0 : 1 ?>"><i class="fas fa-power-off"></i> <?= (int)$p->IsActive ? 'বন্ধ করুন' : 'চালু করুন' ?></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<div class="pt-toast" id="ptToast"></div>
<script>
(function () {
    var tok = document.querySelector('input[name="__RequestVerificationToken"]').value;
    function toast(m) { var t = document.getElementById('ptToast'); t.textContent = m; t.style.display = 'block'; clearTimeout(t._h); t._h = setTimeout(function () { t.style.display = 'none'; }, 2200); }
    async function post(url, data) {
        var fd = new FormData(); fd.append('__RequestVerificationToken', tok);
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        try { return await (await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json(); }
        catch (e) { return { success: false, message: 'সার্ভারে সমস্যা — আবার চেষ্টা করুন।' }; }
    }
    function copy(v) {
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v).then(function () { toast('কপি হয়েছে ✓'); });
        else { var a = document.createElement('textarea'); a.value = v; document.body.appendChild(a); a.select(); try { document.execCommand('copy'); toast('কপি হয়েছে ✓'); } catch (e) { } a.remove(); }
    }
    document.addEventListener('click', async function (e) {
        var c = e.target.closest('[data-copy]'); if (c) { copy(c.getAttribute('data-copy')); return; }
        var t = e.target.closest('[data-toggle]');
        if (t) { t.disabled = true; var d = await post('/Admin/TogglePartner', { id: t.getAttribute('data-toggle'), on: t.getAttribute('data-on') === '1' ? 'true' : 'false' }); toast(d.message || ''); if (d.success) setTimeout(function () { location.reload(); }, 600); else t.disabled = false; }
    });
    var code = document.getElementById('ptCode');
    code.addEventListener('input', function () { code.value = code.value.toUpperCase().replace(/[^A-Z0-9_-]/g, ''); });
    document.getElementById('ptNew').addEventListener('submit', async function (e) {
        e.preventDefault();
        var f = e.target, msg = document.getElementById('ptMsg'), b = f.querySelector('button[type=submit]');
        b.disabled = true; msg.className = ''; msg.textContent = 'সেভ হচ্ছে...';
        var d = await post('/Admin/SavePartner', { id: 0, name: f.elements['name'].value, code: f.elements['code'].value, contact: f.elements['contact'].value, note: f.elements['note'].value, landing: f.elements['landing'].value });
        msg.className = d.success ? 'ok' : 'err'; msg.textContent = d.message || '';
        if (d.success && d.id) location.href = '/Admin/Partner/' + d.id; else b.disabled = false;
    });
})();
</script>
