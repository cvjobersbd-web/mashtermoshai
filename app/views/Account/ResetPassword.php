<?php
$ViewData['Title'] = 'Reset Password';
$valid = (bool)($ViewBag->Valid ?? false);
?>
<?php partial('Account/_AuthStyles'); ?>

<div class="auth-wrap">
    <div class="auth-card">
        <?php if (!$valid): ?>
            <div class="auth-head">
                <div class="auth-ico bad"><i class="fas fa-link-slash"></i></div>
                <h4>লিংকটা আর কাজ করছে না</h4>
                <p>লিংকের মেয়াদ (৩০ মিনিট) শেষ হয়ে গেছে, অথবা এটা আগেই ব্যবহার করা হয়েছে।</p>
            </div>
            <div class="auth-body">
                <a href="/Account/ForgotPassword" class="auth-btn" style="text-decoration:none;"><i class="fas fa-redo"></i> নতুন লিংক চান</a>
                <div class="auth-foot"><a href="/Account/Login"><i class="fas fa-arrow-left"></i> লগইনে ফিরে যান</a></div>
            </div>
        <?php else: ?>
            <div class="auth-head">
                <div class="auth-ico"><i class="fas fa-lock"></i></div>
                <h4>নতুন পাসওয়ার্ড সেট করুন</h4>
                <p>অ্যাকাউন্ট: <b><?= e($ViewBag->UserName) ?></b></p>
            </div>
            <div class="auth-body">
                <?php if ($ViewBag->Error !== null): ?>
                    <div class="auth-alert err"><i class="fas fa-exclamation-circle"></i><div><?= e($ViewBag->Error) ?></div></div>
                <?php endif; ?>

                <form method="post" action="/Account/ResetPassword" id="rpForm" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($ViewBag->Token) ?>" />

                    <label class="auth-label" for="rpPass">নতুন পাসওয়ার্ড</label>
                    <div class="auth-input">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="rpPass" minlength="6" maxlength="128" autocomplete="new-password" placeholder="অন্তত ৬ অক্ষর" required />
                        <button type="button" class="auth-eye" onclick="rpToggle()" aria-label="Show password"><i class="fas fa-eye" id="rpEye"></i></button>
                    </div>
                    <div class="auth-meter"><span id="rpBar"></span></div>

                    <label class="auth-label" for="rpConfirm">আবার লিখুন</label>
                    <div class="auth-input">
                        <i class="fas fa-check-double"></i>
                        <input type="password" name="confirmPassword" id="rpConfirm" maxlength="128" autocomplete="new-password" placeholder="একই পাসওয়ার্ড আবার" required />
                    </div>
                    <div class="auth-hint" id="rpHint"></div>

                    <button type="submit" class="auth-btn" id="rpBtn"><i class="fas fa-save"></i> পাসওয়ার্ড সেভ করুন</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($valid): ?>
<script>
    (function () {
        var p = document.getElementById('rpPass'), c = document.getElementById('rpConfirm'), bar = document.getElementById('rpBar'), hint = document.getElementById('rpHint');
        function score(v) { var s = 0; if (v.length >= 6) s++; if (v.length >= 10) s++; if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++; return s; }
        function upd() {
            var sc = score(p.value), cols = ['#e74c3c', '#e74c3c', '#f39c12', '#f1c40f', '#2ecc71', '#16a34a'];
            bar.style.width = (p.value ? Math.max(15, sc * 20) : 0) + '%'; bar.style.background = cols[sc];
            if (c.value && c.value !== p.value) { hint.textContent = 'পাসওয়ার্ড মেলেনি'; hint.className = 'auth-hint bad'; }
            else if (c.value && c.value === p.value) { hint.textContent = '✓ মিলেছে'; hint.className = 'auth-hint ok'; }
            else { hint.textContent = ''; }
        }
        p.addEventListener('input', upd); c.addEventListener('input', upd);
        document.getElementById('rpForm').addEventListener('submit', function (e) {
            if (p.value.length < 6) { e.preventDefault(); hint.textContent = 'পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে'; hint.className = 'auth-hint bad'; p.focus(); return; }
            if (p.value !== c.value) { e.preventDefault(); hint.textContent = 'পাসওয়ার্ড মেলেনি'; hint.className = 'auth-hint bad'; c.focus(); return; }
            var b = document.getElementById('rpBtn'); b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> সেভ হচ্ছে...';
        });
        window.rpToggle = function () {
            var show = p.type === 'password'; p.type = c.type = show ? 'text' : 'password';
            document.getElementById('rpEye').className = 'fas ' + (show ? 'fa-eye-slash' : 'fa-eye');
        };
    })();
</script>
<?php endif; ?>
