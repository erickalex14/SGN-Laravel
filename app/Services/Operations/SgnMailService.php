<?php

namespace App\Services\Operations;

use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Operations\SolicitudRepuesto;
use App\Models\Operations\SolicitudNc;
use App\Models\Identity\Usuario;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SgnMailService
{
    /**
     * Obtiene los correos de administradores de la sucursal especificada,
     * más todos los administradores master del sistema, de manera única.
     */
    public static function obtenerCorreosNotificacionAdmins(int $sucursalId): array
    {
        return Usuario::query()
            ->where(function ($q) use ($sucursalId) {
                // Admin de la sucursal (columna principal o en la relación de asignadas)
                $q->where(function ($q2) use ($sucursalId) {
                    $q2->where(function ($qSub) use ($sucursalId) {
                        $qSub->where('sucursal_id', $sucursalId)
                             ->orWhereHas('sucursalesAsignadas', fn($s) => $s->where('sucursales.id', $sucursalId));
                    })
                    ->where(function ($q3) {
                        $q3->whereHas('rol', fn($r) => $r->whereIn('rol', ['admin', 'administrador', 'ADMIN', 'ADMINISTRADOR', 'Admin', 'Administrador']))
                           ->orWhereHas('grupo', fn($g) => $g->whereIn('nombre', ['admin', 'administrador', 'ADMIN', 'ADMINISTRADOR', 'Admin', 'Administrador']));
                    });
                })
                // O Admin Master / Superadministrador de cualquier sucursal (reciben de todas)
                ->orWhere(function ($q2) {
                    $q2->whereHas('rol', fn($r) => $r->whereIn('rol', [
                        'admin master', 'administrador master', 'ADMIN MASTER', 'ADMINISTRADOR MASTER', 'Admin Master', 'Administrador Master',
                        'superadmin', 'superadministrador', 'SUPERADMIN', 'SUPERADMINISTRADOR', 'Superadmin', 'Superadministrador'
                    ]))
                    ->orWhereHas('grupo', fn($g) => $g->whereIn('nombre', [
                        'admin master', 'administrador master', 'ADMIN MASTER', 'ADMINISTRADOR MASTER', 'Admin Master', 'Administrador Master',
                        'superadmin', 'superadministrador', 'SUPERADMIN', 'SUPERADMINISTRADOR', 'Superadmin', 'Superadministrador'
                    ]))
                    ->orWhereHas('grupo', fn($g) => $g->where('es_superadmin', 1));
                });
            })
            ->where('activo', 1)
            ->whereNotNull('correo_tec')
            ->where('correo_tec', '!=', '')
            ->pluck('correo_tec')
            ->unique()
            ->all();
    }

    /**
     * Envía una notificación cuando se crea una nueva orden (Personal o Corporativa)
     */
    public static function enviarOrdenCreada($orden): void
    {
        try {
            $esEmpresa = $orden instanceof OrdenEmpresa;
            
            // Obtener sucursal
            $sucursalId = (int) $orden->sucursal_id;
            $nombreSucursal = $orden->sucursal->nombre ?? $orden->sucursal->ciudad ?? 'Sucursal ' . $sucursalId;

            // Obtener correos de destinatarios
            $destinatarios = self::obtenerCorreosNotificacionAdmins($sucursalId);
            if (empty($destinatarios)) {
                Log::warning('No se encontraron correos de administradores para notificar nueva orden', ['orden_id' => $orden->id]);
                return;
            }

            // Datos comunes
            $nroOrden = $orden->nro_orden;
            $tipoOrden = $esEmpresa ? 'Corporativa (' . ($orden->subtipo ?? 'Empresa') . ')' : 'Personal';
            
            // Técnico asignado
            if ($esEmpresa && $orden->subtipo === 'Servicios') {
                $nombreTecnico = $orden->tecnicos->isNotEmpty() 
                    ? $orden->tecnicos->pluck('nombre_tecnico')->implode(', ') 
                    : ($orden->tecnico->nombre_tecnico ?? 'Sin asignar');
            } else {
                $nombreTecnico = $orden->tecnico->nombre_tecnico ?? 'Sin asignar';
            }

            // Cliente y Equipo
            if ($esEmpresa) {
                $nombreCliente = $orden->empresa->nombre ?? 'Empresa';
                $identificacion = $orden->empresa->ruc ?? '-';
                $equipoStr = $orden->subtipo === 'Servicios' ? 'Servicio Corporativo' : trim(($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? ''));
                $serie = $orden->subtipo === 'Servicios' ? '-' : ($orden->equipo->serie ?? '-');
                $falla = $orden->descripcion ?? ($orden->equipo->falla ?? '-');
            } else {
                $nombreCliente = trim(($orden->cliente->nombres ?? '') . ' ' . ($orden->cliente->apellidos ?? ''));
                $identificacion = $orden->cliente->identificacion ?? '-';
                $equipoStr = trim(($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? ''));
                $serie = $orden->equipo->serie ?? '-';
                $falla = $orden->equipo->falla ?? '-';
            }

            $usuarioCreador = $esEmpresa 
                ? ($orden->ingresadoPor->nombre_tecnico ?? $orden->ingresadoPor->usuario ?? 'Sistema')
                : ($orden->usuarioIngreso->nombre_tecnico ?? $orden->usuarioIngreso->usuario ?? 'Sistema');

            $asunto = "[SGN] Nueva Orden de Trabajo Creada - " . $nroOrden;
            
            $cuerpo = view('emails.orden_creada', [
                'nro_orden' => $nroOrden,
                'tipo_orden' => $tipoOrden,
                'nombre_cliente' => $nombreCliente,
                'identificacion' => $identificacion,
                'equipo' => $equipoStr,
                'serie' => $serie,
                'falla' => $falla,
                'tecnico' => $nombreTecnico,
                'sucursal' => $nombreSucursal,
                'creador' => $usuarioCreador,
                'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($destinatarios, $asunto) {
                $message->to($destinatarios)->subject($asunto);
            });

            Log::info('Correo de nueva orden enviado correctamente.', ['nro_orden' => $nroOrden]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de nueva orden', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envía una notificación cuando se crea una nueva solicitud de repuesto
     */
    public static function enviarSolicitudRepuestoCreada(SolicitudRepuesto $solicitud): void
    {
        try {
            $orden = $solicitud->orden ?: $solicitud->ordenEmpresa;
            if (!$orden) {
                return;
            }
            $sucursalId = (int) $orden->sucursal_id;
            $nombreSucursal = $orden->sucursal->nombre ?? $orden->sucursal->ciudad ?? 'Sucursal ' . $sucursalId;

            $destinatarios = self::obtenerCorreosNotificacionAdmins($sucursalId);
            if (empty($destinatarios)) {
                return;
            }

            $asunto = "[SGN] Nueva Solicitud de Repuesto - " . $solicitud->nro_solicitud;

            $cuerpo = view('emails.solicitud_repuesto', [
                'nro_solicitud' => $solicitud->nro_solicitud,
                'nro_orden' => $orden->nro_orden,
                'repuesto_nombre' => $solicitud->repuesto_nombre,
                'cantidad' => $solicitud->cantidad,
                'nro_parte' => $solicitud->nro_parte ?: 'No especificado',
                'link_compra' => $solicitud->link_compra ?: 'No especificado',
                'descripcion' => $solicitud->descripcion,
                'tecnico' => $solicitud->tecnico_nombre,
                'sucursal' => $nombreSucursal,
                'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($destinatarios, $asunto) {
                $message->to($destinatarios)->subject($asunto);
            });

            Log::info('Correo de nueva solicitud de repuesto enviado.', ['nro_solicitud' => $solicitud->nro_solicitud]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de nueva solicitud de repuesto', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envía una notificación cuando se crea una nueva solicitud de Nota de Crédito
     */
    public static function enviarSolicitudNcCreada(SolicitudNc $solicitud): void
    {
        try {
            $orden = $solicitud->orden;
            if (!$orden) {
                return;
            }
            $sucursalId = (int) $orden->sucursal_id;
            $nombreSucursal = $orden->sucursal->nombre ?? $orden->sucursal->ciudad ?? 'Sucursal ' . $sucursalId;

            $destinatarios = self::obtenerCorreosNotificacionAdmins($sucursalId);
            if (empty($destinatarios)) {
                return;
            }

            $asunto = "[SGN] Nueva Solicitud de Nota de Crédito - " . $solicitud->nro_solicitud;

            $cuerpo = view('emails.solicitud_nc', [
                'nro_solicitud' => $solicitud->nro_solicitud,
                'nro_orden' => $orden->nro_orden,
                'asunto_solicitud' => $solicitud->asunto,
                'detalles' => $solicitud->detalles,
                'tecnico' => $solicitud->tecnico_nombre,
                'sucursal' => $nombreSucursal,
                'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($destinatarios, $asunto) {
                $message->to($destinatarios)->subject($asunto);
            });

            Log::info('Correo de nueva solicitud de NC enviado.', ['nro_solicitud' => $solicitud->nro_solicitud]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de nueva solicitud de NC', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envía una notificación al técnico cuando su solicitud de repuesto es aprobada/rechazada/compras
     */
    public static function enviarSolicitudRepuestoGestionada(SolicitudRepuesto $solicitud): void
    {
        try {
            $tecnico = Usuario::find($solicitud->tecnico_id);
            if (!$tecnico || empty($tecnico->correo_tec)) {
                Log::warning('No se pudo notificar al técnico, no tiene correo configurado', ['tecnico_id' => $solicitud->tecnico_id]);
                return;
            }

            $destinatario = $tecnico->correo_tec;
            $estado = $solicitud->estado;

            $asunto = "[SGN] Solicitud de Repuesto " . $solicitud->nro_solicitud . " - " . strtoupper($estado);

            $cuerpo = view('emails.solicitud_repuesto_gestionada', [
                'nro_solicitud' => $solicitud->nro_solicitud,
                'repuesto_nombre' => $solicitud->repuesto_nombre,
                'estado' => $estado,
                'motivo_rechazo' => $solicitud->motivo_rechazo ?: null,
                'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($destinatario, $asunto) {
                $message->to($destinatario)->subject($asunto);
            });

            Log::info('Correo de gestión de repuesto enviado al técnico.', ['nro_solicitud' => $solicitud->nro_solicitud]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de gestión de repuesto', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envía una notificación al técnico cuando su solicitud de nota de crédito es aprobada/rechazada
     */
    public static function enviarSolicitudNcGestionada(SolicitudNc $solicitud): void
    {
        try {
            $tecnico = Usuario::find($solicitud->tecnico_id);
            if (!$tecnico || empty($tecnico->correo_tec)) {
                Log::warning('No se pudo notificar al técnico, no tiene correo configurado', ['tecnico_id' => $solicitud->tecnico_id]);
                return;
            }

            $destinatario = $tecnico->correo_tec;
            $estado = $solicitud->estado;

            $asunto = "[SGN] Solicitud de Nota de Crédito " . $solicitud->nro_solicitud . " - " . strtoupper($estado);

            $cuerpo = view('emails.solicitud_nc_gestionada', [
                'nro_solicitud' => $solicitud->nro_solicitud,
                'estado' => $estado,
                'motivo_rechazo' => $solicitud->motivo_rechazo ?: null,
                'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($destinatario, $asunto) {
                $message->to($destinatario)->subject($asunto);
            });

            Log::info('Correo de gestión de NC enviado al técnico.', ['nro_solicitud' => $solicitud->nro_solicitud]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de gestión de NC', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envía una notificación cuando una orden cambia de estado
     */
    public static function enviarOrdenEstadoCambiado($orden, string $estadoAnterior, string $estadoNuevo): void
    {
        try {
            $esEmpresa = $orden instanceof OrdenEmpresa;
            
            // Obtener sucursal
            $sucursalId = (int) $orden->sucursal_id;
            $nombreSucursal = $orden->sucursal->nombre ?? $orden->sucursal->ciudad ?? 'Sucursal ' . $sucursalId;

            // Obtener correos de destinatarios
            $destinatarios = self::obtenerCorreosNotificacionAdmins($sucursalId);
            if (empty($destinatarios)) {
                Log::warning('No se encontraron correos de administradores para notificar cambio de estado', ['orden_id' => $orden->id]);
                return;
            }

            // Datos comunes
            $nroOrden = $orden->nro_orden;
            $tipoOrden = $esEmpresa ? 'Corporativa (' . ($orden->subtipo ?? 'Empresa') . ')' : 'Personal';
            
            // Técnico asignado
            if ($esEmpresa && $orden->subtipo === 'Servicios') {
                $nombreTecnico = $orden->tecnicos->isNotEmpty() 
                    ? $orden->tecnicos->pluck('nombre_tecnico')->implode(', ') 
                    : ($orden->tecnico->nombre_tecnico ?? 'Sin asignar');
            } else {
                $nombreTecnico = $orden->tecnico->nombre_tecnico ?? 'Sin asignar';
            }

            // Cliente y Equipo
            if ($esEmpresa) {
                $nombreCliente = $orden->empresa->nombre ?? 'Empresa';
                $identificacion = $orden->empresa->ruc ?? '-';
                $equipoStr = $orden->subtipo === 'Servicios' ? 'Servicio Corporativo' : trim(($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? ''));
                $serie = $orden->subtipo === 'Servicios' ? '-' : ($orden->equipo->serie ?? '-');
            } else {
                $nombreCliente = trim(($orden->cliente->nombres ?? '') . ' ' . ($orden->cliente->apellidos ?? ''));
                $identificacion = $orden->cliente->identificacion ?? '-';
                $equipoStr = trim(($orden->equipo->tipo ?? '') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? ''));
                $serie = $orden->equipo->serie ?? '-';
            }

            $asunto = "[SGN] Estado Cambiado - Orden " . $nroOrden . " [" . $estadoNuevo . "]";
            
            $cuerpo = view('emails.orden_estado_cambiado', [
                'nro_orden' => $nroOrden,
                'tipo_orden' => $tipoOrden,
                'nombre_cliente' => $nombreCliente,
                'identificacion' => $identificacion,
                'equipo' => $equipoStr,
                'serie' => $serie,
                'tecnico' => $nombreTecnico,
                'sucursal' => $nombreSucursal,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($destinatarios, $asunto) {
                $message->to($destinatarios)->subject($asunto);
            });

            Log::info('Correo de cambio de estado de orden enviado correctamente.', [
                'nro_orden' => $nroOrden,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de cambio de estado de orden', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Envía un correo electrónico directo al cliente
     */
    public static function enviarEmailCliente($orden, string $asunto, string $contenido): void
    {
        try {
            $esEmpresa = $orden instanceof OrdenEmpresa;
            $correoCliente = $esEmpresa ? ($orden->empresa->correo ?? '') : ($orden->cliente->correo ?? '');
            
            if (empty($correoCliente)) {
                throw new \Exception('El cliente no tiene un correo electrónico registrado.');
            }

            $nroOrden = $orden->nro_orden;

            $cuerpo = view('emails.cliente_notificacion', [
                'nro_orden' => $nroOrden,
                'contenido' => nl2br(e($contenido)),
                'asunto' => $asunto,
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($correoCliente, $asunto) {
                $message->to($correoCliente)->subject($asunto);
            });

            Log::info('Correo enviado al cliente correctamente.', ['nro_orden' => $nroOrden, 'correo' => $correoCliente]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo al cliente', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Envía una notificación para recordar a los administradores el cierre de caja.
     */
    public static function enviarAlertaCierreCaja(array $destinatarios, string $nombreSucursal, string $mesNombre, int $anio): void
    {
        try {
            $asunto = "[SGN] Recordatorio: Cierre Mensual de Caja - {$nombreSucursal} [{$mesNombre} {$anio}]";
            
            $cuerpo = view('emails.alerta_cierre_caja', [
                'sucursal' => $nombreSucursal,
                'mes' => $mesNombre,
                'anio' => $anio,
                'subject' => $asunto
            ])->render();

            Mail::html($cuerpo, function ($message) use ($destinatarios, $asunto) {
                $message->to($destinatarios)->subject($asunto);
            });

            Log::info("Correo de recordatorio de cierre de caja enviado para sucursal {$nombreSucursal}.", ['destinatarios' => count($destinatarios)]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de alerta de cierre de caja', ['error' => $e->getMessage()]);
        }
    }
}
