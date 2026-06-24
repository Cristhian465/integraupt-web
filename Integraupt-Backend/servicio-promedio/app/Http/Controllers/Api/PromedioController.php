<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromedioController extends Controller
{
    public function calcular(Request $request)
    {
        $unidades = $request->input('unidades', []);
        
        $acumulado = 0.0;
        $porcentajeFaltante = 100.0;
        
        // Sumamos lo que ya tiene nota y restamos del porcentaje faltante
        foreach ($unidades as $unidad) {
            $nota = $unidad['nota'] !== null ? floatval($unidad['nota']) : null;
            $porcentaje = floatval($unidad['porcentaje']);
            
            if ($nota !== null) {
                $acumulado += ($nota * ($porcentaje / 100));
                $porcentajeFaltante -= $porcentaje;
            }
        }
        
        // El promedio aprobatorio es 10.5
        $meta = 10.5;
        
        // Cálculo de lo que necesita en el porcentaje restante
        if ($porcentajeFaltante > 0) {
            $notaNecesaria = ($meta - $acumulado) / ($porcentajeFaltante / 100);
            
            // Si notaNecesaria es menor a 0, significa que ya aprobó con lo que tiene
            if ($notaNecesaria < 0) {
                $notaNecesaria = 0;
            }
            
            $posible = $notaNecesaria <= 20;
        } else {
            $notaNecesaria = 0;
            $posible = $acumulado >= $meta;
        }

        $mensaje = '';
        if ($porcentajeFaltante == 0) {
            if ($acumulado >= 0 && $acumulado < 5) {
                $mensaje = 'Al otro año será';
            } elseif ($acumulado >= 5 && $acumulado < 9) {
                $mensaje = 'No hay retorno';
            } elseif ($acumulado >= 9 && $acumulado < 10.5) {
                $mensaje = 'Tan cerca y a la vez tan lejos';
            } elseif ($acumulado >= 10.5 && $acumulado < 12) {
                $mensaje = 'Pasar es pasar, pero que aprendi';
            } else {
                $mensaje = 'Se pasa solo';
            }
        } else {
            $mensaje = $posible 
                ? ($acumulado >= $meta ? 'Se pasa solo' : 'Aún puedes aprobar.') 
                : 'No hay retorno';
        }

        return response()->json([
            'acumulado' => round($acumulado, 2),
            'porcentajeFaltante' => round($porcentajeFaltante, 2),
            'notaNecesaria' => round($notaNecesaria, 2),
            'posible' => $posible,
            'aprobado' => $acumulado >= $meta,
            'mensaje' => $mensaje
        ]);
    }
}
