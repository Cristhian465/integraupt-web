<?php

declare(strict_types=1);

namespace Dto;

class SessionValidationRequest
{
    public ?string $token = null;

    public static function fromArray(array $data): self
    {
        $req        = new self();
        $req->token = $data['token'] ?? null;
        return $req;
    }

    public function normalizedToken(): string
    {
        return $this->token !== null ? trim($this->token) : '';
    }
}
