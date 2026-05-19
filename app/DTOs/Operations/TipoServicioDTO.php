<?php

namespace App\DTOs\Operations;

readonly class TipoServicioDTO
{
    public function __construct(
        public ?int $id,
        public string $nombre,
        public float $precio,
        public ?string $descripcion,
        public int $activo
    ) {}
}
