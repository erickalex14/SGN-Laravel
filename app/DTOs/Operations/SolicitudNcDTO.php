<?php

namespace App\DTOs\Operations;

readonly class SolicitudNcDTO
{
    public function __construct(
        public int $orden_id,
        public string $asunto,
        public string $detalles,
        public int $tecnico_id,
        public string $tecnico_nombre
    ) {}
}