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

    public function findByIdAny(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM users WHERE id = ?",
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

    public function findAll(): array
    {
        return $this->fetchAll(
            "SELECT id, username, full_name, role, active, created_at, updated_at FROM users ORDER BY created_at DESC"
        );
    }

    public function totalUsers(): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) as count FROM users");
        return (int) ($result['count'] ?? 0);
    }

    public function countAdmins(): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND active = 1");
        return (int) ($result['count'] ?? 0);
    }

    public function updateById(int $id, array $data): bool
    {
        return $this->update('users', $data, 'id = ?', [$id]);
    }

    public function updatePassword(int $id, string $password): bool
    {
        return $this->updateById($id, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $result = $this->fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0) > 0;
    }

    public function getTotalCount(): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) as count FROM users");
        return (int) ($result['count'] ?? 0);
    }

    public function getActiveCount(): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) as count FROM users WHERE active = 1");
        return (int) ($result['count'] ?? 0);
    }
}
