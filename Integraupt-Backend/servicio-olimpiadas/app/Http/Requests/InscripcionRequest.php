<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InscripcionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'edicionDisciplinaId' => 'required|integer',
            'usuarioId' => 'required|integer',
            'observaciones' => 'nullable|string|max:255',
        ];
    }
}
