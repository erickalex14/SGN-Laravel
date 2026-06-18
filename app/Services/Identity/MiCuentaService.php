<?php

namespace App\Services\Identity;

use App\DTOs\Identity\MiCuentaPasswordDTO;
use App\DTOs\Identity\MiCuentaPerfilDTO;
use App\Repositories\Identity\MiCuentaRepository;
use Exception;

class MiCuentaService
{
    protected MiCuentaRepository $repository;

    public function __construct(MiCuentaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerContextoUsuario(int $usuarioId): array
    {
        $usuario = $this->repository->buscarPorId($usuarioId);

        return [
            'telefono_actual' => (string) ($usuario->telefono ?? ''),
            'correo_actual' => (string) ($usuario->correo_tec ?? ''),
        ];
    }

    /**
     * @throws Exception
     */
    public function actualizarPerfil(MiCuentaPerfilDTO $dto, ?string $telefono, ?string $correo): void
    {
        $usuario = $this->repository->buscarPorId($dto->usuario_id);
        if (! $usuario) {
            throw new Exception('Usuario no encontrado.');
        }

        $usuario->nombre_tecnico = $dto->nombre;
        $usuario->telefono = trim((string) $telefono);
        $usuario->correo_tec = trim((string) $correo);

        $this->repository->guardar($usuario);
    }

    /**
     * @throws Exception
     */
    public function actualizarPassword(MiCuentaPasswordDTO $dto): void
    {
        $usuario = $this->repository->buscarPorId($dto->usuario_id);
        if (! $usuario) {
            throw new Exception('Usuario no encontrado.');
        }

        if (! $usuario->validarClave($dto->actual)) {
            throw new Exception('La contrasena actual es incorrecta.');
        }

        if (mb_strlen($dto->nueva) < 6 || mb_strlen($dto->nueva) > 12) {
            throw new Exception('La contrasena debe tener entre 6 y 12 caracteres.');
        }

        $usuario->establecerClaveSegura($dto->nueva);
        $this->repository->guardar($usuario);
    }
}
