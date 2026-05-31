<?php

namespace Controllers;

use Config\App;
use Services\CatalogoService;

class CatalogoController
{
    private CatalogoService $service;

    public function __construct()
    {
        $this->service = new CatalogoService();
    }

    /** GET /api/catalogos/tipos-documento */
    public function tiposDocumento(): void
    {
        App::json($this->service->tiposDocumento());
    }

    /** GET /api/catalogos/roles */
    public function roles(): void
    {
        App::json($this->service->roles());
    }

    /** GET /api/catalogos/escuelas */
    public function escuelas(): void
    {
        App::json($this->service->escuelas());
    }
}
