<?php

namespace App\Services;

use App\Repositories\AuditoriaReservaRepository;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuditoriaService
{
    private $repository;

    public function __construct(AuditoriaReservaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buscar(array $filtro)
    {
        $estado = isset($filtro['estado']) && trim($filtro['estado']) !== '' ? strtoupper(trim($filtro['estado'])) : null;
        $terminoUsuario = isset($filtro['usuario']) && trim($filtro['usuario']) !== '' ? trim($filtro['usuario']) : null;

        $registros = $this->repository->buscarResumenes(
            $filtro['reservaId'] ?? null,
            $estado,
            $terminoUsuario,
            $filtro['fechaInicio'] ?? null,
            $filtro['fechaFin'] ?? null
        );

        return $this->mapearColeccion($registros);
    }

    public function listarPorReserva($reservaId)
    {
        $registros = $this->repository->listarPorReserva($reservaId);
        return $this->mapearColeccion($registros);
    }

    public function buscarPorId($idAudit)
    {
        $registro = $this->repository->obtenerDetalle($idAudit);

        if (!$registro) {
            throw new NotFoundHttpException('Auditoria no encontrada');
        }

        return $this->mapear($registro);
    }

    private function mapearColeccion($registros)
    {
        return collect($registros)->filter()->map(function ($registro) {
            return $this->mapear($registro);
        })->values()->all();
    }

    private function mapear($registro)
    {
        return [
            'idAudit' => $registro->idAudit,
            'idReserva' => $registro->idReserva,
            'estadoAnterior' => $registro->estadoAnterior,
            'estadoNuevo' => $registro->estadoNuevo,
            'fechaCambio' => $registro->fechaCambio,
            'usuarioCambioId' => $registro->usuarioCambioId,
            'usuarioCambioNombre' => $this->normalizarNombre($registro->usuarioCambioNombre),
            'usuarioCambioDocumento' => $registro->usuarioCambioDocumento,
            'estadoReservaActual' => $registro->estadoReservaActual,
            'descripcionUso' => $registro->descripcionUso,
            'fechaReserva' => $registro->fechaReserva,
            'codigoEspacio' => $registro->codigoEspacio,
            'nombreEspacio' => $registro->nombreEspacio,
        ];
    }

    private function normalizarNombre($nombre)
    {
        if ($nombre === null) {
            return null;
        }
        return preg_replace('/\s{2,}/', ' ', trim($nombre));
    }
}
