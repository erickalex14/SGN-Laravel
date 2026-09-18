<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\FacturacionLoteService;
use App\Models\Directory\Sucursal;
use Illuminate\Http\Request;
use Exception;

class FacturacionLoteController extends Controller
{
    public function __construct(
        private readonly FacturacionLoteService $service
    ) {}

    private function ensureAccess(): void
    {
        $usuario = auth()->user();
        if (!$usuario) {
            abort(401);
        }

        $sa = session('es_superadmin');
        $p = session('permisos', []);
        $rolNombre = mb_strtolower(trim((string) ($usuario->rol->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) ($usuario->grupo->nombre ?? '')));
        $esAdminMaster = $sa
            || (bool) ($usuario->grupo->es_superadmin ?? false)
            || in_array($rolNombre, ['admin master', 'administrador master', 'administrador', 'admin'], true)
            || in_array($grupoNombre, ['admin master', 'administrador master', 'superadministrador', 'administradores'], true);

        $tienePermiso = !empty($p['caja_general']['ver']) 
            || !empty($p['recuento_b2b']['ver'])
            || !empty($p['reportes']['ver']);

        abort_unless($esAdminMaster || $tienePermiso, 403, 'Acceso denegado. No tienes permisos para gestionar facturación por lotes.');
    }

    public function index(Request $request)
    {
        $this->ensureAccess();

        $filtros = [
            'tipo_orden' => $request->query('tipo_orden', ''),
            'estado_facturacion' => $request->query('estado_facturacion', 'Pendiente'),
            'sucursal_id' => $request->query('sucursal_id', ''),
            'buscar' => $request->query('buscar', ''),
        ];

        $ordenes = $this->service->obtenerOrdenesElegibles($filtros);
        $sucursales = Sucursal::orderBy('ciudad')->get();
        $estadosPermitidos = FacturacionLoteService::ESTADOS_ELEGIBLES;

        return view('accounting.facturacion_lotes.index', compact('ordenes', 'sucursales', 'filtros', 'estadosPermitidos'));
    }

    public function historial(Request $request)
    {
        $this->ensureAccess();

        $filtros = [
            'buscar' => $request->query('buscar', ''),
            'fecha_desde' => $request->query('fecha_desde', ''),
            'fecha_hasta' => $request->query('fecha_hasta', ''),
        ];

        $lotes = $this->service->obtenerHistorialLotes($filtros);

        return view('accounting.facturacion_lotes.historial', compact('lotes', 'filtros'));
    }

    public function guardar(Request $request)
    {
        $this->ensureAccess();

        $validated = $request->validate([
            'nro_factura' => 'required|string|max:50',
            'nro_autorizacion' => 'nullable|string|max:60',
            'valor_facturado' => 'required|numeric|min:0',
            'fecha_factura' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
            'ordenes' => 'required|array|min:1',
            'ordenes.*.tipo_orden' => 'required|in:personal,empresa',
            'ordenes.*.orden_id' => 'required|integer',
            'ordenes.*.nro_orden' => 'required|string',
        ], [
            'nro_factura.required' => 'El número de factura de Milenium es obligatorio.',
            'valor_facturado.required' => 'El valor facturado es obligatorio.',
            'fecha_factura.required' => 'La fecha de facturación es obligatoria.',
            'ordenes.required' => 'Debe seleccionar al menos una orden para facturar.',
            'ordenes.min' => 'Debe seleccionar al menos una orden para facturar.',
        ]);

        try {
            $lote = $this->service->guardarLoteFacturacion(
                [
                    'nro_factura' => $validated['nro_factura'],
                    'nro_autorizacion' => $validated['nro_autorizacion'] ?? null,
                    'valor_facturado' => $validated['valor_facturado'],
                    'fecha_factura' => $validated['fecha_factura'],
                    'observaciones' => $validated['observaciones'] ?? null,
                ],
                $validated['ordenes'],
                auth()->id()
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Lote de facturación guardado exitosamente. Se actualizaron ' . count($validated['ordenes']) . ' órdenes.',
                    'lote_id' => $lote->id,
                ]);
            }

            return redirect()->route('facturacion_lotes.index')
                ->with('success', 'Lote de factura ' . $lote->nro_factura . ' registrado con éxito con ' . count($validated['ordenes']) . ' órdenes.');
        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', 'Error al guardar el lote: ' . $e->getMessage());
        }
    }

    public function detalle(int $id)
    {
        $this->ensureAccess();

        $lote = $this->service->obtenerDetalleLote($id);
        if (!$lote) {
            return response()->json(['ok' => false, 'message' => 'Lote no encontrado.'], 404);
        }

        return response()->json([
            'ok' => true,
            'lote' => $lote,
        ]);
    }

    public function desvincularOrden(int $id)
    {
        $this->ensureAccess();

        try {
            $ok = $this->service->desvincularOrden($id);
            if (!$ok) {
                return response()->json(['ok' => false, 'message' => 'No se encontró la orden vinculada.'], 404);
            }

            return response()->json(['ok' => true, 'message' => 'Orden desvinculada exitosamente. Estado de facturación revertido a Pendiente.']);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
