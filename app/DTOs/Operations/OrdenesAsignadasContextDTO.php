<?php

namespace App\DTOs\Operations;

readonly class OrdenesAsignadasContextDTO
{
    public function __construct(
        public bool $es_superadmin,
        public int $sucursal_id
    ) {
    }
}

