<?php

namespace App\DTOs\Operations;

readonly class ReporteFiltroDTO
{
    public function __construct(
        public ?string $fecha_inicio,
        public ?string $fecha_fin,
        public ?string $estado,
        public ?string $estado_repuesto,
        public ?string $estado_garantia,
        public ?string $motivo_ingreso,
        public ?string $marca,
        public ?string $tipo_equipo,
        public ?string $tipo_orden,
        public ?int $tecnico_id,
        public ?int $sucursal_id,
        public ?int $cas_id
    ) {}
}
