<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnotadorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $reglas = [
            'facultadId' => 'required|integer',
            'nombreJugador' => 'required|string|max:150',
            'cantidad' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
        ];

        if ($this->isMethod('post')) {
            $reglas['edicionDisciplinaId'] = 'required|integer';
        }

        return $reglas;
    }
}
