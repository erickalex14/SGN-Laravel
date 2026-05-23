<?php

namespace App\Http\Controllers\Identity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\GuardarGrupoRequest;
use App\Http\Requests\Identity\GuardarPermisosGrupoRequest;
use App\Services\Identity\GrupoAccesoService;
use App\Repositories\Identity\GrupoAccesoRepository;
use App\DTOs\Identity\GrupoAccesoDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;
class GrupoAccesoController extends Controller
{
    protected GrupoAccesoService $service;
    protected GrupoAccesoRepository $repository;

    public function __construct(GrupoAccesoService $service, GrupoAccesoRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(): View
    {
        $grupos = $this->repository->obtenerTodos();
        return view('identity.grupos.index', compact('grupos'));
    }

    public function guardar (GuardarGrupoRequest $request): JsonResponse
    {
        try {
            $dto = new GrupoAccesoDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('nombre'),
                $request->input('descripcion'),
                (bool) $request->input('es_superadmin')
            );
            $mensaje = $this->service->guardarGrupo($dto);

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'ok' => false,
                'error' => $exception->getMessage()
            ]);
        }
    }

    public function eliminar(Request $request): JsonResponse
    {
        try {
            $id = (int) $request->input('id');
            if (!$id) throw new Exception('ID inválido.');

            $this->service->eliminarGrupo($id);

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function obtenerPermisos (int $id) : JsonResponse
    {
        $permisos = $this->repository->obtenerPermisos($id);

        //Se mapea el formato que espera el JS legacy
        return response()->json([
            'ok' => true,
            'permisos' => $permisos
        ]);
    }

    public function guardarPermisos(GuardarPermisosGrupoRequest $request) : JsonResponse
    {
        try {
            $grupoId = (int) $request->input('grupo_id');
            $permisos = $request->input('permisos', []);

            $this->service->guardarPermisos($grupoId, $permisos);

            return response()->json(['ok' => true]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => 'Error al guardar permisos.']);
        }
    }
}
