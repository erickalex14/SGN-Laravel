<?php

namespace App\Repositories\Directory;

use App\Models\Directory\Cliente;

class ClienteRepository
{
    public function buscarPorIdentificacion(string $identificacion): ?Cliente
    {
        return Cliente::where('identificacion', $identificacion)->first();
    }

    public function actualizarOCrear(array $datos): Cliente
    {
        return Cliente::updateOrCreate(
            ['identificacion' => $datos['identificacion']],
            $datos
        );
    }
}