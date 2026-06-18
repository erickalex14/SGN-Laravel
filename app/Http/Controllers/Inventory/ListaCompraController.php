<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\GuardarListaCompraRequest;
use App\Services\Inventory\ListaCompraService;
use App\Repositories\Inventory\ListaCompraRepository;
use App\DTOs\Inventory\ListaCompraDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class ListaCompraController extends Controller
{
    protected ListaCompraService $service;
    protected ListaCompraRepository $repository;

    public function __construct(ListaCompraService $service, ListaCompraRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(): View
    {
        $listas = $this->repository->obtenerTodas();
        $solicitudesPendientes = $this->repository->obtenerSolicitudesPendientesDeCompra();

        // Carga ansiosa para evitar consultas N+1 en la auditoría
        $auditorias = \App\Models\Operations\SolicitudRepuesto::with([
            'listaCompra',
            'tecnico',
            'orden',
            'orden.cliente',
            'orden.tecnico'
        ])
        ->whereNotNull('lista_compra_id')
        ->orderBy('id', 'desc')
        ->get();

        $tecnicosList = \App\Models\Identity\Usuario::orderBy('nombre_tecnico', 'asc')->get();
        $creadoresList = \App\Models\Inventory\ListaCompra::distinct()
            ->whereNotNull('creado_por')
            ->pluck('creado_por')
            ->filter();

        return view('inventory.listas_compra.index', compact(
            'listas', 
            'solicitudesPendientes', 
            'auditorias', 
            'tecnicosList', 
            'creadoresList'
        ));
    }

    public function store(GuardarListaCompraRequest $request): JsonResponse
    {
        try {
            $dto = new ListaCompraDTO(
                $request->input('solicitudes_ids', []),
                $request->input('observacion')
            );

            $nroLista = $this->service->generarLista($dto, session('tecnico_id'), session('nombre'));

            return response()->json([
                'ok'      => true,
                'mensaje' => "La lista de compra {$nroLista} ha sido consolidada con éxito."
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function imprimir(int $id): View
    {
        $lista = $this->repository->buscarPorId($id);
        abort_if(!$lista, 404);

        $items = $this->repository->obtenerItemsPorLista($id);
        $tecnicoId = (int) session('tecnico_id', 0);
        $esAdmin = $this->esAdminRepuestos();
        $esPropietario = $tecnicoId > 0 && $items->contains(fn ($item) => (int) ($item->tecnico_id ?? 0) === $tecnicoId);
        abort_unless($esAdmin || $esPropietario, 403);

        $totalCantidad = (int) $items->sum('cantidad');

        return view('inventory.listas_compra.imprimir', compact('lista', 'items', 'totalCantidad'));
    }

    private function esAdminRepuestos(): bool
    {
        $permisos = (array) session('permisos', []);

        return (bool) session('es_superadmin', false)
            || (($permisos['repuestos_admin']['ver'] ?? false) === true)
            || (($permisos['repuestos_admin']['editar'] ?? false) === true);
    }
}
