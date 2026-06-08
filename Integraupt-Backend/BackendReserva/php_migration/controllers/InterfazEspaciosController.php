<?php

class InterfazEspaciosController {
    
    public function processRequest($method) {
        if ($method === 'GET') {
            echo json_encode(['mensaje' => 'Servicio disponible']);
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
        }
    }
}
?>
