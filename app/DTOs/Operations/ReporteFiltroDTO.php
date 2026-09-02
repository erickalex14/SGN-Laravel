<?php

namespace App\DTOs\Operations;

readonly class ReporteFiltroDTO
{
    public function __construct(
        public ?string $fecha_inicio = null,
        public ?string $fecha_fin = null,
        public ?string $estado = null,
        public ?string $estado_repuesto = null,
        public ?string $estado_garantia = null,
        public ?string $motivo_ingreso = null,
        public ?string $marca = null,
        public ?string $tipo_equipo = null,
        public ?string $tipo_orden = null,
        public ?int $tecnico_id = null,
        public ?int $sucursal_id = null,
        public ?int $cas_id = null,
        public ?int $empresa_id = null,
        public ?string $garantia_tipo = null
    ) {}
}
