<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/models/Model.php';
require_once dirname(__DIR__, 2) . '/app/models/UserModel.php';

class UserController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    private function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    private function respondJsonOrRedirect(string $kind, string $message, array $errors = []): void
    {
        if ($this->isAjax()) {
            if ($kind === 'success') {
                Response::success($message);
            }
            Response::error($message, $errors);
        }

        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $kind === 'success' ? 'success' : 'danger';
        Url::redirect('settings/users');
    }

    public function index(): void
    {
        Security::requireAdmin();

        $users = $this->userModel->findAll();
        $activePage = 'settings_users';
        $settingsTab = 'users';
        Response::view('settings.users', compact('activePage', 'settingsTab', 'users'));
    }

    public function store(): void
    {
        Security::requireAdmin();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            $this->respondJsonOrRedirect('error', 'Invalid form submission.');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'sales';

        $errors = [];

        if ($fullName === '') {
            $errors['full_name'] = 'Full name is required.';
        } elseif (mb_strlen($fullName) > 100) {
            $errors['full_name'] = 'Full name must not exceed 100 characters.';
        }

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (mb_strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        } elseif (mb_strlen($username) > 50) {
            $errors['username'] = 'Username must not exceed 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Username may only contain letters, numbers, and underscores.';
        } elseif ($this->userModel->usernameExists($username)) {
            $errors['username'] = 'Username is already taken.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (mb_strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!in_array($role, ['admin', 'sales'])) {
            $errors['role'] = 'Invalid role.';
        }

        if (!empty($errors)) {
            $this->respondJsonOrRedirect('error', 'Validation failed.', $errors);
            return;
        }

        try {
            $this->userModel->create($username, $password, $fullName, $role);
            $this->respondJsonOrRedirect('success', 'User created successfully.');
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            $this->respondJsonOrRedirect('error', 'Unable to create user. Please try again.');
        }
    }

    public function update(): void
    {
        Security::requireAdmin();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            $this->respondJsonOrRedirect('error', 'Invalid form submission.');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            $this->respondJsonOrRedirect('error', 'Invalid user.');
            return;
        }

        $user = $this->userModel->findByIdAny($id);
        if (!$user) {
            $this->respondJsonOrRedirect('error', 'User not found.');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? $user['full_name']);
        $username = trim($_POST['username'] ?? $user['username']);
        $role = $_POST['role'] ?? $user['role'];
        $active = isset($_POST['active']) ? (int) $_POST['active'] : $user['active'];

        $errors = [];

        if ($fullName === '') {
            $errors['full_name'] = 'Full name is required.';
        }

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (mb_strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        } elseif (mb_strlen($username) > 50) {
            $errors['username'] = 'Username must not exceed 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Username may only contain letters, numbers, and underscores.';
        } elseif ($username !== $user['username'] && $this->userModel->usernameExists($username, $id)) {
            $errors['username'] = 'Username is already taken.';
        }

        if (!in_array($role, ['admin', 'sales'])) {
            $errors['role'] = 'Invalid role.';
        }

        if (!in_array($active, [0, 1])) {
            $errors['active'] = 'Invalid status.';
        }

        // Prevent removing last admin
        if ($user['role'] === 'admin' && $role !== 'admin' && $this->userModel->countAdmins() <= 1) {
            $errors['role'] = 'Cannot remove the last administrator.';
        }

        if (!empty($errors)) {
            $this->respondJsonOrRedirect('error', 'Validation failed.', $errors);
            return;
        }

        try {
            $this->userModel->updateById($id, [
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role,
                'active' => $active,
            ]);
            $this->respondJsonOrRedirect('success', 'User updated successfully.');
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage());
            $this->respondJsonOrRedirect('error', 'Unable to update user.');
        }
    }

    public function updatePassword(): void
    {
        Security::requireAdmin();

        if (!Security::verifyCSRFToken($_POST['_csrf_token'] ?? '')) {
            $this->respondJsonOrRedirect('error', 'Invalid form submission.');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if ($id === 0) {
            $this->respondJsonOrRedirect('error', 'Invalid user.');
            return;
        }

        $user = $this->userModel->findByIdAny($id);
        if (!$user) {
            $this->respondJsonOrRedirect('error', 'User not found.');
            return;
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (mb_strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $this->respondJsonOrRedirect('error', 'Validation failed.', $errors);
            return;
        }

        try {
            $this->userModel->updatePassword($id, $password);
            $this->respondJsonOrRedirect('success', 'Password updated successfully.');
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            $this->respondJsonOrRedirect('error', 'Unable to update password.');
        }
    }
}
