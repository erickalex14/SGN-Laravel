<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\GuardarMarcaRequest;
use App\Http\Requests\Inventory\GuardarTipoDispositivoRequest;
use App\Services\Inventory\MarcaService;
use App\Services\Inventory\TipoDispositivoService;
use App\Repositories\Inventory\MarcaRepository;
use App\Repositories\Inventory\TipoDispositivoRepository;
use App\DTOs\Inventory\MarcaDTO;
use App\DTOs\Inventory\TipoDispositivoDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class MarcaController extends Controller
{
    protected MarcaService $marcaService;
    protected TipoDispositivoService $tipoService;
    protected MarcaRepository $marcaRepository;
    protected TipoDispositivoRepository $tipoRepository;

    public function __construct(
        MarcaService $marcaService,
        TipoDispositivoService $tipoService,
        MarcaRepository $marcaRepository,
        TipoDispositivoRepository $tipoRepository
    ) {
        $this->marcaService = $marcaService;
        $this->tipoService = $tipoService;
        $this->marcaRepository = $marcaRepository;
        $this->tipoRepository = $tipoRepository;
    }

    public function index(): View
    {
        $marcas = $this->marcaRepository->obtenerTodas();
        $tipos = $this->tipoRepository->obtenerTodos();

        return view('inventory.marcas.index', compact('marcas', 'tipos'));
    }

    public function guardarMarca(GuardarMarcaRequest $request): JsonResponse
    {
        try {
            $dto = new MarcaDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('nombre', '')
            );

            $this->marcaService->procesar($dto, $request->input('accion'));

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function guardarTipo(GuardarTipoDispositivoRequest $request): JsonResponse
    {
        try {
            $dto = new TipoDispositivoDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('codigo', ''),
                $request->input('nombre', '')
            );

            $this->tipoService->procesar($dto, $request->input('accion'));

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
