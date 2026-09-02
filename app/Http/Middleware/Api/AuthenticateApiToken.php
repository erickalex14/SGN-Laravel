<?php

namespace App\Http\Middleware\Api;

use App\Models\Identity\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'ok' => false,
                'error' => 'Token de autenticación no proporcionado. Envía header Authorization: Bearer <token>.'
            ], 401);
        }

        try {
            $decrypted = Crypt::decryptString($token);
            $data = json_decode($decrypted, true);

            if (!isset($data['user_id']) || !isset($data['created_at'])) {
                return response()->json(['ok' => false, 'error' => 'Token inválido o corrupto.'], 401);
            }

            $usuario = Usuario::with(['sucursalCliente', 'rol', 'grupo'])->find($data['user_id']);

            if (!$usuario || (int) ($usuario->activo ?? 1) === 0) {
                return response()->json(['ok' => false, 'error' => 'Usuario inactivo o no encontrado.'], 401);
            }

            Auth::setUser($usuario);
            $request->setUserResolver(fn () => $usuario);

            return $next($request);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Token de sesión expirado o inválido.'
            ], 401);
        }
    }
}
