<?php

namespace App\DTOs\Identity;

readonly class MiCuentaPerfilDTO
{
    public function __construct(
        public int $usuario_id,
        public string $nombre
    ) {
    }
}

