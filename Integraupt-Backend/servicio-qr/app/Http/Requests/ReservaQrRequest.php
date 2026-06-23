<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservaQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservaId' => 'required|integer',
            'laboratorio' => 'required|string',
            'fecha' => 'required|string',
            'hora' => 'required|string',
            'estado' => 'required|string',
            'solicitanteNombre' => 'required|string',
            'solicitanteCodigo' => 'required|string',
            'token' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'reservaId.required' => 'El identificador de la reserva es obligatorio',
            'laboratorio.required' => 'El laboratorio es obligatorio',
            'fecha.required' => 'La fecha es obligatoria',
            'hora.required' => 'La hora es obligatoria',
            'estado.required' => 'El estado es obligatorio',
            'solicitanteNombre.required' => 'El nombre del solicitante es obligatorio',
            'solicitanteCodigo.required' => 'El código del solicitante es obligatorio',
            'token.max' => 'El token no debe exceder los 255 caracteres',
        ];
    }
}
