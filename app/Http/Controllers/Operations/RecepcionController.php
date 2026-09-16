<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use App\Http\Controllers\Controller;
use App\Models\Directory\Sucursal;
use App\Models\Operations\Informe;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Operations\SolicitudNc;
use App\Services\Identity\ActividadDiariaService;
use App\Services\Operations\GestionOrdenService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecepcionController extends Controller
{
    public function __construct(
        private readonly GestionOrdenService $gestionOrdenService,
        private readonly ActividadDiariaService $actividadService
    ) {}

    /**
     * Dashboard principal de Recepción.
     */
    public function index(Request $request): View
    {
        $sa = session('es_superadmin') === true;
        $sucursalSesion = (int) session('sucursal_id');

        // Filtro de sucursal
        $sucursales = Sucursal::orderBy('ciudad', 'asc')->get();
        $sucursalSeleccionada = $request->filled('sucursal_id') && $sa
            ? (int) $request->input('sucursal_id')
            : $sucursalSesion;

        // Base scope para órdenes accesibles por Recepción:
        // Personales O (Empresas de subtipo Stock o Autoconsumo)
        $scopeRecepcion = function ($q) {
            $q->where(function ($sub) {
                $sub->where('vo.tipo_orden', 'personal')
                    ->orWhere(function ($emp) {
                        $emp->where('vo.tipo_orden', 'empresa')
                            ->whereIn('vo.motivo_ingreso', ['Empresa · Stock', 'Empresa · Autoconsumo']);
                    });
            });
        };

        // Base query para métricas unificadas de Recepción
        $metricsQuery = DB::table('vista_ordenes as vo')->where($scopeRecepcion);
        if ($sucursalSeleccionada > 0) {
            $metricsQuery->where('vo.sucursal_id', $sucursalSeleccionada);
        }

        $hoy = Carbon::now('America/Guayaquil')->toDateString();

        $cerradasCount = (clone $metricsQuery)
            ->whereIn('vo.estado_orden', ['Cerrado', 'Entregada'])
            ->whereDate('vo.fecha_entrega', $hoy)
            ->count();

        $kpis = [
            'recibido_recepcion' => (clone $metricsQuery)->whereIn('vo.estado_orden', ['Recibido en Recepcion', 'INGRESO'])->count(),
            'entregado_tecnico' => (clone $metricsQuery)->whereIn('vo.estado_orden', ['Entregado al Tecnico', 'Recibida'])->count(),
            'pendiente' => (clone $metricsQuery)->where('vo.estado_orden', 'Pendiente')->count(),
            'en_reparacion' => (clone $metricsQuery)->whereIn('vo.estado_orden', ['En reparacion', 'En proceso', 'REVISIÓN'])->count(),
            'reparada' => (clone $metricsQuery)->whereIn('vo.estado_orden', ['Reparada', 'Finalizada', 'REPARADO'])->count(),
            'para_entrega' => (clone $metricsQuery)->whereIn('vo.estado_orden', ['Entregado en Recepcion para Entrega', 'Lista para entrega'])->count(),
            'notas_credito' => (clone $metricsQuery)->where('vo.estado_orden', 'Nota de Credito')->count(),
            'cerradas_hoy' => $cerradasCount,
            'cerradas_total' => (clone $metricsQuery)->whereIn('vo.estado_orden', ['Cerrado', 'Entregada'])->count(),
        ];
        $kpis['en_taller'] = $kpis['entregado_tecnico'] + $kpis['pendiente'] + $kpis['en_reparacion'];
        $kpis['total_activas'] = $kpis['recibido_recepcion'] + $kpis['en_taller'] + $kpis['reparada'] + $kpis['para_entrega'] + $kpis['notas_credito'];

        // Query principal de listado sobre vista_ordenes
        $query = DB::table('vista_ordenes as vo')
            ->leftJoin('usuarios as u_ing', 'vo.ingresado_por', '=', 'u_ing.id')
            ->where($scopeRecepcion)
            ->select([
                'vo.*',
                'u_ing.nombre_tecnico as ingresado_por_nombre',
                'u_ing.usuario as ingresado_por_usuario',
            ])
            ->orderByRaw("
                CASE 
                    WHEN vo.estado_orden IN ('Reparada', 'Finalizada') THEN 1
                    WHEN vo.estado_orden IN ('Entregado en Recepcion para Entrega', 'Lista para entrega') THEN 2
                    WHEN vo.estado_orden = 'Nota de Credito' THEN 3
                    WHEN vo.estado_orden IN ('En reparacion', 'En proceso') THEN 4
                    WHEN vo.estado_orden = 'Pendiente' THEN 5
                    WHEN vo.estado_orden IN ('Entregado al Tecnico', 'Recibida') THEN 6
                    WHEN vo.estado_orden IN ('Recibido en Recepcion', 'INGRESO') THEN 7
                    ELSE 8
                END ASC
            ")
            ->orderBy('vo.orden_id', 'desc');

        if ($sucursalSeleccionada > 0) {
            $query->where('vo.sucursal_id', $sucursalSeleccionada);
        }

        // Filtro por motivo de ingreso (Cliente Final, Garantía, Stock, Autoconsumo)
        if ($request->filled('motivo') && $request->input('motivo') !== 'todos') {
            $motivoFiltro = $request->input('motivo');
            if ($motivoFiltro === 'cliente_final') {
                $query->where('vo.tipo_orden', 'personal')
                    ->where('vo.motivo_ingreso', 'Servicio Cliente Externo');
            } elseif ($motivoFiltro === 'garantia') {
                $query->where('vo.tipo_orden', 'personal')
                    ->where('vo.motivo_ingreso', 'Validacion de Garantia');
            } elseif ($motivoFiltro === 'stock') {
                $query->where('vo.tipo_orden', 'empresa')
                    ->where('vo.motivo_ingreso', 'Empresa · Stock');
            } elseif ($motivoFiltro === 'autoconsumo') {
                $query->where('vo.tipo_orden', 'empresa')
                    ->where('vo.motivo_ingreso', 'Empresa · Autoconsumo');
            }
        }

        // Filtro de fecha: fecha específica o rango de fechas
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereDate('vo.fecha_de_ingreso', '>=', $request->input('fecha_desde'))
                ->whereDate('vo.fecha_de_ingreso', '<=', $request->input('fecha_hasta'));
        } elseif ($request->filled('fecha_desde')) {
            $query->whereDate('vo.fecha_de_ingreso', '=', $request->input('fecha_desde'));
        } elseif ($request->filled('fecha_hasta')) {
            $query->whereDate('vo.fecha_de_ingreso', '<=', $request->input('fecha_hasta'));
        }

        // Filtro por estado
        if ($request->filled('estado') && $request->input('estado') !== 'todos') {
            $estadoFiltro = $request->input('estado');
            if ($estadoFiltro === 'en_taller') {
                $query->whereIn('vo.estado_orden', ['Entregado al Tecnico', 'Recibida', 'Pendiente', 'En reparacion', 'En proceso']);
            } elseif ($estadoFiltro === 'Reparada') {
                $query->whereIn('vo.estado_orden', ['Reparada', 'Finalizada']);
            } elseif ($estadoFiltro === 'Entregado en Recepcion para Entrega') {
                $query->whereIn('vo.estado_orden', ['Entregado en Recepcion para Entrega', 'Lista para entrega']);
            } elseif ($estadoFiltro === 'Recibido en Recepcion') {
                $query->whereIn('vo.estado_orden', ['Recibido en Recepcion', 'INGRESO']);
            } elseif ($estadoFiltro === 'Cerrado') {
                $query->whereIn('vo.estado_orden', ['Cerrado', 'Entregada']);
            } else {
                $query->where('vo.estado_orden', $estadoFiltro);
            }
        } else {
            // Por defecto mostrar ordenes activas del flujo operativo (excluyendo cerradas históricas)
            $query->whereNotIn('vo.estado_orden', ['Cerrado', 'Entregada', 'Devuelto sin reparar']);
        }

        // Búsqueda por número de orden, cliente o serie
        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('vo.nro_orden', 'like', "%{$buscar}%")
                    ->orWhere('vo.cliente', 'like', "%{$buscar}%")
                    ->orWhere('vo.identificacion', 'like', "%{$buscar}%")
                    ->orWhere('vo.numero_contacto', 'like', "%{$buscar}%")
                    ->orWhere('vo.serie', 'like', "%{$buscar}%")
                    ->orWhere('vo.modelo', 'like', "%{$buscar}%");
            });
        }

        $ordenes = $query->paginate(25)->withQueryString();

        // Enriquecer cada orden con informes, notas de crédito y formato para Blade
        $now = Carbon::now('America/Guayaquil');
        $pageIds = $ordenes->getCollection()->pluck('orden_id')->all();

        // Cargar últimos informes para las órdenes de la página actual
        $informes = !empty($pageIds)
            ? Informe::whereIn('orden_id', $pageIds)
                ->orWhereIn('orden_id', array_map(fn($id) => -1 * (int)$id, $pageIds))
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy(fn($inf) => abs((int)$inf->orden_id))
            : collect();

        // Cargar solicitudes de NC para las órdenes personales de la página actual
        $solicitudesNc = !empty($pageIds)
            ? SolicitudNc::whereIn('orden_id', $pageIds)
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('orden_id')
            : collect();

        $ordenes->getCollection()->transform(function ($ord) use ($now, $informes, $solicitudesNc) {
            $ord->id = (int) $ord->orden_id;
            $ord->es_empresa = ($ord->tipo_orden === 'empresa');

            // Determinar si esta orden específica es nuevo flujo
            $esNuevoFlujo = in_array($ord->estado_orden, ['Recibido en Recepcion', 'Entregado al Tecnico', 'Entregado en Recepcion para Entrega'], true)
                || !empty($ord->fecha_recibida_tecnico)
                || !empty($ord->fecha_lista_entrega);

            $ord->es_nuevo_flujo = $esNuevoFlujo;

            $timerSegundos = 0;
            $timerActivo = false;

            if ($ord->fecha_recibida_tecnico) {
                $inicio = Carbon::parse($ord->fecha_recibida_tecnico);
                // El timer de reparación termina cuando Recepción confirma recepción o pasa a cerrado
                $fechaCorte = $ord->fecha_lista_entrega ?: $ord->fecha_finalizacion;
                if ($fechaCorte && in_array($ord->estado_orden, ['Entregado en Recepcion para Entrega', 'Lista para entrega', 'Cerrado', 'Entregada', 'Nota de Credito'], true)) {
                    $fin = Carbon::parse($fechaCorte);
                    $timerSegundos = max(0, $inicio->diffInSeconds($fin));
                    $timerActivo = false;
                } else {
                    $timerSegundos = max(0, $inicio->diffInSeconds($now));
                    $timerActivo = in_array($ord->estado_orden, ['Entregado al Tecnico', 'Recibida', 'Pendiente', 'En reparacion', 'En proceso', 'Reparada', 'Finalizada'], true);
                }
            }

            $ord->timer_segundos = $timerSegundos;
            $ord->timer_activo = $timerActivo;
            $ord->timer_formateado = $this->formatearSegundos($timerSegundos);

            // Obtener conclusión técnica del último informe si existe
            $ultimosInfs = $informes->get((int)$ord->orden_id);
            $ultimoInforme = $ultimosInfs ? $ultimosInfs->first() : null;
            $ord->conclusion_tecnica = $ultimoInforme ? ($ultimoInforme->conclusion ?: $ultimoInforme->proceso) : null;
            $ord->informe_id = $ultimoInforme?->id;

            // Información de Nota de Crédito
            $ultimasNcs = $solicitudesNc->get((int)$ord->orden_id);
            $ultimaNc = ($ord->tipo_orden === 'personal' && $ultimasNcs) ? $ultimasNcs->first() : null;
            $ord->nc_solicitud = $ultimaNc;
            $ord->nc_estado = $ultimaNc?->estado;
            $ord->nc_asunto = $ultimaNc?->asunto;
            $ord->nc_detalles = $ultimaNc?->detalles;

            // Objetos estructurados para compatibilidad fluida con la vista Blade
            $ord->cliente_nombre = $ord->cliente;
            $ord->cliente = (object) [
                'nombres' => $ord->cliente,
                'apellidos' => '',
                'identificacion' => $ord->identificacion,
                'numero_contacto' => $ord->numero_contacto,
                'correo' => $ord->correo,
            ];
            $ord->equipo = (object) [
                'tipo' => $ord->tipo,
                'marca' => $ord->marca,
                'modelo' => $ord->modelo,
                'serie' => $ord->serie,
                'falla' => $ord->falla,
                'observacion' => $ord->observacion,
            ];
            $ord->tecnico = (object) [
                'id' => $ord->tecnico_id,
                'nombre_tecnico' => $ord->tecnico,
            ];
            $ord->usuarioIngreso = (object) [
                'id' => $ord->ingresado_por,
                'nombre_tecnico' => $ord->ingresado_por_nombre ?: ($ord->ingresado_por_usuario ?: 'Recepción'),
                'usuario' => $ord->ingresado_por_usuario ?: 'recepcion',
            ];

            return $ord;
        });

        return view('operations.recepcion.index', compact(
            'ordenes',
            'kpis',
            'sucursales',
            'sucursalSeleccionada',
            'sa'
        ));
    }

    /**
     * API POST: Recepción entrega la orden al cliente con memo y foto de evidencia.
     */
    public function entregarOrden(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'orden_id' => 'required|integer',
                'tipo_orden' => 'nullable|string|in:personal,empresa',
                'memo_entrega' => 'required|string|min:3|max:1000',
                'foto_evidencia' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:15360',
            ], [
                'memo_entrega.required' => 'El memo de entrega o constancia es obligatorio.',
                'foto_evidencia.mimes' => 'El archivo adjunto debe ser una imagen (JPG, PNG, WEBP) o un documento PDF.',
                'foto_evidencia.max' => 'El archivo de evidencia no puede superar los 15MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $primerError = collect($ve->errors())->flatten()->first() ?: 'Datos inválidos en el formulario.';
            return response()->json(['ok' => false, 'error' => $primerError], 422);
        }

        $ordenId = (int) $request->input('orden_id');
        $tipoOrden = $request->input('tipo_orden', 'personal');

        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $sessionRol = mb_strtolower(trim((string) session('rol_nombre', '')));
        $esAdmin = session('es_superadmin') === true || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);
        $esRecepcion = session('es_recepcion') === true || in_array($sessionGrupo, ['recepcion', 'recepción'], true) || in_array($sessionRol, ['recepcion', 'recepcionista'], true);

        if (!$esAdmin && !$esRecepcion) {
            return response()->json(['ok' => false, 'error' => 'No tienes permiso de recepción para realizar esta acción.'], 403);
        }

        $usuarioId = (int) session('tecnico_id');
        $usuarioNombre = (string) session('nombre');

        // Procesar foto o documento PDF de evidencia
        $fotoEvidenciaPath = null;
        if ($request->hasFile('foto_evidencia')) {
            $file = $request->file('foto_evidencia');
            if ($file && $file->isValid()) {
                $prefix = $tipoOrden === 'empresa' ? 'emp_' : '';
                $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = 'evidencia_rec_' . $prefix . $ordenId . '_' . time() . '_' . uniqid() . '.' . $extension;
                $path = $file->storeAs('evidencias_entrega', $filename, 'public');
                $fotoEvidenciaPath = '/storage/' . $path;
            }
        }

        $memo = trim((string) $request->input('memo_entrega'));

        // Caso: Orden Corporativa de Empresa (Stock o Autoconsumo)
        if ($tipoOrden === 'empresa') {
            $orden = OrdenEmpresa::with(['empresa', 'equipo'])->find($ordenId);
            if (!$orden) {
                return response()->json(['ok' => false, 'error' => 'Orden de empresa no encontrada.'], 404);
            }
            if (!in_array($orden->subtipo, ['Stock', 'Autoconsumo'], true)) {
                return response()->json(['ok' => false, 'error' => 'Recepción solo gestiona órdenes corporativas de Stock y Autoconsumo.'], 403);
            }

            try {
                $this->gestionOrdenService->actualizarEstadoEmpresa(
                    ordenId: $ordenId,
                    estado: 'Entregada',
                    usuarioId: $usuarioId,
                    esAdmin: $esAdmin,
                    memoEntrega: $memo,
                    fotoEvidenciaEntrega: $fotoEvidenciaPath
                );

                $this->actividadService->registrar(
                    usuarioId: $usuarioId,
                    tipoAccion: 'entrega_recepcion',
                    descripcion: "Entregó orden de empresa ({$orden->subtipo}) #{$orden->nro_orden} al cliente {$orden->empresa?->nombre} (Entregada)",
                    modulo: 'recepcion',
                    referenciaId: $orden->id,
                    referenciaTipo: 'orden_empresa',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $orden->empresa?->nombre ?? '',
                        'memo_entrega' => $memo,
                        'foto' => $fotoEvidenciaPath,
                        'subtipo' => $orden->subtipo,
                    ]
                );

                return response()->json([
                    'ok' => true,
                    'mensaje' => "La orden de empresa #{$orden->nro_orden} ({$orden->subtipo}) ha sido entregada exitosamente.",
                    'foto_evidencia_entrega' => $fotoEvidenciaPath
                ]);
            } catch (Exception $e) {
                return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
            }
        }

        // Caso: Orden Personal
        $orden = Orden::with(['cliente', 'equipo', 'solicitudesNc'])->find($ordenId);
        if (!$orden) {
            return response()->json(['ok' => false, 'error' => 'Orden no encontrada.'], 404);
        }

        $dto = new CambiarEstadoOrdenDTO(
            $ordenId,
            'Cerrado',
            null,
            null,
            $memo,
            $fotoEvidenciaPath
        );

        try {
            $this->gestionOrdenService->actualizarEstado(
                $dto,
                $usuarioId,
                $usuarioNombre,
                $esAdmin
            );

            $esNc = $orden->estado_orden === 'Nota de Credito';

            // Registrar actividad diaria de entrega / cierre
            $tipoAccion = $esNc ? 'cierre_nc_recepcion' : 'entrega_recepcion';
            $descripcion = $esNc
                ? "Cerró orden #{$orden->nro_orden} por Nota de Crédito del cliente {$orden->cliente?->nombres} {$orden->cliente?->apellidos}"
                : "Entregó orden de trabajo #{$orden->nro_orden} al cliente {$orden->cliente?->nombres} {$orden->cliente?->apellidos} (Cerrado)";

            $this->actividadService->registrar(
                usuarioId: $usuarioId,
                tipoAccion: $tipoAccion,
                descripcion: $descripcion,
                modulo: 'recepcion',
                referenciaId: $orden->id,
                referenciaTipo: 'orden',
                metadata: [
                    'nro_orden' => $orden->nro_orden,
                    'cliente' => ($orden->cliente?->nombres ?? '') . ' ' . ($orden->cliente?->apellidos ?? ''),
                    'memo_entrega' => $memo,
                    'foto' => $fotoEvidenciaPath,
                    'es_nc' => $esNc,
                ]
            );

            $msg = $esNc
                ? "La orden #{$orden->nro_orden} ha sido cerrada por Nota de Crédito exitosamente."
                : "La orden #{$orden->nro_orden} ha sido entregada y cerrada exitosamente.";

            return response()->json([
                'ok' => true,
                'mensaje' => $msg,
                'foto_evidencia_entrega' => $fotoEvidenciaPath
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * API POST: Recepción confirma que recibió la orden reparada del técnico (pasa a 'Entregado en Recepcion para Entrega').
     * Esto detiene el tiempo de reparación del técnico.
     */
    public function recibirEnRecepcion(Request $request): JsonResponse
    {
        $request->validate([
            'orden_id' => 'required|integer',
            'tipo_orden' => 'nullable|string|in:personal,empresa',
        ]);

        $ordenId = (int) $request->input('orden_id');
        $tipoOrden = $request->input('tipo_orden', 'personal');

        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $sessionRol = mb_strtolower(trim((string) session('rol_nombre', '')));
        $esAdmin = session('es_superadmin') === true || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);
        $esRecepcion = session('es_recepcion') === true || in_array($sessionGrupo, ['recepcion', 'recepción'], true) || in_array($sessionRol, ['recepcion', 'recepcionista'], true);

        if (!$esAdmin && !$esRecepcion) {
            return response()->json(['ok' => false, 'error' => 'No tienes permiso de recepción para realizar esta acción.'], 403);
        }

        $usuarioId = (int) session('tecnico_id');
        $usuarioNombre = (string) session('nombre');

        // Caso: Orden Corporativa de Empresa
        if ($tipoOrden === 'empresa') {
            $orden = OrdenEmpresa::with(['empresa', 'equipo'])->find($ordenId);
            if (!$orden) {
                return response()->json(['ok' => false, 'error' => 'Orden de empresa no encontrada.'], 404);
            }
            if (!in_array($orden->subtipo, ['Stock', 'Autoconsumo'], true)) {
                return response()->json(['ok' => false, 'error' => 'Recepción solo gestiona órdenes corporativas de Stock y Autoconsumo.'], 403);
            }
            if (!in_array($orden->estado, ['Reparada', 'Finalizada', 'Entregado en Recepcion para Entrega', 'Lista para entrega'], true)) {
                return response()->json([
                    'ok' => false,
                    'error' => "Para recibir en recepción, la orden debe haber sido reparada o finalizada por el técnico (actualmente: {$orden->estado})."
                ], 422);
            }

            try {
                $this->gestionOrdenService->actualizarEstadoEmpresa(
                    ordenId: $ordenId,
                    estado: 'Entregado en Recepcion para Entrega',
                    usuarioId: $usuarioId,
                    esAdmin: $esAdmin
                );

                $this->actividadService->registrar(
                    usuarioId: $usuarioId,
                    tipoAccion: 'recepcion_recibir_tecnico',
                    descripcion: "Recibió orden de empresa ({$orden->subtipo}) #{$orden->nro_orden} del taller técnico para entrega",
                    modulo: 'recepcion',
                    referenciaId: $orden->id,
                    referenciaTipo: 'orden_empresa',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $orden->empresa?->nombre ?? '',
                        'subtipo' => $orden->subtipo,
                    ]
                );

                return response()->json([
                    'ok' => true,
                    'mensaje' => "Orden de empresa #{$orden->nro_orden} ({$orden->subtipo}) recibida con éxito. Ahora está lista para entrega.",
                ]);
            } catch (Exception $e) {
                return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
            }
        }

        // Caso: Orden Personal
        $orden = Orden::with(['cliente', 'equipo'])->find($ordenId);
        if (!$orden) {
            return response()->json(['ok' => false, 'error' => 'Orden no encontrada.'], 404);
        }

        if (!in_array($orden->estado_orden, ['Reparada', 'Finalizada', 'Entregado en Recepcion para Entrega', 'Lista para entrega'], true)) {
            return response()->json([
                'ok' => false,
                'error' => "Para recibir en recepción, la orden debe haber sido reparada o entregada por el técnico (actualmente: {$orden->estado_orden})."
            ], 422);
        }

        $dto = new CambiarEstadoOrdenDTO(
            $ordenId,
            'Entregado en Recepcion para Entrega'
        );

        try {
            $this->gestionOrdenService->actualizarEstado(
                $dto,
                $usuarioId,
                $usuarioNombre,
                $esAdmin
            );

            // Registrar actividad diaria
            $this->actividadService->registrar(
                usuarioId: $usuarioId,
                tipoAccion: 'recepcion_recibir_tecnico',
                descripcion: "Recibió orden #{$orden->nro_orden} del taller técnico para entrega al cliente",
                modulo: 'recepcion',
                referenciaId: $orden->id,
                referenciaTipo: 'orden',
                metadata: [
                    'nro_orden' => $orden->nro_orden,
                    'cliente' => ($orden->cliente?->nombres ?? '') . ' ' . ($orden->cliente?->apellidos ?? ''),
                ]
            );

            return response()->json([
                'ok' => true,
                'mensaje' => "Orden #{$orden->nro_orden} recibida con éxito. Ahora está lista para entrega al cliente.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * API GET: Retorna detalle completo de la orden para el modal de Recepción.
     */
    public function obtenerDetalle(Request $request, int $id): JsonResponse
    {
        $tipoOrden = $request->input('tipo_orden');
        $esEmpresa = ($tipoOrden === 'empresa') || (!$tipoOrden && !Orden::where('id', $id)->exists() && OrdenEmpresa::where('id', $id)->exists());

        // Si es orden corporativa de empresa (Stock / Autoconsumo)
        if ($esEmpresa) {
            $orden = OrdenEmpresa::with(['empresa', 'equipo', 'tecnico', 'ingresadoPor', 'adjuntos'])->find($id);

            if (!$orden) {
                return response()->json(['ok' => false, 'error' => 'Orden de empresa no encontrada.'], 404);
            }

            $ultimoInforme = Informe::where('orden_id', -1 * (int)$orden->id)
                ->orWhere('orden_id', (int)$orden->id)
                ->orderBy('id', 'desc')
                ->first();

            $fotosIngreso = [];
            if ($orden->adjuntos) {
                foreach ($orden->adjuntos as $adj) {
                    $fotosIngreso[] = [
                        'tipo' => $adj->tipo_adjunto,
                        'path' => $adj->archivo_path,
                        'nombre' => $adj->nombre_original,
                    ];
                }
            }

            $timerSegundos = 0;
            $now = Carbon::now('America/Guayaquil');
            if ($orden->fecha_recibida_tecnico) {
                $inicio = Carbon::parse($orden->fecha_recibida_tecnico);
                $fechaCorte = $orden->fecha_lista_entrega ?: $orden->fecha_finalizacion;
                if ($fechaCorte && in_array($orden->estado, ['Lista para entrega', 'Entregada', 'Entregado en Recepcion para Entrega', 'Cerrado'], true)) {
                    $fin = Carbon::parse($fechaCorte);
                    $timerSegundos = max(0, $inicio->diffInSeconds($fin));
                } else {
                    $timerSegundos = max(0, $inicio->diffInSeconds($now));
                }
            }

            return response()->json([
                'ok' => true,
                'orden' => [
                    'id' => $orden->id,
                    'tipo_orden' => 'empresa',
                    'nro_orden' => $orden->nro_orden,
                    'estado' => $orden->estado,
                    'motivo_ingreso' => 'Empresa · ' . $orden->subtipo,
                    'fecha_ingreso' => $orden->fecha_ingreso ? Carbon::parse($orden->fecha_ingreso)->format('d/m/Y H:i') : '-',
                    'fecha_recibida_tecnico' => $orden->fecha_recibida_tecnico ? Carbon::parse($orden->fecha_recibida_tecnico)->format('d/m/Y H:i') : '-',
                    'fecha_finalizacion' => $orden->fecha_finalizacion ? Carbon::parse($orden->fecha_finalizacion)->format('d/m/Y H:i') : '-',
                    'fecha_lista_entrega' => $orden->fecha_lista_entrega ? Carbon::parse($orden->fecha_lista_entrega)->format('d/m/Y H:i') : '-',
                    'fecha_entrega' => $orden->fecha_entrega ? Carbon::parse($orden->fecha_entrega)->format('d/m/Y H:i') : '-',
                    'cliente' => $orden->empresa?->nombre ?? 'Empresa Corporativa',
                    'identificacion' => $orden->empresa?->ruc ?? '-',
                    'telefono' => $orden->empresa?->telefono ?? '-',
                    'correo' => $orden->empresa?->correo ?? '-',
                    'equipo' => trim(($orden->equipo?->tipo ?? '') . ' ' . ($orden->equipo?->marca ?? '') . ' ' . ($orden->equipo?->modelo ?? '')),
                    'serie' => $orden->equipo?->serie ?? '-',
                    'falla' => $orden->equipo?->falla ?? ($orden->descripcion ?? '-'),
                    'observacion' => $orden->equipo?->observacion ?? '-',
                    'tecnico' => $orden->tecnico?->nombre_tecnico ?? '-',
                    'ingresado_por' => $orden->ingresadoPor?->nombre_tecnico ?? ($orden->ingresadoPor?->usuario ?? '-'),
                    'timer_segundos' => $timerSegundos,
                    'timer_formateado' => $this->formatearSegundos($timerSegundos),
                    'conclusion_tecnica' => $ultimoInforme?->conclusion ?: $ultimoInforme?->proceso,
                    'informe_id' => $ultimoInforme?->id,
                    'memo_entrega' => $orden->memo_entrega,
                    'foto_evidencia_entrega' => $orden->foto_evidencia_entrega,
                    'fotos_ingreso' => $fotosIngreso,
                    'factura_adjunta' => null,
                    'nc_estado' => null,
                    'nc_asunto' => null,
                    'nc_detalles' => null,
                    'nc_motivo_rechazo' => null,
                    'nc_nro_solicitud' => null,
                ]
            ]);
        }

        // Caso orden personal
        $orden = Orden::with(['cliente', 'equipo', 'tecnico', 'usuarioIngreso', 'informes', 'solicitudesNc', 'adjuntos'])->find($id);

        if (!$orden) {
            return response()->json(['ok' => false, 'error' => 'Orden no encontrada.'], 404);
        }

        $ultimoInforme = $orden->informes->sortByDesc('id')->first();
        $ultimaNc = $orden->solicitudesNc->sortByDesc('id')->first();

        // Extraer fotos del equipo y factura
        $fotosIngreso = [];
        $facturaAdjunto = null;
        if ($orden->adjuntos) {
            foreach ($orden->adjuntos as $adj) {
                if ($adj->tipo_adjunto === 'factura') {
                    $facturaAdjunto = [
                        'path' => $adj->archivo_path,
                        'nombre' => $adj->nombre_original ?: 'Factura Adjunta',
                        'mime' => $adj->mime_type,
                    ];
                } else {
                    $fotosIngreso[] = [
                        'tipo' => $adj->tipo_adjunto,
                        'path' => $adj->archivo_path,
                        'nombre' => $adj->nombre_original,
                    ];
                }
            }
        }

        $timerSegundos = 0;
        $now = Carbon::now('America/Guayaquil');
        if ($orden->fecha_recibida_tecnico) {
            $inicio = Carbon::parse($orden->fecha_recibida_tecnico);
            $fechaCorte = $orden->fecha_lista_entrega ?: $orden->fecha_finalizacion;
            if ($fechaCorte && in_array($orden->estado_orden, ['Lista para entrega', 'Entregada', 'Nota de Credito'], true)) {
                $fin = Carbon::parse($fechaCorte);
                $timerSegundos = max(0, $inicio->diffInSeconds($fin));
            } else {
                $timerSegundos = max(0, $inicio->diffInSeconds($now));
            }
        }

        return response()->json([
            'ok' => true,
            'orden' => [
                'id' => $orden->id,
                'tipo_orden' => 'personal',
                'nro_orden' => $orden->nro_orden,
                'estado' => $orden->estado_orden,
                'motivo_ingreso' => $orden->motivo_ingreso,
                'fecha_ingreso' => $orden->fecha_de_ingreso ? Carbon::parse($orden->fecha_de_ingreso)->format('d/m/Y H:i') : '-',
                'fecha_recibida_tecnico' => $orden->fecha_recibida_tecnico ? Carbon::parse($orden->fecha_recibida_tecnico)->format('d/m/Y H:i') : '-',
                'fecha_finalizacion' => $orden->fecha_finalizacion ? Carbon::parse($orden->fecha_finalizacion)->format('d/m/Y H:i') : '-',
                'fecha_lista_entrega' => $orden->fecha_lista_entrega ? Carbon::parse($orden->fecha_lista_entrega)->format('d/m/Y H:i') : '-',
                'fecha_entrega' => $orden->fecha_entrega ? Carbon::parse($orden->fecha_entrega)->format('d/m/Y H:i') : '-',
                'cliente' => trim(($orden->cliente?->nombres ?? '') . ' ' . ($orden->cliente?->apellidos ?? '')),
                'identificacion' => $orden->cliente?->identificacion ?? '-',
                'telefono' => $orden->cliente?->numero_contacto ?? '-',
                'correo' => $orden->cliente?->correo ?? '-',
                'equipo' => trim(($orden->equipo?->tipo ?? '') . ' ' . ($orden->equipo?->marca ?? '') . ' ' . ($orden->equipo?->modelo ?? '')),
                'serie' => $orden->equipo?->serie ?? '-',
                'falla' => $orden->equipo?->falla ?? '-',
                'observacion' => $orden->equipo?->observacion ?? '-',
                'tecnico' => $orden->tecnico?->nombre_tecnico ?? '-',
                'ingresado_por' => $orden->usuarioIngreso?->nombre_tecnico ?? ($orden->usuarioIngreso?->usuario ?? '-'),
                'timer_segundos' => $timerSegundos,
                'timer_formateado' => $this->formatearSegundos($timerSegundos),
                'conclusion_tecnica' => $ultimoInforme?->conclusion ?: $ultimoInforme?->proceso,
                'informe_id' => $ultimoInforme?->id,
                'memo_entrega' => $orden->memo_entrega,
                'foto_evidencia_entrega' => $orden->foto_evidencia_entrega,
                'fotos_ingreso' => $fotosIngreso,
                'factura_adjunta' => $facturaAdjunto,
                'nc_estado' => $ultimaNc?->estado,
                'nc_asunto' => $ultimaNc?->asunto,
                'nc_detalles' => $ultimaNc?->detalles,
                'nc_motivo_rechazo' => $ultimaNc?->motivo_rechazo,
                'nc_nro_solicitud' => $ultimaNc?->nro_solicitud,
            ]
        ]);
    }

    private function formatearSegundos(int $segundos): string
    {
        $dias = floor($segundos / 86400);
        $horas = floor(($segundos % 86400) / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;

        if ($dias > 0) {
            return "{$dias}d {$horas}h {$minutos}m";
        }
        if ($horas > 0) {
            return "{$horas}h {$minutos}m {$segs}s";
        }
        return "{$minutos}m {$segs}s";
    }
}
