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
        public ?string $contrasena_equipo,
        public string $falla,
        public ?string $observacion,
        public ?int $tipo_servicio_id,
        public ?string $tipo_servicio_texto,
        public ?string $producto_inventario_codigo,
        public array $series,
        public array $credenciales,

        // Datos de la Orden
        public int $sucursal_id,
        public int $tecnico_id,
        public int $ingresado_por,
        public string $fecha_ingreso,
        public string $motivo_ingreso,
        public ?string $nro_factura,
        public ?string $nro_factura_2,
        public ?string $fecha_facturacion,
        public ?string $fecha_prometido,
        public ?string $nro_sucursal_cliente,
        public ?string $estado_repuesto,
        public ?string $garantia_tipo,
        public ?int $cas_id,
        public ?int $repuesto_inventario_id,
        public ?array $repuestos_seleccionados = null
    ) {}
}
