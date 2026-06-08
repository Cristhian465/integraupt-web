<?php
require_once __DIR__ . '/../config/Database.php';

class FormularioReservaController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function processRequest($method, $uri, $apiIndex) {
        if ($method === 'GET') {
            $this->mostrarFormulario();
        }
    }

    private function mostrarFormulario() {
        $espacioId = isset($_GET['espacioId']) ? $_GET['espacioId'] : null;
        $escuelaId = isset($_GET['escuelaId']) ? $_GET['escuelaId'] : null;

        $espaciosDisponibles = [];

        // Lógica simplificada basada en el FormularioReservaController.java
        $query = "SELECT * FROM espacio WHERE activo = 1";
        if ($escuelaId) {
            $query .= " AND escuela_id = :escuela_id";
        }
        $stmt = $this->db->prepare($query);
        if ($escuelaId) {
            $stmt->bindParam(':escuela_id', $escuelaId);
        }
        $stmt->execute();
        $espaciosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $espacioSeleccionado = null;
        if ($espacioId != null) {
            $query = "SELECT * FROM espacio WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $espacioId);
            $stmt->execute();
            $espacioSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if it's already in the list
            $found = false;
            foreach ($espaciosDisponibles as $espacio) {
                if ($espacio['id'] == $espacioId) {
                    $found = true;
                    break;
                }
            }

            if ($espacioSeleccionado && !$found) {
                array_unshift($espaciosDisponibles, $espacioSeleccionado);
            }
        }

        $response = [
            'espacios' => $espaciosDisponibles,
            'espacioIdSeleccionado' => $espacioId,
            'espacioSeleccionDescripcion' => $espacioSeleccionado ? $espacioSeleccionado['codigo'] . ' - ' . $espacioSeleccionado['nombre'] : null
        ];

        echo json_encode($response);
    }
}
?>
