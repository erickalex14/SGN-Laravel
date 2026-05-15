<?php

namespace App\DTOs\Identity;

class LoginDTO
{
    public function __construct(
        public readonly string $usuario,
        public readonly string $clave
    ) {}
}
