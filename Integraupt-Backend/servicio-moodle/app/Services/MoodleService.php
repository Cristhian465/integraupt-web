<?php

namespace App\Services;

use App\Models\MoodleToken;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class MoodleService
{
    private const URL_SCHEME = 'web+integraupt';

    private string $baseUrl;
    private string $service;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.moodle.base_url'), '/');
        $this->service = (string) config('services.moodle.service');
    }

    public function conectarCuenta(int $usuarioId, string $username, string $password): array
    {
        $username = trim($username);

        if ($username === '' || trim($password) === '') {
            throw new InvalidArgumentException('Usuario y contraseña del Aula Virtual son obligatorios.');
        }

        $tokenResponse = Http::asForm()->post("{$this->baseUrl}/login/token.php", [
            'username' => $username,
            'password' => $password,
            'service' => $this->service,
        ])->json();

        if (!is_array($tokenResponse) || isset($tokenResponse['error'])) {
            throw new InvalidArgumentException($this->traducirError($tokenResponse['error'] ?? 'No se pudo conectar con el Aula Virtual.'));
        }

        $token = $tokenResponse['token'] ?? null;

        if (!$token) {
            throw new RuntimeException('No se pudo obtener un token del Aula Virtual.');
        }

        return $this->persistirToken($usuarioId, $token, $tokenResponse['privatetoken'] ?? null, $username);
    }

    public function generarPassport(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function construirEnlaceSso(string $passport): string
    {
        $query = http_build_query([
            'service' => $this->service,
            'passport' => $passport,
            'urlscheme' => self::URL_SCHEME,
        ]);

        return "{$this->baseUrl}/admin/tool/mobile/launch.php?{$query}";
    }

    public function confirmarSso(int $usuarioId, string $passport, string $siteid, string $token, ?string $privateToken): array
    {
        $esperado = md5($this->baseUrl . $passport);

        if (!hash_equals($esperado, $siteid)) {
            throw new InvalidArgumentException('No se pudo validar la conexión con el Aula Virtual. Intenta nuevamente.');
        }

        return $this->persistirToken($usuarioId, $token, $privateToken);
    }

    private function persistirToken(int $usuarioId, string $token, ?string $privateToken, ?string $usernameFallback = null): array
    {
        $siteInfo = $this->llamarFuncion($token, 'core_webservice_get_site_info');

        if (!is_array($siteInfo) || isset($siteInfo['exception'])) {
            throw new RuntimeException('El token obtenido no es válido para consultar el Aula Virtual.');
        }

        MoodleToken::updateOrCreate(
            ['Estudiante' => $usuarioId],
            [
                'MoodleUserId' => $siteInfo['userid'] ?? 0,
                'MoodleUsername' => $siteInfo['username'] ?? $usernameFallback,
                'Token' => $token,
                'PrivateToken' => $privateToken,
                'FechaConexion' => now(),
                'FechaActualizacion' => now(),
            ]
        );

        return [
            'conectado' => true,
            'nombreCompleto' => $siteInfo['fullname'] ?? null,
            'sitio' => $siteInfo['sitename'] ?? null,
        ];
    }

    public function obtenerEstado(int $usuarioId): array
    {
        $registro = MoodleToken::where('Estudiante', $usuarioId)->first();

        if (!$registro) {
            return ['conectado' => false];
        }

        return [
            'conectado' => true,
            'moodleUsername' => $registro->MoodleUsername,
            'fechaConexion' => $registro->FechaConexion?->format('Y-m-d\TH:i:s'),
        ];
    }

    public function desconectarCuenta(int $usuarioId): void
    {
        MoodleToken::where('Estudiante', $usuarioId)->delete();
    }

    public function obtenerCursos(int $usuarioId): array
    {
        $registro = $this->obtenerTokenOFallar($usuarioId);

        $cursos = $this->llamarFuncion($registro->Token, 'core_enrol_get_users_courses', [
            'userid' => $registro->MoodleUserId,
        ]);

        $this->verificarRespuesta($cursos);

        return collect($cursos)
            ->map(fn ($curso) => [
                'id' => $curso['id'],
                'nombre' => $curso['fullname'] ?? $curso['shortname'] ?? 'Curso',
                'progreso' => $curso['progress'] ?? null,
                'fechaInicio' => isset($curso['startdate']) ? date('Y-m-d', $curso['startdate']) : null,
            ])
            ->all();
    }

    public function obtenerNotas(int $usuarioId, int $cursoId): array
    {
        $registro = $this->obtenerTokenOFallar($usuarioId);

        $respuesta = $this->llamarFuncion($registro->Token, 'gradereport_user_get_grade_items', [
            'courseid' => $cursoId,
            'userid' => $registro->MoodleUserId,
        ]);

        $this->verificarRespuesta($respuesta);

        $items = $respuesta['usergrades'][0]['gradeitems'] ?? [];

        return collect($items)
            ->filter(fn ($item) => !empty($item['itemname']))
            ->map(fn ($item) => [
                'nombre' => $item['itemname'],
                'calificacion' => $item['gradeformatted'] ?? null,
                'porcentaje' => $item['percentageformatted'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function obtenerEventosProximos(int $usuarioId): array
    {
        $registro = $this->obtenerTokenOFallar($usuarioId);

        $respuesta = $this->llamarFuncion($registro->Token, 'core_calendar_get_calendar_upcoming_view');

        $this->verificarRespuesta($respuesta);

        $eventos = $respuesta['events'] ?? [];

        return collect($eventos)
            ->map(fn ($evento) => [
                'id' => $evento['id'],
                'nombre' => $evento['name'] ?? 'Evento',
                'curso' => $evento['course']['fullname'] ?? null,
                'fecha' => isset($evento['timestart']) ? date('Y-m-d H:i:s', $evento['timestart']) : null,
            ])
            ->all();
    }

    private function obtenerTokenOFallar(int $usuarioId): MoodleToken
    {
        $registro = MoodleToken::where('Estudiante', $usuarioId)->first();

        if (!$registro) {
            throw new InvalidArgumentException('Primero debes conectar tu cuenta del Aula Virtual.');
        }

        return $registro;
    }

    private function llamarFuncion(string $token, string $function, array $params = []): mixed
    {
        $respuesta = Http::asForm()->post("{$this->baseUrl}/webservice/rest/server.php", array_merge([
            'wstoken' => $token,
            'wsfunction' => $function,
            'moodlewsrestformat' => 'json',
        ], $params));

        return $respuesta->json();
    }

    private function verificarRespuesta($respuesta): void
    {
        if (is_array($respuesta) && isset($respuesta['exception'])) {
            if (($respuesta['errorcode'] ?? null) === 'invalidtoken') {
                throw new RuntimeException('Tu conexión con el Aula Virtual expiró. Vuelve a conectar tu cuenta.');
            }

            throw new RuntimeException($respuesta['message'] ?? 'El Aula Virtual rechazó la solicitud.');
        }
    }

    private function traducirError(string $error): string
    {
        if (str_contains($error, 'Invalid login') || str_contains(mb_strtolower($error), 'contraseña')) {
            return 'Usuario o contraseña del Aula Virtual incorrectos.';
        }

        return $error;
    }
}
