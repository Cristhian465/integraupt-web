<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResultadoPosicionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $reglas = [
            'posicion' => 'required|integer|min:1',
            'puntos' => 'required|integer|min:0',
            'prueba' => 'nullable|string|max:100',
            'fecha' => 'nullable|date',
            'lugar' => 'nullable|string|max:150',
            'observaciones' => 'nullable|string|max:255',
            'estado' => 'nullable|in:registrado,cancelado',
        ];

        if ($this->isMethod('post')) {
            $reglas['edicionDisciplinaId'] = 'required|integer';
            $reglas['facultadId'] = 'required|integer';
        } else {
            $reglas['facultadId'] = 'nullable|integer';
        }

        return $reglas;
    }
}
