<?php

namespace App\DTOs\Inventory;

readonly class MarcaDTO
{
    public function __construct(
        public ?int $id,
        public string $nombre
    ) {}
}
