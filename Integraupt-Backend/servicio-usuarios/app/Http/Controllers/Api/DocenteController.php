<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Usuario;
use App\Models\UsuarioAuth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DocenteController extends Controller
{
    public function index(): JsonResponse
    {
        $docentes = Docente::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela.facultad'])->get();
        return response()->json($docentes);
    }

    public function show(int $id): JsonResponse
    {
        $docente = Docente::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela.facultad'])->find($id);
        if (!$docente) {
            return response()->json(['error' => 'Docente no encontrado'], 404);
        }
        return response()->json($docente);
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
            'codigoDocente' => 'required|string',
            'tipoContrato' => 'required|string',
            'fechaIncorporacion' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'tipoDoc' => $request->idTipoDoc,
                'numDoc' => $request->numDoc,
                'rol' => 1, // Docente
                'celular' => $request->celular,
                'genero' => $request->genero,
                'estado' => 1,
            ]);

            UsuarioAuth::create([
                'idUsuario' => $usuario->IdUsuario,
                'correoU' => $request->correo,
                'Password' => base64_encode($request->password), 
            ]);

            $docente = Docente::create([
                'idUsuario' => $usuario->IdUsuario,
                'Escuela' => $request->idEscuela,
                'codigoDocente' => $request->codigoDocente,
                'tipoContrato' => $request->tipoContrato,
                'especialidad' => $request->especialidad,
                'fechaIncorporacion' => $request->fechaIncorporacion,
            ]);

            DB::commit();
            return response()->json($this->show($docente->IdDocente)->original);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $docente = Docente::find($id);
        if (!$docente) {
            return response()->json(['error' => 'Docente no encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            $docente->update([
                'Escuela' => $request->idEscuela ?? $docente->Escuela,
                'codigoDocente' => $request->codigoDocente ?? $docente->codigoDocente,
                'tipoContrato' => $request->tipoContrato ?? $docente->tipoContrato,
                'especialidad' => $request->especialidad ?? $docente->especialidad,
                'fechaIncorporacion' => $request->fechaIncorporacion ?? $docente->fechaIncorporacion,
            ]);

            $usuario = Usuario::find($docente->IdUsuario);
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
        $docente = Docente::find($id);
        if (!$docente) {
            return response()->json(['error' => 'Docente no encontrado'], 404);
        }

        $usuario = Usuario::find($docente->IdUsuario);
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
