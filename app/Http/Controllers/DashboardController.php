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
        $permisos = session('permisos', []);
        $esSuperadmin = session('es_superadmin') === true;

        $usuario = auth()->user();
        $esTecnico = $usuario && in_array((int) $usuario->rol_id, [2, 4], true);

        $puedeVerGestion = !$esTecnico && ($esSuperadmin
            || (($permisos['reportes']['ver'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true)
            || (($permisos['repuestos_admin']['ver'] ?? false) === true)
            || (($permisos['ordenes_asignadas']['ver'] ?? false) === true));

        return view('dashboard.index', [
            'esSuperadmin' => $esSuperadmin,
            'puedeVerGestion' => $puedeVerGestion,
        ]);
    }

    public function obtenerMetricas(): JsonResponse
    {
        try {
            $tecnicoId = (int) session('tecnico_id', 0);
            $permisos = session('permisos', []);
            $esSuperadmin = session('es_superadmin') === true;
            $sucursalId = (int) session('sucursal_id', 0);

            $usuario = auth()->user();
            $esTecnico = $usuario && in_array((int) $usuario->rol_id, [2, 4], true);

            $esAdmin = !$esTecnico && ($esSuperadmin
                || (($permisos['reportes']['ver'] ?? false) === true)
                || (($permisos['repuestos_admin']['ver'] ?? false) === true)
                || (($permisos['ordenes_asignadas']['ver'] ?? false) === true));

            $metricas = $this->service->obtenerMetricasGlobales($tecnicoId, $esAdmin, $esSuperadmin, $sucursalId);

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
