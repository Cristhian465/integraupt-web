<?php

declare(strict_types=1);

// ── Autoload ───────────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    // Namespaces → directorios (todo en minúsculas excepto el nombre del archivo)
    $map = [
        'Config\\'          => __DIR__ . '/config/',
        'Models\\'          => __DIR__ . '/models/',
        'Services\\Common\\'=> __DIR__ . '/services/common/',
        'Services\\Estudiante\\'     => __DIR__ . '/services/estudiante/',
        'Services\\Docente\\'        => __DIR__ . '/services/docente/',
        'Services\\Administrativo\\' => __DIR__ . '/services/administrativo/',
        'Services\\'        => __DIR__ . '/services/',
        'Controllers\\'     => __DIR__ . '/controllers/',
        'Routes\\'          => __DIR__ . '/routes/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// ── CORS y cabeceras ────────────────────────────────────────────────────────
\Config\App::enableCors();

// ── Manejo global de errores ────────────────────────────────────────────────
set_exception_handler(function (\Throwable $e): void {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
});

// ── Despachar ───────────────────────────────────────────────────────────────
(new \Routes\Router())->dispatch();
