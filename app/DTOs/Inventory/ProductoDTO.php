<?php

namespace App\DTOs\Inventory;

readonly class ProductoDTO
{
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $descripcion,
        public int $marca_id,
        public int $tipo_dispositivo_id
    ) {}
}
