<?php

namespace Models;

class Usuario
{
    public ?int    $idUsuario    = null;
    public string  $nombre;
    public string  $apellido;
    public int     $tipoDoc;       // FK → tipodocumento.IdTipoDoc
    public string  $numDoc;
    public int     $rol;           // FK → rol.IdRol
    public ?string $celular       = null;
    public ?bool   $genero        = null;
    public int     $estado        = 1;
    public ?string $fechaRegistro = null;

    // Relaciones hidratadas (para respuestas JSON)
    public ?array  $tipoDocObj    = null;
    public ?array  $rolObj        = null;
    public ?array  $auth          = null;

    public function toArray(): array
    {
        return [
            'idUsuario'     => $this->idUsuario,
            'nombre'        => $this->nombre,
            'apellido'      => $this->apellido,
            'tipoDoc'       => $this->tipoDocObj ?? ['idTipoDoc' => $this->tipoDoc],
            'numDoc'        => $this->numDoc,
            'rol'           => $this->rolObj    ?? ['idRol' => $this->rol],
            'celular'       => $this->celular,
            'genero'        => $this->genero,
            'estado'        => $this->estado,
            'fechaRegistro' => $this->fechaRegistro,
            'auth'          => $this->auth ? [
                'idAuth'      => $this->auth['idAuth'],
                'correoU'     => $this->auth['correoU'],
                'ultimoLogin' => $this->auth['ultimoLogin'] ?? null,
            ] : null,
        ];
    }
}
