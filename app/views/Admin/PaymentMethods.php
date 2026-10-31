<?php
/**
 * Admin → Payment Methods: পেমেন্ট মেথড (bKash, Nagad, Rocket, Bank Transfer, Other ...) আর তাদের অ্যাকাউন্ট/নম্বর/QR ছবি।
 * এছাড়া পেমেন্ট বাধ্যতামূলক করার নিয়ম (শিক্ষক / শিক্ষার্থী)।
 */
$ViewData['Title'] = 'Payment Methods';
$methods = is_array($Model) ? $Model : [];
$rules = $ViewBag->Rules ?? ['teacher' => false, 'student' => false];
$kindBn = ['bank' => 'ব্যাংক', 'mobile' => 'মোবাইল ব্যাংকিং', 'other' => 'অন্যান্য'];
?>
<?= payment_logo_css() ?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px; min-height: calc(100vh - 80px); }
    .pm-head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; align-items: flex-start; margin-bottom: 18px; }
    .pm-head h2 { color: #08224A; margin: 0; font-size: 24px; }
    .pm-head p { color: #64748B; margin: 4px 0 0; font-size: 14px; }
    .pm-btn { border: none; border-radius: 10px; padding: 9px 16px; font-size: 13.5px; font-weight: 700; display: inline-flex; gap: 7px; align-items: center; cursor: pointer; text-decoration: none; }
    .pm-btn.pri { background: linear-gradient(135deg, #1D7FE5, #02B8F5); color: #fff; }
    .pm-btn.out { background: #fff; color: #1D7FE5; border: 1.5px solid #DCECF5; }
    .pm-btn.del { background: #fff; color: #DC2626; border: 1.5px solid #FCA5A5; }
    .pm-btn.sm { padding: 6px 11px; font-size: 12.5px; }
    .pm-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.06); margin-bottom: 18px; }
    .pm-rules h5 { margin: 0 0 4px; color: #08224A; font-weight: 700; }
    .pm-rules > p { color: #64748B; font-size: 13.5px; margin: 0 0 14px; }
    .pm-rule { display: flex; gap: 14px; align-items: flex-start; padding: 12px 14px; border: 1.5px solid #E2E8F0; border-radius: 12px; margin-top: 10px; cursor: pointer; }
    .pm-rule b { color: #08224A; display: block; font-size: 14.5px; }
    .pm-rule span { color: #64748B; font-size: 13px; line-height: 1.55; }
    .pm-sw { appearance: none; -webkit-appearance: none; width: 42px; height: 24px; border-radius: 20px; background: #CBD5E1; position: relative; cursor: pointer; transition: background .2s; margin: 2px 0 0; flex-shrink: 0; }
    .pm-sw::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; transition: transform .2s; }
    .pm-sw:checked { background: #22C55E; }
    .pm-sw:checked::after { transform: translateX(18px); }

    .pm-m { border: 1.5px solid #DCECF5; border-radius: 16px; overflow: hidden; margin-bottom: 14px; background: #fff; }
    .pm-m.off { opacity: .7; }
    .pm-m-h { display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: #F8FBFE; flex-wrap: wrap; }
    .pm-move { display: flex; flex-direction: column; gap: 2px; }
    .pm-move button { border: 1px solid #DCECF5; background: #fff; border-radius: 6px; width: 28px; height: 20px; font-size: 10px; color: #475569; cursor: pointer; padding: 0; }
    .pm-move button:disabled { opacity: .35; cursor: default; }
    .pm-m-meta { font-size: 12.5px; color: #64748B; display: flex; gap: 10px; flex-wrap: wrap; }
    .pm-tag { font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px; background: #EEF2F7; color: #475569; }
    .pm-tag.red { background: #FEE2E2; color: #991B1B; }
    .pm-m-acts { margin-left: auto; display: flex; gap: 6px; flex-wrap: wrap; }
    .pm-accs { padding: 12px 16px 16px; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
    .pm-a { display: flex; gap: 12px; padding: 12px; border: 1px solid #E2E8F0; border-radius: 14px; align-items: flex-start; }
    .pm-a.off { background: #F8FAFC; opacity: .7; }
    .pm-qr { width: 78px; height: 78px; border-radius: 10px; border: 1px solid #E2E8F0; overflow: hidden; flex-shrink: 0; background: #fff; display: flex; align-items: center; justify-content: center; color: #CBD5E1; font-size: 26px; }
    .pm-qr img { width: 100%; height: 100%; object-fit: contain; }
    .pm-a-t { font-weight: 800; color: #08224A; font-size: 14px; }
    .pm-a-s { font-size: 12.5px; color: #64748B; margin-top: 2px; word-break: break-all; }
    .pm-a-acts { display: flex; gap: 6px; margin-top: 8px; }
    .pm-add-acc { border: 2px dashed #B9D6E6; border-radius: 14px; background: #F8FBFE; color: #1D7FE5; font-weight: 700; font-size: 13.5px; cursor: pointer; min-height: 104px; display: flex; align-items: center; justify-content: center; gap: 7px; }

    .pm-modal { position: fixed; inset: 0; z-index: 3000; background: rgba(8,34,74,.45); display: flex; align-items: flex-start; justify-content: center; padding: 30px 16px; overflow-y: auto; }
    .pm-modal[hidden] { display: none; }
    .pm-box { background: #fff; border-radius: 18px; width: 100%; max-width: 560px; padding: 22px; box-shadow: 0 24px 60px rgba(8,34,74,.3); }
    .pm-box h4 { margin: 0 0 16px; color: #08224A; font-size: 18px; font-weight: 800; display: flex; align-items: center; }
    .pm-box h4 button { margin-left: auto; border: none; background: none; font-size: 22px; color: #64748B; cursor: pointer; }
    .pm-f { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pm-f .full { grid-column: 1 / -1; }
    .pm-f label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; }
    .pm-f label small { font-weight: 500; color: #94A3B8; }
    .pm-f input[type=text], .pm-f input[type=url], .pm-f select, .pm-f textarea { width: 100%; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 9px 12px; font-size: 14px; outline: none; background: #F8FBFE; font-family: inherit; }
    .pm-f input:focus, .pm-f select:focus, .pm-f textarea:focus { border-color: #1D7FE5; background: #fff; }
    .pm-f input[type=color] { width: 100%; height: 40px; border: 1.5px solid #DCECF5; border-radius: 10px; padding: 3px; background: #fff; }
    .pm-f textarea { min-height: 90px; resize: vertical; }
    .pm-f .chk { display: flex; gap: 18px; flex-wrap: wrap; }
    .pm-f .chk label { display: inline-flex; gap: 8px; align-items: center; font-weight: 600; margin: 0; cursor: pointer; font-size: 13.5px; }
    .pm-img { display: flex; gap: 12px; align-items: center; padding: 10px; border: 1.5px dashed #B9D6E6; border-radius: 12px; }
    .pm-img .pv { width: 84px; height: 84px; border-radius: 10px; border: 1px solid #E2E8F0; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #CBD5E1; font-size: 26px; flex-shrink: 0; }
    .pm-img .pv.logo { width: 130px; height: 56px; }
    .pm-img .pv img { width: 100%; height: 100%; object-fit: contain; }
    .pm-img .r { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
    .pm-img .r input[type=file] { font-size: 12.5px; }
    .pm-err { grid-column: 1 / -1; color: #B91C1C; background: #FEF2F2; border-radius: 10px; padding: 8px 12px; font-size: 13px; }
    .pm-err[hidden] { display: none; }
    .pm-foot { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
    [data-bank-only][hidden] { display: none; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 20px 15px; } }
    @media (max-width: 640px) { .pm-f { grid-template-columns: 1fr; } .pm-m-acts { margin-left: 0; width: 100%; } .pm-accs { grid-template-columns: 1fr; } }
</style>

<?php partial('Shared/_AdminSidebar'); ?>

<div class="admin-main-content">
    <div class="pm-head">
        <div>
            <h2><i class="fas fa-wallet me-2" style="color:#1D7FE5;"></i>Payment Methods &amp; QR</h2>
            <p>পেমেন্ট মেথড, ব্যাংক / মোবাইল অ্যাকাউন্ট আর স্ক্যান করার QR ছবি যোগ বা পরিবর্তন করুন। লোগো আপলোড না করলে মেথডের রঙে নাম দেখায়।</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="pm-btn out" href="/Admin/Payments"><i class="fas fa-money-bill-wave"></i> Payments</a>
            <button type="button" class="pm-btn pri" data-new-method><i class="fas fa-plus"></i> নতুন মেথড</button>
        </div>
    </div>
    <?= csrf_field() ?>

    <div class="pm-card pm-rules" id="pmRules">
        <h5><i class="fas fa-scale-balanced" style="color:#F59E0B;"></i> পেমেন্ট বাধ্যতামূলক করার নিয়ম</h5>
        <p>বন্ধ থাকলে সবাই আগের মতো ফ্রিতে ব্যবহার করতে পারবে। পেমেন্ট নেওয়া শুরু করতে চাইলে চালু করুন।</p>
        <label class="pm-rule"><input type="checkbox" class="pm-sw" data-rule="teacher"<?= $rules['teacher'] ? ' checked' : '' ?> />
            <div><b>শিক্ষকদের Subscription বাধ্যতামূলক</b><span>চালু থাকলে যেসব শিক্ষকের Subscription নেই, তারা Find Tutor তালিকায় দেখাবে না এবং “Teacher Wanted” পোস্ট করতে পারবে না।</span></div></label>
        <label class="pm-rule"><input type="checkbox" class="pm-sw" data-rule="student"<?= $rules['student'] ? ' checked' : '' ?> />
            <div><b>শিক্ষার্থীদের Tuition Post-এ পেমেন্ট বাধ্যতামূলক</b><span>চালু থাকলে Tuition Circular-এ “Teacher Needed” পোস্ট করতে প্যাকেজ (পোস্ট ক্রেডিট) লাগবে। প্রতিটা পোস্টে ১টি ক্রেডিট খরচ হবে।</span></div></label>
    </div>

    <div id="pmList">
        <?php if (!$methods): ?>
            <div class="pm-card" style="text-align:center;color:#64748B;">কোনো পেমেন্ট মেথড নেই — “নতুন মেথড” চেপে যোগ করুন।</div>
        <?php endif; ?>
        <?php foreach ($methods as $i => $m): ?>
            <div class="pm-m<?= $m->IsActive ? '' : ' off' ?>" id="pm-<?= (int)$m->Id ?>">
                <div class="pm-m-h">
                    <div class="pm-move">
                        <button type="button" data-mv="-1" data-id="<?= (int)$m->Id ?>" title="উপরে"<?= $i === 0 ? ' disabled' : '' ?>><i class="fas fa-chevron-up"></i></button>
                        <button type="button" data-mv="1" data-id="<?= (int)$m->Id ?>" title="নিচে"<?= $i === count($methods) - 1 ? ' disabled' : '' ?>><i class="fas fa-chevron-down"></i></button>
                    </div>
                    <?= payment_logo_html($m, 38) ?>
                    <div class="pm-m-meta">
                        <span class="pm-tag"><?= e($kindBn[$m->Kind]) ?></span>
                        <span class="pm-tag"><?= $m->TxnRequired ? 'Transaction ID লাগবে' : 'Transaction ID ঐচ্ছিক' ?></span>
                        <span class="pm-tag"><?= e(bn_num(count($m->Accounts))) ?>টি অ্যাকাউন্ট</span>
                        <?php if (!$m->IsActive): ?><span class="pm-tag red">বন্ধ</span><?php endif; ?>
                    </div>
                    <div class="pm-m-acts">
                        <button type="button" class="pm-btn out sm" data-edit-method="<?= (int)$m->Id ?>"><i class="fas fa-pen"></i> Edit</button>
                        <button type="button" class="pm-btn del sm" data-del-method="<?= (int)$m->Id ?>"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="pm-accs">
                    <?php foreach ($m->Accounts as $a): ?>
                        <div class="pm-a<?= $a->IsActive ? '' : ' off' ?>">
                            <span class="pm-qr"><?php if ($a->QrImageUrl): ?><img src="<?= e($a->QrImageUrl) ?>" alt="QR" loading="lazy" /><?php else: ?><i class="fas fa-qrcode"></i><?php endif; ?></span>
                            <div style="min-width:0;flex:1;">
                                <div class="pm-a-t"><?= e($a->Title) ?></div>
                                <?php if ($a->BankName): ?><div class="pm-a-s"><i class="fas fa-building-columns"></i> <?= e($a->BankName) ?><?= $a->Branch ? ', ' . e($a->Branch) : '' ?></div><?php endif; ?>
                                <?php if ($a->AccountName || $a->AccountNumber): ?><div class="pm-a-s"><?= e(trim($a->AccountName . ' · ' . $a->AccountNumber, ' ·')) ?></div><?php endif; ?>
                                <?php if (!$a->IsActive): ?><div class="pm-a-s" style="color:#B91C1C;font-weight:700;">বন্ধ</div><?php endif; ?>
                                <div class="pm-a-acts">
                                    <button type="button" class="pm-btn out sm" data-edit-acc="<?= (int)$a->Id ?>" data-m="<?= (int)$m->Id ?>"><i class="fas fa-pen"></i> Edit</button>
                                    <button type="button" class="pm-btn del sm" data-del-acc="<?= (int)$a->Id ?>"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="button" class="pm-add-acc" data-new-acc="<?= (int)$m->Id ?>"><i class="fas fa-plus"></i> <?= $m->Kind === 'bank' ? 'নতুন ব্যাংক অ্যাকাউন্ট' : 'নতুন অ্যাকাউন্ট / নম্বর' ?></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- মেথড ফর্ম -->
<div class="pm-modal" id="mModal" hidden role="dialog" aria-modal="true">
    <form class="pm-box" id="mForm" novalidate>
        <h4><span id="mTitle">নতুন পেমেন্ট মেথড</span><button type="button" data-close aria-label="Close">&times;</button></h4>
        <input type="hidden" name="id" value="0" />
        <div class="pm-f">
            <div><label>নাম</label><input type="text" name="name" maxlength="40" placeholder="যেমন: Upay" /></div>
            <div><label>ধরন</label><select name="kind"><option value="mobile">মোবাইল ব্যাংকিং</option><option value="bank">ব্যাংক</option><option value="other">অন্যান্য</option></select></div>
            <div><label>রঙ <small>— লোগো না থাকলে এই রঙে নাম দেখায়</small></label><input type="color" name="color" value="#1D7FE5" /></div>
            <div class="chk" style="align-items:flex-end;"><label><input type="checkbox" class="pm-sw" name="txnRequired" checked /> Transaction ID বাধ্যতামূলক</label></div>
            <div class="full"><label>লোগো <small>— PNG/JPG/WEBP, ৩ MB এর কম</small></label>
                <div class="pm-img"><span class="pv logo" id="mLogoPv"><i class="fas fa-image"></i></span>
                    <div class="r"><input type="file" name="logo" accept="image/*" id="mLogoFile" /><input type="text" name="logoUrl" placeholder="অথবা ছবির লিংক (https://...)" style="font-size:13px;" />
                        <label style="font-weight:600;margin:0;"><input type="checkbox" name="removeLogo" /> লোগো সরিয়ে দিন</label></div></div></div>
            <div class="full"><label>পেমেন্ট করার নিয়ম <small>— পেমেন্ট পেজে দেখাবে</small></label><textarea name="instructions" maxlength="1000"></textarea></div>
            <div class="full chk"><label><input type="checkbox" class="pm-sw" name="isActive" checked /> চালু (পেমেন্ট পেজে দেখাবে)</label></div>
            <div class="pm-err" hidden></div>
        </div>
        <div class="pm-foot"><button type="button" class="pm-btn out" data-close>বাতিল</button><button type="submit" class="pm-btn pri"><i class="fas fa-save"></i> সেভ করুন</button></div>
    </form>
</div>

<!-- অ্যাকাউন্ট ফর্ম -->
<div class="pm-modal" id="aModal" hidden role="dialog" aria-modal="true">
    <form class="pm-box" id="aForm" novalidate>
        <h4><span id="aTitle">নতুন অ্যাকাউন্ট</span><button type="button" data-close aria-label="Close">&times;</button></h4>
        <input type="hidden" name="id" value="0" /><input type="hidden" name="methodId" value="0" />
        <div class="pm-f">
            <div class="full"><label>শিরোনাম <small>— যেমন: Pubali Bank — Mirpur Branch / bKash Personal</small></label><input type="text" name="title" maxlength="80" /></div>
            <div data-bank-only><label>ব্যাংকের নাম</label><input type="text" name="bankName" maxlength="100" placeholder="Pubali Bank PLC" /></div>
            <div data-bank-only><label>Branch</label><input type="text" name="branch" maxlength="100" /></div>
            <div><label>Account name / নাম</label><input type="text" name="accountName" maxlength="100" /></div>
            <div><label>Account number / মোবাইল নম্বর</label><input type="text" name="accountNumber" maxlength="40" /></div>
            <div data-bank-only><label>Routing number</label><input type="text" name="routingNumber" maxlength="20" /></div>
            <div><label>ধরন <small>— যেমন: Personal, Merchant, Savings</small></label><input type="text" name="accountType" maxlength="40" /></div>
            <div class="full"><label>স্ক্যান করার QR ছবি <small>— PNG/JPG/WEBP, ৩ MB এর কম</small></label>
                <div class="pm-img"><span class="pv" id="aQrPv"><i class="fas fa-qrcode"></i></span>
                    <div class="r"><input type="file" name="qr" accept="image/*" id="aQrFile" /><input type="text" name="qrUrl" placeholder="অথবা ছবির লিংক (https://...)" style="font-size:13px;" />
                        <label style="font-weight:600;margin:0;"><input type="checkbox" name="removeQr" /> QR সরিয়ে দিন</label></div></div></div>
            <div class="full chk"><label><input type="checkbox" class="pm-sw" name="isActive" checked /> চালু</label></div>
            <div class="pm-err" hidden></div>
        </div>
        <div class="pm-foot"><button type="button" class="pm-btn out" data-close>বাতিল</button><button type="submit" class="pm-btn pri"><i class="fas fa-save"></i> সেভ করুন</button></div>
    </form>
</div>

<?php section_start('Scripts'); ?>
<script>
(function () {
    var M = <?= js(array_map(fn($m) => (array)$m, $methods)) ?>;
    function token() { var t = document.querySelector('input[name="__RequestVerificationToken"]'); return t ? t.value : ''; }
    async function post(url, data) {
        var fd = data instanceof FormData ? data : new FormData();
        if (!(data instanceof FormData)) Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fd.append('__RequestVerificationToken', token());
        var r = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        return r.json();
    }
    function findM(id) { return M.find(function (m) { return m.Id === id; }); }
    function findA(id) { var o = null; M.forEach(function (m) { (m.Accounts || []).forEach(function (a) { if (a.Id === id) o = a; }); }); return o; }
    function pv(el, url, icon) { el.innerHTML = url ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="" />' : '<i class="fas ' + icon + '"></i>'; }
    function preview(input, el, icon) { input.addEventListener('change', function () { var f = input.files[0]; if (!f) return; var r = new FileReader(); r.onload = function () { pv(el, r.result, icon); }; r.readAsDataURL(f); }); }

    var mModal = document.getElementById('mModal'), mForm = document.getElementById('mForm');
    var aModal = document.getElementById('aModal'), aForm = document.getElementById('aForm');
    preview(document.getElementById('mLogoFile'), document.getElementById('mLogoPv'), 'fa-image');
    preview(document.getElementById('aQrFile'), document.getElementById('aQrPv'), 'fa-qrcode');

    function openMethod(m) {
        mForm.reset(); mForm.querySelector('.pm-err').hidden = true;
        mForm.id.value = m ? m.Id : 0; mForm.name.value = m ? m.Name : ''; mForm.kind.value = m ? m.Kind : 'mobile';
        mForm.color.value = m ? m.Color : '#1D7FE5'; mForm.txnRequired.checked = m ? !!m.TxnRequired : true;
        mForm.instructions.value = m ? (m.Instructions || '') : ''; mForm.isActive.checked = m ? !!m.IsActive : true;
        mForm.logoUrl.value = m && m.LogoUrl ? m.LogoUrl : '';
        pv(document.getElementById('mLogoPv'), m && m.LogoUrl, 'fa-image');
        document.getElementById('mTitle').textContent = m ? m.Name + ' — এডিট' : 'নতুন পেমেন্ট মেথড';
        mModal.hidden = false; mForm.name.focus();
    }
    function openAcc(a, methodId) {
        var m = findM(methodId);
        aForm.reset(); aForm.querySelector('.pm-err').hidden = true;
        aForm.id.value = a ? a.Id : 0; aForm.methodId.value = methodId;
        ['title', 'bankName', 'branch', 'accountName', 'accountNumber', 'routingNumber', 'accountType'].forEach(function (k) {
            var key = k.charAt(0).toUpperCase() + k.slice(1); aForm[k].value = a && a[key] ? a[key] : '';
        });
        aForm.isActive.checked = a ? !!a.IsActive : true;
        aForm.qrUrl.value = a && a.QrImageUrl ? a.QrImageUrl : '';
        pv(document.getElementById('aQrPv'), a && a.QrImageUrl, 'fa-qrcode');
        document.querySelectorAll('[data-bank-only]').forEach(function (x) { x.hidden = m.Kind !== 'bank'; });
        document.getElementById('aTitle').textContent = (a ? 'অ্যাকাউন্ট এডিট' : 'নতুন অ্যাকাউন্ট') + ' — ' + m.Name;
        aModal.hidden = false; aForm.title.focus();
    }
    async function submit(form, url) {
        var err = form.querySelector('.pm-err'), btn = form.querySelector('[type=submit]');
        var fd = new FormData(form);
        form.querySelectorAll('input[type=checkbox]').forEach(function (c) { fd.set(c.name, c.checked ? 'true' : 'false'); });
        btn.disabled = true; err.hidden = true;
        try {
            var d = await post(url, fd);
            if (!d.success) { err.textContent = d.message || 'সেভ হয়নি।'; err.hidden = false; btn.disabled = false; return; }
            location.reload();
        } catch (x) { err.textContent = 'ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'; err.hidden = false; btn.disabled = false; }
    }
    mForm.addEventListener('submit', function (e) { e.preventDefault(); submit(mForm, '/Admin/SavePaymentMethod'); });
    aForm.addEventListener('submit', function (e) { e.preventDefault(); submit(aForm, '/Admin/SavePaymentAccount'); });
    [mModal, aModal].forEach(function (md) { md.addEventListener('click', function (e) { if (e.target === md || e.target.closest('[data-close]')) md.hidden = true; }); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { mModal.hidden = true; aModal.hidden = true; } });

    document.addEventListener('click', async function (e) {
        var t;
        if (e.target.closest('[data-new-method]')) return openMethod(null);
        if ((t = e.target.closest('[data-edit-method]'))) return openMethod(findM(+t.getAttribute('data-edit-method')));
        if ((t = e.target.closest('[data-new-acc]'))) return openAcc(null, +t.getAttribute('data-new-acc'));
        if ((t = e.target.closest('[data-edit-acc]'))) return openAcc(findA(+t.getAttribute('data-edit-acc')), +t.getAttribute('data-m'));
        if ((t = e.target.closest('[data-del-method]'))) {
            var m = findM(+t.getAttribute('data-del-method'));
            if (!confirm('“' + m.Name + '” মেথড আর এর সব অ্যাকাউন্ট/QR মুছে ফেলবেন?')) return;
            var d = await post('/Admin/DeletePaymentMethod', { id: m.Id }); if (d.success) location.reload(); else alert(d.message || 'Failed'); return;
        }
        if ((t = e.target.closest('[data-del-acc]'))) {
            if (!confirm('এই অ্যাকাউন্ট/QR মুছে ফেলবেন?')) return;
            var d2 = await post('/Admin/DeletePaymentAccount', { id: t.getAttribute('data-del-acc') }); if (d2.success) location.reload(); else alert(d2.message || 'Failed'); return;
        }
        if ((t = e.target.closest('[data-mv]')) && !t.disabled) {
            t.disabled = true;
            var d3 = await post('/Admin/MovePaymentMethod', { id: t.getAttribute('data-id'), dir: t.getAttribute('data-mv') }); if (d3.success) location.reload(); else alert(d3.message || 'Failed');
        }
    });
    document.querySelectorAll('[data-rule]').forEach(function (c) {
        c.addEventListener('change', async function () {
            var data = {}; document.querySelectorAll('[data-rule]').forEach(function (x) { data[x.getAttribute('data-rule')] = x.checked ? 'true' : 'false'; });
            c.disabled = true;
            var d = await post('/Admin/SavePaymentRules', data);
            c.disabled = false;
            if (!d.success) { c.checked = !c.checked; alert(d.message || 'Failed'); }
        });
    });
})();
</script>
<?php section_end(); ?>
