<?php

namespace App\DTOs\Identity;

readonly class LoginDTO
{
    public function __construct(
        public string $usuario,
        public string $clave
    ) {
    }
}
