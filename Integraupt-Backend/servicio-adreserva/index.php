<?php

// Configurar el reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivar en producción / salida directa para no corromper respuestas JSON
ini_set('log_errors', 1);

// Configuración de CORS
$allowed_origins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejo de la petición OPTIONS (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Establecer cabecera de respuesta JSON
header("Content-Type: application/json; charset=UTF-8");

// Autoloader PSR-4 personalizado para namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Importar clases del core
use App\Core\Router;

try {
    // Inicializar el enrutador
    $router = new Router();

    // Registrar rutas de la API de administración de reservas
    $router->get('/api/admin/reservas', 'App\Controllers\AdminReservaController@listarReservas');
    $router->get('/api/admin/reservas/filtros', 'App\Controllers\AdminReservaController@obtenerFiltros');
    $router->post('/api/admin/reservas/{reservaId}/gestionar', 'App\Controllers\AdminReservaController@gestionarReserva');

    // Resolver la ruta actual
    $router->dispatch();

} catch (\App\Core\HttpException $e) {
    http_response_code($e->getStatusCode());
    echo json_encode([
        'status' => $e->getStatusCode(),
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d\TH:i:s\Z')
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log("Error no controlado: " . $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
    echo json_encode([
        'status' => 500,
        'error' => 'Ocurrió un error interno en el servidor.',
        'message' => $e->getMessage(), // Opcional, útil para depuración en desarrollo
        'timestamp' => date('Y-m-d\TH:i:s\Z')
    ]);
}
