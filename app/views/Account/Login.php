<?php
$ViewData['Title'] = 'Login';
$returnUrl = (string)($ViewBag->ReturnUrl ?? '');
?>
<style>
    .card-transparent {
        background-color: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.35);
    }

        .card-transparent .card-body {
            background-color: transparent;
        }

    .form-control-transparent {
        background-color: rgba(255, 255, 255, 0.15) !important;
        border: 1px solid rgba(25, 135, 84, 0.4);
        color: #1c1c1c;
        transition: all 0.2s ease;
    }

        .form-control-transparent::placeholder {
            color: rgba(0, 0, 0, 0.45);
        }

        .form-control-transparent:focus {
            background-color: rgba(255, 255, 255, 0.3) !important;
            border-color: #02B8F5;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
            color: #1c1c1c;
        }

        /* Stop Chrome/Edge from painting autofilled fields solid white/blue */
        .form-control-transparent:-webkit-autofill,
        .form-control-transparent:-webkit-autofill:hover,
        .form-control-transparent:-webkit-autofill:focus {
            -webkit-text-fill-color: #1c1c1c;
            transition: background-color 9999s ease-in-out 0s;
            box-shadow: 0 0 0 1000px rgba(255, 255, 255, 0.15) inset;
        }

        .form-control-transparent.is-invalid {
            border-color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

    .invalid-feedback {
        display: none;
        font-size: 0.8rem;
        margin-top: 4px;
    }

    .form-control-transparent.is-invalid ~ .invalid-feedback {
        display: block;
    }

    /* ===== Suspension notice ===== */
    .susp-box { border: 1px solid #F5B7B1; background: #FFF5F4; border-radius: 14px; padding: 14px; margin-bottom: 16px; color: #5B1A14; }
    .susp-head { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
    .susp-ic { width: 40px; height: 40px; border-radius: 50%; background: #e74c3c; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .susp-head strong { display: block; font-size: 14.5px; color: #8B1E15; }
    .susp-head small { font-size: 12px; color: #A0443B; }
    .susp-row { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; padding: 7px 0; border-top: 1px dashed #F5C6C0; }
    .susp-row span { color: #A0443B; white-space: nowrap; }
    .susp-row b { text-align: right; color: #5B1A14; }
    .susp-left { font-variant-numeric: tabular-nums; }
    .susp-reason { margin-top: 6px; padding: 10px 12px; border-radius: 10px; background: #fff; border: 1px solid #F5C6C0; }
    .susp-reason span { font-size: 12px; font-weight: 700; color: #A0443B; }
    .susp-reason p { margin: 4px 0 0; font-size: 13.5px; white-space: pre-wrap; color: #333; }
    .susp-help { margin: 10px 0 0; font-size: 12px; color: #A0443B; }
    .forgot-link { font-size: 13px; font-weight: 600; color: #028EA5; text-decoration: none; margin-bottom: 8px; }
    .forgot-link:hover { text-decoration: underline; color: #08224A; }
</style>

<div class="container-fluid py-5" style="background-image: url('https://i.ibb.co.com/93m2hpJS/Chat-GPT-Image-Sep-26-2026-06-11-52-PM.png'); background-size: cover; background-position: center; min-height: 80vh; display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-4">
            <div class="card shadow card-transparent">
                <div class="card-header text-white text-center" style="background-color:#134856;">
                    <h4>Login</h4>
                </div>
                <div class="card-body">
                    <?php $susp = is_array($ViewBag->Suspension) ? $ViewBag->Suspension : null; ?>
                    <?php if ($susp): ?>
                        <!-- ✅ অ্যাকাউন্ট Suspend — কারণ ও সময় -->
                        <div class="susp-box" role="alert">
                            <div class="susp-head">
                                <span class="susp-ic"><i class="fas fa-user-slash"></i></span>
                                <div>
                                    <strong>আপনার অ্যাকাউন্ট Suspend করা হয়েছে</strong>
                                    <small><?= $susp['permanent'] ? 'স্থায়ীভাবে' : 'সাময়িকভাবে' ?> — এই সময়ে লগইন করা যাবে না</small>
                                </div>
                            </div>

                            <div class="susp-row">
                                <span><i class="far fa-clock"></i> সময়সীমা</span>
                                <?php if ($susp['permanent']): ?>
                                    <b>পারমানেন্ট (স্থায়ী)</b>
                                <?php else: ?>
                                    <b><?= e($susp['untilText']) ?> পর্যন্ত</b>
                                <?php endif; ?>
                            </div>
                            <?php if (!$susp['permanent'] && $susp['untilIso']): ?>
                                <div class="susp-row">
                                    <span><i class="fas fa-hourglass-half"></i> বাকি আছে</span>
                                    <b class="susp-left" id="suspLeft" data-until="<?= e($susp['untilIso']) ?>">—</b>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($susp['since'])): ?>
                                <div class="susp-row">
                                    <span><i class="far fa-calendar"></i> Suspend হয়েছে</span>
                                    <b><?= e($susp['since']) ?></b>
                                </div>
                            <?php endif; ?>

                            <div class="susp-reason">
                                <span><i class="fas fa-comment-dots"></i> কারণ</span>
                                <p><?= $susp['reason'] !== '' ? e($susp['reason']) : 'অ্যাডমিন কোনো কারণ উল্লেখ করেননি।' ?></p>
                            </div>
                            <p class="susp-help">ভুল মনে হলে সাইটের অ্যাডমিনের সাথে যোগাযোগ করুন।</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($ViewBag->Error !== null): ?>
                        <div class="alert alert-danger"><?= e($ViewBag->Error) ?></div>
                    <?php endif; ?>
                    <?php if ($TempData['Success'] !== null): ?>
                        <div class="alert alert-success"><?= e($TempData['Success']) ?></div>
                    <?php endif; ?>
                    <?php if ($TempData['Error'] !== null): ?>
                        <div class="alert alert-danger"><?= e($TempData['Error']) ?></div>
                    <?php endif; ?>

                    <?php if ($returnUrl !== ''): ?>
                        <div class="alert alert-info" style="font-size: 13.5px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Please login to continue. You'll be redirected back after login.
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/Account/Login" id="loginForm" novalidate>
                        <?= csrf_field() ?>

                        <!-- ✅ returnUrl hidden field for post-login redirect -->
                        <input type="hidden" name="returnUrl" value="<?= e($returnUrl) ?>" />

                        <div class="mb-3">
                            <label class="form-label">Username / Email</label>
                            <input name="username" id="loginUsername" type="text" class="form-control form-control-transparent" autocomplete="username" placeholder="Username অথবা Email" required />
                            <div class="invalid-feedback">Username অথবা Email দিন।</div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Password</label>
                                <a href="/Account/ForgotPassword" class="forgot-link">Forgot password?</a>
                            </div>
                            <input name="password" id="loginPassword" type="password" class="form-control form-control-transparent" autocomplete="current-password" required />
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom text-white w-100">Login</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/Account/Register">Create new account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const form = document.getElementById('loginForm');
        const username = document.getElementById('loginUsername');
        const password = document.getElementById('loginPassword');

        function markField(input) {
            const valid = input.value.trim().length > 0;
            input.classList.toggle('is-invalid', !valid);
            return valid;
        }

        [username, password].forEach(function (el) {
            el.addEventListener('input', function () { markField(el); });
        });

        form.addEventListener('submit', function (e) {
            const validUsername = markField(username);
            const validPassword = markField(password);
            if (!(validUsername && validPassword)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    })();
</script>

<script>
    // ✅ Suspend এর বাকি সময় লাইভ দেখানো
    (function () {
        var el = document.getElementById('suspLeft');
        if (!el) return;
        var until = new Date(el.getAttribute('data-until')).getTime();
        function tick() {
            var ms = until - Date.now();
            if (ms <= 0) { el.textContent = 'শেষ — এখন লগইন করতে পারবেন'; el.style.color = '#155724'; return; }
            var s = Math.floor(ms / 1000), d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60), sec = s % 60;
            el.textContent = (d ? d + ' দিন ' : '') + (d || h ? h + ' ঘণ্টা ' : '') + m + ' মিনিট ' + sec + ' সেকেন্ড';
            setTimeout(tick, 1000);
        }
        tick();
    })();
</script>
