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
        $tecnicoId = session('tecnico_id');
        $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $esAdmin = in_array($rol, ['admin', 'master'], true);
        $esMaster = $rol === 'master';
        $sucursalSesion = (int) session('sucursal_id', 0);

        $ordenesPendientes = $this->repository->obtenerOrdenesSinInforme(
            (int) $tecnicoId,
            $esAdmin,
            $esMaster,
            $sucursalSesion
        );
        $informesGenerados = $this->repository->obtenerInformesPorTecnico(
            (int) $tecnicoId,
            $esAdmin,
            $esMaster,
            $sucursalSesion
        );

        return view('operations.informes.index', compact('ordenesPendientes', 'informesGenerados'));
    }

    public function store(GuardarInformeRequest $request): JsonResponse
    {
        try {
            $dto = new InformeDTO(
                (int) $request->input('orden_id'),
                session('tecnico_id'),
                $request->input('antecedentes'),
                $request->input('proceso'),
                $request->input('conclusion'),
                $request->input('recomendaciones'),
                $request->input('estado_equipo'),
                $request->file('fotos', [])
            );

            $rol = mb_strtolower(trim((string) session('grupo_nombre', '')));
            $esAdmin = in_array($rol, ['admin', 'master'], true);
            $esMaster = $rol === 'master';
            $sucursalSesion = (int) session('sucursal_id', 0);

            $this->service->procesarInforme($dto, $esAdmin, $esMaster, $sucursalSesion);

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

        $informe = $this->repository->buscarPorOrdenId($ordenId);
        if (!$informe) {
            return response()->json(['ok' => true, 'informe' => null]);
        }

        return response()->json([
            'ok' => true,
            'informe' => [
                'id' => $informe->id,
                'antecedentes' => (string) $informe->antecedentes,
                'proceso' => (string) $informe->proceso,
                'conclusion' => (string) $informe->conclusion,
                'recomendaciones' => (string) ($informe->recomendaciones ?? ''),
                'estado_equipo' => (string) ($informe->estado_equipo ?? ''),
                'fecha_informe' => (string) ($informe->fecha_informe ?? ''),
                'fotos' => $informe->fotos->map(function ($foto) {
                    $ruta = (string) ($foto->foto_data ?? '');
                    $src = str_starts_with($ruta, 'data:') ? $ruta : asset('storage/' . ltrim($ruta, '/'));
                    return [
                        'id' => $foto->id,
                        'src' => $src,
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

        return view('operations.informes.imprimir', compact('informe'));
    }
}
