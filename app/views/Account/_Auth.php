<?php
/**
 * লগইন + সাইনআপ — একই পেজে দুইটা ফর্ম, অ্যানিমেশন সহ।
 *  লগইন: বাঁয়ে কার্টুন ছবি, ডানে ফর্ম।  সাইনআপ: ছবি ডানে স্মুথলি সরে যায়, ফর্ম বদলে বাঁয়ে আসে।
 *  JS না চললেও দুইটা লিংক (/Account/Login, /Account/Register) সাধারণভাবে কাজ করে।
 * @var string $mode 'login' | 'register'
 */
$mode = ($mode ?? 'login') === 'register' ? 'register' : 'login';
$returnUrl = (string)($ViewBag->ReturnUrl ?? '');
$susp = is_array($ViewBag->Suspension ?? null) ? $ViewBag->Suspension : null;
$err = $ViewBag->Error ?? null;
$authImg = 'https://i.ibb.co.com/tMC8kk2L/Teacher-and-Student-Study-Session.png';
?>
<style>
    .au { --au-blue: #1677E5; --au-cyan: #00CFFF; --au-ink: #0B1F44; --au-dur: .75s; --au-ease: cubic-bezier(.65,.05,.25,1);
        position: relative; overflow: hidden; min-height: calc(100vh - 80px); display: flex; align-items: center;
        background: radial-gradient(1200px 500px at -10% 110%, #CFE6FB 0, transparent 60%), radial-gradient(900px 420px at 110% -10%, #D7ECFD 0, transparent 60%), linear-gradient(180deg, #F4F9FE, #EAF4FD);
        padding: 36px 0 60px; }
    .au::before, .au::after { content: ''; position: absolute; width: 120px; height: 66px; background-image: radial-gradient(#BCD6EE 2.2px, transparent 2.6px); background-size: 22px 22px; pointer-events: none; }
    .au::before { top: 40px; left: 3vw; } .au::after { bottom: 34px; right: 5vw; }
    .au-wave { position: absolute; left: 0; right: 0; bottom: -2px; height: 130px; pointer-events: none; }
    .au-stage { position: relative; width: min(1320px, 100% - 32px); margin: 0 auto; min-height: 640px; }
    .au-art, .au-panel { position: absolute; top: 0; bottom: 0; width: 50%; transition: transform var(--au-dur) var(--au-ease); will-change: transform; }
    .au-art { left: 0; z-index: 1; display: flex; flex-direction: column; justify-content: center; padding: 10px 40px 10px 20px; }
    .au-panel { left: 50%; z-index: 2; display: flex; align-items: center; justify-content: center; padding: 10px 10px 10px 40px; }
    .au[data-mode="register"] .au-art { transform: translateX(100%); }
    .au[data-mode="register"] .au-panel { transform: translateX(-100%); }
    .au[data-mode="register"] .au-art { padding: 10px 20px 10px 40px; }
    .au[data-mode="register"] .au-panel { padding: 10px 40px 10px 10px; }
    /* মাঝের হালকা দাগ */
    .au-stage::after { content: ''; position: absolute; left: 50%; top: 12%; bottom: 12%; width: 1px; background: linear-gradient(180deg, transparent, #D3E3F3, transparent); z-index: 0; transition: opacity .3s; }
    .au.moving .au-stage::after { opacity: 0; }
    .au-pic { width: 100%; max-width: 560px; margin: 0 auto; display: block; mix-blend-mode: multiply; animation: auFloat 6s ease-in-out infinite; }
    @keyframes auFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    .au-copy { max-width: 560px; margin: 8px auto 0; width: 100%; transition: text-align .3s; }
    .au-copy h2 { margin: 0; font-family: var(--font-heading); font-weight: 600; color: var(--au-ink); font-size: clamp(30px, 2.6vw, 44px); line-height: 1.1; }
    .au-copy h2 b { display: block; font-weight: 800; color: var(--au-blue); font-size: 1.18em; letter-spacing: -.5px; margin-top: 4px; }
    .au-copy p { margin: 14px 0 0; color: #475569; font-size: 16.5px; line-height: 1.55; max-width: 520px; }
    /* তিনটা আইকন সমান চওড়া কলামে — আইকন আর লেখা একই লাইনে */
    .au-feats { display: grid; grid-template-columns: repeat(3, 150px); gap: 18px; margin-top: 24px; justify-content: start; }
    .au-feat { display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; font-weight: 600; font-size: 14.5px; color: var(--au-ink); line-height: 1.25; white-space: nowrap; }
    .au-feat i { width: 60px; height: 60px; border-radius: 14px; background: #fff; color: var(--au-blue); font-size: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(22,119,229,.12); }
    .au[data-mode="register"] .au-copy { text-align: center; }
    .au[data-mode="register"] .au-copy p { margin-left: auto; margin-right: auto; }
    .au[data-mode="register"] .au-feats { justify-content: center; }
    @media (max-width: 1200px) { .au-feats { grid-template-columns: repeat(3, 128px); gap: 12px; } .au-feat { font-size: 13.5px; } }
    /* ===== ফর্ম কার্ড ===== */
    .au-card { position: relative; width: 100%; max-width: 600px; background: #fff; border: 1px solid #E3ECF6; border-radius: 26px; box-shadow: 0 24px 60px rgba(15,52,103,.10);
        padding: 40px 48px 34px; overflow: hidden; transition: height .55s var(--au-ease); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
    .au-form { transition: opacity .45s ease, transform .55s var(--au-ease), visibility .45s; }
    .au-form.off { position: absolute; left: 48px; right: 48px; top: 40px; opacity: 0; visibility: hidden; pointer-events: none; }
    .au-form.au-login.off { transform: translateX(40px); } .au-form.au-reg.off { transform: translateX(-40px); }
    .au-card h1 { margin: 0; font-family: var(--font-heading); font-weight: 700; color: var(--au-ink); font-size: clamp(26px, 2.2vw, 34px); letter-spacing: -.3px; }
    .au-card .lead { margin: 8px 0 22px; color: #475569; font-size: 15.5px; line-height: 1.5; }
    .au-f { margin-bottom: 16px; }
    .au-f label { display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 14.5px; color: var(--au-ink); margin-bottom: 7px; }
    .au-f label a { font-weight: 600; font-size: 13.5px; color: var(--au-blue); text-decoration: none; }
    .au-f label a:hover { text-decoration: underline; }
    .au-in { position: relative; display: flex; align-items: center; }
    .au-in > i { position: absolute; left: 16px; color: #334155; font-size: 16px; pointer-events: none; }
    .au-in input { width: 100%; height: 50px; border: 1.5px solid #D7E2EE; border-radius: 12px; padding: 0 46px 0 46px; font-size: 15px; font-family: inherit; color: var(--au-ink); background: #fff; outline: none; transition: border-color .2s, box-shadow .2s; }
    .au-in select { width: 100%; height: 50px; border: 1.5px solid #D7E2EE; border-radius: 12px; padding: 0 40px 0 46px; font-size: 15px; font-family: inherit; color: var(--au-ink); background: #fff; outline: none; appearance: auto; cursor: pointer; }
    .au-in select:focus { border-color: var(--au-blue); box-shadow: 0 0 0 4px rgba(22,119,229,.10); }
    .au-in select.is-invalid, .au-in input.is-invalid { border-color: #E5484D; }
    .au-in select.is-invalid ~ .au-fb, .au-f.err .au-fb { display: block; }
    [hidden].au-f { display: none !important; }
    .au-in input::placeholder { color: #7B8798; }
    .au-in input:focus { border-color: var(--au-blue); box-shadow: 0 0 0 4px rgba(22,119,229,.10); }
    .au-in input.is-invalid { border-color: #E5484D; box-shadow: 0 0 0 4px rgba(229,72,77,.08); }
    .au-in input.is-valid { border-color: #22A35A; }
    .au-eye { position: absolute; right: 8px; width: 36px; height: 36px; border: none; background: none; color: #64748B; cursor: pointer; border-radius: 8px; }
    .au-eye:hover { background: #F1F5F9; }
    .au-fb { display: none; color: #DC2626; font-size: 12.5px; margin-top: 5px; }
    .au-in input.is-invalid ~ .au-fb, .au-f .is-invalid + .au-fb { display: block; }
    .au-f.err .au-fb { display: block; }
    .au-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .au-hint { font-size: 12.5px; color: #64748B; margin: -8px 0 16px; }
    .au-btn { width: 100%; height: 52px; border: none; border-radius: 12px; background: linear-gradient(135deg, #1677E5, #1E88F0); color: #fff; font-weight: 700; font-size: 17px; cursor: pointer; font-family: inherit;
        display: inline-flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 24px rgba(22,119,229,.28); transition: transform .15s, box-shadow .2s, background .2s; margin-top: 6px; }
    .au-btn:hover { background: linear-gradient(135deg, #0F63C8, #1677E5); box-shadow: 0 12px 28px rgba(22,119,229,.34); }
    .au-btn:active { transform: translateY(1px); }
    .au-switch { text-align: center; margin: 22px 0 0; color: #475569; font-size: 15px; }
    .au-switch a { color: var(--au-blue); font-weight: 700; text-decoration: none; margin-left: 4px; }
    .au-switch a:hover { text-decoration: underline; }
    .au-alert { border-radius: 12px; padding: 11px 14px; font-size: 14px; margin-bottom: 16px; display: flex; gap: 9px; align-items: flex-start; }
    .au-alert.err { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
    .au-alert.ok { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }
    .au-alert.info { background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; }
    .susp-box { border: 1.5px solid #F5C2C7; background: #FFF5F5; border-radius: 14px; padding: 14px; margin-bottom: 16px; font-size: 14px; color: #5C1D24; }
    .susp-head { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; } .susp-head small { display: block; color: #8A4B53; }
    .susp-ic { width: 38px; height: 38px; border-radius: 50%; background: #E5484D; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .susp-row { display: flex; justify-content: space-between; gap: 10px; padding: 5px 0; border-top: 1px dashed #F1C9CD; }
    .susp-reason span { display: block; font-weight: 700; margin-top: 6px; } .susp-reason p { margin: 3px 0 0; } .susp-help { margin: 8px 0 0; font-size: 12.5px; color: #8A4B53; }
    /* ===== ট্যাবলেট / মোবাইল: ছবি উপরে ছোট, নিচে ফর্ম ===== */
    @media (max-width: 960px) {
        .au { padding: 20px 0 50px; align-items: flex-start; }
        .au-stage { min-height: 0; }
        .au-stage::after { display: none; }
        .au-art, .au-panel, .au[data-mode="register"] .au-art, .au[data-mode="register"] .au-panel { position: relative; width: 100%; left: 0; transform: none; padding: 0; }
        .au-art { flex-direction: row; align-items: center; gap: 16px; max-width: 600px; margin: 0 auto 18px; }
        .au-pic { width: 42%; max-width: 230px; margin: 0; animation: none; }
        .au-copy, .au[data-mode="register"] .au-copy { margin: 0; text-align: left; }
        .au-copy h2 { font-size: 22px; } .au-copy p, .au-feats { display: none; }
        .au-card { padding: 28px 22px 26px; border-radius: 20px; }
        .au-form.off { left: 22px; right: 22px; top: 28px; }
    }
    @media (max-width: 480px) { .au-row { grid-template-columns: 1fr; gap: 0; } .au-pic { width: 38%; } .au-copy h2 { font-size: 19px; } .au-card h1 { font-size: 24px; } }
    @media (prefers-reduced-motion: reduce) { .au-art, .au-panel, .au-form, .au-card { transition: none !important; } .au-pic { animation: none; } }
</style>

<section class="au" id="auth" data-mode="<?= $mode ?>">
    <svg class="au-wave" viewBox="0 0 1440 130" preserveAspectRatio="none" aria-hidden="true"><path d="M0 70 C 240 130 480 10 760 60 S 1240 120 1440 40 L1440 130 L0 130 Z" fill="#DCEBFA" opacity=".7" /><path d="M0 100 C 300 60 560 140 880 96 S 1300 70 1440 104 L1440 130 L0 130 Z" fill="#CFE3F8" opacity=".8" /></svg>
    <div class="au-stage">
        <!-- ===== কার্টুন ছবি ===== -->
        <div class="au-art" aria-hidden="true">
            <img class="au-pic" src="<?= e($authImg) ?>" alt="" />
            <div class="au-copy">
                <h2>Welcome to <b>Mashtermoshai</b></h2>
                <p>Find the right tutor, post your tuition requirement, and manage your learning — all in one place.</p>
                <div class="au-feats">
                    <span class="au-feat"><i class="fas fa-magnifying-glass"></i>Find Tutor</span>
                    <span class="au-feat"><i class="far fa-calendar-check"></i>Post Requirement</span>
                    <span class="au-feat"><i class="fas fa-graduation-cap"></i>Tuition Circular</span>
                </div>
            </div>
        </div>

        <!-- ===== ফর্ম ===== -->
        <div class="au-panel">
            <div class="au-card" id="auCard">
                <!-- লগইন -->
                <div class="au-form au-login<?= $mode === 'login' ? '' : ' off' ?>" data-form="login"<?= $mode === 'login' ? '' : ' aria-hidden="true"' ?>>
                    <h1>Login to Your Account</h1>
                    <p class="lead">Access your dashboard, manage tuitions, and more.</p>
                    <?php if ($susp): ?>
                        <div class="susp-box" role="alert">
                            <div class="susp-head"><span class="susp-ic"><i class="fas fa-user-slash"></i></span><div><strong>আপনার অ্যাকাউন্ট Suspend করা হয়েছে</strong><small><?= $susp['permanent'] ? 'স্থায়ীভাবে' : 'সাময়িকভাবে' ?> — এই সময়ে লগইন করা যাবে না</small></div></div>
                            <div class="susp-row"><span><i class="far fa-clock"></i> সময়সীমা</span><b><?= $susp['permanent'] ? 'পারমানেন্ট (স্থায়ী)' : e($susp['untilText']) . ' পর্যন্ত' ?></b></div>
                            <?php if (!$susp['permanent'] && $susp['untilIso']): ?><div class="susp-row"><span><i class="fas fa-hourglass-half"></i> বাকি আছে</span><b id="suspLeft" data-until="<?= e($susp['untilIso']) ?>">—</b></div><?php endif; ?>
                            <?php if (!empty($susp['since'])): ?><div class="susp-row"><span><i class="far fa-calendar"></i> Suspend হয়েছে</span><b><?= e($susp['since']) ?></b></div><?php endif; ?>
                            <div class="susp-reason"><span><i class="fas fa-comment-dots"></i> কারণ</span><p><?= $susp['reason'] !== '' ? e($susp['reason']) : 'অ্যাডমিন কোনো কারণ উল্লেখ করেননি।' ?></p></div>
                            <p class="susp-help">ভুল মনে হলে সাইটের অ্যাডমিনের সাথে যোগাযোগ করুন।</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($mode === 'login' && $err !== null): ?><div class="au-alert err"><i class="fas fa-circle-exclamation"></i><span><?= e($err) ?></span></div><?php endif; ?>
                    <?php if ($TempData['Success'] !== null): ?><div class="au-alert ok"><i class="fas fa-circle-check"></i><span><?= e($TempData['Success']) ?></span></div><?php endif; ?>
                    <?php if ($TempData['Error'] !== null): ?><div class="au-alert err"><i class="fas fa-circle-exclamation"></i><span><?= e($TempData['Error']) ?></span></div><?php endif; ?>
                    <?php if ($returnUrl !== ''): ?><div class="au-alert info"><i class="fas fa-circle-info"></i><span>চালিয়ে যেতে লগইন করুন — লগইনের পর আগের পেজে ফিরে যাবেন।</span></div><?php endif; ?>

                    <form method="post" action="/Account/Login" id="loginForm" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="returnUrl" value="<?= e($returnUrl) ?>" />
                        <div class="au-f">
                            <label for="loginUsername">Username / Email</label>
                            <div class="au-in"><i class="far fa-envelope"></i><input name="username" id="loginUsername" type="text" autocomplete="username" placeholder="Enter your username or email" required /></div>
                            <div class="au-fb">Username অথবা Email দিন।</div>
                        </div>
                        <div class="au-f">
                            <label for="loginPassword">Password <a href="/Account/ForgotPassword">Forgot password?</a></label>
                            <div class="au-in"><i class="fas fa-lock"></i><input name="password" id="loginPassword" type="password" autocomplete="current-password" placeholder="Enter your password" required /><button type="button" class="au-eye" data-eye aria-label="পাসওয়ার্ড দেখান"><i class="far fa-eye"></i></button></div>
                            <div class="au-fb">Password দিন।</div>
                        </div>
                        <button type="submit" class="au-btn"><i class="fas fa-right-to-bracket"></i> Login</button>
                    </form>
                    <p class="au-switch">Don't have an account?<a href="/Account/Register" data-go="register">Create New Account</a></p>
                </div>

                <!-- সাইনআপ -->
                <div class="au-form au-reg<?= $mode === 'register' ? '' : ' off' ?>" data-form="register"<?= $mode === 'register' ? '' : ' aria-hidden="true"' ?>>
                    <h1>Create Your Account</h1>
                    <p class="lead">Join Mashtermoshai to find tutors, post requirements, and access learning opportunities.</p>
                    <?php if ($mode === 'register' && $err !== null): ?><div class="au-alert err"><i class="fas fa-circle-exclamation"></i><span><?= e($err) ?></span></div><?php endif; ?>
                    <form method="post" action="/Account/Register" id="registerForm" novalidate autocomplete="off">
                        <?= csrf_field() ?>
                        <!-- ব্রাউজারের সেভ করা পাসওয়ার্ড যেন আসল ঘরে না বসে -->
                        <input type="text" name="fakeUsernameRemembered" autocomplete="off" style="position:absolute; opacity:0; height:0; width:0; pointer-events:none;" tabindex="-1" aria-hidden="true" />
                        <input type="password" name="fakePasswordRemembered" autocomplete="new-password" style="position:absolute; opacity:0; height:0; width:0; pointer-events:none;" tabindex="-1" aria-hidden="true" />
                        <div class="au-f">
                            <label for="fullName">Full Name</label>
                            <div class="au-in"><i class="far fa-user"></i><input name="fullName" id="fullName" type="text" placeholder="Enter your full name" autocomplete="off" required /></div>
                            <div class="au-fb">Please enter your real full name (first and last name).</div>
                        </div>
                        <div class="au-f">
                            <label for="username">Username</label>
                            <div class="au-in"><i class="fas fa-at"></i><input name="username" id="username" type="text" placeholder="Choose a username (e.g. rahim_23)" autocomplete="off" required /></div>
                            <div class="au-fb">Username must be 3–20 characters (letters, numbers, underscore).</div>
                        </div>
                        <div class="au-f">
                            <label for="email">Email</label>
                            <div class="au-in"><i class="far fa-envelope"></i><input name="email" id="email" type="email" placeholder="Enter your email address" autocomplete="off" required /></div>
                            <div class="au-fb">Only a valid Gmail address is allowed (e.g. yourname@gmail.com).</div>
                        </div>
                        <div class="au-row">
                            <div class="au-f">
                                <label for="password">Password</label>
                                <div class="au-in"><i class="fas fa-lock"></i><input name="password" id="password" type="password" placeholder="Enter password" autocomplete="new-password" required /><button type="button" class="au-eye" data-eye aria-label="পাসওয়ার্ড দেখান"><i class="far fa-eye"></i></button></div>
                                <div class="au-fb">Password must be at least 6 characters.</div>
                            </div>
                            <div class="au-f">
                                <label for="confirmPassword">Confirm Password</label>
                                <div class="au-in"><i class="fas fa-lock"></i><input name="confirmPassword" id="confirmPassword" type="password" placeholder="Confirm password" autocomplete="new-password" required /><button type="button" class="au-eye" data-eye aria-label="পাসওয়ার্ড দেখান"><i class="far fa-eye"></i></button></div>
                                <div class="au-fb">Passwords do not match.</div>
                            </div>
                        </div>
                        <p class="au-hint">Minimum 6 characters.</p>
                        <?php $pfRef = ''; if (function_exists('partner_pending') && ($pp = partner_pending())) $pfRef = $pp[0];
                            elseif (preg_match('/^MM[A-Z0-9]{6}$/', (string)($_SESSION['ref_code'] ?? ($_COOKIE['mm_ref'] ?? '')))) $pfRef = (string)($_SESSION['ref_code'] ?? $_COOKIE['mm_ref']);
                            $pfRef = (string)(input_str('refCode', '') ?: $pfRef); ?>
                        <?php $refOpts = []; try { $refOpts = db()->ReferenceOptions(true); } catch (Throwable $e) { }
                            $hhSel = (string)input_str('howHeard', '') ?: ((function_exists('partner_pending') && partner_pending()) ? 'agency' : '');
                            if (!isset($refOpts[$hhSel])) $hhSel = ''; ?>
                        <?php if ($refOpts): ?>
                        <div class="au-f" id="hhBox">
                            <label for="howHeard">আপনি কার রেফারেন্সে / কোথা থেকে জেনে এসেছেন?</label>
                            <div class="au-in"><i class="fas fa-bullhorn"></i><select name="howHeard" id="howHeard" required>
                                <option value="">— বাছাই করুন —</option>
                                <?php foreach ($refOpts as $k => $o): ?><option value="<?= e($k) ?>"<?= $hhSel === $k ? ' selected' : '' ?>><?= e($o['label']) ?></option><?php endforeach; ?>
                            </select></div>
                            <div class="au-fb">কোথা থেকে জেনেছেন সেটা বাছাই করুন।</div>
                        </div>
                        <div class="au-f" id="hhOtherBox"<?= $hhSel === 'other' ? '' : ' hidden' ?>>
                            <label for="howHeardNote">কোথা থেকে / কার কাছ থেকে জেনেছেন?</label>
                            <div class="au-in"><i class="fas fa-pen"></i><input name="howHeardNote" id="howHeardNote" type="text" maxlength="100" placeholder="যেমন: কোচিং সেন্টার, সেমিনার, অমুক ভাই" autocomplete="off" value="<?= e((string)input_str('howHeardNote', '')) ?>" /></div>
                            <div class="au-fb">একটু লিখে দিন কোথা থেকে জেনেছেন।</div>
                        </div>
                        <?php endif; ?>
                        <div class="au-f">
                            <label for="refCode">Referral Code <span style="font-weight:500;color:#94A3B8;">(ঐচ্ছিক)</span></label>
                            <div class="au-in"><i class="fas fa-ticket"></i><input name="refCode" id="refCode" type="text" maxlength="20" placeholder="কোড থাকলে লিখুন (না থাকলে খালি রাখুন)" autocomplete="off" value="<?= e($pfRef) ?>" style="text-transform:uppercase;" /></div>
                        </div>
                        <div id="formError" class="au-alert err" style="display:none;"></div>
                        <button type="submit" class="au-btn"><i class="fas fa-user-plus"></i> Create Account</button>
                    </form>
                    <p class="au-switch">Already have an account?<a href="/Account/Login<?= $returnUrl !== '' ? '?returnUrl=' . rawurlencode($returnUrl) : '' ?>" data-go="login">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var au = document.getElementById('auth'), card = document.getElementById('auCard');
    var forms = { login: card.querySelector('[data-form="login"]'), register: card.querySelector('[data-form="register"]') };
    var titles = { login: 'Login', register: 'Register' };
    var urls = { login: '/Account/Login' + <?= js($returnUrl !== '' ? '?returnUrl=' . rawurlencode($returnUrl) : '') ?>, register: '/Account/Register' };

    function fit() {
        var cur = forms[au.getAttribute('data-mode')], h = cur.offsetHeight + parseFloat(getComputedStyle(card).paddingTop) + parseFloat(getComputedStyle(card).paddingBottom);
        card.style.height = h + 'px';
        // ফর্ম লম্বা হলে পুরো অংশটাও লম্বা হয় — উপরের হেডারের নিচে ঢুকে যায় না
        var st = au.querySelector('.au-stage'); if (st) st.style.minHeight = window.matchMedia('(max-width: 960px)').matches ? '' : Math.max(640, Math.ceil(h) + 24) + 'px';
    }
    // লগইন ↔ সাইনআপ — ছবি আর ফর্ম জায়গা বদলায়, ফর্মের লেখা স্মুথলি বদলায়
    function go(mode, push) {
        if (au.getAttribute('data-mode') === mode) return;
        var from = forms[au.getAttribute('data-mode')], to = forms[mode];
        card.style.height = card.offsetHeight + 'px';
        au.setAttribute('data-mode', mode); au.classList.add('moving'); clearTimeout(au._mv); au._mv = setTimeout(function () { au.classList.remove('moving'); }, 800);
        from.classList.add('off'); from.setAttribute('aria-hidden', 'true');
        to.classList.remove('off'); to.removeAttribute('aria-hidden');
        requestAnimationFrame(fit);
        document.title = document.title.replace(/^(Login|Register)/, titles[mode]);
        if (push) history.pushState({ mode: mode }, '', urls[mode]);
        setTimeout(function () { var f = to.querySelector('input:not([type=hidden]):not([tabindex="-1"])'); if (f && window.matchMedia('(hover: hover)').matches) f.focus({ preventScroll: true }); }, 500);
    }
    document.querySelectorAll('[data-go]').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); go(a.getAttribute('data-go'), true); }); });
    window.addEventListener('popstate', function () { go(/\/register/i.test(location.pathname) ? 'register' : 'login', false); });
    history.replaceState({ mode: au.getAttribute('data-mode') }, '', location.href);
    window.addEventListener('resize', fit); window.addEventListener('load', fit); fit();

    // পাসওয়ার্ড দেখান / লুকান
    document.querySelectorAll('[data-eye]').forEach(function (b) {
        b.addEventListener('click', function () { var i = b.parentNode.querySelector('input'), show = i.type === 'password'; i.type = show ? 'text' : 'password'; b.firstElementChild.className = show ? 'far fa-eye-slash' : 'far fa-eye'; });
    });

    // ===== লগইন যাচাই =====
    var lf = document.getElementById('loginForm'), lu = document.getElementById('loginUsername'), lp = document.getElementById('loginPassword');
    function mark(i) { var ok = i.value.trim().length > 0; i.classList.toggle('is-invalid', !ok); i.closest('.au-f').classList.toggle('err', !ok); return ok; }
    [lu, lp].forEach(function (i) { i.addEventListener('input', function () { mark(i); fit(); }); });
    lf.addEventListener('submit', function (e) { var a = mark(lu), b = mark(lp); if (!(a && b)) { e.preventDefault(); fit(); } });

    // ===== সাইনআপ যাচাই (আগের নিয়মই) =====
    var rf = document.getElementById('registerForm'), fullName = document.getElementById('fullName'), username = document.getElementById('username'), email = document.getElementById('email'),
        password = document.getElementById('password'), confirmPassword = document.getElementById('confirmPassword'), formError = document.getElementById('formError');
    function setState(i, ok) { i.classList.toggle('is-invalid', !ok); i.classList.toggle('is-valid', ok); i.closest('.au-f').classList.toggle('err', !ok); }
    function gib(w) { if (!/^[A-Za-z]+$/.test(w) || w.length <= 2) return false; if (!/[aeiouAEIOU]/.test(w)) return true; return /[^aeiouAEIOU]{4,}/.test(w); }
    function vName() { var w = fullName.value.trim().split(/\s+/).filter(Boolean); var ok = w.length >= 2 && w.every(function (x) { return /^[A-Za-zঀ-৿.]+$/.test(x) && !gib(x); }); setState(fullName, ok); return ok; }
    function vUser() { var ok = /^[A-Za-z0-9_]{3,20}$/.test(username.value.trim()); setState(username, ok); return ok; }
    function vEmail() { var ok = /^(?=.{6,30}@)[a-z0-9]+(?:\.[a-z0-9]+)*@gmail\.com$/i.test(email.value.trim()); setState(email, ok); return ok; }
    function vPass() { var ok = password.value.length >= 6; setState(password, ok); return ok; }
    function vConf() { var ok = confirmPassword.value.length > 0 && confirmPassword.value === password.value; setState(confirmPassword, ok); return ok; }
    [[fullName, vName], [username, vUser], [email, vEmail], [password, function () { vPass(); if (confirmPassword.value) vConf(); }], [confirmPassword, vConf]].forEach(function (p) {
        p[0].addEventListener('input', function () { formError.style.display = 'none'; p[1](); fit(); });
    });
    var hh = document.getElementById('howHeard'), hhNote = document.getElementById('howHeardNote'), hhOther = document.getElementById('hhOtherBox');
    function vHow() { if (!hh) return true; var ok = hh.value !== ''; setState(hh, ok); hhOther.hidden = hh.value !== 'other'; return ok; }
    function vHowNote() { if (!hh || hh.value !== 'other') return true; var ok = hhNote.value.trim().length > 0; setState(hhNote, ok); return ok; }
    if (hh) { hh.addEventListener('change', function () { formError.style.display = 'none'; vHow(); if (hh.value === 'other') hhNote.focus(); fit(); }); hhNote.addEventListener('input', function () { vHowNote(); fit(); }); }
    rf.addEventListener('submit', function (e) {
        var ok = [vName(), vUser(), vEmail(), vPass(), vConf(), vHow(), vHowNote()].every(Boolean);
        if (!ok) { e.preventDefault(); formError.innerHTML = '<i class="fas fa-circle-exclamation"></i><span>Please fix the highlighted fields before creating an account.</span>'; formError.style.display = 'flex'; fit(); }
    });

    // Suspend এর বাকি সময়
    var sl = document.getElementById('suspLeft');
    if (sl) (function tick() {
        var ms = new Date(sl.getAttribute('data-until')).getTime() - Date.now();
        if (ms <= 0) { sl.textContent = 'শেষ — এখন লগইন করতে পারবেন'; sl.style.color = '#155724'; return; }
        var s = Math.floor(ms / 1000), d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60);
        sl.textContent = (d ? d + ' দিন ' : '') + (d || h ? h + ' ঘণ্টা ' : '') + m + ' মিনিট ' + (s % 60) + ' সেকেন্ড'; setTimeout(tick, 1000);
    })();
})();
</script>
