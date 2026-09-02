<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\ActualizarOrdenDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarEdicionOrdenEmpresaRequest;
use App\Http\Requests\Operations\GuardarEdicionOrdenRequest;
use App\Models\Directory\SucursalCliente;
use App\Repositories\Directory\CasRepository;
use App\Repositories\Directory\SucursalClienteRepository;
use App\Repositories\Identity\UsuarioRepository;
use App\Repositories\Inventory\MarcaRepository;
use App\Repositories\Inventory\ProductoRepository;
use App\Repositories\Inventory\TipoDispositivoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\PrecioEstandarRepository;
use App\Repositories\Operations\TipoServicioRepository;
use App\Services\Operations\ActualizarOrdenService;
use App\Services\Identity\ActividadDiariaService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EdicionOrdenController extends Controller
{
    protected ActualizarOrdenService $service;

    protected OrdenRepository $ordenRepo;

    protected PrecioEstandarRepository $precioRepo;

    protected ProductoRepository $productoRepo;

    protected TipoServicioRepository $tipoServicioRepo;

    protected UsuarioRepository $usuarioRepo;

    protected ActividadDiariaService $actividadService;

    public function __construct(
        ActualizarOrdenService $service,
        OrdenRepository $ordenRepo,
        PrecioEstandarRepository $precioRepo,
        ProductoRepository $productoRepo,
        TipoServicioRepository $tipoServicioRepo,
        UsuarioRepository $usuarioRepo,
        ActividadDiariaService $actividadService
    ) {
        $this->service = $service;
        $this->ordenRepo = $ordenRepo;
        $this->precioRepo = $precioRepo;
        $this->productoRepo = $productoRepo;
        $this->tipoServicioRepo = $tipoServicioRepo;
        $this->usuarioRepo = $usuarioRepo;
        $this->actividadService = $actividadService;
    }

    public function edit(int $id): View
    {
        if (! $this->esUsuarioAdminOAdminMaster()) {
            abort(403, 'Acceso denegado: Solo los administradores pueden editar órdenes.');
        }

        $orden = $this->ordenRepo->obtenerOrdenCompleta($id);

        if (! $orden) {
            abort(404, 'La orden solicitada no fue encontrada.');
        }

        $precios = $this->precioRepo->obtenerTodos()->where('activo', 1);
        $productos = $this->productoRepo->obtenerTodos();
        $tiposServicio = $this->tipoServicioRepo->obtenerTodos()->where('activo', 1);

        $casRepo = app(CasRepository::class);
        $cas = $casRepo->obtenerActivos();
        $sucursalesCliente = app(SucursalClienteRepository::class)->obtenerTodas();

        $verTodosTecnicos = (bool) session('es_superadmin', false)
            || $this->tienePermisoSesion('usuarios_crear', 'ver')
            || $this->tienePermisoSesion('usuarios', 'crear')
            || $this->tienePermisoSesion('usuarios', 'ver');

        $tecnicos = $this->usuarioRepo->obtenerTecnicosConCargaActual(
            $verTodosTecnicos,
            (int) session('sucursal_id'),
            (int) session('tecnico_id')
        );

        $marcas = app(MarcaRepository::class)->obtenerTodas();
        $tiposDispositivo = app(TipoDispositivoRepository::class)->obtenerTodos();

        return view('operations.ordenes.editar', compact('orden', 'precios', 'productos', 'tiposServicio', 'cas', 'sucursalesCliente', 'tecnicos', 'marcas', 'tiposDispositivo'));
    }

    public function update(GuardarEdicionOrdenRequest $request): JsonResponse
    {
        if (! $this->esUsuarioAdminOAdminMaster()) {
            return response()->json(['ok' => false, 'error' => 'Acceso denegado: Solo administradores pueden realizar esta acción.'], 403);
        }

        try {
            $nroSucursalCliente = $request->input('nro_sucursal_cliente') ? (string) $request->input('nro_sucursal_cliente') : null;
            if ($nroSucursalCliente !== null && $nroSucursalCliente !== '') {
                if (is_numeric($nroSucursalCliente)) {
                    $suc = SucursalCliente::where('numero', (int) $nroSucursalCliente)->first();
                    if ($suc) {
                        $nroSucursalCliente = $suc->codigo;
                    }
                }
            }

            $dto = new ActualizarOrdenDTO(
                (int) $request->input('orden_id'),
                (int) $request->input('equipo_id'),
                $request->input('estado_orden'),
                $request->input('eq_falla'),
                $request->input('eq_observacion'),
                $request->input('tipo_servicio_id') ? (int) $request->input('tipo_servicio_id') : null,
                $request->input('valor_estandar_id') ? (int) $request->input('valor_estandar_id') : null,
                $request->input('repuesto_inventario_id') ? (int) $request->input('repuesto_inventario_id') : null,
                $request->input('fecha_prometido'),
                session('tecnico_id'),
                $request->input('cas_id') ? (int) $request->input('cas_id') : null,

                $request->input('cli_identificacion'),
                $request->input('cli_nombres'),
                $request->input('cli_apellidos'),
                $request->input('cli_telefono'),
                $request->input('cli_correo'),
                $request->input('cli_direccion'),

                $request->input('nro_factura'),
                $request->input('nro_factura_2'),
                $nroSucursalCliente,
                $request->input('fecha_facturacion'),

                $request->input('series', []),
                (int) $request->input('tecnico_id'),

                // Nuevos campos de equipo
                $request->input('eq_tipo'),
                $request->input('eq_marca'),
                $request->input('eq_modelo'),
                $request->input('eq_contrasena'),

                // Nuevos campos de orden
                $request->input('motivo_ingreso'),
                $request->input('garantia_tipo'),
                $request->input('empresa_garantia'),
                $request->input('observacion_orden'),
                $request->input('transferencia_plataforma'),
                $request->input('transferencia_numero')
            );

            $this->service->actualizarOrden($dto);

            $orden = $this->ordenRepo->obtenerOrdenCompleta((int) $request->input('orden_id'));
            if ($orden) {
                $this->actividadService->registrar(
                    usuarioId: (int) session('tecnico_id'),
                    tipoAccion: 'editar_orden',
                    descripcion: "Editó orden #{$orden->nro_orden}",
                    modulo: 'ordenes',
                    referenciaId: $orden->id,
                    referenciaTipo: 'orden',
                    metadata: [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => $orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? '',
                        'serie' => $orden->equipo?->serie ?? 'sn',
                        'marca' => $orden->equipo?->marca ?? 'sn',
                        'tipo' => $orden->equipo?->tipo ?? 'sn',
                        'estado_orden' => $orden->estado_orden ?? 'Pendiente',
                        'estado_garantia' => $orden->estado_garantia ?? 'sn'
                    ]
                );
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Orden actualizada correctamente.',
            ]);

        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // Endpoint para el buscador global de ordenes
    public function buscarGlobal(Request $request): JsonResponse
    {
        $termino = trim((string) $request->query('q', ''));
        if (mb_strlen($termino) < 2) {
            return response()->json(['ok' => false, 'error' => 'Minimo 2 caracteres.']);
        }

        $tecnicoId = (int) session('tecnico_id', 0);
        $sucursalId = (int) session('sucursal_id', 0);
        $esSuperadmin = session('es_superadmin') === true;
        $permisos = session('permisos', []);

        $puedeVerTodo = $esSuperadmin
            || (($permisos['ordenes_asignadas']['ver'] ?? false) === true)
            || (($permisos['usuarios']['ver'] ?? false) === true);

        try {
            $query = DB::table('vista_ordenes as vo')
                ->select([
                    'vo.orden_id',
                    'vo.nro_orden',
                    'vo.tipo_orden',
                    'vo.cliente',
                    'vo.identificacion',
                    'vo.estado_orden',
                    'vo.marca',
                    'vo.modelo',
                    'vo.tecnico_id',
                    'vo.cliente_id',
                    'vo.equipo_id',
                    DB::raw("DATE_FORMAT(vo.fecha_de_ingreso, '%d/%m/%Y') as fecha"),
                ])
                ->where(function ($q) use ($termino) {
                    $q->where('vo.nro_orden', 'like', "%{$termino}%");
                    if (is_numeric($termino)) {
                        $q->orWhereRaw("CAST(SUBSTRING_INDEX(vo.nro_orden, '-', -1) AS UNSIGNED) = ?", [(int) $termino]);
                    }
                });

            if (! $esSuperadmin && $sucursalId > 0) {
                $query->where('vo.sucursal_id', $sucursalId);
            }
            if (! $puedeVerTodo && $tecnicoId > 0) {
                $query->where('vo.tecnico_id', $tecnicoId);
            }

            $resultados = $query->orderByDesc('vo.fecha_de_ingreso')->limit(15)->get();
        } catch (QueryException $e) {
            $resultados = $this->ordenRepo->buscarPorNumeroONombre($termino);
        }

        return response()->json([
            'ok' => true,
            'ordenes' => $resultados,
        ]);
    }

    public function editEmpresa(int $id): View
    {
        if (! $this->esUsuarioAdminOAdminMaster()) {
            abort(403, 'Acceso denegado: Solo los administradores pueden editar órdenes.');
        }

        $orden = $this->ordenRepo->obtenerOrdenEmpresaCompleta($id);

        if (! $orden) {
            abort(404, 'La orden corporativa solicitada no fue encontrada.');
        }

        $verTodosTecnicos = (bool) session('es_superadmin', false)
            || $this->tienePermisoSesion('usuarios_crear', 'ver')
            || $this->tienePermisoSesion('usuarios', 'crear')
            || $this->tienePermisoSesion('usuarios', 'ver');

        $tecnicos = $this->usuarioRepo->obtenerTecnicosConCargaActual(
            $verTodosTecnicos,
            (int) session('sucursal_id'),
            (int) session('tecnico_id')
        );

        $casRepo = app(CasRepository::class);
        $cas = $casRepo->obtenerActivos();

        $marcas = app(MarcaRepository::class)->obtenerTodas();
        $tiposDispositivo = app(TipoDispositivoRepository::class)->obtenerTodos();

        return view('operations.ordenes.editar_empresa', compact('orden', 'tecnicos', 'cas', 'marcas', 'tiposDispositivo'));
    }

    public function updateEmpresa(GuardarEdicionOrdenEmpresaRequest $request): JsonResponse
    {
        if (! $this->esUsuarioAdminOAdminMaster()) {
            return response()->json(['ok' => false, 'error' => 'Acceso denegado: Solo administradores pueden realizar esta acción.'], 403);
        }

        try {
            $orden = $this->ordenRepo->obtenerOrdenEmpresaCompleta((int) $request->input('orden_id'));
            if (! $orden) {
                throw new Exception('La orden corporativa solicitada no fue encontrada.');
            }

            if ($orden->subtipo === 'Servicios') {
                $tecnicosAsignados = $request->input('tecnicos_asignados', []);
                if (! is_array($tecnicosAsignados)) {
                    $tecnicosAsignados = [$tecnicosAsignados];
                }
                foreach ($tecnicosAsignados as $tecId) {
                    $this->validarTecnicoAsignable((int) $tecId);
                }
                $this->validarTecnicoAsignable((int) $request->input('tecnico_encargado'));
            } elseif ((int) $request->input('tecnico_id') > 0) {
                $this->validarTecnicoAsignable((int) $request->input('tecnico_id'));
            }

            $this->service->actualizarOrdenEmpresa($request->validated(), (int) session('tecnico_id'));

            $this->actividadService->registrar(
                usuarioId: (int) session('tecnico_id'),
                tipoAccion: 'editar_orden_empresa',
                descripcion: "Editó orden de empresa #{$orden->nro_orden}",
                modulo: 'ordenes',
                referenciaId: $orden->id,
                referenciaTipo: 'orden_empresa',
                metadata: [
                    'nro_orden' => $orden->nro_orden,
                    'cliente' => $orden->empresa?->nombre ?? '',
                    'serie' => $orden->equipo?->serie ?? 'sn',
                    'marca' => $orden->equipo?->marca ?? 'sn',
                    'tipo' => $orden->equipo?->tipo ?? 'sn',
                    'estado_orden' => $orden->estado ?? 'Pendiente',
                    'estado_garantia' => 'sn'
                ]
            );

            return response()->json([
                'ok' => true,
                'mensaje' => 'Orden de empresa actualizada correctamente.',
            ]);

        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    private function tienePermisoSesion(string $modulo, string $accion): bool
    {
        $permisos = (array) session('permisos', []);
        $acciones = (array) ($permisos[$modulo] ?? []);

        return (bool) ($acciones[$accion] ?? false);
    }

    private function esUsuarioAdminOAdminMaster(): bool
    {
        $usuario = auth()->user();
        if (! $usuario) {
            return false;
        }

        if (session('es_superadmin') === true) {
            return true;
        }

        if ($this->tienePermisoSesion('ordenes_editar', 'editar') || $this->tienePermisoSesion('ordenes_editar', 'ver')) {
            return true;
        }

        $rol = $usuario->rol ? mb_strtolower(trim((string) $usuario->rol->rol)) : '';
        $grupo = $usuario->grupo ? mb_strtolower(trim((string) $usuario->grupo->nombre)) : '';
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        $rolesAdmitidos = ['admin', 'administrador', 'admin master', 'administrador master'];

        return in_array($rol, $rolesAdmitidos, true)
            || in_array($grupo, $rolesAdmitidos, true)
            || in_array($sessionGrupo, $rolesAdmitidos, true);
    }

    private function validarTecnicoAsignable(int $tecnicoId): void
    {
        $puedeVerTodos = (bool) session('es_superadmin', false)
            || $this->tienePermisoSesion('usuarios_crear', 'ver')
            || $this->tienePermisoSesion('usuarios', 'crear')
            || $this->tienePermisoSesion('usuarios', 'ver');

        if (! $this->usuarioRepo->tecnicoAsignable(
            $tecnicoId,
            $puedeVerTodos,
            (int) session('sucursal_id'),
            (int) session('tecnico_id')
        )) {
            throw new Exception('Solo puedes asignar tecnicos de tu sucursal o CAS.');
        }
    }
}
