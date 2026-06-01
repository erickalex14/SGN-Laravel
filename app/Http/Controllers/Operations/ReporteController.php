<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\ReporteFiltroDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\FiltrarReporteRequest;
use App\Models\Operations\Equipo;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Repositories\Directory\SucursalRepository;
use App\Repositories\Directory\CasRepository;
use App\Repositories\Identity\UsuarioRepository;
use App\Services\Operations\ReporteService;
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

    public function __construct(
        ReporteService $service,
        UsuarioRepository $usuarioRepo,
        SucursalRepository $sucursalRepo,
        CasRepository $casRepo
    ) {
        $this->service = $service;
        $this->usuarioRepo = $usuarioRepo;
        $this->sucursalRepo = $sucursalRepo;
        $this->casRepo = $casRepo;
    }

    public function index(): View
    {
        $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $esMaster = $rol === 'master';
        $sucursalSesion = (int) session('sucursal_id', 0);

        $tecnicos = $this->filtrarTecnicosSegunRol(
            $this->usuarioRepo->obtenerTodosConRelaciones(),
            $esMaster,
            $sucursalSesion
        );

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
                $request->input('cas_id') ? (int) $request->input('cas_id') : null
            );

            $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
            $esMaster = $rol === 'master';
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
                $rol = mb_strtolower(trim((string) ($usuario->rol->rol ?? $usuario->grupo->nombre ?? '')));
                $esTecnico = in_array($rol, ['tecnico', 'tecnico master'], true);

                if (!$esTecnico) {
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
}
