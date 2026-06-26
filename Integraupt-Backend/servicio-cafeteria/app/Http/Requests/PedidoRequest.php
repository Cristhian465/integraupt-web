<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PedidoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuarioId' => 'required|integer|exists:usuario,IdUsuario',
            'items' => 'required|array|min:1',
            'items.*.productoId' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
            'codigoOperacion' => 'nullable|string|max:50',
            'comprobante' => 'required|file|image|max:5120',
        ];
    }
}
