<?php

namespace App\Http\Controllers;

use App\Models\EventoCertificado;

class CertificadoController extends Controller
{
    public function verificar($idInscripcion)
    {
        $certificado = EventoCertificado::with('inscripcion.usuario', 'inscripcion.evento')
            ->where('IdInscripcion', $idInscripcion)
            ->first();

        if (! $certificado) {
            return response()->json(['valido' => false, 'message' => 'Certificado no encontrado.'], 404);
        }

        $inscripcion = $certificado->inscripcion;
        $evento = $inscripcion?->evento;

        return response()->json([
            'valido' => true,
            'certificadoId' => $certificado->IdCertificado,
            'fechaEmision' => optional($certificado->FechaEmision)->toIso8601String(),
            'usuarioNombre' => $inscripcion?->usuario?->NombreCompleto,
            'eventoTitulo' => $evento?->Titulo,
            'eventoFechaInicio' => optional($evento?->FechaInicio)->toIso8601String(),
            'eventoFechaFin' => optional($evento?->FechaFin)->toIso8601String(),
        ]);
    }
}
