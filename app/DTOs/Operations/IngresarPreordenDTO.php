<?php

namespace App\DTOs\Operations;

readonly class IngresarPreordenDTO
{
    public function __construct(
        public int $preorden_id,
        public int $tecnico_id,
        public int $usuario_sesion_id,
        public int $sucursal_sesion_id,
        public bool $es_superadmin,
        public string $direccion,
        public string $serie,
        public string $observacion,
        public string $fecha_prometido
    ) {
    }
}

