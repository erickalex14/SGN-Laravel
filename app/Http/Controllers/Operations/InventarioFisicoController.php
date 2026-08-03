<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ProductoInventarioFisicoSt;
use App\Models\Operations\OrdenEmpresa;
use App\Services\Operations\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class InventarioFisicoController extends Controller
{
    /**
     * Dashboard de auditoría de inventario físico ST.
     */
    public function index(Request $request): View
    {
        $sa = session('es_superadmin') === true;
        $sucursalId = (int) session('sucursal_id');

        // 1. Obtener métricas rápidas (contadores de todos los estados aplicando filtro de sucursal)
        $countQuery = ProductoInventarioFisicoSt::query();
        if (!$sa && $sucursalId > 0) {
            $countQuery->where('sucursal_id', $sucursalId);
        }

        $totalProductos = (clone $countQuery)->count();
        $totalTienda = (clone $countQuery)->where('estado', 'Tienda')->count();
        $totalIncinerox = (clone $countQuery)->where('estado', 'Incinerox')->count();
        $totalOutlet = (clone $countQuery)->where('estado', 'Outlet')->count();

        // 2. Construir la consulta
        $query = ProductoInventarioFisicoSt::with(['ordenEmpresa', 'sucursal'])
            ->orderBy('id', 'desc');

        if (!$sa && $sucursalId > 0) {
            $query->where('sucursal_id', $sucursalId);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        // Filtro de búsqueda general (código, serie, nombre o número de orden)
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($sub) use ($buscar) {
                $sub->where('codigo', 'like', '%' . $buscar . '%')
                    ->orWhere('serie', 'like', '%' . $buscar . '%')
                    ->orWhere('nombre', 'like', '%' . $buscar . '%')
                    ->orWhereHas('ordenEmpresa', function ($q) use ($buscar) {
                        $q->where('nro_orden', 'like', '%' . $buscar . '%');
                    });
            });
        }

        // 3. Paginar resultados (50 por página)
        $productos = $query->paginate(50)->withQueryString();

        return view('operations.inventario_fisico.index', compact(
            'productos',
            'totalProductos',
            'totalTienda',
            'totalIncinerox',
            'totalOutlet'
        ));
    }

    /**
     * API GET: Obtiene todos los productos de inventario físico de una orden corporativa.
     */
    public function obtenerPorOrden(int $ordenId): JsonResponse
    {
        $sa = session('es_superadmin') === true;
        $sucursalId = (int) session('sucursal_id');

        $query = ProductoInventarioFisicoSt::where('orden_empresa_id', $ordenId);

        if (!$sa && $sucursalId > 0) {
            $query->where('sucursal_id', $sucursalId);
        }

        $productos = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'ok' => true,
            'productos' => $productos
        ]);
    }

    /**
     * API POST: Guarda el estado físico del inventario seleccionado por el usuario.
     */
    public function guardarEstados(Request $request): JsonResponse
    {
        $request->validate([
            'orden_empresa_id' => 'required|integer',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.estado' => 'required|string|in:Tienda,Incinerox,Outlet',
            'productos.*.detalle_outlet' => 'nullable|string',
        ]);

        $ordenId = (int) $request->input('orden_empresa_id');
        $orden = OrdenEmpresa::find($ordenId);

        if (!$orden) {
            return response()->json(['ok' => false, 'error' => 'Orden corporativa no encontrada.'], 404);
        }

        $sa = session('es_superadmin') === true;
        $sucursalId = (int) session('sucursal_id');
        $userId = (int) auth()->id();

        // Validar que el usuario sea superadmin, pertenezca a la sucursal de la orden, o sea el técnico asignado
        $esTecnicoAsignado = ((int)$orden->tecnico_id === $userId);
        if (!$sa && $sucursalId > 0 && (int)$orden->sucursal_id !== $sucursalId && !$esTecnicoAsignado) {
            return response()->json(['ok' => false, 'error' => 'No tienes permisos para modificar el inventario físico de esta sucursal.'], 403);
        }

        try {
            DB::transaction(function () use ($request, $ordenId, $orden) {
                foreach ($request->input('productos') as $pData) {
                    $prod = ProductoInventarioFisicoSt::where('orden_empresa_id', $ordenId)
                        ->where('id', (int) $pData['id'])
                        ->first();

                    if ($prod) {
                        $estadoAnterior = $prod->estado;
                        $prod->estado = $pData['estado'];
                        $prod->detalle_outlet = $pData['estado'] === 'Outlet' ? trim($pData['detalle_outlet'] ?? '') : null;
                        $prod->save();

                        // Registrar log si el estado cambió
                        if ($estadoAnterior !== $prod->estado) {
                            AuditLogger::registrar(
                                'MODIFICAR_ESTADO_FISICO_ST',
                                'inventario',
                                (string) $prod->id,
                                [
                                    'serie' => $prod->serie,
                                    'codigo' => $prod->codigo,
                                    'nro_orden' => $orden->nro_orden ?? '',
                                    'estado_anterior' => $estadoAnterior,
                                    'estado_nuevo' => $prod->estado,
                                ]
                            );
                        }
                    }
                }
            });

            return response()->json([
                'ok' => true,
                'mensaje' => 'Estados de inventario físico actualizados correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Error al guardar los estados: ' . $e->getMessage()
            ], 500);
        }
    }
}
