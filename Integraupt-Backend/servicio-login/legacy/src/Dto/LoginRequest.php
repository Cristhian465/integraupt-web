<?php

declare(strict_types=1);

namespace Dto;

class LoginRequest
{
    public ?string $codigoOEmail = null;
    public ?string $password     = null;
    public ?string $tipoLogin    = null;

    public static function fromArray(array $data): self
    {
        $req                = new self();
        $req->codigoOEmail  = $data['codigoOEmail'] ?? null;
        $req->password      = $data['password']     ?? null;
        $req->tipoLogin     = $data['tipoLogin']     ?? null;
        return $req;
    }

    public function identifier(): string
    {
        return $this->codigoOEmail !== null ? trim($this->codigoOEmail) : '';
    }

    public function normalizedTipoLogin(): string
    {
        return $this->tipoLogin !== null ? strtolower(trim($this->tipoLogin)) : '';
    }

    public function getPassword(): string
    {
        return $this->password !== null ? trim($this->password) : '';
    }
}
