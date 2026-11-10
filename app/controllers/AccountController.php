<?php
class AccountController extends Controller
{
    /** GET /Account/Login */
    public function Login(): void
    {
        if (session_get('UserName') !== null) $this->redirectToAction('Index', 'Home');
        // লগইন থাকা অবস্থায় Suspend হলে এখানে পাঠানো হয় — তথ্যটা একবার দেখানো হয়
        $susp = $_SESSION['__suspension'] ?? null;
        unset($_SESSION['__suspension']);
        $this->view(null, null, ['ReturnUrl' => input_str('returnUrl'), 'Suspension' => $susp]);
    }

    /** POST /Account/Login */
    public function Login_POST(): void
    {
        $username = trim((string)input_str('username', ''));
        $password = (string)input_str('password', '');
        $returnUrl = input_str('returnUrl');

        $user = ($username !== '' && $password !== '') ? $this->db->AuthenticateUser($username, $password) : null;
        if ($user && $user->Role !== 'Admin' && ($susp = $this->db->GetActiveSuspension((int)$user->Id))) {
            // ✅ পাসওয়ার্ড ঠিক, কিন্তু অ্যাকাউন্ট Suspend করা — লগইন হবে না, কারণ ও সময় দেখাবে
            $this->view('Login', null, ['Suspension' => suspension_info($susp), 'ReturnUrl' => $returnUrl]);
        }
        if ($user && $user->Role !== 'Admin' && !$this->db->IsEmailVerified((int)$user->Id)) {
            // ✅ পাসওয়ার্ড ঠিক, কিন্তু ইমেইল ভেরিফাই হয়নি — নতুন কোড পাঠিয়ে ভেরিফাই পেজে
            session_set('verify_uid', (int)$user->Id);
            $err = null;
            if ($this->sendOtp((int)$user->Id, $err)) tempdata_set('Success', 'লগইনের আগে ইমেইল ভেরিফাই করুন — আপনার ইমেইলে নতুন কোড পাঠানো হয়েছে।');
            else tempdata_set('Error', 'লগইনের আগে ইমেইল ভেরিফাই করুন। ' . $err);
            $this->redirectToAction('VerifyEmail');
        }
        if ($user && device_lock_applies($user->Role)) {
            // ✅ এক ডিভাইসে লগইন — অন্য ডিভাইসে চালু থাকলে অ্যাডমিনের অনুমতি লাগবে
            $st = $this->db->CheckLoginDevice((int)$user->Id, device_hash(), device_label(), client_ip());
            if ($st !== 'ok') { session_set('dev_uid', (int)$user->Id); $this->redirectToAction('Device'); }
        }
        if ($user) {
            $this->db->UpdateLastLogin($user->UserName);
            session_regenerate_id(true);
            session_set('UserId', $user->Id);
            session_set('UserName', $user->UserName);
            session_set('UserFullName', $user->FullName);
            session_set('UserEmail', $user->Email);
            session_set('UserRole', $user->Role);
            session_set('ProfileImageLink', $user->ProfileImageLink ?? '');

            if (!is_blank($returnUrl) && is_local_url($returnUrl)) redirect($returnUrl);
            if ($user->Role === 'Admin') $this->redirectToAction('Dashboard', 'Admin');
            $this->redirectToAction('Index', 'Home');
        }

        $this->view('Login', null, ['Error' => 'Username/Email অথবা পাসওয়ার্ড ভুল।', 'ReturnUrl' => $returnUrl]);
    }

    /** GET /Account/Register */
    public function Register(): void
    {
        if (session_get('UserName') !== null) $this->redirectToAction('Index', 'Home');
        $this->view();
    }

