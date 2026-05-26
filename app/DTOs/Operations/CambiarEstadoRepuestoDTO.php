<?php

namespace App\DTOs\Operations;

class CambiarEstadoRepuestoDTO
{
    public function __construct(
        public int $orden_id,
        public string $estado_repuesto
    ) {
    }
}

