<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarPrecioRequest;
use App\Http\Requests\Operations\GuardarTipoServicioRequest;
use App\Services\Operations\PrecioEstandarService;
use App\Services\Operations\TipoServicioService;
use App\Repositories\Operations\PrecioEstandarRepository;
use App\Repositories\Operations\TipoServicioRepository;
use App\DTOs\Operations\PrecioEstandarDTO;
use App\DTOs\Operations\TipoServicioDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class CatalogoPrecioController extends Controller
{
    protected PrecioEstandarService $precioService;
    protected TipoServicioService $tipoService;
    protected PrecioEstandarRepository $precioRepository;
    protected TipoServicioRepository $tipoRepository;

    public function __construct(
        PrecioEstandarService $precioService,
        TipoServicioService $tipoService,
        PrecioEstandarRepository $precioRepository,
        TipoServicioRepository $tipoRepository
    ) {
        $this->precioService = $precioService;
        $this->tipoService = $tipoService;
        $this->precioRepository = $precioRepository;
        $this->tipoRepository = $tipoRepository;
    }

    public function index(): View
    {
        $precios = $this->precioRepository->obtenerTodos();
        $tipos   = $this->tipoRepository->obtenerTodos();

        return view('operations.precios.index', compact('precios', 'tipos'));
    }

    public function procesarPrecio(GuardarPrecioRequest $request): JsonResponse
    {
        try {
            $dto = new PrecioEstandarDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('servicio', ''),
                (float) $request->input('precio', 0),
                $request->input('descripcion'),
                $request->has('activo') ? (int) $request->input('activo') : 1
            );

            $this->precioService->procesar($dto, $request->input('accion'));

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function procesarTipo(GuardarTipoServicioRequest $request): JsonResponse
    {
        try {
            $dto = new TipoServicioDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('nombre', ''),
                (float) $request->input('precio', 0),
                $request->input('descripcion'),
                $request->has('activo') ? (int) $request->input('activo') : 1
            );

            $this->tipoService->procesar($dto, $request->input('accion'));

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
