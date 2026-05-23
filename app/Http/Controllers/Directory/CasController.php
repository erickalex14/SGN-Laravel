<?php

namespace App\Http\Controllers\Directory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\GuardarCasRequest;
use App\Services\Directory\CasService;
use App\Repositories\Directory\CasRepository;
use App\Repositories\Inventory\MarcaRepository;
use App\DTOs\Directory\CasDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class CasController extends Controller
{
    protected CasService $casService;
    protected MarcaRepository $marcaRepository;
    protected CasRepository $casRepository;

    public function __construct(CasService $casService, CasRepository $casRepository, MarcaRepository $marcaRepository)
    {
        $this->casService = $casService;
        $this->casRepository = $casRepository;
        $this->marcaRepository = $marcaRepository;
    }

    public function index(): View
    {
        $cas_list = $this->casRepository->obtenerTodOs();
        $marcas_list = $this->marcaRepository->obtenerTodas();
        return view('directory.cas.index', compact('cas_list', 'marcas_list'));
    }

    public function guardar(GuardarCasRequest $request): JsonResponse
    {
        try {
            $accion = $request->input('accion');

            $dto = new CasDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('nombre'),
                $request->input('marca'),
                $request->input('telefono'),
                $request->input('correo'),
                $request->input('ciudad'),
                $request->input('direccion'),
                $request->input('contacto'),
                $request->input('notas'),
                $request->has('activo') ? (int) $request->input('activo') : 1
            );

            $mensaje = $this->casService->guardar($dto, $accion);

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint equivalente a get_cas.php para consumo de otros modulos
     */
    public function listarActivos(): JsonResponse
    {
        $cas = $this->casRepository->obtenerActivos();

        return response()->json([
            'ok' => true,
            'cas' => $cas
        ]);
    }

}
