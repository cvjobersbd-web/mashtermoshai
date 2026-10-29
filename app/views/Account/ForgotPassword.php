<?php
$ViewData['Title'] = 'Forgot Password';
$value = (string)($ViewBag->Value ?? '');
?>
<?php partial('Account/_AuthStyles'); ?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <div class="auth-ico"><i class="fas fa-key"></i></div>
            <h4>পাসওয়ার্ড ভুলে গেছেন?</h4>
            <p>রেজিস্টার করা ইমেইল অথবা Username দিন — আমরা ইমেইলে একটা লিংক পাঠাব, সেখান থেকে নতুন পাসওয়ার্ড সেট করবেন।</p>
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
                <label class="auth-label" for="fpEmail">ইমেইল অথবা Username</label>
                <div class="auth-input">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="email" id="fpEmail" value="<?= e($value) ?>" maxlength="200" autocomplete="email" placeholder="yourname@gmail.com" required />
                </div>
                <button type="submit" class="auth-btn" id="fpBtn"><i class="fas fa-paper-plane"></i> রিসেট লিংক পাঠান</button>
            </form>

            <div class="auth-foot">
                <a href="/Account/Login"><i class="fas fa-arrow-left"></i> লগইনে ফিরে যান</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('fpForm').addEventListener('submit', function (e) {
        var v = document.getElementById('fpEmail');
        if (!v.value.trim()) { e.preventDefault(); v.closest('.auth-input').classList.add('bad'); v.focus(); return; }
        var b = document.getElementById('fpBtn'); b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> পাঠানো হচ্ছে...';
    });
</script>
