<?php

namespace App\DTOs\Directory;

readonly class EmpresaDTO
{
    public function __construct(
        public ?int $id,
        public string $nombre,
        public string $ruc,
        public ?string $telefono,
        public ?string $correo,
        public ?string $direccion
    ) {}
}
