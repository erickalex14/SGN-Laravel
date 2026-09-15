<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\PresupuestoContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Operations\PresupuestoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresupuestoController extends Controller
{
    protected PresupuestoService $service;

    public function __construct(PresupuestoService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $contexto = $this->construirContextoSesion();

        $data = $this->service->obtenerContextoIndex($contexto);

        return view('operations.presupuestos.index', [
            'ordenes' => $data['ordenes'],
            'catalogo' => $data['catalogo'],
        ]);
    }

    public function imprimir(Request $request, int $id): View
    {
        $contexto = $this->construirContextoSesion();
        $orden = $this->service->obtenerOrdenParaImpresion($contexto, $id);
        if (!$orden) {
            abort(404, 'Orden no encontrada o sin permisos para visualizarla.');
        }

        $payload = [];
        $rawPayload = (string) $request->query('payload', '');
        if ($rawPayload !== '') {
            $decoded = base64_decode($rawPayload, true);
            if ($decoded !== false) {
                $json = json_decode($decoded, true);
                if (is_array($json)) {
                    $payload = $json;
                }
            }
        }

        $items = [];
        foreach ((array) ($payload['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $nombre = trim((string) ($item['nombre'] ?? ''));
            if ($nombre === '') {
                continue;
            }
            $descripcion = trim((string) ($item['desc'] ?? ''));
            $precio = (float) ($item['precio'] ?? 0);
            if ($precio < 0) {
                $precio = 0;
            }
            $items[] = [
                'nombre' => $nombre,
                'desc' => $descripcion,
                'precio' => $precio,
            ];
        }

        $notas = trim((string) ($payload['notas'] ?? ''));
        $subtotal = collect($items)->sum(fn (array $it): float => (float) $it['precio']);
        $iva = round($subtotal * 0.15, 2);
        $total = round($subtotal + $iva, 2);

        return view('operations.presupuestos.imprimir', [
            'orden' => $orden,
            'items' => $items,
            'notas' => $notas,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now()->format('d/m/Y'),
            'tecnicoSesion' => (string) (session('nombre') ?? session('usuario') ?? ''),
            'autoImprimir' => (bool) $request->boolean('auto'),
        ]);
    }

    public function buscarOrdenes(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = (string) $request->query('q', '');
        $contexto = $this->construirContextoSesion();
        $ordenes = $this->service->buscarOrdenesDinamicas($contexto, $q);

        return response()->json([
            'ok' => true,
            'ordenes' => $ordenes
        ]);
    }

    public function buscarArticulos(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = (string) $request->query('q', '');
        $articulos = $this->service->buscarArticulosCatalogo($q);

        return response()->json([
            'ok' => true,
            'articulos' => $articulos
        ]);
    }

    public function imprimirDirecta(Request $request): View
    {
        $payload = [];
        $rawPayload = (string) $request->query('payload', '');
        if ($rawPayload !== '') {
            $decoded = base64_decode($rawPayload, true);
            if ($decoded !== false) {
                $json = json_decode($decoded, true);
                if (is_array($json)) {
                    $payload = $json;
                }
            }
        }

        // Si no vino por base64, leer de query params directos
        if (empty($payload) && ($request->filled('cliente') || $request->filled('cliente_nombre') || $request->filled('items'))) {
            $rawItems = $request->input('items');
            $itemsArr = is_string($rawItems) ? json_decode($rawItems, true) : $rawItems;
            $payload = [
                'cliente' => $request->input('cliente') ?: $request->input('cliente_nombre'),
                'identificacion' => $request->input('identificacion') ?: $request->input('cliente_documento'),
                'telefono' => $request->input('telefono') ?: $request->input('cliente_telefono'),
                'notas' => $request->input('notas') ?: $request->input('observaciones'),
                'items' => is_array($itemsArr) ? $itemsArr : [],
            ];
        }

        $items = [];
        foreach ((array) ($payload['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $nombre = trim((string) ($item['nombre'] ?? ''));
            if ($nombre === '') {
                continue;
            }
            $descripcion = trim((string) ($item['desc'] ?? $item['descripcion'] ?? ''));
            $precio = (float) ($item['precio'] ?? $item['precio_unitario'] ?? 0);
            $cantidad = isset($item['cantidad']) ? max(1, (int) $item['cantidad']) : 1;
            if ($precio < 0) {
                $precio = 0;
            }
            $items[] = [
                'nombre' => $nombre,
                'desc' => $descripcion,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'subtotal_linea' => round($precio * $cantidad, 2),
            ];
        }

        $cliente = trim((string) ($payload['cliente'] ?? 'CONSUMIDOR FINAL'));
        $identificacion = trim((string) ($payload['identificacion'] ?? ''));
        $telefono = trim((string) ($payload['telefono'] ?? ''));
        $notas = trim((string) ($payload['notas'] ?? ''));

        $subtotal = collect($items)->sum(fn (array $it): float => (float) ($it['subtotal_linea'] ?? $it['precio']));
        $iva = round($subtotal * 0.15, 2);
        $total = round($subtotal + $iva, 2);

        return view('operations.presupuestos.imprimir', [
            'orden' => null,
            'esDirecta' => true,
            'clienteDirecto' => [
                'nombre' => $cliente ?: 'CONSUMIDOR FINAL',
                'identificacion' => $identificacion,
                'telefono' => $telefono,
            ],
            'items' => $items,
            'notas' => $notas,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'fecha' => now()->format('d/m/Y'),
            'tecnicoSesion' => (string) (session('nombre') ?? session('usuario') ?? 'Atención en Recepción'),
            'autoImprimir' => (bool) $request->boolean('auto'),
        ]);
    }

    private function construirContextoSesion(): PresupuestoContextDTO
    {
        $permisos = (array) session('permisos', []);
        $esAdminPorPermiso = (($permisos['usuarios']['crear'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true);

        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $sessionRol = mb_strtolower(trim((string) session('rol_nombre', '')));
        $esRecepcion = session('es_recepcion') === true 
            || in_array($sessionGrupo, ['recepcion', 'recepción'], true)
            || in_array($sessionRol, ['recepcion', 'recepcionista'], true);

        return new PresupuestoContextDTO(
            (int) session('tecnico_id', 0),
            (int) session('sucursal_id', 0),
            (bool) session('es_superadmin', false) || $esAdminPorPermiso || $esRecepcion,
            (($permisos['ordenes_asignadas']['ver'] ?? false) === true) || $esRecepcion
        );
    }
}
