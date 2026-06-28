<?php

namespace App\Http\Controllers;

use App\Models\Silabo;
use App\Models\SilaboUnidad;
use App\Models\SilaboTema;
use App\Services\SilaboParserService;
use Illuminate\Http\Request;

class SilaboController extends Controller
{
    // ─── Parseo de PDF (paso 1 del flujo admin) ──────────────────────────────

    /**
     * POST /api/silabos/parsear-pdf
     * Recibe un PDF y retorna la estructura inferida para revisión del admin.
     * No persiste nada todavía.
     */
    public function parsearPdf(Request $request)
    {
        $request->validate([
            'ArchivoPdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file    = $request->file('ArchivoPdf');
        $tmpPath = $file->getRealPath();

        try {
            $parser    = new SilaboParserService();
            $estructura = $parser->parse($tmpPath);
        } catch (\Exception $e) {
            return response()->json([
                'message'    => 'Error al procesar el PDF: ' . $e->getMessage(),
                'estructura' => null,
            ], 422);
        }

        return response()->json($estructura);
    }

    // ─── Sílabos CRUD ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Silabo::with('unidades.temas');

        if ($request->filled('ciclo_numero')) {
            $query->where('CicloNumero', $request->ciclo_numero);
        }
        if ($request->filled('semestre')) {
            $query->where('Semestre', $request->semestre);
        }
        if ($request->filled('codigo_curso')) {
            $query->where('CodigoCurso', 'like', '%' . $request->codigo_curso . '%');
        }
        if ($request->filled('nombre_curso')) {
            $query->where('NombreCurso', 'like', '%' . $request->nombre_curso . '%');
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('CodigoCurso', 'like', "%$q%")
                    ->orWhere('NombreCurso', 'like', "%$q%");
            });
        }
        if ($request->filled('horario_curso_id')) {
            $query->where('HorarioCursoId', $request->horario_curso_id);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    /**
     * POST /api/silabos
     * Crea el sílabo con toda su estructura (resultado revisado del parseo).
     * Body JSON:
     *  semestre, ciclo_numero, codigo_curso, nombre_curso, horas, creditos, docente,
     *  horario_curso_id (opcional),
     *  archivo_pdf_path (ruta ya guardada, opcional),
     *  unidades: [{numero, nombre, horas_total, temas:[{semana, conceptual, procedimental, orden}]}]
     */
    public function store(Request $request)
    {
        // multipart/form-data envía 'unidades' como string JSON → decodificar ANTES de validar
        if (is_string($request->unidades)) {
            $request->merge(['unidades' => json_decode($request->unidades, true) ?? []]);
        }

        $request->validate([
            'semestre'        => 'required|string|max:10',
            'ciclo_numero'    => 'nullable|integer|min:1|max:12',
            'codigo_curso'    => 'nullable|string|max:20',
            'nombre_curso'    => 'nullable|string|max:255',
            'unidades'        => 'nullable|array|max:3',
            'unidades.*.numero'     => 'required|integer|min:1|max:3',
            'unidades.*.nombre'     => 'required|string|max:200',
            'unidades.*.horas_total'=> 'nullable|integer|min:0',
            'unidades.*.temas'      => 'nullable|array',
        ]);

        $pdfPath = null;
        if ($request->hasFile('ArchivoPdf')) {
            $file     = $request->file('ArchivoPdf');
            $filename = 'silabo_' . ($request->codigo_curso ?? 'X') . '_' . $request->semestre . '_' . time() . '.pdf';
            $dir      = public_path('storage/silabos');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $file->move($dir, $filename);
            $pdfPath  = 'storage/silabos/' . $filename;
        } elseif ($request->filled('archivo_pdf_path')) {
            $pdfPath = $request->archivo_pdf_path;
        }

        $silabo = Silabo::create([
            'CodigoCurso'    => $request->codigo_curso,
            'NombreCurso'    => $request->nombre_curso,
            'CicloNumero'    => $request->ciclo_numero,
            'Semestre'       => $request->semestre,
            'Horas'          => $request->horas,
            'Creditos'       => $request->creditos,
            'Docente'        => $request->docente,
            'HorarioCursoId' => $request->horario_curso_id,
            'ArchivoPdf'     => $pdfPath,
            'FechaCarga'     => now()->toDateString(),
            'Estado'         => 1,
        ]);

        foreach (($request->unidades ?? []) as $uData) {
            $unidad = SilaboUnidad::create([
                'SilaboId'   => $silabo->IdSilabo,
                'Numero'     => $uData['numero'],
                'Nombre'     => $uData['nombre'],
                'HorasTotal' => $uData['horas_total'] ?? 0,
            ]);

            foreach (($uData['temas'] ?? []) as $tData) {
                SilaboTema::create([
                    'UnidadId'               => $unidad->IdUnidad,
                    'Semana'                 => $tData['semana'],
                    'ContenidoConceptual'    => $tData['contenido_conceptual'] ?? $tData['conceptual'] ?? null,
                    'ContenidoProcedimental' => $tData['contenido_procedimental'] ?? $tData['procedimental'] ?? null,
                    'Orden'                  => $tData['orden'] ?? $tData['semana'],
                ]);
            }
        }

        return response()->json($silabo->load('unidades.temas'), 201);
    }

    public function show($id)
    {
        return response()->json(Silabo::with('unidades.temas')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $silabo = Silabo::findOrFail($id);

        $data = $request->only([
            'CodigoCurso', 'NombreCurso', 'CicloNumero', 'Semestre',
            'Horas', 'Creditos', 'Docente', 'HorarioCursoId', 'Estado',
        ]);

        if ($request->hasFile('ArchivoPdf')) {
            $file     = $request->file('ArchivoPdf');
            $filename = 'silabo_' . ($silabo->CodigoCurso ?? 'X') . '_' . ($silabo->Semestre ?? '') . '_' . time() . '.pdf';
            $dir      = public_path('storage/silabos');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $file->move($dir, $filename);
            $data['ArchivoPdf'] = 'storage/silabos/' . $filename;
        }

        $silabo->update($data);
        return response()->json($silabo->load('unidades.temas'));
    }

    public function destroy($id)
    {
        Silabo::findOrFail($id)->delete();
        return response()->json(['message' => 'Sílabo eliminado']);
    }

    // ─── Reporte de avance ───────────────────────────────────────────────────

    public function avance($id)
    {
        $silabo = Silabo::with('unidades.temas.avances')->findOrFail($id);

        $totalTemas      = 0;
        $temasAprobados  = 0;
        $temasPendientes = 0;

        foreach ($silabo->unidades as $unidad) {
            foreach ($unidad->temas as $tema) {
                $totalTemas++;
                if ($tema->avances->where('Estado', 'aprobado')->count() > 0) {
                    $temasAprobados++;
                } elseif ($tema->avances->where('Estado', 'pendiente')->count() > 0) {
                    $temasPendientes++;
                }
            }
        }

        return response()->json([
            'silabo'           => $silabo,
            'total_temas'      => $totalTemas,
            'temas_aprobados'  => $temasAprobados,
            'temas_pendientes' => $temasPendientes,
            'temas_sin_avance' => $totalTemas - $temasAprobados - $temasPendientes,
            'porcentaje'       => $totalTemas > 0
                ? round(($temasAprobados / $totalTemas) * 100, 1)
                : 0,
        ]);
    }
}
