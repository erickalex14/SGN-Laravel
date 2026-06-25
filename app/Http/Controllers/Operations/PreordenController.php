<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\IngresarPreordenDTO;
use App\DTOs\Operations\VerificarPreordenDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\IngresarPreordenRequest;
use App\Http\Requests\Operations\VerificarPreordenRequest;
use App\Services\Operations\PreordenService;
use App\Services\Identity\ActividadDiariaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreordenController extends Controller
{
    protected PreordenService $service;
    protected ActividadDiariaService $actividadService;

    public function __construct(PreordenService $service, ActividadDiariaService $actividadService)
    {
        $this->service = $service;
        $this->actividadService = $actividadService;
    }

    public function index(): View
    {
        $sucursalSesion = (int) session('sucursal_id', 0);
        $esSuperadmin = $this->esSuperAdminOMaster();
        $contexto = $this->service->obtenerContextoIndex($esSuperadmin, $sucursalSesion);

        return view('operations.preordenes.index', [
            'tecnicos' => $contexto['tecnicos'],
            'preordenes' => $contexto['preordenes'],
            'preordenesIngresadas' => $contexto['preordenesIngresadas'],
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
                $this->esSuperAdminOMaster(),
                mb_strtoupper(trim((string) $request->input('direccion', ''))),
                trim((string) $request->input('serie', '')),
                trim((string) $request->input('observacion', '')),
                trim((string) $request->input('fecha_prometido', ''))
            );

            $resultado = $this->service->ingresar($dto);

            $orden = \App\Models\Operations\Orden::with(['cliente', 'equipo'])->find($resultado['orden_id']);
            if ($orden) {
                $this->actividadService->registrar(
                    usuarioId: (int) session('tecnico_id'),
                    tipoAccion: 'ingresar_preorden',
                    descripcion: "Creó orden #{$orden->nro_orden} desde preorden",
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

                if (! $this->esSuperAdminOMaster()) {
                    $sucursalSesion = (int) session('sucursal_id', 0);
                    if (isset($data['sucursal_id']) && (int) $data['sucursal_id'] !== $sucursalSesion) {
                        abort(403, 'No tienes permisos para ver el reporte de esta pre-orden.');
                    }
                }

                return view('operations.preordenes.reporte', ['o' => (object) $data]);
            }
        }

        $preordenId = (int) $request->input('preorden_id', 0);
        $preorden = $this->service->obtenerReporte($preordenId);
        abort_unless($preorden, 404);

        if (! $this->esSuperAdminOMaster()) {
            $sucursalSesion = (int) session('sucursal_id', 0);
            if (isset($preorden->sucursal_id) && (int) $preorden->sucursal_id !== $sucursalSesion) {
                abort(403, 'No tienes permisos para ver el reporte de esta pre-orden.');
            }
        }

        return view('operations.preordenes.reporte', ['o' => $preorden]);
    }

    public function verificar(VerificarPreordenRequest $request): JsonResponse
    {
        try {
            $dto = new VerificarPreordenDTO(
                trim((string) $request->query('ci', '')),
                trim((string) $request->query('codigo', '')),
                trim((string) $request->query('serie', ''))
            );

            $preorden = $this->service->verificarPreorden($dto);

            return response()->json([
                'ok' => true,
                'preorden' => $preorden,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'preorden' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function esSuperAdminOMaster(): bool
    {
        $usuario = auth()->user();
        if (! $usuario) {
            return false;
        }

        $rol = $usuario->rol ? mb_strtolower(trim((string) $usuario->rol->rol)) : '';
        $grupo = $usuario->grupo ? mb_strtolower(trim((string) $usuario->grupo->nombre)) : '';
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        $superRoles = [
            'admin master', 'administrador master', 'superadmin', 'superadministrador',
        ];

        return session('es_superadmin') === true
            || in_array($rol, $superRoles, true)
            || in_array($grupo, $superRoles, true)
            || in_array($sessionGrupo, $superRoles, true);
    }
}
