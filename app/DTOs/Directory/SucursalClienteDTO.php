<?php

namespace App\DTOs\Directory;

readonly class SucursalClienteDTO
{
    public function __construct(
        public ?int $id,
        public ?int $numero, // Es null cuando editamos
        public ?string $codigo, // Es null cuando editamos
        public string $nombre,
        public ?string $provincia,
        public ?string $novitec_sucursal,
        public int $activa
    ) {}
}
