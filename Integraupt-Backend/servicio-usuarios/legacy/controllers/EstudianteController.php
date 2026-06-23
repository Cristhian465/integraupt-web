<?php

namespace Controllers;

use Config\App;
use Services\Estudiante\EstudianteService;

class EstudianteController
{
    private EstudianteService $service;

    public function __construct()
    {
        $this->service = new EstudianteService();
    }

    /** GET /api/estudiantes */
    public function listar(): void
    {
        App::json($this->service->listar());
    }

    /** GET /api/estudiantes/{id} */
    public function obtener(int $id): void
    {
        try {
            App::json($this->service->obtener($id));
        } catch (\RuntimeException $e) {
            App::notFound($e->getMessage());
        }
    }

    /** POST /api/estudiantes */
    public function crear(): void
    {
        try {
            $body = App::getBody();
            $estudiante = $this->service->registrar($body);
            App::json($estudiante, 201);
        } catch (\RuntimeException $e) {
            App::error($e->getMessage());
        }
    }

    /** PUT /api/estudiantes/{id} */
    public function actualizar(int $id): void
    {
        try {
            $body = App::getBody();
            App::json($this->service->actualizar($id, $body));
        } catch (\RuntimeException $e) {
            App::error($e->getMessage());
        }
    }

    /** PATCH /api/estudiantes/{id}/estado */
    public function actualizarEstado(int $id): void
    {
        $body = App::getBody();
        if (!isset($body['activo'])) {
            App::error('El campo activo es requerido.');
        }
        try {
            App::json($this->service->actualizarEstado($id, (bool)$body['activo']));
        } catch (\RuntimeException $e) {
            App::notFound($e->getMessage());
        }
    }

    /** DELETE /api/estudiantes/{id} */
    public function eliminar(int $id): void
    {
        try {
            $this->service->eliminar($id);
            http_response_code(204);
        } catch (\RuntimeException $e) {
            App::notFound($e->getMessage());
        }
    }
}
