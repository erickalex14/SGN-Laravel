<?php

namespace App\DTOs\Operations;

class AsignarRepuestoOrdenDTO
{
    public function __construct(
        public int $orden_id,
        public int $repuesto_inventario_id
    ) {
    }
}

