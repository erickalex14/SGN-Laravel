<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\GestionarSolicitudRepuestoDTO;
use App\DTOs\Operations\SolicitudRepuestoDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GestionarSolicitudRepuestoRequest;
use App\Http\Requests\Operations\GuardarSolicitudRepuestoRequest;
use App\Models\Identity\Usuario;
use App\Repositories\Inventory\RepuestoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\SolicitudRepuestoRepository;
use App\Services\Operations\SolicitudRepuestoService;
use App\Services\Identity\ActividadDiariaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SolicitudRepuestoController extends Controller
{
    protected SolicitudRepuestoService $service;

    protected SolicitudRepuestoRepository $srRepository;

    protected OrdenRepository $ordenRepository;

    protected RepuestoRepository $repuestoRepository;
    protected ActividadDiariaService $actividadService;

    public function __construct(
        SolicitudRepuestoService $service,
        SolicitudRepuestoRepository $srRepository,
        OrdenRepository $ordenRepository,
        RepuestoRepository $repuestoRepository,
        ActividadDiariaService $actividadService
    ) {
        $this->service = $service;
        $this->srRepository = $srRepository;
        $this->ordenRepository = $ordenRepository;
        $this->repuestoRepository = $repuestoRepository;
        $this->actividadService = $actividadService;
    }

    public function indexAdmin(): View
    {
        $sucursalId = null;
        if (! $this->esSuperAdminOMaster()) {
            $sucursalId = (int) session('sucursal_id');
        }

        $solicitudes = $this->srRepository->obtenerTodas($sucursalId);
        $repuestos = $this->repuestoRepository->buscarParaOrden('', true);

        return view('operations.solicitudes_repuestos.admin', compact('solicitudes', 'repuestos'));
    }

    public function indexTecnico(): View
    {
        $tecnicoId = (int) session('tecnico_id', 0);
        $esAdmin = $this->esAdminRepuestos();

        $solicitudes = $this->srRepository->obtenerPorTecnico($tecnicoId);
        $ordenes = $this->ordenRepository->obtenerOrdenesElegiblesParaRepuesto($tecnicoId, $esAdmin);

        return view('operations.solicitudes_repuestos.tecnico', compact('solicitudes', 'ordenes'));
    }

    public function solicitar(GuardarSolicitudRepuestoRequest $request): JsonResponse
    {
        try {
            $esAdmin = $this->esAdminRepuestos();

            $tecnicoId = (int) session('tecnico_id', 0);
            $tecnico = Usuario::find($tecnicoId);
            $tecnicoNombre = $tecnico ? $tecnico->nombre_tecnico : (session('nombre') ?? session('usuario') ?? '');

            $dto = new SolicitudRepuestoDTO(
                (int) $request->input('orden_id'),
                $tecnicoId,
                (string) $tecnicoNombre,
                $request->input('repuesto_nombre'),
                $request->input('nro_parte'),
                $request->input('link_compra'),
                (int) $request->input('cantidad'),
                $request->input('descripcion'),
                $request->input('repuesto_inv_id') ? (int) $request->input('repuesto_inv_id') : null
            );

            $nro = $this->service->registrarSolicitud($dto, $esAdmin);

            $orden = \App\Models\Operations\Orden::with(['cliente', 'equipo'])->find($dto->ordenId);
            if ($orden) {
                $this->actividadService->registrar(
                    usuarioId: (int) session('tecnico_id'),
                    tipoAccion: 'solicitar_repuesto',
                    descripcion: "Solicitó repuesto a bodega para orden #{$orden->nro_orden}",
                    modulo: 'repuestos',
                    referenciaId: $orden->id,
                    referenciaTipo: 'orden',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? '',
                        'serie' => $orden->equipo?->serie ?? 'sn',
                        'marca' => $orden->equipo?->marca ?? 'sn',
                        'tipo' => $orden->equipo?->tipo ?? 'sn',
                        'repuesto_nombre' => $dto->repuestoNombre,
                        'cantidad' => $dto->cantidad,
                        'nro_ticket' => $nro
                    ]
                );
            }

            return response()->json(['ok' => true, 'mensaje' => "Ticket {$nro} enviado a bodega."]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function gestionar(GestionarSolicitudRepuestoRequest $request): JsonResponse
    {
        if (! $this->esSuperAdminOMaster()) {
            $solicitud = $this->srRepository->buscarPorId((int) $request->input('solicitud_id'));
            if ($solicitud && $solicitud->orden && (int) $solicitud->orden->sucursal_id !== (int) session('sucursal_id')) {
                return response()->json(['ok' => false, 'error' => 'No tienes permisos para gestionar solicitudes de repuestos de otra sucursal.']);
            }
        }

        try {
            $dto = new GestionarSolicitudRepuestoDTO(
                (int) $request->input('solicitud_id'),
                $request->input('estado'),
                $request->input('motivo_rechazo'),
                session('nombre'),
                $request->filled('repuesto_id') ? (int) $request->input('repuesto_id') : null,
                $request->filled('cantidad') ? (int) $request->input('cantidad') : null
            );

            $this->service->gestionar($dto);

            return response()->json(['ok' => true, 'mensaje' => 'Solicitud procesada con éxito.']);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function imprimir(int $id): View
    {
        $solicitud = $this->srRepository->buscarPorIdConRelaciones($id);
        abort_if(! $solicitud, 404);

        $tecnicoId = (int) session('tecnico_id', 0);
        $esAdmin = $this->esAdminRepuestos();

        $esPropietario = (int) $solicitud->tecnico_id === $tecnicoId;
        abort_unless($esAdmin || $esPropietario, 403);

        if (! $this->esSuperAdminOMaster() && ! $esPropietario) {
            if ($solicitud->orden && (int) $solicitud->orden->sucursal_id !== (int) session('sucursal_id')) {
                abort(403, 'No tienes permisos para ver solicitudes de repuestos de otra sucursal.');
            }
        }

        return view('operations.solicitudes_repuestos.imprimir', compact('solicitud'));
    }

    private function esAdminRepuestos(): bool
    {
        $permisos = (array) session('permisos', []);

        return (bool) session('es_superadmin', false)
            || (($permisos['repuestos_admin']['ver'] ?? false) === true)
            || (($permisos['repuestos_admin']['editar'] ?? false) === true);
    }

    private function esSuperAdminOMaster(): bool
    {
        $usuario = auth()->user();
        if (! $usuario) {
            return false;
        }

        $rol = $usuario->rol ? mb_strtolower(trim((string) $usuario->rol->rol)) : '';
        $grupo = $usuario->grupo ? mb_strtolower(trim((string) $usuario->grupo->nombre)) : '';
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        $superRoles = [
            'admin master', 'administrador master', 'superadmin', 'superadministrador',
        ];

        return session('es_superadmin') === true
            || in_array($rol, $superRoles, true)
            || in_array($grupo, $superRoles, true)
            || in_array($sessionGrupo, $superRoles, true);
    }
}
