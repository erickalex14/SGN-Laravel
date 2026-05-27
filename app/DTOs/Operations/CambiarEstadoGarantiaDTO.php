<?php

namespace App\DTOs\Operations;

readonly class CambiarEstadoGarantiaDTO
{
    public function __construct(
        public int $orden_id,
        public string $estado_garantia
    ) {}
}
