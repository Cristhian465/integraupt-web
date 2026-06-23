<?php

declare(strict_types=1);

namespace Dto;

class PerfilResponse
{
    public ?string $id            = null;
    public ?string $codigo        = null;
    public ?string $email         = null;
    public ?string $nombres       = null;
    public ?string $apellidos     = null;
    public ?string $rol           = null;
    public ?string $tipoLogin     = null;
    public ?string $avatarUrl     = null;
    public ?int    $escuelaId     = null;
    public ?string $escuelaNombre = null;
}
