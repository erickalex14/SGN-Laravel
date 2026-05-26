<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\IngresarPreordenDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\IngresarPreordenRequest;
use App\Services\Operations\PreordenService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreordenController extends Controller
{
    protected PreordenService $service;

    public function __construct(PreordenService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $sucursalSesion = (int) session('sucursal_id', 0);
        $esSuperadmin = (bool) session('es_superadmin', false);
        $contexto = $this->service->obtenerContextoIndex($esSuperadmin, $sucursalSesion);

        return view('operations.preordenes.index', [
            'tecnicos' => $contexto['tecnicos'],
            'preordenes' => $contexto['preordenes'],
        ]);
    }

    public function ingresar(IngresarPreordenRequest $request): JsonResponse
    {
        try {
            $dto = new IngresarPreordenDTO(
                (int) $request->input('preorden_id'),
                (int) $request->input('tecnico_id'),
                (int) session('tecnico_id', 0),
                (int) session('sucursal_id', 0),
                (bool) session('es_superadmin', false),
                mb_strtoupper(trim((string) $request->input('direccion', ''))),
                trim((string) $request->input('serie', '')),
                trim((string) $request->input('observacion', '')),
                trim((string) $request->input('fecha_prometido', ''))
            );

            $resultado = $this->service->ingresar($dto);

            return response()->json([
                'ok' => true,
                'nro_orden' => $resultado['nro_orden'],
                'orden_id' => $resultado['orden_id'],
                'mensaje' => 'Orden ' . $resultado['nro_orden'] . ' creada correctamente.',
            ]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function reporte(Request $request): View
    {
        $preordenJson = trim((string) $request->input('preorden_json', ''));
        if ($preordenJson !== '') {
            $data = json_decode($preordenJson, true);
            if (is_array($data)) {
                if (empty($data['orden_ref']) && !empty($data['orden_id'])) {
                    $data['orden_ref'] = $this->service->obtenerNumeroOrdenPorId((int) $data['orden_id']);
                }

                return view('operations.preordenes.reporte', ['o' => (object) $data]);
            }
        }

        $preordenId = (int) $request->input('preorden_id', 0);
        $preorden = $this->service->obtenerReporte($preordenId);
        abort_unless($preorden, 404);

        return view('operations.preordenes.reporte', ['o' => $preorden]);
    }
}
