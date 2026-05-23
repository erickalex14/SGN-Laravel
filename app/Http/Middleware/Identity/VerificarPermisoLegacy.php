<?php

namespace App\Http\Middleware\Identity;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermisoLegacy
{

     //Verifica si el usuario autenticado tiene el permiso requerido en la sesion legacy.

    public function handle(Request $request, Closure $next, string $modulo, string $accion = 'ver'): Response
    {
        // El superadmin siempre tiene acceso absoluto
        if (session('es_superadmin') === true) {
            return $next($request);
        }

        $permisos = session('permisos', []);

        // Validar la existencia del permiso especifico en el array multidimensional
        $tienePermiso = isset($permisos[$modulo][$accion]) && $permisos[$modulo][$accion] === true;

        if (!$tienePermiso) {
            Log::warning('Acceso denegado por falta de permisos.', [
                'usuario' => session('usuario'),
                'modulo'  => $modulo,
                'accion'  => $accion,
                'url'     => $request->fullUrl()
            ]);

            // Comportamiento identico al sistema original: redireccionar con error o mostrar vista de acceso denegado
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Acceso denegado'], 403);
            }

            abort(403, 'No tienes permisos para acceder a este modulo.');
        }

        return $next($request);
    }
}
