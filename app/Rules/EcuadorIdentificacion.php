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
        if (!str_ends_with($value, '001')) {
            return false;
        }
        $provincia = (int) substr($value, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            return false;
        }
        $tercerDigito = (int) $value[2];
        if ($tercerDigito < 6) {
            // RUC natural person: first 10 digits must be a valid cédula
            $cedula = substr($value, 0, 10);
            return $this->validarCedula($cedula);
        } elseif ($tercerDigito === 9) {
            // RUC private juridical entity
            $digitoVerificador = (int) $value[9];
            $coeficientes = [4, 3, 2, 7, 6, 5, 4, 3, 2];
            $suma = 0;
            for ($i = 0; $i < 9; $i++) {
                $suma += (int)$value[$i] * $coeficientes[$i];
            }
            $residuo = $suma % 11;
            $resultado = ($residuo === 0) ? 0 : 11 - $residuo;
            return $resultado === $digitoVerificador;
        } elseif ($tercerDigito === 6) {
            // RUC public entity
            $digitoVerificador = (int) $value[8];
            $coeficientes = [3, 2, 7, 6, 5, 4, 3, 2];
            $suma = 0;
            for ($i = 0; $i < 8; $i++) {
                $suma += (int)$value[$i] * $coeficientes[$i];
            }
            $residuo = $suma % 11;
            $resultado = ($residuo === 0) ? 0 : 11 - $residuo;
            return $resultado === $digitoVerificador;
        }
        return false;
    }
}
