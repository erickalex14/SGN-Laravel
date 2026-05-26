<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\CambiarEstadoOrdenRequest;
use App\Services\Operations\GestionOrdenService;
use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Exception;

class MisOrdenesController extends Controller
{
    protected GestionOrdenService $service;
    protected OrdenRepository $repository;

    public function __construct(GestionOrdenService $service, OrdenRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(): View
    {
        $tecnicoId = session('tecnico_id');
        
        if (!$tecnicoId) {
            abort(403, 'Sesión de técnico no identificada.');
        }

        $ordenes = $this->repository->obtenerOrdenesPorTecnico($tecnicoId);

        return view('operations.mis_ordenes.index', compact('ordenes'));
    }

    public function cambiarEstado(CambiarEstadoOrdenRequest $request): JsonResponse
    {
        try {
            $dto = new CambiarEstadoOrdenDTO(
                (int) $request->input('id'),
                (string) $request->input('estado'),
                $request->input('nc_asunto'),
                $request->input('nc_detalles')
            );

            $usuarioModificacionId = (int) session('tecnico_id', 0);
            $tecnicoNombre = (string) (session('nombre_tecnico') ?? session('nombre') ?? session('usuario') ?? '');
            $permisos = (array) session('permisos', []);
            $esAdmin = (bool) session('es_superadmin', false)
                || (($permisos['ordenes_asignadas']['ver'] ?? false) === true)
                || (($permisos['usuarios_crear']['ver'] ?? false) === true)
                || (($permisos['usuarios']['crear'] ?? false) === true);

            $this->service->actualizarEstado($dto, $usuarioModificacionId, $tecnicoNombre, $esAdmin);

            return response()->json([
                'ok'      => true,
                'mensaje' => 'El estado de la orden ha sido actualizado correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
