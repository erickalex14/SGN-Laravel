<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\OrdenesAsignadasContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Operations\OrdenesAsignadasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function cargarOrdenesAjax(Request $request): JsonResponse
    {
        $tecnicoId = (int) $request->query('tecnico_id');
        $type = $request->query('type') === 'entregadas' ? 'entregadas' : 'en_curso';
        $page = (int) $request->query('page', 1);
        $q = $request->query('q');
        $estado = $request->query('estado');
        $motivo = $request->query('motivo');
        $repuesto = $request->query('repuesto');

        $entregadas = ($type === 'entregadas');

        $paginador = $this->service->obtenerOrdenesPaginadas(
            $tecnicoId,
            $entregadas,
            12,
            $q,
            $estado,
            $motivo,
            $repuesto
        );

        return response()->json([
            'ok' => true,
            'current_page' => $paginador->currentPage(),
            'last_page' => $paginador->lastPage(),
            'per_page' => $paginador->perPage(),
            'total' => $paginador->total(),
            'data' => $paginador->items(),
        ]);
    }
}
