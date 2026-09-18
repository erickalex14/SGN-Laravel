<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use App\Models\Operations\TicketMensaje;
use App\Services\Operations\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TicketChatController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    /**
     * Sincroniza los nuevos mensajes del chat en tiempo real.
     */
    public function sync(Request $request, int $ticketId): JsonResponse
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
        }

        $ticket = Ticket::with(['solicitante', 'asignadoA'])->find($ticketId);
        if (!$ticket) {
            return response()->json(['ok' => false, 'error' => 'Ticket no encontrado'], 404);
        }

        $lastId = (int) ($request->input('last_id') ?? $request->input('ultimo_id') ?? 0);

        $query = TicketMensaje::with(['autor:id,usuario,nombre_tecnico,rol_id', 'adjuntos'])
            ->where('ticket_id', $ticketId)
            ->where('id', '>', $lastId);

        // Si el usuario es el solicitante o es de tienda, NO puede ver notas internas
        $esAdminOTecnico = $this->esPersonalSoporte($usuario);
        if (!$esAdminOTecnico) {
            $query->where('es_nota_interna', 0);
        }

        $mensajes = $query->orderBy('id', 'asc')->get();

        $mensajesFormateados = $mensajes->map(function ($m) use ($usuario, $ticket) {
            $esMio = (int) $m->usuario_id === (int) $usuario->id;
            $esSolicitante = (int) $m->usuario_id === (int) $ticket->solicitante_id;
            return [
                'id' => $m->id,
                'es_propio' => $esMio,
                'es_solicitante' => $esSolicitante,
                'es_nota_interna' => (bool) $m->es_nota_interna,
                'es_sistema' => (bool) $m->es_sistema,
                'autor_nombre' => $m->autor ? ($m->autor->nombre_tecnico ?: $m->autor->usuario) : 'Sistema SGN',
                'autor_usuario' => $m->autor?->usuario,
                'mensaje' => $m->mensaje,
                'hora' => $m->created_at ? $m->created_at->format('H:i') : '',
                'fecha_completa' => $m->created_at ? $m->created_at->format('d/m/Y H:i') : '',
                'adjuntos' => $m->adjuntos->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'nombre_original' => $a->nombre_original,
                        'url' => asset('storage/' . $a->ruta_archivo),
                        'es_imagen' => str_starts_with($a->tipo_mime ?? '', 'image/'),
                        'tamano_kb' => round(($a->tamano_bytes ?? 0) / 1024, 1),
                    ];
                }),
            ];
        });

        return response()->json([
            'ok' => true,
            'ticket_id' => $ticketId,
            'estado' => $ticket->estado,
            'fecha_resolucion' => $ticket->fecha_resolucion ? $ticket->fecha_resolucion->format('d/m/Y H:i') : null,
            'asignado_nombre' => $ticket->asignadoA ? ($ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario) : 'Sin asignar',
            'mensajes' => $mensajesFormateados,
        ]);
    }

    /**
     * Envía un nuevo mensaje al chat con soporte de imágenes y notas internas.
     */
    public function enviar(Request $request, int $ticketId): JsonResponse
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
        }

        $ticket = Ticket::findOrFail($ticketId);

        $request->validate([
            'mensaje' => 'required_without:archivos|nullable|string',
            'es_nota_interna' => 'nullable|boolean',
            'archivos.*' => 'nullable|file|max:15360',
        ]);

        try {
            $esNota = $request->boolean('es_nota_interna', false);
            
            // Si el usuario no es soporte/admin, no puede enviar notas internas
            if (!$this->esPersonalSoporte($usuario)) {
                $esNota = false;
            }

            $archivos = $request->file('archivos', []);
            if (!is_array($archivos) && $archivos) {
                $archivos = [$archivos];
            }

            $mensajeTexto = trim((string) $request->input('mensaje', ''));
            if ($mensajeTexto === '' && empty($archivos)) {
                return response()->json(['ok' => false, 'error' => 'El mensaje no puede estar vacío.'], 422);
            }

            $nuevoMensaje = $this->ticketService->agregarMensaje(
                $ticket,
                $usuario,
                $mensajeTexto,
                $esNota,
                null,
                $archivos
            );

            // Cargar relaciones
            $nuevoMensaje->load(['autor', 'adjuntos']);

            return response()->json([
                'ok' => true,
                'mensaje' => [
                    'id' => $nuevoMensaje->id,
                    'es_propio' => true,
                    'es_nota_interna' => (bool) $nuevoMensaje->es_nota_interna,
                    'es_sistema' => false,
                    'autor_nombre' => $usuario->nombre_tecnico ?: $usuario->usuario,
                    'mensaje' => $nuevoMensaje->mensaje,
                    'hora' => $nuevoMensaje->created_at ? $nuevoMensaje->created_at->format('H:i') : '',
                    'fecha_completa' => $nuevoMensaje->created_at ? $nuevoMensaje->created_at->format('d/m/Y H:i') : '',
                    'adjuntos' => $nuevoMensaje->adjuntos->map(function ($a) {
                        return [
                            'id' => $a->id,
                            'nombre_original' => $a->nombre_original,
                            'url' => asset('storage/' . $a->ruta_archivo),
                            'es_imagen' => str_starts_with($a->tipo_mime ?? '', 'image/'),
                            'tamano_kb' => round(($a->tamano_bytes ?? 0) / 1024, 1),
                        ];
                    }),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al enviar mensaje: ' . $e->getMessage()], 500);
        }
    }

    private function esPersonalSoporte(Usuario $usuario): bool
    {
        $sa = (bool) session('es_superadmin', false);
        $rolNombre = mb_strtolower(trim((string) ($usuario->rol?->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) ($usuario->grupo?->nombre ?? '')));
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        if ($sa || (bool)($usuario->grupo?->es_superadmin ?? false)) {
            return true;
        }

        $rolesSoporte = ['admin', 'administrador', 'admin master', 'administrador master', 'tecnico', 'tecnico master', 'técnico', 'técnico master'];
        if (in_array($rolNombre, $rolesSoporte, true) || in_array($grupoNombre, $rolesSoporte, true) || in_array($sessionGrupo, $rolesSoporte, true)) {
            return true;
        }

        if ($grupoNombre === 'sistemas' || $sessionGrupo === 'sistemas') {
            return true;
        }

        return false;
    }
}
