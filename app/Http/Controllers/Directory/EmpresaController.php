<?php

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\GuardarEmpresaRequest;
use App\Services\Directory\EmpresaService;
use App\Repositories\Directory\EmpresaRepository;
use App\DTOs\Directory\EmpresaDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class EmpresaController extends Controller
{
    protected EmpresaService $service;
    protected EmpresaRepository $repository;

    public function __construct(EmpresaService $service, EmpresaRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    // Renderiza la vista principal (modulo-empresas.php)
    public function index(): View
    {
        $empresas = $this->repository->obtenerTodas();
        return view('directory.empresas.index', compact('empresas'));
    }

    // Endpoint para peticion AJAX de refresco (get_empresas.php)
    public function listar(): JsonResponse
    {
        $empresas = $this->repository->obtenerTodas();
        return response()->json([
            'ok' => true,
            'empresas' => $empresas
        ]);
    }

    // Endpoint unificado para Guardar y Eliminar (guardar_empresa.php)
    public function guardar(GuardarEmpresaRequest $request): JsonResponse
    {
        try {
            if ($request->input('accion') === 'eliminar') {
                $this->service->eliminar((int) $request->input('id'));
                return response()->json(['ok' => true]);
            }

            $dto = new EmpresaDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('nombre'),
                $request->input('ruc'),
                $request->input('telefono'),
                $request->input('correo'),
                $request->input('direccion')
            );

            $mensaje = $this->service->guardar($dto);

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
}
