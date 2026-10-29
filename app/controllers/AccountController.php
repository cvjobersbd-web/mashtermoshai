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

        try {
            if ($this->db->RegisterUser(obj(['UserName' => $username, 'Password' => $password, 'FullName' => $fullName, 'Email' => $email]))) {
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
        try { $this->db->CreateNotification((int)$r->UserId, 'Password Changed 🔒', 'আপনার পাসওয়ার্ড ইমেইল লিংকের মাধ্যমে পরিবর্তন করা হয়েছে। আপনি না করে থাকলে এখনই Helpdesk এ জানান।', 'warning', '/Helpdesk', 'fa-key'); } catch (Throwable $e) { }
        tempdata_set('Success', 'পাসওয়ার্ড পরিবর্তন হয়েছে! এখন নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
        $this->redirectToAction('Login');
    }
}
