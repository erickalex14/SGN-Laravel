<?php

namespace App\DTOs\Identity;

class RolDTO
{
    public readonly string $nombre;

    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }

    //Transforma el objeto a un array nativo

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
        ];
    }
}
