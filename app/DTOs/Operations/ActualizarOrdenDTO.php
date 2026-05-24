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
        public int $usuario_modificacion_id
    ) {}
}