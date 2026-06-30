<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CafeteriaSeeder extends Seeder
{
    public function run(): void
    {
        DB::unprepared("
            INSERT INTO `cafeteria` (`IdCafeteria`, `IdFacultad`, `Nombre`, `Estado`, `IdEncargado`) VALUES
                (1, 1, 'Cafetería FAING', 1, 14),
                (2, 2, 'Cafetería FADE', 1, 15);
        ");

        DB::unprepared("
            INSERT INTO `cafeteria_producto` (`IdProducto`, `IdCafeteria`, `Nombre`, `Descripcion`, `Precio`, `Stock`, `Estado`) VALUES
                (1, 1, 'Café Americano', 'Café recién pasado', 3.50, 50, 1),
                (2, 1, 'Sándwich Mixto', 'Pan con jamón y queso', 5.00, 30, 1),
                (3, 2, 'Jugo de Naranja', 'Jugo natural de naranja', 4.00, 40, 1),
                (4, 2, 'Empanada de Carne', 'Empanada horneada', 4.50, 20, 1);
        ");
    }
}
