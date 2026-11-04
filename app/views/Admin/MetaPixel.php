<?php
/** Admin → Meta Pixel & Conversions API (শুধু Super Admin) */
$ViewData['Title'] = 'Meta Pixel';
$S = $ViewBag->S;
$events = $ViewBag->Events;
$on = $S['px_enabled'] !== '0' && $S['meta_pixel_id'] !== '';
$evOn = array_filter(array_keys($events), fn($e) => ($S['px_ev_' . $e] ?? '1') !== '0');
$mode = $ViewBag->HasToken ? 'Browser + Server (CAPI)' : 'Browser only';
?>
<style>
    .admin-main-content { margin-left: 250px; padding: 20px 20px 110px; min-height: calc(100vh - 80px); }
    .mp { max-width: 1000px; }
    .mp h2 { color: #0F172A; margin: 0; font-size: 26px; font-weight: 800; }
    .mp .sub { color: #64748B; margin: 6px 0 18px; font-size: 14.5px; line-height: 1.6; max-width: 680px; }
    .mp-status { display: flex; align-items: center; gap: 14px; border-radius: 16px; padding: 16px 20px; margin-bottom: 18px; border: 1px solid #A7F3D0; background: #ECFDF5; }
    .mp-status.off { border-color: #E2E8F0; background: #F8FAFC; }
    .mp-dot { width: 14px; height: 14px; border-radius: 50%; background: #10B981; box-shadow: 0 0 0 5px rgba(16,185,129,.18); flex-shrink: 0; }
    .mp-status.off .mp-dot { background: #94A3B8; box-shadow: 0 0 0 5px rgba(148,163,184,.2); }
    .mp-status b { display: block; font-size: 17px; color: #0F172A; } .mp-status small { color: #475569; font-size: 13.5px; }
    .mp-card { background: #fff; border: 1px solid #E8EEF4; border-radius: 18px; padding: 22px 24px; margin-bottom: 18px; box-shadow: 0 4px 16px rgba(15,23,42,.04); }
    .mp-card h3 { margin: 0 0 16px; font-size: 17px; font-weight: 800; color: #0F172A; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .mp-row { display: flex; align-items: center; justify-content: space-between; gap: 18px; }
    .mp-row .t b { display: block; font-size: 15.5px; color: #0F172A; } .mp-row .t small { color: #64748B; font-size: 13px; line-height: 1.55; }
    .mp-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 20px; }
    .mp-f label { display: block; font-size: 13.5px; font-weight: 700; color: #334155; margin-bottom: 7px; }
    .mp-f input { width: 100%; height: 46px; border: 1.5px solid #DCE5EE; border-radius: 12px; padding: 0 14px; font-size: 15px; font-family: inherit; color: #0F172A; background: #fff; }
    .mp-f input:focus { outline: none; border-color: #0E9F8A; box-shadow: 0 0 0 3px rgba(14,159,138,.12); }
    .mp-f .h { font-size: 12.5px; color: #64748B; margin-top: 6px; line-height: 1.55; }
    .mp-f .saved { color: #047857; font-weight: 700; }
    .mp-sw { position: relative; width: 52px; height: 30px; flex-shrink: 0; }
    .mp-sw input { opacity: 0; width: 0; height: 0; position: absolute; }
    .mp-sw span { position: absolute; inset: 0; border-radius: 999px; background: #CBD5E1; cursor: pointer; transition: background .2s; }
    .mp-sw span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 24px; height: 24px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
    .mp-sw input:checked + span { background: #0E9F8A; }
    .mp-sw input:checked + span::after { transform: translateX(22px); }
    .mp-sw input:focus-visible + span { box-shadow: 0 0 0 3px rgba(14,159,138,.3); }
    .mp-ev { padding: 14px 0; border-top: 1px solid #F1F5F9; }
    .mp-ev:first-of-type { border-top: none; padding-top: 4px; }
    .mp-ev .t b small { font-weight: 700; font-size: 11px; color: #7C3AED; background: #F3E8FF; border-radius: 999px; padding: 2px 8px; margin-left: 6px; vertical-align: middle; }
    .mp-links { display: flex; gap: 16px; } .mp-links button { border: none; background: none; color: #0E9F8A; font-weight: 700; font-size: 14px; cursor: pointer; padding: 0; }
    .mp-btn { height: 44px; padding: 0 22px; border-radius: 11px; border: none; background: #0E7C6B; color: #fff; font-weight: 800; font-size: 15px; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 8px; }
    .mp-btn:hover { background: #0B6758; } .mp-btn:disabled { opacity: .6; cursor: default; }
    .mp-btn.o { background: #fff; color: #334155; border: 1.5px solid #DCE5EE; font-weight: 700; }
    .mp-btn.o:hover { background: #F8FAFC; }
    .mp-res { margin-top: 12px; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; line-height: 1.6; display: none; }
    .mp-res.ok { display: block; background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
    .mp-res.err { display: block; background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
    .mp-bar { position: fixed; left: 270px; right: 20px; bottom: 16px; z-index: 50; max-width: 1000px; background: #fff; border: 1px solid #E8EEF4; border-radius: 16px; box-shadow: 0 10px 30px rgba(15,23,42,.12); padding: 12px 14px 12px 22px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .mp-bar small { color: #64748B; font-size: 13.5px; }
    .mp-toast { position: absolute; left: 50%; bottom: calc(100% + 10px); transform: translate(-50%, 8px); opacity: 0; pointer-events: none; transition: opacity .2s, transform .2s;
        padding: 10px 16px; border-radius: 12px; font-size: 14px; font-weight: 600; box-shadow: 0 8px 24px rgba(15,23,42,.15); max-width: 92%; width: max-content; }
    .mp-toast.show { opacity: 1; transform: translate(-50%, 0); }
    .mp-toast.ok { background: #065F46; color: #fff; } .mp-toast.err { background: #B91C1C; color: #fff; }
    .mp-help { background: #F8FAFC; border-radius: 12px; padding: 12px 14px; font-size: 13px; color: #475569; line-height: 1.7; margin-top: 14px; }
    .mp-help code { background: #E2E8F0; padding: 1px 6px; border-radius: 6px; font-size: 12px; }
    @media (max-width: 992px) { .admin-main-content { margin-left: 0; padding: 14px 14px 120px; } .mp-bar { left: 12px; right: 12px; } }
    @media (max-width: 680px) { .mp-grid { grid-template-columns: minmax(0, 1fr); } .mp-card { padding: 18px 16px; } .mp h2 { font-size: 22px; } .mp-bar small { display: none; } .mp-bar .mp-btn { width: 100%; justify-content: center; } }
</style>
<?php partial('Shared/_AdminSidebar'); ?>
<div class="admin-main-content">
<form class="mp" id="mpForm" novalidate>
    <?= csrf_field() ?>
    <h2>Meta Pixel &amp; Conversions API</h2>
    <p class="sub">সাইটের ভিজিট, সার্চ, সাইনআপ, টিউটর রিকোয়েস্ট আর পোস্টের তথ্য Facebook বিজ্ঞাপনে পাঠায় — যাতে বিজ্ঞাপন ঠিক মানুষের কাছে যায়। অ্যাডমিন অ্যাকাউন্ট কখনো ট্র্যাক হয় না।</p>

    <div class="mp-status<?= $on ? '' : ' off' ?>" id="mpStatus">
        <span class="mp-dot"></span>
        <div><b id="mpStTitle"><?= $on ? 'Active' : 'বন্ধ' ?></b>
            <small id="mpStLine"><?= $on ? 'Pixel ' . e($S['meta_pixel_id']) . ' · ' . $mode . ' · ' . count($evOn) . '/' . count($events) . ' events' : 'Pixel চালু নেই — কোনো ইভেন্ট যাচ্ছে না।' ?></small></div>
    </div>

    <div class="mp-card">
        <div class="mp-row">
            <div class="t"><b>Enable Meta Pixel</b><small>চালু থাকলে সাইটের সব পেজে Pixel লোড হবে আর নিচের ইভেন্টগুলো যাবে। অ্যাডমিন লগইন থাকলে কখনো ট্র্যাক হয় না।</small></div>
            <label class="mp-sw"><input type="checkbox" name="enabled" value="1" <?= $S['px_enabled'] !== '0' ? 'checked' : '' ?> /><span></span></label>
        </div>
    </div>

    <div class="mp-card">
        <h3>Connection</h3>
        <div class="mp-grid">
            <div class="mp-f">
                <label for="mpId">Pixel ID</label>
                <input type="text" id="mpId" name="pixelId" inputmode="numeric" value="<?= e($S['meta_pixel_id']) ?>" placeholder="1125687780363494" />
                <div class="h">Events Manager → Data sources → Pixel এর ১৫–১৬ ডিজিটের ID।</div>
            </div>
            <div class="mp-f">
                <label for="mpTest">Test event code</label>
                <input type="text" id="mpTest" name="testCode" value="<?= e($S['px_test_code']) ?>" placeholder="TEST12345" />
                <div class="h">Events Manager → Test Events থেকে। টেস্ট শেষে খালি করে দিন (সার্ভার ইভেন্টে কাজে লাগে)।</div>
            </div>
            <div class="mp-f" style="grid-column: 1 / -1;">
                <label for="mpTok">Conversions API access token <span style="font-weight:500;color:#94A3B8;">(ঐচ্ছিক)</span></label>
                <input type="password" id="mpTok" name="capiToken" autocomplete="off" placeholder="<?= $ViewBag->HasToken ? 'নতুন token দিলে আগেরটা বদলে যাবে' : 'Optional' ?>" />
                <div class="h">দিলে একই ইভেন্ট সার্ভার থেকেও যায় (একই event ID — Meta duplicate বাদ দেয়)। Ad-blocker / iPhone এ ইভেন্ট হারায় না।
                    <span id="mpTokSaved"><?php if ($ViewBag->HasToken): ?><br><span class="saved"><i class="fas fa-lock"></i> Token সেভ করা আছে (<?= e($ViewBag->TokenHint) ?>)</span> · <label style="display:inline;font-weight:600;cursor:pointer;"><input type="checkbox" name="clearToken" value="1" /> Token মুছে দিন</label><?php endif; ?></span>
                </div>
            </div>
        </div>
        <div class="mp-row" style="margin-top:18px;">
            <div class="t"><b>Advanced matching</b><small>লগইন করা ইউজারের ইমেইল / ফোন SHA-256 hash করে পাঠায় — আসল তথ্য যায় না, কিন্তু Meta ইউজার মেলাতে পারে (match rate বাড়ে)।</small></div>
            <label class="mp-sw"><input type="checkbox" name="advMatch" value="1" <?= $S['px_adv_match'] !== '0' ? 'checked' : '' ?> /><span></span></label>
        </div>
    </div>

    <div class="mp-card">
        <h3>Events <span class="mp-links"><button type="button" data-all="1">Select all</button><button type="button" data-all="0">Clear</button></span></h3>
        <?php foreach ($events as $ev => [$desc, $std]): ?>
            <div class="mp-row mp-ev">
                <div class="t"><b><?= e($ev) ?><?= $std ? '' : '<small>Custom</small>' ?></b><small><?= e($desc) ?>।</small></div>
                <label class="mp-sw"><input type="checkbox" name="events[]" value="<?= e($ev) ?>" <?= in_array($ev, $evOn, true) ? 'checked' : '' ?> /><span></span></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mp-card">
        <div class="mp-row">
            <div class="t"><b>Check the connection</b><small>সেভ করার পরে সার্ভার থেকে একটা test PageView পাঠায় আর Meta কী উত্তর দিল দেখায়। এর জন্য Conversions API token লাগবে।</small></div>
            <button type="button" class="mp-btn o" id="mpTestBtn"><i class="fas fa-paper-plane"></i> Send test event</button>
        </div>
        <div class="mp-res" id="mpTestRes"></div>
        <div class="mp-help"><b>ব্রাউজার Pixel চেক করার সহজ উপায়:</b> Facebook Events Manager → আপনার Pixel → <b>Test Events</b> → সাইটের ঠিকানা দিয়ে <b>Open website</b>। অ্যাডমিন থেকে লগআউট করা (বা Incognito) ব্রাউজারে সাইট ঘুরলে PageView, Search ইত্যাদি সাথে সাথে সেখানে দেখা যাবে। Ad blocker বন্ধ রাখুন।</div>
    </div>

    <div class="mp-bar">
        <small>সব সেকশন একসাথে সেভ হয়।</small>
        <div class="mp-toast" id="mpToast" role="status" aria-live="polite"></div>
        <button type="submit" class="mp-btn" id="mpSave"><i class="fas fa-save"></i> Save settings</button>
    </div>
</form>
</div>

<script>
(function () {
    var f = document.getElementById('mpForm');
    var tEl = document.getElementById('mpToast'), tT;
    function toast(ok, msg) {
        tEl.className = 'mp-toast show ' + (ok ? 'ok' : 'err'); tEl.innerHTML = '<i class="fas ' + (ok ? 'fa-circle-check' : 'fa-circle-exclamation') + '"></i> ';
        tEl.appendChild(document.createTextNode(msg)); clearTimeout(tT); tT = setTimeout(function () { tEl.className = 'mp-toast'; }, ok ? 2600 : 6000);
    }
    function post(url, fd) {
        fd.append('__RequestVerificationToken', f.querySelector('input[name="__RequestVerificationToken"]').value);
        return fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); });
    }
    f.querySelectorAll('[data-all]').forEach(function (b) {
        b.addEventListener('click', function () { var v = b.getAttribute('data-all') === '1'; f.querySelectorAll('input[name="events[]"]').forEach(function (c) { c.checked = v; }); });
    });
    f.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('mpSave'); btn.disabled = true;
        var fd = new FormData(f); fd.delete('__RequestVerificationToken');
        post('/Admin/SaveMetaPixel', fd).then(function (d) {
            btn.disabled = false;
            if (!d.success) return toast(false, d.message);
            var s = d.status, box = document.getElementById('mpStatus');
            box.classList.toggle('off', !s.active);
            document.getElementById('mpStTitle').textContent = s.active ? 'Active' : 'বন্ধ';
            document.getElementById('mpStLine').textContent = s.active ? ('Pixel ' + s.pixel + ' · ' + s.mode + ' · ' + s.events + '/' + s.total + ' events') : 'Pixel চালু নেই — কোনো ইভেন্ট যাচ্ছে না।';
            var tok = document.getElementById('mpTok'); tok.value = '';
            document.getElementById('mpTokSaved').innerHTML = s.tokenHint ? '<br><span class="saved"><i class="fas fa-lock"></i> Token সেভ করা আছে (' + s.tokenHint + ')</span> · <label style="display:inline;font-weight:600;cursor:pointer;"><input type="checkbox" name="clearToken" value="1" /> Token মুছে দিন</label>' : '';
            tok.placeholder = s.tokenHint ? 'নতুন token দিলে আগেরটা বদলে যাবে' : 'Optional';
            toast(true, d.message);
        }).catch(function () { btn.disabled = false; toast(false, 'সেভ করা যায়নি। আবার চেষ্টা করুন।'); });
    });
    document.getElementById('mpTestBtn').addEventListener('click', function () {
        var b = this, res = document.getElementById('mpTestRes');
        b.disabled = true; res.className = 'mp-res';
        post('/Admin/TestMetaPixel', new FormData()).then(function (d) {
            b.disabled = false; res.className = 'mp-res ' + (d.success ? 'ok' : 'err'); res.textContent = d.message;
        }).catch(function () { b.disabled = false; res.className = 'mp-res err'; res.textContent = 'সার্ভারে সমস্যা হয়েছে।'; });
    });
})();
</script>
