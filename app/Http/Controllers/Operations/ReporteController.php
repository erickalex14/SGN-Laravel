<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\FiltrarReporteRequest;
use App\Services\Operations\ReporteService;
use App\Repositories\Identity\UsuarioRepository;
use App\Repositories\Directory\SucursalRepository;
use App\DTOs\Operations\ReporteFiltroDTO;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Exception;

class ReporteController extends Controller
{
    protected ReporteService $service;
    protected UsuarioRepository $usuarioRepo;
    protected SucursalRepository $sucursalRepo;

    public function __construct(
        ReporteService $service,
        UsuarioRepository $usuarioRepo,
        SucursalRepository $sucursalRepo
    ) {
        $this->service = $service;
        $this->usuarioRepo = $usuarioRepo;
        $this->sucursalRepo = $sucursalRepo;
    }

    public function index(): View
    {
        // Cargar catálogos para los filtros de la vista
        $tecnicos = $this->usuarioRepo->obtenerTodosConRelaciones();
        $sucursales = $this->sucursalRepo->obtenerTodas();

        // Obtener estados únicos de las órdenes existentes en la BD
        $estados = \App\Models\Operations\Orden::select('estado_orden')
            ->distinct()
            ->orderBy('estado_orden')
            ->pluck('estado_orden');

        return view('operations.reportes.index', compact('tecnicos', 'sucursales', 'estados'));
    }

    public function filtrar(FiltrarReporteRequest $request): JsonResponse
    {
        try {
            $dto = new ReporteFiltroDTO(
                $request->input('fecha_inicio'),
                $request->input('fecha_fin'),
                $request->input('estado'),
                $request->input('tecnico_id') ? (int) $request->input('tecnico_id') : null,
                $request->input('sucursal_id') ? (int) $request->input('sucursal_id') : null
            );

            $resultados = $this->service->generarReporte($dto, session('tecnico_id'));

            return response()->json([
                'ok' => true,
                'data' => $resultados
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}