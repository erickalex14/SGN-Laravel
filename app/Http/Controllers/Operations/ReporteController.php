<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\ReporteFiltroDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\FiltrarReporteRequest;
use App\Models\Operations\Equipo;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Identity\Usuario;
use App\Repositories\Directory\SucursalRepository;
use App\Repositories\Directory\CasRepository;
use App\Repositories\Identity\UsuarioRepository;
use App\Services\Operations\ReporteService;
use App\Services\Identity\ActividadDiariaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReporteController extends Controller
{
    protected ReporteService $service;
    protected UsuarioRepository $usuarioRepo;
    protected SucursalRepository $sucursalRepo;
    protected CasRepository $casRepo;
    protected ActividadDiariaService $actividadService;

    public function __construct(
        ReporteService $service,
        UsuarioRepository $usuarioRepo,
        SucursalRepository $sucursalRepo,
        CasRepository $casRepo,
        ActividadDiariaService $actividadService
    ) {
        $this->service = $service;
        $this->usuarioRepo = $usuarioRepo;
        $this->sucursalRepo = $sucursalRepo;
        $this->casRepo = $casRepo;
        $this->actividadService = $actividadService;
    }

    public function index(): View
    {
        $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $esMaster = true; // En el panel de Reportes Admin se visualiza la información de todas las sucursales
        $sucursalSesion = (int) session('sucursal_id', 0);

        // Técnicos, administradores y superadministradores de TODAS las sucursales (excluyendo admin solo lectura y generadores de tickets)
        $tecnicos = Usuario::tecnicosOperativos()
            ->with('sucursalPrincipal')
            ->orderBy('nombre_tecnico')
            ->get();

        // Permitir ver todas las sucursales de Novitec universalmente en reportes
        $sucursales = $this->sucursalRepo->obtenerTodas();

        // Obtener todos los CAS activos para el filtrado en reportes
        $cas = $this->casRepo->obtenerActivos();

        $estadosOrdenPersonal = Orden::select('estado_orden')
            ->distinct()
            ->orderBy('estado_orden')
            ->pluck('estado_orden');

        $estadosOrdenEmpresa = OrdenEmpresa::select('estado')
            ->whereNotNull('estado')
            ->where('estado', '<>', '')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        $estados = $estadosOrdenPersonal
            ->merge($estadosOrdenEmpresa)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->unique()
            ->values();

        $estadosRepuesto = Orden::select('estado_repuesto')
            ->whereNotNull('estado_repuesto')
            ->where('estado_repuesto', '<>', '')
            ->distinct()
            ->orderBy('estado_repuesto')
            ->pluck('estado_repuesto');

        $estadosGarantia = Orden::select('estado_garantia')
            ->whereNotNull('estado_garantia')
            ->where('estado_garantia', '<>', '')
            ->distinct()
            ->orderBy('estado_garantia')
            ->pluck('estado_garantia');

        $motivosOrdenPersonal = Orden::select('motivo_ingreso')
            ->whereNotNull('motivo_ingreso')
            ->where('motivo_ingreso', '<>', '')
            ->distinct()
            ->orderBy('motivo_ingreso')
            ->pluck('motivo_ingreso');

        $motivosOrdenEmpresa = OrdenEmpresa::select('subtipo')
            ->whereNotNull('subtipo')
            ->where('subtipo', '<>', '')
            ->distinct()
            ->orderBy('subtipo')
            ->pluck('subtipo');

        $motivos = $motivosOrdenPersonal
            ->merge($motivosOrdenEmpresa)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->unique()
            ->values();

        $marcas = Equipo::select('marca')
            ->whereNotNull('marca')
            ->where('marca', '<>', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        $tiposEquipo = Equipo::select('tipo')
            ->whereNotNull('tipo')
            ->where('tipo', '<>', '')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        $empresas = \App\Models\Directory\Empresa::orderBy('nombre')->get();

        return view('operations.reportes.index', compact(
            'tecnicos',
            'sucursales',
            'cas',
            'estados',
            'estadosRepuesto',
            'estadosGarantia',
            'motivos',
            'marcas',
            'tiposEquipo',
            'empresas',
            'esMaster'
        ));
    }

    public function filtrar(FiltrarReporteRequest $request): JsonResponse
    {
        try {
            $dto = new ReporteFiltroDTO(
                $request->input('fecha_inicio'),
                $request->input('fecha_fin'),
                $request->input('estado'),
                $request->input('estado_repuesto'),
                $request->input('estado_garantia'),
                $request->input('motivo_ingreso'),
                $request->input('marca'),
                $request->input('tipo_equipo'),
                $request->input('tipo_orden'),
                $request->input('tecnico_id') ? (int) $request->input('tecnico_id') : null,
                $request->input('sucursal_id') ? (int) $request->input('sucursal_id') : null,
                $request->input('cas_id') ? (int) $request->input('cas_id') : null,
                $request->input('empresa_id') ? (int) $request->input('empresa_id') : null,
                $request->input('garantia_tipo')
            );

            $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
            $esMaster = true;
            $sucursalSesion = (int) session('sucursal_id', 0);

            $resultados = $this->service->generarReporte(
                $dto,
                (int) session('tecnico_id'),
                $esMaster,
                $sucursalSesion
            );

            return response()->json([
                'ok' => true,
                'data' => $resultados,
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    private function filtrarTecnicosSegunRol(Collection $usuarios, bool $esMaster, int $sucursalSesion): Collection
    {
        return $usuarios
            ->filter(function ($usuario) use ($esMaster, $sucursalSesion) {
                if (!$usuario->activo || empty($usuario->nombre_tecnico)) {
                    return false;
                }

                $grupoId = (int) ($usuario->grupo_id ?? 0);
                $rolId = (int) ($usuario->rol_id ?? 0);

                // Excluir estrictamente Admin Solo Lectura (8) y Generadores de Ticket / Tiendas (9)
                if (in_array($grupoId, [8, 9], true) || !empty($usuario->sucursal_cliente_id)) {
                    return false;
                }

                // Debe ser Superadmin (1), Admin (2), Tecnico Master (3), Tecnico (4), Sistemas (5)
                $esOperativo = in_array($grupoId, [1, 2, 3, 4, 5], true) || in_array($rolId, [1, 2, 3, 4], true);
                if (!$esOperativo) {
                    return false;
                }

                if ($esMaster) {
                    return true;
                }

                return (int) ($usuario->sucursal_id ?? 0) === $sucursalSesion;
            })
            ->sortBy(fn ($usuario) => mb_strtolower((string) ($usuario->nombre_tecnico ?? '')))
            ->values();
    }

    public function indexTecnico(): View
    {
        $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $esTecnicoMaster = $rol === 'tecnico master';
        $tecnicoSesionId = (int) session('tecnico_id', 0);
        $sucursalSesion = (int) session('sucursal_id', 0);
        
        $esSuperadmin = session('es_superadmin') === true;

        if ($esSuperadmin || $rol === 'master' || $rol === 'admin') {
            $tecnicos = $this->usuarioRepo->obtenerTodosConRelaciones()
                ->filter(function ($usuario) {
                    $uRol = mb_strtolower(trim((string) ($usuario->rol->rol ?? $usuario->grupo->nombre ?? '')));
                    return in_array($uRol, ['tecnico', 'tecnico master'], true);
                })
                ->sortBy(fn ($usuario) => mb_strtolower((string) ($usuario->nombre_tecnico ?? '')))
                ->values();
        } elseif ($esTecnicoMaster) {
            $tecnicos = $this->usuarioRepo->obtenerTodosConRelaciones()
                ->filter(function ($usuario) use ($sucursalSesion) {
                    $uRol = mb_strtolower(trim((string) ($usuario->rol->rol ?? $usuario->grupo->nombre ?? '')));
                    return in_array($uRol, ['tecnico', 'tecnico master'], true)
                        && (int) ($usuario->sucursal_id ?? 0) === $sucursalSesion;
                })
                ->sortBy(fn ($usuario) => mb_strtolower((string) ($usuario->nombre_tecnico ?? '')))
                ->values();
        } else {
            $tecnicos = $this->usuarioRepo->obtenerTodosConRelaciones()
                ->filter(function ($usuario) use ($tecnicoSesionId) {
                    return (int) $usuario->id === $tecnicoSesionId;
                })
                ->values();
        }

        if ($esSuperadmin || $rol === 'master' || $rol === 'admin') {
            $sucursales = $this->sucursalRepo->obtenerTodas();
        } else {
            $sucursales = $this->sucursalRepo->obtenerTodas()
                ->filter(fn ($s) => (int) $s->id === $sucursalSesion)
                ->values();
        }

        $cas = $this->casRepo->obtenerActivos();

        $estadosOrdenPersonal = Orden::select('estado_orden')
            ->distinct()
            ->orderBy('estado_orden')
            ->pluck('estado_orden');

        $estadosOrdenEmpresa = OrdenEmpresa::select('estado')
            ->whereNotNull('estado')
            ->where('estado', '<>', '')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        $estados = $estadosOrdenPersonal
            ->merge($estadosOrdenEmpresa)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->unique()
            ->values();

        $estadosRepuesto = Orden::select('estado_repuesto')
            ->whereNotNull('estado_repuesto')
            ->where('estado_repuesto', '<>', '')
            ->distinct()
            ->orderBy('estado_repuesto')
            ->pluck('estado_repuesto');

        $estadosGarantia = Orden::select('estado_garantia')
            ->whereNotNull('estado_garantia')
            ->where('estado_garantia', '<>', '')
            ->distinct()
            ->orderBy('estado_garantia')
            ->pluck('estado_garantia');

        $motivosOrdenPersonal = Orden::select('motivo_ingreso')
            ->whereNotNull('motivo_ingreso')
            ->where('motivo_ingreso', '<>', '')
            ->distinct()
            ->orderBy('motivo_ingreso')
            ->pluck('motivo_ingreso');

        $motivosOrdenEmpresa = OrdenEmpresa::select('subtipo')
            ->whereNotNull('subtipo')
            ->where('subtipo', '<>', '')
            ->distinct()
            ->orderBy('subtipo')
            ->pluck('subtipo');

        $motivos = $motivosOrdenPersonal
            ->merge($motivosOrdenEmpresa)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->unique()
            ->values();

        $marcas = Equipo::select('marca')
            ->whereNotNull('marca')
            ->where('marca', '<>', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        $tiposEquipo = Equipo::select('tipo')
            ->whereNotNull('tipo')
            ->where('tipo', '<>', '')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return view('operations.reportes.tecnico', compact(
            'tecnicos',
            'sucursales',
            'cas',
            'estados',
            'estadosRepuesto',
            'estadosGarantia',
            'motivos',
            'marcas',
            'tiposEquipo',
            'esTecnicoMaster',
            'rol',
            'tecnicoSesionId',
            'sucursalSesion'
        ));
    }

    public function filtrarTecnico(FiltrarReporteRequest $request): JsonResponse
    {
        try {
            $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
            $esTecnicoMaster = $rol === 'tecnico master';
            $tecnicoSesionId = (int) session('tecnico_id', 0);
            $sucursalSesion = (int) session('sucursal_id', 0);
            $esSuperadmin = session('es_superadmin') === true;

            if ($esSuperadmin || $rol === 'master' || $rol === 'admin') {
                $tecnicoIdFilter = $request->input('tecnico_id') ? (int) $request->input('tecnico_id') : null;
                $sucursalIdFilter = $request->input('sucursal_id') ? (int) $request->input('sucursal_id') : null;
            } elseif ($esTecnicoMaster) {
                $sucursalIdFilter = $sucursalSesion;
                $tecnicoIdFilter = $request->input('tecnico_id') ? (int) $request->input('tecnico_id') : null;
                
                if ($tecnicoIdFilter) {
                    $tecnicoObj = \App\Models\Identity\Usuario::find($tecnicoIdFilter);
                    if (!$tecnicoObj || (int) $tecnicoObj->sucursal_id !== $sucursalSesion) {
                        $tecnicoIdFilter = -1;
                    }
                }
            } else {
                $tecnicoIdFilter = $tecnicoSesionId;
                $sucursalIdFilter = $sucursalSesion;
            }

            $dto = new ReporteFiltroDTO(
                $request->input('fecha_inicio'),
                $request->input('fecha_fin'),
                $request->input('estado'),
                $request->input('estado_repuesto'),
                $request->input('estado_garantia'),
                $request->input('motivo_ingreso'),
                $request->input('marca'),
                $request->input('tipo_equipo'),
                $request->input('tipo_orden'),
                $tecnicoIdFilter,
                $sucursalIdFilter,
                $request->input('cas_id') ? (int) $request->input('cas_id') : null,
                $request->input('empresa_id') ? (int) $request->input('empresa_id') : null,
                $request->input('garantia_tipo')
            );

            $resultados = $this->service->generarReporte(
                $dto,
                (int) session('tecnico_id'),
                false,
                $sucursalSesion
            );

            return response()->json([
                'ok' => true,
                'data' => $resultados,
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function imprimir(\Illuminate\Http\Request $request): \Illuminate\View\View
    {
        $dto = new ReporteFiltroDTO(
            $request->input('fecha_inicio'),
            $request->input('fecha_fin'),
            $request->input('estado'),
            $request->input('estado_repuesto'),
            $request->input('estado_garantia'),
            $request->input('motivo_ingreso'),
            $request->input('marca'),
            $request->input('tipo_equipo'),
            $request->input('tipo_orden'),
            $request->input('tecnico_id') ? (int) $request->input('tecnico_id') : null,
            $request->input('sucursal_id') ? (int) $request->input('sucursal_id') : null,
            $request->input('cas_id') ? (int) $request->input('cas_id') : null,
            $request->input('empresa_id') ? (int) $request->input('empresa_id') : null
        );

        $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
            $esMaster = true;
        $sucursalSesion = (int) session('sucursal_id', 0);

        $resultados = $this->service->generarReporte(
            $dto,
            (int) session('tecnico_id'),
            $esMaster,
            $sucursalSesion
        );

        $buscar = trim((string) $request->input('buscar'));
        if ($buscar !== '') {
            $buscarLower = mb_strtolower($buscar);
            $resultados = $resultados->filter(function($r) use ($buscarLower) {
                return mb_strpos(mb_strtolower($r['nro_orden'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['cliente_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['identificacion'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['cliente_telefono'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['equipo_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['marca'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['tipo_equipo'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['serie'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['tecnico_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['sucursal_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['sucursal_cliente'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['motivo_ingreso'] ?? ''), $buscarLower) !== false;
            });
        }

        $filtrosTxt = $this->obtenerTextoFiltros($request);

        return view('operations.reportes.imprimir', [
            'resultados' => $resultados,
            'filtrosTxt' => $filtrosTxt,
            'titulo' => 'REPORTE DE ÓRDENES DE SERVICIO'
        ]);
    }

    public function imprimirTecnico(\Illuminate\Http\Request $request): \Illuminate\View\View
    {
        $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $esTecnicoMaster = $rol === 'tecnico master';
        $tecnicoSesionId = (int) session('tecnico_id', 0);
        $sucursalSesion = (int) session('sucursal_id', 0);
        $esSuperadmin = session('es_superadmin') === true;

        if ($esSuperadmin || $rol === 'master' || $rol === 'admin') {
            $tecnicoIdFilter = $request->input('tecnico_id') ? (int) $request->input('tecnico_id') : null;
            $sucursalIdFilter = $request->input('sucursal_id') ? (int) $request->input('sucursal_id') : null;
        } elseif ($esTecnicoMaster) {
            $sucursalIdFilter = $sucursalSesion;
            $tecnicoIdFilter = $request->input('tecnico_id') ? (int) $request->input('tecnico_id') : null;
            
            if ($tecnicoIdFilter) {
                $tecnicoObj = \App\Models\Identity\Usuario::find($tecnicoIdFilter);
                if (!$tecnicoObj || (int) $tecnicoObj->sucursal_id !== $sucursalSesion) {
                    $tecnicoIdFilter = -1;
                }
            }
        } else {
            $tecnicoIdFilter = $tecnicoSesionId;
            $sucursalIdFilter = $sucursalSesion;
        }

        $dto = new ReporteFiltroDTO(
            $request->input('fecha_inicio'),
            $request->input('fecha_fin'),
            $request->input('estado'),
            $request->input('estado_repuesto'),
            $request->input('estado_garantia'),
            $request->input('motivo_ingreso'),
            $request->input('marca'),
            $request->input('tipo_equipo'),
            $request->input('tipo_orden'),
            $tecnicoIdFilter,
            $sucursalIdFilter,
            $request->input('cas_id') ? (int) $request->input('cas_id') : null,
            $request->input('empresa_id') ? (int) $request->input('empresa_id') : null
        );

        $resultados = $this->service->generarReporte(
            $dto,
            (int) session('tecnico_id'),
            false,
            $sucursalSesion
        );

        $buscar = trim((string) $request->input('buscar'));
        if ($buscar !== '') {
            $buscarLower = mb_strtolower($buscar);
            $resultados = $resultados->filter(function($r) use ($buscarLower) {
                return mb_strpos(mb_strtolower($r['nro_orden'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['cliente_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['identificacion'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['cliente_telefono'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['equipo_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['marca'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['tipo_equipo'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['serie'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['tecnico_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['sucursal_nombre'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['sucursal_cliente'] ?? ''), $buscarLower) !== false
                    || mb_strpos(mb_strtolower($r['motivo_ingreso'] ?? ''), $buscarLower) !== false;
            });
        }

        $filtrosTxt = $this->obtenerTextoFiltros($request);

        $this->actividadService->registrar(
            usuarioId: (int) session('tecnico_id'),
            tipoAccion: 'imprimir_reporte_tecnico',
            descripcion: 'Imprimió reporte de órdenes técnica',
            modulo: 'reportes',
            metadata: [
                'filtros' => $filtrosTxt,
                'cantidad_registros' => $resultados->count()
            ]
        );

        return view('operations.reportes.imprimir', [
            'resultados' => $resultados,
            'filtrosTxt' => $filtrosTxt,
            'titulo' => 'REPORTE DE ÓRDENES DE SERVICIO - TÉCNICO'
        ]);
    }

    private function obtenerTextoFiltros(\Illuminate\Http\Request $request): array
    {
        $filtrosTxt = [];
        if ($request->input('fecha_inicio')) {
            $filtrosTxt[] = 'Desde: ' . \Carbon\Carbon::parse($request->input('fecha_inicio'))->format('d/m/Y');
        }
        if ($request->input('fecha_fin')) {
            $filtrosTxt[] = 'Hasta: ' . \Carbon\Carbon::parse($request->input('fecha_fin'))->format('d/m/Y');
        }
        if ($request->input('estado')) {
            $filtrosTxt[] = 'Estado: ' . $request->input('estado');
        }
        if ($request->input('estado_repuesto')) {
            $filtrosTxt[] = 'Repuestos: ' . $request->input('estado_repuesto');
        }
        if ($request->input('estado_garantia')) {
            $filtrosTxt[] = 'Garantía: ' . $request->input('estado_garantia');
        }
        if ($request->input('motivo_ingreso')) {
            $filtrosTxt[] = 'Motivo: ' . $request->input('motivo_ingreso');
        }
        if ($request->input('marca')) {
            $filtrosTxt[] = 'Marca: ' . $request->input('marca');
        }
        if ($request->input('tipo_equipo')) {
            $filtrosTxt[] = 'Tipo: ' . $request->input('tipo_equipo');
        }
        if ($request->input('tipo_orden')) {
            $filtrosTxt[] = 'Orden: ' . ($request->input('tipo_orden') === 'empresa' ? 'Empresas' : 'Personales');
        }
        if ($request->input('tecnico_id')) {
            $tec = \App\Models\Identity\Usuario::find($request->input('tecnico_id'));
            if ($tec) {
                $filtrosTxt[] = 'Técnico: ' . $tec->nombre_tecnico;
            }
        }
        if ($request->input('sucursal_id')) {
            $suc = \App\Models\Directory\Sucursal::find($request->input('sucursal_id'));
            if ($suc) {
                $filtrosTxt[] = 'Sucursal: ' . $suc->ciudad;
            }
        }
        if ($request->input('cas_id')) {
            $cas = \App\Models\Directory\Cas::find($request->input('cas_id'));
            if ($cas) {
                $filtrosTxt[] = 'CAS: ' . $cas->nombre;
            }
        }
        if ($request->input('empresa_id')) {
            $emp = \App\Models\Directory\Empresa::find($request->input('empresa_id'));
            if ($emp) {
                $filtrosTxt[] = 'Empresa: ' . $emp->nombre;
            }
        }
        if ($request->input('buscar')) {
            $filtrosTxt[] = 'Búsqueda: "' . $request->input('buscar') . '"';
        }
        return $filtrosTxt;
    }
}
