<?php

namespace App\Http\Controllers\Directory;

use App\DTOs\Directory\SucursalDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\Sucursal\StoreSucursalRequest;
use App\Http\Requests\Directory\Sucursal\UpdateSucursalRequest;
use App\Services\Directory\SucursalService;
use Exception;
use Illuminate\Http\JsonResponse;

class SucursalController extends Controller
{
    protected SucursalService $sucursalService;

    public function __construct(SucursalService $sucursalService)
    {
        $this->sucursalService = $sucursalService;
    }

    public function store(StoreSucursalRequest $request): JsonResponse
    {
        try {
            $dto = new SucursalDTO(
                $request->input('nombre_sucursal'),
                $request->input('ciudad'),
                $request->input('secuencial'),
                $request->input('nro_caso')
            );

            $this->sucursalService->registrarSucursal($dto);

            return response()->json([
                'status' => 'success',
                'message' => 'La sucursal ha sido registrada correctamente en el sistema.'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Se ha producido un error interno. Consulte los registros del sistema.'
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $sucursales = $this->sucursalService->listarSucursales();
            return response()->json(['status' => 'success', 'data' => $sucursales], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error obteniendo las sucursales.'], 500);
        }
    }

    public function update(UpdateSucursalRequest $request, int $id): JsonResponse
    {
        try {
            $dto = new SucursalDTO(
                $request->input('nombre_sucursal'),
                $request->input('ciudad'),
                $request->input('secuencial'),
                $request->input('nro_caso')
            );

            $this->sucursalService->modificarSucursal($id, $dto);

            return response()->json(['status' => 'success', 'message' => 'Sucursal actualizada exitosamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->sucursalService->removerSucursal($id);
            return response()->json(['status' => 'success', 'message' => 'Sucursal eliminada del sistema.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
