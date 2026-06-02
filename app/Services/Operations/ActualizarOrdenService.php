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
                $orden->estado_orden           = $this->normalizarEstado($dto->estado_orden);
                $orden->valor_estandar_id      = $dto->valor_estandar_id;
                $orden->repuesto_inventario_id = $dto->repuesto_inventario_id;
                $orden->fecha_prometido        = $dto->fecha_prometido;
                $orden->modificado_por         = $dto->usuario_modificacion_id;
                $orden->fecha_modificacion     = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

                // Cierre automatico si el estado corresponde
                if (in_array($orden->estado_orden, ['Finalizada', 'Entregada', 'Devuelto sin reparar', 'Nota de Credito', 'REPARADO', 'ENTREGADO', 'DEVUELTO SIN REPARAR'], true)) {
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

    /**
     * @throws Exception
     */
    public function actualizarOrdenEmpresa(array $data, int $usuarioModificacionId): void
    {
        $orden = \App\Models\Operations\OrdenEmpresa::find((int) $data['orden_id']);
        
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        try {
            DB::transaction(function () use ($orden, $data, $usuarioModificacionId) {
                // 1. Actualizar datos de la Orden
                $orden->estado = $this->normalizarEstado($data['estado']);
                $orden->fecha_prometido = $data['fecha_prometido'];
                $orden->descripcion = trim($data['descripcion']);

                $esServicios = ($orden->subtipo === 'Servicios');

                if ($esServicios) {
                    $orden->valor_hora = (float) ($data['valor_hora'] ?? 0);
                    $orden->horas_trabajadas = (float) ($data['horas_trabajadas'] ?? 0);

                    $tecnicosAsignados = $data['tecnicos_asignados'] ?? [];
                    if (!is_array($tecnicosAsignados)) {
                        $tecnicosAsignados = [$tecnicosAsignados];
                    }
                    $tecnicosAsignados = array_map('intval', array_filter($tecnicosAsignados));

                    if (!empty($tecnicosAsignados)) {
                        $orden->tecnico_id = (int) $tecnicosAsignados[0];
                        $orden->tecnicos()->sync($tecnicosAsignados);
                    }
                }

                $orden->save();

                // 2. Actualizar datos del Equipo
                $equipo = Equipo::find((int) $data['equipo_id']);
                if ($equipo) {
                    $equipo->observacion = trim($data['eq_observacion'] ?? '');
                    if ($orden->subtipo !== 'Servicios') {
                        $equipo->falla = trim($data['descripcion']);
                    }
                    $equipo->save();
                }

                Log::info('Orden de empresa actualizada mediante modulo de edicion.', [
                    'orden_id' => $orden->id,
                    'nro_orden' => $orden->nro_orden,
                    'tecnico_id' => $usuarioModificacionId
                ]);
            });
        } catch (Exception $e) {
            Log::error('Error transaccional al actualizar orden de empresa.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error interno al actualizar la orden corporativa. Los cambios fueron revertidos.');
        }
    }

    private function normalizarEstado(string $estado): string
    {
        $estado = trim($estado);

        $map = [
            'INGRESO' => 'Pendiente',
            'REVISIÓN' => 'En proceso',
            'REVISION' => 'En proceso',
            'ESPERA REPUESTO' => 'En proceso',
            'REPARADO' => 'Finalizada',
            'ENTREGADO' => 'Entregada',
            'DEVUELTO SIN REPARAR' => 'Devuelto sin reparar'
        ];

        return $map[$estado] ?? $estado;
    }
}