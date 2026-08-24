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

class MisTicketsController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    /**
     * Listado de tickets creados por el usuario solicitante.
     */
    public function index(Request $request)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $query = Ticket::with(['asignadoA', 'sucursalCliente'])
            ->where('solicitante_id', $usuario->id);

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_ticket', $request->input('tipo'));
        }

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo_ticket', 'LIKE', "%{$q}%")
                    ->orWhere('titulo', 'LIKE', "%{$q}%")
                    ->orWhere('descripcion', 'LIKE', "%{$q}%");
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $stats = [
            'total' => Ticket::where('solicitante_id', $usuario->id)->count(),
            'abiertos' => Ticket::where('solicitante_id', $usuario->id)->whereIn('estado', ['abierto', 'en_proceso', 'en_espera'])->count(),
            'resueltos' => Ticket::where('solicitante_id', $usuario->id)->where('estado', 'resuelto')->count(),
            'cerrados' => Ticket::where('solicitante_id', $usuario->id)->where('estado', 'cerrado')->count(),
        ];

        return view('tickets.mis_tickets', compact('tickets', 'stats', 'usuario'));
    }

    /**
     * Formulario de creación de ticket.
     */
    public function create()
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $usuario->load('sucursalCliente');

        $tiendasNovicompu = SucursalCliente::where('activa', 1)
            ->orderBy('nombre')
            ->get();

        return view('tickets.crear', compact('usuario', 'tiendasNovicompu'));
    }

    /**
     * Guarda el nuevo ticket.
     */
    public function store(Request $request)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

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
            'archivos.*' => 'nullable|file|max:15360', // Máx 15MB por archivo
        ]);

        try {
            $archivos = $request->file('archivos', []);
            $ticket = $this->ticketService->crearTicket($requestData, $usuario, is_array($archivos) ? $archivos : []);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'mensaje' => "Ticket {$ticket->codigo_ticket} creado con éxito.",
                    'ticket_id' => $ticket->id,
                    'redirect' => route('mistickets.show', $ticket->id),
                ]);
            }

            return redirect()->route('mistickets.show', $ticket->id)->with('success', "Ticket {$ticket->codigo_ticket} creado correctamente.");
        } catch (Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => 'Error al crear ticket: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error al crear ticket: ' . $e->getMessage());
        }
    }

    /**
     * Vista de detalle y seguimiento del ticket.
     */
    public function show(int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $ticket = Ticket::with(['asignadoA', 'solicitante', 'sucursalCliente', 'mensajes.usuario', 'adjuntos'])
            ->where('id', $id)
            ->where('solicitante_id', $usuario->id)
            ->firstOrFail();

        return view('tickets.ver', compact('ticket', 'usuario'));
    }

    /**
     * Envía un mensaje / respuesta al ticket desde el solicitante.
     */
    public function responder(Request $request, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $ticket = Ticket::where('id', $id)
            ->where('solicitante_id', $usuario->id)
            ->firstOrFail();

        $request->validate([
            'mensaje' => 'required|string',
            'archivos.*' => 'nullable|file|max:15360',
        ]);

        try {
            $archivos = $request->file('archivos', []);
            $this->ticketService->agregarMensaje(
                $ticket,
                $usuario,
                $request->input('mensaje'),
                false,
                null,
                is_array($archivos) ? $archivos : []
            );

            // Si el ticket estaba en espera o resuelto y el solicitante responde, vuelve a en_proceso
            if (in_array($ticket->estado, ['en_espera', 'resuelto'])) {
                $this->ticketService->cambiarEstado($ticket, 'en_proceso', $usuario, 'Reabierto por respuesta del solicitante');
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'mensaje' => 'Mensaje enviado correctamente.']);
            }

            return back()->with('success', 'Mensaje enviado.');
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al responder: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Califica y cierra el ticket resuelto.
     */
    public function calificar(Request $request, int $id)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        $ticket = Ticket::where('id', $id)
            ->where('solicitante_id', $usuario->id)
            ->firstOrFail();

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

            return response()->json(['ok' => true, 'mensaje' => '¡Gracias por calificar la atención!']);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Vista de perfil / datos técnicos del solicitante (MBA3, AnyDesk, Código).
     */
    public function perfil()
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $usuario->load('sucursalCliente');

        return view('tickets.mi_perfil', compact('usuario'));
    }

    /**
     * Guarda los datos técnicos del solicitante.
     */
    public function guardarPerfil(Request $request)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        $request->validate([
            'correo_tec' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:30',
            'usuario_mba' => 'nullable|string|max:60',
            'codigo_usuario' => 'nullable|string|max:60',
            'anydesk_id' => 'nullable|string|max:50',
        ]);

        try {
            $usuario->update([
                'correo_tec' => $request->input('correo_tec') ? trim($request->input('correo_tec')) : null,
                'telefono' => $request->input('telefono'),
                'usuario_mba' => $request->input('usuario_mba') ? trim($request->input('usuario_mba')) : null,
                'codigo_usuario' => $request->input('codigo_usuario') ? trim($request->input('codigo_usuario')) : null,
                'anydesk_id' => $request->input('anydesk_id') ? trim($request->input('anydesk_id')) : null,
            ]);

            return back()->with('success', 'Tus datos de soporte técnico se actualizaron correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error al guardar datos: ' . $e->getMessage());
        }
    }
}
