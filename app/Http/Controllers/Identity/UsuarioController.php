<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\GuardarUsuarioRequest;
use App\Services\Identity\UsuarioService;
use App\Repositories\Identity\UsuarioRepository;
use App\Repositories\Identity\GrupoAccesoRepository;
use App\Repositories\Directory\SucursalRepository;
use App\Models\Identity\Rol; // Usamos el modelo directo para los roles por simplicidad
use App\DTOs\Identity\UsuarioDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;
class UsuarioController extends Controller
{
    //Inyeccion de dependencias
    protected UsuarioService $service;
    protected UsuarioRepository $repository;
    protected GrupoAccesoRepository $grupoRepository;
    protected SucursalRepository $sucursalRepository;

    public function __construct(
        UsuarioService $service,
        UsuarioRepository $repository,
        GrupoAccesoRepository $grupoRepository,
        SucursalRepository $sucursalRepository
    ) {
        $this->service = $service;
        $this->repository = $repository;
        $this->grupoRepository = $grupoRepository;
        $this->sucursalRepository = $sucursalRepository;
    }

    // Renderiza modulo-crear-usuario.php
    public function index(): View
    {
        $roles = Rol::all();
        $grupos = $this->grupoRepository->obtenerTodos();
        $sucursales = $this->sucursalRepository->obtenerTodas();

        return view('identity.usuarios.crear', compact('roles', 'grupos', 'sucursales'));
    }

    // Renderiza modulo-modificar-usuario.php
    public function editList(): View
    {
        $usuarios = $this->repository->obtenerTodosConRelaciones();
        $roles = Rol::all();
        $grupos = $this->grupoRepository->obtenerTodos();
        $sucursales = $this->sucursalRepository->obtenerTodas();

        return view('identity.usuarios.modificar', compact('usuarios', 'roles', 'grupos', 'sucursales'));
    }

    //Endpoint unificado para guardar o actualizar un usuario
    public function storeOrUpdate(GuardarUsuarioRequest $request): JsonResponse
    {
        try {
            $dto = new UsuarioDTO(
                $request->input('id') ? (int) $request->input('id') : null,
                $request->input('usuario'),
                $request->input('clave'),
                $request->input('nombre_tecnico'),
                $request->input('telefono'),
                $request->input('correo_tec'),
                (int) $request->input('rol_id'),
                (int) $request->input('grupo_id'),
                (int) $request->input('sucursal_id'),
                (bool) $request->input('acceso_nc', false),
                $request->input('sucursales', []), // Arrays vienen limpios desde FormRequest
                $request->input('permisos', [])
            );

            $mensaje = $this->service->guardarUsuario($dto);
            return response()->json(['ok' => true, 'mensaje' => $mensaje]);

        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    //Endpoint para activar/desactivar un usuario
    public function toggle(Request $request): JsonResponse
    {
        try {
            $id = (int) $request->input('id');
            $nuevoEstado = $this->service->toggleActivo($id);
            return response()->json(['ok' => true, 'activo' => $nuevoEstado]);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // Endpoints AJAX para edicion
    public function getPermisos(int $id): JsonResponse
    {
        $usuario = $this->repository->buscarPorId($id);
        return response()->json([
            'ok' => true,
            'permisos' => $usuario ? $usuario->permisos : []
        ]);
    }

    public function getSucursales(int $id): JsonResponse
    {
        $usuario = $this->repository->buscarPorId($id);
        return response()->json([
            'ok' => true,
            'sucursales' => $usuario ? $usuario->sucursalesAsignadas->pluck('id') : []
        ]);
    }
}
