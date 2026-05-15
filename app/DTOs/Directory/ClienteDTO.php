<?php

namespace App\DTOs\Directory;

class ClienteDTO
{
    public function __construct(
        public readonly string $nombres,
        public readonly string $apellidos,
        public readonly string $identificacion,
        public readonly ?string $numeroContacto = null,
        public readonly ?string $correo = null,
        public readonly ?string $direccionClientes = null
    ){}

    public function toArray(): array
    {
        return [
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'identificacion' => $this->identificacion,
            'numero_contacto' => $this->numeroContacto,
            'correo' => $this->correo,
            'direccion_clientes' => $this->direccionClientes,
        ];
    }
}
