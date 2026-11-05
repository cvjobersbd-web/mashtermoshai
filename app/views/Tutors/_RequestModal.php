<?php
/**
 * টিচারের পেজেই "Direct Request" ফর্ম (পপআপ) — আর পড়াতে চাই পেজে নিয়ে যায় না।
 * দরকার: $uid (টিচারের UserId), $name (টিচারের নাম)। কোনো বাটনে data-req-open দিলে ফর্ম খোলে।
 * পাঠায়: POST /FindTutor/SubmitTutorRequest (সার্ভারে প্যাকেজ যাচাই হয়)
 */
if (!empty($GLOBALS['__req_modal_once'])) return;
$GLOBALS['__req_modal_once'] = 1;
$tutorCode = '';
try { foreach (db()->GetTutorDirectory() as $__t) if ((int)$__t->UserId === (int)$uid) { $tutorCode = (string)$__t->TutorId; break; } } catch (Throwable $e) { }
if ($tutorCode === '') return;   // তালিকায় নেই এমন টিচার — রিকোয়েস্ট হয় না
$meU = current_user_id() !== null ? db()->GetUserById((int)current_user_id()) : null;
$myName = $meU ? (!is_blank($meU->FullName) ? (string)$meU->FullName : (string)$meU->UserName) : '';
$myPhone = $meU ? preg_replace('/\D/', '', (string)($meU->MobileNumber ?? '')) : '';
if (str_starts_with($myPhone, '880')) $myPhone = substr($myPhone, 2);
$classes = tuition_options()['classes'] ?? [];
?>
<style>
    .rq-ov { position: fixed; inset: 0; z-index: 3000; background: rgba(15,23,42,.55); display: none; align-items: center; justify-content: center; padding: 16px; }
    .rq-ov.open { display: flex; }
    .rq { background: #fff; border-radius: 18px; width: min(620px, 100%); max-height: 92vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 24px 70px rgba(0,0,0,.3); }
    .rq-h { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 20px; background: #134856; color: #fff; }
    .rq-h h3 { margin: 0; font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 9px; color: #fff; }
    .rq-x { width: 36px; height: 36px; border-radius: 50%; border: none; background: rgba(255,255,255,.15); color: #fff; font-size: 17px; cursor: pointer; }
    .rq-b { padding: 18px 20px 20px; overflow-y: auto; }
    .rq-intro { border-left: 4px solid #00CFFF; background: #F0FCFF; border-radius: 10px; padding: 10px 14px; font-size: 14px; color: #334155; margin-bottom: 14px; }
    .rq-sec { font-size: 12px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: #64748B; margin: 16px 0 8px; }
    .rq-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .rq-f label { display: block; font-size: 13.5px; font-weight: 700; color: #1E293B; margin-bottom: 6px; }
    .rq-f label b { color: #DC2626; }
    .rq-f input, .rq-f select, .rq-f textarea { width: 100%; border: 1.5px solid #DCE5EF; border-radius: 10px; padding: 10px 12px; font-size: 14.5px; font-family: inherit; color: #0F172A; background: #F8FBFE; outline: none; }
    .rq-f input:focus, .rq-f select:focus, .rq-f textarea:focus { border-color: #00CFFF; background: #fff; }
    .rq-f textarea { min-height: 80px; resize: vertical; }
    .rq-f + .rq-f, .rq-2 + .rq-f, .rq-f + .rq-2, .rq-2 + .rq-2 { margin-top: 12px; }
    .rq-2 > .rq-f { margin-top: 0 !important; }
    .rq-same { display: flex; align-items: center; gap: 7px; font-size: 13px; color: #475569; margin-top: 6px; font-weight: 600; }
    .rq-err { display: none; margin-top: 12px; padding: 10px 12px; border-radius: 10px; background: #FEF2F2; color: #B91C1C; font-size: 13.5px; font-weight: 600; }
    .rq-err.show { display: block; }
    .rq-err a { color: #B91C1C; font-weight: 800; }
    .rq-send { margin-top: 16px; width: 100%; height: 48px; border: none; border-radius: 12px; background: #00CFFF; color: #fff; font-size: 16px; font-weight: 800; cursor: pointer; font-family: inherit; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .rq-send:disabled { opacity: .65; }
    .rq-ok { text-align: center; padding: 18px 6px 6px; }
    .rq-ok .ic { width: 64px; height: 64px; border-radius: 50%; background: #DCFCE7; color: #16A34A; font-size: 28px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
    .rq-ok h4 { margin: 0 0 6px; font-size: 19px; font-weight: 800; color: #0F172A; }
    .rq-ok p { color: #475569; font-size: 14px; margin: 0 0 12px; }
    .rq-ok code { background: #F1F5F9; padding: 4px 10px; border-radius: 8px; font-weight: 800; color: #0F172A; }
    @media (max-width: 560px) { .rq-2 { grid-template-columns: 1fr; } .rq-b { padding: 14px; } }
</style>
<div class="rq-ov" id="rqOv" aria-hidden="true">
    <div class="rq" role="dialog" aria-modal="true" aria-labelledby="rqT">
        <div class="rq-h"><h3 id="rqT"><i class="fas fa-paper-plane"></i> Request This Tutor</h3><button type="button" class="rq-x" data-rq-close aria-label="বন্ধ করুন"><i class="fas fa-xmark"></i></button></div>
        <div class="rq-b" id="rqBody">
            <form id="rqForm" novalidate>
                <div class="rq-intro"><b><?= e($name) ?></b> (<?= e($tutorCode) ?>) কে সরাসরি রিকোয়েস্ট পাঠাচ্ছেন — টিচার নোটিফিকেশন পাবেন, Accept করলে দুজনের চ্যাট খুলে যাবে।</div>
                <div class="rq-sec">Student Information</div>
                <div class="rq-2">
                    <div class="rq-f"><label for="rqName">Student Name <b>*</b></label><input id="rqName" maxlength="100" value="<?= e($myName) ?>" /></div>
                    <div class="rq-f"><label for="rqPhone">Phone Number <b>*</b></label><input id="rqPhone" type="tel" inputmode="numeric" maxlength="11" placeholder="01XXXXXXXXX" value="<?= e($myPhone) ?>" /></div>
                </div>
                <div class="rq-f"><label for="rqWa">WhatsApp Number <b>*</b></label><input id="rqWa" type="tel" inputmode="numeric" maxlength="11" placeholder="01XXXXXXXXX" value="<?= e($myPhone) ?>" />
                    <label class="rq-same"><input type="checkbox" id="rqSame" checked style="width:auto;" /> Same as phone number</label></div>
                <div class="rq-sec">Requirement Details</div>
                <div class="rq-2">
                    <div class="rq-f"><label for="rqClass">Student Class <b>*</b></label><select id="rqClass"><option value="">Select Class</option><?php foreach ($classes as $o): ?><option><?= e($o) ?></option><?php endforeach; ?></select></div>
                    <div class="rq-f"><label for="rqSubj">Required Subject <b>*</b></label><input id="rqSubj" maxlength="120" placeholder="যেমন: Physics, Math" /></div>
                </div>
                <div class="rq-2">
                    <div class="rq-f"><label for="rqArea">Preferred Area</label><input id="rqArea" maxlength="100" placeholder="যেমন: Mirpur 10" /></div>
                    <div class="rq-f"><label for="rqMode">Teaching Mode</label><select id="rqMode"><option value="Both">Both</option><option value="Online">Online</option><option value="Offline">Home / Offline</option></select></div>
                </div>
                <div class="rq-2">
                    <div class="rq-f"><label for="rqDays">Preferred Days</label><input id="rqDays" maxlength="60" placeholder="যেমন: সপ্তাহে ৩ দিন" /></div>
                    <div class="rq-f"><label for="rqTime">Preferred Time</label><input id="rqTime" maxlength="60" placeholder="যেমন: সন্ধ্যা ৬টা" /></div>
                </div>
                <div class="rq-f"><label for="rqBudget">Monthly Budget (৳)</label><input id="rqBudget" inputmode="numeric" maxlength="10" placeholder="যেমন: 5000" /></div>
                <div class="rq-f"><label for="rqNotes">Additional Requirements</label><textarea id="rqNotes" maxlength="1000"></textarea></div>
                <div class="rq-err" id="rqErr" role="alert"></div>
                <button type="submit" class="rq-send" id="rqSend"><i class="fas fa-paper-plane"></i> Send Tutor Request</button>
            </form>
        </div>
    </div>
</div>
<script>
(function () {
    var ov = document.getElementById('rqOv'), form = document.getElementById('rqForm'); if (!ov || !form) return;
    var $ = function (id) { return document.getElementById(id); };
    function open() { ov.classList.add('open'); ov.setAttribute('aria-hidden', 'false'); document.documentElement.style.overflow = 'hidden'; setTimeout(function () { ($('rqName').value ? $('rqClass') : $('rqName')).focus(); }, 50); }
    function close() { ov.classList.remove('open'); ov.setAttribute('aria-hidden', 'true'); document.documentElement.style.overflow = ''; }
    document.addEventListener('click', function (e) { if (e.target.closest('[data-req-open]')) { e.preventDefault(); open(); } });
    ov.addEventListener('click', function (e) { if (e.target === ov || e.target.closest('[data-rq-close]')) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && ov.classList.contains('open')) close(); });
    var same = $('rqSame');
    function sync() { if (same.checked) $('rqWa').value = $('rqPhone').value; $('rqWa').readOnly = same.checked; }
    same.addEventListener('change', sync); $('rqPhone').addEventListener('input', sync); sync();
    var bd = function (v) { return String(v || '').replace(/[০-৯]/g, function (c) { return '০১২৩৪৫৬৭৮৯'.indexOf(c); }).replace(/\D/g, ''); };
    var err = $('rqErr');
    function fail(msg, el, link) {
        err.textContent = msg; if (link) { var a = document.createElement('a'); a.href = link; a.textContent = ' প্যাকেজ নিন →'; err.appendChild(a); }
        err.classList.add('show'); if (el) el.focus(); else err.scrollIntoView({ block: 'nearest' });
    }
    form.addEventListener('submit', async function (e) {
        e.preventDefault(); err.classList.remove('show'); sync();
        var phone = bd($('rqPhone').value), wa = bd($('rqWa').value), re = /^01[3-9]\d{8}$/;
        if (!$('rqName').value.trim()) return fail('Student Name দিন।', $('rqName'));
        if (!re.test(phone)) return fail('সঠিক মোবাইল নম্বর দিন (যেমন 01712345678)।', $('rqPhone'));
        if (!re.test(wa)) return fail('সঠিক WhatsApp নম্বর দিন।', $('rqWa'));
        if (!$('rqClass').value) return fail('Student Class বাছাই করুন।', $('rqClass'));
        if (!$('rqSubj').value.trim()) return fail('Required Subject দিন।', $('rqSubj'));
        var fd = new FormData(), v = function (id) { return $(id).value.trim(); };
        fd.append('tutorId', <?= js($tutorCode) ?>); fd.append('tutorName', <?= js((string)$name) ?>);
        fd.append('studentName', v('rqName')); fd.append('phoneNumber', phone); fd.append('whatsAppNumber', wa);
        fd.append('email', <?= js($meU ? (string)($meU->Email ?? '') : '') ?>);
        fd.append('studentClass', v('rqClass')); fd.append('requiredSubject', v('rqSubj')); fd.append('preferredArea', v('rqArea'));
        fd.append('teachingMode', v('rqMode')); fd.append('preferredDays', v('rqDays')); fd.append('preferredTime', v('rqTime'));
        fd.append('monthlyBudget', bd(v('rqBudget'))); fd.append('additionalNotes', v('rqNotes')); fd.append('requestType', 'SpecificTutor');
        var tk = document.querySelector('input[name="__RequestVerificationToken"]'); fd.append('__RequestVerificationToken', tk ? tk.value : '');
        var btn = $('rqSend'), h = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> পাঠানো হচ্ছে...';
        try {
            var d = await (await fetch('/FindTutor/SubmitTutorRequest', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            if (!d.success) { btn.disabled = false; btn.innerHTML = h; return fail(d.message || 'পাঠানো যায়নি।', null, d.needPackage); }
            $('rqBody').innerHTML = '<div class="rq-ok"><div class="ic"><i class="fas fa-check"></i></div><h4>রিকোয়েস্ট পাঠানো হয়েছে!</h4>'
                + '<p><b></b> নোটিফিকেশন পেয়েছেন — Accept করলে Messages এ দুজনের চ্যাট খুলে যাবে।</p>'
                + (d.requestId ? '<p>Request ID: <code></code></p>' : '') + '<button type="button" class="rq-send" data-rq-close style="max-width:200px;margin:8px auto 0;">OK</button></div>';
            $('rqBody').querySelector('p b').textContent = <?= js((string)$name) ?>;
            if (d.requestId) $('rqBody').querySelector('code').textContent = d.requestId;
        } catch (x) { btn.disabled = false; btn.innerHTML = h; fail('ইন্টারনেট সমস্যা — আবার চেষ্টা করুন।'); }
    });
})();
</script>
