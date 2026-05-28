<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\BuscarOrdenRequest;
use App\Repositories\Operations\BuscarOrdenRepository;
use App\Services\Operations\BuscarOrdenService;
use App\DTOs\Operations\BuscarOrdenDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class BuscarOrdenController extends Controller
{
    public function __construct(
        protected BuscarOrdenService    $service,
        protected BuscarOrdenRepository $repository
    ) {}

    public function index(): View
    {
        $sucursalId   = (int) session('sucursal_id', 0);
        $esSuperadmin = session('es_superadmin') === true;

        $tecnicos = $this->repository->obtenerTecnicos($sucursalId, $esSuperadmin);
        $estados  = $this->repository->obtenerEstados();

        return view('operations.ordenes.buscar', compact('tecnicos', 'estados'));
    }

    public function listar(BuscarOrdenRequest $request): JsonResponse
    {
        try {
            $dto = new BuscarOrdenDTO(
                tipo:        (string)  $request->query('tipo', 'nro_orden'),
                q:           (string)  $request->query('q', ''),
                sucursal_id: (int)     session('sucursal_id', 0),
                es_superadmin:         session('es_superadmin') === true,
                estado:      (string)  $request->query('estado', ''),
                tecnico_id:  (int)     $request->query('tecnico_id', 0),
                fecha_desde: (string)  $request->query('fecha_desde', ''),
                fecha_hasta: (string)  $request->query('fecha_hasta', ''),
            );

            $ordenes = $this->service->buscar($dto);

            if ($ordenes->isEmpty()) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'No se encontraron órdenes con ese criterio.',
                ]);
            }

            return response()->json([
                'ok'      => true,
                'total'   => $ordenes->count(),
                'ordenes' => $ordenes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
