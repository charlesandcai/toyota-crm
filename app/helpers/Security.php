<?php
declare(strict_types=1);

class Security
{
    public static function generateCSRFToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRFToken(?string $token): bool
    {
        if ($token === null || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::generateCSRFToken() . '">';
    }

    public static function csrfInput(): string
    {
        return self::csrfField();
    }

    public static function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . Url::base() . '/index.php?route=auth/login');
            exit;
        }
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            Response::error('Access denied. Administrator privileges required.', [], 403);
            exit;
        }
    }

    public static function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    public static function escape(?string $input): string
    {
        return htmlspecialchars($input ?? '', ENT_QUOTES, 'UTF-8');
    }
}
