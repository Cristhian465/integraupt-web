<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CafeteriaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:150',
            'facultadId' => 'required|integer|exists:facultad,IdFacultad',
            'estado' => 'nullable|boolean',
            'encargadoId' => 'nullable|integer|exists:usuario,IdUsuario',
        ];
    }
}
