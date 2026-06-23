<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SancionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuarioId' => 'nullable|integer',
            'usuarioCodigo' => 'nullable|string|max:50',
            'usuarioNombre' => 'nullable|string|max:255',
            'tipoUsuario' => 'required|string',
            'rol' => 'nullable|string|max:30',
            'facultadId' => 'nullable|integer',
            'escuelaId' => 'nullable|integer',
            'escuelaContextoId' => 'nullable|integer',
            'motivo' => 'required|string|max:255',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:today',
        ];
    }
}
