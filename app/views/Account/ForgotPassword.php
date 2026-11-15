<?php
$ViewData['Title'] = t('পাসওয়ার্ড ভুলে গেছেন', 'Forgot Password');
$value = (string)($ViewBag->Value ?? '');
?>
<?php partial('Account/_AuthStyles'); ?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <div class="auth-ico"><i class="fas fa-key"></i></div>
            <h4><?= t('পাসওয়ার্ড ভুলে গেছেন?', 'Forgot your password?') ?></h4>
            <p><?= t('রেজিস্টার করা ইমেইল অথবা Username দিন — আমরা ইমেইলে একটা লিংক পাঠাব, সেখান থেকে নতুন পাসওয়ার্ড সেট করবেন।', 'Enter your registered email or username — we will email you a link to set a new password.') ?></p>
        </div>
        <div class="auth-body">
            <?php if ($TempData['Success'] !== null): ?>
                <div class="auth-alert ok"><i class="fas fa-envelope-open-text"></i><div><?= e($TempData['Success']) ?></div></div>
            <?php endif; ?>
            <?php if ($ViewBag->Error !== null): ?>
                <div class="auth-alert err"><i class="fas fa-exclamation-circle"></i><div><?= e($ViewBag->Error) ?></div></div>
            <?php endif; ?>

            <form method="post" action="/Account/ForgotPassword" id="fpForm" novalidate>
                <?= csrf_field() ?>
                <label class="auth-label" for="fpEmail"><?= t('ইমেইল অথবা Username', 'Email or Username') ?></label>
                <div class="auth-input">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="email" id="fpEmail" value="<?= e($value) ?>" maxlength="200" autocomplete="email" placeholder="yourname@gmail.com" required />
                </div>
                <button type="submit" class="auth-btn" id="fpBtn"><i class="fas fa-paper-plane"></i> <?= t('রিসেট লিংক পাঠান', 'Send reset link') ?></button>
            </form>

            <div class="auth-foot">
                <a href="/Account/Login"><i class="fas fa-arrow-left"></i> <?= t('লগইনে ফিরে যান', 'Back to login') ?></a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('fpForm').addEventListener('submit', function (e) {
        var v = document.getElementById('fpEmail');
        if (!v.value.trim()) { e.preventDefault(); v.closest('.auth-input').classList.add('bad'); v.focus(); return; }
        var b = document.getElementById('fpBtn'); b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= t('পাঠানো হচ্ছে...', 'Sending...') ?>';
    });
</script>
