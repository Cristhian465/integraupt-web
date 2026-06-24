<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EdicionDisciplinaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $reglas = [
            'cupoMaximoPorFacultad' => 'nullable|integer|min:1',
            'reglasEspecificas' => 'nullable|string',
            'lugar' => 'nullable|string|max:150',
            'categoria' => 'nullable|in:general,varones,damas,mixto',
            'estado' => 'nullable|in:activa,inactiva',
        ];

        if ($this->isMethod('post')) {
            $reglas['disciplinaId'] = 'required|integer';
        }

        return $reglas;
    }
}
