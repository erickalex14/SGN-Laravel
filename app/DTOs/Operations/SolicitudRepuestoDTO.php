<?php

namespace App\DTOs\Operations;

readonly class SolicitudRepuestoDTO
{
    public function __construct(
        public int $orden_id,
        public int $tecnico_id,
        public string $tecnico_nombre,
        public ?string $repuesto_nombre,
        public ?string $nro_parte,
        public ?string $link_compra,
        public int $cantidad,
        public ?string $descripcion,
        // Si seleccionó uno del catálogo existente:
        public ?int $repuesto_inv_id 
    ) {}
}