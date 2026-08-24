<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use App\Models\Operations\TicketLlamada;
use App\Services\Operations\TicketService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TicketLlamadaController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    /**
     * Inicia una llamada de soporte técnico para el ticket.
     */
    public function iniciar(Request $request, int $ticketId): JsonResponse
    {
        try {
            $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
            if (!$usuario) {
                return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
            }

            $ticket = Ticket::with(['solicitante'])->findOrFail($ticketId);

            // Cancelar llamadas anteriores pendientes o colgadas
            TicketLlamada::where('ticket_id', $ticketId)
                ->whereIn('estado', ['timbrando', 'en_curso'])
                ->update([
                    'estado' => 'finalizada',
                    'fecha_fin' => now(),
                ]);

            $llamada = TicketLlamada::create([
                'ticket_id' => $ticketId,
                'iniciador_id' => $usuario->id,
                'receptor_id' => $ticket->solicitante_id,
                'estado' => 'timbrando',
                'signal_offer' => $request->input('offer'),
                'signal_ice_iniciador' => json_encode($request->input('ice', [])),
                'fecha_inicio' => now(),
            ]);

            // Agregar mensaje informativo en el chat
            try {
                $this->ticketService->agregarMensaje(
                    $ticket,
                    $usuario,
                    "📞 Llamada de soporte iniciada por " . ($usuario->nombre_tecnico ?: $usuario->usuario) . "...",
                    false,
                    null
                );
            } catch (Throwable $e) {}

            return response()->json([
                'ok' => true,
                'llamada_id' => $llamada->id,
                'estado' => $llamada->estado,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Error al iniciar llamada: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acepta la llamada entrante y envía la respuesta SDP (WebRTC Answer).
     */
    public function contestar(Request $request, int $ticketId): JsonResponse
    {
        try {
            $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
            if (!$usuario) {
                return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
            }

            $llamada = TicketLlamada::where('ticket_id', $ticketId)
                ->where('estado', 'timbrando')
                ->latest('id')
                ->first();

            if (!$llamada) {
                return response()->json(['ok' => false, 'error' => 'No hay una llamada activa'], 404);
            }

            $llamada->update([
                'estado' => 'en_curso',
                'receptor_id' => $usuario->id,
                'signal_answer' => $request->input('answer'),
                'signal_ice_receptor' => json_encode($request->input('ice', [])),
                'fecha_inicio' => now(),
            ]);

            return response()->json([
                'ok' => true,
                'llamada_id' => $llamada->id,
                'estado' => 'en_curso',
                'offer' => $llamada->signal_offer,
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Envía candidatos ICE adicionales para la conexión WebRTC.
     */
    public function enviarIce(Request $request, int $ticketId): JsonResponse
    {
        try {
            $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
            if (!$usuario) {
                return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
            }

            $llamada = TicketLlamada::where('ticket_id', $ticketId)
                ->whereIn('estado', ['timbrando', 'en_curso'])
                ->latest('id')
                ->first();

            if (!$llamada) {
                return response()->json(['ok' => false, 'error' => 'Sin llamada activa'], 404);
            }

            $nuevosIce = $request->input('ice');
            if ($nuevosIce) {
                $esIniciador = (int) $llamada->iniciador_id === (int) $usuario->id;
                $campo = $esIniciador ? 'signal_ice_iniciador' : 'signal_ice_receptor';
                $actuales = json_decode($llamada->$campo ?? '[]', true) ?: [];
                $actuales[] = $nuevosIce;
                $llamada->update([$campo => json_encode($actuales)]);
            }

            return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Consulta el estado en tiempo real de la llamada y señales WebRTC.
     */
    public function estado(Request $request, int $ticketId): JsonResponse
    {
        try {
            $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
            if (!$usuario) {
                return response()->json(['ok' => false, 'error' => 'No autenticado'], 401);
            }

            $llamada = TicketLlamada::with(['iniciador', 'receptor'])
                ->where('ticket_id', $ticketId)
                ->whereIn('estado', ['timbrando', 'en_curso'])
                ->latest('id')
                ->first();

            if (!$llamada) {
                return response()->json([
                    'ok' => true,
                    'hay_llamada' => false,
                ]);
            }

            $esIniciador = (int) $llamada->iniciador_id === (int) $usuario->id;

            return response()->json([
                'ok' => true,
                'hay_llamada' => true,
                'llamada_id' => $llamada->id,
                'estado' => $llamada->estado,
                'es_iniciador' => $esIniciador,
                'iniciador_nombre' => $llamada->iniciador ? ($llamada->iniciador->nombre_tecnico ?: $llamada->iniciador->usuario) : 'Soporte',
                'receptor_nombre' => $llamada->receptor ? ($llamada->receptor->nombre_tecnico ?: $llamada->receptor->usuario) : 'Tienda',
                'offer' => $llamada->signal_offer,
                'answer' => $llamada->signal_answer,
                'ice_peer' => json_decode(($esIniciador ? $llamada->signal_ice_receptor : $llamada->signal_ice_iniciador) ?? '[]', true),
                'fecha_inicio' => $llamada->fecha_inicio ? $llamada->fecha_inicio->toIso8601String() : null,
            ]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Rechaza la llamada entrante.
     */
    public function rechazar(Request $request, int $ticketId): JsonResponse
    {
        try {
            $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
            $ticket = Ticket::findOrFail($ticketId);

            $llamada = TicketLlamada::where('ticket_id', $ticketId)
                ->where('estado', 'timbrando')
                ->latest('id')
                ->first();

            if ($llamada) {
                $llamada->update([
                    'estado' => 'rechazada',
                    'fecha_fin' => now(),
                ]);

                try {
                    $this->ticketService->agregarMensaje(
                        $ticket,
                        $usuario,
                        "📞 Llamada rechazada por " . ($usuario ? ($usuario->nombre_tecnico ?: $usuario->usuario) : 'la tienda'),
                        false,
                        null
                    );
                } catch (Throwable $e) {}
            }

            return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Finaliza la llamada en curso y registra la duración.
     */
    public function finalizar(Request $request, int $ticketId): JsonResponse
    {
        try {
            $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
            $ticket = Ticket::findOrFail($ticketId);

            $llamada = TicketLlamada::where('ticket_id', $ticketId)
                ->whereIn('estado', ['timbrando', 'en_curso'])
                ->latest('id')
                ->first();

            if ($llamada) {
                $duracion = 0;
                if ($llamada->fecha_inicio) {
                    $duracion = max(1, now()->diffInSeconds($llamada->fecha_inicio));
                }

                $llamada->update([
                    'estado' => 'finalizada',
                    'duracion_segundos' => $duracion,
                    'fecha_fin' => now(),
                ]);

                $minutos = floor($duracion / 60);
                $segundos = $duracion % 60;
                $textoDuracion = ($minutos > 0 ? "{$minutos}m " : "") . "{$segundos}s";

                try {
                    $this->ticketService->agregarMensaje(
                        $ticket,
                        $usuario,
                        "📞 Llamada finalizada · Duración: {$textoDuracion}",
                        false,
                        null
                    );
                } catch (Throwable $e) {}
            }

        return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
