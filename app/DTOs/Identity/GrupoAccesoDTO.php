<?php

namespace App\DTOs\Identity;

readonly class GrupoAccesoDTO
{
    public function __construct(
        public ?int $id,
        public string $nombre,
        public ?string $descripcion,
        public bool $es_superadmin
    ) {}
}
