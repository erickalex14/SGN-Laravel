
<?php

namespace App\DTOs\Directory;

class EmpresaDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $ruc,
        public readonly string $telefono,
        public readonly string $email,
        public readonly string $direccion_empresa,
    ) {
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'ruc' => $this->ruc,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion_empresa' => $this->direccion_empresa,
        ];
    }

}
