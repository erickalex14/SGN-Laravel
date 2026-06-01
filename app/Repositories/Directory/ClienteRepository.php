<?php

namespace App\Repositories\Directory;

use App\Models\Directory\Cliente;

class ClienteRepository
{
    public function buscarPorIdentificacion(string $identificacion): ?Cliente
    {
        $identificacion = trim($identificacion);
        if ($identificacion === '') {
            return null;
        }

        // 1. Coincidencia exacta con limpieza
        $cliente = Cliente::whereRaw('UPPER(TRIM(identificacion)) = ?', [strtoupper($identificacion)])->first();
        if ($cliente) {
            return $cliente;
        }

        // 2. Coincidencia inteligente Cédula vs RUC (Ecuador)
        $len = strlen($identificacion);
        if ($len === 10 && ctype_digit($identificacion)) {
            // Cédula de 10 dígitos -> Buscar también el RUC (añadiendo '001')
            $ruc = $identificacion . '001';
            $cliente = Cliente::whereRaw('UPPER(TRIM(identificacion)) = ?', [$ruc])->first();
            if ($cliente) {
                return $cliente;
            }
        } elseif ($len === 13 && ctype_digit($identificacion) && str_ends_with($identificacion, '001')) {
            // RUC de 13 dígitos -> Buscar también la cédula (primeros 10 dígitos)
            $cedula = substr($identificacion, 0, 10);
            $cliente = Cliente::whereRaw('UPPER(TRIM(identificacion)) = ?', [$cedula])->first();
            if ($cliente) {
                return $cliente;
            }
        }

        return null;
    }

    public function actualizarOCrear(array $datos): Cliente
    {
        return Cliente::updateOrCreate(
            ['identificacion' => $datos['identificacion']],
            $datos
        );
    }
}