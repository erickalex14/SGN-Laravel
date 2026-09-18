<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResuelveRolesTicket;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use App\Models\Operations\TicketMensaje;
use App\Services\Operations\TicketDocxService;
use App\Services\Operations\TicketMailService;
use App\Services\Operations\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Throwable;

class TicketApiController extends Controller
{
    use ResuelveRolesTicket;

    /** Vigencia de los enlaces firmados a documentos (impresión y Word MBA). */
    private const MINUTOS_VIGENCIA_DOCUMENTO = 10;

    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $esTecnico = $this->esTecnicoSistemas($usuario);

        $query = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente'])
            ->latest('id');

        if (!$esTecnico) {
            if ($usuario->sucursal_cliente_id) {
                $query->where(function ($q) use ($usuario) {
                    $q->where('solicitante_id', $usuario->id)
                      ->orWhere('sucursal_cliente_id', $usuario->sucursal_cliente_id);
                });
            } else {
                $query->where('solicitante_id', $usuario->id);
            }
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->input('categoria'));
        }

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo_ticket', 'like', "%{$q}%")
                    ->orWhere('titulo', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%")
                    ->orWhere('tienda_nombre', 'like', "%{$q}%");
            });
        }

        $perPage = min(50, max(10, (int) $request->input('per_page', 20)));
        $tickets = $query->paginate($perPage);

        $statsQuery = Ticket::query();
        if (!$esTecnico) {
            if ($usuario->sucursal_cliente_id) {
                $statsQuery->where(function ($q) use ($usuario) {
                    $q->where('solicitante_id', $usuario->id)
                      ->orWhere('sucursal_cliente_id', $usuario->sucursal_cliente_id);
                });
            } else {
                $statsQuery->where('solicitante_id', $usuario->id);
            }
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'abiertos' => (clone $statsQuery)->whereIn('estado', ['abierto', 'en_proceso', 'en_espera'])->count(),
            'en_mba' => (clone $statsQuery)->where('estado', 'en_mba')->count(),
            'resueltos' => (clone $statsQuery)->where('estado', 'resuelto')->count(),
            'cerrados' => (clone $statsQuery)->where('estado', 'cerrado')->count(),
        ];

        return response()->json([
            'ok' => true,
            'tickets' => $tickets->items(),
            'current_page' => $tickets->currentPage(),
            'last_page' => $tickets->lastPage(),
            'total' => $tickets->total(),
            'stats' => $stats,
            'es_tecnico' => $esTecnico,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $esTecnico = $this->esTecnicoSistemas($usuario);

        $ticket = Ticket::with([
            'solicitante',
            'asignadoA',
            'sucursalCliente',
            'adjuntos',
            'mensajes' => function ($q) use ($esTecnico) {
                if (!$esTecnico) {
                    $q->where('es_nota_interna', false);
                }
                $q->with(['usuario', 'adjuntos'])->oldest('id');
            }
        ])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'ticket' => $ticket,
            'mensajes' => $ticket->mensajes,
            'pdf_url' => route('tickets.imprimir', $ticket->id),
            'es_tecnico' => $esTecnico,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $requestData = $request->all();
        if (empty($requestData['sucursal_cliente_id']) && $usuario->sucursal_cliente_id) {
            $requestData['sucursal_cliente_id'] = $usuario->sucursal_cliente_id;
            $requestData['empresa_origen'] = $usuario->empresa_origen ?? 'NOVICOMPU';
        }

        $request->validate([
            'tipo_ticket' => 'required|in:soporte_tecnico,sistemas',
            'categoria' => 'required|string|max:60',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'empresa_origen' => 'required|in:NOVICOMPU,ENV,OTRO',
            'sucursal_cliente_id' => 'nullable|integer',
            'tienda_nombre' => 'nullable|string|max:120',
            'contacto_telefono' => 'nullable|string|max:30',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'archivos.*' => 'nullable|file|max:15360',
        ]);

        try {
            $archivos = $request->file('archivos', []);
            $ticket = $this->ticketService->crearTicket($requestData, $usuario, is_array($archivos) ? $archivos : []);

            return response()->json([
                'ok' => true,
                'mensaje' => "Ticket {$ticket->codigo_ticket} creado con éxito.",
                'ticket' => $ticket,
            ], 201);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al crear ticket: ' . $e->getMessage()], 500);
        }
    }

    public function chat(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'mensaje' => 'nullable|string',
            'es_nota_interna' => 'nullable|boolean',
            'archivos.*' => 'nullable|file|max:15360',
        ]);

        $texto = trim((string) $request->input('mensaje', ''));
        $archivos = $request->file('archivos', []);

        if (empty($texto) && empty($archivos)) {
            return response()->json(['ok' => false, 'error' => 'Debe escribir un mensaje o adjuntar una foto.'], 422);
        }

        $esTecnico = $this->esTecnicoSistemas($usuario);
        $esNota = $esTecnico && $request->boolean('es_nota_interna');

        try {
            $mensaje = $this->ticketService->agregarMensaje(
                $ticket,
                $usuario,
                $texto ?: '(Foto / Evidencia adjunta)',
                $esNota,
                null,
                is_array($archivos) ? $archivos : []
            );

            if (!$esNota && $ticket->estado === 'abierto' && $esTecnico) {
                $this->ticketService->cambiarEstado($ticket, 'en_proceso', $usuario);
            } elseif (!$esNota && in_array($ticket->estado, ['en_espera', 'resuelto']) && !$esTecnico) {
                $this->ticketService->cambiarEstado($ticket, 'en_proceso', $usuario, 'Reabierto por respuesta de tienda');
            }

            $mensaje->load(['usuario', 'adjuntos']);

            return response()->json([
                'ok' => true,
                'mensaje' => [
                    'id' => $mensaje->id,
                    'usuario_id' => $mensaje->usuario_id,
                    'autor_nombre' => $mensaje->usuario ? ($mensaje->usuario->nombre_tecnico ?: $mensaje->usuario->usuario) : 'Usuario',
                    'mensaje' => $mensaje->mensaje,
                    'es_nota_interna' => (bool) $mensaje->es_nota_interna,
                    'cambio_estado' => $mensaje->cambio_estado,
                    'hora' => $mensaje->created_at ? $mensaje->created_at->format('H:i') : '',
                    'fecha' => $mensaje->created_at ? $mensaje->created_at->format('d/m/Y H:i') : '',
                    'adjuntos' => $mensaje->adjuntos->map(fn ($a) => [
                        'id' => $a->id,
                        'nombre_original' => $a->nombre_archivo,
                        'url' => asset('storage/' . $a->ruta_archivo),
                        'es_imagen' => str_starts_with($a->mime_type ?? '', 'image/'),
                    ]),
                ]
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al enviar mensaje: ' . $e->getMessage()], 500);
        }
    }

    public function syncChat(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $esTecnico = $this->esTecnicoSistemas($usuario);
        $lastId = (int) $request->query('last_id', 0);

        $query = TicketMensaje::with(['usuario', 'adjuntos'])
            ->where('ticket_id', $id)
            ->where('id', '>', $lastId);

        if (!$esTecnico) {
            $query->where('es_nota_interna', false);
        }

        $nuevos = $query->oldest('id')->get();

        return response()->json([
            'ok' => true,
            'mensajes' => $nuevos->map(fn ($m) => [
                'id' => $m->id,
                'usuario_id' => $m->usuario_id,
                'autor_nombre' => $m->usuario ? ($m->usuario->nombre_tecnico ?: $m->usuario->usuario) : 'Usuario',
                'mensaje' => $m->mensaje,
                'es_nota_interna' => (bool) $m->es_nota_interna,
                'cambio_estado' => $m->cambio_estado,
                'hora' => $m->created_at ? $m->created_at->format('H:i') : '',
                'fecha' => $m->created_at ? $m->created_at->format('d/m/Y H:i') : '',
                'adjuntos' => $m->adjuntos->map(fn ($a) => [
                    'id' => $a->id,
                    'nombre_original' => $a->nombre_archivo,
                    'url' => asset('storage/' . $a->ruta_archivo),
                    'es_imagen' => str_starts_with($a->mime_type ?? '', 'image/'),
                ]),
            ]),
        ]);
    }

    public function calificar(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:255',
        ]);

        try {
            $this->ticketService->calificarTicket(
                $ticket,
                (int) $request->input('calificacion'),
                $request->input('comentario'),
                $usuario
            );

            return response()->json(['ok' => true, 'mensaje' => '¡Muchas gracias por calificar la atención!']);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al calificar: ' . $e->getMessage()], 500);
        }
    }

    public function reabrir(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'motivo' => 'required|string|min:4|max:1000',
        ]);

        try {
            $this->ticketService->reabrirTicket(
                $ticket,
                trim($request->input('motivo')),
                $usuario
            );

            return response()->json(['ok' => true, 'mensaje' => "Ticket {$ticket->codigo_ticket} reabierto con éxito."]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al reabrir ticket: ' . $e->getMessage()], 500);
        }
    }

    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'estado' => 'required|in:abierto,en_proceso,en_espera,en_mba,resuelto,cerrado,cancelado',
            'motivo' => 'nullable|string|max:500',
            'solucion' => 'nullable|string',
            'numero_ticket_mba' => 'nullable|string|max:60',
            'evidencia' => 'nullable|file|max:15360',
        ]);

        $nuevoEstado = $request->input('estado');
        $archivos = [];
        if ($request->hasFile('evidencia')) {
            $f = $request->file('evidencia');
            $archivos = is_array($f) ? $f : [$f];
        }

        try {
            $this->ticketService->cambiarEstado(
                $ticket,
                $nuevoEstado,
                $usuario,
                $request->input('motivo'),
                $request->input('solucion'),
                $archivos,
                $request->input('numero_ticket_mba')
            );

            return response()->json([
                'ok' => true,
                'mensaje' => "Estado actualizado a '{$nuevoEstado}'.",
                'ticket' => $ticket->fresh()
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al cambiar estado: ' . $e->getMessage()], 500);
        }
    }

    public function asignar(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'tecnico_id' => 'required|integer|exists:usuarios,id',
        ]);

        try {
            $this->ticketService->asignarTicket(
                $ticket,
                (int) $request->input('tecnico_id'),
                $usuario
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Técnico asignado exitosamente.',
                'ticket' => $ticket->fresh(['asignadoA'])
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al asignar: ' . $e->getMessage()], 500);
        }
    }

    public function catalogo(): JsonResponse
    {
        $tiendas = SucursalCliente::where('activa', 1)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'provincia']);

        $tecnicosSistemas = Usuario::whereIn('id', TicketService::TECNICOS_SISTEMAS_IDS)
            ->get(['id', 'usuario', 'nombre_tecnico', 'correo_tec']);

        return response()->json([
            'ok' => true,
            'tiendas' => $tiendas,
            'tecnicos_sistemas' => $tecnicosSistemas,
            // Mismo listado que `categoriasSoporte` en resources/views/tickets/crear.blade.php.
            // Faltaba aquí, así que el móvil no podía crear tickets de soporte técnico.
            'categorias_soporte' => [
                'Impresoras térmicas / Matriciales',
                'Lectores de código de barras',
                'CPU / Computador de Facturación',
                'Pantallas / Monitores',
                'Equipos de Clientes / Garantías',
                'Mantenimiento Físico Hardware',
                'Otro Soporte de Hardware',
            ],
            'categorias_sistemas' => [
                [
                    'grupo' => 'MBA3 (Sistema ERP)',
                    'categorias' => [
                        'Casos MBA3',
                        'Creación de usuario MBA',
                        'Colocación / Creación icono MBA',
                        'Creación vendedor MBA',
                        'Dar de baja usuario MBA',
                        'Mantenimiento código',
                        'Parametrización permisos usuarios'
                    ]
                ],
                [
                    'grupo' => 'MILLENIUM',
                    'categorias' => [
                        'Creación icono Millenium',
                        'Parametrización permisos usuarios Millenium'
                    ]
                ],
                [
                    'grupo' => 'CORREOS ELECTRÓNICOS',
                    'categorias' => [
                        'Creación nuevos correos',
                        'Actualización datos correos'
                    ]
                ],
                [
                    'grupo' => 'OTROS REQUERIMIENTOS',
                    'categorias' => [
                        'Requerimiento general de sistemas',
                        'Otro problema de TI'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Auditoría Ejecutiva y Rendimiento para la App Móvil.
     */
    public function auditoria(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $esTecnico = $this->esTecnicoSistemas($usuario);

        if (!$esTecnico) {
            return response()->json(['ok' => false, 'error' => 'Acceso denegado a auditoría.'], 403);
        }

        $totalTickets = Ticket::count();
        $resueltos = Ticket::whereIn('estado', ['resuelto', 'cerrado'])->count();
        $abiertos = Ticket::whereIn('estado', ['abierto', 'en_proceso', 'en_espera'])->count();
        $enMba = Ticket::where('estado', 'en_mba')->count();
        $tasaResolucion = $totalTickets > 0 ? round(($resueltos / $totalTickets) * 100, 1) : 0;

        // Top 5 Tiendas con más incidencias
        $topTiendas = Ticket::select('tienda_nombre', \DB::raw('count(*) as total'))
            ->whereNotNull('tienda_nombre')
            ->groupBy('tienda_nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Rendimiento por Especialista de Sistemas
        $especialistas = Usuario::whereIn('id', TicketService::TECNICOS_SISTEMAS_IDS)
            ->get()
            ->map(function ($tec) {
                $asignados = Ticket::where('asignado_a_id', $tec->id)->count();
                $resueltos = Ticket::where('asignado_a_id', $tec->id)->whereIn('estado', ['resuelto', 'cerrado'])->count();
                $califMedia = Ticket::where('asignado_a_id', $tec->id)->whereNotNull('calificacion')->avg('calificacion');

                return [
                    'id' => $tec->id,
                    'nombre' => $tec->nombre_tecnico ?: $tec->usuario,
                    'asignados' => $asignados,
                    'resueltos' => $resueltos,
                    'calificacion_promedio' => $califMedia ? round((float)$califMedia, 1) : 5.0,
                ];
            });

        // Tabla de tickets auditables. La vista web la tiene con estos mismos filtros; en la
        // API faltaba, así que el móvil solo podía ver los indicadores agregados.
        $listado = Ticket::with(['solicitante', 'asignadoA'])->latest('id');

        if ($request->filled('estado')) {
            $listado->where('estado', $request->input('estado'));
        }
        if ($request->filled('tipo_ticket')) {
            $listado->where('tipo_ticket', $request->input('tipo_ticket'));
        }
        if ($request->filled('empresa_origen')) {
            $listado->where('empresa_origen', $request->input('empresa_origen'));
        }
        if ($request->filled('asignado_a_id')) {
            $listado->where('asignado_a_id', (int) $request->input('asignado_a_id'));
        }
        if ($request->filled('fecha_desde')) {
            $listado->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $listado->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $listado->where(function ($sub) use ($q) {
                $sub->where('codigo_ticket', 'LIKE', "%{$q}%")
                    ->orWhere('titulo', 'LIKE', "%{$q}%")
                    ->orWhere('tienda_nombre', 'LIKE', "%{$q}%");
            });
        }

        $paginado = $listado->paginate(25, ['*'], 'page', (int) $request->input('page', 1));

        $tickets = collect($paginado->items())->map(fn ($t) => [
            'id' => $t->id,
            'codigo_ticket' => $t->codigo_ticket,
            'titulo' => $t->titulo,
            'estado' => $t->estado,
            'categoria' => $t->categoria,
            'prioridad' => $t->prioridad,
            'tienda_nombre' => $t->tienda_nombre,
            'empresa_origen' => $t->empresa_origen,
            'calificacion' => $t->calificacion,
            'fecha_apertura' => $t->created_at?->format('d/m/Y H:i'),
            'solicitante_nombre' => $t->solicitante?->nombre_tecnico ?: $t->solicitante?->usuario,
            'asignado_nombre' => $t->asignadoA?->nombre_tecnico ?: $t->asignadoA?->usuario,
        ])->values();

        return response()->json([
            'ok' => true,
            'kpis' => [
                'total_tickets' => $totalTickets,
                'resueltos' => $resueltos,
                'abiertos' => $abiertos,
                'en_mba' => $enMba,
                'tasa_resolucion' => $tasaResolucion,
            ],
            'top_tiendas' => $topTiendas,
            'especialistas' => $especialistas,
            'tickets' => $tickets,
            'current_page' => $paginado->currentPage(),
            'last_page' => $paginado->lastPage(),
            'total_listado' => $paginado->total(),
        ]);
    }

    /**
     * Directorio de Solicitantes y Tiendas para la App Móvil.
     */
    public function solicitantes(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        $query = Usuario::with('sucursalCliente')
            ->whereNotNull('sucursal_cliente_id');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('usuario', 'LIKE', "%{$q}%")
                    ->orWhere('nombre_tecnico', 'LIKE', "%{$q}%")
                    ->orWhere('telefono', 'LIKE', "%{$q}%")
                    ->orWhere('anydesk_id', 'LIKE', "%{$q}%")
                    ->orWhereHas('sucursalCliente', function ($sc) use ($q) {
                        $sc->where('nombre', 'LIKE', "%{$q}%");
                    });
            });
        }

        $solicitantes = $query->paginate(30);

        // Se mapea explícitamente en vez de serializar el modelo crudo: la app esperaba
        // `nombre` y `tienda`, que no existen como columnas, así que la lista llegaba con
        // el nombre en null. Estos son los campos que consume la pantalla y el formulario.
        $items = collect($solicitantes->items())->map(fn ($u) => [
            'id' => $u->id,
            'usuario' => $u->usuario,
            'nombre' => $u->nombre_tecnico ?: $u->usuario,
            'correo' => $u->correo_tec,
            'telefono' => $u->telefono,
            'departamento' => $u->departamento,
            'empresa_origen' => $u->empresa_origen,
            'anydesk_id' => $u->anydesk_id,
            'usuario_mba' => $u->usuario_mba,
            'codigo_usuario' => $u->codigo_usuario,
            'activo' => (int) $u->activo,
            'sucursal_cliente_id' => $u->sucursal_cliente_id,
            'tienda' => $u->sucursalCliente ? [
                'id' => $u->sucursalCliente->id,
                'codigo' => $u->sucursalCliente->codigo,
                'nombre' => $u->sucursalCliente->nombre,
                'provincia' => $u->sucursalCliente->provincia ?? null,
            ] : null,
        ])->values();

        return response()->json([
            'ok' => true,
            'solicitantes' => $items,
            'total' => $solicitantes->total()
        ]);
    }

    // ------------------------------------------------------------------
    // Paridad con el módulo web: perfil, solicitantes, auditoría y documentos.
    // ------------------------------------------------------------------

    /** Espeja MisTicketsController::perfil. */
    public function perfil(Request $request): JsonResponse
    {
        $usuario = $request->user()->load('sucursalCliente');

        return response()->json([
            'ok' => true,
            'perfil' => [
                'id' => $usuario->id,
                'usuario' => $usuario->usuario,
                'nombre_tecnico' => $usuario->nombre_tecnico,
                'correo_tec' => $usuario->correo_tec,
                'telefono' => $usuario->telefono,
                'departamento' => $usuario->departamento,
                'empresa_origen' => $usuario->empresa_origen,
                'usuario_mba' => $usuario->usuario_mba,
                'codigo_usuario' => $usuario->codigo_usuario,
                'anydesk_id' => $usuario->anydesk_id,
                'sucursal_cliente' => $usuario->sucursalCliente,
            ],
        ]);
    }

    /** Espeja MisTicketsController::guardarPerfil. Mismas reglas de validación. */
    public function guardarPerfil(Request $request): JsonResponse
    {
        $request->validate([
            'correo_tec' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:30',
            'departamento' => 'nullable|string|max:100',
            'usuario_mba' => 'nullable|string|max:60',
            'codigo_usuario' => 'nullable|string|max:60',
            'anydesk_id' => 'nullable|string|max:50',
        ]);

        try {
            $usuario = $request->user();
            $usuario->update([
                'correo_tec' => $request->input('correo_tec') ? trim($request->input('correo_tec')) : null,
                'telefono' => $request->input('telefono'),
                'departamento' => $request->input('departamento') ? trim($request->input('departamento')) : null,
                'usuario_mba' => $request->input('usuario_mba') ? trim($request->input('usuario_mba')) : null,
                'codigo_usuario' => $request->input('codigo_usuario') ? trim($request->input('codigo_usuario')) : null,
                'anydesk_id' => $request->input('anydesk_id') ? trim($request->input('anydesk_id')) : null,
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Tus datos de soporte técnico se actualizaron correctamente.',
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al guardar datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Espeja TicketSolicitantesController::store.
     *
     * Se añade la comprobación de administrador que la ruta web NO hace: allí solo `index`
     * llama a verificarAccesoAdmin, así que cualquier usuario autenticado puede dar de alta
     * un solicitante con credenciales. Esa laguna no se replica aquí.
     */
    public function solicitanteStore(Request $request): JsonResponse
    {
        if (!$this->esAdminTickets($request->user())) {
            return response()->json(['ok' => false, 'error' => 'No autorizado para administrar solicitantes.'], 403);
        }

        $request->validate([
            'usuario' => 'required|string|max:50|unique:usuarios,usuario',
            'nombre_tecnico' => 'required|string|max:100',
            'clave' => 'required|string|min:4',
            'correo_tec' => 'nullable|email|max:100',
            'empresa_origen' => 'required|in:NOVICOMPU,ENV,OTRO',
            'departamento' => 'nullable|string|max:100',
            'sucursal_cliente_id' => 'required|integer',
            'telefono' => 'nullable|string|max:30',
            'usuario_mba' => 'nullable|string|max:60',
            'codigo_usuario' => 'nullable|string|max:60',
            'anydesk_id' => 'nullable|string|max:50',
        ]);

        try {
            $grupo = GrupoAcceso::firstOrCreate(
                ['nombre' => 'Generador de Tickets (Tiendas)'],
                ['es_superadmin' => 0]
            );

            $clavePlana = trim($request->input('clave'));

            $nuevo = Usuario::create([
                'usuario' => trim($request->input('usuario')),
                'nombre_tecnico' => trim($request->input('nombre_tecnico')),
                'clave_hash' => Hash::make($clavePlana),
                'clave' => '',
                'correo_tec' => $request->input('correo_tec') ? trim($request->input('correo_tec')) : null,
                'telefono' => $request->input('telefono'),
                'grupo_id' => $grupo->id,
                'rol_id' => 1,
                'sucursal_id' => 1,
                'sucursal_cliente_id' => (int) $request->input('sucursal_cliente_id'),
                'empresa_origen' => $request->input('empresa_origen', 'NOVICOMPU'),
                'departamento' => $request->input('departamento') ? trim($request->input('departamento')) : null,
                'usuario_mba' => $request->input('usuario_mba') ? trim($request->input('usuario_mba')) : null,
                'codigo_usuario' => $request->input('codigo_usuario') ? trim($request->input('codigo_usuario')) : null,
                'anydesk_id' => $request->input('anydesk_id') ? trim($request->input('anydesk_id')) : null,
                'activo' => 1,
                'acceso_nc' => 0,
            ]);

            foreach (['ver', 'crear'] as $accion) {
                DB::table('permisosusuario')->updateOrInsert(
                    ['usuario_id' => $nuevo->id, 'modulo' => 'tickets', 'accion' => $accion],
                    ['permitido' => 1]
                );
            }

            if ($nuevo->correo_tec) {
                TicketMailService::enviarCredencialesSolicitante($nuevo, $clavePlana);
            }

            return response()->json([
                'ok' => true,
                'mensaje' => "Usuario solicitante '{$nuevo->usuario}' creado con éxito"
                    . ($nuevo->correo_tec ? " y credenciales enviadas a {$nuevo->correo_tec}." : '.'),
                'solicitante' => $nuevo->load('sucursalCliente'),
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al crear usuario: ' . $e->getMessage()], 500);
        }
    }

    /** Espeja TicketSolicitantesController::update, con la misma comprobación añadida. */
    public function solicitanteUpdate(Request $request, int $id): JsonResponse
    {
        if (!$this->esAdminTickets($request->user())) {
            return response()->json(['ok' => false, 'error' => 'No autorizado para administrar solicitantes.'], 403);
        }

        $solicitante = Usuario::findOrFail($id);

        $request->validate([
            'nombre_tecnico' => 'required|string|max:100',
            'correo_tec' => 'nullable|email|max:100',
            'empresa_origen' => 'required|in:NOVICOMPU,ENV,OTRO',
            'departamento' => 'nullable|string|max:100',
            'sucursal_cliente_id' => 'required|integer',
            'telefono' => 'nullable|string|max:30',
            'usuario_mba' => 'nullable|string|max:60',
            'codigo_usuario' => 'nullable|string|max:60',
            'anydesk_id' => 'nullable|string|max:50',
            'clave' => 'nullable|string|min:4',
            'activo' => 'required|in:0,1',
        ]);

        try {
            $data = [
                'nombre_tecnico' => trim($request->input('nombre_tecnico')),
                'correo_tec' => $request->input('correo_tec') ? trim($request->input('correo_tec')) : null,
                'empresa_origen' => $request->input('empresa_origen'),
                'departamento' => $request->input('departamento') ? trim($request->input('departamento')) : null,
                'sucursal_cliente_id' => (int) $request->input('sucursal_cliente_id'),
                'telefono' => $request->input('telefono'),
                'usuario_mba' => $request->input('usuario_mba') ? trim($request->input('usuario_mba')) : null,
                'codigo_usuario' => $request->input('codigo_usuario') ? trim($request->input('codigo_usuario')) : null,
                'anydesk_id' => $request->input('anydesk_id') ? trim($request->input('anydesk_id')) : null,
                'activo' => (int) $request->input('activo'),
            ];

            $claveCambiada = false;
            $nuevaClavePlana = '';
            if ($request->filled('clave')) {
                $nuevaClavePlana = trim($request->input('clave'));
                $data['clave_hash'] = Hash::make($nuevaClavePlana);
                $data['clave'] = '';
                $claveCambiada = true;
            }

            $solicitante->update($data);

            if ($claveCambiada && $solicitante->correo_tec) {
                TicketMailService::enviarCredencialesSolicitante($solicitante, $nuevaClavePlana);
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Usuario actualizado correctamente.',
                'solicitante' => $solicitante->load('sucursalCliente'),
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
        }
    }

    /** Espeja TicketAuditoriaController::detalleModal. */
    public function auditoriaDetalle(Request $request, int $id): JsonResponse
    {
        if (!$this->esTecnicoSistemas($request->user())) {
            return response()->json(['ok' => false, 'error' => 'Acceso denegado a auditoría.'], 403);
        }

        $ticket = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente', 'adjuntos', 'mensajes.usuario'])
            ->find($id);

        if (!$ticket) {
            return response()->json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
        }

        return response()->json([
            'ok' => true,
            'ticket' => $ticket,
            'solicitante_nombre' => $ticket->solicitante
                ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario)
                : ($ticket->solicitante_id ? 'ID ' . $ticket->solicitante_id : 'Solicitante'),
            'asignado_nombre' => $ticket->asignadoA
                ? ($ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario)
                : ($ticket->asignado_a_id ? 'ID ' . $ticket->asignado_a_id : 'Sin asignar'),
            'tienda_nombre' => $ticket->tienda_nombre
                ?: ($ticket->sucursalCliente
                    ? ($ticket->sucursalCliente->codigo . ' - ' . $ticket->sucursalCliente->nombre)
                    : '—'),
            'solucion_texto' => $ticket->solucion ?: $ticket->solucion_texto,
        ]);
    }

    /**
     * Entrega enlaces firmados y temporales a los documentos del ticket.
     *
     * El móvil autentica con Bearer, no con cookie de sesión, así que no puede abrir las
     * rutas web de impresión y Word. En vez de exponerlas sin protección, aquí se valida el
     * permiso con el token y se devuelve una URL firmada de vida corta que el navegador del
     * teléfono sí puede abrir.
     */
    public function documentos(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        $ticket = Ticket::findOrFail($id);

        if (!$this->puedeVerTicket($usuario, $ticket)) {
            return response()->json(['ok' => false, 'error' => 'No autorizado para ver este ticket.'], 403);
        }

        $expira = now()->addMinutes(self::MINUTOS_VIGENCIA_DOCUMENTO);
        $tieneMba = $ticket->estado === 'en_mba' || !empty($ticket->numero_ticket_mba);

        return response()->json([
            'ok' => true,
            'imprimir_url' => URL::temporarySignedRoute('api.tickets.documento.imprimir', $expira, ['id' => $ticket->id]),
            'word_mba_url' => $tieneMba
                ? URL::temporarySignedRoute('api.tickets.documento.word', $expira, ['id' => $ticket->id])
                : null,
            'expira_en' => $expira->toIso8601String(),
        ]);
    }

    /** Handler de la ruta firmada: misma vista de impresión que usa la web. */
    public function documentoImprimir(int $id)
    {
        $ticket = Ticket::with(['solicitante.sucursalCliente', 'asignadoA', 'sucursalCliente', 'mensajes.usuario', 'adjuntos'])
            ->findOrFail($id);

        return view('tickets.imprimir', compact('ticket'));
    }

    /** Handler de la ruta firmada: mismo .docx que genera la web. */
    public function documentoWordMba(TicketDocxService $docxService, int $id)
    {
        $ticket = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente'])->findOrFail($id);

        $docxContent = $docxService->generarDocxCasoMba($ticket);

        return response($docxContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="Caso_MBA3_' . $ticket->codigo_ticket . '.docx"',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /** Técnico ve cualquier ticket; el solicitante, solo los suyos. */
    private function puedeVerTicket(Usuario $usuario, Ticket $ticket): bool
    {
        return $this->esTecnicoSistemas($usuario)
            || (int) $ticket->solicitante_id === (int) $usuario->id;
    }
}
