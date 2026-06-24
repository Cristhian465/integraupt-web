<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisciplinaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'tipoParticipacion' => 'nullable|in:individual,equipo',
            'reglas' => 'nullable|string',
            'cupoMaximoDefault' => 'nullable|integer|min:1',
            'estado' => 'nullable|in:activa,inactiva',
        ];
    }
}
