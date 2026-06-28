<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class SilaboParserService
{
    public function parse(string $pdfPath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);
        $text   = $pdf->getText();

        return [
            'codigo_curso' => $this->extractCodigoCurso($text),
            'nombre_curso' => $this->extractNombreCurso($text),
            'ciclo_numero' => $this->extractCicloNumero($text),
            'semestre'     => $this->extractSemestre($text),
            'horas'        => $this->extractHoras($text),
            'creditos'     => $this->extractCreditos($text),
            'docente'      => $this->extractDocente($text),
            'unidades'     => $this->extractUnidades($text),
        ];
    }

    // ─── Cabecera ────────────────────────────────────────────────────────────

    private function extractCodigoCurso(string $text): string
    {
        // Intento 1: etiqueta explícita en el documento
        $patrones = [
            '/C[ÓO]DIGO\s+DEL\s+CURSO\s*:?\s*([A-Z]{1,4}\s*[-–]\s*\d{3,4})/iu',
            '/C[ÓO]D(?:IGO)?\s*\.?\s*:?\s*([A-Z]{1,4}\s*[-–]\s*\d{3,4})/iu',
            '/ASIGNATURA\s*:?\s*([A-Z]{1,4}\s*[-–]\s*\d{3,4})/iu',
        ];
        foreach ($patrones as $pat) {
            if (preg_match($pat, $text, $m)) {
                return $this->normalizarCodigo($m[1]);
            }
        }

        // Intento 2: buscar patrón XX-NNN en cualquier parte del texto
        // (captura hasta 4 letras mayúsculas, guión, 3-4 dígitos)
        if (preg_match('/\b([A-Z]{1,4})\s*[-–]\s*(\d{3,4})\b/u', $text, $m)) {
            return strtoupper($m[1]) . '-' . $m[2];
        }

        return '';
    }

    private function normalizarCodigo(string $raw): string
    {
        // "SI - 786" / "EG – 725" / "EG-725" → "EG-725"
        $raw = preg_replace('/\s*[-–]\s*/', '-', trim($raw));
        return strtoupper($raw);
    }

    private function extractNombreCurso(string $text): string
    {
        $patrones = [
            '/NOMBRE\s+DEL\s+CURSO\s*:?\s*(.+)/iu',
            '/DENOMINACI[ÓO]N\s+DEL\s+CURSO\s*:?\s*(.+)/iu',
            '/ASIGNATURA\s*:?\s*([^:\n]{5,})/iu',
            '/CURSO\s*:?\s*([^:\n]{5,})/iu',
        ];
        foreach ($patrones as $pat) {
            if (preg_match($pat, $text, $m)) {
                $nombre = trim($m[1]);
                // Quitar si capturó el código de curso pegado al nombre
                $nombre = preg_replace('/^[A-Z]{1,4}\s*[-–]\s*\d{3,4}\s*/u', '', $nombre);
                // Cortar en el siguiente label (dos puntos + palabra en mayúsculas)
                $nombre = preg_split('/\s{2,}|[:\n]/u', $nombre)[0];
                $nombre = trim($nombre);
                if (mb_strlen($nombre) > 3) return $nombre;
            }
        }
        return '';
    }

    private function extractCicloNumero(string $text): ?int
    {
        // Primero intentar desde el código
        $codigo = $this->extractCodigoCurso($text);
        if (preg_match('/(\d{3,4})$/', $codigo, $m)) {
            $centena = intdiv((int) $m[1], 100);
            if ($centena === 0)  return 10;
            if ($centena >= 1 && $centena <= 9) return $centena;
        }
        // Fallback: etiqueta explícita en el PDF
        if (preg_match('/CICLO\s*:?\s*(\d{1,2})/iu', $text, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    private function extractSemestre(string $text): string
    {
        // Formato "2026-I", "2026 - II", "2026–I"
        $patrones = [
            '/SEMESTRE\s+AC[AÁ]D[EÉ]MICO\s*:?\s*(\d{4}\s*[-–]\s*I{1,2})/iu',
            '/SEMESTRE\s*:?\s*(\d{4}\s*[-–]\s*I{1,2})/iu',
            '/(\d{4})\s*[-–]\s*(I{1,2})\b/u',
        ];
        foreach ($patrones as $pat) {
            if (preg_match($pat, $text, $m)) {
                if (isset($m[2])) {
                    // Patrón con dos grupos: año y numeral romano
                    return $m[1] . '-' . strtoupper($m[2]);
                }
                // Normalizar espacios alrededor del guión
                $sem = preg_replace('/\s*[-–]\s*/', '-', $m[1]);
                return strtoupper(trim($sem));
            }
        }
        return '';
    }

    private function extractHoras(string $text): ?int
    {
        $patrones = [
            '/HORAS\s+(?:SEMANALES|TOTAL(?:ES)?|TEOR[IÍ]A|LECTIVA)?\s*:?\s*(\d+)/iu',
            '/H\.?\s*SEMANALES?\s*:?\s*(\d+)/iu',
            '/HORAS\s*:?\s*(\d+)/iu',
        ];
        foreach ($patrones as $pat) {
            if (preg_match($pat, $text, $m)) return (int) $m[1];
        }
        return null;
    }

    private function extractCreditos(string $text): ?int
    {
        if (preg_match('/CR[EÉ]DITOS?\s*:?\s*(\d+)/iu', $text, $m)) return (int) $m[1];
        if (preg_match('/UNIDADES?\s+DE?\s+CR[EÉ]DITO\s*:?\s*(\d+)/iu', $text, $m)) return (int) $m[1];
        return null;
    }

    private function extractDocente(string $text): string
    {
        $patrones = [
            '/DOCENTE\s*:?\s*(.+)/iu',
            '/PROFESOR(?:ES?)?\s*:?\s*(.+)/iu',
            '/CATEDR[AÁ]TICO\s*:?\s*(.+)/iu',
            '/ING\.\s+(.{10,60})/iu',
            '/LIC\.\s+(.{10,60})/iu',
        ];
        foreach ($patrones as $pat) {
            if (preg_match($pat, $text, $m)) {
                $nombre = trim($m[1]);
                // Cortar en el siguiente campo o salto de línea doble
                $nombre = preg_split('/\n{2,}|:\s*[A-ZÁÉÍÓÚÑ]{3}/u', $nombre)[0];
                $nombre = trim(preg_replace('/\s+/', ' ', $nombre));
                if (mb_strlen($nombre) > 3) return $nombre;
            }
        }
        return '';
    }

    // ─── Unidades ────────────────────────────────────────────────────────────

    private function extractUnidades(string $text): array
    {
        $ordenes  = ['PRIMERA', 'SEGUNDA', 'TERCERA'];
        $patFin   = 'SEGUNDA|TERCERA|PLAN\s+DE\s+EVALUACI[ÓO]N|ATRIBUTOS|BIBLIOGRAF|$';
        $unidades = [];

        foreach ($ordenes as $idx => $orden) {
            $numero = $idx + 1;

            $pattern = '/(' . $orden . '\s+UNIDAD\s+DID[AÁ]CTICA.+?)(?=' . $patFin . ')/isu';
            if ($idx === 0) {
                $patFin = 'SEGUNDA|PLAN\s+DE\s+EVALUACI[ÓO]N|ATRIBUTOS|BIBLIOGRAF|$';
            } elseif ($idx === 1) {
                $patFin = 'TERCERA|PLAN\s+DE\s+EVALUACI[ÓO]N|ATRIBUTOS|BIBLIOGRAF|$';
            } else {
                $patFin = 'PLAN\s+DE\s+EVALUACI[ÓO]N|ATRIBUTOS|BIBLIOGRAF|$';
            }

            if (!preg_match('/(' . $orden . '\s+UNIDAD\s+DID[AÁ]CTICA.+?)(?=' . $patFin . ')/isu', $text, $m)) {
                continue;
            }

            $bloque = $m[1];
            $nombre = $this->extractNombreUnidad($bloque, $orden);
            $horas  = 0;
            if (preg_match('/Total\s+Horas\s*:?\s*(\d+)/iu', $bloque, $hm)) {
                $horas = (int) $hm[1];
            }

            $temas = $this->extractTemas($bloque);

            $unidades[] = [
                'numero'      => $numero,
                'nombre'      => $nombre,
                'horas_total' => $horas,
                'temas'       => $temas,
            ];
        }

        return $unidades;
    }

    private function extractNombreUnidad(string $bloque, string $orden): string
    {
        if (preg_match('/' . $orden . '\s+UNIDAD\s+DID[AÁ]CTICA\s*:?\s*(.+)/iu', $bloque, $m)) {
            $nombre = trim($m[1]);
            $nombre = preg_replace('/Total\s+Horas.*/iu', '', $nombre);
            $nombre = trim(preg_replace('/\s+/', ' ', $nombre));
            return $nombre;
        }
        return '';
    }

    private function extractTemas(string $bloque): array
    {
        // Recortar desde el encabezado de la tabla
        if (preg_match('/Semana\s+Contenidos?\s+Conceptuales?/isu', $bloque, $hm, PREG_OFFSET_CAPTURE)) {
            $bloque = substr($bloque, $hm[0][1]);
        }

        // Cortar al llegar a secciones posteriores
        if (preg_match('/(?:Contenidos?\s+Actitudinales?|Estrategias?\s+(?:de\s+)?(?:Ense[ñn]anza|Aprendizaje)|Bibliograf)/iu', $bloque, $cm, PREG_OFFSET_CAPTURE)) {
            $bloque = substr($bloque, 0, $cm[0][1]);
        }

        // Localizar cada número de semana al inicio de línea
        preg_match_all('/(?:^|[\r\n])[ \t]*(\d{1,2})[ \t]+/m', $bloque, $matches, PREG_OFFSET_CAPTURE);

        $hits = [];
        foreach ($matches[1] as $idx => $hit) {
            $semana = (int) $hit[0];
            if ($semana < 1 || $semana > 17) continue;
            $posContenido = $matches[0][$idx][1] + strlen($matches[0][$idx][0]);
            $hits[] = ['semana' => $semana, 'pos' => $posContenido];
        }

        $temas = [];
        for ($i = 0; $i < count($hits); $i++) {
            $inicio    = $hits[$i]['pos'];
            $fin       = isset($hits[$i + 1]) ? $hits[$i + 1]['pos'] : strlen($bloque);
            $contenido = trim(substr($bloque, $inicio, $fin - $inicio));

            [$conceptual, $procedimental] = $this->splitContenido($contenido);

            $temas[] = [
                'semana'                  => $hits[$i]['semana'],
                'contenido_conceptual'    => $conceptual,
                'contenido_procedimental' => $procedimental,
                'orden'                   => $hits[$i]['semana'],
            ];
        }

        return $temas;
    }

    private function splitContenido(string $texto): array
    {
        $verbos = 'Aplica|Utiliza|Realiza|Presenta|Maneja|Demuestra|Resuelve|Elabora|'
                . 'Construye|Identifica|Prueba|Observa|Diferencia|Experimenta|Crea|'
                . 'Diseña|Desarrolla|Implementa|Evalúa|Propone|Genera|Produce|Formula|'
                . 'Analiza|Ejecuta|Emplea|Comprende|Conoce|Explica';

        if (preg_match('/\b(' . $verbos . ')\b/u', $texto, $m, PREG_OFFSET_CAPTURE)) {
            $pos           = $m[0][1];
            $conceptual    = trim(substr($texto, 0, $pos));
            $procedimental = trim(substr($texto, $pos));
        } else {
            $conceptual    = trim($texto);
            $procedimental = '';
        }

        return [
            $this->limpiarTexto($conceptual),
            $this->limpiarTexto($procedimental),
        ];
    }

    private function limpiarTexto(string $texto): string
    {
        $texto = preg_replace('/[ \t]+/', ' ', $texto);
        $texto = preg_replace('/[\r\n]+/', ' ', $texto);
        return trim($texto);
    }
}
