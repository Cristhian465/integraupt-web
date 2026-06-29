<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EscribiendoController extends Controller
{
    /**
     * Una marca de "escribiendo" se considera vigente solo unos segundos;
     * si el cliente deja de hacer ping, desaparece sin necesidad de borrarla.
     */
    private const VIGENCIA_SEGUNDOS = 6;

    public function marcar(Request $request, $idCanal)
    {
        $request->validate([
            'usuarioId' => 'required|integer|exists:usuario,IdUsuario',
            'temaId' => 'nullable|integer',
        ]);

        DB::table('canal_escribiendo')->updateOrInsert(
            ['IdCanal' => (int) $idCanal, 'IdUsuario' => (int) $request->input('usuarioId')],
            ['IdTema' => $request->input('temaId'), 'ActualizadoEn' => now()]
        );

        return response()->json(['ok' => true]);
    }

    public function listar(Request $request, $idCanal)
    {
        $usuarioId = (int) $request->query('usuarioId');
        $temaId = $request->query('temaId') !== null ? (int) $request->query('temaId') : null;
        $limite = now()->subSeconds(self::VIGENCIA_SEGUNDOS);

        $nombres = DB::table('canal_escribiendo')
            ->join('usuario', 'usuario.IdUsuario', '=', 'canal_escribiendo.IdUsuario')
            ->where('canal_escribiendo.IdCanal', (int) $idCanal)
            ->where('canal_escribiendo.IdUsuario', '!=', $usuarioId)
            ->where('canal_escribiendo.ActualizadoEn', '>=', $limite)
            ->where(function ($q) use ($temaId) {
                $q->where('canal_escribiendo.IdTema', $temaId)
                    ->orWhereNull('canal_escribiendo.IdTema');
            })
            ->selectRaw("TRIM(CONCAT(usuario.Nombre, ' ', usuario.Apellido)) AS nombre")
            ->pluck('nombre');

        return response()->json(['usuarios' => $nombres->all()]);
    }
}
