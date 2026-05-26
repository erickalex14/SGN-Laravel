<?php

namespace App\Services\Identity;

use App\DTOs\Identity\LoginDTO;
use App\Repositories\Identity\UsuarioRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function autenticar(LoginDTO $dto): void
    {
        $usuarioInput = $this->normalizarInput($dto->usuario);
        $claveInput = $this->normalizarInput($dto->clave);

        // Validacion compatible con legacy: usuario o correo + clave (comparacion SQL)
        $usuario = $this->usuarioRepository->encontrarPorCredencialesLegadas($usuarioInput, $claveInput);

        if (!$usuario) {
            Log::warning('Intento de login fallido: credenciales incorrectas', ['usuario' => $usuarioInput]);
            throw new Exception('credenciales_invalidas');
        }

        // En legacy, NULL equivale a activo
        if ((int) ($usuario->activo ?? 1) === 0) {
            Log::warning('Intento de login fallido: usuario inactivo', ['usuario' => $usuarioInput]);
            throw new Exception('usuario_inactivo');
        }

        Auth::login($usuario);

        $grupo = $usuario->grupo;
        $esSuperadmin = $grupo ? (bool) $grupo->es_superadmin : false;

        $sucursalesIds = [];
        foreach ($usuario->sucursalesAsignadas as $sucursalAsignada) {
            $sucursalesIds[] = $sucursalAsignada->id;
        }
        $sucursalesIds = array_unique(array_filter($sucursalesIds));
        if (empty($sucursalesIds) && (int) $usuario->sucursal_id > 0) {
            $sucursalesIds[] = (int) $usuario->sucursal_id;
        }

        $permisosFinales = [];
        if ($grupo && $grupo->permisos) {
            foreach ($grupo->permisos as $permiso) {
                $permisosFinales[$permiso->modulo][$permiso->accion] = (bool) $permiso->permitido;
            }
        }
        foreach ($usuario->permisos as $permiso) {
            $permisosFinales[$permiso->modulo][$permiso->accion] = (bool) $permiso->permitido;
        }

        session([
            'usuario'        => $usuario->usuario,
            'nombre'         => $usuario->nombre_tecnico,
            'tecnico_id'     => $usuario->id,
            'sucursal_id'    => $usuario->sucursal_id,
            'sucursales_ids' => $sucursalesIds,
            'grupo_id'       => $usuario->grupo_id ?? 0,
            'grupo_nombre'   => $grupo ? $grupo->nombre : 'Sin grupo',
            'es_superadmin'  => $esSuperadmin,
            'permisos'       => $permisosFinales,
        ]);
    }

    public function cerrarSesion(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    private function normalizarInput(string $valor): string
    {
        $normalizado = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $valor);
        return trim($normalizado ?? $valor);
    }
}
