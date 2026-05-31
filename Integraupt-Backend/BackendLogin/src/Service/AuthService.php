<?php

declare(strict_types=1);

namespace Service;

use Dto\LoginRequest;
use Dto\LoginResponse;
use Dto\LogoutRequest;
use Dto\PerfilResponse;
use Dto\SessionValidationRequest;
use Repository\AdministrativoRepository;
use Repository\EstudianteRepository;
use Repository\UsuarioAuthRepository;

class AuthService
{
    private const SESSION_INACTIVITY_MINUTES = 20;

    /** Roles: 1 = Docente, 2 = Estudiante */
    private const ACADEMIC_ROLES = [1, 2];

    /** Roles: 3 = Administrativo, 4 = Director/Admin */
    private const ADMINISTRATIVE_ROLES = [3, 4];

    private UsuarioAuthRepository  $authRepository;
    private EstudianteRepository   $estudianteRepository;
    private AdministrativoRepository $administrativoRepository;

    public function __construct()
    {
        $this->authRepository           = new UsuarioAuthRepository();
        $this->estudianteRepository     = new EstudianteRepository();
        $this->administrativoRepository = new AdministrativoRepository();
    }

    // ------------------------------------------------------------------ //
    //  LOGIN
    // ------------------------------------------------------------------ //

    public function login(LoginRequest $request): LoginResponse
    {
        $identifier = $request->identifier();
        $password   = $request->getPassword();
        $tipoLogin  = $request->normalizedTipoLogin();

        if ($identifier === '') {
            return LoginResponse::failure(
                'Debes ingresar tu código universitario o correo electrónico.',
                400
            );
        }

        if ($password === '') {
            return LoginResponse::failure('Debes ingresar tu contraseña institucional.', 400);
        }

        if ($tipoLogin === '') {
            return LoginResponse::failure('Debes seleccionar un tipo de acceso válido.', 400);
        }

        $isAcademicLogin       = $tipoLogin === 'academic';
        $isAdministrativeLogin = $tipoLogin === 'administrative';

        if (!$isAcademicLogin && !$isAdministrativeLogin) {
            return LoginResponse::failure('El tipo de acceso seleccionado no es válido.', 400);
        }

        $auth = $this->authRepository->findByCorreoUOrNumDoc($identifier, $identifier);

        if ($auth === null) {
            return LoginResponse::failure(
                'No encontramos una cuenta vinculada a las credenciales proporcionadas.',
                401
            );
        }

        // Validate stored password (Base64 encoded in DB, same as Java logic)
        $storedPasswordRaw = $auth['Password'] ?? '';
        if ($storedPasswordRaw === '') {
            return LoginResponse::failure(
                'Las credenciales almacenadas son inválidas. Contacta al administrador.',
                500
            );
        }

        $decoded = base64_decode($storedPasswordRaw, true);
        if ($decoded === false) {
            return LoginResponse::failure(
                'No fue posible validar tus credenciales. Contacta al administrador.',
                500
            );
        }

        if ($decoded !== $password) {
            return LoginResponse::failure('La contraseña es incorrecta. Intenta nuevamente.', 401);
        }

        // Check usuario
        $rolId   = isset($auth['r_IdRol']) ? (int) $auth['r_IdRol'] : null;
        $estado  = isset($auth['u_Estado']) ? (int) $auth['u_Estado'] : null;

        if ($auth['u_IdUsuario'] === null) {
            return LoginResponse::failure(
                'Tu cuenta no tiene un perfil asociado. Contacta al administrador.',
                401
            );
        }

        if ($rolId === null) {
            return LoginResponse::failure(
                'Tu cuenta no tiene un rol asignado. Contacta al administrador.',
                403
            );
        }

        if ($isAcademicLogin && !in_array($rolId, self::ACADEMIC_ROLES, true)) {
            return LoginResponse::failure(
                'Acceso denegado. El portal académico es exclusivo para estudiantes y docentes.',
                403
            );
        }

        if ($isAdministrativeLogin && !in_array($rolId, self::ADMINISTRATIVE_ROLES, true)) {
            return LoginResponse::failure(
                'Acceso denegado. El portal administrativo es exclusivo para personal autorizado.',
                403
            );
        }

        if ($estado !== 1) {
            return LoginResponse::failure(
                'Tu cuenta se encuentra inactiva. Contacta al administrador.',
                403
            );
        }

        // Check active session
        $hadSessionData = ($auth['SesionToken'] ?? '') !== '';

        if ($this->isSessionActive($auth)) {
            return LoginResponse::failure(
                'Tu cuenta ya tiene una sesión activa. Cierra la sesión anterior antes de iniciar una nueva.',
                409
            );
        }

        // Session expired — clear stale data if needed
        if ($hadSessionData && ($auth['SesionToken'] ?? '') === '') {
            $this->authRepository->clearSession((int) $auth['IdAuth']);
        }

        $loginType = $isAcademicLogin ? 'academic' : 'administrative';
        $perfil    = $this->construirPerfil($auth, $loginType, $rolId, $identifier);

        if ($perfil === null) {
            return LoginResponse::failure(
                'No fue posible construir tu perfil. Contacta al administrador.',
                500
            );
        }

        $token   = $this->iniciarNuevaSesion((int) $auth['IdAuth'], $loginType);
        $message = $isAcademicLogin ? 'Login académico exitoso' : 'Login administrativo exitoso';

        return LoginResponse::success($message, $token, $perfil);
    }

