<?php

declare(strict_types=1);

namespace Dto;

class LoginResponse
{
    public bool            $success = false;
    public string          $message = '';
    public ?string         $token   = null;
    public ?PerfilResponse $perfil  = null;

    /** HTTP status code — NOT serialized to JSON */
    private int $httpStatus = 200;

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public static function success(string $message, string $token, PerfilResponse $perfil): self
    {
        $r             = new self();
        $r->success    = true;
        $r->message    = $message;
        $r->token      = $token;
        $r->perfil     = $perfil;
        $r->httpStatus = 200;
        return $r;
    }

    public static function failure(string $message, int $httpStatus = 400): self
    {
        $r             = new self();
        $r->success    = false;
        $r->message    = $message;
        $r->token      = null;
        $r->perfil     = null;
        $r->httpStatus = $httpStatus;
        return $r;
    }

    public function toArray(): array
    {
        $data = [
            'success' => $this->success,
            'message' => $this->message,
            'token'   => $this->token,
            'perfil'  => null,
        ];

        if ($this->perfil !== null) {
            $data['perfil'] = [
                'id'           => $this->perfil->id,
                'codigo'       => $this->perfil->codigo,
                'email'        => $this->perfil->email,
                'nombres'      => $this->perfil->nombres,
                'apellidos'    => $this->perfil->apellidos,
                'rol'          => $this->perfil->rol,
                'tipoLogin'    => $this->perfil->tipoLogin,
                'avatarUrl'    => $this->perfil->avatarUrl,
                'escuelaId'    => $this->perfil->escuelaId,
                'escuelaNombre'=> $this->perfil->escuelaNombre,
            ];
        }

        return $data;
    }
}
