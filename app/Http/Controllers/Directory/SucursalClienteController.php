<?php

namespace App\Http\Controllers\Directory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\GuardarSucursalClienteRequest;
use App\Services\Directory\SucursalClienteService;
use App\Repositories\Directory\SucursalClienteRepository;
use App\Repositories\Directory\SucursalRepository; // Para el combobox Novitec
use App\DTOs\Directory\SucursalClienteDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Exception;
class SucursalClienteController extends Controller
{
    protected SucursalClienteService $service;
    protected SucursalClienteRepository $repository;
    protected SucursalRepository $sucursalNovitecRepository;

    public function __construct(
        SucursalClienteService $service,
        SucursalClienteRepository $repository,
        SucursalRepository $sucursalNovitecRepository
    ) {
        $this->service = $service;
        $this->repository = $repository;
        $this->sucursalNovitecRepository = $sucursalNovitecRepository;
    }

    public function index(): View
    {
        $sucursales = $this->repository->obtenerTodas();
        $sucursales_novitec = $this->sucursalNovitecRepository->obtenerTodas();

        // Emulamos la logica de buscar provincias en DB si la tabla existe
        $provincias_db = [];
        if (DB::getSchemaBuilder()->hasTable('Provincias')) {
            $provincias_db = DB::table('Provincias')->orderBy('nombre', 'asc')->pluck('nombre')->toArray();
        }

        if (empty($provincias_db)) {
            $provincias_db = [
                'AZUAY','BOLÍVAR','CAÑAR','CARCHI','CHIMBORAZO','COTOPAXI',
                'EL ORO','ESMERALDAS','GALÁPAGOS','GUAYAS','IMBABURA','LOJA',
                'LOS RÍOS','MANABÍ','MORONA SANTIAGO','NAPO','ORELLANA',
                'PASTAZA','PICHINCHA','SANTA ELENA','SANTO DOMINGO DE LOS TSÁCHILAS',
                'SUCUMBÍOS','TUNGURAHUA','ZAMORA CHINCHIPE'
            ];
        }

        return view('directory.sucursales_cliente.index', compact('sucursales', 'sucursales_novitec', 'provincias_db'));
    }

    public function crear(GuardarSucursalClienteRequest $request): JsonResponse
    {
        return $this->procesarGuardado($request);
    }

    public function actualizar(GuardarSucursalClienteRequest $request): JsonResponse
    {
        if (!$request->input('id')) {
            return response()->json(['ok' => false, 'error' => 'ID inválido.']);
        }
        return $this->procesarGuardado($request);
    }

    private function procesarGuardado(GuardarSucursalClienteRequest $request): JsonResponse
    {
        try {
            $dto = new SucursalClienteDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->has('numero') ? (int) $request->input('numero') : null,
                $request->input('codigo'),
                $request->input('nombre'),
                $request->input('provincia'),
                $request->input('novitec_sucursal'),
                $request->has('activa') ? (int) $request->input('activa') : 1
            );

            $resultado = $this->service->guardar($dto);

            return response()->json([
                'ok' => true,
                'mensaje' => $resultado['mensaje'],
                'sucursal' => $resultado['sucursal']
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function toggle(Request $request): JsonResponse
    {
        try {
            $id = (int) $request->input('id');
            $activa = (int) $request->input('activa');

            if (!$id) throw new Exception('ID inválido.');

            $resultado = $this->service->toggleEstatus($id, $activa);

            return response()->json([
                'ok' => true,
                'mensaje' => $resultado['mensaje'],
                'sucursal' => $resultado['sucursal']
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
