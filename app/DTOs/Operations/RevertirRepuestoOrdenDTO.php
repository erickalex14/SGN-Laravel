<?php

namespace App\DTOs\Operations;

class RevertirRepuestoOrdenDTO
{
    public function __construct(
        public int $orden_id,
        public ?int $repuesto_id = null
    ) {
    }
}

