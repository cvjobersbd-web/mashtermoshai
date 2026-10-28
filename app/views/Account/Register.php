<?php
$ViewData['Title'] = 'Register';
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

        .form-control-transparent.is-valid {
            border-color: #02B8F5 !important;
        }

    .invalid-feedback {
        display: none;
        font-size: 0.8rem;
        margin-top: 4px;
    }

    .form-control-transparent.is-invalid ~ .invalid-feedback,
    .was-validated .form-control-transparent:invalid ~ .invalid-feedback {
        display: block;
    }

    /* Green theme overrides */
    .card-header.bg-primary {
        background-color: #134856 !important;
    }

    .btn-primary {
        background-color: #02B8F5 !important;
        border-color: #02B8F5 !important;
    }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #0099D6 !important;
            border-color: #0099D6 !important;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
        }

    a {
        color: #028EA5;
    }

        a:hover {
            color: #0099D6;
        }
</style>

<div class="container-fluid py-5" style="background-image: url('https://i.ibb.co.com/93m2hpJS/Chat-GPT-Image-Sep-26-2026-06-11-52-PM.png'); background-size: cover; background-position: center; min-height: 80vh; display: flex; align-items: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-4">
            <div class="card shadow card-transparent">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Register</h4>
                </div>
                <div class="card-body">
                    <?php if ($ViewBag->Error !== null): ?>
                        <div class="alert alert-danger"><?= e($ViewBag->Error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="/Account/Register" id="registerForm" novalidate autocomplete="off">
                        <?= csrf_field() ?>

                        <!-- Decoy fields: absorb the browser's saved-credential autofill so it doesn't fill the real fields below -->
                        <input type="text" name="fakeUsernameRemembered" autocomplete="off" style="position:absolute; opacity:0; height:0; width:0; pointer-events:none;" tabindex="-1" />
                        <input type="password" name="fakePasswordRemembered" autocomplete="new-password" style="position:absolute; opacity:0; height:0; width:0; pointer-events:none;" tabindex="-1" />

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input name="fullName" id="fullName" type="text" class="form-control form-control-transparent"
                                   placeholder="e.g. Rahim Ahmed" autocomplete="off" required />
                            <div class="invalid-feedback">Please enter your real full name (first and last name).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input name="username" id="username" type="text" class="form-control form-control-transparent"
                                   placeholder="e.g. rahim_23" autocomplete="off" required />
                            <div class="invalid-feedback">Username must be at least 3 characters (letters, numbers, underscore).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input name="email" id="email" type="email" class="form-control form-control-transparent"
                                   placeholder="e.g. yourname@gmail.com" autocomplete="off" required />
                            <div class="invalid-feedback">Only a valid Gmail address is allowed (e.g. yourname@gmail.com).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input name="password" id="password" type="password" class="form-control form-control-transparent"
                                   autocomplete="new-password" required />
                            <small class="text-muted d-block">Minimum 6 characters.</small>
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input name="confirmPassword" id="confirmPassword" type="password" class="form-control form-control-transparent"
                                   autocomplete="new-password" required />
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>

                        <div id="formError" class="alert alert-danger py-2" style="display:none;"></div>

                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="/Account/Login">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('registerForm');
        const fullName = document.getElementById('fullName');
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const formError = document.getElementById('formError');

        const usernameRegex = /^[A-Za-z0-9_]{3,20}$/;

    
        // Gmail username rules: 6-30 chars, letters/numbers/dots only,
        // must start & end with a letter or number, no consecutive dots.
        function isValidEmail(value) {
            const gmailRegex = /^(?=.{6,30}@)[a-z0-9]+(?:\.[a-z0-9]+)*@gmail\.com$/i;
            return gmailRegex.test(value);
        }

        function setState(input, valid, message) {
            const feedback = input.parentElement.querySelector('.invalid-feedback');
            if (valid) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (message && feedback) feedback.textContent = message;
            }
        }

        function isGibberishWord(word) {
            // Only sanity-check pure Latin-alphabet words; leave Bengali/other scripts alone.
            if (!/^[A-Za-z]+$/.test(word)) return false;

            // Short abbreviations like "Md", "Al" are common in real names — allow them.
            if (word.length <= 2) return false;

            const hasVowel = /[aeiouAEIOU]/.test(word);
            if (!hasVowel) return true; // e.g. "dddf", "fgfgd" — no vowel at all

            const longConsonantRun = /[^aeiouAEIOU]{4,}/.test(word);
            if (longConsonantRun) return true; // e.g. "fgdfgdf" — unrealistic consonant cluster

            return false;
        }

        function validateFullName() {
            const value = fullName.value.trim();
            const words = value.split(/\s+/).filter(Boolean);

            const validChars = words.every(function(w) { return /^[A-Za-z\u0980-\u09FF.]+$/.test(w); });
            const enoughWords = words.length >= 2;
            const notGibberish = words.every(function(w) { return !isGibberishWord(w); });

            const ok = validChars && enoughWords && notGibberish;
            setState(fullName, ok);
            return ok;
        }

        function validateUsername() {
            const ok = usernameRegex.test(username.value.trim());
            setState(username, ok);
            return ok;
        }

        function validateEmail() {
            const ok = isValidEmail(email.value.trim());
            setState(email, ok);
            return ok;
        }

        function validatePassword() {
            const ok = password.value.length >= 6;
            setState(password, ok);
            return ok;
        }

        function validateConfirmPassword() {
            const ok = confirmPassword.value.length > 0 && confirmPassword.value === password.value;
            setState(confirmPassword, ok);
            return ok;
        }

        [fullName, username, email, password, confirmPassword].forEach(function(el) {
            el.addEventListener('input', function() {
                formError.style.display = 'none';
                if (el === fullName) validateFullName();
                if (el === username) validateUsername();
                if (el === email) validateEmail();
                if (el === password) { validatePassword(); validateConfirmPassword(); }
                if (el === confirmPassword) validateConfirmPassword();
            });
        });

        form.addEventListener('submit', function(e) {
            const validName = validateFullName();
            const validUsername = validateUsername();
            const validEmail = validateEmail();
            const validPassword = validatePassword();
            const validConfirm = validateConfirmPassword();

            if (!(validName && validUsername && validEmail && validPassword && validConfirm)) {
                e.preventDefault();
                e.stopPropagation();
                formError.textContent = 'Please fix the highlighted fields before creating an account.';
                formError.style.display = 'block';
            }

            form.classList.add('was-validated');
        });
    })();
</script>