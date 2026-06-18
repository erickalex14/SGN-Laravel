<?php

namespace App\Http\Controllers\Inventory;

use App\DTOs\Inventory\BuscarRepuestoOrdenDTO;
use App\DTOs\Inventory\RepuestoDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\BuscarRepuestoOrdenRequest;
use App\Http\Requests\Inventory\GuardarRepuestoRequest;
use App\Models\Identity\Usuario;
use App\Models\Inventory\Repuesto;
use App\Models\Operations\OrdenRepuesto;
use App\Repositories\Inventory\RepuestoRepository;
use App\Services\Inventory\RepuestoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
            'ok' => true,
            'repuestos' => $repuestos,
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

    public function auditoria(Request $request): View
    {
        $query = OrdenRepuesto::with(['repuesto', 'orden.tecnico', 'orden.sucursal', 'usuario'])
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

        $repuestosList = Repuesto::orderBy('nombre', 'asc')->get();
        $tecnicosList = Usuario::orderBy('nombre_tecnico', 'asc')->get();

        return view('inventory.repuestos.auditoria', compact('auditorias', 'repuestosList', 'tecnicosList'));
    }

    public function imprimirReporte(Request $request): View
    {
        $query = OrdenRepuesto::with(['repuesto', 'orden.tecnico', 'orden.sucursal', 'usuario'])
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

        $buscar = trim((string) $request->input('buscar'));
        if ($buscar !== '') {
            $buscarLower = mb_strtolower($buscar);
            $auditorias = $auditorias->filter(function ($a) use ($buscarLower) {
                $tecnicoNombre = $a->usuario->nombre_tecnico ?? $a->orden->tecnico->nombre_tecnico ?? 'N/A';

                return mb_strpos(mb_strtolower($a->repuesto->codigo ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($a->repuesto->nombre ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($tecnicoNombre), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($a->orden->nro_orden ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($a->orden->motivo_ingreso ?? ''), $buscarLower) !== false;
            });
        }

        // Obtener texto explicativo de filtros
        $filtrosTxt = [];
        if ($request->filled('repuesto_id')) {
            $rep = Repuesto::find($request->input('repuesto_id'));
            if ($rep) {
                $filtrosTxt[] = 'Repuesto: '.$rep->nombre;
            }
        }
        if ($request->filled('usuario_id')) {
            $user = Usuario::find($request->input('usuario_id'));
            if ($user) {
                $filtrosTxt[] = 'Técnico: '.($user->nombre_tecnico ?: $user->usuario);
            }
        }
        if ($request->filled('fecha_desde')) {
            $filtrosTxt[] = 'Desde: '.Carbon::parse($request->input('fecha_desde'))->format('d/m/Y');
        }
        if ($request->filled('fecha_hasta')) {
            $filtrosTxt[] = 'Hasta: '.Carbon::parse($request->input('fecha_hasta'))->format('d/m/Y');
        }
        if ($buscar !== '') {
            $filtrosTxt[] = 'Búsqueda local: "'.$buscar.'"';
        }

        return view('inventory.repuestos.imprimir_reporte', compact('auditorias', 'filtrosTxt'));
    }
}
