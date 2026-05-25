<?php

declare(strict_types=1);

// ── Autoloader ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../autoload.php';

// ── CORS ──────────────────────────────────────────────────────────────────────
\Config\Cors::apply();

// ── Route ─────────────────────────────────────────────────────────────────────
$router = \Config\Router::register();
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);
