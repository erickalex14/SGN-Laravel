<?php

namespace App\DTOs\Identity;

readonly class UsuarioDTO
{
    public function __construct(
        public ?int $id,
        public string $usuario,
        public ?string $clave,
        public string $nombre_tecnico,
        public ?string $telefono,
        public ?string $correo_tec,
        public int $rol_id,
        public int $grupo_id,
        public int $sucursal_id,
        public bool $acceso_nc,
        public array $sucursales_secundarias, // Array de IDs
        public array $permisos // Array estructurado: [modulo][accion] => 1|0
    ) {}
}
