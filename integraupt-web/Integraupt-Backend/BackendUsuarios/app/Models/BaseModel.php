<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    public function toArray()
    {
        $array = parent::toArray();
        $camelArray = [];
        foreach ($array as $key => $value) {
            // Convierte la primera letra a minúscula (IdUsuario -> idUsuario)
            $camelKey = lcfirst($key);
            
            // Laravel por defecto convierte las relaciones a snake_case (ej. tipo_documento).
            // Lo convertimos a camelCase (tipoDocumento) si es necesario
            $camelKey = Str::camel($camelKey);

            if ($camelKey === 'rolObj') {
                $camelKey = 'rol';
            }
            
            $camelArray[$camelKey] = $value;
        }
        return $camelArray;
    }
}
