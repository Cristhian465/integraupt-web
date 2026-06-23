<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para Escuela.
 * Estructura JSON compatible con la interfaz TypeScript:
 *
 * export interface Escuela {
 *   id: number;
 *   nombre: string;
 *   facultadId: number;
 * }
 */
class EscuelaResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->IdEscuela,
            'nombre'     => $this->Nombre,
            'facultadId' => (int) $this->IdFacultad,
        ];
    }
}
