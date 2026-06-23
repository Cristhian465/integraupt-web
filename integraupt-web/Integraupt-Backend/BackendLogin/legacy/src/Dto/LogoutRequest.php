<?php

declare(strict_types=1);

namespace Dto;

class LogoutRequest
{
    public ?int    $usuarioId = null;
    public ?string $token     = null;

    public static function fromArray(array $data): self
    {
        $req             = new self();
        $req->usuarioId  = isset($data['usuarioId']) ? (int) $data['usuarioId'] : null;
        $req->token      = $data['token'] ?? null;
        return $req;
    }

    public function normalizedToken(): string
    {
        return $this->token !== null ? trim($this->token) : '';
    }
}
