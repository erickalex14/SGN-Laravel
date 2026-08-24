<?php

namespace App\Services\Operations;

use App\Models\Directory\Sucursal;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use App\Models\Operations\TicketAdjunto;
use App\Models\Operations\TicketMensaje;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TicketService
{
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
            $tipoTicket = $data['tipo_ticket'] ?? 'soporte_tecnico';
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

            // Registrar fecha de primera respuesta si es la primera respuesta técnica
            if ($usuario->id !== $ticket->solicitante_id && !$esNotaInterna && !$ticket->fecha_primera_respuesta) {
                $ticket->update(['fecha_primera_respuesta' => now()]);
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
        ?string $solucion = null
    ): Ticket {
        return DB::transaction(function () use ($ticket, $nuevoEstado, $usuario, $motivo, $solucion) {
            $estadoAnterior = $ticket->estado;
            $updates = ['estado' => $nuevoEstado];

            if ($nuevoEstado === 'en_proceso' && !$ticket->fecha_asignacion) {
                $updates['fecha_asignacion'] = now();
                if (!$ticket->asignado_a_id) {
                    $updates['asignado_a_id'] = $usuario->id;
                }
            }

            if ($nuevoEstado === 'resuelto') {
                $updates['fecha_resolucion'] = now();
                if ($solucion) {
                    $updates['solucion'] = trim($solucion);
                }
            }

            if ($nuevoEstado === 'cerrado') {
                $updates['fecha_cierre'] = now();
            }

            $ticket->update($updates);

            $detalleMensaje = "Estado cambiado de '{$estadoAnterior}' a '{$nuevoEstado}'." . ($motivo ? " Motivo/Detalle: {$motivo}" : "");
            if ($solucion) {
                $detalleMensaje .= "\nSolución registrada: {$solucion}";
            }

            TicketMensaje::create([
                'ticket_id' => $ticket->id,
                'usuario_id' => $usuario->id,
                'mensaje' => $detalleMensaje,
                'es_nota_interna' => false,
                'cambio_estado' => $nuevoEstado,
            ]);

            return $ticket;
        });
    }

    /**
     * Asigna el ticket a un técnico o admin resolutor.
     */
    public function asignarTicket(Ticket $ticket, ?int $tecnicoId, Usuario $usuario): Ticket
    {
        return DB::transaction(function () use ($ticket, $tecnicoId, $usuario) {
            $tecnicoNombre = 'Sin asignar';
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
            'fecha_cierre' => now(),
        ]);

        TicketMensaje::create([
            'ticket_id' => $ticket->id,
            'usuario_id' => $usuario->id,
            'mensaje' => "Solicitante calificó la atención con {$calificacion} estrellas." . ($comentario ? " Comentario: {$comentario}" : ""),
            'es_nota_interna' => false,
            'cambio_estado' => 'cerrado',
        ]);

        return $ticket;
    }
}
