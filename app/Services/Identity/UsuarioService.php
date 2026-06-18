<?php

namespace App\Services\Identity;

use App\DTOs\Identity\UsuarioDTO;
use App\Models\Identity\Usuario;
use App\Repositories\Identity\UsuarioRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class UsuarioService
{
    protected UsuarioRepository $repository;

    public function __construct(UsuarioRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    // Guardar un nuevo usuario
    public function guardarUsuario(UsuarioDTO $dto): string
    {
        if ($this->repository->existeUsuario($dto->usuario, $dto->id)) {
            throw new Exception("El nombre de usuario '{$dto->usuario}' ya está en uso.");
        }

        if ($dto->id) {
            $usuario = $this->repository->buscarPorId($dto->id);
            if (! $usuario) {
                throw new Exception('Usuario no encontrado.');
            }
            $mensaje = 'Usuario actualizado exitosamente.';

            if (! empty($dto->clave)) {
                $usuario->establecerClaveSegura($dto->clave);
            }
        } else {
            $usuario = new Usuario;
            $usuario->establecerClaveSegura((string) $dto->clave);
            $mensaje = 'Usuario creado exitosamente.';
        }

        $usuario->usuario = $dto->usuario;
        $usuario->nombre_tecnico = $dto->nombre_tecnico;
        $usuario->telefono = $dto->telefono;
        $usuario->correo_tec = $dto->correo_tec;
        $usuario->rol_id = $dto->rol_id;
        $usuario->grupo_id = $dto->grupo_id;
        $usuario->sucursal_id = $dto->sucursal_id;
        $usuario->acceso_nc = $dto->acceso_nc;
        // Al crear, se marca activo por defecto
        if (! $dto->id) {
            $usuario->activo = 1;
        }

        $usuario->save();

        // Sincronizar sucursales secundarias, permisos y CAS asignados
        $this->repository->sincronizarRelaciones($usuario, $dto->sucursales_secundarias, $dto->permisos, $dto->cas);

        Log::info('Usuario gestionado', ['usuario_id' => $usuario->id, 'accion' => $dto->id ? 'editar' : 'crear']);

        return $mensaje;
    }

    public function toogleActivo(int $id): bool
    {
        $usuario = $this->repository->buscarPorId($id);
        if (! $usuario) {
            throw new Exception('Usuario no encontrado.');
        }

        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        Log::info('Usuario '.($usuario->activo ? 'activado' : 'desactivado'), ['usuario_id' => $usuario->id]);

        return $usuario->activo;
    }
}
