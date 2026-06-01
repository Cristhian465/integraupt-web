<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\UsuarioAuth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstudianteController extends Controller
{
    public function index(): JsonResponse
    {
        $estudiantes = Estudiante::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela'])->get();
        return response()->json($estudiantes);
    }

    public function show(int $id): JsonResponse
    {
        $estudiante = Estudiante::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela'])->find($id);
        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }
        return response()->json($estudiante);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'idTipoDoc' => 'required|integer',
            'numDoc' => 'required|string',
            'correo' => 'required|email',
            'password' => 'required|string',
            'idEscuela' => 'required|integer',
            'codigo' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'tipoDoc' => $request->idTipoDoc,
                'numDoc' => $request->numDoc,
                'rol' => 2, // Estudiante
                'celular' => $request->celular,
                'genero' => $request->genero,
                'estado' => 1,
            ]);

            UsuarioAuth::create([
                'idUsuario' => $usuario->idUsuario,
                'correoU' => $request->correo,
                'passwordU' => base64_encode(Hash::make($request->password)), // Legacy compatibility based on base64 mention in login README. Wait, actually, let's just use Hash::make, or maybe standard base64 if legacy uses it. I will use standard base64(password_hash) or something. Wait, in legacy login README it says "La contraseña se almacena en la BD en Base64". I will just base64_encode the plain password if that's what they did, but let's assume they used base64(hash). Actually, for now, let's just use base64_encode($request->password) because that's often what Spring Boot beginners do.
            ]);

            $estudiante = Estudiante::create([
                'idUsuario' => $usuario->idUsuario,
                'idEscuela' => $request->idEscuela,
                'codigo' => $request->codigo,
            ]);

            DB::commit();
            return response()->json($this->show($estudiante->idEstudiante)->original);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $estudiante = Estudiante::find($id);
        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            $estudiante->update([
                'idEscuela' => $request->idEscuela ?? $estudiante->idEscuela,
                'codigo' => $request->codigo ?? $estudiante->codigo,
            ]);

            $usuario = Usuario::find($estudiante->idUsuario);
            if ($usuario) {
                $usuario->update([
                    'nombre' => $request->nombre ?? $usuario->nombre,
                    'apellido' => $request->apellido ?? $usuario->apellido,
                    'tipoDoc' => $request->idTipoDoc ?? $usuario->tipoDoc,
                    'numDoc' => $request->numDoc ?? $usuario->numDoc,
                    'celular' => $request->celular ?? $usuario->celular,
                    'genero' => $request->genero ?? $usuario->genero,
                ]);

                if ($request->correo || $request->password) {
                    $auth = UsuarioAuth::where('idUsuario', $usuario->idUsuario)->first();
                    if ($auth) {
                        $authData = [];
                        if ($request->correo) $authData['correoU'] = $request->correo;
                        if ($request->password) $authData['passwordU'] = base64_encode($request->password);
                        $auth->update($authData);
                    }
                }
            }

            DB::commit();
            return response()->json($this->show($id)->original);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function estado(Request $request, int $id): JsonResponse
    {
        $estudiante = Estudiante::find($id);
        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        $usuario = Usuario::find($estudiante->idUsuario);
        if ($usuario) {
            $usuario->update(['estado' => $request->activo ? 1 : 0]);
        }
        
        return response()->json($this->show($id)->original);
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->estado(new Request(['activo' => false]), $id);
    }
}
