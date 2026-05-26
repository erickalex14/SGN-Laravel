<?php

namespace App\DTOs\Inventory;

readonly class BuscarRepuestoOrdenDTO
{
    public function __construct(
        public string $q,
        public bool $stock_only
    ) {
    }
}

