<?php

namespace Routes;

use Config\App;
use Controllers\AdministrativoController;
use Controllers\CatalogoController;
use Controllers\DocenteController;
use Controllers\EstudianteController;

class Router
{
    private string $method;
    private array  $segments;  // partes de la URI tras /api/

    public function __construct()
    {
        $this->method   = $_SERVER['REQUEST_METHOD'];
        $uri            = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri            = rtrim($uri, '/');
        // quitar prefijo /api
        $path           = preg_replace('#^/api#', '', $uri);
        $this->segments = array_values(array_filter(explode('/', $path)));
    }

    public function dispatch(): void
    {
        $resource  = $this->segments[0] ?? '';
        $id        = isset($this->segments[1]) ? (int)$this->segments[1] : null;
        $subAction = $this->segments[2] ?? null;

        match ($resource) {
            'estudiantes'    => $this->routeEstudiantes($id, $subAction),
            'docentes'       => $this->routeDocentes($id, $subAction),
            'administrativos'=> $this->routeAdministrativos($id, $subAction),
            'catalogos'      => $this->routeCatalogos($id),
            default          => App::notFound("Ruta no encontrada: /$resource"),
        };
    }

    // ──────────────────────────────────────────────
    // Rutas por recurso
    // ──────────────────────────────────────────────

    private function routeEstudiantes(?int $id, ?string $sub): void
    {
        $ctrl = new EstudianteController();
        match (true) {
            $this->method === 'GET'   && $id === null            => $ctrl->listar(),
            $this->method === 'GET'   && $id !== null            => $ctrl->obtener($id),
            $this->method === 'POST'  && $id === null            => $ctrl->crear(),
            $this->method === 'PUT'   && $id !== null            => $ctrl->actualizar($id),
            $this->method === 'PATCH' && $id !== null && $sub === 'estado' => $ctrl->actualizarEstado($id),
            $this->method === 'DELETE'&& $id !== null            => $ctrl->eliminar($id),
            default => App::notFound('Método o ruta de estudiante no válida.'),
        };
    }

    private function routeDocentes(?int $id, ?string $sub): void
    {
        $ctrl = new DocenteController();
        match (true) {
            $this->method === 'GET'   && $id === null            => $ctrl->listar(),
            $this->method === 'GET'   && $id !== null            => $ctrl->obtener($id),
            $this->method === 'POST'  && $id === null            => $ctrl->crear(),
            $this->method === 'PUT'   && $id !== null            => $ctrl->actualizar($id),
            $this->method === 'PATCH' && $id !== null && $sub === 'estado' => $ctrl->actualizarEstado($id),
            $this->method === 'DELETE'&& $id !== null            => $ctrl->eliminar($id),
            default => App::notFound('Método o ruta de docente no válida.'),
        };
    }

    private function routeAdministrativos(?int $id, ?string $sub): void
    {
        $ctrl = new AdministrativoController();
        match (true) {
            $this->method === 'GET'   && $id === null            => $ctrl->listar(),
            $this->method === 'GET'   && $id !== null            => $ctrl->obtener($id),
            $this->method === 'POST'  && $id === null            => $ctrl->crear(),
            $this->method === 'PUT'   && $id !== null            => $ctrl->actualizar($id),
            $this->method === 'PATCH' && $id !== null && $sub === 'estado' => $ctrl->actualizarEstado($id),
            $this->method === 'DELETE'&& $id !== null            => $ctrl->eliminar($id),
            default => App::notFound('Método o ruta de administrativo no válida.'),
        };
    }

    private function routeCatalogos(?int $id): void
    {
        // El "id" aquí es el sub-recurso: tipos-documento, roles, escuelas
        // Pero como usamos split('/')  el sub-recurso queda en $this->segments[1]
        $sub  = $this->segments[1] ?? null;
        $ctrl = new CatalogoController();

        if ($this->method !== 'GET') {
            App::error('Los catálogos solo admiten GET.', 405);
        }

        match ($sub) {
            'tipos-documento' => $ctrl->tiposDocumento(),
            'roles'           => $ctrl->roles(),
            'escuelas'        => $ctrl->escuelas(),
            default           => App::notFound("Catálogo '$sub' no encontrado."),
        };
    }
}
