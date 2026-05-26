<?php

namespace App\DTOs\Operations;

readonly class ReporteFiltroDTO
{
    public function __construct(
        public ?string $fecha_inicio,
        public ?string $fecha_fin,
        public ?string $estado,
        public ?int $tecnico_id,
        public ?int $sucursal_id
    ) {}
}