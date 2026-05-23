<?php

namespace App\Services\Identity;

use App\DTOs\Identity\LoginDTO;
use App\Repositories\Identity\UsuarioRepository;
use Exception;
use Illuminate\Support\Facades\Log;
USE Illuminate\Support\Facades\Auth;

class AuthService
{
    protected UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    //Autenticar

    public function autenticar(LoginDTO $dto): void
    {
        $usuario = $this->usuarioRepository->encontrarPorCredencialesLegadas($dto->usuario, $dto->clave);

        //Valida si un usuario ingresa mal
        if (!$usuario) {
            Log::warning('Intento de inicio de sesion fallido. Credenciales incorrectas.', ['usuario' => $dto->usuario]);
            throw new Exception('Credenciales incorrectas');
        }

        //Valida si un usuario no esta activo le deniega el login
        IF (!$usuario->activo) {
            Log::warning('Intento de inicio de sesion fallido. Usuario inactivo.', ['usuario_id' => $usuario->id]);
            throw new Exception('Usuario inactivo');
        }

        //Registramops al user al sistema de auth de laravel de forma manual para no tener que hashear claves ni modificar BD
        Auth::login($usuario);

        // Replicar las variables de sesion legacy para asegurar retrocompatibilidad
        $this->establecerSesionLegada($usuario);

        log::info('Usuario autenticado exitosamente.', ['usuario_id' => $usuario->id]);
    }

    private function establecerSesionLegada($usuario): void
    {
        $grupo = $usuario->grupo;
        $es_superadmin = $grupo ? (bool)$grupo->es_superadmin : false;

        //LOGICA REPLICADA SGN_CARGAR_SUCURSALES
        $sucursalesIds = [$usuario->sucursal_id];
        foreach ($usuario->sucursalesAsignadas as $sucursalAsignada)
        {
            $sucursalesIds[] = $sucursalAsignada->id;
        }
        $sucursalesIds = array_unique(array_filter($sucursalesIds));

        // Replicar logica de sgn_cargar_permisos (Merge de grupo y usuario)
        $permisosFinales = [];
        if ($grupo && $grupo->permisos) {
            foreach ($grupo->permisos as $permiso) {
                $permisosFinales[$permiso->modulo][$permiso->accion] = (bool) $permiso->permitido;
            }
        }
        foreach ($usuario->permisos as $permiso) {
            $permisosFinales[$permiso->modulo][$permiso->accion] = (bool) $permiso->permitido;
        }

        // Registrar en la sesion de Laravel utilizando las mismas llaves del PHP Vanilla
        session([
            'usuario'        => $usuario->usuario,
            'nombre'         => $usuario->nombre_tecnico,
            'tecnico_id'     => $usuario->id,
            'sucursal_id'    => $usuario->sucursal_id,
            'sucursales_ids' => $sucursalesIds,
            'grupo_id'       => $usuario->grupo_id ?? 0,
            'grupo_nombre'   => $grupo ? $grupo->nombre : 'Sin grupo',
            'es_superadmin'  => $es_superadmin,
            'permisos'       => $permisosFinales,
        ]);
    }

    public function cerrarSesion(): void
    {
        $userId = Auth::id();
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        Log::info('Sesion cerrada correctamente.', ['usuario_id' => $userId]);
    }
}
