<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Administrativo;
use App\Models\Usuario;
use App\Models\UsuarioAuth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministrativoController extends Controller
{
    public function index(): JsonResponse
    {
        $administrativos = Administrativo::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela.facultad'])->get();
        return response()->json($administrativos);
    }

    public function show(int $id): JsonResponse
    {
        $administrativo = Administrativo::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela.facultad'])->find($id);
        if (!$administrativo) {
            return response()->json(['error' => 'Administrativo no encontrado'], 404);
        }
        return response()->json($administrativo);
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
            'turno' => 'required|string',
            'idRol' => 'required|integer',
            'fechaIncorporacion' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'Nombre' => $request->nombre,
                'Apellido' => $request->apellido,
                'TipoDoc' => $request->idTipoDoc,
                'NumDoc' => $request->numDoc,
                'Rol' => $request->idRol,
                'Celular' => $request->celular,
                'Genero' => $request->genero,
                'Estado' => 1,
            ]);

            UsuarioAuth::create([
                'idUsuario' => $usuario->IdUsuario,
                'correoU' => $request->correo,
                'Password' => base64_encode($request->password), 
            ]);

            $administrativo = Administrativo::create([
                'idUsuario' => $usuario->IdUsuario,
                'Escuela' => $request->idEscuela,
                'Turno' => $request->turno,
                'Extension' => $request->extension,
                'FechaIncorporacion' => $request->fechaIncorporacion,
            ]);

            DB::commit();
            return response()->json($this->show($administrativo->IdAdministrativo)->original);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $administrativo = Administrativo::find($id);
        if (!$administrativo) {
            return response()->json(['error' => 'Administrativo no encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            $administrativo->update([
                'Escuela' => $request->idEscuela ?? $administrativo->Escuela,
                'Turno' => $request->turno ?? $administrativo->Turno,
                'Extension' => $request->extension ?? $administrativo->Extension,
                'FechaIncorporacion' => $request->fechaIncorporacion ?? $administrativo->FechaIncorporacion,
            ]);

            $usuario = Usuario::find($administrativo->IdUsuario);
            if ($usuario) {
                $usuario->update([
                    'Nombre' => $request->nombre ?? $usuario->Nombre,
                    'Apellido' => $request->apellido ?? $usuario->Apellido,
                    'TipoDoc' => $request->idTipoDoc ?? $usuario->TipoDoc,
                    'NumDoc' => $request->numDoc ?? $usuario->NumDoc,
                    'Celular' => $request->celular ?? $usuario->Celular,
                    'Genero' => $request->genero ?? $usuario->Genero,
                    'Rol' => $request->idRol ?? $usuario->Rol,
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
        $administrativo = Administrativo::find($id);
        if (!$administrativo) {
            return response()->json(['error' => 'Administrativo no encontrado'], 404);
        }

        $usuario = Usuario::find($administrativo->IdUsuario);
        if ($usuario) {
            $usuario->update(['Estado' => $request->activo ? 1 : 0]);
        }
        
        return response()->json($this->show($id)->original);
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->estado(new Request(['activo' => false]), $id);
    }
}
