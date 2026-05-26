<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\OrdenesAsignadasContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Operations\OrdenesAsignadasService;
use Illuminate\View\View;

class OrdenesAsignadasController extends Controller
{
    protected OrdenesAsignadasService $service;

    public function __construct(OrdenesAsignadasService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $contexto = new OrdenesAsignadasContextDTO(
            (bool) session('es_superadmin', false),
            (int) session('sucursal_id', 0)
        );

        $porTecnico = $this->service->obtenerAgrupadas($contexto);
        return view('operations.ordenes_asignadas.index', compact('porTecnico'));
    }
}

