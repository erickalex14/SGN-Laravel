<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\GuardarProductoRequest;
use App\Services\Inventory\ProductoService;
use App\Repositories\Inventory\ProductoRepository;
use App\Repositories\Inventory\MarcaRepository;
use App\Repositories\Inventory\TipoDispositivoRepository;
use App\DTOs\Inventory\ProductoDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class ProductoController extends Controller
{
    protected ProductoService $service;
    protected ProductoRepository $repository;
    protected MarcaRepository $marcaRepository;
    protected TipoDispositivoRepository $tipoRepository;

    public function __construct(
        ProductoService $service,
        ProductoRepository $repository,
        MarcaRepository $marcaRepository,
        TipoDispositivoRepository $tipoRepository
    ) {
        $this->service = $service;
        $this->repository = $repository;
        $this->marcaRepository = $marcaRepository;
        $this->tipoRepository = $tipoRepository;
    }

    public function index(): View
    {
        $productos = $this->repository->obtenerTodos();
        $marcas = $this->marcaRepository->obtenerTodas();
        $tipos = $this->tipoRepository->obtenerTodos();

        return view('inventory.productos.index', compact('productos', 'marcas', 'tipos'));
    }

    public function procesar(GuardarProductoRequest $request): JsonResponse
    {
        try {
            $accion = $request->input('accion');

            if ($accion === 'eliminar') {
                $this->service->eliminar((int) $request->input('id'));
                return response()->json(['ok' => true]);
            }

            $dto = new ProductoDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('codigo'),
                $request->input('descripcion'),
                (int) $request->input('marca_id'),
                (int) $request->input('tipo_dispositivo_id')
            );

            $this->service->guardar($dto, $accion);

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint API para recarga de grillas
     */
    public function listar(): JsonResponse
    {
        $productos = $this->repository->obtenerTodos();

        return response()->json([
            'ok' => true,
            'productos' => $productos
        ]);
    }
}
