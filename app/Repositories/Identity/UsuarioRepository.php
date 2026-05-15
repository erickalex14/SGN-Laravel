<?php

namespace App\Repositories\Identity;

use App\Models\Identity\Usuario;
use App\Repositories\Contracts\Identity\UsuarioRepositoryInterface;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    public function buscarPorCredenciales(string $usuario, string $clave): ?object
    {
        // Se realiza la comparacion en texto plano segun requerimiento de produccion
        return Usuario::where('usuario', $usuario)
            ->where('clave', $clave)
            ->first();
    }
}
