<?php

namespace App\DTOs\Identity;

readonly class MiCuentaPasswordDTO
{
    public function __construct(
        public int $usuario_id,
        public string $actual,
        public string $nueva
    ) {
    }
}

