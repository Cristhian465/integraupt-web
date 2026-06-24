<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResultadoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $reglas = [
            'facultadVisitanteId' => 'nullable|integer',
            'fase' => 'nullable|string|max:50',
            'grupo' => 'nullable|string|max:50',
            'fechaPartido' => 'nullable|date',
            'lugar' => 'nullable|string|max:150',
            'puntajeLocal' => 'nullable|integer|min:0',
            'puntajeVisitante' => 'nullable|integer|min:0',
            'estado' => 'nullable|in:programado,en_curso,finalizado,cancelado,suspendido',
            'observaciones' => 'nullable|string|max:255',
        ];

        if ($this->isMethod('post')) {
            $reglas['edicionDisciplinaId'] = 'required|integer';
            $reglas['facultadLocalId'] = 'required|integer';
        } else {
            $reglas['facultadLocalId'] = 'nullable|integer';
        }

        return $reglas;
    }
}
