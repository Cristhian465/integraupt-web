<?php

namespace Models;

class Administrativo
{
    // Turnos válidos
    public const TURNOS = ['Mañana', 'Tarde', 'Noche', 'Completo'];

    public ?int    $idAdministrativo  = null;
    public int     $idUsuario;
    public ?int    $idEscuela         = null;
    public ?string $turno             = null;   // Mañana | Tarde | Noche | Completo
    public ?string $extension         = null;
    public string  $fechaIncorporacion;

    // Relaciones hidratadas
    public ?array  $usuario           = null;
    public ?array  $escuela           = null;

    public function toArray(): array
    {
        return [
            'idAdministrativo'  => $this->idAdministrativo,
            'usuario'           => $this->usuario,
            'escuela'           => $this->escuela,
            'turno'             => $this->turno,
            'extension'         => $this->extension,
            'fechaIncorporacion'=> $this->fechaIncorporacion,
        ];
    }

    public static function normalizarTurno(?string $valor): ?string
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }
        foreach (self::TURNOS as $turno) {
            if (strcasecmp(trim($valor), $turno) === 0) {
                return $turno;
            }
        }
        throw new \InvalidArgumentException("Turno no válido: $valor");
    }
}
