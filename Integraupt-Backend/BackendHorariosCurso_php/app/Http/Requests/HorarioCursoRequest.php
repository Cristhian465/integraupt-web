<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\DiaSemana;

class HorarioCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'curso' => ['required', 'integer', 'min:1'],
            'docente' => ['required', 'integer', 'min:1'],
            'espacio' => ['required', 'integer', 'min:1'],
            'bloque' => ['required', 'integer', 'min:1'],
            'diaSemana' => ['required', Rule::enum(DiaSemana::class)],
            'fechaInicio' => ['required', 'date_format:Y-m-d'],
            'fechaFin' => ['required', 'date_format:Y-m-d'],
            'estado' => ['required', 'boolean'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'timestamp' => now()->toISOString(),
                'status' => 400,
                'error' => 'Bad Request',
                'message' => $validator->errors()->first(),
                'path' => request()->getPathInfo()
            ], 400)
        );
    }
}
