<?php

namespace App\DTOs\Directory;

readonly class SucursalDTO
{
    public function __construct(
        public?int $id,
        public int $nro_sucursal,
        public string $ciudad,
        public string $secuencial,
        public ?string $nro_base,
    ) {
    }
}
