<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\GuardarRepuestoRequest;
use App\Http\Requests\Inventory\BuscarRepuestoOrdenRequest;
use App\Services\Inventory\RepuestoService;
use App\Repositories\Inventory\RepuestoRepository;
use App\DTOs\Inventory\RepuestoDTO;
use App\DTOs\Inventory\BuscarRepuestoOrdenDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class RepuestoController extends Controller
{
    protected RepuestoService $service;
    protected RepuestoRepository $repository;

    public function __construct(
        RepuestoService $service,
        RepuestoRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(): View
    {
        $repuestos = $this->repository->obtenerTodos();

        return view('inventory.repuestos.index', compact('repuestos'));
    }

    public function procesar(GuardarRepuestoRequest $request): JsonResponse
    {
        try {
            $accion = $request->input('accion');

            if ($accion === 'eliminar') {
                $this->service->eliminar((int) $request->input('id'));
                return response()->json(['ok' => true]);
            }

            $dto = new RepuestoDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('codigo'),
                $request->input('nro_parte'),
                $request->input('nombre'),
                (int) $request->input('stock'),
                (float) $request->input('costo'),
                $request->input('bodega'),
                $request->input('descripcion'),
                $request->input('marca_id'),
                $request->input('tipo_dispositivo_id')
            );

            $this->service->guardar($dto, $accion);

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint API para recarga de grillas y autocompletado en ordenes
     */
    public function listar(): JsonResponse
    {
        $repuestos = $this->repository->obtenerTodos();
        return response()->json([
            'ok'        => true,
            'repuestos' => $repuestos
        ]);
    }

    public function buscarParaOrden(BuscarRepuestoOrdenRequest $request): JsonResponse
    {
        try {
            $dto = new BuscarRepuestoOrdenDTO(
                (string) $request->query('q', ''),
                filter_var($request->query('stock_only', true), FILTER_VALIDATE_BOOLEAN)
            );

            $repuestos = $this->service->buscarParaOrden($dto);

            return response()->json([
                'ok' => true,
                'repuestos' => $repuestos,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function auditoria(\Illuminate\Http\Request $request): \Illuminate\View\View
    {
        $query = \App\Models\Operations\OrdenRepuesto::with(['repuesto', 'orden.tecnico', 'orden.sucursal', 'usuario'])
            ->orderBy('fecha', 'desc');

        if ($request->filled('repuesto_id')) {
            $query->where('repuesto_id', $request->input('repuesto_id'));
        }
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $auditorias = $query->get();

        $repuestosList = \App\Models\Inventory\Repuesto::orderBy('nombre', 'asc')->get();
        $tecnicosList = \App\Models\Identity\Usuario::orderBy('nombre_tecnico', 'asc')->get();

        return view('inventory.repuestos.auditoria', compact('auditorias', 'repuestosList', 'tecnicosList'));
    }
}
