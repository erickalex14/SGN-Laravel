<?php

namespace App\DTOs\Operations;

readonly class PrecioEstandarDTO
{
    public function __construct(
        public ?int $id,
        public string $servicio,
        public float $precio,
        public ?string $descripcion,
        public int $activo
    ) {}
}
