<?php

declare(strict_types=1);

namespace Controller;

use Dto\LoginRequest;
use Dto\LogoutRequest;
use Dto\SessionValidationRequest;
use Service\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/login
     */
    public function login(): void
    {
        $body    = $this->getJsonBody();
        $request = LoginRequest::fromArray($body);
        $response = $this->authService->login($request);

        $this->json($response->toArray(), $response->getHttpStatus());
    }

    /**
     * POST /api/auth/validate
     */
    public function validate(): void
    {
        $body    = $this->getJsonBody();
        $request = SessionValidationRequest::fromArray($body);
        $response = $this->authService->validateSession($request);

        $this->json($response->toArray(), $response->getHttpStatus());
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        $body    = $this->getJsonBody();
        $request = LogoutRequest::fromArray($body);
        $this->authService->logout($request);

        http_response_code(204);
    }

    /**
     * GET /api/auth/health
     */
    public function health(): void
    {
        $this->json(['status' => 'Auth service is running'], 200);
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
