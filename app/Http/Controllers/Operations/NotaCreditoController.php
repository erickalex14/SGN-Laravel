<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\SolicitarNcRequest;
use App\Http\Requests\Operations\GestionarNcRequest;
use App\Services\Operations\NotaCreditoService;
use App\Repositories\Operations\NotaCreditoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\SolicitudNcDTO;
use App\DTOs\Operations\GestionarNcDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

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

    // Muestra la vista del panel administrativo de Notas de Credito (Solo Admin)
    public function indexAdmin(): View
    {
        // Se asume que el Middleware ha validado el acceso a este metodo
        $solicitudes = $this->ncRepository->obtenerTodas();
        return view('operations.notas_credito.admin', compact('solicitudes'));
    }

    // Muestra la vista de "Mis Solicitudes" (Para Tecnicos)
    public function indexTecnico(): View
    {
        $tecnicoId = session('tecnico_id');
        $solicitudes = $this->ncRepository->obtenerPorTecnico($tecnicoId);
        
        // Obtenemos ordenes asignadas para el combobox de nueva solicitud
        $ordenes = $this->ordenRepository->obtenerOrdenesPorTecnico($tecnicoId);

        return view('operations.notas_credito.tecnico', compact('solicitudes', 'ordenes'));
    }

    // Endpoint para enviar una nueva solicitud
    public function solicitar(SolicitarNcRequest $request): JsonResponse
    {
        try {
            $dto = new SolicitudNcDTO(
                (int) $request->input('orden_id'),
                $request->input('asunto'),
                $request->input('detalles'),
                session('tecnico_id'),
                session('nombre') // Nombre del tecnico
            );

            $nroSolicitud = $this->service->solicitar($dto);

            return response()->json([
                'ok' => true,
                'mensaje' => "Solicitud {$nroSolicitud} generada correctamente."
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // Endpoint para Aprobar/Rechazar (Solo Admin)
    public function gestionar(GestionarNcRequest $request): JsonResponse
    {
        // Validacion de seguridad adicional (Solo admins pueden gestionar)
        if (!session('acceso_nc') && !session('es_superadmin')) {
            return response()->json(['ok' => false, 'error' => 'No tienes permisos para autorizar notas de crédito.']);
        }

        try {
            $dto = new GestionarNcDTO(
                (int) $request->input('solicitud_id'),
                $request->input('estado'),
                $request->input('motivo_rechazo'),
                session('nombre') // Nombre del admin
            );

            $this->service->gestionar($dto);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Solicitud procesada correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}