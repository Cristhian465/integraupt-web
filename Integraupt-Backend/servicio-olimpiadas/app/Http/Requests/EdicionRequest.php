<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EdicionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $reglasComunes = [
            'nombre' => 'required|string|max:150',
            'fechaInicioJuegos' => 'nullable|date',
            'fechaFinJuegos' => 'nullable|date|after_or_equal:fechaInicioJuegos',
            'observaciones' => 'nullable|string',
        ];

        if ($this->isMethod('post')) {
            return array_merge($reglasComunes, [
                'anioInicio' => 'required|integer|min:2000|max:2100',
                'semestreInicio' => 'required|integer|in:1,2',
            ]);
        }

        return $reglasComunes;
    }
}
