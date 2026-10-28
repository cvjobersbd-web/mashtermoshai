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
</style>

<div class="container-fluid py-5" style="background-image: url('https://i.ibb.co.com/93m2hpJS/Chat-GPT-Image-Sep-26-2026-06-11-52-PM.png'); background-size: cover; background-position: center; min-height: 80vh; display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-4">
            <div class="card shadow card-transparent">
                <div class="card-header text-white text-center" style="background-color:#134856;">
                    <h4>Login</h4>
                </div>
                <div class="card-body">
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
                            <label class="form-label">Username</label>
                            <input name="username" id="loginUsername" type="text" class="form-control form-control-transparent" required />
                            <div class="invalid-feedback">Please enter your username.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input name="password" id="loginPassword" type="password" class="form-control form-control-transparent" required />
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