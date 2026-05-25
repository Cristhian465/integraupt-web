<?php

namespace App\Controllers;

use App\Core\HttpException;
use App\DTO\AdminGestionReservaRequest;
use App\DTO\AdminReservaFilter;
use App\Services\AdminReservaService;

class AdminReservaController {
    private AdminReservaService $adminReservaService;

    public function __construct() {
        $this->adminReservaService = new AdminReservaService();
    }

    public function listarReservas(): void {
        $estado = $_GET['estado'] ?? null;
        $tipoEspacio = $_GET['tipoEspacio'] ?? null;
        
        $facultadId = isset($_GET['facultadId']) && $_GET['facultadId'] !== '' ? (int)$_GET['facultadId'] : null;
        $escuelaId = isset($_GET['escuelaId']) && $_GET['escuelaId'] !== '' ? (int)$_GET['escuelaId'] : null;
        $fecha = $_GET['fecha'] ?? null;
        $search = $_GET['search'] ?? null;
        $usuarioId = isset($_GET['usuarioId']) && $_GET['usuarioId'] !== '' ? (int)$_GET['usuarioId'] : null;
        $rol = $_GET['rol'] ?? null;

        $filter = new AdminReservaFilter(
            $estado,
            $tipoEspacio,
            $facultadId,
            $escuelaId,
            $fecha,
            $search,
            $usuarioId,
            $rol
        );

        $response = $this->adminReservaService->obtenerReservas($filter);
        
        echo json_encode($response);
    }

    public function obtenerFiltros(): void {
        $usuarioId = isset($_GET['usuarioId']) && $_GET['usuarioId'] !== '' ? (int)$_GET['usuarioId'] : null;
        $rol = $_GET['rol'] ?? null;

        $response = $this->adminReservaService->obtenerFiltros($usuarioId, $rol);
        
        echo json_encode($response);
    }

    public function gestionarReserva(string $reservaIdRaw): void {
        $reservaId = (int)$reservaIdRaw;
        
        // Obtener el cuerpo de la petición JSON
        $inputRaw = file_get_contents('php://input');
        $body = json_decode($inputRaw, true);

        if ($body === null) {
            throw new HttpException("Cuerpo de petición no es un JSON válido.", 400);
        }

        $usuarioGestionId = isset($body['usuarioGestionId']) && $body['usuarioGestionId'] !== '' ? (int)$body['usuarioGestionId'] : null;
        $accion = $body['accion'] ?? null;
        $motivo = $body['motivo'] ?? null;
        $comentarios = $body['comentarios'] ?? null;

        // Validaciones correspondientes a las anotaciones de Java
        if ($usuarioGestionId === null) {
            throw new HttpException("El identificador del usuario gestor es obligatorio", 400);
        }

        if ($accion === null || trim($accion) === '') {
            throw new HttpException("La acción a ejecutar es obligatoria", 400);
        }

        if ($motivo === null || trim($motivo) === '') {
            throw new HttpException("Debes registrar un motivo para la gestión realizada", 400);
        }

        if (mb_strlen($motivo) > 1000) {
            throw new HttpException("El motivo no debe superar los 1000 caracteres", 400);
        }

        if ($comentarios !== null && mb_strlen($comentarios) > 1000) {
            throw new HttpException("Los comentarios no deben superar los 1000 caracteres", 400);
        }

        $request = new AdminGestionReservaRequest(
            $usuarioGestionId,
            $accion,
            $motivo,
            $comentarios
        );

        $response = $this->adminReservaService->gestionarReserva($reservaId, $request);

        echo json_encode($response);
    }
}
