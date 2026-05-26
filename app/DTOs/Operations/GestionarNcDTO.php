<?php

namespace App\DTOs\Operations;

readonly class GestionarNcDTO
{
    public function __construct(
        public int $solicitud_id,
        public string $estado, // 'APROBADA' o 'RECHAZADA'
        public ?string $motivo_rechazo,
        public string $nombre_admin
    ) {}
}