<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function buscar(Request $request)
    {
        $busqueda = trim((string) $request->query('search', ''));

        if ($busqueda === '') {
            return response()->json(['data' => []]);
        }

        $usuarios = Usuario::where('Nombre', 'like', "%{$busqueda}%")
            ->orWhere('Apellido', 'like', "%{$busqueda}%")
            ->orWhere('NumDoc', 'like', "%{$busqueda}%")
            ->limit(15)
            ->get();

        return response()->json([
            'data' => $usuarios->map(fn (Usuario $u) => [
                'id' => $u->IdUsuario,
                'nombre' => $u->NombreCompleto,
                'numDoc' => $u->NumDoc,
                'esAdmin' => $u->esAdmin(),
                'esDocente' => $u->esDocente(),
            ])->all(),
        ]);
    }
}
