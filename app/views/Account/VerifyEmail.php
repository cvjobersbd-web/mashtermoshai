<?php
$ViewData['Title'] = 'Verify Email';
$wait = (int)($ViewBag->Wait ?? 0);
?>
<?php partial('Account/_AuthStyles'); ?>
<style>
    .otp-boxes { display: flex; justify-content: center; gap: 8px; margin: 6px 0 4px; }
    .otp-boxes input { width: 48px; height: 56px; text-align: center; font-size: 24px; font-weight: 700; color: #08224A;
        border: 1.5px solid #DCECF5; border-radius: 12px; background: #F5FBFF; outline: none; transition: border-color .2s, background .2s, transform .15s; }
    .otp-boxes input:focus { border-color: #00CFFF; background: #fff; transform: translateY(-1px); }
    .otp-boxes.bad input { border-color: #e74c3c; }
    .otp-mail { display: inline-block; margin-top: 6px; font-weight: 700; color: #08224A; word-break: break-all; }
    .otp-resend { text-align: center; margin-top: 16px; font-size: 14px; color: #64748B; }
    .otp-resend button { border: none; background: none; color: #028EA5; font-weight: 700; cursor: pointer; padding: 0; }
    .otp-resend button:disabled { color: #94A3B8; cursor: default; }
    .otp-tip { font-size: 12.5px; color: #64748B; text-align: center; margin-top: 10px; }
    @media (max-width: 380px) { .otp-boxes { gap: 5px; } .otp-boxes input { width: 40px; height: 50px; font-size: 21px; } }
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <div class="auth-ico"><i class="fas fa-envelope-circle-check"></i></div>
            <h4>ইমেইল ভেরিফাই করুন</h4>
            <p>আমরা ৬ ডিজিটের একটা কোড পাঠিয়েছি<br><span class="otp-mail"><?= e($ViewBag->Email) ?></span></p>
        </div>
        <div class="auth-body">
            <?php if ($TempData['Success'] !== null): ?>
                <div class="auth-alert ok"><i class="fas fa-paper-plane"></i><div><?= e($TempData['Success']) ?></div></div>
            <?php endif; ?>
            <?php $err = $ViewBag->Error ?? $TempData['Error']; if ($err !== null): ?>
                <div class="auth-alert err"><i class="fas fa-exclamation-circle"></i><div><?= e($err) ?></div></div>
            <?php endif; ?>

            <form method="post" action="/Account/VerifyEmail" id="otpForm" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="code" id="otpCode" />
                <label class="auth-label" style="text-align:center">ভেরিফিকেশন কোড</label>
                <div class="otp-boxes" id="otpBoxes">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <input type="text" inputmode="numeric" maxlength="1" <?= $i === 0 ? 'autocomplete="one-time-code" autofocus' : 'autocomplete="off"' ?> aria-label="Digit <?= $i + 1 ?>" />
                    <?php endfor; ?>
                </div>
                <div class="otp-tip">কোডটা <?= (int)$ViewBag->Minutes ?> মিনিট কাজ করবে। Inbox এ না পেলে Spam / Promotions ফোল্ডার দেখুন।</div>
                <button type="submit" class="auth-btn" id="otpBtn"><i class="fas fa-shield-halved"></i> ভেরিফাই করুন</button>
            </form>

            <form method="post" action="/Account/ResendOtp" class="otp-resend" id="resendForm">
                <?= csrf_field() ?>
                কোড পাননি? <button type="submit" id="resendBtn" <?= $wait > 0 ? 'disabled' : '' ?>>আবার কোড পাঠান</button>
                <span id="resendWait"><?= $wait > 0 ? '(' . $wait . 's)' : '' ?></span>
            </form>

            <div class="auth-foot">
                <a href="/Account/Login"><i class="fas fa-arrow-left"></i> লগইনে ফিরে যান</a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var boxes = [].slice.call(document.querySelectorAll('#otpBoxes input'));
    var bn = { '০':'0','১':'1','২':'2','৩':'3','৪':'4','৫':'5','৬':'6','৭':'7','৮':'8','৯':'9' };
    function digits(v) { return String(v || '').replace(/[০-৯]/g, function (c) { return bn[c]; }).replace(/\D/g, ''); }
    function fill(from, str) {
        var d = digits(str).split('');
        for (var i = from; i < boxes.length && d.length; i++) boxes[i].value = d.shift();
        var next = boxes.find(function (b) { return !b.value; }) || boxes[boxes.length - 1];
        next.focus();
        if (boxes.every(function (b) { return b.value; })) submit();
    }
    function submit() {
        document.getElementById('otpCode').value = boxes.map(function (b) { return b.value; }).join('');
        var btn = document.getElementById('otpBtn');
        if (btn.disabled) return;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> যাচাই হচ্ছে...';
        document.getElementById('otpForm').submit();
    }
    boxes.forEach(function (b, i) {
        b.addEventListener('input', function () {
            var v = digits(b.value);
            if (v.length > 1) { b.value = ''; fill(i, v); return; }
            b.value = v;
            document.getElementById('otpBoxes').classList.remove('bad');
            if (v && i < boxes.length - 1) boxes[i + 1].focus();
            if (boxes.every(function (x) { return x.value; })) submit();
        });
        b.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !b.value && i > 0) { boxes[i - 1].value = ''; boxes[i - 1].focus(); e.preventDefault(); }
            if (e.key === 'ArrowLeft' && i > 0) boxes[i - 1].focus();
            if (e.key === 'ArrowRight' && i < boxes.length - 1) boxes[i + 1].focus();
        });
        b.addEventListener('paste', function (e) { e.preventDefault(); fill(i, (e.clipboardData || window.clipboardData).getData('text')); });
        b.addEventListener('focus', function () { b.select(); });
    });
    document.getElementById('otpForm').addEventListener('submit', function (e) {
        var code = boxes.map(function (b) { return b.value; }).join('');
        if (code.length !== 6) { e.preventDefault(); document.getElementById('otpBoxes').classList.add('bad'); (boxes.find(function (b) { return !b.value; }) || boxes[0]).focus(); return; }
        document.getElementById('otpCode').value = code;
    });

    var wait = <?= $wait ?>, rb = document.getElementById('resendBtn'), rw = document.getElementById('resendWait');
    if (wait > 0) {
        var t = setInterval(function () {
            wait--;
            if (wait <= 0) { clearInterval(t); rb.disabled = false; rw.textContent = ''; }
            else rw.textContent = '(' + wait + 's)';
        }, 1000);
    }
    document.getElementById('resendForm').addEventListener('submit', function () { rb.disabled = true; rb.textContent = 'পাঠানো হচ্ছে...'; });
})();
</script>
