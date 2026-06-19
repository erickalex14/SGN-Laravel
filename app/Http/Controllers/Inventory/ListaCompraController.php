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
        $sucursalId = null;
        if (! $this->esSuperAdminOMaster()) {
            $sucursalId = (int) session('sucursal_id');
        }

        $listas = $this->repository->obtenerTodas($sucursalId);
        $solicitudesPendientes = $this->repository->obtenerSolicitudesPendientesDeCompra($sucursalId);

        // Carga ansiosa para evitar consultas N+1 en la auditoría
        $auditoriasQuery = \App\Models\Operations\SolicitudRepuesto::with([
            'listaCompra',
            'tecnico',
            'orden',
            'orden.cliente',
            'orden.tecnico'
        ])
        ->whereNotNull('lista_compra_id');

        if ($sucursalId !== null && $sucursalId > 0) {
            $auditoriasQuery->whereHas('orden', function ($o) use ($sucursalId) {
                $o->where('sucursal_id', $sucursalId);
            });
        }

        $auditorias = $auditoriasQuery->orderBy('id', 'desc')->get();

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
        if (! $this->esSuperAdminOMaster()) {
            $sucursalId = (int) session('sucursal_id');
            $ids = array_filter(array_unique((array) $request->input('solicitudes_ids', [])));
            if (! empty($ids)) {
                $count = \App\Models\Operations\SolicitudRepuesto::whereIn('id', $ids)
                    ->whereHas('orden', function ($o) use ($sucursalId) {
                        $o->where('sucursal_id', $sucursalId);
                    })
                    ->count();
                if ($count !== count($ids)) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'No tienes permisos para consolidar solicitudes de repuestos de otra sucursal.'
                    ]);
                }
            }
        }

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

        if (! $this->esSuperAdminOMaster()) {
            $sucursalId = (int) session('sucursal_id');
            $items = $items->filter(function ($item) use ($sucursalId) {
                return $item->orden && (int) $item->orden->sucursal_id === $sucursalId;
            });
            abort_if($items->isEmpty(), 403, 'No tienes permisos para ver esta lista de compras.');
        }

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

    private function esSuperAdminOMaster(): bool
    {
        $usuario = auth()->user();
        if (! $usuario) {
            return false;
        }

        $rol = $usuario->rol ? mb_strtolower(trim((string) $usuario->rol->rol)) : '';
        $grupo = $usuario->grupo ? mb_strtolower(trim((string) $usuario->grupo->nombre)) : '';
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        $superRoles = [
            'admin master', 'administrador master', 'superadmin', 'superadministrador',
        ];

        return session('es_superadmin') === true
            || in_array($rol, $superRoles, true)
            || in_array($grupo, $superRoles, true)
            || in_array($sessionGrupo, $superRoles, true);
    }
}
