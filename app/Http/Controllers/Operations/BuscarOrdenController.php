<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\BuscarOrdenRequest;
use App\Services\Operations\BuscarOrdenService;
use App\DTOs\Operations\BuscarOrdenDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class BuscarOrdenController extends Controller
{
    public function __construct(
        protected BuscarOrdenService $service
    ) {
    }

    public function index(): View
    {
        return view('operations.ordenes.buscar');
    }

    public function listar(BuscarOrdenRequest $request): JsonResponse
    {
        try {
            $dto = new BuscarOrdenDTO(
                (string) $request->query('tipo', 'nro_orden'),
                (string) $request->query('q', ''),
                (int) session('sucursal_id', 0),
                session('es_superadmin') === true
            );

            $ordenes = $this->service->buscar($dto);
            if ($ordenes->isEmpty()) {
                return response()->json([
                    'ok' => false,
                    'error' => 'No se encontraron ordenes con ese criterio.',
                ]);
            }

            return response()->json([
                'ok' => true,
                'total' => $ordenes->count(),
                'ordenes' => $ordenes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

