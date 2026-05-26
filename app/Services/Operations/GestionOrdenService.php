<?php

namespace App\Services\Operations;

use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\NotaCreditoRepository;
use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use App\Models\Operations\Orden;
use App\Models\Operations\SolicitudNc;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class GestionOrdenService
{
    protected OrdenRepository $repository;
    protected NotaCreditoRepository $notaCreditoRepository;

    public function __construct(
        OrdenRepository $repository,
        NotaCreditoRepository $notaCreditoRepository
    )
    {
        $this->repository = $repository;
        $this->notaCreditoRepository = $notaCreditoRepository;
    }

    /**
     * @throws Exception
     */
    public function actualizarEstado(
        CambiarEstadoOrdenDTO $dto,
        int $usuarioModificacionId,
        string $tecnicoNombre,
        bool $esAdmin = false
    ): void
    {
        $orden = $this->repository->buscarPorId($dto->orden_id);

        if (!$orden) {
            Log::error('Intento de actualizacion en orden inexistente.', ['orden_id' => $dto->orden_id]);
            throw new Exception('La orden especificada no existe en el sistema.');
        }

        if (!$esAdmin && (int) $orden->tecnico_id !== $usuarioModificacionId) {
            throw new Exception('Sin permiso sobre esta orden.');
        }

        $estadoAnterior = $orden->estado_orden;
        $estadoNormalizado = $this->normalizarEstado($dto->estado_orden);
        $this->validarTransicion($orden, $estadoNormalizado, $dto);

        DB::transaction(function () use ($orden, $estadoNormalizado, $usuarioModificacionId, $dto, $tecnicoNombre): void {
            $orden->estado_orden = $estadoNormalizado;
            $orden->modificado_por = $usuarioModificacionId;
            $orden->fecha_modificacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

            if ($estadoNormalizado === 'Nota de Credito') {
                $orden->fecha_finalizacion = $orden->fecha_modificacion;
                $orden->fecha_entrega = null;
            } elseif ($estadoNormalizado === 'Finalizada') {
                $orden->fecha_finalizacion = $orden->fecha_modificacion;
                $orden->fecha_entrega = null;
            } elseif ($estadoNormalizado === 'Entregada') {
                $orden->fecha_entrega = $orden->fecha_modificacion;
            } else {
                $orden->fecha_finalizacion = null;
                $orden->fecha_entrega = null;
            }

            $orden->save();

            if ($estadoNormalizado === 'Nota de Credito') {
                $solicitud = SolicitudNc::where('orden_id', $orden->id)->first();
                if (!$solicitud) {
                    $solicitud = new SolicitudNc();
                    $solicitud->nro_solicitud = $this->notaCreditoRepository->generarNumeroSolicitud();
                    $solicitud->orden_id = $orden->id;
                    $solicitud->estado = 'Pendiente';
                }

                $solicitud->fecha_solicitud = Carbon::now('America/Guayaquil')->format('Y-m-d');
                $solicitud->asunto = trim((string) $dto->nc_asunto);
                $solicitud->detalles = trim((string) $dto->nc_detalles);
                $solicitud->tecnico_id = $usuarioModificacionId;
                $solicitud->tecnico_nombre = trim($tecnicoNombre);
                $solicitud->save();
            }
        });

        Log::info('Estado de orden de servicio actualizado.', [
            'orden_id'        => $orden->id,
            'nro_orden'       => $orden->nro_orden,
            'estado_anterior' => $estadoAnterior,
            'nuevo_estado'    => $orden->estado_orden,
            'tecnico_id'      => $usuarioModificacionId
        ]);
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

    /**
     * @throws Exception
     */
    private function validarTransicion(Orden $orden, string $nuevoEstado, CambiarEstadoOrdenDTO $dto): void
    {
        $estadoActual = trim((string) $orden->estado_orden);
        $motivo = trim((string) $orden->motivo_ingreso);
        $estadoGarantia = trim((string) ($orden->estado_garantia ?? ''));

        if (!in_array($nuevoEstado, ['Pendiente', 'En proceso', 'Finalizada', 'Entregada', 'Nota de Credito'], true)) {
            throw new Exception('Estado no permitido.');
        }

        if ($estadoActual === 'Entregada') {
            throw new Exception('La orden ya fue entregada y no puede modificarse.');
        }

        if ($estadoActual === 'Nota de Credito') {
            throw new Exception('La orden ya tiene Nota de Credito y no puede modificarse.');
        }

        if ($estadoActual === 'Finalizada' && !in_array($nuevoEstado, ['Entregada', 'Nota de Credito'], true)) {
            throw new Exception('Una orden finalizada solo puede cambiar a Entregada o Nota de Credito.');
        }

        if ($nuevoEstado === 'Finalizada' && !$orden->informes()->exists()) {
            throw new Exception('Debes registrar el informe tecnico antes de finalizar la orden.');
        }

        if (
            $motivo === 'Validacion de Garantia'
            && in_array($nuevoEstado, ['Finalizada', 'Entregada'], true)
            && ($estadoGarantia === '' || $estadoGarantia === 'Pendiente')
        ) {
            throw new Exception('Define el estado de garantia antes de finalizar o entregar.');
        }

        if ($nuevoEstado !== 'Nota de Credito') {
            return;
        }

        if (trim((string) $dto->nc_asunto) === '') {
            throw new Exception('El asunto es obligatorio.');
        }

        if (trim((string) $dto->nc_detalles) === '') {
            throw new Exception('Los detalles son obligatorios.');
        }

        if ($motivo !== 'Validacion de Garantia') {
            throw new Exception('La Nota de Credito solo aplica a ordenes de Validacion de Garantia.');
        }

        if ($estadoGarantia === 'Rechazada') {
            throw new Exception('Garantia rechazada. No se puede emitir Nota de Credito.');
        }

        if ($estadoGarantia !== 'Aceptada') {
            throw new Exception('La garantia debe estar Aceptada para emitir Nota de Credito.');
        }

        if (!$orden->informes()->exists()) {
            throw new Exception('Debe registrar un informe tecnico antes de solicitar la Nota de Credito.');
        }

    }
}
