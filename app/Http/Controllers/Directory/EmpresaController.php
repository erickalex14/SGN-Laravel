<?php

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\Empresa\UpdateEmpresaRequest;
use App\Http\Requests\Directory\Empresa\StoreEmpresaRequest;
use App\DTOs\Directory\EmpresaDTO;
use App\Services\Directory\EmpresaService;
use Illuminate\Http\JsonResponse;
use Exception;

class EmpresaController extends Controller
{
    protected EmpresaService $empresaService;

    public function __construct(EmpresaService $empresaService)
    {
        $this->empresaService = $empresaService;
    }

    /**
     * Obtiene el listado de empresas (Equivalente a get_empresas.php)
     */
    public function index(): JsonResponse
    {
        try {
            $empresas = $this->empresaService->listarEmpresas();
            return response()->json([
                'status' => 'success',
                'data' => $empresas
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al recuperar el listado de empresas.'
            ], 500);
        }
    }

    /**
     * Almacena una nueva empresa (Equivalente a guardar_empresa.php)
     */
    public function store(StoreEmpresaRequest $request): JsonResponse
    {
        try {
            $dto = new EmpresaDTO(
                $request->input('nombre'),
                $request->input('ruc'),
                $request->input('telefono'),
                $request->input('correo'),
                $request->input('direccion_empresa')
            );

            $this->empresaService->registrarEmpresa($dto);

            return response()->json([
                'status' => 'success',
                'message' => 'El registro se ha procesado correctamente.'
            ], 201);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza un registro existente
     */
    public function update(UpdateEmpresaRequest $request, int $id): JsonResponse
    {
        try {
            $dto = new EmpresaDTO(
                $request->input('nombre'),
                $request->input('ruc'),
                $request->input('telefono'),
                $request->input('correo'),
                $request->input('direccion_empresa')
            );

            $this->empresaService->modificarEmpresa($id, $dto);

            return response()->json([
                'status' => 'success',
                'message' => 'La empresa ha sido actualizada correctamente.'
            ], 200);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un registro del sistema
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->empresaService->removerEmpresa($id);
            return response()->json([
                'status' => 'success',
                'message' => 'El registro ha sido eliminado del sistema.'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
