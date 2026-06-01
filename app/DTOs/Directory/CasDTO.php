<?php

namespace App\DTOs\Directory;

readonly class CasDTO
{
    public function __construct(
        public ?int $id,
        public string $nombre,
        public ?string $marca, // IDs separados por comas
        public ?string $telefono,
        public ?string $correo,
        public ?string $ciudad,
        public ?string $direccion,
        public ?string $contacto,
        public ?string $notas,
        public int $activo,
        public ?string $prefijo = null
    ){}
}
