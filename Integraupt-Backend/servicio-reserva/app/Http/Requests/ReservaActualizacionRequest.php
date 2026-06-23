<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservaActualizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuarioId' => 'required|integer',
            'espacioId' => 'required|integer',
            'bloqueId' => 'required|integer',
            'cursoId' => 'required|integer',
            'fechaReserva' => 'required|date',
            'descripcionUso' => 'nullable|string|max:65535',
            'cantidadEstudiantes' => 'required|integer|min:1',
            'estado' => 'nullable|string',
        ];
    }
}
