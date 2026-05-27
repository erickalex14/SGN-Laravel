<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarInformeRequest;
use App\Services\Operations\InformeService;
use App\Repositories\Operations\InformeRepository;
use App\DTOs\Operations\InformeDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class InformeController extends Controller
{
    protected InformeService $service;
    protected InformeRepository $repository;

    public function __construct(InformeService $service, InformeRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(): View
    {
        $contexto = $this->resolverContextoInformes();

        $ordenesPendientes = $this->repository->obtenerOrdenesSinInforme(
            $contexto['tecnico_id'],
            $contexto['es_admin'],
            $contexto['es_master'],
            $contexto['sucursal_id']
        );
        $informesGenerados = $this->repository->obtenerInformesPorTecnico(
            $contexto['tecnico_id'],
            $contexto['es_admin'],
            $contexto['es_master'],
            $contexto['sucursal_id']
        );

        $esAdmin = $contexto['es_admin'];
        $permisos = (array) session('permisos', []);
        $puedeEditar = !$esAdmin && (
            (($permisos['informes']['crear'] ?? false) === true)
            || (($permisos['informes']['editar'] ?? false) === true)
        );
        $nombreTecnico = (string) session('nombre', session('usuario', ''));

        return view('operations.informes.index', compact('ordenesPendientes', 'informesGenerados', 'esAdmin', 'puedeEditar', 'nombreTecnico'));
    }

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
                'mensaje' => 'El informe técnico ha sido generado y adjuntado a la orden correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function verPorOrden(Request $request): JsonResponse
    {
        $ordenId = (int) $request->query('orden_id', 0);
        if ($ordenId <= 0) {
            return response()->json(['ok' => false, 'error' => 'Orden invalida.']);
        }

        $contexto = $this->resolverContextoInformes();
        $ordenValida = $this->repository->buscarOrdenValidaParaInforme(
            $ordenId,
            $contexto['tecnico_id'],
            $contexto['es_admin'],
            $contexto['es_master'],
            $contexto['sucursal_id']
        );
        if (!$ordenValida) {
            return response()->json(['ok' => false, 'error' => 'No tiene permisos sobre la orden seleccionada.']);
        }

        $informe = $this->repository->buscarPorOrdenId($ordenId);
        if (!$informe) {
            return response()->json(['ok' => true, 'existe' => false, 'informe' => null]);
        }

        $repuestosUsados = $this->repository->obtenerRepuestosUsados($ordenId);

        return response()->json([
            'ok' => true,
            'existe' => true,
            'informe' => [
                'id' => $informe->id,
                'antecedentes' => (string) $informe->antecedentes,
                'proceso' => (string) $informe->proceso,
                'conclusion' => (string) $informe->conclusion,
                'recomendaciones' => (string) ($informe->recomendaciones ?? ''),
                'estado_equipo' => (string) ($informe->estado_equipo ?? ''),
                'fecha_informe' => (string) ($informe->fecha_informe ?? ''),
                'repuestos_usados' => $repuestosUsados,
                'fotos' => $informe->fotos->map(function ($foto) {
                    $ruta = (string) ($foto->foto_data ?? '');
                    $src = str_starts_with($ruta, 'data:') ? $ruta : asset('storage/' . ltrim($ruta, '/'));
                    return [
                        'id' => $foto->id,
                        'src' => $src,
                        'dataUrl' => $src,
                        'caption' => (string) ($foto->caption ?? ''),
                        'nombre_archivo' => (string) ($foto->nombre_archivo ?? ''),
                    ];
                })->values(),
            ],
        ]);
    }

    public function imprimir(int $id): View
    {
        $informe = $this->repository->buscarPorId($id);
        abort_if(!$informe, 404);

        $contexto = $this->resolverContextoInformes();
        $esPropietario = (int) ($informe->tecnico_id ?? 0) === $contexto['tecnico_id'];
        abort_unless($contexto['es_admin'] || $esPropietario, 403);

        return view('operations.informes.imprimir', compact('informe'));
    }

    private function resolverContextoInformes(): array
    {
        $tecnicoId = (int) session('tecnico_id', 0);
        $sucursalSesion = (int) session('sucursal_id', 0);
        $esSuperadmin = (bool) session('es_superadmin', false);
        $grupoNombre = mb_strtolower(trim((string) session('grupo_nombre', '')));

        // Paridad legacy: modo admin solo para admin/master.
        $esAdmin = $esSuperadmin || in_array($grupoNombre, ['admin', 'master'], true);

        return [
            'tecnico_id' => $tecnicoId,
            'sucursal_id' => $sucursalSesion,
            'es_admin' => $esAdmin,
            // En este modulo, admin debe listar todos los informes.
            'es_master' => $esAdmin,
        ];
    }
}
