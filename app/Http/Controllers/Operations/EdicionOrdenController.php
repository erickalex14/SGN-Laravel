<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarEdicionOrdenRequest;
use App\Http\Requests\Operations\GuardarEdicionOrdenEmpresaRequest;
use App\Services\Operations\ActualizarOrdenService;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\PrecioEstandarRepository;
use App\Repositories\Inventory\ProductoRepository;
use App\Repositories\Operations\TipoServicioRepository;
use App\Repositories\Identity\UsuarioRepository;
use App\DTOs\Operations\ActualizarOrdenDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class EdicionOrdenController extends Controller
{
    protected ActualizarOrdenService $service;
    protected OrdenRepository $ordenRepo;
    protected PrecioEstandarRepository $precioRepo;
    protected ProductoRepository $productoRepo;
    protected TipoServicioRepository $tipoServicioRepo;
    protected UsuarioRepository $usuarioRepo;

    public function __construct(
        ActualizarOrdenService $service,
        OrdenRepository $ordenRepo,
        PrecioEstandarRepository $precioRepo,
        ProductoRepository $productoRepo,
        TipoServicioRepository $tipoServicioRepo,
        UsuarioRepository $usuarioRepo
    ) {
        $this->service = $service;
        $this->ordenRepo = $ordenRepo;
        $this->precioRepo = $precioRepo;
        $this->productoRepo = $productoRepo;
        $this->tipoServicioRepo = $tipoServicioRepo;
        $this->usuarioRepo = $usuarioRepo;
    }

    public function edit(int $id): View
    {
        $orden = $this->ordenRepo->obtenerOrdenCompleta($id);

        if (!$orden) {
            abort(404, 'La orden solicitada no fue encontrada.');
        }

        $precios = $this->precioRepo->obtenerTodos()->where('activo', 1);
        $productos = $this->productoRepo->obtenerTodos();
        $tiposServicio = $this->tipoServicioRepo->obtenerTodos()->where('activo', 1);

        return view('operations.ordenes.editar', compact('orden', 'precios', 'productos', 'tiposServicio'));
    }

    public function update(GuardarEdicionOrdenRequest $request): JsonResponse
    {
        try {
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
                session('tecnico_id')
            );

            $this->service->actualizarOrden($dto);

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Orden actualizada correctamente.'
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

            if (!$esSuperadmin && $sucursalId > 0) {
                $query->where('vo.sucursal_id', $sucursalId);
            }
            if (!$puedeVerTodo && $tecnicoId > 0) {
                $query->where('vo.tecnico_id', $tecnicoId);
            }

            $resultados = $query->orderByDesc('vo.fecha_de_ingreso')->limit(15)->get();
        } catch (QueryException $e) {
            $resultados = $this->ordenRepo->buscarPorNumeroONombre($termino);
        }

        return response()->json([
            'ok'      => true,
            'ordenes' => $resultados
        ]);
    }

    public function editEmpresa(int $id): View
    {
        $orden = $this->ordenRepo->obtenerOrdenEmpresaCompleta($id);

        if (!$orden) {
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

        return view('operations.ordenes.editar_empresa', compact('orden', 'tecnicos'));
    }

    public function updateEmpresa(GuardarEdicionOrdenEmpresaRequest $request): JsonResponse
    {
        try {
            $this->service->actualizarOrdenEmpresa($request->all(), (int) session('tecnico_id'));

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Orden de empresa actualizada correctamente.'
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
}
