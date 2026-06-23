<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncidenciaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reservaId' => 'required|integer',
            'descripcion' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'reservaId.required' => 'El identificador de la reserva es obligatorio',
            'reservaId.integer' => 'El identificador de la reserva debe ser un número válido',
            'descripcion.required' => 'La descripción de la incidencia es obligatoria',
            'descripcion.string' => 'La descripción debe ser un texto válido',
        ];
    }
}
