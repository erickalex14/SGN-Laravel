<?php

namespace App\Services\Operations;

use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\ReporteFiltroDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

class ReporteService
{
    protected OrdenRepository $ordenRepo;

    public function __construct(OrdenRepository $ordenRepo)
    {
        $this->ordenRepo = $ordenRepo;
    }

    /**
     * @throws Exception
     */
    public function generarReporte(
        ReporteFiltroDTO $dto,
        int $usuarioSolicitanteId,
        bool $esMaster,
        int $sucursalSesion
    ): Collection
    {
        try {
            $resultados = $this->ordenRepo->filtrarParaReporte($dto, $esMaster, $sucursalSesion);
            
            Log::info('Reporte de ordenes generado.', [
                'usuario_id' => $usuarioSolicitanteId,
                'total_registros' => $resultados->count()
            ]);

            return $resultados;
        } catch (Exception $e) {
            Log::error('Fallo al generar reporte de ordenes.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error al procesar los datos del reporte.');
        }
    }
}
