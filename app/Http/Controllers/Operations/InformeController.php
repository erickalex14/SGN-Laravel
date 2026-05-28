<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarInformeRequest;
use App\Services\Operations\InformeService;
use App\Repositories\Operations\InformeRepository;
use App\DTOs\Operations\InformeDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class InformeController extends Controller
{
    protected InformeService $service;
    protected InformeRepository $repository;

    public function __construct(InformeService $service, InformeRepository $repository)
    {
        $this->service    = $service;
        $this->repository = $repository;
    }

    /**
     * Ruta raíz del módulo: redirige según el rol.
     * Admin/Master → Buscar Informes
     * Técnico/SA    → Crear Informe
     */
    public function index(): RedirectResponse
    {
        $contexto = $this->resolverContextoInformes();
        if ($contexto['es_admin'] && !$contexto['es_superadmin']) {
            return redirect()->route('informes.buscar');
        }
        return redirect()->route('informes.crear');
    }

    // ══════════════════════════════════════════════════════════════
    //  VISTA: Crear / Editar Informe  (Técnicos + Superadmin)
    // ══════════════════════════════════════════════════════════════

    public function indexCrear(Request $request): View
    {
        $contexto          = $this->resolverContextoInformes();
        $nombreTecnico     = (string) session('nombre', session('usuario', ''));
        // Orden precargada desde "Mis Informes → Editar"
        $ordenIdPrecargado = (int) $request->query('orden_id', 0);

        return view('operations.informes.crear', compact('nombreTecnico', 'ordenIdPrecargado'));
    }

    /**
     * Endpoint AJAX: buscar órdenes para el formulario de creación.
     */
    public function buscarOrdenesAjax(Request $request): JsonResponse
    {
        $contexto = $this->resolverContextoInformes();
        $q        = trim((string) $request->query('q', ''));
        $tipo     = (string) $request->query('tipo', 'nro_orden');

        if ($tipo !== 'id' && strlen($q) < 2) {
            return response()->json(['ok' => false, 'error' => 'Escribe al menos 2 caracteres.']);
        }

        try {
            $ordenes = $this->repository->buscarOrdenesParaInforme(
                $q,
                $tipo,
                $contexto['tecnico_id'],
                $contexto['es_admin'],
                $contexto['es_master'],
                $contexto['sucursal_id']
            );

            if (empty($ordenes)) {
                return response()->json(['ok' => false, 'error' => 'No se encontraron órdenes con ese criterio.']);
            }

            return response()->json(['ok' => true, 'ordenes' => $ordenes]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  VISTA: Mis Informes  (Técnicos + Superadmin)
    // ══════════════════════════════════════════════════════════════

    public function misInformes(): View
    {
        $contexto = $this->resolverContextoInformes();
        $informes = $this->repository->obtenerMisInformes(
            $contexto['tecnico_id'],
            $contexto['es_master'],
            $contexto['sucursal_id']
        );

        return view('operations.informes.mis', compact('informes'));
    }

    // ══════════════════════════════════════════════════════════════
    //  GUARDAR (POST)
    // ══════════════════════════════════════════════════════════════

    public function store(GuardarInformeRequest $request): JsonResponse
    {
        try {
            $dto = new InformeDTO(
                (int) $request->input('orden_id'),
                (int) session('tecnico_id'),
                $request->input('antecedentes'),
                $request->input('proceso'),
                $request->input('conclusion'),
                $request->input('recomendaciones'),
                $request->input('estado_equipo'),
                $request->input('fecha_informe'),
                $request->file('fotos', []),
                $request->input('captions', [])
            );

            $contexto = $this->resolverContextoInformes();
            $this->service->procesarInforme($dto, $contexto['es_admin'], $contexto['es_master'], $contexto['sucursal_id']);

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Informe técnico guardado correctamente.',
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  ENDPOINTS AJAX auxiliares
    // ══════════════════════════════════════════════════════════════

    public function verPorOrden(Request $request): JsonResponse
    {
        $ordenId = (int) $request->query('orden_id', 0);
        if ($ordenId === 0) {
            return response()->json(['ok' => false, 'error' => 'Orden inválida.']);
        }

        $contexto   = $this->resolverContextoInformes();
        $ordenValida = $this->repository->buscarOrdenValidaParaInforme(
            $ordenId,
            $contexto['tecnico_id'],
            $contexto['es_admin'],
            $contexto['es_master'],
            $contexto['sucursal_id']
        );
        if (!$ordenValida) {
            return response()->json(['ok' => false, 'error' => 'No tiene permisos sobre esa orden.']);
        }

        $informe = $this->repository->buscarPorOrdenId($ordenId);
        if (!$informe) {
            return response()->json(['ok' => true, 'existe' => false, 'informe' => null]);
        }

        $repuestosUsados = $this->repository->obtenerRepuestosUsados($ordenId);

        return response()->json([
            'ok'     => true,
            'existe' => true,
            'informe' => [
                'id'              => $informe->id,
                'antecedentes'    => (string) $informe->antecedentes,
                'proceso'         => (string) $informe->proceso,
                'conclusion'      => (string) $informe->conclusion,
                'recomendaciones' => (string) ($informe->recomendaciones ?? ''),
                'estado_equipo'   => (string) ($informe->estado_equipo   ?? ''),
                'fecha_informe'   => (string) ($informe->fecha_informe   ?? ''),
                'repuestos_usados'=> $repuestosUsados,
                'fotos'           => $informe->fotos->map(function ($foto) {
                    $ruta = (string) ($foto->foto_data ?? '');
                    $src  = str_starts_with($ruta, 'data:') ? $ruta : asset('storage/' . ltrim($ruta, '/'));
                    return [
                        'id'             => $foto->id,
                        'src'            => $src,
                        'dataUrl'        => $src,
                        'caption'        => (string) ($foto->caption        ?? ''),
                        'nombre_archivo' => (string) ($foto->nombre_archivo ?? ''),
                    ];
                })->values(),
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  IMPRIMIR
    // ══════════════════════════════════════════════════════════════

    public function imprimir(int $id): View
    {
        $informe = $this->repository->buscarPorId($id);
        abort_if(!$informe, 404);

        $contexto    = $this->resolverContextoInformes();
        $esPropietario = (int) ($informe->tecnico_id ?? 0) === $contexto['tecnico_id'];
        abort_unless($contexto['es_admin'] || $esPropietario, 403);

        return view('operations.informes.imprimir', compact('informe'));
    }

    // ══════════════════════════════════════════════════════════════
    //  VISTA: Buscar Informes  (Admin + Superadmin)
    // ══════════════════════════════════════════════════════════════

    public function indexBuscar(): View
    {
        $contexto = $this->resolverContextoInformes();
        $tecnicos = $this->repository->obtenerTecnicosActivos($contexto['sucursal_id'], $contexto['es_master']);
        $estados  = ['Operativo', 'Reparado parcialmente', 'Sin reparación posible', 'Desguace', 'En espera de repuesto'];

        $esAdmin  = $contexto['es_admin'];
        $esMaster = $contexto['es_master'];

        return view('operations.informes.buscar', compact('tecnicos', 'estados', 'esAdmin', 'esMaster'));
    }

    public function buscarInformes(Request $request): JsonResponse
    {
        try {
            $contexto = $this->resolverContextoInformes();
            $filtros  = [
                'q'           => (string) $request->query('q',           ''),
                'tipo'        => (string) $request->query('tipo',        'nro_orden'),
                'tecnico_id'  => (int)    $request->query('tecnico_id',  0),
                'estado'      => (string) $request->query('estado',      ''),
                'fecha_desde' => (string) $request->query('fecha_desde', ''),
                'fecha_hasta' => (string) $request->query('fecha_hasta', ''),
            ];

            $informes = $this->repository->buscarInformes(
                $filtros,
                $contexto['tecnico_id'],
                $contexto['es_admin'],
                $contexto['es_master'],
                $contexto['sucursal_id']
            );

            if ($informes->isEmpty()) {
                return response()->json(['ok' => false, 'error' => 'No se encontraron informes.']);
            }

            return response()->json(['ok' => true, 'total' => $informes->count(), 'informes' => $informes]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  Helpers privados
    // ══════════════════════════════════════════════════════════════

    private function resolverContextoInformes(): array
    {
        $tecnicoId      = (int)  session('tecnico_id',    0);
        $sucursalSesion = (int)  session('sucursal_id',   0);
        $esSuperadmin   = (bool) session('es_superadmin', false);
        $grupoNombre    = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $rolNombre      = mb_strtolower(trim((string) (auth()->user()?->rol?->rol ?? '')));

        $rolesAdmin = ['admin', 'administrador', 'master', 'admin master', 'administrador master', 'tecnico master'];
        $esAdmin = $esSuperadmin
            || in_array($grupoNombre, $rolesAdmin, true)
            || in_array($rolNombre, $rolesAdmin, true);

        return [
            'tecnico_id'    => $tecnicoId,
            'sucursal_id'   => $sucursalSesion,
            'es_admin'      => $esAdmin,
            'es_master'     => $esAdmin,
            'es_superadmin' => $esSuperadmin,
        ];
    }
}
