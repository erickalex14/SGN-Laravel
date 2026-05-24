<?php

namespace App\Services\Operations;

use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class GestionOrdenService
{
    protected OrdenRepository $repository;

    public function __construct(OrdenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function actualizarEstado(CambiarEstadoOrdenDTO $dto, int $usuarioModificacionId): void
    {
        $orden = $this->repository->buscarPorId($dto->orden_id);

        if (!$orden) {
            Log::error('Intento de actualizacion en orden inexistente.', ['orden_id' => $dto->orden_id]);
            throw new Exception('La orden especificada no existe en el sistema.');
        }

        $estadoAnterior = $orden->estado_orden;
        $orden->estado_orden = strtoupper(trim($dto->estado_orden));
        $orden->modificado_por = $usuarioModificacionId;
        $orden->fecha_modificacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

        // Registrar fecha de finalizacion si el estado corresponde al fin del flujo tecnico
        if (in_array($orden->estado_orden, ['REPARADO', 'ENTREGADO', 'DEVUELTO SIN REPARAR'])) {
            if (!$orden->fecha_finalizacion) {
                $orden->fecha_finalizacion = $orden->fecha_modificacion;
            }
        }

        $orden->save();

        Log::info('Estado de orden de servicio actualizado.', [
            'orden_id'        => $orden->id,
            'nro_orden'       => $orden->nro_orden,
            'estado_anterior' => $estadoAnterior,
            'nuevo_estado'    => $orden->estado_orden,
            'tecnico_id'      => $usuarioModificacionId
        ]);
    }
}