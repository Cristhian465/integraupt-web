<?php

namespace App\Services;

use App\Repositories\CatalogoRepository;

class CatalogoService
{
    public function __construct(protected CatalogoRepository $repository) {}

    public function listarCursos(): array
    {
        return $this->repository->listarCursos();
    }

    public function listarDocentes(): array
    {
        return $this->repository->listarDocentes();
    }

    public function listarEspacios(): array
    {
        return $this->repository->listarEspacios();
    }

    public function listarBloques(): array
    {
        return $this->repository->listarBloques();
    }

    public function obtenerMeta(): array
    {
        return [
            'cursos' => $this->listarCursos(),
            'docentes' => $this->listarDocentes(),
            'espacios' => $this->listarEspacios(),
            'bloques' => $this->listarBloques()
        ];
    }
}
