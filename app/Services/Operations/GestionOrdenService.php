<?php

namespace App\Services\Operations;

use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\NotaCreditoRepository;
use App\Repositories\Operations\OrdenRepuestoRepository;
use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use App\DTOs\Operations\CambiarEstadoRepuestoDTO;
use App\DTOs\Operations\CambiarEstadoGarantiaDTO;
use App\DTOs\Operations\AsignarRepuestoOrdenDTO;
use App\DTOs\Operations\RevertirRepuestoOrdenDTO;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Operations\SolicitudNc;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class GestionOrdenService
{
    protected OrdenRepository $repository;
    protected NotaCreditoRepository $notaCreditoRepository;
    protected OrdenRepuestoRepository $ordenRepuestoRepository;

    public function __construct(
        OrdenRepository $repository,
        NotaCreditoRepository $notaCreditoRepository,
        OrdenRepuestoRepository $ordenRepuestoRepository
    )
    {
        $this->repository = $repository;
        $this->notaCreditoRepository = $notaCreditoRepository;
        $this->ordenRepuestoRepository = $ordenRepuestoRepository;
    }

    /**
     * @throws Exception
     */
    public function actualizarEstadoEmpresa(int $ordenId, string $estado, int $usuarioId, bool $esAdmin = false): void
    {
        $orden = $this->repository->obtenerOrdenEmpresaCompleta($ordenId);
        if (!$orden) {
            throw new Exception('La orden de empresa especificada no existe.');
        }

        $esTecnicoAsignado = ((int) $orden->tecnico_id === $usuarioId)
            || ($orden->subtipo === 'Servicios' && $orden->tecnicos()->where('tecnico_id', $usuarioId)->exists());

        if (!$esAdmin && !$esTecnicoAsignado) {
            throw new Exception('Sin permiso sobre esta orden.');
        }

        $estadoAnterior = (string) $orden->estado;
        $estadoNormalizado = $this->normalizarEstado($estado);
        $this->validarTransicionEmpresa($orden, $estadoNormalizado);

        $orden->estado = $estadoNormalizado;

        // Cierre y entrega automática para empresas
        if (in_array($estadoNormalizado, ['Finalizada', 'Entregada', 'Devuelto sin reparar', 'Nota de Credito', 'REPARADO', 'ENTREGADO', 'DEVUELTO SIN REPARAR'], true)) {
            if (!$orden->fecha_finalizacion) {
                $orden->fecha_finalizacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
            }
            if ($estadoNormalizado === 'Entregada' || $estadoNormalizado === 'ENTREGADO') {
                if (!$orden->fecha_entrega) {
                    $orden->fecha_entrega = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
                }
            } else {
                $orden->fecha_entrega = null;
            }
        } else {
            $orden->fecha_finalizacion = null;
            $orden->fecha_entrega = null;
        }

        $orden->save();

        Log::info('Estado de orden de empresa actualizado.', [
            'orden_empresa_id' => $orden->id,
            'nro_orden' => $orden->nro_orden,
            'estado_anterior' => $estadoAnterior,
            'nuevo_estado' => $orden->estado,
            'usuario_id' => $usuarioId,
        ]);

        if ($estadoAnterior !== $estadoNormalizado) {
            try {
                \App\Services\Operations\SgnMailService::enviarOrdenEstadoCambiado($orden, $estadoAnterior, $estadoNormalizado);
            } catch (\Throwable $e) {
                Log::error('Error al enviar mail de cambio de estado gestion empresa', ['error' => $e->getMessage()]);
            }
        }
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

        if ($estadoAnterior !== $estadoNormalizado) {
            try {
                \App\Services\Operations\SgnMailService::enviarOrdenEstadoCambiado($orden, $estadoAnterior, $estadoNormalizado);
            } catch (\Throwable $e) {
                Log::error('Error al enviar mail de cambio de estado gestion', ['error' => $e->getMessage()]);
            }
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

    /**
     * @throws Exception
     */
    private function validarTransicionEmpresa(OrdenEmpresa $orden, string $nuevoEstado): void
    {
        $estadoActual = trim((string) $orden->estado);

        if (!in_array($nuevoEstado, ['Pendiente', 'En proceso', 'Finalizada', 'Entregada'], true)) {
            throw new Exception('Estado no permitido para orden de empresa.');
        }

        if ($estadoActual === 'Entregada') {
            throw new Exception('La orden ya fue entregada y no puede modificarse.');
        }

        if ($estadoActual === 'Finalizada' && $nuevoEstado !== 'Entregada') {
            throw new Exception('Una orden finalizada solo puede cambiar a Entregada.');
        }
    }

    /**
     * @throws Exception
     */
    public function actualizarEstadoRepuesto(CambiarEstadoRepuestoDTO $dto, int $usuarioId, bool $esAdmin = false, string $tipoOrden = 'personal'): void
    {
        if ($tipoOrden === 'empresa') {
            $orden = \App\Models\Operations\OrdenEmpresa::find($dto->orden_id);
        } else {
            $orden = $this->repository->buscarPorId($dto->orden_id);
        }
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        if (!$esAdmin && (int) $orden->tecnico_id !== $usuarioId) {
            throw new Exception('Sin permiso sobre esta orden.');
        }

        $estadoOrden = $tipoOrden === 'empresa' ? $orden->estado : $orden->estado_orden;
        if (in_array((string) $estadoOrden, ['Entregada', 'Nota de Credito'], true)) {
            throw new Exception('La orden no puede modificarse en su estado actual.');
        }

        $styleEstado = trim($dto->estado_repuesto);
        if (!in_array($styleEstado, ['No requerido', 'Requerido', 'Con stock'], true)) {
            throw new Exception('Estado de repuesto no permitido.');
        }

        $orden->estado_repuesto = $styleEstado;
        if ($styleEstado !== 'Con stock') {
            $orden->repuesto_inventario_id = null;
        }
        if ($tipoOrden !== 'empresa') {
            $orden->modificado_por = $usuarioId > 0 ? $usuarioId : null;
            $orden->fecha_modificacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
        }
        $orden->save();
    }

    /**
     * @throws Exception
     */
    public function actualizarEstadoGarantia(CambiarEstadoGarantiaDTO $dto, int $usuarioId, bool $esAdmin = false): void
    {
        $orden = $this->repository->buscarPorId($dto->orden_id);
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        if (!$esAdmin && (int) $orden->tecnico_id !== $usuarioId) {
            throw new Exception('Sin permiso sobre esta orden.');
        }

        if (trim((string) $orden->motivo_ingreso) !== 'Validacion de Garantia') {
            throw new Exception('Solo las ordenes de validacion de garantia permiten este cambio.');
        }

        if (in_array((string) $orden->estado_orden, ['Entregada', 'Nota de Credito'], true)) {
            throw new Exception('La orden no puede modificarse en su estado actual.');
        }

        $estado = trim($dto->estado_garantia);
        if (!in_array($estado, ['Pendiente', 'Aceptada', 'Rechazada'], true)) {
            throw new Exception('Estado de garantia no permitido.');
        }

        $orden->estado_garantia = $estado;
        $orden->modificado_por = $usuarioId > 0 ? $usuarioId : null;
        $orden->fecha_modificacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
        $orden->save();
    }

    /**
     * @throws Exception
     */
    public function asignarRepuesto(AsignarRepuestoOrdenDTO $dto, int $usuarioId, bool $esAdmin = false, string $tipoOrden = 'personal'): void
    {
        if ($tipoOrden === 'empresa') {
            $orden = \App\Models\Operations\OrdenEmpresa::find($dto->orden_id);
        } else {
            $orden = $this->repository->buscarPorId($dto->orden_id);
        }
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        if (!$esAdmin && (int) $orden->tecnico_id !== $usuarioId) {
            throw new Exception('Sin permiso sobre esta orden.');
        }

        $estadoOrden = $tipoOrden === 'empresa' ? $orden->estado : $orden->estado_orden;
        if (in_array((string) $estadoOrden, ['Entregada', 'Nota de Credito'], true)) {
            throw new Exception('La orden no puede modificarse en su estado actual.');
        }

        DB::transaction(function () use ($dto, $orden, $usuarioId, $tipoOrden): void {
            $this->ordenRepuestoRepository->asignarRepuestoEnOrden(
                (int) $orden->id,
                (int) $dto->repuesto_inventario_id,
                $usuarioId,
                true,
                $tipoOrden
            );

            $orden->repuesto_inventario_id = (int) $dto->repuesto_inventario_id;
            $orden->estado_repuesto = 'Con stock';
            if ($tipoOrden !== 'empresa') {
                $orden->modificado_por = $usuarioId > 0 ? $usuarioId : null;
                $orden->fecha_modificacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
            }
            $orden->save();
        });
    }

    /**
     * @throws Exception
     */
    public function revertirRepuesto(RevertirRepuestoOrdenDTO $dto, int $usuarioId, bool $esAdmin = false, string $tipoOrden = 'personal'): void
    {
        if (!$esAdmin) {
            throw new Exception('No autorizado para revertir repuestos.');
        }

        if ($tipoOrden === 'empresa') {
            $orden = \App\Models\Operations\OrdenEmpresa::find($dto->orden_id);
        } else {
            $orden = $this->repository->buscarPorId($dto->orden_id);
        }
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        DB::transaction(function () use ($dto, $orden, $usuarioId, $tipoOrden): void {
            $this->ordenRepuestoRepository->revertirRepuestosDeOrden(
                (int) $orden->id,
                $dto->repuesto_id,
                $tipoOrden
            );

            // Verificar si aún quedan repuestos asignados en la orden
            $queryRestantes = \App\Models\Operations\OrdenRepuesto::query();
            if ($tipoOrden === 'empresa') {
                $queryRestantes->where('orden_empresa_id', $orden->id);
            } else {
                $queryRestantes->where('orden_id', $orden->id);
            }

            $restantes = $queryRestantes->get();
            if ($restantes->isNotEmpty()) {
                $orden->repuesto_inventario_id = $restantes->first()->repuesto_id;
                $orden->estado_repuesto = 'Con stock';
            } else {
                $orden->repuesto_inventario_id = null;
                $orden->estado_repuesto = 'No requerido';
            }
            if ($tipoOrden !== 'empresa') {
                $orden->modificado_por = $usuarioId > 0 ? $usuarioId : null;
                $orden->fecha_modificacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
            }
            $orden->save();
        });
    }

    /**
     * @throws Exception
     */
    public function reasignarTecnico(int $ordenId, int $nuevoTecnicoId, string $tipoOrden = 'personal'): void
    {
        if ($tipoOrden === 'empresa') {
            $orden = $this->repository->obtenerOrdenEmpresaCompleta($ordenId);
            if (!$orden) {
                throw new Exception('La orden de empresa especificada no existe.');
            }
            $orden->tecnico_id = $nuevoTecnicoId;
            $orden->save();
        } else {
            $orden = $this->repository->buscarPorId($ordenId);
            if (!$orden) {
                throw new Exception('La orden especificada no existe.');
            }
            $orden->tecnico_id = $nuevoTecnicoId;
            $orden->save();
        }

        Log::info('Orden reasignada a nuevo técnico.', [
            'orden_id' => $ordenId,
            'nuevo_tecnico_id' => $nuevoTecnicoId,
            'tipo_orden' => $tipoOrden,
        ]);
    }
}
