<?php

namespace App\DTOs\Operations;

readonly class CrearOrdenDTO
{
    public function __construct(
        // Datos del Cliente
        public ?int $cliente_id,
        public string $identificacion,
        public string $nombres,
        public string $apellidos,
        public string $telefono,
        public ?string $correo,
        public ?string $direccion,

        // Datos del Equipo
        public string $tipo_equipo,
        public string $marca,
        public string $modelo,
        public string $serie,
        public ?string $contrasena_equipo,
        public string $falla,
        public ?string $observacion,
        public ?int $tipo_servicio_id,
        public ?string $tipo_servicio_texto,
        public ?string $producto_inventario_codigo,

        // Datos de la Orden
        public int $sucursal_id,
        public int $tecnico_id,
        public int $ingresado_por,
        public string $fecha_ingreso,
        public ?string $motivo_ingreso
    ) {}
}