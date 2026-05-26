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

        return view('inventory.listas_compra.index', compact('listas', 'solicitudesPendientes'));
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
}