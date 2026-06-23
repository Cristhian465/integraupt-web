<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    // Convert namespace separators to directory separators
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
