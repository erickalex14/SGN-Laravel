<?php

namespace App\DTOs\Directory;

class SucursalDTO
{
    public function __construct(
        public readonly string $nombreSucursal,
        public readonly ?string $ciudad = null,
        public readonly ?string $secuencial = null,
        public readonly ?string $nroCaso = null
    ) {}

    public function toArray(): array
    {
        return [
            'nombre_sucursal' => $this->nombreSucursal,
            'ciudad' => $this->ciudad,
            'secuencial' => $this->secuencial,
            'nro_caso' => $this->nroCaso,
        ];
    }
}
