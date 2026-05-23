<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra la vista principal del sistema tras el inicio de sesion.
     */
    public function index(Request $request): View
    {
        // En la siguiente fase, aqui inyectaremos el DashboardService
        // para traer las metricas (Ordenes abiertas, equipos reparados, etc.)
        // replicando las consultas de dashboard_content.php

        return view('dashboard.index');
    }
}
