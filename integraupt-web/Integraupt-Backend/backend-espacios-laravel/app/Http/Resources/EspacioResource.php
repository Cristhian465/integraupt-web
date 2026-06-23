<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Reemplaza: EspacioResponse.java (DTO de salida)
 *
 * Estructura JSON idéntica al DTO Java para compatibilidad con
 * la interfaz TypeScript del frontend:
 *
 * export interface Espacio {
 *   id: number;
 *   codigo: string;
 *   nombre: string;
 *   tipo: string;
 *   capacidad: number;
 *   equipamiento?: string | null;
 *   estado: number;
 *   escuelaId: number;
 *   escuelaNombre?: string | null;
 * }
 */
class EspacioResource extends JsonResource
{
    /**
     * Desactiva el envolvimiento con "data" para mantener compatibilidad
     * con las respuestas directas de Spring Boot.
     */
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->IdEspacio,
            'codigo'         => $this->Codigo,
            'nombre'         => $this->Nombre,
            'tipo'           => $this->Tipo,
            'capacidad'      => (int) $this->Capacidad,
            'equipamiento'   => $this->Equipamiento,
            'estado'         => (int) $this->Estado,
            'escuelaId'      => $this->escuela ? (int) $this->escuela->IdEscuela : null,
            'escuelaNombre'  => $this->escuela ? $this->escuela->Nombre : null,
        ];
    }
}
