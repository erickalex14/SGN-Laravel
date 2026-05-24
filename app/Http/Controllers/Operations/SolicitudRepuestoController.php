<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarSolicitudRepuestoRequest;
use App\Http\Requests\Operations\GestionarSolicitudRepuestoRequest;
use App\Services\Operations\SolicitudRepuestoService;
use App\Repositories\Operations\SolicitudRepuestoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\SolicitudRepuestoDTO;
use App\DTOs\Operations\GestionarSolicitudRepuestoDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class SolicitudRepuestoController extends Controller
{
    protected SolicitudRepuestoService $service;
    protected SolicitudRepuestoRepository $srRepository;
    protected OrdenRepository $ordenRepository;

    public function __construct(
        SolicitudRepuestoService $service,
        SolicitudRepuestoRepository $srRepository,
        OrdenRepository $ordenRepository
    ) {
        $this->service = $service;
        $this->srRepository = $srRepository;
        $this->ordenRepository = $ordenRepository;
    }

    public function indexAdmin(): View
    {
        $solicitudes = $this->srRepository->obtenerTodas();
        return view('operations.solicitudes_repuestos.admin', compact('solicitudes'));
    }

    public function indexTecnico(): View
    {
        $tecnicoId = session('tecnico_id');
        $solicitudes = $this->srRepository->obtenerPorTecnico($tecnicoId);
        $ordenes = $this->ordenRepository->obtenerOrdenesPorTecnico($tecnicoId);
        
        return view('operations.solicitudes_repuestos.tecnico', compact('solicitudes', 'ordenes'));
    }

    public function solicitar(GuardarSolicitudRepuestoRequest $request): JsonResponse
    {
        try {
            $dto = new SolicitudRepuestoDTO(
                (int) $request->input('orden_id'),
                session('tecnico_id'),
                session('nombre'),
                $request->input('repuesto_nombre'),
                $request->input('nro_parte'),
                $request->input('link_compra'),
                (int) $request->input('cantidad'),
                $request->input('descripcion'),
                $request->input('repuesto_inv_id') ? (int) $request->input('repuesto_inv_id') : null
            );

            $nro = $this->service->registrarSolicitud($dto);

            return response()->json(['ok' => true, 'mensaje' => "Ticket {$nro} enviado a bodega."]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function gestionar(GestionarSolicitudRepuestoRequest $request): JsonResponse
    {
        try {
            $dto = new GestionarSolicitudRepuestoDTO(
                (int) $request->input('solicitud_id'),
                $request->input('estado'),
                $request->input('motivo_rechazo'),
                session('nombre')
            );

            $this->service->gestionar($dto);

            return response()->json(['ok' => true, 'mensaje' => 'Solicitud procesada con éxito.']);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}