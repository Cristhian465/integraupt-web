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
        $administrativos = Administrativo::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela'])->get();
        return response()->json($administrativos);
    }

    public function show(int $id): JsonResponse
    {
        $administrativo = Administrativo::with(['usuario.tipoDocumento', 'usuario.auth', 'usuario.rolObj', 'escuela'])->find($id);
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
            'fechaIncorporacion' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'tipoDoc' => $request->idTipoDoc,
                'numDoc' => $request->numDoc,
                'rol' => 3, // Administrativo default
                'celular' => $request->celular,
                'genero' => $request->genero,
                'estado' => 1,
            ]);

            UsuarioAuth::create([
                'idUsuario' => $usuario->idUsuario,
                'correoU' => $request->correo,
                'passwordU' => base64_encode($request->password), 
            ]);

            $administrativo = Administrativo::create([
                'idUsuario' => $usuario->idUsuario,
                'idEscuela' => $request->idEscuela,
                'turno' => $request->turno,
                'extension' => $request->extension,
                'fechaIncorporacion' => $request->fechaIncorporacion,
            ]);

            DB::commit();
            return response()->json($this->show($administrativo->idAdministrativo)->original);
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
                'idEscuela' => $request->idEscuela ?? $administrativo->idEscuela,
                'turno' => $request->turno ?? $administrativo->turno,
                'extension' => $request->extension ?? $administrativo->extension,
                'fechaIncorporacion' => $request->fechaIncorporacion ?? $administrativo->fechaIncorporacion,
            ]);

            $usuario = Usuario::find($administrativo->idUsuario);
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
        $administrativo = Administrativo::find($id);
        if (!$administrativo) {
            return response()->json(['error' => 'Administrativo no encontrado'], 404);
        }

        $usuario = Usuario::find($administrativo->idUsuario);
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
