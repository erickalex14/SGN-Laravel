<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EcuadorIdentificacion implements ValidationRule
{
    protected string $type; // 'both', 'cedula', 'ruc', 'pasaporte'

    public function __construct(string $type = 'both')
    {
        $type = strtolower(trim($type));
        $this->type = in_array($type, ['cedula', 'ruc', 'pasaporte'], true) ? $type : 'both';
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }
        $value = trim((string) $value);
        $len = strlen($value);

        // Modo Explícito: Pasaporte (permite cualquier pasaporte alfanumérico entre 3 y 30 caracteres)
        if ($this->type === 'pasaporte') {
            if ($len < 3 || $len > 30) {
                $fail('El pasaporte debe tener entre 3 y 30 caracteres.');
                return;
            }
            if (!preg_match('/^[A-Za-z0-9\-\.]+$/', $value)) {
                $fail('El pasaporte contiene caracteres no válidos.');
                return;
            }
            return;
        }

        // Modo Explícito: Cédula (exactamente 10 dígitos numéricos)
        if ($this->type === 'cedula') {
            if ($len !== 10) {
                $fail('La cédula debe tener exactamente 10 dígitos.');
                return;
            }
            if (!ctype_digit($value)) {
                $fail('La cédula sólo debe contener números.');
                return;
            }
            if (!$this->validarCedula($value)) {
                $fail('La cédula ingresada no es válida.');
            }
            return;
        }

        // Modo Explícito: RUC (exactamente 13 dígitos numéricos)
        if ($this->type === 'ruc') {
            if ($len !== 13) {
                $fail('El RUC debe tener exactamente 13 dígitos.');
                return;
            }
            if (!ctype_digit($value)) {
                $fail('El RUC sólo debe contener números.');
                return;
            }
            if (!$this->validarRuc($value)) {
                $fail('El RUC ingresado no es válido.');
            }
            return;
        }

        // Modo 'both' (cuando no se especifica el tipo explícito)
        $esPasaporte = false;
        if (preg_match('/^(?=.*[A-Za-z])[A-Za-z0-9\-\.]{3,30}$/', $value)) {
            $esPasaporte = true;
        }

        if (! $esPasaporte) {
            if ($len !== 10 && $len !== 13) {
                $fail('La identificación debe ser una cédula (10 dígitos), RUC (13 dígitos) o un pasaporte válido.');
                return;
            }

            if (! ctype_digit($value)) {
                $fail('La identificación sólo debe contener números.');
                return;
            }

            if ($len === 10) {
                // Validar cédula
                if (! $this->validarCedula($value)) {
                    $fail('La cédula ingresada no es válida.');
                }
            } else {
                // Validar RUC
                if (! $this->validarRuc($value)) {
                    $fail('El RUC ingresado no es válido.');
                }
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
