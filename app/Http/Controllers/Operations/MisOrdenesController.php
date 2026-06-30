<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\CambiarEstadoOrdenRequest;
use App\Http\Requests\Operations\CambiarEstadoRepuestoRequest;
use App\Http\Requests\Operations\CambiarEstadoGarantiaRequest;
use App\Http\Requests\Operations\AsignarRepuestoOrdenRequest;
use App\Http\Requests\Operations\RevertirRepuestoOrdenRequest;
use App\Services\Operations\GestionOrdenService;
use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use App\DTOs\Operations\CambiarEstadoRepuestoDTO;
use App\DTOs\Operations\CambiarEstadoGarantiaDTO;
use App\DTOs\Operations\AsignarRepuestoOrdenDTO;
use App\DTOs\Operations\RevertirRepuestoOrdenDTO;
use App\Services\Identity\ActividadDiariaService;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class MisOrdenesController extends Controller
{
    protected GestionOrdenService $service;
    protected OrdenRepository $repository;
    protected \App\Repositories\Identity\UsuarioRepository $usuarioRepo;
    protected ActividadDiariaService $actividadService;

    public function __construct(
        GestionOrdenService $service,
        OrdenRepository $repository,
        \App\Repositories\Identity\UsuarioRepository $usuarioRepo,
        ActividadDiariaService $actividadService
    )
    {
        $this->service = $service;
        $this->repository = $repository;
        $this->usuarioRepo = $usuarioRepo;
        $this->actividadService = $actividadService;
    }

    public function index(): View
    {
        $tecnicoId = session('tecnico_id');
        
        if (!$tecnicoId) {
            abort(403, 'Sesión de técnico no identificada.');
        }

        $ordenes = $this->repository->obtenerOrdenesPorTecnico($tecnicoId);

        // Cargar los técnicos del mismo CAS o sucursal del técnico en sesión
        $verTodosTecnicos = $this->resolverEsAdmin();
        $tecnicos = $this->usuarioRepo->obtenerTecnicosConCargaActual(
            $verTodosTecnicos,
            (int) session('sucursal_id'),
            $tecnicoId
        );

        return view('operations.mis_ordenes.index', compact('ordenes', 'tecnicos'));
    }

    public function cambiarEstado(CambiarEstadoOrdenRequest $request): JsonResponse
    {
        try {
            $ordenId = (int) $request->input('id');
            $tipoOrden = $request->input('tipo_orden');
            if ($tipoOrden === 'empresa') {
                $orden = \App\Models\Operations\OrdenEmpresa::with(['empresa', 'equipo'])->find($ordenId);
                $estadoAnterior = $orden ? $orden->estado : '';
            } else {
                $orden = \App\Models\Operations\Orden::with(['cliente', 'equipo'])->find($ordenId);
                $estadoAnterior = $orden ? $orden->estado_orden : '';
            }

            $dto = new CambiarEstadoOrdenDTO(
                $ordenId,
                (string) $request->input('estado'),
                $request->input('nc_asunto'),
                $request->input('nc_detalles')
            );

            $usuarioModificacionId = (int) session('tecnico_id', 0);
            $tecnicoNombre = (string) (session('nombre_tecnico') ?? session('nombre') ?? session('usuario') ?? '');
            $esAdmin = $this->resolverEsAdmin();

            if ($tipoOrden === 'empresa') {
                $this->service->actualizarEstadoEmpresa(
                    $ordenId,
                    (string) $request->input('estado'),
                    $usuarioModificacionId,
                    $esAdmin
                );

                if ($orden) {
                    $estadoNuevo = $request->input('estado');
                    $this->actividadService->registrar(
                        usuarioId: $usuarioModificacionId,
                        tipoAccion: 'cambiar_estado_empresa',
                        descripcion: "Cambió estado de orden de empresa #{$orden->nro_orden} de '{$estadoAnterior}' a '{$estadoNuevo}'",
                        modulo: 'ordenes',
                        referenciaId: $orden->id,
                        referenciaTipo: 'orden_empresa',
                        metadata: [
                            'nro_orden' => $orden->nro_orden,
                            'cliente' => $orden->empresa?->nombre ?? '',
                            'serie' => $orden->equipo?->serie ?? 'sn',
                            'marca' => $orden->equipo?->marca ?? 'sn',
                            'tipo' => $orden->equipo?->tipo ?? 'sn',
                            'estado_anterior' => $estadoAnterior,
                            'estado_nuevo' => $estadoNuevo
                        ]
                    );
                }

                return response()->json([
                    'ok' => true,
                    'mensaje' => 'El estado de la orden de empresa ha sido actualizado correctamente.'
                ]);
            }

            $this->service->actualizarEstado($dto, $usuarioModificacionId, $tecnicoNombre, $esAdmin);

            if ($orden) {
                $estadoNuevo = $request->input('estado');
                $this->actividadService->registrar(
                    usuarioId: $usuarioModificacionId,
                    tipoAccion: 'cambiar_estado',
                    descripcion: "Cambió estado de orden #{$orden->nro_orden} de '{$estadoAnterior}' a '{$estadoNuevo}'",
                    modulo: 'ordenes',
                    referenciaId: $orden->id,
                    referenciaTipo: 'orden',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? '',
                        'serie' => $orden->equipo?->serie ?? 'sn',
                        'marca' => $orden->equipo?->marca ?? 'sn',
                        'tipo' => $orden->equipo?->tipo ?? 'sn',
                        'estado_anterior' => $estadoAnterior,
                        'estado_nuevo' => $estadoNuevo
                    ]
                );
            }

            return response()->json([
                'ok'      => true,
                'mensaje' => 'El estado de la orden ha sido actualizado correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cambiarEstadoRepuesto(CambiarEstadoRepuestoRequest $request): JsonResponse
    {
        try {
            $ordenId = (int) $request->input('orden_id');
            $tipoOrden = $request->input('tipo_orden', 'personal');

            if ($tipoOrden === 'empresa') {
                $orden = \App\Models\Operations\OrdenEmpresa::with(['empresa', 'equipo'])->find($ordenId);
                $estadoAnterior = $orden ? $orden->estado_repuesto : '';
            } else {
                $orden = \App\Models\Operations\Orden::with(['cliente', 'equipo'])->find($ordenId);
                $estadoAnterior = $orden ? $orden->estado_repuesto : '';
            }

            $dto = new CambiarEstadoRepuestoDTO(
                $ordenId,
                (string) $request->input('estado_repuesto')
            );

            $this->service->actualizarEstadoRepuesto(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin(),
                $tipoOrden
            );

            if ($orden) {
                $estadoNuevo = $request->input('estado_repuesto');
                $clienteNombre = $tipoOrden === 'empresa'
                    ? ($orden->empresa?->nombre ?? '')
                    : ($orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? '');

                $this->actividadService->registrar(
                    usuarioId: (int) session('tecnico_id'),
                    tipoAccion: 'cambiar_estado_repuesto',
                    descripcion: "Cambió estado repuesto orden #{$orden->nro_orden} a '{$estadoNuevo}'",
                    modulo: 'ordenes',
                    referenciaId: $orden->id,
                    referenciaTipo: $tipoOrden === 'empresa' ? 'orden_empresa' : 'orden',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $clienteNombre,
                        'serie' => $orden->equipo?->serie ?? 'sn',
                        'marca' => $orden->equipo?->marca ?? 'sn',
                        'tipo' => $orden->equipo?->tipo ?? 'sn',
                        'estado_anterior' => $estadoAnterior,
                        'estado_nuevo' => $estadoNuevo
                    ]
                );
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Estado de repuesto actualizado.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cambiarEstadoGarantia(CambiarEstadoGarantiaRequest $request): JsonResponse
    {
        try {
            $ordenId = (int) $request->input('orden_id');
            $orden = \App\Models\Operations\Orden::with(['cliente', 'equipo'])->find($ordenId);
            $estadoAnterior = $orden ? $orden->estado_garantia : '';

            $dto = new CambiarEstadoGarantiaDTO(
                $ordenId,
                (string) $request->input('estado_garantia')
            );

            $this->service->actualizarEstadoGarantia(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin()
            );

            if ($orden) {
                $estadoNuevo = $request->input('estado_garantia');
                $this->actividadService->registrar(
                    usuarioId: (int) session('tecnico_id'),
                    tipoAccion: 'cambiar_estado_garantia',
                    descripcion: "Cambió estado garantía orden #{$orden->nro_orden} a '{$estadoNuevo}'",
                    modulo: 'ordenes',
                    referenciaId: $orden->id,
                    referenciaTipo: 'orden',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? '',
                        'serie' => $orden->equipo?->serie ?? 'sn',
                        'marca' => $orden->equipo?->marca ?? 'sn',
                        'tipo' => $orden->equipo?->tipo ?? 'sn',
                        'estado_anterior' => $estadoAnterior,
                        'estado_nuevo' => $estadoNuevo
                    ]
                );
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Estado de garantia actualizado.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asignarRepuesto(AsignarRepuestoOrdenRequest $request): JsonResponse
    {
        try {
            $ordenId = (int) $request->input('orden_id');
            $tipoOrden = $request->input('tipo_orden', 'personal');

            if ($tipoOrden === 'empresa') {
                $orden = \App\Models\Operations\OrdenEmpresa::with(['empresa', 'equipo'])->find($ordenId);
            } else {
                $orden = \App\Models\Operations\Orden::with(['cliente', 'equipo'])->find($ordenId);
            }

            $dto = new AsignarRepuestoOrdenDTO(
                $ordenId,
                (int) $request->input('repuesto_inventario_id')
            );

            $this->service->asignarRepuesto(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin(),
                $tipoOrden
            );

            if ($orden) {
                $clienteNombre = $tipoOrden === 'empresa'
                    ? ($orden->empresa?->nombre ?? '')
                    : ($orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? '');

                $this->actividadService->registrar(
                    usuarioId: (int) session('tecnico_id'),
                    tipoAccion: 'asignar_repuesto',
                    descripcion: "Asignó repuesto a orden #{$orden->nro_orden}",
                    modulo: 'repuestos',
                    referenciaId: $orden->id,
                    referenciaTipo: $tipoOrden === 'empresa' ? 'orden_empresa' : 'orden',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $clienteNombre,
                        'serie' => $orden->equipo?->serie ?? 'sn',
                        'marca' => $orden->equipo?->marca ?? 'sn',
                        'tipo' => $orden->equipo?->tipo ?? 'sn',
                        'repuesto_inventario_id' => (int) $request->input('repuesto_inventario_id')
                    ]
                );
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Repuesto asignado correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function revertirRepuesto(RevertirRepuestoOrdenRequest $request): JsonResponse
    {
        try {
            $tipoOrden = $request->input('tipo_orden', 'personal');
            $dto = new RevertirRepuestoOrdenDTO(
                (int) $request->input('orden_id'),
                $request->filled('repuesto_id') ? (int) $request->input('repuesto_id') : null
            );

            $this->service->revertirRepuesto(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin(),
                $tipoOrden
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Repuesto revertido correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function reasignarTecnico(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $ordenId = (int) $request->input('orden_id');
            $nuevoTecnicoId = (int) $request->input('tecnico_id');
            $tipoOrden = $request->input('tipo_orden', 'personal');

            if ($ordenId <= 0 || $nuevoTecnicoId <= 0) {
                throw new Exception('ID de orden o técnico inválido.');
            }

            if ($tipoOrden === 'empresa') {
                $orden = \App\Models\Operations\OrdenEmpresa::with(['empresa', 'equipo'])->find($ordenId);
            } else {
                $orden = \App\Models\Operations\Orden::with(['cliente', 'equipo'])->find($ordenId);
            }

            // Validar que el nuevo técnico es asignable (pertenece al mismo CAS o sucursal del creador/logueado)
            $tecnicoActualId = (int) session('tecnico_id');
            $esAdmin = $this->resolverEsAdmin();

            if (!$this->usuarioRepo->tecnicoAsignable(
                $nuevoTecnicoId,
                $esAdmin,
                (int) session('sucursal_id'),
                $tecnicoActualId
            )) {
                throw new Exception('No puedes asignar esta orden a ese técnico.');
            }

            // Reasignar la orden
            $this->service->reasignarTecnico($ordenId, $nuevoTecnicoId, $tipoOrden);

            if ($orden) {
                $nuevoTecnico = \App\Models\Identity\Usuario::find($nuevoTecnicoId);
                $nuevoTecnicoNombre = $nuevoTecnico ? $nuevoTecnico->nombre_tecnico : 'desconocido';

                if ($tipoOrden === 'empresa') {
                    $this->actividadService->registrar(
                        usuarioId: $tecnicoActualId,
                        tipoAccion: 'reasignar_tecnico_empresa',
                        descripcion: "Reasignó técnico de orden de empresa #{$orden->nro_orden} a {$nuevoTecnicoNombre}",
                        modulo: 'ordenes',
                        referenciaId: $orden->id,
                        referenciaTipo: 'orden_empresa',
                        metadata: [
                            'nro_orden' => $orden->nro_orden,
                            'cliente' => $orden->empresa?->nombre ?? '',
                            'serie' => $orden->equipo?->serie ?? 'sn',
                            'marca' => $orden->equipo?->marca ?? 'sn',
                            'tipo' => $orden->equipo?->tipo ?? 'sn',
                            'nuevo_tecnico' => $nuevoTecnicoNombre
                        ]
                    );
                } else {
                    $this->actividadService->registrar(
                        usuarioId: $tecnicoActualId,
                        tipoAccion: 'reasignar_tecnico',
                        descripcion: "Reasignó técnico de orden #{$orden->nro_orden} a {$nuevoTecnicoNombre}",
                        modulo: 'ordenes',
                        referenciaId: $orden->id,
                        referenciaTipo: 'orden',
                        metadata: [
                            'nro_orden' => $orden->nro_orden,
                            'cliente' => $orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? '',
                            'serie' => $orden->equipo?->serie ?? 'sn',
                            'marca' => $orden->equipo?->marca ?? 'sn',
                            'tipo' => $orden->equipo?->tipo ?? 'sn',
                            'nuevo_tecnico' => $nuevoTecnicoNombre
                        ]
                    );
                }
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'La orden ha sido reasignada correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function registrarLlamada(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $request->validate([
                'orden_id' => 'required_without:orden_empresa_id|nullable|integer',
                'orden_empresa_id' => 'required_without:orden_id|nullable|integer',
                'observacion' => 'nullable|string|max:1000',
            ]);

            $ordenId = $request->input('orden_id') ? (int) $request->input('orden_id') : null;
            $ordenEmpresaId = $request->input('orden_empresa_id') ? (int) $request->input('orden_empresa_id') : null;
            $observacion = trim((string) $request->input('observacion'));
            $tecnicoId = (int) session('tecnico_id');

            if ($ordenId) {
                $orden = \App\Models\Operations\Orden::find($ordenId);
            } else {
                $orden = \App\Models\Operations\OrdenEmpresa::find($ordenEmpresaId);
            }

            if (!$orden) {
                throw new \Exception('Orden no encontrada.');
            }

            $llamada = \App\Models\Operations\LlamadaOrden::create([
                'orden_id' => $ordenId,
                'orden_empresa_id' => $ordenEmpresaId,
                'usuario_id' => $tecnicoId,
                'fecha_hora' => \Carbon\Carbon::now('America/Guayaquil'),
                'observacion' => $observacion !== '' ? $observacion : null,
            ]);

            // Registrar en la bitácora
            $this->actividadService->registrar(
                usuarioId: $tecnicoId,
                tipoAccion: 'llamada_cliente',
                descripcion: "Registró llamada a cliente para orden #{$orden->nro_orden}" . ($observacion !== '' ? ": {$observacion}" : ""),
                modulo: 'ordenes',
                referenciaId: $llamada->id,
                referenciaTipo: 'llamada_orden',
                metadata: [
                    'nro_orden' => $orden->nro_orden,
                    'observacion' => $observacion
                ]
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Llamada registrada con éxito.',
                'llamada' => [
                    'id' => $llamada->id,
                    'fecha_hora' => $llamada->fecha_hora->format('d/m/Y H:i'),
                    'usuario_nombre' => session('nombre') ?? session('usuario') ?? 'Técnico',
                    'observacion' => $llamada->observacion ?? 'Llamada registrada sin observaciones.',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function enviarEmailCliente(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $request->validate([
                'orden_id' => 'required_without:orden_empresa_id|nullable|integer',
                'orden_empresa_id' => 'required_without:orden_id|nullable|integer',
                'asunto' => 'required|string|max:200',
                'contenido' => 'required|string|max:5000',
            ]);

            $ordenId = $request->input('orden_id') ? (int) $request->input('orden_id') : null;
            $ordenEmpresaId = $request->input('orden_empresa_id') ? (int) $request->input('orden_empresa_id') : null;
            $asunto = trim((string) $request->input('asunto'));
            $contenido = trim((string) $request->input('contenido'));
            $tecnicoId = (int) session('tecnico_id');

            if ($ordenId) {
                $orden = \App\Models\Operations\Orden::with('cliente')->find($ordenId);
                $correo = $orden ? ($orden->cliente->correo ?? '') : '';
            } else {
                $orden = \App\Models\Operations\OrdenEmpresa::with('empresa')->find($ordenEmpresaId);
                $correo = $orden ? ($orden->empresa->correo ?? '') : '';
            }

            if (!$orden) {
                throw new \Exception('Orden no encontrada.');
            }

            if (empty($correo)) {
                throw new \Exception('El cliente no tiene un correo electrónico registrado.');
            }

            // Enviar correo
            \App\Services\Operations\SgnMailService::enviarEmailCliente($orden, $asunto, $contenido);

            // Registrar en bitácora
            $this->actividadService->registrar(
                usuarioId: $tecnicoId,
                tipoAccion: 'enviar_email_cliente',
                descripcion: "Envió correo al cliente para orden #{$orden->nro_orden}: {$asunto}",
                modulo: 'ordenes',
                referenciaId: $orden->id,
                referenciaTipo: $ordenId ? 'orden' : 'orden_empresa',
                metadata: [
                    'nro_orden' => $orden->nro_orden,
                    'asunto' => $asunto,
                    'correo_cliente' => $correo
                ]
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Correo electrónico enviado al cliente correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function registrarTransferencia(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'orden_id' => 'required|integer',
            'plataforma' => 'required|string|max:50',
            'numero' => ['required', 'regex:/^\d+$/', 'max:100'],
        ], [
            'numero.regex' => 'El número de transferencia de inventario solo debe contener dígitos numéricos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        $ordenId = (int) $request->input('orden_id');
        $plataforma = $request->input('plataforma');
        $numero = $request->input('numero');
        $usuarioId = (int) session('tecnico_id');

        try {
            $orden = \App\Models\Operations\Orden::findOrFail($ordenId);

            // Aseguramos que sea una orden personal de garantía
            if (mb_strtolower(trim((string) $orden->motivo_ingreso)) !== 'validacion de garantia') {
                throw new Exception('Esta acción solo está permitida para órdenes personales de tipo Garantía.');
            }

            $orden->transferencia_plataforma = $plataforma;
            $orden->transferencia_numero = $numero;
            $orden->fecha_finalizacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
            $orden->save();

            // Registrar actividad diaria
            $this->actividadService->registrar(
                usuarioId: $usuarioId,
                tipoAccion: 'registrar_transferencia',
                descripcion: "Registró transferencia para la orden #{$orden->nro_orden}: {$plataforma} - {$numero}",
                modulo: 'ordenes',
                referenciaId: $orden->id,
                referenciaTipo: 'orden',
                metadata: [
                    'nro_orden' => $orden->nro_orden,
                    'plataforma' => $plataforma,
                    'numero' => $numero,
                ]
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Número de transferencia registrado y orden cerrada correctamente.',
                'fecha_finalizacion' => $orden->fecha_finalizacion,
                'transferencia_plataforma' => $plataforma,
                'transferencia_numero' => $numero
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function resolverEsAdmin(): bool
    {
        $permisos = (array) session('permisos', []);

        return (bool) session('es_superadmin', false)
            || (($permisos['ordenes_asignadas']['ver'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true)
            || (($permisos['usuarios']['crear'] ?? false) === true)
            || (($permisos['repuestos_admin']['ver'] ?? false) === true);
    }
}
