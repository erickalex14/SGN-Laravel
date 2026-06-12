<?php

namespace App\DTOs\Operations;

readonly class ActualizarOrdenDTO
{
    public function __construct(
        public int $orden_id,
        public int $equipo_id,
        public string $estado_orden,
        public string $falla,
        public ?string $observacion,
        public ?int $tipo_servicio_id,
        public ?int $valor_estandar_id,
        public ?int $repuesto_inventario_id,
        public ?string $fecha_prometido,
        public int $usuario_modificacion_id,
        public ?int $cas_id = null,
        
        // Campos de cliente
        public string $cli_identificacion = '',
        public string $cli_nombres = '',
        public string $cli_apellidos = '',
        public string $cli_telefono = '',
        public ?string $cli_correo = null,
        public ?string $cli_direccion = null,

        // Campos de factura/garantía
        public ?string $nro_factura = null,
        public ?string $nro_factura_2 = null,
        public ?int $nro_sucursal_cliente = null,
        public ?string $fecha_facturacion = null,

        // Series
        public array $series = [],
        public ?int $tecnico_id = null
    ) {}
}