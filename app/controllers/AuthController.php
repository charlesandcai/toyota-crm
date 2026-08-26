<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/UserModel.php';

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function showLogin(): void
    {
        if (Security::isLoggedIn()) {
            Url::redirect('dashboard');
            return;
        }
        Response::viewOnly('auth.login');
    }

    public function login(): void
    {
        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            $error = 'Invalid form submission. Please try again.';
            Response::viewOnly('auth.login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
            Response::viewOnly('auth.login');
            return;
        }

        $user = $this->userModel->authenticate($username, $password);
        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            Url::redirect('dashboard');
            return;
        }

        $error = 'Invalid username or password.';
        Response::viewOnly('auth.login');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ' . Url::route('auth/login'));
        exit;
    }
}
