<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class UserModel extends Model
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM users WHERE id = ? AND active = 1",
            [$id]
        );
    }

    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM users WHERE username = ? AND active = 1",
            [$username]
        );
    }

    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }

    public function create(string $username, string $password, string $fullName, string $role = 'sales'): int
    {
        return $this->insert('users', [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'role' => $role,
            'active' => 1,
        ]);
    }
}
