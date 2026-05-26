<?php

namespace App\DTOs\Operations;

readonly class CambiarEstadoOrdenDTO
{
    public function __construct(
        public int $orden_id,
        public string $estado_orden,
        public ?string $nc_asunto = null,
        public ?string $nc_detalles = null
    ) {}
}
