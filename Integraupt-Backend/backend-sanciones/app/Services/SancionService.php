<?php

namespace App\Services;

use App\Models\Sancion;
use App\Models\Usuario;
use App\Models\Estudiante;
use App\Models\Docente;
use Carbon\Carbon;
use Exception;

class SancionService
{
    public function registrarSancion(array $request)
    {
        $tipoUsuario = $request['tipoUsuario'];
        $this->validarFechas($request['fechaInicio'], $request['fechaFin']);

        $usuarioId = $this->resolverUsuarioId($request, $tipoUsuario);

        $detalle = $this->obtenerDetalleUsuario($usuarioId, $tipoUsuario);
        $this->validarUsuarioContraFiltros($detalle, $request);

        $sancion = Sancion::create([
            'Usuario' => $usuarioId,
            'TipoUsuario' => $tipoUsuario,
            'Motivo' => $request['motivo'],
            'FechaInicio' => $request['fechaInicio'],
            'FechaFin' => $request['fechaFin'],
            'Estado' => 'ACTIVA'
        ]);

        return $this->mapear($sancion);
    }

    public function obtenerTodas($rol = null, $facultadId = null, $escuelaId = null, $escuelaContextoId = null)
    {
        return Sancion::all()->map(fn($s) => $this->mapear($s));
    }

    public function obtenerActivas($rol = null, $facultadId = null, $escuelaId = null, $escuelaContextoId = null)
    {
        return Sancion::where('Estado', 'ACTIVA')
            ->get()
            ->map(fn($s) => $this->mapear($s));
    }

    public function tieneSancionActiva($usuarioId, $tipoUsuario)
    {
        $sancion = Sancion::where('Usuario', $usuarioId)
            ->where('TipoUsuario', $tipoUsuario)
            ->where('Estado', 'ACTIVA')
            ->first();

        if (!$sancion) {
            return false;
        }

        $vigente = Carbon::parse($sancion->FechaFin)->isFuture();

        return $vigente;
    }

    public function levantarSancion($id)
    {
        $sancion = Sancion::findOrFail($id);

        if ($sancion->Estado === 'CUMPLIDA') {
            return $this->mapear($sancion);
        }

        $sancion->Estado = 'CUMPLIDA';

        if (Carbon::parse($sancion->FechaFin)->isFuture()) {
            $sancion->FechaFin = Carbon::now();
        }

        $sancion->save();

        return $this->mapear($sancion);
    }

    public function verificarUsuarioSancionado($usuarioId, $tipoUsuario)
    {
        $sancion = Sancion::where('Usuario', $usuarioId)
            ->where('TipoUsuario', $tipoUsuario)
            ->where('Estado', 'ACTIVA')
            ->first();

        if (!$sancion) {
            return [
                'sancionado' => false
            ];
        }

        $vigente = Carbon::parse($sancion->FechaFin)->isFuture();

        return [
            'sancionado' => $vigente,
            'sancionId' => $sancion->IdSancion,
            'motivo' => $sancion->Motivo,
            'fechaFin' => $sancion->FechaFin,
            'estado' => $vigente ? 'ACTIVA' : 'CUMPLIDA'
        ];
    }

    // ========================
    // LÓGICA INTERNA (equivalente Java private methods)
    // ========================

    private function validarFechas($inicio, $fin)
    {
        if (!$inicio || !$fin) {
            throw new Exception("Fechas obligatorias");
        }

        if ($fin < $inicio) {
            throw new Exception("Fecha fin no puede ser menor");
        }
    }

    private function resolverUsuarioId($request, $tipo)
    {
        if (!empty($request['usuarioId'])) {
            return $request['usuarioId'];
        }

        if (!empty($request['usuarioCodigo'])) {
            return $this->buscarPorCodigo($request['usuarioCodigo'], $tipo);
        }

        if (!empty($request['usuarioNombre'])) {
            return $this->buscarPorNombre($request['usuarioNombre'], $tipo);
        }

        throw new Exception("Debe enviar usuarioId, código o nombre");
    }

    private function buscarPorCodigo($codigo, $tipo)
    {
        if ($tipo === 'ESTUDIANTE') {
            $est = Estudiante::where('codigo', $codigo)->firstOrFail();
            return $est->usuario_id;
        }

        $doc = Docente::where('codigo_docente', $codigo)->firstOrFail();
        return $doc->usuario_id;
    }

    private function buscarPorNombre($nombre, $tipo)
    {
        if ($tipo === 'ESTUDIANTE') {
            $est = Estudiante::where('nombre', 'like', "%$nombre%")->firstOrFail();
            return $est->usuario_id;
        }

        $doc = Docente::where('nombre', 'like', "%$nombre%")->firstOrFail();
        return $doc->usuario_id;
    }

    private function obtenerDetalleUsuario($id, $tipo)
    {
        $usuario = Usuario::find($id);

        return [
            'nombre' => $usuario?->Nombre,
            'escuela_id' => null,
            'facultad_id' => null
        ];
    }

    private function validarUsuarioContraFiltros($detalle, $request)
    {
        // aquí replicamos lógica de permisos
        return true;
    }

    private function mapear($sancion)
    {
        return [
            'id' => $sancion->IdSancion,
            'usuarioId' => $sancion->Usuario,
            'tipoUsuario' => $sancion->TipoUsuario,
            'motivo' => $sancion->Motivo,
            'fechaInicio' => $sancion->FechaInicio,
            'fechaFin' => $sancion->FechaFin,
            'estado' => $sancion->Estado
        ];
    }
}