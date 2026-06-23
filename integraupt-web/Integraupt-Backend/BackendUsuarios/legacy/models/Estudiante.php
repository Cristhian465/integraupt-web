<?php

namespace Models;

class Estudiante
{
    public ?int    $idEstudiante = null;
    public int     $idUsuario;
    public ?int    $idEscuela   = null;
    public string  $codigo;

    // Relaciones hidratadas
    public ?array  $usuario     = null;
    public ?array  $escuela     = null;

    public function toArray(): array
    {
        return [
            'idEstudiante' => $this->idEstudiante,
            'usuario'      => $this->usuario,
            'escuela'      => $this->escuela,
            'codigo'       => $this->codigo,
        ];
    }
}
