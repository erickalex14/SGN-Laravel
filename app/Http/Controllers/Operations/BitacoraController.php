<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Identity\Bitacora;
use App\Models\Identity\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BitacoraController extends Controller
{
    /**
     * Muestra la vista principal de la bitácora con los filtros aplicados.
     */
    public function index(Request $request): View
    {
        // 1. Validación estricta de permisos
        $sa = session('es_superadmin') === true;

        $rolNombre = mb_strtolower(trim((string) (auth()->user()?->rol?->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) (auth()->user()?->grupo?->nombre ?? '')));
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        $esAdmin = in_array($rolNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($grupoNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);

        if (!$sa && !$esAdmin) {
            abort(403, 'No tienes permisos para acceder a la bitácora de auditoría.');
        }

        // 2. Construcción de consulta de logs
        $query = Bitacora::orderBy('id', 'desc');

        // Filtro por usuario
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }

        // Filtro por módulo
        if ($request->filled('modulo')) {
            $query->where('modulo', strtolower(trim($request->input('modulo'))));
        }

        // Filtro por acción (búsqueda parcial)
        if ($request->filled('accion')) {
            $query->where('accion', 'like', '%' . strtoupper(trim($request->input('accion'))) . '%');
        }

        // Filtro por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }

        // Filtro por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        // Filtro de búsqueda general (por detalles o nombre de usuario o registro_id)
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($sub) use ($buscar) {
                $sub->where('usuario_nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('detalles', 'like', '%' . $buscar . '%')
                    ->orWhere('registro_id', 'like', '%' . $buscar . '%');
            });
        }

        // 3. Paginación de resultados (50 por página)
        $logs = $query->paginate(50)->withQueryString();

        // 4. Catálogos para filtros
        $usuarios = Usuario::where('activo', 1)
            ->orderBy('nombre_tecnico', 'asc')
            ->get();

        // Módulos conocidos para el dropdown
        $modulos = [
            'auth' => 'Autenticación',
            'ordenes' => 'Órdenes y Preórdenes',
            'inventario' => 'Inventario y Repuestos',
            'usuarios' => 'Usuarios y Grupos',
            'directorio' => 'Directorio (CAS / Empresas)',
            'informes' => 'Informes Técnicos',
            'notas_credito' => 'Notas de Crédito',
        ];

        return view('operations.bitacora.index', compact('logs', 'usuarios', 'modulos'));
    }
}
