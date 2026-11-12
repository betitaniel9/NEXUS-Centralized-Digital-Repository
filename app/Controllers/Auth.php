<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    /** Only BatStateU G Suite accounts may sign up or sign in. */
    private const EMAIL_DOMAIN = '@g.batstate-u.edu.ph';

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url(UserModel::homeFor(session()->get('roles') ?? [])));
        }

        return view('auth/login');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url(UserModel::homeFor(session()->get('roles') ?? [])));
        }

        return view('auth/register');
    }

    public function attemptLogin()
    {
        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        if ($email === '' || $password === '') {
            return $this->loginFailed('Please enter your email and password.');
        }

        if (! $this->isSchoolEmail($email)) {
            return $this->loginFailed('Please use your G Suite account (' . self::EMAIL_DOMAIN . ').');
        }

        $users = new UserModel();
        $user  = $users->findByEmail($email);

        if ($user === null || ! password_verify($password, $user['password'])) {
            return $this->loginFailed('Incorrect email or password. Please try again.');
        }

        // Upgrade the hash if PHP's default algorithm has changed since it was stored.
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $users->update($user['user_id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        $roles = $users->getRoleNames((int) $user['user_id']);

        $session = session();
        $session->regenerate(true); // new session ID on login (prevents session fixation)
        $session->set([
            'isLoggedIn'   => true,
            'user_id'      => (int) $user['user_id'],
            'first_name'   => $user['first_name'],
            'last_name'    => $user['Last_name'],
            'email'        => $user['email'],
            'roles'        => $roles,
            'council_role' => UserModel::primaryCouncilRole($roles),
        ]);

        // Student Council members see the badge loading screen first, then their dashboard.
        $next = UserModel::isCouncil($roles) ? 'loading' : 'student';

        return redirect()->to(base_url($next))
            ->with('success', 'Login successful. Welcome back, ' . $user['first_name'] . '!');
    }

    public function attemptRegister()
    {
        $rules = [
            'first_name' => [
                'label'  => 'First name',
                'rules'  => 'required|max_length[255]',
            ],
            'last_name' => [
                'label'  => 'Last name',
                'rules'  => 'required|max_length[255]',
            ],
            'email' => [
                'label'  => 'Email',
                'rules'  => [
                    'required',
                    'valid_email',
                    'max_length[255]',
                    'regex_match[/@g\.batstate-u\.edu\.ph$/i]',
                    'is_unique[users.email]',
                ],
                'errors' => [
                    'regex_match' => 'Use your G Suite account (ending in ' . self::EMAIL_DOMAIN . ').',
                    'is_unique'   => 'An account with this email already exists. Try signing in instead.',
                ],
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required|min_length[8]|max_length[72]',
                'errors' => [
                    'min_length' => 'Password must be at least 8 characters.',
                ],
            ],
            'confirm_password' => [
                'label'  => 'Confirm password',
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'matches' => 'Passwords do not match.',
                ],
            ],
            'terms' => [
                'label'  => 'Terms and Conditions',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'You must agree to the Terms and Conditions.',
                ],
            ],
        ];

        // Normalise the email before validating so the uniqueness check matches what we store.
        $post          = $this->request->getPost();
        $post['email'] = strtolower(trim((string) ($post['email'] ?? '')));

        if (! $this->validateData($post, $rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Registration failed. Please fix the highlighted fields.')
                ->with('errors', $this->validator->getErrors());
        }

        $userId = (new UserModel())->createStudent($post);

        if ($userId === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Registration failed due to a server error. Please try again.');
        }

        return redirect()->to(base_url('login'))
            ->with('success', 'Account created successfully! You can now sign in.')
            ->with('registered_email', $post['email']);
    }

    public function logout()
    {
        // Clear the login data but keep the session alive so the flash message survives the redirect.
        $session = session();
        $session->remove(['isLoggedIn', 'user_id', 'first_name', 'last_name', 'email', 'roles', 'council_role']);
        $session->regenerate(true);

        return redirect()->to(base_url('login'))
            ->with('success', 'You have been signed out.');
    }

    private function loginFailed(string $message)
    {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Login failed. ' . $message);
    }

    private function isSchoolEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && str_ends_with($email, self::EMAIL_DOMAIN);
    }
}