    /** POST /Account/Register */
    public function Register_POST(): void
    {
        $username = trim((string)input_str('username', ''));
        $password = (string)input_str('password', '');
        $confirm = (string)input_str('confirmPassword', '');
        $fullName = trim((string)input_str('fullName', ''));
        $email = (string)input_str('email', '');

        $fail = fn(string $msg) => $this->view('Register', null, ['Error' => $msg]);

        if ($username === '' || $fullName === '') $fail('All fields are required.');
        if ($password !== $confirm) $fail('Passwords do not match');
        if (strlen($password) < 6) $fail('Password must be at least 6 characters');
        if (trim($email) === '') $fail('Email is required.');
        $email = trim($email);
        if (!str_ends_with($email, '@gmail.com')) $fail('Email must end with @gmail.com (all lowercase).');
        if (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/', $email)) $fail('Please enter a valid Gmail address (e.g. yourname@gmail.com).');
        if ($this->db->UsernameExists($username)) $fail('Username already exists');
        if ($this->db->EmailExists($email)) $fail('Email already registered');

        // কোথা থেকে জেনেছেন (Facebook, YouTube ...) — ঐচ্ছিক, শুধু তালিকা থেকে
        $how = null;
        $howKey = (string)input_str('howHeard', '');
        if ($howKey !== '') $how = $this->db->CleanReference($howKey);

        // রেফারেল / পার্টনার কোড (ঐচ্ছিক) — এজেন্সির কোড হলে পার্টনার, MMxxxxxx হলে ইউজার রেফারেল
        $typed = strtoupper(trim((string)input_str('refCode', '')));
        $partner = null; $pVia = 'link'; $pSrc = 'direct'; $userRef = '';
        $pend = partner_pending();
        if ($typed !== '') {
            if (($partner = $this->db->GetPartnerByCode($typed)) !== null) {
                if ($pend && $pend[0] === strtoupper((string)$partner->Code)) $pSrc = $pend[1];   // লিংক থেকে এসে কোডটাই দিয়েছেন
                else { $pVia = 'code'; $pSrc = 'code'; }
            } elseif (preg_match('/^MM[A-Z0-9]{6}$/', $typed) && $this->db->GetUserIdByReferralCode($typed)) {
                $userRef = $typed;
            } else {
                $fail('রেফারেন্স কোডটি সঠিক নয় — আবার দেখে লিখুন (যেমন MASHTER), অথবা ঘরটি খালি রাখুন।');
            }
        } elseif ($pend && ($partner = $this->db->GetPartnerByCode($pend[0])) !== null) {
            $pSrc = $pend[1];
        }

        try {
            if ($this->db->RegisterUser(obj(['UserName' => $username, 'Password' => $password, 'FullName' => $fullName, 'Email' => $email]))) {
                $newId = (int)$this->db->scalar('SELECT `Id` FROM `Users` WHERE LOWER(`UserName`) = LOWER(:u)', ['u' => $username]);
                $code = $userRef !== '' ? $userRef : (string)($_SESSION['ref_code'] ?? ($_COOKIE['mm_ref'] ?? ''));
                if (!preg_match('/^MM[A-Z0-9]{6}$/', $code)) $code = '';
                if ($newId && $how) { try { $this->db->SetUserHowHeard($newId, $how[0], $how[1]); } catch (Throwable $e) { error_log('[howHeard] ' . $e->getMessage()); } }
                if ($newId && $partner) { $this->db->RecordPartnerSignup($newId, (int)$partner->Id, $pVia, $pSrc); partner_forget(); }
                unset($_SESSION['ref_code']);
                if (!headers_sent()) setcookie('mm_ref', '', ['expires' => time() - 3600, 'path' => '/']);

                // রেফারেল সাইনআপের সময়ই রেকর্ড হয় (অ্যাডমিন দেখতে পান কে কার লিংকে এসেছে) — বোনাস যায় প্যাকেজ কিনলে
                if ($newId && $code !== '') $this->creditReferral($code, $newId, $fullName);
                if ($newId) {
                    // SMTP সেট না থাকলেও Mailer সার্ভারের mail() দিয়ে পাঠানোর চেষ্টা করে
                    // ✅ ইমেইল ভেরিফিকেশন: OTP না দেওয়া পর্যন্ত লগইন হবে না
                    $this->db->MarkEmailUnverified($newId);
                    session_set('verify_uid', $newId);
                    $err = null;
                    if ($this->sendOtp($newId, $err)) tempdata_set('Success', 'আপনার ইমেইলে ৬ ডিজিটের একটা কোড পাঠানো হয়েছে।');
                    else tempdata_set('Error', $err);
                    $this->redirectToAction('VerifyEmail');
                }
                // (নতুন আইডি পাওয়া না গেলে — হওয়ার কথা না)
                pixel_track('CompleteRegistration', ['status' => true, 'content_name' => 'Signup']);
                tempdata_set('Success', 'Registration successful! Please login.');
                $this->redirectToAction('Login');
            }
        } catch (Throwable $e) {
            $fail($e->getMessage());
        }
        $fail('Registration failed. Please try again.');
    }

    /** POST /Account/Logout */
    public function Logout_POST(): void
    {
        session_clear();
        $this->redirectToAction('Login');
    }

    /** GET /Account/Logout — লিংক থেকে লগআউট করলেও কাজ করবে */
    public function Logout(): void
    {
        session_clear();
        $this->redirectToAction('Login');
    }

    // ============================================================
    // ===== FORGOT / RESET PASSWORD =====
    // ============================================================
    private const RESET_SENT_MSG = 'এই তথ্যের সাথে কোনো অ্যাকাউন্ট থাকলে, তার রেজিস্টার করা ইমেইলে পাসওয়ার্ড রিসেটের লিংক পাঠানো হয়েছে। ইনবক্স চেক করুন — লিংকটা ৩০ মিনিট কাজ করবে।';

    /** GET /Account/ForgotPassword */
    public function ForgotPassword(): void
    {
        if (session_get('UserName') !== null) $this->redirectToAction('Index', 'Home');
        $this->view();
    }

    /** POST /Account/ForgotPassword */
    public function ForgotPassword_POST(): void
    {
        $v = trim((string)input_str('email', ''));
        if ($v === '' || mb_strlen($v) > 200) $this->view('ForgotPassword', null, ['Error' => 'আপনার ইমেইল অথবা Username দিন।', 'Value' => $v]);

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if ($this->db->CountRecentResets(null, $ip, 60) >= 10) {
            $this->view('ForgotPassword', null, ['Error' => 'অনেকবার চেষ্টা করা হয়েছে। ১ ঘণ্টা পর আবার চেষ্টা করুন।', 'Value' => $v]);
        }

        $mailer = Mailer::fromSettings();
        $failed = null;
        foreach ($this->db->FindUsersForReset($v) as $u) {
            if ($this->db->CountRecentResets((int)$u->Id, null, 60) >= 3) continue;   // এক অ্যাকাউন্টে ঘণ্টায় সর্বোচ্চ ৩টা মেইল
            $token = $this->db->CreatePasswordReset((int)$u->Id, $ip);
            $link = site_public_url() . '/Account/ResetPassword?token=' . $token;
            $name = (string)($u->FullName ?: $u->UserName);
            $site = site_settings()->GetSafeSiteName();
            $html = Mailer::template('পাসওয়ার্ড রিসেট করুন',
                '<p>হ্যালো <b>' . e($name) . '</b>,</p><p>আপনার <b>' . e($site) . '</b> অ্যাকাউন্টের (Username: <b>' . e($u->UserName) . '</b>) পাসওয়ার্ড রিসেট করার অনুরোধ পেয়েছি। নিচের বাটনে ক্লিক করে নতুন পাসওয়ার্ড সেট করুন।</p>',
                'নতুন পাসওয়ার্ড সেট করুন', $link,
                'লিংকটা ৩০ মিনিট পর্যন্ত কাজ করবে এবং একবারই ব্যবহার করা যাবে। আপনি অনুরোধ না করে থাকলে এই ইমেইলটা উপেক্ষা করুন — আপনার পাসওয়ার্ড বদলাবে না।');
            $text = "হ্যালো $name,\n\n$site অ্যাকাউন্টের (Username: {$u->UserName}) পাসওয়ার্ড রিসেট করতে এই লিংকটা খুলুন (৩০ মিনিট কাজ করবে):\n$link\n\nআপনি অনুরোধ না করে থাকলে এই ইমেইলটা উপেক্ষা করুন।";
            $err = null;
            if (!$mailer->send((string)$u->Email, $name, "$site — পাসওয়ার্ড রিসেট", $html, $text, $err)) {
                $failed = $err;
                error_log('[ForgotPassword] mail to user#' . $u->Id . ' failed: ' . $err);
            }
        }
        if ($failed !== null) {
            // ইমেইল পাঠানো যায়নি (SMTP সেট করা নেই/ভুল) — ইউজারকে সাধারণ বার্তা, আসল কারণ লগে
            $this->view('ForgotPassword', null, ['Error' => 'এই মুহূর্তে ইমেইল পাঠানো যাচ্ছে না। কিছুক্ষণ পর চেষ্টা করুন অথবা Helpdesk এ যোগাযোগ করুন।', 'Value' => $v]);
        }
        tempdata_set('Success', self::RESET_SENT_MSG);
        $this->redirectToAction('ForgotPassword');
    }

    /** GET /Account/ResetPassword?token=... */
    public function ResetPassword(): void
    {
        $token = (string)input_str('token', '');
        $r = $this->db->GetValidPasswordReset($token);
        $this->view(null, null, ['Token' => $token, 'Valid' => $r !== null, 'UserName' => $r->UserName ?? null]);
    }

    /** POST /Account/ResetPassword */
    public function ResetPassword_POST(): void
    {
        $token = (string)input_str('token', '');
        $pw = (string)input_str('password', '');
        $confirm = (string)input_str('confirmPassword', '');
        $r = $this->db->GetValidPasswordReset($token);
        if (!$r) $this->view('ResetPassword', null, ['Token' => $token, 'Valid' => false]);
        $back = fn(string $msg) => $this->view('ResetPassword', null, ['Token' => $token, 'Valid' => true, 'UserName' => $r->UserName, 'Error' => $msg]);
        if (strlen($pw) < 6) $back('পাসওয়ার্ড অন্তত ৬ অক্ষরের হতে হবে।');
        if (strlen($pw) > 128) $back('পাসওয়ার্ড খুব বড়।');
        if ($pw !== $confirm) $back('দুইটা পাসওয়ার্ড মেলেনি।');
        if (!$this->db->CompletePasswordReset((int)$r->ResetId, (int)$r->UserId, $pw)) {
            $this->view('ResetPassword', null, ['Token' => $token, 'Valid' => false]);
        }
        if (!$this->db->IsEmailVerified((int)$r->UserId)) {   // ইমেইলের লিংক খুলেছে = ইমেইল তারই
            $ref = $this->db->MarkEmailVerified((int)$r->UserId);
            if ($ref !== '') $this->creditReferral($ref, (int)$r->UserId, (string)($r->FullName ?: $r->UserName));
        }
        try { $this->db->CreateNotification((int)$r->UserId, 'Password Changed 🔒', 'আপনার পাসওয়ার্ড ইমেইল লিংকের মাধ্যমে পরিবর্তন করা হয়েছে। আপনি না করে থাকলে এখনই Helpdesk এ জানান।', 'warning', '/Helpdesk', 'fa-key'); } catch (Throwable $e) { }
        tempdata_set('Success', 'পাসওয়ার্ড পরিবর্তন হয়েছে! এখন নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
        $this->redirectToAction('Login');
    }

    // ============================================================
    // ===== EMAIL VERIFICATION (সাইনআপের পর OTP) =====
    // ============================================================
    private const OTP_COOLDOWN = 60;   // সেকেন্ড — আবার কোড পাঠানোর আগে অপেক্ষা

    /** রেফারেল রেকর্ড (বোনাস পরে — প্যাকেজ কিনলে) + রেফারারকে জানানো */
    private function creditReferral(string $code, int $newId, string $fullName): void
    {
        $refId = $this->db->CreditReferral($code, $newId, (string)($_SERVER['REMOTE_ADDR'] ?? ''));
        if ($refId) {
            $bonus = $this->db->ReferralSettings()['bonus'];
            $this->db->CreateNotification($refId, 'নতুন রেফারেল 👋', "{$fullName} আপনার রেফারেল লিংক দিয়ে সাইনআপ করেছেন। উনি প্যাকেজ কিনলে আপনি ৳{$bonus} বোনাস পাবেন।", 'info', '/Referral', 'fa-user-plus');
        }
    }

    /** ইমেইলে OTP পাঠায়। ১ মিনিটে একবার, ঘণ্টায় সর্বোচ্চ ৬টা। ব্যর্থ হলে $err এ বাংলা কারণ */
    private function sendOtp(int $uid, ?string &$err): bool
    {
        $since = $this->db->SecondsSinceLastOtp($uid);
        if ($since !== null && $since < self::OTP_COOLDOWN) { $err = 'আগের কোডটা কয়েক সেকেন্ড আগে পাঠানো হয়েছে। ' . (self::OTP_COOLDOWN - $since) . ' সেকেন্ড পর আবার চাইতে পারবেন।'; return false; }
        if ($this->db->CountRecentOtps($uid, 60) >= 6) { $err = 'অনেকবার কোড চাওয়া হয়েছে। ১ ঘণ্টা পর আবার চেষ্টা করুন।'; return false; }
        $u = $this->db->GetUserById($uid);
        if (!$u || is_blank($u->Email)) { $err = 'অ্যাকাউন্টে কোনো ইমেইল নেই। Helpdesk এ যোগাযোগ করুন।'; return false; }
        $code = $this->db->CreateEmailOtp($uid, (string)($_SERVER['REMOTE_ADDR'] ?? ''));
        $name = (string)($u->FullName ?: $u->UserName);
        $site = site_settings()->GetSafeSiteName();
        $min = Db::OTP_MINUTES;
        $html = Mailer::template('ইমেইল ভেরিফিকেশন কোড',
            '<p>হ্যালো <b>' . e($name) . '</b>,</p><p><b>' . e($site) . '</b> এ সাইনআপ করার জন্য ধন্যবাদ! আপনার ইমেইল ভেরিফাই করতে নিচের কোডটা দিন:</p>'
            . '<p style="text-align:center;margin:22px 0"><span style="display:inline-block;font-size:32px;font-weight:700;letter-spacing:10px;padding:12px 22px;border-radius:12px;background:#E6FAFF;color:#0B1F44;border:1px solid #9BE7FA;font-family:Consolas,monospace">' . $code . '</span></p>',
            null, null,
            "কোডটা {$min} মিনিট কাজ করবে। কাউকে এই কোড বলবেন না — আমরা কখনো কোড চাই না। আপনি সাইনআপ না করে থাকলে এই ইমেইলটা উপেক্ষা করুন।");
        $text = "হ্যালো {$name},\n\n{$site} — আপনার ইমেইল ভেরিফিকেশন কোড: {$code}\n\nকোডটা {$min} মিনিট কাজ করবে। কাউকে এই কোড বলবেন না।";
        $mErr = null;
        if (!Mailer::fromSettings()->send((string)$u->Email, $name, "{$site} — ভেরিফিকেশন কোড {$code}", $html, $text, $mErr)) {
            error_log('[VerifyEmail] mail to user#' . $uid . ' failed: ' . $mErr);
            $err = 'এই মুহূর্তে ইমেইল পাঠানো যাচ্ছে না। একটু পর "আবার কোড পাঠান" চাপুন অথবা Helpdesk এ যোগাযোগ করুন।';
            return false;
        }
        return true;
    }

    private static function maskEmail(string $email): string
    {
        [$a, $d] = array_pad(explode('@', $email, 2), 2, '');
        $keep = mb_substr($a, 0, min(2, mb_strlen($a)));
        return $keep . str_repeat('•', max(2, mb_strlen($a) - mb_strlen($keep))) . ($d !== '' ? '@' . $d : '');
    }

    /** সেশনে থাকা ভেরিফাই-বাকি ইউজার (না থাকলে লগইনে পাঠায়) */
    private function pendingVerifyUser(): Obj
    {
        $uid = (int)(session_get('verify_uid') ?? 0);
        $u = $uid ? $this->db->GetUserById($uid) : null;
        if (!$u || $this->db->IsEmailVerified($uid)) {
            unset($_SESSION['verify_uid']);
            $this->redirectToAction('Login');
        }
        return $u;
    }

    private function verifyView(Obj $u, ?string $error = null): void
    {
        $since = $this->db->SecondsSinceLastOtp((int)$u->Id);
        $this->view('VerifyEmail', null, [
            'Email' => self::maskEmail((string)$u->Email), 'Error' => $error,
            'Wait' => $since === null ? 0 : max(0, self::OTP_COOLDOWN - $since), 'Minutes' => Db::OTP_MINUTES,
        ]);
    }

    /** GET /Account/VerifyEmail */
    public function VerifyEmail(): void
    {
        if (session_get('UserName') !== null) $this->redirectToAction('Index', 'Home');
        $this->verifyView($this->pendingVerifyUser());
    }

    /** POST /Account/VerifyEmail — কোড যাচাই */
    public function VerifyEmail_POST(): void
    {
        $u = $this->pendingVerifyUser();
        $code = preg_replace('/\D/', '', strtr((string)input_str('code', ''), ['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9']));
        $res = $this->db->CheckEmailOtp((int)$u->Id, $code);
        if ($res === 'wrong') $this->verifyView($u, 'কোডটা ভুল হয়েছে। ইমেইলে পাঠানো ৬ ডিজিটের কোডটা আবার দেখে দিন।');
        if ($res === 'locked') $this->verifyView($u, 'অনেকবার ভুল কোড দেওয়া হয়েছে। "আবার কোড পাঠান" চেপে নতুন কোড নিন।');
        if ($res === 'expired') $this->verifyView($u, 'কোডের মেয়াদ শেষ। "আবার কোড পাঠান" চেপে নতুন কোড নিন।');

        $ref = $this->db->MarkEmailVerified((int)$u->Id);
        if ($ref !== '') $this->creditReferral($ref, (int)$u->Id, (string)($u->FullName ?: $u->UserName));
        unset($_SESSION['verify_uid']);
        pixel_track('CompleteRegistration', ['status' => true, 'content_name' => 'Email Verified Signup']);
        tempdata_set('Success', 'ইমেইল ভেরিফাই হয়েছে! এখন আপনার Username/Email আর পাসওয়ার্ড দিয়ে লগইন করুন।');
        $this->redirectToAction('Login');
    }

    /** POST /Account/ResendOtp */
    public function ResendOtp_POST(): void
    {
        $u = $this->pendingVerifyUser();
        $err = null;
        if ($this->sendOtp((int)$u->Id, $err)) tempdata_set('Success', 'নতুন কোড পাঠানো হয়েছে। আগের কোডটা আর কাজ করবে না।');
        else tempdata_set('Error', $err);
        $this->redirectToAction('VerifyEmail');
    }

    // ============================================================
    // ===== এক ডিভাইসে লগইন — নতুন ডিভাইসের জন্য অ্যাডমিনের কাছে অনুরোধ =====
    // ============================================================
    private function deviceUser(): Obj
    {
        $uid = (int)(session_get('dev_uid') ?? 0);
        $u = $uid ? $this->db->GetUserById($uid) : null;
        if (!$u || !device_lock_applies($u->Role)) { unset($_SESSION['dev_uid']); $this->redirectToAction('Login'); }
        return $u;
    }

    /** GET /Account/Device */
    public function Device(): void
    {
        if (session_get('UserName') !== null) $this->redirectToAction('Index', 'Home');
        $u = $this->deviceUser();
        $st = $this->db->CheckLoginDevice((int)$u->Id, device_hash(), device_label(), client_ip());
        if ($st === 'ok') {
            unset($_SESSION['dev_uid']);
            tempdata_set('Success', 'এই ডিভাইস চালু করা হয়েছে — এখন লগইন করুন।');
            $this->redirectToAction('Login');
        }
        $act = $this->db->GetActiveDevice((int)$u->Id);
        $this->view(null, null, ['State' => $st, 'Name' => (string)($u->FullName ?: $u->UserName), 'This' => device_label(),
            'Other' => $act ? (string)$act->Label : '', 'OtherSeen' => $act ? (string)($act->LastSeenAt ?: $act->CreatedAt) : '']);
    }

    /** POST /Account/Device (note) — অ্যাডমিনের কাছে অনুরোধ */
    public function Device_POST(): void
    {
        $u = $this->deviceUser();
        $note = mb_substr(trim(strip_tags((string)input_str('note', ''))), 0, 300);
        $this->db->RequestDevice((int)$u->Id, device_hash(), device_label(), client_ip(), $note);
        $name = (string)($u->FullName ?: $u->UserName);
        foreach ($this->db->GetAdmins() as $a) {
            $this->db->CreateNotification((int)$a->Id, '📱 নতুন ডিভাইসে লগইনের অনুরোধ', "$name ({$u->Role}) " . device_label() . ' থেকে লগইন করতে চান' . ($note !== '' ? " — \"$note\"" : '') . '।', 'info', '/Admin/Devices', 'fa-mobile-screen');
        }
        tempdata_set('Success', 'অনুরোধ পাঠানো হয়েছে। অ্যাডমিন Accept করলে এই ডিভাইস থেকে লগইন করতে পারবেন — আগের ডিভাইস থেকে তখন লগআউট হয়ে যাবে।');
        $this->redirectToAction('Device');
    }
}
