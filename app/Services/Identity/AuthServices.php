<?php

namespace App\Services\Identity;

use App\DTOs\Identity\LoginDTO;
use App\Repositories\Contracts\Identity\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthService
{
    protected UsuarioRepositoryInterface $usuarioRepository;

    public function __construct(UsuarioRepositoryInterface $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function validarAcceso(LoginDTO $dto): void
    {
        Log::info('Procesando solicitud de acceso al sistema.', ['usuario' => $dto->usuario]);

        $usuario = $this->usuarioRepository->buscarPorCredenciales($dto->usuario, $dto->clave);

        if (!$usuario) {
            Log::warning('Intento de acceso fallido: Credenciales invalidas.', ['usuario' => $dto->usuario]);
            throw new Exception('Usuario o clave incorrectos.');
        }

        if (!$usuario->activo) {
            Log::warning('Intento de acceso fallido: Cuenta de usuario inactiva.', ['usuario' => $dto->usuario]);
            throw new Exception('El usuario se encuentra desactivado en el sistema.');
        }

        // Autenticacion manual en Laravel sin validacion de hash
        Auth::login($usuario);

        // Reconstruccion de la sesion compatible con el sistema original
        $this->establecerVariablesSesionLegacy($usuario);

        Log::info('Acceso concedido satisfactoriamente.', ['usuario_id' => $usuario->id]);
    }

    protected function establecerVariablesSesionLegacy($usuario): void
    {
        // Se replican exactamente las llaves de sesion del sistema vanilla
        session([
            'usuario'        => $usuario->usuario,
            'nombre'         => $usuario->nombre_tecnico,
            'tecnico_id'     => $usuario->id,
            'sucursal_id'    => $usuario->sucursal_id,
            'grupo_id'       => $usuario->grupo_id,
            'es_superadmin'  => (bool) ($usuario->grupoAcceso->es_superadmin ?? false),
            // Nota: sucursales_ids y permisos se mapearan en el siguiente paso con sus repositorios
            'sucursales_ids' => [],
            'permisos'       => []
        ]);
    }
}
