<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioAuth;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private const SESSION_INACTIVITY_MINUTES = 20;
    private const ACADEMIC_ROLES = [1, 2];
    private const ADMINISTRATIVE_ROLES = [3, 4];

    public function login(Request $request): JsonResponse
    {
        $identifier = $request->input('codigoOEmail', '');
        $password = $request->input('password', '');
        $tipoLogin = strtolower(trim($request->input('tipoLogin', '')));

        if (!$identifier) return response()->json(['success' => false, 'message' => 'Debes ingresar tu código universitario o correo electrónico.'], 400);
        if (!$password) return response()->json(['success' => false, 'message' => 'Debes ingresar tu contraseña institucional.'], 400);
        if (!in_array($tipoLogin, ['academic', 'administrative'])) return response()->json(['success' => false, 'message' => 'El tipo de acceso seleccionado no es válido.'], 400);

        // Find by CorreoU or NumDoc
        $auth = UsuarioAuth::where('CorreoU', $identifier)
            ->orWhereHas('usuario', function ($q) use ($identifier) {
                $q->where('NumDoc', $identifier);
            })->first();

        if (!$auth) return response()->json(['success' => false, 'message' => 'No encontramos una cuenta vinculada a las credenciales.'], 401);

        $storedPassword = $auth->Password ?? '';
        if (base64_decode($storedPassword) !== $password) {
            return response()->json(['success' => false, 'message' => 'La contraseña es incorrecta. Intenta nuevamente.'], 401);
        }

        $usuario = $auth->usuario;
        if (!$usuario) return response()->json(['success' => false, 'message' => 'Cuenta sin perfil.'], 401);
        if (!$usuario->Rol) return response()->json(['success' => false, 'message' => 'Cuenta sin rol.'], 403);

        if ($tipoLogin === 'academic' && !in_array($usuario->Rol, self::ACADEMIC_ROLES)) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }
        if ($tipoLogin === 'administrative' && !in_array($usuario->Rol, self::ADMINISTRATIVE_ROLES)) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }
        if ($usuario->Estado != 1) return response()->json(['success' => false, 'message' => 'Cuenta inactiva.'], 403);

        // check session
        $now = now();
        if ($auth->SesionToken && $auth->SesionExpira && $auth->SesionExpira > $now) {
            return response()->json(['success' => false, 'message' => 'Tu cuenta ya tiene una sesión activa.'], 409);
        }

        $token = Str::uuid()->toString();
        $auth->update([
            'SesionToken' => $token,
            'SesionExpira' => $now->copy()->addMinutes(self::SESSION_INACTIVITY_MINUTES),
            'SesionTipo' => $tipoLogin,
            'UltimoLogin' => $now
        ]);

        $perfil = $this->buildProfile($usuario, $auth, $tipoLogin, $identifier);

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'perfil' => $perfil
        ]);
    }

    public function validateToken(Request $request): JsonResponse
    {
        $token = $request->input('token', '');
        if (!$token) return response()->json(['success' => false, 'message' => 'Token inválido'], 400);

        $auth = UsuarioAuth::where('SesionToken', $token)->first();
        if (!$auth || !$auth->SesionExpira || $auth->SesionExpira < now()) {
            if ($auth) $auth->update(['SesionToken' => null, 'SesionExpira' => null, 'SesionTipo' => null]);
            return response()->json(['success' => false, 'message' => 'Sesión expirada o inválida'], 401);
        }

        $usuario = $auth->usuario;
        if (!$usuario) return response()->json(['success' => false, 'message' => 'Cuenta sin perfil.'], 401);

        $auth->update([
            'SesionExpira' => now()->addMinutes(self::SESSION_INACTIVITY_MINUTES),
            'UltimoLogin' => now()
        ]);

        $perfil = $this->buildProfile($usuario, $auth, $auth->SesionTipo, $usuario->NumDoc);

        return response()->json([
            'success' => true,
            'message' => 'Sesión válida',
            'token' => $auth->SesionToken,
            'perfil' => $perfil
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $auth = UsuarioAuth::where('IdUsuario', $request->input('usuarioId'))->first();
        if ($auth) {
            $auth->update([
                'SesionToken' => null,
                'SesionExpira' => null,
                'SesionTipo' => null
            ]);
        }
        return response()->json([], 204);
    }

    public function health(): JsonResponse
    {
        return response()->json(['status' => 'Auth service is running in Laravel']);
    }

    private function buildProfile($usuario, $auth, $loginType, $identifier)
    {
        $codigo = $usuario->NumDoc;
        $escuelaId = null;
        $escuelaNombre = null;

        if ($usuario->Rol == 2 && $usuario->estudiante) {
            $codigo = $usuario->estudiante->Codigo ?? $codigo;
            $escuelaId = $usuario->estudiante->Escuela;
            $escuelaNombre = $usuario->estudiante->escuela->Nombre ?? null;
        } elseif (in_array($usuario->Rol, self::ADMINISTRATIVE_ROLES) && $usuario->administrativo) {
            $escuelaId = $usuario->administrativo->Escuela;
            $escuelaNombre = $usuario->administrativo->escuela->Nombre ?? null;
        }

        return [
            'id' => (string) $usuario->IdUsuario,
            'codigo' => $codigo,
            'email' => $auth->CorreoU,
            'nombres' => $usuario->Nombre,
            'apellidos' => $usuario->Apellido,
            'rol' => $usuario->rolObj->Nombre ?? null,
            'tipoLogin' => $loginType,
            'avatarUrl' => null,
            'escuelaId' => $escuelaId,
            'escuelaNombre' => $escuelaNombre
        ];
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $auth = UsuarioAuth::where('CorreoU', $googleUser->email)->first();
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            
            if (!$auth || !$auth->usuario || $auth->usuario->Estado != 1) {
                return redirect()->away($frontendUrl . '/?error=unauthorized_google');
            }

            $now = now();
            $token = Str::uuid()->toString();
            
            $tipoLogin = in_array($auth->usuario->Rol, self::ACADEMIC_ROLES) ? 'academic' : 'administrative';

            $auth->update([
                'SesionToken' => $token,
                'SesionExpira' => $now->copy()->addMinutes(self::SESSION_INACTIVITY_MINUTES),
                'SesionTipo' => $tipoLogin,
                'UltimoLogin' => $now
            ]);

            return redirect()->away($frontendUrl . '/?token=' . $token);

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->away($frontendUrl . '/?error=google_auth_failed');
        }
    }
}
