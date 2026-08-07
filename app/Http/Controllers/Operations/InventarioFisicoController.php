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
     * API GET: Obtiene todos los productos de inventario físico de una orden.
     */
    public function obtenerPorOrden(int $ordenId): JsonResponse
    {
        $sa = session('es_superadmin') === true;
        $sucursalId = (int) session('sucursal_id');

        $query = ProductoInventarioFisicoSt::where(function ($q) use ($ordenId) {
            $q->where('orden_empresa_id', $ordenId);
            if (DB::getSchemaBuilder()->hasColumn('productos_inventario_fisico_st', 'orden_id')) {
                $q->orWhere('orden_id', $ordenId);
            }
        });

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
            'orden_empresa_id' => 'nullable|integer',
            'orden_id' => 'nullable|integer',
            'productos' => 'required|array',
            'productos.*.id' => 'required|integer',
            'productos.*.estado' => 'required|string',
            'productos.*.detalle_outlet' => 'nullable|string',
        ]);

        $ordenId = (int) ($request->input('orden_empresa_id') ?: $request->input('orden_id', 0));
        
        $orden = null;
        if ($ordenId > 0) {
            $orden = OrdenEmpresa::find($ordenId);
            if (!$orden) {
                $orden = DB::table('ordenes')->where('id', $ordenId)->first();
            }
        }

        try {
            DB::transaction(function () use ($request, $ordenId, $orden) {
                foreach ($request->input('productos') as $pData) {
                    $productId = (int) $pData['id'];

                    // Buscar el producto en productos_inventario_fisico_st por su ID primario
                    $prod = ProductoInventarioFisicoSt::find($productId);

                    // Si no se encuentra solo por ID, intentar buscar asociando la orden
                    if (!$prod && $ordenId > 0) {
                        $prod = ProductoInventarioFisicoSt::where('orden_empresa_id', $ordenId)
                            ->where('id', $productId)
                            ->first();
                    }

                    if ($prod) {
                        $estadoAnterior = $prod->estado;
                        $rawEstado = trim((string) $pData['estado']);
                        $estadoNorm = ucfirst(strtolower($rawEstado));
                        if (!in_array($estadoNorm, ['Tienda', 'Incinerox', 'Outlet'])) {
                            $estadoNorm = $rawEstado;
                        }

                        $prod->estado = $estadoNorm;
                        $prod->detalle_outlet = $estadoNorm === 'Outlet' ? trim($pData['detalle_outlet'] ?? '') : null;
                        $prod->save();

                        // Registrar log de auditoría si cambió el estado
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
