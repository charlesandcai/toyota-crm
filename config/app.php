<?php
declare(strict_types=1);

class Config
{
    private static array $config = [];

    private const ENV_KEYS = [
        'APP_NAME', 'APP_ENV', 'APP_URL', 'APP_TIMEZONE',
        'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
        'APP_DEBUG', 'APP_LOG_LEVEL',
    ];

    public static function load(): void
    {
        $envPath = dirname(__DIR__) . '/.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    self::$config[$key] = $value;
                }
            }
        }

        foreach (self::ENV_KEYS as $key) {
            $value = getenv($key);
            if ($value !== false) {
                self::$config[$key] = $value;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::$config[$key] = $value;
    }

    public static function all(): array
    {
        return self::$config;
    }
}

Config::load();
