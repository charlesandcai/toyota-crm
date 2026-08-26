<?php
declare(strict_types=1);

class Response
{
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success(string $message = 'Success', mixed $data = null): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message = 'Error', array $errors = [], int $statusCode = 400): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function view(string $viewPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__, 2) . '/views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: {$viewPath}");
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        
        require dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public static function viewOnly(string $viewPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__, 2) . '/views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: {$viewPath}");
        }
        require $viewFile;
    }
}
