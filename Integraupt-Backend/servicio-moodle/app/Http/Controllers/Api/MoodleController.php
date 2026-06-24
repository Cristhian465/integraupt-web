<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MoodleService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class MoodleController extends Controller
{
    public function __construct(private MoodleService $service)
    {
    }

    public function conectar(Request $request)
    {
        $usuarioId = (int) $request->input('usuarioId', 0);
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');

        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es obligatorio.'], 400);
        }

        try {
            $resultado = $this->service->conectarCuenta($usuarioId, $username, $password);
            return response()->json($resultado, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    public function iniciarSso(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es obligatorio.'], 400);
        }

        $passport = $this->service->generarPassport();

        return response()->json([
            'passport' => $passport,
            'launchUrl' => $this->service->construirEnlaceSso($passport),
        ]);
    }

    public function confirmarSso(Request $request)
    {
        $usuarioId = (int) $request->input('usuarioId', 0);
        $passport = (string) $request->input('passport', '');
        $siteid = (string) $request->input('siteid', '');
        $token = (string) $request->input('token', '');
        $privateToken = $request->input('privateToken');
        $privateToken = $privateToken !== null ? (string) $privateToken : null;

        if ($usuarioId < 1 || $passport === '' || $siteid === '' || $token === '') {
            return response()->json(['error' => 'Faltan parámetros para confirmar la conexión.'], 400);
        }

        try {
            $resultado = $this->service->confirmarSso($usuarioId, $passport, $siteid, $token, $privateToken);
            return response()->json($resultado, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    public function estado(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es obligatorio.'], 400);
        }

        return response()->json($this->service->obtenerEstado($usuarioId));
    }

    public function desconectar(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es obligatorio.'], 400);
        }

        $this->service->desconectarCuenta($usuarioId);

        return response()->json(['conectado' => false]);
    }

    public function cursos(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        try {
            return response()->json($this->service->obtenerCursos($usuarioId));
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    public function notas(Request $request, int $cursoId)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        try {
            return response()->json($this->service->obtenerNotas($usuarioId, $cursoId));
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    public function eventos(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        try {
            return response()->json($this->service->obtenerEventosProximos($usuarioId));
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }
}
