<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\CrearOrdenDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarOrdenRequest;
use App\Models\Directory\SucursalCliente;
use App\Repositories\Directory\CasRepository;
use App\Repositories\Directory\ClienteRepository;
use App\Repositories\Directory\EmpresaRepository;
use App\Repositories\Directory\SucursalClienteRepository;
use App\Repositories\Identity\UsuarioRepository;
use App\Repositories\Inventory\MarcaRepository;
use App\Repositories\Inventory\ProductoRepository;
use App\Repositories\Inventory\TipoDispositivoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\TipoServicioRepository;
use App\Services\Operations\CrearOrdenService;
use App\Services\Identity\ActividadDiariaService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdenController extends Controller
{
    protected CrearOrdenService $service;

    protected ClienteRepository $clienteRepo;

    protected UsuarioRepository $usuarioRepo;

    protected TipoServicioRepository $tipoServicioRepo;

    protected MarcaRepository $marcaRepo;

    protected TipoDispositivoRepository $tipoDispositivoRepo;

    protected CasRepository $casRepo;

    protected SucursalClienteRepository $sucursalClienteRepo;

    protected EmpresaRepository $empresaRepo;

    protected ProductoRepository $productoRepo;

    protected OrdenRepository $ordenRepo;

    protected ActividadDiariaService $actividadService;

    public function __construct(
        CrearOrdenService $service,
        ClienteRepository $clienteRepo,
        UsuarioRepository $usuarioRepo,
        TipoServicioRepository $tipoServicioRepo,
        MarcaRepository $marcaRepo,
        TipoDispositivoRepository $tipoDispositivoRepo,
        CasRepository $casRepo,
        SucursalClienteRepository $sucursalClienteRepo,
        EmpresaRepository $empresaRepo,
        ProductoRepository $productoRepo,
        OrdenRepository $ordenRepo,
        ActividadDiariaService $actividadService
    ) {
        $this->service = $service;
        $this->clienteRepo = $clienteRepo;
        $this->usuarioRepo = $usuarioRepo;
        $this->tipoServicioRepo = $tipoServicioRepo;
        $this->marcaRepo = $marcaRepo;
        $this->tipoDispositivoRepo = $tipoDispositivoRepo;
        $this->casRepo = $casRepo;
        $this->sucursalClienteRepo = $sucursalClienteRepo;
        $this->empresaRepo = $empresaRepo;
        $this->productoRepo = $productoRepo;
        $this->ordenRepo = $ordenRepo;
        $this->actividadService = $actividadService;
    }

    public function create(): View
    {
        $verTodosTecnicos = $this->puedeVerTodosTecnicos();
        $sucursalSesion = (int) session('sucursal_id');

        // Tecnicos activos con carga actual (pendientes/en proceso), ordenados por menor carga
        $tecnicos = $this->usuarioRepo->obtenerTecnicosConCargaActual(
            $verTodosTecnicos,
            $sucursalSesion,
            (int) session('tecnico_id')
        );
        $tiposServicio = $this->tipoServicioRepo->obtenerTodos()->where('activo', 1);
        $marcas = $this->marcaRepo->obtenerTodas();
        $tiposDispositivo = $this->tipoDispositivoRepo->obtenerTodos();
        $cas = $this->casRepo->obtenerActivos();
        $sucursalesCliente = $this->sucursalClienteRepo->obtenerTodas();
        $empresas = $this->empresaRepo->obtenerTodas();
        $productosInventario = $this->productoRepo->obtenerTodos();

        return view('operations.ordenes.crear', compact(
            'tecnicos',
            'tiposServicio',
            'marcas',
            'tiposDispositivo',
            'cas',
            'sucursalesCliente',
            'empresas',
            'productosInventario'
        ));
    }

    public function store(GuardarOrdenRequest $request): JsonResponse
    {
        try {
            $fechaIngreso = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

            $isEmpresa = $request->input('motivo_ingreso') === 'Servicios a Empresas';
            $subtipo = $request->input('subtipo_empresa');
            $esServicioEmpresa = $isEmpresa && $subtipo === 'Servicios';

            if ($esServicioEmpresa) {
                $tecnicosAsignados = $request->input('tecnicos_asignados', []);
                if (! is_array($tecnicosAsignados)) {
                    $tecnicosAsignados = [$tecnicosAsignados];
                }
                foreach ($tecnicosAsignados as $tecId) {
                    $this->validarTecnicoAsignable((int) $tecId);
                }
                $this->validarTecnicoAsignable((int) $request->input('tecnico_encargado'));
            } else {
                $this->validarTecnicoAsignable((int) $request->input('ord_tecnico_id'));
            }

            if ($isEmpresa) {
                $orden = $this->service->crearOrdenEmpresa(array_merge($request->validated(), [
                    'sucursal_id' => (int) session('sucursal_id'),
                    'ingresado_por' => (int) session('tecnico_id'),
                    'fecha_ingreso' => $fechaIngreso,
                ]));

                $this->actividadService->registrar(
                    usuarioId: (int) session('tecnico_id'),
                    tipoAccion: 'crear_orden_empresa',
                    descripcion: "Creó orden de empresa #{$orden->nro_orden} para cliente {$orden->empresa?->nombre}",
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
                    'mensaje' => 'Orden '.$orden->nro_orden.' generada con exito.',
                    'nro_orden' => $orden->nro_orden,
                    'orden_id' => $orden->id,
                    'tipo_orden' => 'empresa',
                ]);
            }

            $nroSucursalCliente = $request->input('nro_sucursal_cliente')
                ? (string) $request->input('nro_sucursal_cliente')
                : $this->resolverSucursalClienteDesdeFactura(
                    (string) $request->input('motivo_ingreso'),
                    (string) $request->input('nro_factura')
                );

            if ($nroSucursalCliente !== null && $nroSucursalCliente !== '') {
                if (is_numeric($nroSucursalCliente)) {
                    $suc = SucursalCliente::where('numero', (int) $nroSucursalCliente)->first();
                    if ($suc) {
                        $nroSucursalCliente = $suc->codigo;
                    }
                }
            }

            $series = $request->input('series', []);
            if (! is_array($series)) {
                $series = [$series];
            }

            $credUsuarios = $request->input('cred_usuario', []);
            $credContrasenas = $request->input('cred_contrasena', []);
            $credEsPatron = $request->input('cred_es_patron', []);
            $credenciales = [];

            foreach ($credContrasenas as $idx => $pwd) {
                $pwd = trim((string) $pwd);
                if ($pwd === '') {
                    continue;
                }
                $credenciales[] = [
                    'usuario' => trim((string) ($credUsuarios[$idx] ?? '')),
                    'contrasena' => $pwd,
                    'es_patron' => (int) ($credEsPatron[$idx] ?? 0),
                ];
            }

            $dto = new CrearOrdenDTO(
                null, // cliente_id se resuelve en el service
                $request->input('cli_identificacion'),
                $request->input('cli_nombres'),
                $request->input('cli_apellidos'),
                $request->input('cli_telefono'),
                $request->input('cli_correo'),
                $request->input('cli_direccion'),

                $request->input('eq_tipo'),
                $request->input('eq_marca'),
                $request->input('eq_modelo'),
                $request->input('eq_contrasena'),
                $request->input('eq_falla'),
                $request->input('eq_observacion'),
                $request->input('eq_tipo_servicio') ? (int) $request->input('eq_tipo_servicio') : null,
                $request->input('tipo_servicio_texto'),
                $request->input('producto_inventario_codigo'),
                $series,
                $credenciales,

                session('sucursal_id'), // Extraido directo de la sesion del usuario logueado
                (int) $request->input('ord_tecnico_id'),
                session('tecnico_id'), // Usuario que registra
                $fechaIngreso, // Forzamos timezone legacy
                $request->input('motivo_ingreso'),
                $request->input('nro_factura'),
                $request->input('nro_factura_2'),
                $request->input('fecha_facturacion'),
                $request->input('fecha_prometido'),
                $nroSucursalCliente,
                $request->input('estado_repuesto'),
                $request->input('garantia_tipo'),
                $request->input('cas_id') ? (int) $request->input('cas_id') : null,
                $request->input('repuesto_inventario_id') ? (int) $request->input('repuesto_inventario_id') : null,
                $request->input('repuestos_seleccionados', [])
            );

            $orden = $this->service->crearOrden($dto);

            $this->actividadService->registrar(
                usuarioId: (int) session('tecnico_id'),
                tipoAccion: 'crear_orden',
                descripcion: "Creó orden #{$orden->nro_orden} para cliente " . ($orden->cliente?->nombre_completo ?? $orden->cliente?->nombre ?? ''),
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

            return response()->json([
                'ok' => true,
                'mensaje' => 'Orden '.$orden->nro_orden.' generada con éxito.',
                'nro_orden' => $orden->nro_orden,
                'orden_id' => $orden->id,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    private function validarTecnicoAsignable(int $tecnicoId): void
    {
        if (! $this->usuarioRepo->tecnicoAsignable(
            $tecnicoId,
            $this->puedeVerTodosTecnicos(),
            (int) session('sucursal_id'),
            (int) session('tecnico_id')
        )) {
            throw new Exception('Solo puedes asignar tecnicos de tu sucursal o CAS.');
        }
    }

    private function puedeVerTodosTecnicos(): bool
    {
        return (bool) session('es_superadmin', false)
            || $this->tienePermisoSesion('usuarios_crear', 'ver')
            || $this->tienePermisoSesion('usuarios', 'crear')
            || $this->tienePermisoSesion('usuarios', 'ver');
    }

    private function tienePermisoSesion(string $modulo, string $accion): bool
    {
        $permisos = (array) session('permisos', []);
        $acciones = (array) ($permisos[$modulo] ?? []);

        return (bool) ($acciones[$accion] ?? false);
    }

    private function resolverSucursalClienteDesdeFactura(string $motivoIngreso, string $nroFactura): ?string
    {
        if ($motivoIngreso !== 'Validacion de Garantia') {
            return null;
        }

        $digitos = preg_replace('/\D+/', '', $nroFactura);
        if (strlen((string) $digitos) < 3) {
            return null;
        }

        $numeroSucursal = (int) substr((string) $digitos, 0, 3);
        if ($numeroSucursal <= 0) {
            return null;
        }

        $sucursales = SucursalCliente::where('numero', $numeroSucursal)->get();
        if ($sucursales->count() === 1) {
            return $sucursales->first()->codigo;
        }

        return null;
    }

    // Endpoint AJAX para autocompletar datos del cliente al digitar la cedula
    public function buscarCliente(Request $request): JsonResponse
    {
        $identificacion = $request->query('identificacion');
        if (! $identificacion) {
            return response()->json(['ok' => false]);
        }

        $cliente = $this->clienteRepo->buscarPorIdentificacion($identificacion);

        if ($cliente) {
            return response()->json(['ok' => true, 'cliente' => $cliente]);
        }

        return response()->json(['ok' => false, 'error' => 'Cliente no encontrado']);
    }

    public function buscarProducto(Request $request): JsonResponse
    {
        $codigo = strtoupper(trim((string) $request->query('codigo', '')));
        if ($codigo === '') {
            return response()->json(['ok' => false]);
        }

        $producto = $this->productoRepo->buscarPorCodigo($codigo);
        if (! $producto) {
            return response()->json(['ok' => false, 'error' => 'Producto no encontrado']);
        }

        return response()->json([
            'ok' => true,
            'producto' => [
                'codigo' => (string) $producto->codigo,
                'descripcion' => (string) $producto->descripcion,
                'marca' => (string) ($producto->marca->nombre ?? ''),
                'tipo_codigo' => (string) ($producto->tipoDispositivo->codigo ?? ''),
                'tipo_nombre' => (string) ($producto->tipoDispositivo->nombre ?? ''),
            ],
        ]);
    }

    public function imprimir(int $id): View
    {
        $orden = $this->ordenRepo->obtenerOrdenCompleta($id);
        abort_if(! $orden, 404);

        $orden->loadMissing([
            'equipo.series',
            'equipo.tipoServicio',
            'tecnico',
            'sucursal',
            'cas',
            'usuarioIngreso',
            'repuestoInventario',
            'preciosOrden', // Carga automática de los cargos personalizados de la orden
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

        // Obtener todas las series del equipo
        $seriesList = [];
        if ($orden->equipo) {
            if ($orden->equipo->serie) {
                $seriesList[] = strtolower(trim($orden->equipo->serie));
            }
            if ($orden->equipo->series && $orden->equipo->series->isNotEmpty()) {
                foreach ($orden->equipo->series as $es) {
                    $seriesList[] = strtolower(trim($es->serie));
                }
            }
        }
        $seriesList = array_unique(array_filter($seriesList, fn($s) => $s !== '' && $s !== 'sn' && $s !== 's/n'));

        $historialIngresos = [];
        if (!empty($seriesList)) {
            // Buscar en ordenes personales anteriores (excluyendo la actual)
            $prevPersonales = \App\Models\Operations\Orden::where(function ($q) use ($seriesList) {
                $q->whereHas('equipo', function ($eqQ) use ($seriesList) {
                    $eqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $seriesList);
                })->orWhereHas('equipo.series', function ($seqQ) use ($seriesList) {
                    $seqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $seriesList);
                });
            })->where('id', '<>', $orden->id)
              ->with(['tecnico', 'usuarioIngreso'])
              ->get();

            foreach ($prevPersonales as $prev) {
                $historialIngresos[] = [
                    'nro_orden' => $prev->nro_orden,
                    'tecnico_ingreso' => $prev->usuarioIngreso?->nombre_tecnico ?? $prev->usuarioIngreso?->name ?? 'Desconocido',
                    'tecnico_asignado' => $prev->tecnico?->nombre_tecnico ?? $prev->tecnico?->name ?? 'Desconocido',
                    'fecha_ingreso' => $prev->fecha_de_ingreso,
                    'timestamp' => $prev->fecha_de_ingreso ? Carbon::parse($prev->fecha_de_ingreso)->timestamp : 0,
                ];
            }

            // Buscar en ordenes empresas anteriores
            $prevEmpresas = \App\Models\Operations\OrdenEmpresa::where(function ($q) use ($seriesList) {
                $q->whereHas('equipo', function ($eqQ) use ($seriesList) {
                    $eqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $seriesList);
                });
            })->with(['tecnico', 'ingresadoPor'])
              ->get();

            foreach ($prevEmpresas as $prev) {
                $historialIngresos[] = [
                    'nro_orden' => $prev->nro_orden,
                    'tecnico_ingreso' => $prev->ingresadoPor?->nombre_tecnico ?? $prev->ingresadoPor?->name ?? 'Desconocido',
                    'tecnico_asignado' => $prev->tecnico?->nombre_tecnico ?? $prev->tecnico?->name ?? 'Desconocido',
                    'fecha_ingreso' => $prev->fecha_ingreso,
                    'timestamp' => $prev->fecha_ingreso ? Carbon::parse($prev->fecha_ingreso)->timestamp : 0,
                ];
            }

            // Ordenar por fecha cronológica (del más antiguo al más reciente)
            usort($historialIngresos, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        }

        return view('operations.ordenes.imprimir', compact('orden', 'nombreSucursalCliente', 'historialIngresos'));
    }

    public function imprimirEmpresa(int $id): View
    {
        $orden = $this->ordenRepo->obtenerOrdenEmpresaCompleta($id);
        abort_if(! $orden, 404);

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

        $orden->loadMissing([
            'equipo.series',
            'tecnico',
            'sucursal',
            'cas',
            'ingresadoPor',
        ]);

        // Obtener todas las series del equipo
        $seriesList = [];
        if ($orden->equipo) {
            if ($orden->equipo->serie) {
                $parts = explode(',', $orden->equipo->serie);
                foreach ($parts as $p) {
                    $seriesList[] = strtolower(trim($p));
                }
            }
            if ($orden->equipo->series && $orden->equipo->series->isNotEmpty()) {
                foreach ($orden->equipo->series as $es) {
                    $seriesList[] = strtolower(trim($es->serie));
                }
            }
        }
        $seriesList = array_unique(array_filter($seriesList, fn($s) => $s !== '' && $s !== 'sn' && $s !== 's/n'));

        $historialIngresos = [];
        if (!empty($seriesList)) {
            // Buscar en ordenes personales anteriores
            $prevPersonales = \App\Models\Operations\Orden::where(function ($q) use ($seriesList) {
                $q->whereHas('equipo', function ($eqQ) use ($seriesList) {
                    $eqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $seriesList);
                })->orWhereHas('equipo.series', function ($seqQ) use ($seriesList) {
                    $seqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $seriesList);
                });
            })->with(['tecnico', 'usuarioIngreso'])
              ->get();

            foreach ($prevPersonales as $prev) {
                $historialIngresos[] = [
                    'nro_orden' => $prev->nro_orden,
                    'tecnico_ingreso' => $prev->usuarioIngreso?->nombre_tecnico ?? $prev->usuarioIngreso?->name ?? 'Desconocido',
                    'tecnico_asignado' => $prev->tecnico?->nombre_tecnico ?? $prev->tecnico?->name ?? 'Desconocido',
                    'fecha_ingreso' => $prev->fecha_de_ingreso,
                    'timestamp' => $prev->fecha_de_ingreso ? Carbon::parse($prev->fecha_de_ingreso)->timestamp : 0,
                ];
            }

            // Buscar en ordenes empresas anteriores (excluyendo la actual)
            $prevEmpresas = \App\Models\Operations\OrdenEmpresa::where(function ($q) use ($seriesList) {
                $q->whereHas('equipo', function ($eqQ) use ($seriesList) {
                    $eqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $seriesList);
                });
            })->where('id', '<>', $orden->id)
              ->with(['tecnico', 'ingresadoPor'])
              ->get();

            foreach ($prevEmpresas as $prev) {
                $historialIngresos[] = [
                    'nro_orden' => $prev->nro_orden,
                    'tecnico_ingreso' => $prev->ingresadoPor?->nombre_tecnico ?? $prev->ingresadoPor?->name ?? 'Desconocido',
                    'tecnico_asignado' => $prev->tecnico?->nombre_tecnico ?? $prev->tecnico?->name ?? 'Desconocido',
                    'fecha_ingreso' => $prev->fecha_ingreso,
                    'timestamp' => $prev->fecha_ingreso ? Carbon::parse($prev->fecha_ingreso)->timestamp : 0,
                ];
            }

            // Ordenar por fecha cronológica (del más antiguo al más reciente)
            usort($historialIngresos, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        }

        return view('operations.ordenes.imprimir_empresa', compact('orden', 'nombreSucursalCliente', 'historialIngresos'));
    }

    public function verificarDuplicado(Request $request): JsonResponse
    {
        $seriesInput = $request->input('series', []);
        if (!is_array($seriesInput)) {
            $seriesInput = [$seriesInput];
        }
        $series = array_filter(
            array_map(fn($s) => strtolower(trim((string)$s)), $seriesInput),
            fn($s) => $s !== '' && $s !== 'sn' && $s !== 's/n'
        );

        $facturasInput = $request->input('facturas', []);
        if (!is_array($facturasInput)) {
            $facturasInput = [$facturasInput];
        }
        $facturas = array_filter(
            array_map(fn($f) => strtolower(trim((string)$f)), $facturasInput),
            fn($f) => $f !== ''
        );

        if (empty($series) && empty($facturas)) {
            return response()->json(['duplicated' => false, 'coincidencias' => []]);
        }

        $coincidencias = [];

        // Buscar en ordenes personales
        $queryPersonales = \App\Models\Operations\Orden::query();
        $queryPersonales->where(function($q) use ($series, $facturas) {
            if (!empty($series)) {
                $q->whereHas('equipo', function ($eqQ) use ($series) {
                    $eqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $series);
                })->orWhereHas('equipo.series', function ($seqQ) use ($series) {
                    $seqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $series);
                });
            }
            if (!empty($facturas)) {
                $q->orWhereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(nro_factura))'), $facturas)
                  ->orWhereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(nro_factura_2))'), $facturas);
            }
        });

        $resPersonales = $queryPersonales->with(['equipo', 'tecnico', 'usuarioIngreso'])->get();

        foreach ($resPersonales as $ord) {
            $coincidencias[] = [
                'tipo_orden' => 'personal',
                'nro_orden' => $ord->nro_orden,
                'fecha_ingreso' => $ord->fecha_de_ingreso ? Carbon::parse($ord->fecha_de_ingreso)->format('Y-m-d H:i') : '-',
                'tecnico_ingreso' => $ord->usuarioIngreso?->nombre_tecnico ?? $ord->usuarioIngreso?->name ?? 'Desconocido',
                'tecnico_asignado' => $ord->tecnico?->nombre_tecnico ?? $ord->tecnico?->name ?? 'Desconocido',
                'serie' => $ord->equipo?->serie ?? '',
                'factura' => $ord->nro_factura ?? '',
            ];
        }

        // Buscar en ordenes empresas
        $queryEmpresas = \App\Models\Operations\OrdenEmpresa::query();
        $queryEmpresas->where(function($q) use ($series, $facturas) {
            if (!empty($series)) {
                $q->whereHas('equipo', function ($eqQ) use ($series) {
                    $eqQ->whereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(serie))'), $series);
                });
            }
            if (!empty($facturas)) {
                $q->orWhereIn(\Illuminate\Support\Facades\DB::raw('TRIM(LOWER(nro_ticket))'), $facturas);
            }
        });

        $resEmpresas = $queryEmpresas->with(['equipo', 'tecnico', 'ingresadoPor'])->get();

        foreach ($resEmpresas as $ord) {
            $coincidencias[] = [
                'tipo_orden' => 'empresa',
                'nro_orden' => $ord->nro_orden,
                'fecha_ingreso' => $ord->fecha_ingreso ? Carbon::parse($ord->fecha_ingreso)->format('Y-m-d H:i') : '-',
                'tecnico_ingreso' => $ord->ingresadoPor?->nombre_tecnico ?? $ord->ingresadoPor?->name ?? 'Desconocido',
                'tecnico_asignado' => $ord->tecnico?->nombre_tecnico ?? $ord->tecnico?->name ?? 'Desconocido',
                'serie' => $ord->equipo?->serie ?? '',
                'factura' => $ord->nro_ticket ?? '',
            ];
        }

        return response()->json([
            'duplicated' => !empty($coincidencias),
            'coincidencias' => $coincidencias,
            'count' => count($coincidencias),
        ]);
    }
}
