<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarInformeRequest;
use App\Services\Operations\InformeService;
use App\Repositories\Operations\InformeRepository;
use App\DTOs\Operations\InformeDTO;
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

        $ordenesPendientes = $this->repository->obtenerOrdenesSinInforme($tecnicoId);
        $informesGenerados = $this->repository->obtenerInformesPorTecnico($tecnicoId);

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

            $this->service->procesarInforme($dto);

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
}