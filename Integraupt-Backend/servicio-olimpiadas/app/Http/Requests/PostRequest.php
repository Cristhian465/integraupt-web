<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'edicionId' => 'required|integer',
            'titulo' => 'required|string|max:200',
            'contenido' => 'required|string',
            'imagenUrl' => 'nullable|string|max:500',
            'autor' => 'nullable|string|max:150',
        ];
    }
}
