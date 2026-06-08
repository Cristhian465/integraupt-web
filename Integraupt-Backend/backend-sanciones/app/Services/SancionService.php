<?php

namespace App\Services;

use App\Models\Sancion;
use App\Models\Usuario;
use App\Models\Estudiante;
use App\Models\Docente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class SancionService
{
    public function registrarSancion(array $request)
    {
        $tipoUsuario = strtoupper(trim($request['tipoUsuario']));
        $this->validarFechas($request['fechaInicio'], $request['fechaFin']);

        $rolValor = $request['rol'] ?? null;
        $facultadId = $request['facultadId'] ?? null;
        $escuelaId = $request['escuelaId'] ?? null;
        $escuelaContextoId = $request['escuelaContextoId'] ?? null;

        $filtros = $this->construirFiltroContexto($rolValor, $facultadId, $escuelaId, $escuelaContextoId);

        $usuarioId = $this->resolverUsuarioId($request, $tipoUsuario);

        $detalle = $this->obtenerDetalleUsuario($usuarioId, $tipoUsuario);
        $this->validarUsuarioContraFiltros($detalle, $filtros);

        $sancion = Sancion::create([
            'Usuario' => $usuarioId,
            'TipoUsuario' => $tipoUsuario,
            'Motivo' => trim($request['motivo']),
            'FechaInicio' => $request['fechaInicio'],
            'FechaFin' => $request['fechaFin'],
            'Estado' => 'ACTIVA'
        ]);

        return $this->mapearRespuesta($sancion);
    }

    public function obtenerTodas($rolValor = null, $facultadId = null, $escuelaId = null, $escuelaContextoId = null)
    {
        $filtros = $this->construirFiltroContexto($rolValor, $facultadId, $escuelaId, $escuelaContextoId);
        return $this->obtenerSancionesFiltradas($filtros, false);
    }

    public function obtenerActivas($rolValor = null, $facultadId = null, $escuelaId = null, $escuelaContextoId = null)
    {
        $filtros = $this->construirFiltroContexto($rolValor, $facultadId, $escuelaId, $escuelaContextoId);
        return $this->obtenerSancionesFiltradas($filtros, true);
    }

    public function levantarSancion($sancionId)
    {
        $sancion = Sancion::findOrFail($sancionId);

        if ($sancion->Estado === 'CUMPLIDA') {
            return $this->mapearRespuesta($sancion);
        }

        $sancion->Estado = 'CUMPLIDA';
        if (Carbon::parse($sancion->FechaFin)->isAfter(Carbon::now())) {
            $sancion->FechaFin = Carbon::now()->format('Y-m-d');
        }

        $sancion->save();

        return $this->mapearRespuesta($sancion);
    }

    public function verificarUsuarioSancionado($usuarioId, $tipoUsuarioValor)
    {
        $tipoUsuario = strtoupper(trim($tipoUsuarioValor));

        $sancionActiva = Sancion::where('Usuario', $usuarioId)
            ->where('TipoUsuario', $tipoUsuario)
            ->where('Estado', 'ACTIVA')
            ->first();

        if ($sancionActiva) {
            $sancionActiva = $this->actualizarSancionSiVencida($sancionActiva);
        }

        if (!$sancionActiva || $sancionActiva->Estado !== 'ACTIVA') {
            return [
                'sancionado' => false
            ];
        }

        $vigente = Carbon::parse($sancionActiva->FechaFin)->isAfter(Carbon::now()) || Carbon::parse($sancionActiva->FechaFin)->isSameDay(Carbon::now());
        
        $response = [
            'sancionado' => $vigente,
            'sancionId' => $sancionActiva->IdSancion,
            'motivo' => $sancionActiva->Motivo,
            'fechaFin' => $sancionActiva->FechaFin,
        ];

        if (!$vigente) {
            $sancionActiva->Estado = 'CUMPLIDA';
            $sancionActiva->save();
            $response['estado'] = 'CUMPLIDA';
        } else {
            $response['estado'] = $sancionActiva->Estado;
        }

        return $response;
    }

    public function tieneSancionActiva($usuarioId, $tipoUsuarioValor)
    {
        $tipoUsuario = strtoupper(trim($tipoUsuarioValor));
        $hoy = Carbon::now()->format('Y-m-d');
        return Sancion::where('Usuario', $usuarioId)
            ->where('TipoUsuario', $tipoUsuario)
            ->where('Estado', 'ACTIVA')
            ->where('FechaFin', '>=', $hoy)
            ->exists();
    }

    // ========================
    // LÓGICA INTERNA (Java private methods equivalentes)
    // ========================

    private function validarFechas($inicio, $fin)
    {
        if (empty($inicio)) {
            throw new InvalidArgumentException("La fecha de inicio es obligatoria");
        }
        if (empty($fin)) {
            throw new InvalidArgumentException("La fecha de fin es obligatoria");
        }
        if (Carbon::parse($fin)->isBefore(Carbon::parse($inicio))) {
            throw new InvalidArgumentException("La fecha de fin no puede ser anterior a la fecha de inicio");
        }
    }

    private function mapearRespuesta(Sancion $sancion)
    {
        $detalle = $this->obtenerDetalleUsuario($sancion->Usuario, $sancion->TipoUsuario);
        
        return [
            'id' => $sancion->IdSancion,
            'usuarioId' => $sancion->Usuario,
            'tipoUsuario' => $sancion->TipoUsuario,
            'motivo' => $sancion->Motivo,
            'fechaInicio' => $sancion->FechaInicio ? Carbon::parse($sancion->FechaInicio)->format('Y-m-d') : null,
            'fechaFin' => $sancion->FechaFin ? Carbon::parse($sancion->FechaFin)->format('Y-m-d') : null,
            'estado' => $sancion->Estado,
            'usuarioNombre' => $detalle['nombreCompleto'],
            'usuarioCodigo' => $detalle['codigo'],
            'usuarioEscuela' => $detalle['escuelaNombre'],
            'usuarioEscuelaId' => $detalle['escuelaId'],
            'usuarioFacultad' => $detalle['facultadNombre'],
            'usuarioFacultadId' => $detalle['facultadId'],
        ];
    }

    private function actualizarSancionSiVencida(Sancion $sancion)
    {
        if ($sancion->Estado === 'ACTIVA' && Carbon::parse($sancion->FechaFin)->isBefore(Carbon::now()->startOfDay())) {
            $sancion->Estado = 'CUMPLIDA';
            $sancion->save();
        }
        return $sancion;
    }

    private function resolverUsuarioId(array $request, $tipoUsuario)
    {
        $usuarioId = null;

        if (!empty($request['usuarioId'])) {
            $this->validarUsuarioPorTipo($request['usuarioId'], $tipoUsuario);
            $usuarioId = $request['usuarioId'];
        }

        if (!empty($request['usuarioCodigo'])) {
            $usuarioIdPorCodigo = $this->obtenerUsuarioIdPorCodigo($request['usuarioCodigo'], $tipoUsuario);
            $usuarioId = $this->combinarUsuarioId($usuarioId, $usuarioIdPorCodigo, "código");
        }

        if (!empty($request['usuarioNombre'])) {
            $usuarioIdPorNombre = $this->obtenerUsuarioIdPorNombre($request['usuarioNombre'], $tipoUsuario);
            $usuarioId = $this->combinarUsuarioId($usuarioId, $usuarioIdPorNombre, "nombre");
        }

        if ($usuarioId !== null) {
            return $usuarioId;
        }

        throw new InvalidArgumentException("Debe proporcionar el código o el nombre del usuario a sancionar.");
    }

    private function validarUsuarioPorTipo($usuarioId, $tipoUsuario)
    {
        if ($tipoUsuario === 'ESTUDIANTE') {
            $existe = Estudiante::where('IdUsuario', $usuarioId)->exists();
        } else {
            $existe = Docente::where('IdUsuario', $usuarioId)->exists();
        }

        if (!$existe) {
            $tipo = $tipoUsuario === 'ESTUDIANTE' ? "estudiante" : "docente";
            throw new ModelNotFoundException("El " . $tipo . " indicado no existe o no pertenece al tipo seleccionado.");
        }
    }

    private function obtenerDetalleUsuario($usuarioId, $tipoUsuario)
    {
        if (!$usuarioId) {
            return $this->detalleVacio();
        }

        $usuario = Usuario::find($usuarioId);

        if ($tipoUsuario === 'ESTUDIANTE') {
            $estudiante = DB::table('estudiante as e')
                ->leftJoin('escuela as esc', 'esc.IdEscuela', '=', 'e.Escuela')
                ->leftJoin('facultad as fac', 'fac.IdFacultad', '=', 'esc.IdFacultad')
                ->where('e.IdUsuario', $usuarioId)
                ->select('e.Codigo', 'esc.IdEscuela', 'esc.Nombre as EscuelaNombre', 'fac.IdFacultad', 'fac.Nombre as FacultadNombre')
                ->first();

            if ($estudiante) {
                return $this->crearDetalle($usuario, $estudiante->Codigo, $estudiante->IdEscuela, $estudiante->EscuelaNombre, $estudiante->IdFacultad, $estudiante->FacultadNombre);
            }
        } else {
            $docente = DB::table('docente as d')
                ->leftJoin('escuela as esc', 'esc.IdEscuela', '=', 'd.Escuela')
                ->leftJoin('facultad as fac', 'fac.IdFacultad', '=', 'esc.IdFacultad')
                ->where('d.IdUsuario', $usuarioId)
                ->select('d.CodigoDocente as Codigo', 'esc.IdEscuela', 'esc.Nombre as EscuelaNombre', 'fac.IdFacultad', 'fac.Nombre as FacultadNombre')
                ->first();

            if ($docente) {
                return $this->crearDetalle($usuario, $docente->Codigo, $docente->IdEscuela, $docente->EscuelaNombre, $docente->IdFacultad, $docente->FacultadNombre);
            }
        }

        return $usuario ? $this->detalleDesdeUsuario($usuario) : $this->detalleVacio();
    }

    private function detalleVacio()
    {
        return [
            'nombreCompleto' => null,
            'codigo' => null,
            'escuelaId' => null,
            'escuelaNombre' => null,
            'facultadId' => null,
            'facultadNombre' => null
        ];
    }

    private function detalleDesdeUsuario($usuario)
    {
        return [
            'nombreCompleto' => $usuario->nombre_completo,
            'codigo' => null,
            'escuelaId' => null,
            'escuelaNombre' => null,
            'facultadId' => null,
            'facultadNombre' => null
        ];
    }

    private function crearDetalle($usuario, $codigo, $escuelaId, $escuelaNombre, $facultadId, $facultadNombre)
    {
        return [
            'nombreCompleto' => $usuario ? $usuario->nombre_completo : null,
            'codigo' => $codigo,
            'escuelaId' => $escuelaId,
            'escuelaNombre' => $escuelaNombre,
            'facultadId' => $facultadId,
            'facultadNombre' => $facultadNombre
        ];
    }

    private function obtenerUsuarioIdPorCodigo($codigo, $tipoUsuario)
    {
        $codigoNormalizado = trim($codigo);
        if ($codigoNormalizado === '') {
            throw new InvalidArgumentException("El código del usuario no puede estar vacío.");
        }

        if ($tipoUsuario === 'ESTUDIANTE') {
            $estudiante = Estudiante::where('Codigo', $codigoNormalizado)->first();
            if (!$estudiante) {
                throw new ModelNotFoundException("No se encontró un estudiante con el código " . $codigoNormalizado);
            }
            return $estudiante->IdUsuario;
        } else {
            $docente = Docente::where('CodigoDocente', $codigoNormalizado)->first();
            if (!$docente) {
                throw new ModelNotFoundException("No se encontró un docente con el código " . $codigoNormalizado);
            }
            return $docente->IdUsuario;
        }
    }

    private function obtenerUsuarioIdPorNombre($nombre, $tipoUsuario)
    {
        $nombreNormalizado = $this->normalizarNombre($nombre);

        if ($tipoUsuario === 'ESTUDIANTE') {
            $resultados = DB::table('estudiante as e')
                ->join('usuario as u', 'u.IdUsuario', '=', 'e.IdUsuario')
                ->where(DB::raw("LOWER(TRIM(CONCAT(TRIM(COALESCE(u.Nombre, '')), ' ', TRIM(COALESCE(u.Apellido, '')))))"), strtolower($nombreNormalizado))
                ->pluck('e.IdUsuario')->toArray();
            return $this->obtenerUnicoUsuarioId($resultados, "estudiante", "nombre", $nombreNormalizado);
        } else {
            $resultados = DB::table('docente as d')
                ->join('usuario as u', 'u.IdUsuario', '=', 'd.IdUsuario')
                ->where(DB::raw("LOWER(TRIM(CONCAT(TRIM(COALESCE(u.Nombre, '')), ' ', TRIM(COALESCE(u.Apellido, '')))))"), strtolower($nombreNormalizado))
                ->pluck('d.IdUsuario')->toArray();
            return $this->obtenerUnicoUsuarioId($resultados, "docente", "nombre", $nombreNormalizado);
        }
    }

    private function obtenerUnicoUsuarioId(array $resultados, $tipoDescripcion, $criterio, $valorBuscado)
    {
        if (empty($resultados)) {
            throw new ModelNotFoundException("No se encontró un " . $tipoDescripcion . " con el " . $criterio . " " . $valorBuscado);
        }

        if (count($resultados) > 1) {
            throw new InvalidArgumentException("Se encontró más de un " . $tipoDescripcion . " con el " . $criterio . " " . $valorBuscado . ".");
        }

        $usuarioId = $resultados[0];
        if ($usuarioId === null) {
            throw new LogicException("El " . $tipoDescripcion . " localizado no tiene un usuario asociado.");
        }

        return $usuarioId;
    }

    private function combinarUsuarioId($actual, $nuevo, $criterio)
    {
        if ($nuevo === null) {
            return $actual;
        }

        if ($actual !== null && $actual !== $nuevo) {
            throw new InvalidArgumentException("Los datos del usuario proporcionados no coinciden entre sí (difieren en el " . $criterio . ").");
        }

        return $nuevo;
    }

    private function normalizarNombre($nombre)
    {
        $nombreLimpio = $nombre !== null ? trim($nombre) : "";
        if ($nombreLimpio === '') {
            throw new InvalidArgumentException("El nombre del usuario no puede estar vacío.");
        }

        return preg_replace('/\s+/', ' ', $nombreLimpio);
    }

    private function obtenerSancionesFiltradas($filtros, $soloActivas)
    {
        $sanciones = Sancion::all()->map(function ($sancion) {
            return $this->actualizarSancionSiVencida($sancion);
        });

        if ($soloActivas) {
            $sanciones = $sanciones->filter(function ($sancion) {
                return $sancion->Estado === 'ACTIVA';
            });
        }

        return $sanciones->map(function ($sancion) {
            return $this->mapearRespuesta($sancion);
        })->filter(function ($response) use ($filtros) {
            return $this->coincideConFiltros($response, $filtros);
        })->values()->all();
    }

    private function coincideConFiltros($response, $filtros)
    {
        if ($filtros['escuelaId'] !== null) {
            if ($filtros['escuelaId'] != $response['usuarioEscuelaId']) return false;
        }

        if ($filtros['facultadId'] !== null) {
            if ($filtros['facultadId'] != $response['usuarioFacultadId']) return false;
        }

        return true;
    }

    private function construirFiltroContexto($rolValor, $facultadId, $escuelaId, $escuelaContextoId)
    {
        $rol = strtoupper(trim($rolValor ?? ''));
        $facultadFiltro = $facultadId;
        $escuelaFiltro = $escuelaId;

        if ($rol === 'SUPERVISOR') {
            $escuelaRestriccion = $escuelaContextoId !== null ? $escuelaContextoId : $escuelaId;
            if ($escuelaRestriccion === null) {
                throw new InvalidArgumentException("Los supervisores deben tener una escuela asociada para gestionar sanciones.");
            }
            $escuelaFiltro = $escuelaRestriccion;
            $facultadFiltro = null;
        }

        return [
            'rol' => $rol,
            'facultadId' => $facultadFiltro,
            'escuelaId' => $escuelaFiltro
        ];
    }

    private function validarUsuarioContraFiltros($detalle, $filtros)
    {
        if ($filtros['rol'] === 'SUPERVISOR') {
            if ($detalle['escuelaId'] === null || $detalle['escuelaId'] != $filtros['escuelaId']) {
                throw new InvalidArgumentException("No tienes permisos para sancionar usuarios fuera de tu escuela asignada.");
            }
            return;
        }

        if ($filtros['escuelaId'] !== null) {
            if ($detalle['escuelaId'] === null || $filtros['escuelaId'] != $detalle['escuelaId']) {
                throw new InvalidArgumentException("El usuario seleccionado no pertenece a la escuela indicada.");
            }
        }

        if ($filtros['facultadId'] !== null) {
            if ($detalle['facultadId'] === null || $filtros['facultadId'] != $detalle['facultadId']) {
                throw new InvalidArgumentException("El usuario seleccionado no pertenece a la facultad indicada.");
            }
        }
    }
}