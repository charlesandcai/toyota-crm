<?php
declare(strict_types=1);

class Url
{
    public static function base(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        
        $baseUrl = $scheme . '://' . $host;
        if ($scriptDir !== '/' && $scriptDir !== '\\') {
            $baseUrl .= rtrim($scriptDir, '/\\');
        }
        return $baseUrl;
    }

    public static function current(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public static function route(string $route): string
    {
        return self::base() . '/index.php?route=' . urlencode($route);
    }

    public static function redirect(string $route): void
    {
        header('Location: ' . self::route($route));
        exit;
    }

    public static function asset(string $path): string
    {
        return self::base() . '/assets/' . ltrim($path, '/');
    }
}
