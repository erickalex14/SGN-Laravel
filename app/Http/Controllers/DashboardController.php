<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class DashboardController extends Controller
{
    protected DashboardService $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        return view('dashboard.index');
    }

    public function obtenerMetricas(): JsonResponse
    {
        try {
            $tecnicoId = session('tecnico_id');
            $permisos = session('permisos', []);
            $esAdmin = session('es_superadmin') === true
                || (($permisos['reportes']['ver'] ?? false) === true)
                || (($permisos['repuestos_admin']['ver'] ?? false) === true)
                || (($permisos['ordenes_asignadas']['ver'] ?? false) === true);

            $metricas = $this->service->obtenerMetricasGlobales($tecnicoId, $esAdmin);

            return response()->json([
                'ok' => true,
                'data' => $metricas
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
