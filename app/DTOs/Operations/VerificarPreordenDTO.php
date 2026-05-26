<?php

namespace App\DTOs\Operations;

readonly class VerificarPreordenDTO
{
    public function __construct(
        public string $ci,
        public string $codigo
    ) {
    }
}

