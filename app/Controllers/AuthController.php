<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\Validator;
use App\Helpers\Csrf;
use App\Services\Logger;

class AuthController extends BaseController
{
    private $userModel;
    private const BRUTE_FORCE_LIMIT = 5;
    private const BRUTE_FORCE_LOCKOUT = 900; // 15 minutes

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/documind/public/dashboard');
        }
        $this->render('auth.login', [
            'csrf_token' => Csrf::generate(),
        ], 'auth');
    }

    public function login(): void
    {
        // Check for brute force attempts
        $this->checkBruteForce();

        try {
            Validator::make($_POST, [
                'email' => 'required|email|max:255',
                'password' => 'required|min:8|max:128',
            ])->validate();
        } catch (\App\Helpers\ValidationException $e) {
            $_SESSION['errors'] = $e->errors();
            $this->redirect('/documind/public/login');
        }

        $email = strtolower(trim($_POST['email']));
        $password = $_POST['password'];

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            $this->recordFailedAttempt();
            Logger::warning('Failed login attempt', ['email' => $email]);
            
            $_SESSION['errors'] = ['auth' => 'Invalid email or password'];
            $this->redirect('/documind/public/login');
        }

        if (!$user['is_active']) {
            $_SESSION['errors'] = ['auth' => 'Your account is inactive'];
            $this->redirect('/documind/public/login');
        }

        // Successful login
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_timeout'] = 0;

        Csrf::regenerate();
        Logger::info('User logged in', ['user_id' => $user['id']]);

        $this->redirect('/documind/public/dashboard');
    }

    public function showRegister(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/documind/public/dashboard');
        }
        $this->render('auth.register', [
            'csrf_token' => Csrf::generate(),
        ], 'auth');
    }

    public function register(): void
    {
        try {
            Validator::make($_POST, [
                'name' => 'required|string|max:150',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8|max:128',
                'password_confirmation' => 'required',
                'password' => 'confirmed',
            ])->validate();
        } catch (\App\Helpers\ValidationException $e) {
            $_SESSION['errors'] = $e->errors();
            $this->redirect('/documind/public/register');
        }

        $email = strtolower(trim($_POST['email']));
        $existing = $this->userModel->findByEmail($email);

        if ($existing) {
            $_SESSION['errors'] = ['email' => 'This email is already registered'];
            $this->redirect('/documind/public/register');
        }

        try {
            $userId = $this->userModel->create([
                'name' => trim($_POST['name']),
                'email' => $email,
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ]);

            Logger::info('New user registered', ['user_id' => $userId]);

            $_SESSION['success'] = 'Registration successful! Please log in.';
            $this->redirect('/documind/public/login');
        } catch (\Exception $e) {
            Logger::error('Registration error', ['error' => $e->getMessage()]);
            $_SESSION['errors'] = ['general' => 'Registration failed. Please try again.'];
            $this->redirect('/documind/public/register');
        }
    }

    public function logout(): void
    {
        Logger::info('User logged out', ['user_id' => $_SESSION['user_id'] ?? null]);
        
        $_SESSION = [];
        session_destroy();
        
        $this->redirect('/documind/public/login');
    }

    public function googleLogin(): void
    {
        // Placeholder for Google OAuth implementation
        // Will be implemented in next phase
        $this->render('auth.login', ['csrf_token' => Csrf::generate()], 'auth');
    }

    public function googleCallback(): void
    {
        // Placeholder for Google OAuth callback
        // Will be implemented in next phase
        $this->abort(501); // Not Implemented
    }

    public function showDashboard(): void
    {
        $this->render('dashboard.index', [], 'app');
    }

    private function checkBruteForce(): void
    {
        $timeout = $_SESSION['login_timeout'] ?? 0;
        
        if ($timeout > time()) {
            $_SESSION['errors'] = ['auth' => 'Too many failed attempts. Please try again in 15 minutes.'];
            http_response_code(429);
            include __DIR__ . '/../../views/errors/429.php';
            exit;
        }
    }

    private function recordFailedAttempt(): void
    {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

        if ($_SESSION['login_attempts'] >= self::BRUTE_FORCE_LIMIT) {
            $_SESSION['login_timeout'] = time() + self::BRUTE_FORCE_LOCKOUT;
            Logger::warning('Brute force attempt detected', [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'attempts' => $_SESSION['login_attempts'],
            ]);
        }
    }
}
