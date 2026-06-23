<?php

namespace App\Enums;

/**
 * Replica del enum Java: com.horarios.model.DiaSemana
 */
enum DiaSemana: string
{
    case LUNES = 'Lunes';
    case MARTES = 'Martes';
    case MIERCOLES = 'Miercoles';
    case JUEVES = 'Jueves';
    case VIERNES = 'Viernes';
    case SABADO = 'Sabado';

    /**
     * Busca un DiaSemana por nombre (case-insensitive).
     * Replica: DiaSemana.fromNombre(String nombre)
     */
    public static function fromNombre(string $nombre): self
    {
        foreach (self::cases() as $dia) {
            if (strcasecmp($dia->value, $nombre) === 0) {
                return $dia;
            }
        }
        throw new \InvalidArgumentException("Día de la semana no válido: {$nombre}");
    }

    /**
     * Retorna todos los días en orden.
     */
    public static function ordered(): array
    {
        return [
            self::LUNES,
            self::MARTES,
            self::MIERCOLES,
            self::JUEVES,
            self::VIERNES,
            self::SABADO,
        ];
    }
}
