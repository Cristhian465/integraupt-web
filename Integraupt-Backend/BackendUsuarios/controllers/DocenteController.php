<?php

namespace Controllers;

use Config\App;
use Services\Docente\DocenteService;

class DocenteController
{
    private DocenteService $service;

    public function __construct()
    {
        $this->service = new DocenteService();
    }

    /** GET /api/docentes */
    public function listar(): void
    {
        App::json($this->service->listar());
    }

    /** GET /api/docentes/{id} */
    public function obtener(int $id): void
    {
        try {
            App::json($this->service->obtener($id));
        } catch (\RuntimeException $e) {
            App::notFound($e->getMessage());
        }
    }

    /** POST /api/docentes */
    public function crear(): void
    {
        try {
            $body = App::getBody();
            App::json($this->service->registrar($body), 201);
        } catch (\RuntimeException $e) {
            App::error($e->getMessage());
        }
    }

    /** PUT /api/docentes/{id} */
    public function actualizar(int $id): void
    {
        try {
            $body = App::getBody();
            App::json($this->service->actualizar($id, $body));
        } catch (\RuntimeException $e) {
            App::error($e->getMessage());
        }
    }

    /** PATCH /api/docentes/{id}/estado */
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

    /** DELETE /api/docentes/{id} */
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
