<?php

namespace App\Services\Operations;

use App\Models\Directory\Sucursal;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use App\Models\Operations\TicketAdjunto;
use App\Models\Operations\TicketMensaje;
use App\Services\Identity\ActividadDiariaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TicketService
{
    /**
     * Técnicos autorizados exclusivamente para resolver tickets del área de Sistemas:
     * - Erick Chavarrea (ID 29)
     * - Carlos Ramos (ID 2)
     * - Omar Almeida (ID 8)
     * - Josué Romero (ID 1)
     */
    public const TECNICOS_SISTEMAS_IDS = [29, 2, 8, 1];

    /**
     * Genera un código de ticket atómico secuencial.
     * Ejemplos: SYS-000001 (Sistemas), UIO-TK-000001 (Soporte Técnico)
     */
    public function generarCodigoTicket(string $tipoTicket, int $sucursalAtencionId = 1): string
    {
        if ($tipoTicket === 'sistemas') {
            $prefijo = 'SYS-';
            $ultimo = DB::table('tickets')
                ->where('tipo_ticket', 'sistemas')
                ->where('codigo_ticket', 'LIKE', 'SYS-%')
                ->orderByDesc('id')
                ->value('codigo_ticket');

            $numero = 1;
            if ($ultimo && preg_match('/SYS-(\d+)/i', $ultimo, $m)) {
                $numero = ((int) $m[1]) + 1;
            }
            return $prefijo . str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
        }

        // Soporte técnico (Quito / UIO)
        $sucursal = Sucursal::find($sucursalAtencionId);
        $sec = $sucursal ? ($sucursal->secuencial ?: 'UIO') : 'UIO';
        $prefijo = $sec . '-TK-';

        $ultimo = DB::table('tickets')
            ->where('tipo_ticket', 'soporte_tecnico')
            ->where('codigo_ticket', 'LIKE', $prefijo . '%')
            ->orderByDesc('id')
            ->value('codigo_ticket');

        $numero = 1;
        if ($ultimo && preg_match('/' . preg_quote($prefijo, '/') . '(\d+)/i', $ultimo, $m)) {
            $numero = ((int) $m[1]) + 1;
        }

        return $prefijo . str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crea un nuevo ticket con sus adjuntos iniciales.
     */
    public function crearTicket(array $data, Usuario $usuario, array $archivos = []): Ticket
    {
        return DB::transaction(function () use ($data, $usuario, $archivos) {
            $tipoTicket = $data['tipo_ticket'] ?? 'sistemas';
            $sucursalAtencionId = (int) ($data['sucursal_atencion_id'] ?? 1); // 1 = Quito por defecto

            $codigo = $this->generarCodigoTicket($tipoTicket, $sucursalAtencionId);

            // Obtener tienda externa si viene sucursal_cliente_id
            $sucursalClienteId = !empty($data['sucursal_cliente_id']) ? (int) $data['sucursal_cliente_id'] : null;
            $tiendaNombre = $data['tienda_nombre'] ?? null;
            if ($sucursalClienteId && empty($tiendaNombre)) {
                $sucCli = SucursalCliente::find($sucursalClienteId);
                if ($sucCli) {
                    $tiendaNombre = $sucCli->codigo . ' - ' . $sucCli->nombre;
                }
            }

            $ticket = Ticket::create([
                'codigo_ticket' => $codigo,
                'tipo_ticket' => $tipoTicket,
                'categoria' => $data['categoria'] ?? 'General',
                'prioridad' => $data['prioridad'] ?? 'media',
                'estado' => 'abierto',
                'solicitante_id' => $usuario->id,
                'empresa_origen' => $data['empresa_origen'] ?? 'NOVICOMPU',
                'sucursal_cliente_id' => $sucursalClienteId,
                'tienda_nombre' => $tiendaNombre,
                'contacto_telefono' => $data['contacto_telefono'] ?? $usuario->telefono,
                'sucursal_atencion_id' => $sucursalAtencionId,
                'asignado_a_id' => !empty($data['asignado_a_id']) ? (int) $data['asignado_a_id'] : null,
                'titulo' => trim($data['titulo']),
                'descripcion' => trim($data['descripcion']),
                'fecha_apertura' => now(),
            ]);

            // Guardar archivos adjuntos si existen
            if (!empty($archivos)) {
                $this->guardarAdjuntos($ticket, null, $usuario, $archivos);
            }

            // Registrar primer mensaje de apertura
            TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'usuario_id' => $usuario->id,
                'mensaje' => 'Ticket creado por el usuario solicitante.',
                'es_nota_interna' => false,
                'cambio_estado' => 'abierto',
            ]);

            // Enviar notificación por correo al solicitante
            TicketMailService::enviarTicketCreado($ticket);

            return $ticket;
        });
    }

    /**
     * Agrega un mensaje o respuesta al timeline del ticket.
     */
    public function agregarMensaje(
        Ticket $ticket,
        Usuario $usuario,
        string $mensaje,
        bool $esNotaInterna = false,
        ?string $cambioEstado = null,
        array $archivos = []
    ): TicketMensaje {
        return DB::transaction(function () use ($ticket, $usuario, $mensaje, $esNotaInterna, $cambioEstado, $archivos) {
            // Si es la primera respuesta del técnico, registrar fecha
            if (!$ticket->fecha_primera_respuesta && $usuario->id !== $ticket->solicitante_id && !$esNotaInterna) {
                $ticket->update(['fecha_primera_respuesta' => now()]);
            }

            $msg = TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'usuario_id' => $usuario->id,
                'mensaje' => trim($mensaje),
                'es_nota_interna' => $esNotaInterna,
                'cambio_estado' => $cambioEstado,
            ]);

            // Guardar archivos si vienen adjuntos en el mensaje
            if (!empty($archivos)) {
                $this->guardarAdjuntos($ticket, $msg->id, $usuario, $archivos);
            }

            // Registrar en actividades diarias del técnico si quien escribe es el técnico / soporte
            if ($usuario->id !== $ticket->solicitante_id) {
                try {
                    app(ActividadDiariaService::class)->registrar(
                        $usuario->id,
                        'tickets',
                        "Seguimiento técnico en ticket {$ticket->codigo_ticket}: {$ticket->titulo}",
                        'tickets',
                        $ticket->id,
                        'Ticket'
                    );
                } catch (Throwable $e) {
                    Log::error("Error registrando actividad diaria: " . $e->getMessage());
                }
            }

            // Notificar por correo al solicitante si el mensaje es de soporte/técnico
            if ($usuario->id !== $ticket->solicitante_id && !$esNotaInterna) {
                TicketMailService::enviarRespuestaMensaje($ticket, $msg, $usuario);
            }

            return $msg;
        });
    }

    /**
     * Cambia el estado del ticket y registra en el historial.
     */
    public function cambiarEstado(
        Ticket $ticket,
        string $nuevoEstado,
        Usuario $usuario,
        ?string $motivo = null,
        ?string $solucion = null,
        array $archivos = [],
        ?string $numeroTicketMba = null
    ): Ticket {
        return DB::transaction(function () use ($ticket, $nuevoEstado, $usuario, $motivo, $solucion, $archivos, $numeroTicketMba) {
            $estadoAnterior = $ticket->estado;
            $updates = ['estado' => $nuevoEstado];

            if ($numeroTicketMba !== null) {
                $updates['numero_ticket_mba'] = trim($numeroTicketMba);
            }

            if ($nuevoEstado === 'en_mba') {
                $updates['fecha_escalado_mba'] = now();
                if ($numeroTicketMba) {
                    $updates['numero_ticket_mba'] = trim($numeroTicketMba);
                }
            }

            if ($nuevoEstado === 'en_proceso' && !$ticket->fecha_asignacion) {
                $updates['fecha_asignacion'] = now();
                if (!$ticket->asignado_a_id) {
                    $updates['asignado_a_id'] = $usuario->id;
                }
            }

            if ($nuevoEstado === 'resuelto') {
                if (empty($ticket->asignado_a_id)) {
                    throw new Exception('No se puede resolver un ticket que no tiene un técnico asignado. Por favor asigna un técnico responsable primero.');
                }
                $updates['fecha_resolucion'] = now();
                if ($solucion) {
                    $updates['solucion'] = trim($solucion);
                }
            }

            if ($nuevoEstado === 'cerrado') {
                $updates['fecha_cierre'] = now();
                if ($solucion && empty($ticket->solucion)) {
                    $updates['solucion'] = trim($solucion);
                }
            }

            $ticket->update($updates);

            if ($nuevoEstado === 'en_mba') {
                $detalleMensaje = "Caso escalado a soporte externo MBA (Tiempo máx. de resolución: 48 horas).";
                if ($ticket->numero_ticket_mba) {
                    $detalleMensaje .= "\nN° Caso / Ticket MBA: {$ticket->numero_ticket_mba}";
                }
                if ($motivo) {
                    $detalleMensaje .= "\nObservaciones: {$motivo}";
                }
            } else {
                $detalleMensaje = "Estado cambiado de '{$estadoAnterior}' a '{$nuevoEstado}'." . ($motivo ? " Motivo/Detalle: {$motivo}" : "");
                if ($solucion) {
                    $detalleMensaje .= "\nSolución registrada: {$solucion}";
                }
            }

            $msg = TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'usuario_id' => $usuario->id,
                'mensaje' => $detalleMensaje,
                'es_nota_interna' => false,
                'cambio_estado' => $nuevoEstado,
            ]);

            // Guardar fotos o archivos de evidencia adjuntos a la resolución/cambio de estado
            if (!empty($archivos)) {
                $this->guardarAdjuntos($ticket, $msg->id, $usuario, $archivos);
            }

            // Registrar en actividades diarias del técnico
            try {
                if ($nuevoEstado === 'en_mba') {
                    $mbaNum = $ticket->numero_ticket_mba ? " [Ticket MBA #{$ticket->numero_ticket_mba}]" : "";
                    app(ActividadDiariaService::class)->registrar(
                        $usuario->id,
                        'tickets',
                        "Caso escalado a soporte MBA{$mbaNum} en Ticket {$ticket->codigo_ticket}: {$ticket->titulo}",
                        'tickets',
                        $ticket->id,
                        'Ticket'
                    );
                } elseif ($nuevoEstado === 'resuelto') {
                    $solDesc = $solucion ? (" | Solución: " . mb_substr($solucion, 0, 150)) : "";
                    app(ActividadDiariaService::class)->registrar(
                        $usuario->id,
                        'tickets',
                        "Resolución de Ticket {$ticket->codigo_ticket}: {$ticket->titulo} ({$ticket->categoria}){$solDesc}",
                        'tickets',
                        $ticket->id,
                        'Ticket'
                    );
                } elseif ($nuevoEstado === 'cerrado') {
                    app(ActividadDiariaService::class)->registrar(
                        $usuario->id,
                        'tickets',
                        "Cierre de Ticket {$ticket->codigo_ticket}: {$ticket->titulo}",
                        'tickets',
                        $ticket->id,
                        'Ticket'
                    );
                } elseif ($nuevoEstado === 'en_proceso') {
                    app(ActividadDiariaService::class)->registrar(
                        $usuario->id,
                        'tickets',
                        "Atención iniciada en Ticket {$ticket->codigo_ticket}: {$ticket->titulo}",
                        'tickets',
                        $ticket->id,
                        'Ticket'
                    );
                }
            } catch (Throwable $e) {
                Log::error("Error registrando actividad diaria en cambio de estado: " . $e->getMessage());
            }

            // Notificar por correo al solicitante del cambio de estado
            if ($nuevoEstado !== $estadoAnterior) {
                TicketMailService::enviarEstadoCambiado($ticket, $nuevoEstado, $motivo, $solucion);
            }

            return $ticket;
        });
    }

    /**
     * Asigna el ticket a un técnico o admin resolutor.
     */
    public function asignarTicket(Ticket $ticket, ?int $tecnicoId, Usuario $usuario): Ticket
    {
        if (in_array($ticket->estado, ['resuelto', 'cerrado', 'cancelado'])) {
            throw new \Exception("No se puede reasignar un ticket que ya se encuentra en estado '" . strtoupper($ticket->estado) . "'.");
        }

        if ($tecnicoId && $ticket->tipo_ticket === 'sistemas') {
            if (!in_array($tecnicoId, self::TECNICOS_SISTEMAS_IDS)) {
                throw new \Exception("Para tickets de sistemas, únicamente pueden ser asignados: Erick Chavarrea, Carlos Ramos, Omar Almeida o Josué Romero.");
            }
        }

        return DB::transaction(function () use ($ticket, $tecnicoId, $usuario) {
            $tecnicoNombre = 'Sin asignar';
            $tec = null;
            if ($tecnicoId) {
                $tec = Usuario::find($tecnicoId);
                $tecnicoNombre = $tec ? ($tec->nombre_tecnico ?: $tec->usuario) : 'ID ' . $tecnicoId;
            }

            $updates = [
                'asignado_a_id' => $tecnicoId,
            ];

            if ($tecnicoId && !$ticket->fecha_asignacion) {
                $updates['fecha_asignacion'] = now();
            }

            if ($tecnicoId && $ticket->estado === 'abierto') {
                $updates['estado'] = 'en_proceso';
            }

            $ticket->update($updates);

            TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'usuario_id' => $usuario->id,
                'mensaje' => "Ticket asignado a: {$tecnicoNombre}.",
                'es_nota_interna' => true,
                'cambio_estado' => $ticket->estado,
            ]);

            // Registrar en actividades diarias del técnico
            if ($tecnicoId) {
                try {
                    app(ActividadDiariaService::class)->registrar(
                        $tecnicoId,
                        'tickets',
                        "Ticket asignado {$ticket->codigo_ticket}: {$ticket->titulo} ({$ticket->categoria})",
                        'tickets',
                        $ticket->id,
                        'Ticket'
                    );
                } catch (Throwable $e) {
                    Log::error("Error registrando actividad diaria en asignación: " . $e->getMessage());
                }
            }

            // Notificar al solicitante que se asignó un técnico
            if ($tec) {
                TicketMailService::enviarTecnicoAsignado($ticket, $tec);
            }

            return $ticket;
        });
    }

    /**
     * Guarda archivos adjuntos asociados a un ticket / mensaje.
     */
    public function guardarAdjuntos(Ticket $ticket, ?int $mensajeId, Usuario $usuario, array $archivos): array
    {
        $adjuntos = [];
        foreach ($archivos as $archivo) {
            if (!($archivo instanceof UploadedFile) || !$archivo->isValid()) {
                continue;
            }

            $nombreOriginal = $archivo->getClientOriginalName();
            $extension = $archivo->getClientOriginalExtension();
            $mimeType = $archivo->getClientMimeType();
            $tamano = $archivo->getSize();

            $nombreGuardado = 'ticket_' . $ticket->id . '_' . uniqid() . '.' . ($extension ?: 'bin');
            $ruta = $archivo->storeAs('tickets_adjuntos', $nombreGuardado, 'public');

            $adjunto = TicketAdjunto::create([
                'ticket_id' => $ticket->id,
                'mensaje_id' => $mensajeId,
                'usuario_id' => $usuario->id,
                'nombre_archivo' => $nombreOriginal,
                'ruta_archivo' => $ruta,
                'mime_type' => $mimeType,
                'tamano_bytes' => $tamano,
            ]);

            $adjuntos[] = $adjunto;
        }

        return $adjuntos;
    }

    /**
     * Califica la atención de un ticket resuelto.
     */
        public function calificarTicket(Ticket $ticket, int $calificacion, ?string $comentario, Usuario $usuario): Ticket
    {
        $ticket->update([
            'calificacion' => max(1, min(5, $calificacion)),
            'comentario_calificacion' => $comentario ? trim($comentario) : null,
            'estado' => 'cerrado',
            'fecha_cierre' => $ticket->fecha_cierre ?: now(),
        ]);

        return $ticket;
    }

    /**
     * Reabre un ticket resuelto o cerrado por solicitud del usuario solicitante.
     */
    public function reabrirTicket(Ticket $ticket, string $motivo, Usuario $usuario): Ticket
    {
        return DB::transaction(function () use ($ticket, $motivo, $usuario) {
            $nuevoEstado = $ticket->asignado_a_id ? 'en_proceso' : 'abierto';

            $ticket->update([
                'estado' => $nuevoEstado,
                'fecha_cierre' => null,
            ]);

            TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'usuario_id' => $usuario->id,
                'mensaje' => "TICKET REABIERTO POR EL SOLICITANTE\nMotivo de reapertura: {$motivo}",
                'es_nota_interna' => false,
                'cambio_estado' => $nuevoEstado,
            ]);

            // Notificar reapertura al solicitante y a los Administradores Master
            TicketMailService::enviarTicketReabierto($ticket, $motivo, $usuario);

            return $ticket;
        });
    }
}
