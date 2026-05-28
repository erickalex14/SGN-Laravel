<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarOrdenRequest;
use App\Services\Operations\CrearOrdenService;
use App\Repositories\Directory\ClienteRepository;
use App\Repositories\Directory\CasRepository;
use App\Repositories\Directory\EmpresaRepository;
use App\Repositories\Directory\SucursalClienteRepository;
use App\Repositories\Identity\UsuarioRepository;
use App\Repositories\Inventory\MarcaRepository;
use App\Repositories\Inventory\ProductoRepository;
use App\Repositories\Inventory\TipoDispositivoRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\TipoServicioRepository;
use App\DTOs\Operations\CrearOrdenDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Exception;

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
        OrdenRepository $ordenRepo
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
    }

    public function create(): View
    {
        $verTodosTecnicos = $this->puedeVerTodosTecnicos();
        $sucursalSesion = (int) session('sucursal_id');

        // Tecnicos activos con carga actual (pendientes/en proceso), ordenados por menor carga
        $tecnicos = $this->usuarioRepo->obtenerTecnicosConCargaActual($verTodosTecnicos, $sucursalSesion);
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
            $this->validarTecnicoAsignable((int) $request->input('ord_tecnico_id'));

            if ($request->input('motivo_ingreso') === 'Servicios a Empresas') {
                $orden = $this->service->crearOrdenEmpresa(array_merge($request->validated(), [
                    'sucursal_id' => (int) session('sucursal_id'),
                    'ingresado_por' => (int) session('tecnico_id'),
                    'fecha_ingreso' => $fechaIngreso,
                ]));

                return response()->json([
                    'ok' => true,
                    'mensaje' => 'Orden ' . $orden->nro_orden . ' generada con exito.',
                    'nro_orden' => $orden->nro_orden,
                    'orden_id' => $orden->id,
                    'tipo_orden' => 'empresa'
                ]);
            }

            $nroSucursalCliente = $request->input('nro_sucursal_cliente')
                ? (int) $request->input('nro_sucursal_cliente')
                : $this->resolverSucursalClienteDesdeFactura(
                    (string) $request->input('motivo_ingreso'),
                    (string) $request->input('nro_factura')
                );

            $series = $request->input('series', []);
            if (!is_array($series)) {
                $series = [$series];
            }

            $credUsuarios = $request->input('cred_usuario', []);
            $credContrasenas = $request->input('cred_contrasena', []);
            $credEsPatron = $request->input('cred_es_patron', []);
            $credenciales = [];

            foreach ($credContrasenas as $idx => $pwd) {
                $pwd = trim((string)$pwd);
                if ($pwd === '') {
                    continue;
                }
                $credenciales[] = [
                    'usuario' => trim((string)($credUsuarios[$idx] ?? '')),
                    'contrasena' => $pwd,
                    'es_patron' => (int)($credEsPatron[$idx] ?? 0)
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
                $request->input('eq_tipo_servicio') ? (int)$request->input('eq_tipo_servicio') : null,
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
                $request->input('cas_id') ? (int)$request->input('cas_id') : null,
                $request->input('repuesto_inventario_id') ? (int)$request->input('repuesto_inventario_id') : null
            );

            $orden = $this->service->crearOrden($dto);

            return response()->json([
                'ok' => true, 
                'mensaje' => 'Orden ' . $orden->nro_orden . ' generada con éxito.',
                'nro_orden' => $orden->nro_orden,
                'orden_id' => $orden->id
            ]);

        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    private function validarTecnicoAsignable(int $tecnicoId): void
    {
        if (!$this->usuarioRepo->tecnicoAsignable(
            $tecnicoId,
            $this->puedeVerTodosTecnicos(),
            (int) session('sucursal_id')
        )) {
            throw new Exception('Solo puedes asignar tecnicos de tu sucursal.');
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

    private function resolverSucursalClienteDesdeFactura(string $motivoIngreso, string $nroFactura): ?int
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

        return $this->sucursalClienteRepo->existeNumero($numeroSucursal)
            ? $numeroSucursal
            : null;
    }

    // Endpoint AJAX para autocompletar datos del cliente al digitar la cedula
    public function buscarCliente(Request $request): JsonResponse
    {
        $identificacion = $request->query('identificacion');
        if (!$identificacion) return response()->json(['ok' => false]);

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
        if (!$producto) {
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
        abort_if(!$orden, 404);

        $orden->loadMissing([
            'equipo.series',
            'equipo.tipoServicio',
            'tecnico',
            'sucursal',
            'cas',
            'usuarioIngreso',
            'repuestoInventario',
        ]);

        $nombreSucursalCliente = '-';
        $nroSucursalCliente = (int) ($orden->nro_sucursal_cliente ?? 0);
        if ($nroSucursalCliente > 0) {
            if ($nroSucursalCliente === 999) {
                $nombreSucursalCliente = '999 - SERVICIO EXTERNO';
            } else {
                $suc = $this->sucursalClienteRepo
                    ->obtenerTodas()
                    ->firstWhere('numero', $nroSucursalCliente);
                $nombreSucursalCliente = $suc
                    ? str_pad((string) $nroSucursalCliente, 3, '0', STR_PAD_LEFT) . ' - ' . (string) $suc->nombre
                    : 'Nro. ' . str_pad((string) $nroSucursalCliente, 3, '0', STR_PAD_LEFT);
            }
        }

        return view('operations.ordenes.imprimir', compact('orden', 'nombreSucursalCliente'));
    }

    public function imprimirEmpresa(int $id): View
    {
        $orden = $this->ordenRepo->obtenerOrdenEmpresaCompleta($id);
        abort_if(!$orden, 404);

        return view('operations.ordenes.imprimir_empresa', compact('orden'));
    }
}
