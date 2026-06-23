<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Reemplaza: EspacioRequest.java (DTO de entrada)
 *
 * Reglas equivalentes a las anotaciones Jakarta:
 * - @NotBlank → required|string
 * - @Size(max=N) → max:N
 * - @NotNull @Min(1) → required|integer|min:1
 *
 * El frontend envía camelCase (escuelaId), se mapea internamente.
 */
class EspacioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo'       => 'required|string|max:20',
            'nombre'       => 'required|string|max:100',
            'tipo'         => 'required|string|max:20',
            'capacidad'    => 'required|integer|min:1',
            'equipamiento' => 'nullable|string|max:1000',
            'escuelaId'    => 'required|integer|exists:escuela,IdEscuela',
            'estado'       => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required'    => 'El código es obligatorio.',
            'codigo.max'         => 'El código no puede exceder 20 caracteres.',
            'nombre.required'    => 'El nombre es obligatorio.',
            'nombre.max'         => 'El nombre no puede exceder 100 caracteres.',
            'tipo.required'      => 'El tipo es obligatorio.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.min'      => 'La capacidad debe ser al menos 1.',
            'escuelaId.required' => 'La escuela es obligatoria.',
            'escuelaId.exists'   => 'La escuela seleccionada no existe.',
        ];
    }

    /**
     * Mapea los campos camelCase del frontend a los nombres de columna de la BD.
     * EspacioPayload (TS) → Espacio Model (Eloquent)
     */
    public function toModelData(): array
    {
        return [
            'Codigo'       => trim($this->input('codigo')),
            'Nombre'       => trim($this->input('nombre')),
            'Tipo'         => trim($this->input('tipo')),
            'Capacidad'    => $this->input('capacidad'),
            'Equipamiento' => $this->input('equipamiento') ? trim($this->input('equipamiento')) : null,
            'Escuela'      => $this->input('escuelaId'),
            'Estado'       => $this->input('estado', 1),
        ];
    }

    /**
     * Respuesta JSON personalizada cuando la validación falla.
     * Retorna 422 con estructura predecible para el frontend TS.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Error de validación.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
