<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'tipoEvento' => 'required|string|in:charla,taller,cultural,academico',
            'alcance' => 'required|string|in:facultad,escuela',
            'facultadId' => 'required|integer|exists:facultad,IdFacultad',
            'escuelaId' => 'nullable|integer|exists:escuela,IdEscuela|required_if:alcance,escuela',
            'espacioId' => 'nullable|integer|exists:espacio,IdEspacio',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after:fechaInicio',
            'aforoMaximo' => 'nullable|integer|min:1',
            'requiereInscripcion' => 'nullable|boolean',
            'responsableId' => 'required|integer|exists:usuario,IdUsuario',
            'imagen' => 'nullable|image|max:5120',
        ];
    }
}
