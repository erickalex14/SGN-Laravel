<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use App\Services\Operations\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TicketGestionController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    /**
     * Mesa de Ayuda Centralizada (Quito).
     */
    public function index(Request $request)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $tab = $request->input('tab', 'sistemas'); // sistemas como principal por ahora

        $query = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente'])
            ->where('tipo_ticket', $tab);

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        // Filtro por prioridad
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->input('prioridad'));
        }

        // Filtro por empresa origen
        if ($request->filled('empresa')) {
            $query->where('empresa_origen', $request->input('empresa'));
        }

        // Filtro por tienda/sucursal cliente
        if ($request->filled('sucursal_cliente_id')) {
            $query->where('sucursal_cliente_id', $request->input('sucursal_cliente_id'));
        }

        // Filtro por técnico asignado
        if ($request->filled('asignado_a_id')) {
            if ($request->input('asignado_a_id') === 'sin_asignar') {
                $query->whereNull('asignado_a_id');
            } else {
                $query->where('asignado_a_id', $request->input('asignado_a_id'));
            }
        }

        // Búsqueda general
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo_ticket', 'LIKE', "%{$q}%")
                    ->orWhere('titulo', 'LIKE', "%{$q}%")
                    ->orWhere('descripcion', 'LIKE', "%{$q}%")
                    ->orWhere('tienda_nombre', 'LIKE', "%{$q}%")
                    ->orWhereHas('solicitante', function ($us) use ($q) {
                        $us->where('nombre_tecnico', 'LIKE', "%{$q}%")
                           ->orWhere('usuario', 'LIKE', "%{$q}%");
                    });
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Conteo por tabs y estados
        $conteoSoporte = Ticket::where('tipo_ticket', 'soporte_tecnico')->whereIn('estado', ['abierto', 'en_proceso', 'en_espera', 'en_mba'])->count();
        $conteoSistemas = Ticket::where('tipo_ticket', 'sistemas')->whereIn('estado', ['abierto', 'en_proceso', 'en_espera', 'en_mba'])->count();

        $stats = [
            'abiertos' => Ticket::where('tipo_ticket', $tab)->where('estado', 'abierto')->count(),
            'en_proceso' => Ticket::where('tipo_ticket', $tab)->where('estado', 'en_proceso')->count(),
            'en_espera' => Ticket::where('tipo_ticket', $tab)->where('estado', 'en_espera')->count(),
            'en_mba' => Ticket::where('tipo_ticket', $tab)->where('estado', 'en_mba')->count(),
            'resueltos' => Ticket::where('tipo_ticket', $tab)->where('estado', 'resuelto')->count(),
            'cerrados' => Ticket::where('tipo_ticket', $tab)->where('estado', 'cerrado')->count(),
        ];

        // Lista de técnicos elegibles según el área (Sistemas vs Soporte Técnico)
        if ($tab === 'sistemas') {
            $tecnicosQuito = Usuario::where('activo', 1)
                ->whereIn('id', TicketService::TECNICOS_SISTEMAS_IDS)
                ->orderByRaw("FIELD(id, 29, 2, 8, 1)")
                ->get();
        } else {
            $tecnicosQuito = Usuario::tecnicosOperativos()
                ->whereHas('rol', fn($r) => $r->whereIn('rol', ['tecnico', 'tecnico master', 'administrador master']))
                ->orderBy('nombre_tecnico')
                ->get();
        }

        $tiendas = SucursalCliente::where('activa', 1)->orderBy('nombre')->get();

        return view('tickets.gestion', compact('tickets', 'tab', 'stats', 'conteoSoporte', 'conteoSistemas', 'tecnicosQuito', 'tiendas', 'usuario'));
    }

    /**
     * Vista de atención de un ticket individual.
     */
    public function show(int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $ticket = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente', 'mensajes.usuario', 'adjuntos'])
            ->findOrFail($id);

        if ($ticket->tipo_ticket === 'sistemas') {
            $tecnicosQuito = Usuario::where('activo', 1)
                ->whereIn('id', TicketService::TECNICOS_SISTEMAS_IDS)
                ->orderByRaw("FIELD(id, 29, 2, 8, 1)")
                ->get();
        } else {
            $tecnicosQuito = Usuario::where('activo', 1)
                ->whereHas('rol', fn($r) => $r->whereIn('rol', ['tecnico', 'tecnico master', 'administrador master']))
                ->orderBy('nombre_tecnico')
                ->get();
        }

        return view('tickets.atender', compact('ticket', 'tecnicosQuito', 'usuario'));
    }

    /**
     * Asignar o reasignar ticket.
     */
    public function asignar(Request $request, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $ticket = Ticket::findOrFail($id);

        if (in_array($ticket->estado, ['resuelto', 'cerrado', 'cancelado'])) {
            return response()->json(['ok' => false, 'error' => "No se puede reasignar un ticket que ya se encuentra en estado '" . strtoupper($ticket->estado) . "'."], 422);
        }

        $tecnicoId = $request->filled('tecnico_id') ? (int) $request->input('tecnico_id') : null;

        if ($tecnicoId && $ticket->tipo_ticket === 'sistemas') {
            if (!in_array($tecnicoId, TicketService::TECNICOS_SISTEMAS_IDS)) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Para tickets de sistemas, únicamente pueden ser asignados: Erick Chavarrea, Carlos Ramos, Omar Almeida o Josué Romero.'
                ], 422);
            }
        }

        try {
            $this->ticketService->asignarTicket($ticket, $tecnicoId, $usuario);
            return response()->json(['ok' => true, 'mensaje' => 'Ticket asignado correctamente.']);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al asignar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cambiar estado del ticket (En proceso, En espera, En manos de MBA, Resuelto, Cerrado, Cancelado).
     */
    public function cambiarEstado(Request $request, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $request->validate([
            'estado' => 'required|in:abierto,en_proceso,en_espera,en_mba,resuelto,cerrado,cancelado',
            'motivo' => 'nullable|string',
            'solucion' => 'nullable|string',
            'numero_ticket_mba' => 'nullable|string|max:60',
        ]);

        $ticket = Ticket::findOrFail($id);
        $nuevoEstado = $request->input('estado');

        if ($nuevoEstado === 'resuelto') {
            if (empty($ticket->asignado_a_id)) {
                return response()->json([
                    'ok' => false,
                    'error' => 'No se puede resolver el ticket porque no tiene un técnico asignado. Por favor asigna un técnico responsable primero.'
                ], 422);
            }
            if (!$request->filled('solucion')) {
                return response()->json(['ok' => false, 'error' => 'Debe describir la solución aplicada para resolver el ticket.'], 422);
            }
        }

        if ($nuevoEstado === 'en_mba' && !$request->filled('numero_ticket_mba')) {
            return response()->json(['ok' => false, 'error' => 'Debe ingresar el número de ticket / caso asignado por MBA.'], 422);
        }

        $archivos = [];
        if ($request->hasFile('evidencia')) {
            $f = $request->file('evidencia');
            $archivos = is_array($f) ? $f : [$f];
        } elseif ($request->hasFile('archivos')) {
            $f = $request->file('archivos');
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

            return response()->json(['ok' => true, 'mensaje' => "Estado actualizado a '{$nuevoEstado}'."]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al cambiar estado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Agregar respuesta técnica o nota interna.
     */
    public function responder(Request $request, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $request->validate([
            'mensaje' => 'required|string',
            'es_nota_interna' => 'nullable|boolean',
            'archivos.*' => 'nullable|file|max:15360',
        ]);

        $ticket = Ticket::findOrFail($id);

        try {
            $esNotaInterna = $request->boolean('es_nota_interna');
            $archivos = $request->file('archivos', []);

            $this->ticketService->agregarMensaje(
                $ticket,
                $usuario,
                $request->input('mensaje'),
                $esNotaInterna,
                null,
                is_array($archivos) ? $archivos : []
            );

            // Si estaba abierto y un técnico responde públicamente, pasa a en_proceso
            if (!$esNotaInterna && $ticket->estado === 'abierto') {
                $this->ticketService->cambiarEstado($ticket, 'en_proceso', $usuario);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'mensaje' => $esNotaInterna ? 'Nota interna guardada.' : 'Respuesta enviada al solicitante.']);
            }

            return back()->with('success', $esNotaInterna ? 'Nota interna guardada.' : 'Respuesta enviada.');
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Genera y descarga el documento oficial de Word (.docx) para un Caso MBA3.
     */
    public function descargarWordMba(\App\Services\Operations\TicketDocxService $docxService, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $ticket = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente'])->findOrFail($id);

        try {
            $docxContent = $docxService->generarDocxCasoMba($ticket);
            $filename = "Caso_MBA3_{$ticket->codigo_ticket}.docx";

            return response($docxContent, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, private',
                'Pragma' => 'no-cache',
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Error al generar documento Word: ' . $e->getMessage());
        }
    }

    /**
     * Vista de impresión oficial y generación de PDF del Ticket (Estilo idéntico a Órdenes de Trabajo).
     */
    public function imprimir(int $id)
    {
        $ticket = Ticket::with(['solicitante.sucursalCliente', 'asignadoA', 'sucursalCliente', 'mensajes.usuario', 'adjuntos'])
            ->findOrFail($id);

        return view('tickets.imprimir', compact('ticket'));
    }
    /**
     * Califica la atención del ticket.
     */
    public function calificar(Request $request, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
        }

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

            return response()->json(['ok' => true, 'mensaje' => '¡Muchas gracias por tu reseña y calificación!']);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al registrar calificación: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reabre un ticket resuelto o cerrado.
     */
    public function reabrir(Request $request, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
        }

        $request->validate([
            'motivo' => 'required|string|min:4|max:1000',
        ]);

        try {
            $this->ticketService->reabrirTicket(
                $ticket,
                trim($request->input('motivo')),
                $usuario
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'mensaje' => "El ticket {$ticket->codigo_ticket} ha sido reabierto con éxito."
                ]);
            }

            return back()->with('success', "Ticket {$ticket->codigo_ticket} reabierto correctamente.");
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al reabrir el ticket: ' . $e->getMessage()], 500);
        }
    }
}
