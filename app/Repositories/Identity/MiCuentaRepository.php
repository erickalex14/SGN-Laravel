<?php

namespace App\Repositories\Identity;

use App\Models\Identity\Usuario;

class MiCuentaRepository
{
    public function buscarPorId(int $id): ?Usuario
    {
        return Usuario::find($id);
    }

    public function guardar(Usuario $usuario): void
    {
        $usuario->save();
    }
}

