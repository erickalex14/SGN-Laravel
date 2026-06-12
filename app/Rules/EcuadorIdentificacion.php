<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EcuadorIdentificacion implements ValidationRule
{
    protected string $type; // 'both', 'cedula', 'ruc'

    public function __construct(string $type = 'both')
    {
        $this->type = $type;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) return;
        $value = trim((string)$value);
        $len = strlen($value);

        if ($this->type === 'cedula' && $len !== 10) {
            $fail('La identificación debe ser una cédula de 10 dígitos.');
            return;
        }

        if ($this->type === 'ruc' && $len !== 13) {
            $fail('La identificación debe ser un RUC de 13 dígitos.');
            return;
        }

        if ($len !== 10 && $len !== 13) {
            $fail('La identificación debe tener 10 dígitos (cédula) o 13 dígitos (RUC).');
            return;
        }

        if (!ctype_digit($value)) {
            $fail('La identificación sólo debe contener números.');
            return;
        }

        if ($len === 10) {
            // Validar cédula
            if (!$this->validarCedula($value)) {
                $fail('La cédula ingresada no es válida.');
            }
        } else {
            // Validar RUC
            if (!$this->validarRuc($value)) {
                $fail('El RUC ingresado no es válido.');
            }
        }
    }

    protected function validarCedula(string $value): bool
    {
        $provincia = (int) substr($value, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            return false;
        }

        return true;
    }

    protected function validarRuc(string $value): bool
    {
        // El RUC debe terminar en un código de establecimiento diferente de 000 (ej: 001, 002, etc.)
        $establecimiento = substr($value, 10, 3);
        if ($establecimiento === '000') {
            return false;
        }

        // Validar provincia (primeros 2 dígitos entre 01 y 24, o 30)
        $provincia = (int) substr($value, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            return false;
        }

        return true;
    }
}
