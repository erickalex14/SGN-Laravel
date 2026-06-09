<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EcuadorTelefono implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) return;
        $value = trim((string)$value);

        // Regex for Ecuadorian cellular (10 digits starting with 09)
        // or conventional (9 digits starting with 0 followed by 2-7)
        if (!preg_match('/^(09[0-9]{8}|0[2-7][0-9]{7})$/', $value)) {
            $fail('El teléfono debe ser un celular de 10 dígitos (ej: 0987654321) o convencional de 9 dígitos (ej: 022345678) en formato de Ecuador.');
        }
    }
}