    // ------------------------------------------------------------------ //
    //  VALIDATE SESSION
    // ------------------------------------------------------------------ //

    public function validateSession(SessionValidationRequest $request): LoginResponse
    {
        $token = $request->normalizedToken();

        if ($token === '') {
            return LoginResponse::failure('Debes proporcionar un token de sesión válido.', 400);
        }

        $auth = $this->authRepository->findBySesionToken($token);

        if ($auth === null) {
            return LoginResponse::failure('La sesión no es válida o ha expirado.', 401);
        }

        $hadSessionData = ($auth['SesionToken'] ?? '') !== '';

        if (!$this->isSessionActive($auth)) {
            if ($hadSessionData) {
                $this->authRepository->clearSession((int) $auth['IdAuth']);
            }
            return LoginResponse::failure('La sesión ha expirado. Inicia sesión nuevamente.', 401);
        }

        $rolId = isset($auth['r_IdRol']) ? (int) $auth['r_IdRol'] : null;

        if ($auth['u_IdUsuario'] === null) {
            $this->authRepository->clearSession((int) $auth['IdAuth']);
            return LoginResponse::failure(
                'Tu cuenta no tiene un perfil asociado. Contacta al administrador.',
                401
            );
        }

        if ($rolId === null) {
            $this->authRepository->clearSession((int) $auth['IdAuth']);
            return LoginResponse::failure(
                'Tu cuenta no tiene un rol asignado. Contacta al administrador.',
                403
            );
        }

        $storedLoginType       = strtolower(trim($auth['SesionTipo'] ?? ''));
        $isAcademicLogin       = $storedLoginType === 'academic';
        $isAdministrativeLogin = $storedLoginType === 'administrative';

        if (!$isAcademicLogin && !$isAdministrativeLogin) {
            $this->authRepository->clearSession((int) $auth['IdAuth']);
            return LoginResponse::failure(
                'La sesión almacenada es inválida. Inicia sesión nuevamente.',
                401
            );
        }

        if ($isAcademicLogin && !in_array($rolId, self::ACADEMIC_ROLES, true)) {
            $this->authRepository->clearSession((int) $auth['IdAuth']);
            return LoginResponse::failure('Acceso denegado para el portal académico.', 403);
        }

        if ($isAdministrativeLogin && !in_array($rolId, self::ADMINISTRATIVE_ROLES, true)) {
            $this->authRepository->clearSession((int) $auth['IdAuth']);
            return LoginResponse::failure('Acceso denegado para el portal administrativo.', 403);
        }

        $loginType = $isAcademicLogin ? 'academic' : 'administrative';
        $perfil    = $this->construirPerfil($auth, $loginType, $rolId, $auth['u_NumDoc'] ?? '');

        if ($perfil === null) {
            $this->authRepository->clearSession((int) $auth['IdAuth']);
            return LoginResponse::failure(
                'No fue posible reconstruir tu perfil. Inicia sesión nuevamente.',
                500
            );
        }

        // Refresh expiry on valid session
        $now    = new \DateTime();
        $expira = (clone $now)->modify('+' . self::SESSION_INACTIVITY_MINUTES . ' minutes');

        $this->authRepository->updateSession(
            (int) $auth['IdAuth'],
            $auth['SesionToken'],
            $expira->format('Y-m-d H:i:s'),
            $auth['SesionTipo'],
            $now->format('Y-m-d H:i:s')
        );

        return LoginResponse::success('Sesión válida', $auth['SesionToken'], $perfil);
    }

    // ------------------------------------------------------------------ //
    //  LOGOUT
    // ------------------------------------------------------------------ //

    public function logout(LogoutRequest $request): void
    {
        if ($request->usuarioId === null) {
            return;
        }

        $auth = $this->authRepository->findByUsuarioId($request->usuarioId);

        if ($auth === null) {
            return;
        }

        $providedToken = $request->normalizedToken();
        $storedToken   = $auth['SesionToken'] ?? '';

        // If token is provided but doesn't match, do nothing
        if ($providedToken !== '' && $storedToken !== '' && $storedToken !== $providedToken) {
            return;
        }

        $this->authRepository->clearSession((int) $auth['IdAuth']);
    }

