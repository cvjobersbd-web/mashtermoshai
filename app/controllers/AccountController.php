<?php
class AccountController extends Controller
{
    /** GET /Account/Login */
    public function Login(): void
    {
        if (session_get('UserName') !== null) $this->redirectToAction('Index', 'Home');
        $this->view(null, null, ['ReturnUrl' => input_str('returnUrl')]);
    }

    /** POST /Account/Login */
    public function Login_POST(): void
    {
        $username = trim((string)input_str('username', ''));
        $password = (string)input_str('password', '');
        $returnUrl = input_str('returnUrl');

        $user = ($username !== '' && $password !== '') ? $this->db->AuthenticateUser($username, $password) : null;
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

        $this->view('Login', null, ['Error' => 'Invalid username or password', 'ReturnUrl' => $returnUrl]);
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
}
