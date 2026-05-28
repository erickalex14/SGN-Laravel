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
        // El superadmin siempre tiene acceso absoluto (tolerante a 1/"1"/true)
        if ($this->toBool(session('es_superadmin', false))) {
            return $next($request);
        }

        if ($this->puedeBuscarInformesPorRol($request, $modulo, $accion)) {
            return $next($request);
        }

        if ($this->puedeAccederRutasTecnicoInformes($request, $modulo, $accion)) {
            return $next($request);
        }

        $permisos = (array) session('permisos', []);
        $tienePermiso = $this->tienePermiso($permisos, $modulo, $accion);

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

    private function tienePermiso(array $permisos, string $modulo, string $accion): bool
    {
        $moduloNorm = $this->norm($modulo);
        $accionNorm = $this->norm($accion);

        // Alias de compatibilidad legacy entre módulos equivalentes
        $aliases = [
            'inv_repuestos' => ['repuestos_admin'],
            'repuestos_admin' => ['inv_repuestos'],
        ];

        $modulosRevisar = [$moduloNorm];
        foreach ($aliases[$moduloNorm] ?? [] as $alias) {
            $modulosRevisar[] = $this->norm($alias);
        }
        $modulosRevisar = array_values(array_unique($modulosRevisar));

        $accionesRevisar = [$accionNorm];
        // En el flujo legacy, "editar" suele implicar gestión completa del módulo.
        if ($accionNorm === 'crear') {
            $accionesRevisar[] = 'editar';
        }
        $accionesRevisar = array_values(array_unique($accionesRevisar));

        foreach ($permisos as $moduloKey => $acciones) {
            if (!is_array($acciones)) {
                continue;
            }
            if (!in_array($this->norm((string) $moduloKey), $modulosRevisar, true)) {
                continue;
            }

            foreach ($acciones as $accionKey => $permitido) {
                if (
                    in_array($this->norm((string) $accionKey), $accionesRevisar, true)
                    && $this->toBool($permitido)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function puedeBuscarInformesPorRol(Request $request, string $modulo, string $accion): bool
    {
        if ($this->norm($modulo) !== 'informes' || $this->norm($accion) !== 'ver') {
            return false;
        }

        $ruta = (string) ($request->route()?->getName() ?? '');
        if (!in_array($ruta, ['informes.buscar', 'informes.buscar.listar'], true)) {
            return false;
        }

        $usuario = $request->user();
        $roles = [
            $this->norm((string) session('grupo_nombre', '')),
            $this->norm((string) ($usuario?->grupo?->nombre ?? '')),
            $this->norm((string) ($usuario?->rol?->rol ?? '')),
        ];

        $permitidos = ['admin', 'administrador', 'master', 'admin master', 'administrador master', 'tecnico master'];

        return count(array_intersect($roles, $permitidos)) > 0;
    }

    /**
     * Permite a cualquier usuario autenticado acceder a las rutas de técnico del módulo informes
     * (crear informe, mis informes, guardar informe, buscar órdenes para informe e imprimir).
     * El acceso real a los datos ya está restringido internamente en el controller por tecnico_id.
     */
    private function puedeAccederRutasTecnicoInformes(Request $request, string $modulo, string $accion): bool
    {
        if ($this->norm($modulo) !== 'informes') {
            return false;
        }

        $accionNorm = $this->norm($accion);
        if (!in_array($accionNorm, ['crear', 'ver'], true)) {
            return false;
        }

        $rutasTecnico = [
            'informes.crear',
            'informes.crear.buscar',
            'informes.mis',
            'informes.store',
            'informes.imprimir',
            'informes.ver',
        ];

        $ruta = (string) ($request->route()?->getName() ?? '');

        return in_array($ruta, $rutasTecnico, true) && $request->user() !== null;
    }

    private function norm(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1;
        }
        if (is_string($value)) {
            $v = mb_strtolower(trim($value));
            return in_array($v, ['1', 'true', 'si', 'sí', 'yes', 'on'], true);
        }
        return false;
    }
}
