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
use App\Services\Operations\AuditLogger;

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
    public function actualizarEstadoEmpresa(
        int $ordenId,
        string $estado,
        int $usuarioId,
        bool $esAdmin = false,
        ?float $horasTrabajadas = null,
        ?float $valorHora = null,
        ?string $memoEntrega = null,
        ?string $fotoEvidenciaEntrega = null,
        ?string $tituloServicio = null,
        ?float $valorManoObra = null
    ): void {
        $orden = $this->repository->obtenerOrdenEmpresaCompleta($ordenId);
        if (!$orden) {
            throw new Exception('La orden de empresa especificada no existe.');
        }

        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $sessionRol = mb_strtolower(trim((string) session('rol_nombre', '')));
        $esAdmin = $esAdmin || session('es_superadmin') === true || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);
        $esRecepcion = session('es_recepcion') === true || in_array($sessionGrupo, ['recepcion', 'recepción'], true) || in_array($sessionRol, ['recepcion', 'recepcionista'], true);
        $esRecepcionSucursal = $esRecepcion && ((int) session('sucursal_id', 0) === 0 || (int) $orden->sucursal_id === (int) session('sucursal_id') || $esAdmin);

        $esTecnicoAsignado = ((int) $orden->tecnico_id === $usuarioId)
            || ($orden->subtipo === 'Servicios' && $orden->tecnicos()->where('tecnico_id', $usuarioId)->exists());

        $esEmpresaPermitidaRecepcion = in_array($orden->subtipo, ['Stock', 'Autoconsumo'], true);

        if (!$esAdmin && !$esTecnicoAsignado && !($esRecepcionSucursal && $esEmpresaPermitidaRecepcion)) {
            throw new Exception('Sin permiso sobre esta orden.');
        }

        $estadoAnterior = (string) $orden->estado;
        $estadoNormalizado = $this->normalizarEstado($estado);
        $this->validarTransicionEmpresa($orden, $estadoNormalizado);

        $orden->estado = $estadoNormalizado;

        if ($memoEntrega !== null && trim($memoEntrega) !== '') {
            $orden->memo_entrega = trim($memoEntrega);
        }
        if ($fotoEvidenciaEntrega !== null && trim($fotoEvidenciaEntrega) !== '') {
            $orden->foto_evidencia_entrega = trim($fotoEvidenciaEntrega);
        }

        if (in_array(mb_strtolower($estadoNormalizado), ['entregada', 'entregado', 'cerrado'], true)) {
            if (empty($orden->foto_evidencia_entrega) && empty($fotoEvidenciaEntrega)) {
                throw new Exception('Debe adjuntar una foto de evidencia de entrega obligatoriamente para marcar la orden como Entregada.');
            }
        }

        if ($horasTrabajadas !== null) {
            $orden->horas_trabajadas = $horasTrabajadas;
        }
        if ($valorHora !== null) {
            $orden->valor_hora = $valorHora;
        }
        if ($orden->empresa && trim(strtoupper($orden->empresa->nombre)) === 'RB-HEALTH ECUADOR CIA LTDA') {
            $orden->valor_hora = 52.0;
        }

        if ($tituloServicio !== null && trim($tituloServicio) !== '') {
            $orden->titulo_servicio = trim($tituloServicio);
        }
        if ($valorManoObra !== null) {
            $orden->valor_mano_obra = round($valorManoObra, 2);
        }

        // Calcular y sincronizar valor_repuestos desde orden_repuestos
        $totalRep = DB::table('orden_repuestos as orp')
            ->join('repuestos as r', 'orp.repuesto_id', '=', 'r.id')
            ->where('orp.orden_empresa_id', $orden->id)
            ->sum(DB::raw('orp.cantidad * r.costo'));
        $orden->valor_repuestos = round((float) $totalRep, 2);

        $now = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
        $orden->fecha_modificacion = $now;
        $orden->modificado_por = $usuarioId;

        // Ciclo de vida y marcas de tiempo para empresas
        if (in_array($estadoNormalizado, ['Recibida', 'Entregado al Tecnico'], true)) {
            if (!$orden->fecha_recibida_tecnico) {
                $orden->fecha_recibida_tecnico = $now;
            }
        } elseif ($estadoNormalizado === 'Finalizada' || $estadoNormalizado === 'REPARADO' || $estadoNormalizado === 'Reparada') {
            $orden->fecha_finalizacion = $now;
            $orden->fecha_entrega = null;
        } elseif (in_array($estadoNormalizado, ['Lista para entrega', 'Entregado en Recepcion para Entrega'], true)) {
            $orden->fecha_lista_entrega = $now;
            if (!$orden->fecha_finalizacion) {
                $orden->fecha_finalizacion = $now;
            }
            $orden->fecha_entrega = null;
        } elseif ($estadoNormalizado === 'Incinerox') {
            $orden->fecha_finalizacion = $now;
            $orden->fecha_entrega = null;
            // Sincronizar inventario físico ST a Incinerox
            \App\Models\Inventory\ProductoInventarioFisicoSt::where('orden_empresa_id', $orden->id)
                ->update(['estado' => 'Incinerox']);
        } elseif (in_array($estadoNormalizado, ['Entregada', 'ENTREGADO'], true)) {
            $orden->fecha_entrega = $now;
            if (!$orden->fecha_lista_entrega) {
                $orden->fecha_lista_entrega = $now;
            }
            if (!$orden->fecha_finalizacion) {
                $orden->fecha_finalizacion = $now;
            }
        } elseif (in_array($estadoNormalizado, ['Devuelto sin reparar', 'DEVUELTO SIN REPARAR', 'Nota de Credito'], true)) {
            $orden->fecha_finalizacion = $now;
            $orden->fecha_entrega = null;
        } else {
            $orden->fecha_finalizacion = null;
            $orden->fecha_entrega = null;
        }

        $orden->save();

        $accion = in_array($estadoNormalizado, ['Finalizada', 'Entregada', 'Devuelto sin reparar', 'Nota de Credito'], true) ? 'CERRAR_ORDEN' : 'EDITAR_ORDEN';
        AuditLogger::registrar($accion, 'ordenes', (string)$orden->id, [
            'nro_orden' => $orden->nro_orden,
            'tipo_orden' => 'empresa',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $orden->estado,
            'fecha_cambio' => $now,
            'horas_trabajadas' => $horasTrabajadas,
            'valor_hora' => $orden->valor_hora,
        ]);

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

        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $esAdmin = $esAdmin || session('es_superadmin') === true || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);
        $esRecepcion = session('es_recepcion') === true || in_array($sessionGrupo, ['recepcion', 'recepción'], true);
        $esTecnicoAsignado = (int) $orden->tecnico_id === $usuarioModificacionId;
        $esRecepcionSucursal = $esRecepcion && ((int) session('sucursal_id', 0) === 0 || (int) $orden->sucursal_id === (int) session('sucursal_id') || $esAdmin);

        if (!$esAdmin && !$esTecnicoAsignado && !$esRecepcionSucursal && !$esRecepcion) {
            throw new Exception('Sin permiso sobre esta orden.');
        }

        $estadoAnterior = $orden->estado_orden;
        $estadoNormalizado = $this->normalizarEstado($dto->estado_orden);
        $this->validarTransicion($orden, $estadoNormalizado, $dto);

        DB::transaction(function () use ($orden, $estadoNormalizado, $usuarioModificacionId, $dto, $tecnicoNombre): void {
            $orden->estado_orden = $estadoNormalizado;
            $orden->modificado_por = $usuarioModificacionId;
            $orden->fecha_modificacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

            if ($dto->memo_entrega !== null && trim($dto->memo_entrega) !== '') {
                $orden->memo_entrega = trim($dto->memo_entrega);
            }
            if ($dto->foto_evidencia_entrega !== null && trim($dto->foto_evidencia_entrega) !== '') {
                $orden->foto_evidencia_entrega = trim($dto->foto_evidencia_entrega);
            }

            if ($dto->titulo_servicio !== null && trim($dto->titulo_servicio) !== '') {
                $orden->titulo_servicio = trim($dto->titulo_servicio);
            }
            if ($dto->valor_mano_obra !== null) {
                $orden->valor_mano_obra = round($dto->valor_mano_obra, 2);
            }

            // Calcular y sincronizar valor_repuestos desde orden_repuestos
            $totalRep = DB::table('orden_repuestos as orp')
                ->join('repuestos as r', 'orp.repuesto_id', '=', 'r.id')
                ->where('orp.orden_id', $orden->id)
                ->sum(DB::raw('orp.cantidad * r.costo'));
            $orden->valor_repuestos = round((float) $totalRep, 2);

            // Si hay mano de obra, registrar o actualizar en preciosorden
            if ($dto->valor_mano_obra !== null && $dto->valor_mano_obra > 0) {
                \App\Models\Operations\PrecioOrden::updateOrCreate(
                    [
                        'orden_id' => $orden->id,
                        'servicio' => $dto->titulo_servicio ?: 'Mano de Obra Técnica'
                    ],
                    [
                        'precio' => round($dto->valor_mano_obra, 2),
                        'descripcion' => $dto->titulo_servicio ?: 'Servicio técnico realizado en taller',
                        'creado_en' => Carbon::now('America/Guayaquil')
                    ]
                );
            }

            if (in_array(mb_strtolower($estadoNormalizado), ['cerrado', 'entregada', 'entregado'], true)) {
                if (empty($orden->foto_evidencia_entrega) && empty($dto->foto_evidencia_entrega)) {
                    throw new Exception('Debe adjuntar una foto de evidencia de entrega obligatoriamente para marcar la orden como Cerrado.');
                }
            }

            if (in_array($estadoNormalizado, ['Entregado al Tecnico', 'Recibida'], true)) {
                if (!$orden->fecha_recibida_tecnico) {
                    $orden->fecha_recibida_tecnico = $orden->fecha_modificacion;
                }
            } elseif ($estadoNormalizado === 'Nota de Credito') {
                $esGarantia = mb_strtolower(trim((string) $orden->motivo_ingreso)) === 'validacion de garantia';
                if ($esGarantia) {
                    $orden->fecha_finalizacion = $orden->transferencia_numero ? $orden->fecha_modificacion : null;
                } else {
                    $orden->fecha_finalizacion = $orden->fecha_modificacion;
                }
                $orden->fecha_entrega = null;
            } elseif (in_array($estadoNormalizado, ['Reparada', 'Finalizada'], true)) {
                $orden->fecha_finalizacion = $orden->fecha_modificacion;
                $orden->fecha_entrega = null;
            } elseif (in_array($estadoNormalizado, ['Entregado en Recepcion para Entrega', 'Lista para entrega'], true)) {
                $orden->fecha_lista_entrega = $orden->fecha_modificacion;
                if (!$orden->fecha_finalizacion) {
                    $orden->fecha_finalizacion = $orden->fecha_modificacion;
                }
                $orden->fecha_entrega = null;
            } elseif (in_array($estadoNormalizado, ['Cerrado', 'Entregada'], true)) {
                $orden->fecha_entrega = $orden->fecha_modificacion;
                if (!$orden->fecha_lista_entrega) {
                    $orden->fecha_lista_entrega = $orden->fecha_modificacion;
                }
                if (!$orden->fecha_finalizacion) {
                    $orden->fecha_finalizacion = $orden->fecha_modificacion;
                }
            } elseif (in_array($estadoNormalizado, ['Recibido en Recepcion', 'Pendiente', 'En reparacion'], true)) {
                $orden->fecha_finalizacion = null;
                $orden->fecha_entrega = null;
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

        $accion = in_array($estadoNormalizado, ['Finalizada', 'Entregada', 'Devuelto sin reparar', 'Nota de Credito'], true) ? 'CERRAR_ORDEN' : 'EDITAR_ORDEN';
        AuditLogger::registrar($accion, 'ordenes', (string)$orden->id, [
            'nro_orden' => $orden->nro_orden,
            'tipo_orden' => 'personal',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $orden->estado_orden,
            'fecha_cambio' => Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'),
        ]);

        if ($estadoNormalizado === 'Nota de Credito') {
            AuditLogger::registrar('CREAR_SOLICITUD_NC', 'notas_credito', (string)$orden->id, [
                'nro_orden' => $orden->nro_orden,
                'asunto' => trim((string) $dto->nc_asunto),
                'detalles' => trim((string) $dto->nc_detalles),
            ]);
        }

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
            'INGRESO'                                => 'Recibido en Recepcion',
            'RECIBIDO EN RECEPCION'                  => 'Recibido en Recepcion',
            'RECIBIDO EN RECEPCIÓN'                  => 'Recibido en Recepcion',
            'RECIBIDA'                               => 'Entregado al Tecnico',
            'ENTREGADO AL TECNICO'                   => 'Entregado al Tecnico',
            'ENTREGADO AL TÉCNICO'                   => 'Entregado al Tecnico',
            'PENDIENTE'                              => 'Pendiente',
            'REVISIÓN'                               => 'En reparacion',
            'REVISION'                               => 'En reparacion',
            'EN PROCESO'                             => 'En reparacion',
            'EN REPARACION'                          => 'En reparacion',
            'EN REPARACIÓN'                          => 'En reparacion',
            'ESPERA REPUESTO'                        => 'En reparacion',
            'REPARADO'                               => 'Reparada',
            'FINALIZADA'                             => 'Reparada',
            'REPARADA'                               => 'Reparada',
            'LISTA PARA ENTREGA'                     => 'Entregado en Recepcion para Entrega',
            'LISTO PARA ENTREGA'                     => 'Entregado en Recepcion para Entrega',
            'ENTREGADO EN RECEPCION PARA ENTREGA'    => 'Entregado en Recepcion para Entrega',
            'ENTREGADO EN RECEPCIÓN PARA ENTREGA'    => 'Entregado en Recepcion para Entrega',
            'ENTREGADA'                              => 'Cerrado',
            'ENTREGADO'                              => 'Cerrado',
            'CERRADO'                                => 'Cerrado',
            'DEVUELTO SIN REPARAR'                   => 'Devuelto sin reparar',
            'INCINEROX'                              => 'Incinerox',
            'NOTA DE CREDITO'                        => 'Nota de Credito',
            'NOTA DE CRÉDITO'                        => 'Nota de Credito',
        ];

        return $map[mb_strtoupper($estado)] ?? ($map[$estado] ?? $estado);
    }

    /**
     * @throws Exception
     */
    private function validarTransicion(Orden $orden, string $nuevoEstado, CambiarEstadoOrdenDTO $dto): void
    {
        $estadoActual = trim((string) $orden->estado_orden);
        $motivo = trim((string) $orden->motivo_ingreso);
        $estadoGarantia = trim((string) ($orden->estado_garantia ?? ''));

        $estadosPermitidos = [
            'Recibido en Recepcion',
            'Entregado al Tecnico',
            'Pendiente',
            'En reparacion',
            'Reparada',
            'Entregado en Recepcion para Entrega',
            'Cerrado',
            'Nota de Credito',
            // Compatibilidad retroactiva
            'Recibida',
            'En proceso',
            'Finalizada',
            'Lista para entrega',
            'Entregada',
            'Devuelto sin reparar'
        ];

        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            throw new Exception("Estado no permitido: {$nuevoEstado}.");
        }

        if (in_array($estadoActual, ['Cerrado', 'Entregada'], true)) {
            throw new Exception('La orden ya fue cerrada/entregada y no puede modificarse.');
        }

        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $esAdmin = session('es_superadmin') === true || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);
        $esRecepcion = session('es_recepcion') === true || in_array($sessionGrupo, ['recepcion', 'recepción'], true);

        // Si la orden está en 'Recibido en Recepcion', debe confirmarse la entrega al técnico
        if ($estadoActual === 'Recibido en Recepcion' && !in_array($nuevoEstado, ['Recibido en Recepcion', 'Entregado al Tecnico', 'Recibida'], true) && !$esAdmin) {
            throw new Exception('Debes confirmar la recepción del equipo en taller ("Entregado al Tecnico") antes de avanzar.');
        }

        // Obligatoriedad de informe técnico para Reparada / Finalizada o Entregado en Recepcion para Entrega
        if (in_array($nuevoEstado, ['Reparada', 'Finalizada', 'Entregado en Recepcion para Entrega', 'Lista para entrega'], true) && !$orden->informes()->exists()) {
            throw new Exception('Debes registrar el informe técnico antes de finalizar la reparación o enviarla a entrega.');
        }

        if (
            $motivo === 'Validacion de Garantia'
            && $estadoActual !== 'Nota de Credito'
            && in_array($nuevoEstado, ['Reparada', 'Finalizada', 'Entregado en Recepcion para Entrega', 'Lista para entrega', 'Cerrado', 'Entregada'], true)
            && ($estadoGarantia === '' || $estadoGarantia === 'Pendiente')
        ) {
            throw new Exception('Define el estado de garantia antes de finalizar o entregar.');
        }

        // Solo Recepción o Admin pueden entregar al cliente y cerrar la orden
        if (in_array($nuevoEstado, ['Cerrado', 'Entregada'], true) && !$esAdmin && !$esRecepcion) {
            throw new Exception('Solo el personal de recepción o administración puede entregar la orden al cliente y cerrarla.');
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

        if ($estadoActual === $nuevoEstado) {
            return;
        }

        $estadosPermitidos = [
            'Pendiente',
            'Recibida',
            'Recibido en Recepcion',
            'Entregado al Tecnico',
            'En proceso',
            'Finalizada',
            'Reparada',
            'Lista para entrega',
            'Entregado en Recepcion para Entrega',
            'Entregada',
            'Cerrado',
            'Incinerox',
            'Devuelto sin reparar'
        ];

        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            throw new Exception('Estado no permitido para orden de empresa.');
        }

        if ($estadoActual === 'Entregada') {
            throw new Exception('La orden ya fue entregada y no puede modificarse.');
        }

        if ($estadoActual === 'Incinerox') {
            throw new Exception('La orden ya fue enviada a Incinerox y no puede modificarse.');
        }

        if ($nuevoEstado === 'Incinerox') {
            if (!$orden->tieneInforme()) {
                throw new Exception('Debe registrar un informe técnico antes de enviar la orden a Incinerox.');
            }
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
                (int) $dto->cantidad,
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

        AuditLogger::registrar('ASIGNAR_REPUESTO', 'inventario', (string)$orden->id, [
            'nro_orden' => $orden->nro_orden,
            'tipo_orden' => $tipoOrden,
            'repuesto_inventario_id' => $dto->repuesto_inventario_id,
        ]);
    }

    /**
     * @throws Exception
     */
    public function revertirRepuesto(RevertirRepuestoOrdenDTO $dto, int $usuarioId, bool $esAdmin = false, string $tipoOrden = 'personal'): void
    {
        if ($tipoOrden === 'empresa') {
            $orden = \App\Models\Operations\OrdenEmpresa::find($dto->orden_id);
        } else {
            $orden = $this->repository->buscarPorId($dto->orden_id);
        }
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        if (!$esAdmin) {
            $tecnicoIdSesion = (int) session('tecnico_id', 0);
            $sucursalIdSesion = (int) session('sucursal_id', 0);

            $esAsignado = false;
            if ($tipoOrden === 'empresa') {
                $esAsignado = ($tecnicoIdSesion > 0 && (int) $orden->tecnico_id === $tecnicoIdSesion)
                    || ($sucursalIdSesion > 0 && (int) $orden->sucursal_id === $sucursalIdSesion)
                    || ($tecnicoIdSesion > 0 && $orden->tecnicos()->where('tecnico_id', $tecnicoIdSesion)->exists());
            } else {
                $esAsignado = ($tecnicoIdSesion > 0 && (int) $orden->tecnico_id === $tecnicoIdSesion)
                    || ($sucursalIdSesion > 0 && (int) $orden->sucursal_id === $sucursalIdSesion);
            }

            if (!$esAsignado) {
                throw new Exception('No está autorizado para revertir repuestos de esta orden.');
            }
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

        AuditLogger::registrar('REVERTIR_REPUESTO', 'inventario', (string)$orden->id, [
            'nro_orden' => $orden->nro_orden,
            'tipo_orden' => $tipoOrden,
            'repuesto_id' => $dto->repuesto_id,
        ]);
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
            $tecnicoAnterior = (int) $orden->tecnico_id;
            $orden->tecnico_id = $nuevoTecnicoId;
            $orden->save();
        } else {
            $orden = $this->repository->buscarPorId($ordenId);
            if (!$orden) {
                throw new Exception('La orden especificada no existe.');
            }
            $tecnicoAnterior = (int) $orden->tecnico_id;
            $orden->tecnico_id = $nuevoTecnicoId;
            $orden->save();
        }

        if ($nuevoTecnicoId !== $tecnicoAnterior && $nuevoTecnicoId > 0) {
            $msg = $tipoOrden === 'empresa'
                ? "Se te ha asignado una nueva orden de empresa: {$orden->nro_orden}"
                : "Se te ha asignado una nueva orden: {$orden->nro_orden}";

            \App\Models\Identity\Notificacion::create([
                'usuario_id' => $nuevoTecnicoId,
                'tipo' => 'orden_asignada',
                'mensaje' => $msg,
                'orden_id' => $orden->id,
                'nro_orden' => $orden->nro_orden,
            ]);
        }

        Log::info('Orden reasignada a nuevo técnico.', [
            'orden_id' => $ordenId,
            'nuevo_tecnico_id' => $nuevoTecnicoId,
            'tipo_orden' => $tipoOrden,
        ]);

        AuditLogger::registrar('EDITAR_ORDEN', 'ordenes', (string)$orden->id, [
            'nro_orden' => $orden->nro_orden,
            'tipo_orden' => $tipoOrden,
            'accion' => 'reasignar_tecnico',
            'tecnico_anterior' => $tecnicoAnterior,
            'tecnico_nuevo' => $nuevoTecnicoId,
        ]);
    }
}




