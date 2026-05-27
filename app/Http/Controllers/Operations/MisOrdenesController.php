<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\CambiarEstadoOrdenRequest;
use App\Http\Requests\Operations\CambiarEstadoRepuestoRequest;
use App\Http\Requests\Operations\CambiarEstadoGarantiaRequest;
use App\Http\Requests\Operations\AsignarRepuestoOrdenRequest;
use App\Http\Requests\Operations\RevertirRepuestoOrdenRequest;
use App\Repositories\Inventory\RepuestoRepository;
use App\Services\Operations\GestionOrdenService;
use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\CambiarEstadoOrdenDTO;
use App\DTOs\Operations\CambiarEstadoRepuestoDTO;
use App\DTOs\Operations\CambiarEstadoGarantiaDTO;
use App\DTOs\Operations\AsignarRepuestoOrdenDTO;
use App\DTOs\Operations\RevertirRepuestoOrdenDTO;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Exception;

class MisOrdenesController extends Controller
{
    protected GestionOrdenService $service;
    protected OrdenRepository $repository;
    protected RepuestoRepository $repuestoRepository;

    public function __construct(
        GestionOrdenService $service,
        OrdenRepository $repository,
        RepuestoRepository $repuestoRepository
    )
    {
        $this->service = $service;
        $this->repository = $repository;
        $this->repuestoRepository = $repuestoRepository;
    }

    public function index(): View
    {
        $tecnicoId = session('tecnico_id');
        
        if (!$tecnicoId) {
            abort(403, 'Sesión de técnico no identificada.');
        }

        $ordenes = $this->repository->obtenerOrdenesPorTecnico($tecnicoId);
        $repuestos = $this->repuestoRepository->buscarParaOrden('', true);

        return view('operations.mis_ordenes.index', compact('ordenes', 'repuestos'));
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
            $esAdmin = $this->resolverEsAdmin();

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

    public function cambiarEstadoRepuesto(CambiarEstadoRepuestoRequest $request): JsonResponse
    {
        try {
            $dto = new CambiarEstadoRepuestoDTO(
                (int) $request->input('orden_id'),
                (string) $request->input('estado_repuesto')
            );

            $this->service->actualizarEstadoRepuesto(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin()
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Estado de repuesto actualizado.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cambiarEstadoGarantia(CambiarEstadoGarantiaRequest $request): JsonResponse
    {
        try {
            $dto = new CambiarEstadoGarantiaDTO(
                (int) $request->input('orden_id'),
                (string) $request->input('estado_garantia')
            );

            $this->service->actualizarEstadoGarantia(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin()
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Estado de garantia actualizado.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asignarRepuesto(AsignarRepuestoOrdenRequest $request): JsonResponse
    {
        try {
            $dto = new AsignarRepuestoOrdenDTO(
                (int) $request->input('orden_id'),
                (int) $request->input('repuesto_inventario_id')
            );

            $this->service->asignarRepuesto(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin()
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Repuesto asignado correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function revertirRepuesto(RevertirRepuestoOrdenRequest $request): JsonResponse
    {
        try {
            $dto = new RevertirRepuestoOrdenDTO(
                (int) $request->input('orden_id'),
                $request->filled('repuesto_id') ? (int) $request->input('repuesto_id') : null
            );

            $this->service->revertirRepuesto(
                $dto,
                (int) session('tecnico_id', 0),
                $this->resolverEsAdmin()
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Repuesto revertido correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function resolverEsAdmin(): bool
    {
        $permisos = (array) session('permisos', []);

        return (bool) session('es_superadmin', false)
            || (($permisos['ordenes_asignadas']['ver'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true)
            || (($permisos['usuarios']['crear'] ?? false) === true)
            || (($permisos['repuestos_admin']['ver'] ?? false) === true);
    }
}