    // ------------------------------------------------------------------ //
    //  PRIVATE HELPERS
    // ------------------------------------------------------------------ //

    private function isSessionActive(array $auth): bool
    {
        $token   = $auth['SesionToken'] ?? '';
        $expira  = $auth['SesionExpira'] ?? '';

        if ($token === '') {
            // Implicitly clear if stale data detected — caller handles DB update
            $auth['SesionToken']  = null;
            $auth['SesionExpira'] = null;
            $auth['SesionTipo']   = null;
            return false;
        }

        if ($expira === '' || $expira === null) {
            $auth['SesionToken']  = null;
            $auth['SesionExpira'] = null;
            $auth['SesionTipo']   = null;
            return false;
        }

        $expiresAt = new \DateTime($expira);
        $now       = new \DateTime();

        if ($expiresAt > $now) {
            return true;
        }

        // Expired — signal caller to clear
        $auth['SesionToken']  = null;
        $auth['SesionExpira'] = null;
        $auth['SesionTipo']   = null;
        return false;
    }

    private function construirPerfil(array $auth, string $loginType, int $rolId, string $identifier): ?PerfilResponse
    {
        if ($loginType === '') {
            return null;
        }

        $perfil               = new PerfilResponse();
        $perfil->id           = isset($auth['u_IdUsuario']) ? (string) $auth['u_IdUsuario'] : null;
        $perfil->codigo       = $auth['u_NumDoc']    ?? null;
        $perfil->email        = $auth['CorreoU']     ?? null;
        $perfil->nombres      = $auth['u_Nombre']    ?? null;
        $perfil->apellidos    = $auth['u_Apellido']  ?? null;
        $perfil->rol          = $auth['r_Nombre']    ?? null;
        $perfil->tipoLogin    = $loginType;
        $perfil->avatarUrl    = null;

        $usuarioId = isset($auth['u_IdUsuario']) ? (int) $auth['u_IdUsuario'] : null;

        // Enrich for Estudiante (rolId = 2)
        if ($rolId === 2 && $usuarioId !== null) {
            $estudiante = $this->buscarEstudiante($usuarioId, $identifier);
            if ($estudiante !== null) {
                if (($estudiante['Codigo'] ?? '') !== '') {
                    $perfil->codigo = $estudiante['Codigo'];
                }
                if (($estudiante['escuela_IdEscuela'] ?? null) !== null) {
                    $perfil->escuelaId = (int) $estudiante['escuela_IdEscuela'];
                }
                if (($estudiante['escuela_Nombre'] ?? '') !== '') {
                    $perfil->escuelaNombre = $estudiante['escuela_Nombre'];
                }
            }
        }

        // Enrich for Administrativo roles
        if (in_array($rolId, self::ADMINISTRATIVE_ROLES, true) && $usuarioId !== null) {
            $administrativo = $this->administrativoRepository->findByUsuarioId($usuarioId);
            if ($administrativo !== null) {
                if (($administrativo['escuela_IdEscuela'] ?? null) !== null) {
                    $perfil->escuelaId = (int) $administrativo['escuela_IdEscuela'];
                }
                if (($administrativo['escuela_Nombre'] ?? '') !== '') {
                    $perfil->escuelaNombre = $administrativo['escuela_Nombre'];
                }
            }
        }

        if (($perfil->codigo ?? '') === '') {
            $perfil->codigo = $auth['u_NumDoc'] ?? null;
        }

        return $perfil;
    }

    private function iniciarNuevaSesion(int $idAuth, string $loginType): string
    {
        $token  = (string) \Ramsey\Uuid\Uuid::uuid4();
        // Fallback if ramsey/uuid not installed — use built-in PHP random
        if (!class_exists('Ramsey\Uuid\Uuid')) {
            $token = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }

        $now    = new \DateTime();
        $expira = (clone $now)->modify('+' . self::SESSION_INACTIVITY_MINUTES . ' minutes');

        $this->authRepository->updateSession(
            $idAuth,
            $token,
            $expira->format('Y-m-d H:i:s'),
            $loginType,
            $now->format('Y-m-d H:i:s')
        );

        return $token;
    }

    private function buscarEstudiante(int $usuarioId, string $identifier): ?array
    {
        $estudiante = $this->estudianteRepository->findByUsuarioId($usuarioId);

        if ($estudiante !== null) {
            return $estudiante;
        }

        if (trim($identifier) !== '') {
            return $this->estudianteRepository->findByCodigo(trim($identifier));
        }

        return null;
    }
}
