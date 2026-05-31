<?php

namespace Models;

class Docente
{
    // Tipos de contrato válidos
    public const TIPOS_CONTRATO = [
        'Tiempo Completo',
        'Medio Tiempo',
        'Contratado',
    ];

    public ?int    $idDocente          = null;
    public int     $idUsuario;
    public string  $codigoDocente;
    public ?int    $idEscuela          = null;
    public string  $tipoContrato;        // 'Tiempo Completo' | 'Medio Tiempo' | 'Contratado'
    public ?string $especialidad        = null;
    public string  $fechaIncorporacion;

    // Relaciones hidratadas
    public ?array  $usuario            = null;
    public ?array  $escuela            = null;

    public function toArray(): array
    {
        return [
            'idDocente'          => $this->idDocente,
            'usuario'            => $this->usuario,
            'codigoDocente'      => $this->codigoDocente,
            'escuela'            => $this->escuela,
            'tipoContrato'       => $this->tipoContrato,
            'especialidad'       => $this->especialidad,
            'fechaIncorporacion' => $this->fechaIncorporacion,
        ];
    }

    public static function normalizarTipoContrato(?string $valor): string
    {
        if ($valor === null || trim($valor) === '') {
            return '';
        }
        foreach (self::TIPOS_CONTRATO as $tipo) {
            if (strcasecmp(trim($valor), $tipo) === 0) {
                return $tipo;
            }
        }
        throw new \InvalidArgumentException("Tipo de contrato no válido: $valor");
    }
}
