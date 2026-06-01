<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaReserva;

class AuditoriaController extends Controller
{
    public function index()
    {
        return AuditoriaReserva::all();
    }
}
?>