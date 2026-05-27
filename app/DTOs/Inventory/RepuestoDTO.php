<?php

namespace App\DTOs\Inventory;

readonly class RepuestoDTO
{
    public function __construct(
        public ?int $id,
        public string $codigo,
        public ?string $nro_parte,
        public string $nombre,
        public int $stock,
        public float $costo,
        public ?string $bodega,
        public ?string $descripcion,
        public ?string $marca_id, // Legacy lo define como varchar(36)
        public ?string $tipo_dispositivo_id // Legacy lo define como varchar(36)
    ) {}
}
