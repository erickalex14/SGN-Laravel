<?php

namespace App\DTOs\Operations;

readonly class GestionarSolicitudRepuestoDTO
{
    public function __construct(
        public int $solicitud_id,
        public string $estado, // APROBADA, RECHAZADA, COMPRA
        public ?string $motivo_rechazo,
        public string $aprobado_por
    ) {}
}