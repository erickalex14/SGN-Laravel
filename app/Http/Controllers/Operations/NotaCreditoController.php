<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\GestionarNcDTO;
use App\DTOs\Operations\SolicitudNcDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GestionarNcRequest;
use App\Http\Requests\Operations\SolicitarNcRequest;
use App\Repositories\Operations\NotaCreditoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Services\Operations\NotaCreditoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotaCreditoController extends Controller
{
    protected NotaCreditoService $service;
    protected NotaCreditoRepository $ncRepository;
    protected OrdenRepository $ordenRepository;

    public function __construct(
        NotaCreditoService $service,
        NotaCreditoRepository $ncRepository,
        OrdenRepository $ordenRepository
    ) {
        $this->service = $service;
        $this->ncRepository = $ncRepository;
        $this->ordenRepository = $ordenRepository;
    }

    public function indexAdmin(): View
    {
        $solicitudes = $this->ncRepository->obtenerTodas();
        return view('operations.notas_credito.admin', compact('solicitudes'));
    }

    public function indexTecnico(): View
    {
        $tecnicoId = (int) session('tecnico_id');
        $permisos = (array) session('permisos', []);
        $esAdmin = (bool) session('es_superadmin', false)
            || (($permisos['repuestos_admin']['ver'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true)
            || (($permisos['usuarios']['crear'] ?? false) === true);

        $solicitudes = $this->ncRepository->obtenerPorTecnico($tecnicoId);
        $ordenes = $this->ordenRepository->obtenerOrdenesElegiblesParaNc($tecnicoId, $esAdmin);

        return view('operations.notas_credito.tecnico', compact('solicitudes', 'ordenes'));
    }

    public function solicitar(SolicitarNcRequest $request): JsonResponse
    {
        try {
            $permisos = (array) session('permisos', []);
            $esAdmin = (bool) session('es_superadmin', false)
                || (($permisos['repuestos_admin']['ver'] ?? false) === true)
                || (($permisos['usuarios_crear']['ver'] ?? false) === true)
                || (($permisos['usuarios']['crear'] ?? false) === true);

            $dto = new SolicitudNcDTO(
                (int) $request->input('orden_id'),
                (string) $request->input('asunto'),
                (string) $request->input('detalles'),
                (int) session('tecnico_id', 0),
                (string) (session('nombre_tecnico') ?? session('nombre') ?? session('usuario') ?? '')
            );

            $nroSolicitud = $this->service->solicitar($dto, $esAdmin);

            return response()->json([
                'ok' => true,
                'mensaje' => "Solicitud {$nroSolicitud} generada correctamente.",
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function gestionar(GestionarNcRequest $request): JsonResponse
    {
        $permisos = session('permisos', []);
        $puedeGestionar = session('es_superadmin') === true
            || (($permisos['notas_credito']['editar'] ?? false) === true);

        if (!$puedeGestionar) {
            return response()->json(['ok' => false, 'error' => 'No tienes permisos para autorizar notas de credito.']);
        }

        try {
            $dto = new GestionarNcDTO(
                (int) $request->input('solicitud_id'),
                (string) $request->input('estado'),
                (string) $request->input('motivo_rechazo'),
                (string) session('nombre')
            );

            $this->service->gestionar($dto);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Solicitud procesada correctamente.',
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
