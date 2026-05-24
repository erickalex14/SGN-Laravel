<?php

namespace App\Services\Operations;

use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\ActualizarOrdenDTO;
use App\Models\Operations\Equipo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ActualizarOrdenService
{
    protected OrdenRepository $ordenRepo;

    public function __construct(OrdenRepository $ordenRepo)
    {
        $this->ordenRepo = $ordenRepo;
    }

    /**
     * @throws Exception
     */
    public function actualizarOrden(ActualizarOrdenDTO $dto): void
    {
        $orden = $this->ordenRepo->buscarPorId($dto->orden_id);
        
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        try {
            DB::transaction(function () use ($orden, $dto) {
                // 1. Actualizar datos del Equipo
                $equipo = Equipo::find($dto->equipo_id);
                if ($equipo) {
                    $equipo->falla            = trim($dto->falla);
                    $equipo->observacion      = trim($dto->observacion);
                    $equipo->tipo_servicio_id = $dto->tipo_servicio_id;
                    $equipo->save();
                }

                // 2. Actualizar datos de la Orden
                $orden->estado_orden           = strtoupper(trim($dto->estado_orden));
                $orden->valor_estandar_id      = $dto->valor_estandar_id;
                $orden->repuesto_inventario_id = $dto->repuesto_inventario_id;
                $orden->fecha_prometido        = $dto->fecha_prometido;
                $orden->modificado_por         = $dto->usuario_modificacion_id;
                $orden->fecha_modificacion     = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

                // Cierre automatico si el estado corresponde
                if (in_array($orden->estado_orden, ['REPARADO', 'ENTREGADO', 'DEVUELTO SIN REPARAR'])) {
                    if (!$orden->fecha_finalizacion) {
                        $orden->fecha_finalizacion = $orden->fecha_modificacion;
                    }
                }

                $orden->save();

                Log::info('Orden de servicio actualizada mediante modulo de edicion.', [
                    'orden_id' => $orden->id,
                    'nro_orden' => $orden->nro_orden,
                    'tecnico_id' => $dto->usuario_modificacion_id
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error transaccional al actualizar orden.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error interno al actualizar la orden. Los cambios fueron revertidos.');
        }
    }
}