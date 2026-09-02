<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResuelveRolesTicket;
use App\Models\Identity\Usuario;
use App\Services\Operations\AuditLogger;
use App\Services\Operations\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthApiController extends Controller
{
    use ResuelveRolesTicket;

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'usuario' => 'required|string',
            'clave' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $usuarioInput = trim((string) $request->input('usuario'));
        $claveInput = (string) $request->input('clave');

        $usuario = Usuario::with(['sucursalCliente', 'rol', 'grupo'])
            ->where('usuario', $usuarioInput)
            ->first();

        if (!$usuario || !$usuario->validarClave($claveInput)) {
            AuditLogger::registrar('LOGIN_MOVIL_FALLIDO', 'auth', null, 'Login móvil fallido para usuario: ' . $usuarioInput);
            return response()->json([
                'ok' => false,
                'error' => 'Credenciales incorrectas. Verifica tu usuario y contraseña.'
            ], 401);
        }

        if ((int) ($usuario->activo ?? 1) === 0) {
            AuditLogger::registrar('LOGIN_MOVIL_FALLIDO', 'auth', (string)$usuario->id, 'Usuario inactivo intentó acceder por móvil: ' . $usuarioInput);
            return response()->json([
                'ok' => false,
                'error' => 'Tu usuario se encuentra inactivo. Contacta a Soporte Quito.'
            ], 403);
        }

        if ($usuario->usaClaveLegacy()) {
            $usuario->establecerClaveSegura($claveInput);
            $usuario->save();
        }

        $payload = [
            'user_id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'device' => $request->input('device_name', 'Android App'),
            'created_at' => now()->toIso8601String(),
        ];
        $token = Crypt::encryptString(json_encode($payload));

        AuditLogger::registrar('LOGIN_MOVIL', 'auth', (string)$usuario->id, 'Sesión iniciada desde App Android');

        $esTecnicoSistemas = $this->esTecnicoSistemas($usuario);
        $esAdmin = $this->esAdminTickets($usuario);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Inicio de sesión exitoso.',
            'token' => $token,
            'user' => [
                'id' => $usuario->id,
                'usuario' => $usuario->usuario,
                'nombre' => $usuario->nombre_tecnico ?: $usuario->usuario,
                'correo' => $usuario->correo_tec,
                'telefono' => $usuario->telefono,
                'departamento' => $usuario->departamento,
                'empresa_origen' => $usuario->empresa_origen ?? 'NOVICOMPU',
                'anydesk_id' => $usuario->anydesk_id,
                'usuario_mba' => $usuario->usuario_mba,
                'codigo_usuario' => $usuario->codigo_usuario,
                'es_tecnico_sistemas' => $esTecnicoSistemas,
                'es_admin' => $esAdmin,
                'tienda' => $usuario->sucursalCliente ? [
                    'id' => $usuario->sucursalCliente->id,
                    'codigo' => $usuario->sucursalCliente->codigo,
                    'nombre' => $usuario->sucursalCliente->nombre,
                    'provincia' => $usuario->sucursalCliente->provincia,
                ] : null,
            ]
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $usuario->load(['sucursalCliente', 'rol', 'grupo']);

        $esTecnicoSistemas = $this->esTecnicoSistemas($usuario);
        $esAdmin = $this->esAdminTickets($usuario);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $usuario->id,
                'usuario' => $usuario->usuario,
                'nombre' => $usuario->nombre_tecnico ?: $usuario->usuario,
                'correo' => $usuario->correo_tec,
                'telefono' => $usuario->telefono,
                'departamento' => $usuario->departamento,
                'empresa_origen' => $usuario->empresa_origen ?? 'NOVICOMPU',
                'anydesk_id' => $usuario->anydesk_id,
                'usuario_mba' => $usuario->usuario_mba,
                'codigo_usuario' => $usuario->codigo_usuario,
                'es_tecnico_sistemas' => $esTecnicoSistemas,
                'es_admin' => $esAdmin,
                'tienda' => $usuario->sucursalCliente ? [
                    'id' => $usuario->sucursalCliente->id,
                    'codigo' => $usuario->sucursalCliente->codigo,
                    'nombre' => $usuario->sucursalCliente->nombre,
                    'provincia' => $usuario->sucursalCliente->provincia,
                ] : null,
            ]
        ]);
    }

    public function registerFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $usuario = $request->user();
        Log::info("FCM Token registrado para usuario #{$usuario->id} ({$usuario->usuario}): " . substr($request->input('fcm_token'), 0, 20) . '...');

        return response()->json([
            'ok' => true,
            'mensaje' => 'Dispositivo registrado para notificaciones push.'
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $usuario = $request->user();
        if ($usuario) {
            AuditLogger::registrar('LOGOUT_MOVIL', 'auth', (string)$usuario->id, 'Sesión cerrada desde App Android');
        }

        return response()->json([
            'ok' => true,
            'mensaje' => 'Sesión cerrada correctamente.'
        ]);
    }
}
