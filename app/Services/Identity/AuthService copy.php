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
        // 1. Buscamos al usuario manualmente por el nombre de usuario
        $usuario = Usuario::with('grupo')->where('usuario', $dto->usuario)->first();

        // 2. Comparamos la contraseña en texto plano (Retrocompatibilidad Legacy)
        if (!$usuario || $usuario->clave !== $dto->clave) {
            Log::warning('Intento de login fallido: credenciales incorrectas', ['usuario' => $dto->usuario]);
            throw new Exception('credenciales_invalidas');
        }

        // 3. Verificamos si el usuario está inactivo
        if ($usuario->activo === 0) {
            Log::warning('Intento de login fallido: usuario inactivo', ['usuario' => $dto->usuario]);
            throw new Exception('usuario_inactivo');
        }

        // 4. Forzamos el inicio de sesión en Laravel
        Auth::login($usuario);

        // 5. Replicamos las sesiones requeridas por el código legacy
        $esSuperadmin = $usuario->grupo ? (bool) $usuario->grupo->es_superadmin : false;
        
        session([
            'usuario'        => $usuario->usuario,
            'nombre'         => $usuario->nombre_tecnico,
            'tecnico_id'     => $usuario->id,
            'sucursal_id'    => $usuario->sucursal_id,
            'grupo_id'       => $usuario->grupo_id,
            'grupo_nombre'   => $usuario->grupo ? $usuario->grupo->nombre : 'Sin grupo',
            'es_superadmin'  => $esSuperadmin,
        ]);

        // Nota: Si ya tienes portadas las funciones sgn_cargar_sucursales() y sgn_cargar_permisos(),
        // este es el punto exacto para llamarlas y guardarlas en session(['permisos' => ...]).
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
