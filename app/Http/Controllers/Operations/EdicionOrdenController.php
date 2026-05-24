<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarEdicionOrdenRequest;
use App\Services\Operations\ActualizarOrdenService;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\PrecioEstandarRepository;
use App\Repositories\Inventory\ProductoRepository;
use App\Repositories\Operations\TipoServicioRepository;
use App\DTOs\Operations\ActualizarOrdenDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class EdicionOrdenController extends Controller
{
    protected ActualizarOrdenService $service;
    protected OrdenRepository $ordenRepo;
    protected PrecioEstandarRepository $precioRepo;
    protected ProductoRepository $productoRepo;
    protected TipoServicioRepository $tipoServicioRepo;

    public function __construct(
        ActualizarOrdenService $service,
        OrdenRepository $ordenRepo,
        PrecioEstandarRepository $precioRepo,
        ProductoRepository $productoRepo,
        TipoServicioRepository $tipoServicioRepo
    ) {
        $this->service = $service;
        $this->ordenRepo = $ordenRepo;
        $this->precioRepo = $precioRepo;
        $this->productoRepo = $productoRepo;
        $this->tipoServicioRepo = $tipoServicioRepo;
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
        $termino = $request->query('q');
        if (empty($termino)) {
            return response()->json(['ok' => false]);
        }

        $resultados = $this->ordenRepo->buscarPorNumeroONombre($termino);

        return response()->json([
            'ok'      => true,
            'ordenes' => $resultados
        ]);
    }
}