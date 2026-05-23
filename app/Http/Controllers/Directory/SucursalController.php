<?php

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\GuardarSucursalRequest;
use App\Services\Directory\SucursalService;
use App\Repositories\Directory\SucursalRepository;
use App\DTOs\Directory\SucursalDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class SucursalController extends Controller
{
    protected SucursalService $service;
    protected SucursalRepository $repository;

    public function __construct(SucursalService $service, SucursalRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(): View
    {
        $sucursales = $this->repository->obtenerTodas();
        return view('directory.sucursales.index', compact('sucursales'));
    }

    public function crear(GuardarSucursalRequest $request): JsonResponse
    {
        return $this->procesarGuardado($request);
    }

    public function actualizar(GuardarSucursalRequest $request): JsonResponse
    {
        if (!$request->input('id')) {
            return response()->json(['ok' => false, 'error' => 'Sucursal no identificada.'], 422);
        }
        return $this->procesarGuardado($request);
    }

    private function procesarGuardado(GuardarSucursalRequest $request): JsonResponse
    {
        try {
            $dto = new SucursalDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                (int) $request->input('nro_sucursal'),
                $request->input('ciudad'),
                strtoupper($request->input('secuencial')),
                $request->input('nro_base')
            );

            $resultado = $this->service->guardar($dto);

            return response()->json([
                'ok'       => true,
                'mensaje'  => $resultado['mensaje'],
                'sucursal' => $resultado['sucursal']
            ]);

        } catch (Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
