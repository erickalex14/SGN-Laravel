<?php

namespace App\Services\Operations;

use App\Models\Identity\Bitacora;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Registra una acción de auditoría en la tabla bitacoras.
     *
     * @param string $accion Acción realizada (ej. LOGIN, CREAR_ORDEN)
     * @param string $modulo Módulo afectado (ej. auth, ordenes, usuarios)
     * @param string|null $registroId ID del registro afectado
     * @param mixed $detalles Datos extra (array, object o string)
     */
    public static function registrar(string $accion, string $modulo, ?string $registroId = null, $detalles = null): void
    {
        try {
            $usuario = auth()->user();

            Bitacora::create([
                'usuario_id' => $usuario?->id,
                'usuario_nombre' => $usuario?->nombre_tecnico ?? $usuario?->usuario ?? 'Invitado/Sistema',
                'accion' => strtoupper(trim($accion)),
                'modulo' => strtolower(trim($modulo)),
                'registro_id' => $registroId,
                'detalles' => is_array($detalles) || is_object($detalles) ? json_encode($detalles) : $detalles,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Prevenir que un fallo al escribir la bitácora rompa el flujo principal del usuario,
            // pero loguear el error internamente.
            \Illuminate\Support\Facades\Log::error('Fallo al registrar bitácora de auditoría: ' . $e->getMessage(), [
                'accion' => $accion,
                'modulo' => $modulo,
                'registroId' => $registroId,
            ]);
        }
    }
}
