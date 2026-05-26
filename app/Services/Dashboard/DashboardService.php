<?php

namespace App\Services\Dashboard;

use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\SolicitudRepuestoRepository;
use App\Repositories\Operations\NotaCreditoRepository;
use App\Repositories\Dashboard\DashboardRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardService
{
    protected OrdenRepository $ordenRepo;
    protected SolicitudRepuestoRepository $repuestoRepo;
    protected NotaCreditoRepository $ncRepo;
    protected DashboardRepository $dashboardRepo;

    public function __construct(
        OrdenRepository $ordenRepo,
        SolicitudRepuestoRepository $repuestoRepo,
        NotaCreditoRepository $ncRepo,
        DashboardRepository $dashboardRepo
    ) {
        $this->ordenRepo = $ordenRepo;
        $this->repuestoRepo = $repuestoRepo;
        $this->ncRepo = $ncRepo;
        $this->dashboardRepo = $dashboardRepo;
    }

    public function obtenerMetricasGlobales(int $tecnicoId, bool $esAdmin, bool $esSuperadmin, int $sucursalId): array
    {
        try {
            $metricas = [
                'mis_ordenes_activas' => $this->ordenRepo->contarOrdenesActivasPorTecnico($tecnicoId),
                'equipos_reparados_mes' => $this->ordenRepo->contarEquiposReparadosMesActual()
            ];

            // Las metricas administrativas se cargan solo si el usuario tiene privilegios
            if ($esAdmin) {
                $metricas['ordenes_activas_globales'] = $this->ordenRepo->contarOrdenesActivasGlobales();
                $metricas['repuestos_pendientes'] = $this->repuestoRepo->contarSolicitudesPendientes();
                $metricas['nc_pendientes'] = $this->ncRepo->contarSolicitudesNcPendientes();

                $metricas['dashboard'] = $this->dashboardRepo->obtenerDatosGestion($esSuperadmin, $sucursalId);
            } else {
                $metricas['dashboard'] = $this->dashboardRepo->obtenerDatosTecnico($tecnicoId);
            }

            Log::info('Metricas de dashboard calculadas correctamente.', ['tecnico_id' => $tecnicoId]);

            return $metricas;

        } catch (Exception $e) {
            Log::error('Error al calcular metricas del dashboard.', ['error' => $e->getMessage()]);
            throw new Exception('Fallo al procesar los indicadores de rendimiento.');
        }
    }
}
