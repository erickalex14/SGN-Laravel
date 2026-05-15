<?php

namespace App\Repositories\Contracts\Identity;

interface UsuarioRepositoryInterface
{
    public function buscarPorCredenciales(string $usuario, string $clave): ?object;
}
