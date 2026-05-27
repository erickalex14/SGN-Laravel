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

    private function construirContextoSesion(): PresupuestoContextDTO
    {
        $permisos = (array) session('permisos', []);
        $esAdminPorPermiso = (($permisos['usuarios']['crear'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true);

        return new PresupuestoContextDTO(
            (int) session('tecnico_id', 0),
            (int) session('sucursal_id', 0),
            (bool) session('es_superadmin', false) || $esAdminPorPermiso,
            (($permisos['ordenes_asignadas']['ver'] ?? false) === true)
        );
    }
}
