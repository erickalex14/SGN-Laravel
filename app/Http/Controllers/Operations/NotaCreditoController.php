<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\GestionarNcDTO;
use App\DTOs\Operations\SolicitudNcDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GestionarNcRequest;
use App\Http\Requests\Operations\SolicitarNcRequest;
use App\Models\Operations\SolicitudNc;
use App\Repositories\Directory\SucursalClienteRepository;
use App\Repositories\Directory\SucursalRepository;
use App\Repositories\Operations\NotaCreditoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Services\Operations\NotaCreditoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotaCreditoController extends Controller
{
    protected NotaCreditoService $service;

    protected NotaCreditoRepository $ncRepository;

    protected OrdenRepository $ordenRepository;

    protected SucursalClienteRepository $sucursalClienteRepo;

    public function __construct(
        NotaCreditoService $service,
        NotaCreditoRepository $ncRepository,
        OrdenRepository $ordenRepository,
        SucursalClienteRepository $sucursalClienteRepo
    ) {
        $this->service = $service;
        $this->ncRepository = $ncRepository;
        $this->ordenRepository = $ordenRepository;
        $this->sucursalClienteRepo = $sucursalClienteRepo;
    }

    public function indexAdmin(): View
    {
        $sucursalId = null;
        if (! $this->esSuperAdminOMaster()) {
            $sucursalId = (int) session('sucursal_id');
        }

        $solicitudes = $this->ncRepository->obtenerTodas($sucursalId);
        $sucursales = app(SucursalRepository::class)->obtenerTodas();

        if ($sucursalId !== null && $sucursalId > 0) {
            $sucursales = $sucursales->where('id', $sucursalId);
        }

        return view('operations.notas_credito.admin', compact('solicitudes', 'sucursales'));
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

        if (! $puedeGestionar) {
            return response()->json(['ok' => false, 'error' => 'No tienes permisos para autorizar notas de credito.']);
        }

        if (! $this->esSuperAdminOMaster()) {
            $solicitud = $this->ncRepository->buscarPorId((int) $request->input('solicitud_id'));
            if ($solicitud && $solicitud->orden && (int) $solicitud->orden->sucursal_id !== (int) session('sucursal_id')) {
                return response()->json(['ok' => false, 'error' => 'No tienes permisos para autorizar notas de credito de otra sucursal.']);
            }
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

    public function imprimir(int $id): View
    {
        $solicitud = $this->ncRepository->buscarPorIdConRelaciones($id);
        abort_if(! $solicitud, 404);

        $tecnicoId = (int) session('tecnico_id', 0);
        $permisos = (array) session('permisos', []);
        $esAdmin = (bool) session('es_superadmin', false)
            || (($permisos['notas_credito']['ver'] ?? false) === true)
            || (($permisos['notas_credito']['editar'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true);

        $esPropietario = (int) $solicitud->tecnico_id === $tecnicoId;
        abort_unless($esAdmin || $esPropietario, 403);

        if (! $this->esSuperAdminOMaster() && ! $esPropietario) {
            if ($solicitud->orden && (int) $solicitud->orden->sucursal_id !== (int) session('sucursal_id')) {
                abort(403, 'No tienes permisos para ver notas de crédito de otra sucursal.');
            }
        }

        $orden = $this->ordenRepository->obtenerOrdenCompleta($solicitud->orden_id);
        abort_if(! $orden, 404);

        $orden->loadMissing([
            'equipo.series',
            'equipo.tipoServicio',
            'tecnico',
            'sucursal',
            'cas',
            'usuarioIngreso',
            'repuestoInventario',
            'preciosOrden',
            'solicitudesNc',
        ]);

        $nombreSucursalCliente = '-';
        $nroSuc = $orden->nro_sucursal_cliente;
        if ($nroSuc !== null && $nroSuc !== '') {
            if ($nroSuc === '999' || $nroSuc === '999 - SERVICIO EXTERNO') {
                $nombreSucursalCliente = '999 - SERVICIO EXTERNO';
            } else {
                // 1. Prioridad: Buscar por código
                $suc = $this->sucursalClienteRepo->obtenerTodas()->firstWhere('codigo', $nroSuc);
                if ($suc) {
                    $nombreSucursalCliente = $suc->codigo.' - '.$suc->nombre;
                } else {
                    // 2. Fallback: Si es numérico (histórico), buscar por número
                    $numeroInt = (int) $nroSuc;
                    if ($numeroInt > 0) {
                        $suc = $this->sucursalClienteRepo->obtenerTodas()->firstWhere('numero', $numeroInt);
                        if ($suc) {
                            $nombreSucursalCliente = $suc->codigo.' - '.$suc->nombre;
                        } else {
                            $nombreSucursalCliente = 'Nro. '.str_pad((string) $numeroInt, 3, '0', STR_PAD_LEFT);
                        }
                    } else {
                        $nombreSucursalCliente = $nroSuc;
                    }
                }
            }
        }

        return view('operations.ordenes.imprimir', [
            'orden' => $orden,
            'nombreSucursalCliente' => $nombreSucursalCliente,
            'solicitudNc' => $solicitud,
        ]);
    }

    public function imprimirReporte(Request $request): View
    {
        $q = trim((string) $request->input('q'));
        $estado = trim((string) $request->input('estado'));
        $sucursalId = (int) $request->input('sucursal_id');
        $tecnico = trim((string) $request->input('tecnico'));
        $desde = trim((string) $request->input('desde'));
        $hasta = trim((string) $request->input('hasta'));

        $query = SolicitudNc::with(['orden.sucursal', 'tecnico']);

        if (! $this->esSuperAdminOMaster()) {
            $branchId = (int) session('sucursal_id');
            $query->whereHas('orden', function ($o) use ($branchId) {
                $o->where('sucursal_id', $branchId);
            });
        } else {
            if ($sucursalId > 0) {
                $query->whereHas('orden', function ($o) use ($sucursalId) {
                    $o->where('sucursal_id', $sucursalId);
                });
            }
        }

        if (! empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nro_solicitud', 'like', "%{$q}%")
                    ->orWhere('asunto', 'like', "%{$q}%")
                    ->orWhereHas('orden', function ($o) use ($q) {
                        $o->where('nro_orden', 'like', "%{$q}%")
                            ->orWhere('nro_factura', 'like', "%{$q}%");
                    });
            });
        }

        if (! empty($estado)) {
            $query->where('estado', $estado);
        }

        if (! empty($tecnico)) {
            $query->where(function ($sub) use ($tecnico) {
                $sub->where('tecnico_nombre', 'like', "%{$tecnico}%")
                    ->orWhereHas('tecnico', function ($t) use ($tecnico) {
                        $t->where('nombre_tecnico', 'like', "%{$tecnico}%");
                    });
            });
        }

        if (! empty($desde)) {
            $query->whereDate('creado_en', '>=', $desde);
        }

        if (! empty($hasta)) {
            $query->whereDate('creado_en', '<=', $hasta);
        }

        $solicitudes = $query->orderBy('creado_en', 'desc')->get();

        return view('operations.notas_credito.imprimir_reporte', compact('solicitudes'));
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
