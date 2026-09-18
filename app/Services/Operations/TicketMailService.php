<?php

namespace App\Services\Operations;

use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use App\Models\Operations\TicketMensaje;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TicketMailService
{
    /**
     * Correos de los Administradores Master y Jefaturas para notificaciones de control.
     */
    public static function obtenerCorreosAdminMaster(): array
    {
        return [
            'sistemas@novicompu.com',
            'gerencia@novicompu.com',
            'soporte@novitec.com.ec',
        ];
    }

    /**
     * Obtiene el correo válido del solicitante.
     */
    private static function obtenerCorreoSolicitante(Ticket $ticket): ?string
    {
        $solicitante = $ticket->solicitante;
        if (!$solicitante) {
            return null;
        }

        $email = trim((string) ($solicitante->correo_tec ?: ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        return null;
    }

    /**
     * Notificación cuando el solicitante crea un nuevo ticket.
     */
    public static function enviarTicketCreado(Ticket $ticket): void
    {
        try {
            $destinatarios = self::obtenerCorreosAdminMaster();
            $correoSolicitante = self::obtenerCorreoSolicitante($ticket);
            if ($correoSolicitante) {
                $destinatarios[] = $correoSolicitante;
            }
            if ($ticket->asignadoA && filter_var($ticket->asignadoA->correo_tec, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = trim($ticket->asignadoA->correo_tec);
            }
            $destinatarios = array_values(array_unique($destinatarios));

            if (empty($destinatarios)) {
                Log::info("Ticket {$ticket->codigo_ticket}: sin destinatarios válidos para notificación.");
                return;
            }

            $solicitanteNombre = $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : 'Solicitante';
            $asunto = "[SGN Tickets] Solicitud Registrada: {$ticket->codigo_ticket} - {$ticket->titulo}";
            $urlTicket = "https://novitec.com.ec/sgn/mistickets/{$ticket->id}";

            $cuerpoHtml = self::generarPlantillaHtml([
                'titulo_banner' => 'Solicitud de Ticket Registrada',
                'color_banner' => '#2563eb',
                'icono_badge' => '',
                'mensaje_principal' => "Se ha registrado exitosamente el ticket <b>{$ticket->codigo_ticket}</b> de la tienda <b>{$ticket->tienda_nombre}</b> por <b>{$solicitanteNombre}</b>.",
                'codigo_ticket' => $ticket->codigo_ticket,
                'titulo_ticket' => $ticket->titulo,
                'tipo_ticket' => $ticket->tipo_ticket === 'sistemas' ? 'Sistemas / TI (Quito)' : 'Soporte Técnico (Hardware)',
                'categoria' => $ticket->categoria,
                'prioridad' => strtoupper($ticket->prioridad),
                'estado' => strtoupper($ticket->estado),
                'tienda' => $ticket->tienda_nombre ?: 'Sucursal',
                'detalle_cuerpo' => nl2br(e($ticket->descripcion)),
                'texto_boton' => 'Ver Ticket en SGN',
                'url_boton' => $urlTicket,
            ]);

            self::enviarEmail($destinatarios, $asunto, $cuerpoHtml);
        } catch (Throwable $e) {
            Log::error("Error al enviar email de ticket creado ({$ticket->codigo_ticket}): " . $e->getMessage());
        }
    }

    /**
     * Notificación cuando se asigna un técnico o especialista al ticket.
     */
    public static function enviarTecnicoAsignado(Ticket $ticket, Usuario $tecnico): void
    {
        try {
            $destinatarios = self::obtenerCorreosAdminMaster();
            $correoSolicitante = self::obtenerCorreoSolicitante($ticket);
            if ($correoSolicitante) {
                $destinatarios[] = $correoSolicitante;
            }
            if (filter_var($tecnico->correo_tec, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = trim($tecnico->correo_tec);
            }
            $destinatarios = array_values(array_unique($destinatarios));

            if (empty($destinatarios)) return;

            $solicitanteNombre = $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : 'Solicitante';
            $tecnicoNombre = $tecnico->nombre_tecnico ?: $tecnico->usuario;
            $asunto = "[SGN Tickets] Técnico Asignado: {$ticket->codigo_ticket} - {$ticket->titulo}";
            $urlTicket = "https://novitec.com.ec/sgn/mistickets/{$ticket->id}";

            $cuerpoHtml = self::generarPlantillaHtml([
                'titulo_banner' => 'Técnico Asignado a Ticket',
                'color_banner' => '#7c3aed',
                'icono_badge' => '',
                'mensaje_principal' => "El especialista <b>{$tecnicoNombre}</b> ha sido asignado para atender el ticket <b>{$ticket->codigo_ticket}</b> de <b>{$solicitanteNombre}</b>.",
                'codigo_ticket' => $ticket->codigo_ticket,
                'titulo_ticket' => $ticket->titulo,
                'tipo_ticket' => $ticket->tipo_ticket === 'sistemas' ? 'Sistemas / TI' : 'Soporte Técnico',
                'categoria' => $ticket->categoria,
                'prioridad' => strtoupper($ticket->prioridad),
                'estado' => 'EN PROCESO / ATENCIÓN',
                'tienda' => $ticket->tienda_nombre ?: 'Sucursal',
                'detalle_cuerpo' => "El equipo técnico se encuentra gestionando el requerimiento. La comunicación se mantiene activa a través del chat del ticket.",
                'texto_boton' => 'Abrir Chat del Ticket',
                'url_boton' => $urlTicket,
            ]);

            self::enviarEmail($destinatarios, $asunto, $cuerpoHtml);
        } catch (Throwable $e) {
            Log::error("Error al enviar email de asignación ({$ticket->codigo_ticket}): " . $e->getMessage());
        }
    }

    /**
     * Notificación cuando el técnico o mesa de ayuda responde en el chat del ticket.
     * Desactivada para evitar saturación de correos por cada mensaje del chat.
     */
    public static function enviarRespuestaMensaje(Ticket $ticket, TicketMensaje $mensaje, Usuario $autor): void
    {
        // Se desactiva el envío de correos en cada mensaje del chat para evitar spam
        return;
    }

    /**
     * Notificación cuando se REABRE un ticket (Enviado al solicitante, técnico y Administradores Master).
     */
    public static function enviarTicketReabierto(Ticket $ticket, string $motivo, Usuario $usuario): void
    {
        try {
            $destinatarios = self::obtenerCorreosAdminMaster();
            $correoSolicitante = self::obtenerCorreoSolicitante($ticket);
            if ($correoSolicitante) {
                $destinatarios[] = $correoSolicitante;
            }
            if ($ticket->asignadoA && filter_var($ticket->asignadoA->correo_tec, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = trim($ticket->asignadoA->correo_tec);
            }
            $destinatarios = array_values(array_unique($destinatarios));

            $solicitanteNombre = $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : 'Solicitante';
            $usuarioReabrio = $usuario->nombre_tecnico ?: $usuario->usuario;
            $asunto = "[SGN Tickets] REAPERTURA DE TICKET: {$ticket->codigo_ticket} - {$ticket->titulo}";
            $urlTicket = "https://novitec.com.ec/sgn/mistickets/{$ticket->id}";

            $cuerpoHtml = self::generarPlantillaHtml([
                'titulo_banner' => 'Ticket Reabierto por Solicitante',
                'color_banner' => '#dc2626',
                'icono_badge' => '',
                'mensaje_principal' => "El ticket <b>{$ticket->codigo_ticket}</b> ha sido <b>REABIERTO</b> por <b>{$usuarioReabrio}</b> ({$ticket->tienda_nombre}) debido a que el problema persiste o requiere revisión técnica urgente.",
                'codigo_ticket' => $ticket->codigo_ticket,
                'titulo_ticket' => $ticket->titulo,
                'tipo_ticket' => $ticket->tipo_ticket === 'sistemas' ? 'Sistemas / TI' : 'Soporte Técnico',
                'categoria' => $ticket->categoria,
                'prioridad' => strtoupper($ticket->prioridad),
                'estado' => 'REABIERTO / EN PROCESO',
                'tienda' => $ticket->tienda_nombre ?: 'Sucursal',
                'detalle_cuerpo' => "
                    <div style='background-color:#fef2f2; border-left:4px solid #dc2626; padding:14px; border-radius:6px;'>
                        <b style='color:#991b1b;'>Motivo de Reapertura indicado por el usuario:</b><br>
                        <p style='margin:6px 0 0 0; color:#1e293b; font-size:14px; font-weight:500;'>\"" . nl2br(e($motivo)) . "\"</p>
                    </div>
                    <div style='margin-top:12px; font-size:13px; color:#64748b;'>
                        <b>Solicitante Original:</b> {$solicitanteNombre}<br>
                        <b>Fecha de reapertura:</b> " . date('d/m/Y H:i') . "
                    </div>
                ",
                'texto_boton' => 'Gestionar Ticket en Mesa de Ayuda',
                'url_boton' => $urlTicket,
            ]);

            self::enviarEmail($destinatarios, $asunto, $cuerpoHtml);
        } catch (Throwable $e) {
            Log::error("Error al enviar email de ticket reabierto ({$ticket->codigo_ticket}): " . $e->getMessage());
        }
    }

    /**
     * Notificación cuando se CIERRA un ticket (Enviado al solicitante, técnico y Administradores Master).
     */
    public static function enviarTicketCerrado(Ticket $ticket, ?int $calificacion = null, ?string $comentario = null): void
    {
        try {
            $destinatarios = self::obtenerCorreosAdminMaster();
            $correoSolicitante = self::obtenerCorreoSolicitante($ticket);
            if ($correoSolicitante) {
                $destinatarios[] = $correoSolicitante;
            }
            if ($ticket->asignadoA && filter_var($ticket->asignadoA->correo_tec, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = trim($ticket->asignadoA->correo_tec);
            }
            $destinatarios = array_values(array_unique($destinatarios));

            $solicitanteNombre = $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : 'Solicitante';
            $tecnicoNombre = $ticket->asignadoA ? ($ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario) : 'Mesa de Ayuda';
            $asunto = "[SGN Tickets] Ticket Cerrado: {$ticket->codigo_ticket} - {$ticket->titulo}";
            $urlTicket = "https://novitec.com.ec/sgn/mistickets/{$ticket->id}";

            $califHtml = '';
            if ($calificacion) {
                $estrellas = str_repeat('★', $calificacion);
                $califHtml = "
                    <div style='background-color:#fffbeb; border:1px solid #fef3c7; border-left:4px solid #f59e0b; padding:12px; border-radius:6px; margin-bottom:12px;'>
                        <div style='font-size:14px; font-weight:700; color:#b45309;'>Calificación del Solicitante: {$estrellas} ({$calificacion}/5)</div>
                        " . ($comentario ? "<div style='margin-top:6px; font-size:13px; color:#451a03;'><b>Reseña:</b> \"" . e($comentario) . "\"</div>" : "") . "
                    </div>
                ";
            }

            $solucionHtml = '';
            if ($ticket->solucion) {
                $solucionHtml = "
                    <div style='background-color:#f0fdf4; border:1px solid #dcfce7; border-left:4px solid #059669; padding:12px; border-radius:6px; margin-bottom:12px;'>
                        <b style='color:#065f46;'>Solución Técnica Aplicada:</b><br>
                        <p style='margin:4px 0 0 0; color:#1e293b; font-size:13.5px;'>" . nl2br(e($ticket->solucion)) . "</p>
                    </div>
                ";
            }

            $cuerpoHtml = self::generarPlantillaHtml([
                'titulo_banner' => 'Ticket Finalizado y Cerrado',
                'color_banner' => '#334155',
                'icono_badge' => '',
                'mensaje_principal' => "El ticket <b>{$ticket->codigo_ticket}</b> de la tienda <b>{$ticket->tienda_nombre}</b> ha sido calificado y cerrado satisfactoriamente.",
                'codigo_ticket' => $ticket->codigo_ticket,
                'titulo_ticket' => $ticket->titulo,
                'tipo_ticket' => $ticket->tipo_ticket === 'sistemas' ? 'Sistemas / TI' : 'Soporte Técnico',
                'categoria' => $ticket->categoria,
                'prioridad' => strtoupper($ticket->prioridad),
                'estado' => 'CERRADO',
                'tienda' => $ticket->tienda_nombre ?: 'Sucursal',
                'detalle_cuerpo' => "
                    {$califHtml}
                    {$solucionHtml}
                    <div style='font-size:13px; color:#64748b;'>
                        <b>Solicitante:</b> {$solicitanteNombre}<br>
                        <b>Atendido por:</b> {$tecnicoNombre}<br>
                        <b>Fecha de Cierre:</b> " . date('d/m/Y H:i') . "
                    </div>
                ",
                'texto_boton' => 'Ver Historial Completo del Ticket',
                'url_boton' => $urlTicket,
            ]);

            self::enviarEmail($destinatarios, $asunto, $cuerpoHtml);
        } catch (Throwable $e) {
            Log::error("Error al enviar email de ticket cerrado ({$ticket->codigo_ticket}): " . $e->getMessage());
        }
    }

    /**
     * Notificación cuando cambia el estado del ticket (Resuelto, En Espera, Cancelado, etc.).
     */
    public static function enviarEstadoCambiado(Ticket $ticket, string $nuevoEstado, ?string $motivo = null, ?string $solucion = null): void
    {
        try {
            if ($nuevoEstado === 'cerrado') {
                self::enviarTicketCerrado($ticket, $ticket->calificacion, $ticket->comentario_calificacion);
                return;
            }

            $destinatarios = self::obtenerCorreosAdminMaster();
            $correoSolicitante = self::obtenerCorreoSolicitante($ticket);
            if ($correoSolicitante) {
                $destinatarios[] = $correoSolicitante;
            }
            if ($ticket->asignadoA && filter_var($ticket->asignadoA->correo_tec, FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = trim($ticket->asignadoA->correo_tec);
            }
            $destinatarios = array_values(array_unique($destinatarios));

            if (empty($destinatarios)) return;

            $solicitanteNombre = $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : 'Solicitante';
            $urlTicket = "https://novitec.com.ec/sgn/mistickets/{$ticket->id}";

            $configEstado = match ($nuevoEstado) {
                'resuelto' => [
                    'banner' => '¡Tu Ticket ha sido Resuelto!',
                    'color' => '#059669',
                    'icono' => '',
                    'asunto' => "[SGN Tickets] Ticket Resuelto: {$ticket->codigo_ticket} - {$ticket->titulo}",
                    'msj' => "Hola <b>{$solicitanteNombre}</b>, el equipo de soporte ha concluido y resuelto tu requerimiento satisfactoriamente.",
                    'btn' => 'Calificar Atención y Ver Solución'
                ],
                'en_espera' => [
                    'banner' => 'Ticket en Espera de Información',
                    'color' => '#d97706',
                    'icono' => '⏳',
                    'asunto' => "[SGN Tickets] Ticket en Espera: {$ticket->codigo_ticket}",
                    'msj' => "Hola <b>{$solicitanteNombre}</b>, tu ticket está temporalmente en espera de información o validación adicional.",
                    'btn' => 'Ver Detalle del Ticket'
                ],
                'en_proceso' => [
                    'banner' => 'Ticket en Proceso de Atención',
                    'color' => '#2563eb',
                    'icono' => '',
                    'asunto' => "[SGN Tickets] Ticket en Proceso: {$ticket->codigo_ticket}",
                    'msj' => "Hola <b>{$solicitanteNombre}</b>, tu ticket está siendo atendido activamente por nuestro equipo.",
                    'btn' => 'Seguimiento del Ticket'
                ],
                'en_mba' => [
                    'banner' => 'Ticket Escalado a Soporte Oficial MBA (Máx 48h)',
                    'color' => '#9333ea',
                    'icono' => '',
                    'asunto' => "[SGN Tickets] En Manos de MBA" . ($ticket->numero_ticket_mba ? " (Caso #{$ticket->numero_ticket_mba})" : "") . ": {$ticket->codigo_ticket} - {$ticket->titulo}",
                    'msj' => "Hola <b>{$solicitanteNombre}</b>, debido a la complejidad del caso, tu requerimiento ha sido escalado al equipo de soporte de <b>MBA3</b>" . ($ticket->numero_ticket_mba ? " bajo el N° de caso <b>#{$ticket->numero_ticket_mba}</b>" : "") . " (plazo estimado de resolución de hasta 48 horas).",
                    'btn' => 'Ver Seguimiento del Ticket'
                ],
                'cancelado' => [
                    'banner' => 'Ticket Cancelado',
                    'color' => '#dc2626',
                    'icono' => '',
                    'asunto' => "[SGN Tickets] Ticket Cancelado: {$ticket->codigo_ticket}",
                    'msj' => "Hola <b>{$solicitanteNombre}</b>, tu ticket ha sido cancelado.",
                    'btn' => 'Ver Motivo de Cancelación'
                ],
                default => [
                    'banner' => 'Actualización de Estado de Ticket',
                    'color' => '#475569',
                    'icono' => '',
                    'asunto' => "[SGN Tickets] Estado Actualizado: {$ticket->codigo_ticket}",
                    'msj' => "Hola <b>{$solicitanteNombre}</b>, se ha actualizado el estado de tu ticket a <b>" . strtoupper($nuevoEstado) . "</b>.",
                    'btn' => 'Ver Ticket'
                ]
            };

            $detalleExtra = '';
            if ($solucion) {
                $detalleExtra .= "<div style='background-color:#f0fdf4; border-left:4px solid #059669; padding:12px; border-radius:6px; margin-top:10px;'><b>Solución Registrada por Soporte:</b><br>" . nl2br(e($solucion)) . "</div>";
            }
            if ($motivo) {
                $detalleExtra .= "<div style='background-color:#f8fafc; border-left:4px solid #94a3b8; padding:12px; border-radius:6px; margin-top:10px;'><b>Observación / Motivo:</b><br>" . nl2br(e($motivo)) . "</div>";
            }

            $cuerpoHtml = self::generarPlantillaHtml([
                'titulo_banner' => $configEstado['banner'],
                'color_banner' => $configEstado['color'],
                'icono_badge' => $configEstado['icono'],
                'mensaje_principal' => $configEstado['msj'],
                'codigo_ticket' => $ticket->codigo_ticket,
                'titulo_ticket' => $ticket->titulo,
                'tipo_ticket' => $ticket->tipo_ticket === 'sistemas' ? 'Sistemas / TI' : 'Soporte Técnico',
                'categoria' => $ticket->categoria,
                'prioridad' => strtoupper($ticket->prioridad),
                'estado' => strtoupper(str_replace('_', ' ', $nuevoEstado)),
                'tienda' => $ticket->tienda_nombre ?: 'Sucursal',
                'detalle_cuerpo' => $detalleExtra ?: 'Puedes ingresar al sistema para ver los detalles.',
                'texto_boton' => $configEstado['btn'],
                'url_boton' => $urlTicket,
            ]);

            self::enviarEmail($destinatarios, $configEstado['asunto'], $cuerpoHtml);
        } catch (Throwable $e) {
            Log::error("Error al enviar email de cambio de estado ({$ticket->codigo_ticket}): " . $e->getMessage());
        }
    }

    /**
     * Notificación con credenciales de acceso cuando se crea o resetea un usuario solicitante.
     */
    public static function enviarCredencialesSolicitante(Usuario $usuario, string $clavePlana): void
    {
        try {
            $destinatario = trim((string) ($usuario->correo_tec ?: ''));
            if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                Log::info("Usuario {$usuario->usuario}: sin correo electrónico válido para enviar credenciales.");
                return;
            }

            $usuario->loadMissing('sucursalCliente');
            $tiendaNombre = $usuario->sucursalCliente ? ($usuario->sucursalCliente->codigo . ' - ' . $usuario->sucursalCliente->nombre) : 'Tienda / Sucursal Asignada';
            $nombre = $usuario->nombre_tecnico ?: $usuario->usuario;
            $asunto = "[SGN] Tus Credenciales de Acceso a Mesa de Ayuda y Tickets - {$nombre}";
            $urlLogin = 'https://novitec.com.ec/sgn';

            $cuerpoHtml = self::generarPlantillaCredencialesHtml([
                'nombre' => $nombre,
                'usuario' => $usuario->usuario,
                'clave' => $clavePlana,
                'tienda' => $tiendaNombre,
                'departamento' => $usuario->departamento ?: 'General / Tienda',
                'empresa' => $usuario->empresa_origen ?: 'NOVICOMPU',
                'url_login' => $urlLogin,
            ]);

            self::enviarEmail($destinatario, $asunto, $cuerpoHtml);
        } catch (Throwable $e) {
            Log::error("Error al enviar credenciales a {$usuario->usuario}: " . $e->getMessage());
        }
    }

    /**
     * Generador de plantilla HTML para credenciales de usuarios solicitantes.
     */
    private static function generarPlantillaCredencialesHtml(array $d): string
    {
        $nombre = $d['nombre'] ?? 'Usuario';
        $usuario = $d['usuario'] ?? '';
        $clave = $d['clave'] ?? '';
        $tienda = $d['tienda'] ?? 'Sucursal Asignada';
        $departamento = $d['departamento'] ?? 'General';
        $empresa = $d['empresa'] ?? 'NOVICOMPU';
        $urlLogin = $d['url_login'] ?? 'https://novitec.com.ec/sgn';

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Tus Credenciales de Acceso SGN</title>
        </head>
        <body style='margin:0; padding:0; background-color:#f1f5f9; font-family:-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color:#f1f5f9; padding:30px 10px;'>
                <tr>
                    <td align='center'>
                        <table role='presentation' width='100%' style='max-width:620px; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.06); border:1px solid #e2e8f0;' cellspacing='0' cellpadding='0'>
                            <!-- Cabecera -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding:28px 30px; text-align:center;'>
                                    
                                    <h1 style='margin:0; color:#ffffff; font-size:22px; font-weight:800; letter-spacing:-0.3px;'>Bienvenido a la Mesa de Ayuda SGN</h1>
                                    <div style='color:rgba(255,255,255,0.9); font-size:13px; margin-top:4px;'>Credenciales de Acceso para Generación de Tickets</div>
                                </td>
                            </tr>

                            <!-- Cuerpo -->
                            <tr>
                                <td style='padding:32px 30px;'>
                                    <p style='margin:0 0 16px 0; color:#1e293b; font-size:16px; font-weight:700;'>
                                        Hola {$nombre},
                                    </p>
                                    <p style='margin:0 0 24px 0; color:#475569; font-size:14px; line-height:1.6;'>
                                        Tu cuenta institucional ha sido registrada en el <b>Sistema de Gestión Novitec (SGN)</b>. A partir de ahora podrás ingresar para registrar solicitudes de soporte técnico, requerimientos de sistemas (MBA3 / Millenium / Correos) y dar seguimiento en tiempo real con nuestro equipo.
                                    </p>

                                    <!-- Tarjeta Destacada de Credenciales -->
                                    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color:#f8fafc; border:2px solid #3b82f6; border-radius:12px; padding:20px; margin-bottom:24px;'>
                                        <tr>
                                            <td colspan='2' style='padding-bottom:14px; border-bottom:1px dashed #cbd5e1;'>
                                                <div style='color:#1e40af; font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;'>
                                                    Tus Credenciales de Inicio de Sesión
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='padding-top:14px; padding-bottom:8px; width:45%;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Usuario / Cédula:</div>
                                                <div style='color:#0f172a; font-size:16px; font-weight:800; font-family:monospace;'>{$usuario}</div>
                                            </td>
                                            <td style='padding-top:14px; padding-bottom:8px; width:55%;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Contraseña Asignada:</div>
                                                <div style='color:#2563eb; font-size:16px; font-weight:800; font-family:monospace; background:#e0e7ff; padding:3px 10px; border-radius:4px; display:inline-block;'>{$clave}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='padding-top:10px;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Tienda / Punto de Venta:</div>
                                                <div style='color:#1e293b; font-size:13px; font-weight:600;'>{$tienda}</div>
                                            </td>
                                            <td style='padding-top:10px;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Departamento / Área:</div>
                                                <div style='color:#1e293b; font-size:13px; font-weight:600;'>{$departamento} <span style='color:#64748b; font-size:11px;'>({$empresa})</span></div>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Botón de Acción -->
                                    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='margin-bottom:26px;'>
                                        <tr>
                                            <td align='center'>
                                                <a href='{$urlLogin}' target='_blank' style='display:inline-block; background-color:#2563eb; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:14px 34px; border-radius:8px; box-shadow:0 4px 12px rgba(37,99,235,0.25);'>
                                                    Ingresar al Portal SGN &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Recomendaciones de Seguridad -->
                                    <div style='background-color:#eff6ff; border-left:4px solid #3b82f6; padding:12px 16px; border-radius:4px; font-size:12.5px; color:#1e40af; line-height:1.5;'>
                                        <b>Recomendaciones:</b>
                                        <ul style='margin:6px 0 0 0; padding-left:18px;'>
                                            <li>Guarda tus credenciales en un lugar seguro.</li>
                                            <li>Al ingresar por primera vez, puedes completar tus datos de conexión (AnyDesk y MBA) en la sección <b>Mis Datos de Soporte</b>.</li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Pie de página -->
                            <tr>
                                <td style='background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 30px; text-align:center; color:#94a3b8; font-size:12px; line-height:1.5;'>
                                    Sistema de Gestión Novitec (SGN) · Mesa de Ayuda Quito & Soporte Nacional<br>
                                    Este es un correo automático confidencial para el usuario registrado.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    /**
     * Envía el correo mediante Symfony / Laravel Mailer (soporta 1 destinatario o array de destinatarios).
     */
    private static function enviarEmail(string|array $destinatarios, string $asunto, string $html): void
    {
        $destinatarios = (array) $destinatarios;
        $destValidos = array_values(array_filter($destinatarios, fn($d) => filter_var(trim((string)$d), FILTER_VALIDATE_EMAIL)));
        if (empty($destValidos)) {
            return;
        }

        Mail::send([], [], function ($message) use ($destValidos, $asunto, $html) {
            $message->to($destValidos)
                ->subject($asunto)
                ->html($html);
        });

        Log::info("Notificación de ticket enviada a: " . implode(', ', $destValidos) . " - Asunto: {$asunto}");
    }

    /**
     * Generador de plantilla HTML responsiva y moderna para emails de tickets.
     */
    private static function generarPlantillaHtml(array $d): string
    {
        $color = $d['color_banner'] ?? '#2563eb';
        $icono = $d['icono_badge'] ?? '';
        $tituloBanner = $d['titulo_banner'] ?? 'Notificación de Ticket';
        $msjPrincipal = $d['mensaje_principal'] ?? '';
        $codigo = $d['codigo_ticket'] ?? 'TK';
        $titulo = $d['titulo_ticket'] ?? '';
        $tipo = $d['tipo_ticket'] ?? '';
        $categoria = $d['categoria'] ?? '';
        $prioridad = $d['prioridad'] ?? '';
        $estado = $d['estado'] ?? '';
        $tienda = $d['tienda'] ?? '';
        $detalleCuerpo = $d['detalle_cuerpo'] ?? '';
        $textoBoton = $d['texto_boton'] ?? 'Ver Ticket';
        $urlBoton = $d['url_boton'] ?? '#';
        $asuntoTitulo = $tituloBanner;

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$asuntoTitulo}</title>
        </head>
        <body style='margin:0; padding:0; background-color:#f1f5f9; font-family:-apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color:#f1f5f9; padding:30px 10px;'>
                <tr>
                    <td align='center'>
                        <table role='presentation' width='100%' style='max-width:620px; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.06); border:1px solid #e2e8f0;' cellspacing='0' cellpadding='0'>
                            <!-- Cabecera -->
                            <tr>
                                <td style='background-color:{$color}; padding:26px 30px; text-align:center;'>
                                    <div style='font-size:32px; margin-bottom:6px;'>{$icono}</div>
                                    <h1 style='margin:0; color:#ffffff; font-size:20px; font-weight:700; letter-spacing:-0.3px;'>{$tituloBanner}</h1>
                                    <div style='color:rgba(255,255,255,0.85); font-size:13px; margin-top:4px;'>Mesa de Ayuda · Novitec SGN</div>
                                </td>
                            </tr>

                            <!-- Cuerpo -->
                            <tr>
                                <td style='padding:30px;'>
                                    <p style='margin:0 0 20px 0; color:#334155; font-size:15px; line-height:1.6;'>
                                        {$msjPrincipal}
                                    </p>

                                    <!-- Tarjeta de Datos del Ticket -->
                                    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px; margin-bottom:24px;'>
                                        <tr>
                                            <td style='padding-bottom:10px;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Código del Ticket:</div>
                                                <div style='color:{$color}; font-size:17px; font-weight:800; font-family:monospace;'>{$codigo}</div>
                                            </td>
                                            <td style='padding-bottom:10px;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Estado Actual:</div>
                                                <div style='color:#0f172a; font-size:13px; font-weight:700;'>{$estado}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='padding-bottom:10px;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Título / Asunto:</div>
                                                <div style='color:#1e293b; font-size:13px; font-weight:600;'>{$titulo}</div>
                                            </td>
                                            <td style='padding-bottom:10px;'>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Categoría:</div>
                                                <div style='color:#1e293b; font-size:13px;'>{$categoria}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Tienda / Sucursal:</div>
                                                <div style='color:#1e293b; font-size:13px;'>{$tienda}</div>
                                            </td>
                                            <td>
                                                <div style='color:#64748b; font-size:11px; text-transform:uppercase; font-weight:700;'>Prioridad:</div>
                                                <div style='color:#1e293b; font-size:13px; font-weight:600;'>{$prioridad}</div>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Detalle o Contenido Extra -->
                                    <div style='color:#334155; font-size:14px; line-height:1.6; margin-bottom:28px;'>
                                        {$detalleCuerpo}
                                    </div>

                                    <!-- Botón de Acción -->
                                    <table role='presentation' width='100%' cellspacing='0' cellpadding='0'>
                                        <tr>
                                            <td align='center'>
                                                <a href='{$urlBoton}' target='_blank' style='display:inline-block; background-color:{$color}; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:13px 32px; border-radius:8px; box-shadow:0 3px 8px rgba(0,0,0,0.12);'>
                                                    {$textoBoton} &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Pie de página -->
                            <tr>
                                <td style='background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 30px; text-align:center; color:#94a3b8; font-size:12px; line-height:1.5;'>
                                    Este es un mensaje automático generado por el Sistema de Gestión Novitec (SGN).<br>
                                    Para responder o consultar el estado de tu ticket, ingresa a través del enlace superior.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }
}
