<?php

namespace App\Services\Identity;

use App\Models\Identity\Usuario;

use App\DTOs\Identity\LoginDTO;
use App\Repositories\Identity\UsuarioRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    protected UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function autenticar(LoginDTO $dto): void
    {
        // 1. Buscamos al usuario manualmente cargando las relaciones necesarias
        $usuario = \App\Models\Identity\Usuario::with(['grupo', 'sucursalesAsignadas', 'permisos'])
            ->where('usuario', $dto->usuario)
            ->first();

        // 2. Comparamos la contraseña en texto plano (Retrocompatibilidad Legacy)
        if (!$usuario || $usuario->clave !== $dto->clave) {
            Log::warning('Intento de login fallido: credenciales incorrectas', ['usuario' => $dto->usuario]);
            throw new Exception('credenciales_invalidas');
        }

        // 3. Verificamos si el usuario está inactivo (en legacy, NULL equivale a activo)
        if ((int)($usuario->activo ?? 1) === 0) {
            Log::warning('Intento de login fallido: usuario inactivo', ['usuario' => $dto->usuario]);
            throw new Exception('usuario_inactivo');
        }

        // 4. Forzamos el inicio de sesión en Laravel
        Auth::login($usuario);

        // 5. Replicamos las sesiones requeridas por el código legacy
        $grupo = $usuario->grupo;
        $es_superadmin = $grupo ? (bool) $grupo->es_superadmin : false;

        // Extraer IDs de sucursales asignadas
        $sucursalesIds = [];
        foreach ($usuario->sucursalesAsignadas as $sucursalAsignada) {
            $sucursalesIds[] = $sucursalAsignada->id;
        }
        $sucursalesIds = array_unique(array_filter($sucursalesIds));
        if (empty($sucursalesIds) && (int) $usuario->sucursal_id > 0) {
            $sucursalesIds[] = (int) $usuario->sucursal_id;
        }

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

        // Registrar en la sesión de Laravel utilizando las mismas llaves del PHP Vanilla
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
        // Limpiamos la sesión de forma segura
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}