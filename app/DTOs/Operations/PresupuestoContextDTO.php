<?php

namespace App\DTOs\Operations;

readonly class PresupuestoContextDTO
{
    public function __construct(
        public int $tecnico_id,
        public int $sucursal_id,
        public bool $es_admin,
        public bool $puede_ver_asignadas
    ) {
    }
}

