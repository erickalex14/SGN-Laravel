<?php

namespace App\DTOs\Operations;

class BuscarOrdenDTO
{
    public function __construct(
        public string $tipo,
        public string $q,
        public int $sucursal_id,
        public bool $es_superadmin
    ) {
    }
}

