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
                'idUsuario' => $usuario->IdUsuario,
                'correoU' => $request->correo,
                'Password' => base64_encode($request->password),
            ]);

            $estudiante = Estudiante::create([
                'idUsuario' => $usuario->IdUsuario,
                'Escuela' => $request->idEscuela,
                'codigo' => $request->codigo,
            ]);

            DB::commit();
            return response()->json($this->show($estudiante->IdEstudiante)->original);
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
                'Escuela' => $request->idEscuela ?? $estudiante->Escuela,
                'codigo' => $request->codigo ?? $estudiante->codigo,
            ]);

            $usuario = Usuario::find($estudiante->IdUsuario);
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
                    $auth = UsuarioAuth::where('idUsuario', $usuario->IdUsuario)->first();
                    if ($auth) {
                        $authData = [];
                        if ($request->correo) $authData['correoU'] = $request->correo;
                        if ($request->password) $authData['Password'] = base64_encode($request->password);
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

        $usuario = Usuario::find($estudiante->IdUsuario);
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
