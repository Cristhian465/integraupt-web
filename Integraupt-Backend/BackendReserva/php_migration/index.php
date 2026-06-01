<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if (in_array('api', $uri)) {
    $apiIndex = array_search('api', $uri);
    $resource = isset($uri[$apiIndex + 1]) ? $uri[$apiIndex + 1] : null;
    $id = isset($uri[$apiIndex + 2]) ? $uri[$apiIndex + 2] : null;

    if ($resource === 'reservas') {
        require_once 'controllers/ReservaController.php';
        $controller = new ReservaController();
        $controller->processRequest($_SERVER['REQUEST_METHOD'], $id, $uri, $apiIndex);
    } elseif ($resource === 'espacios') {
        require_once 'controllers/EspacioController.php';
        $controller = new EspacioController();
        $controller->processRequest($_SERVER['REQUEST_METHOD'], $id, $uri, $apiIndex);
    } elseif ($resource === 'catalogos') {
        require_once 'controllers/CatalogoController.php';
        $controller = new CatalogoController();
        $controller->processRequest($_SERVER['REQUEST_METHOD'], $id, $uri, $apiIndex);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['message' => 'Resource not found']);
    }
} else {
    echo json_encode(['mensaje' => 'Servicio disponible']);
}
?>
