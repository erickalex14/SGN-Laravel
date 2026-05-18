<?php

namespace App\DTOs\Inventory;

readonly class TipoDispositivoDTO
{
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $nombre
    ) {}
}
