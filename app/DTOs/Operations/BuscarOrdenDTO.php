<?php

namespace App\DTOs\Operations;

class BuscarOrdenDTO
{
    public function __construct(
        public string $tipo,       // nro_orden | cedula | nombre | serie | factura | tecnico | empresa
        public string $q,
        public int    $sucursal_id,
        public bool   $es_superadmin,
        // Filtros adicionales (opcionales, para búsqueda avanzada)
        public string $estado      = '',
        public int    $tecnico_id  = 0,
        public string $fecha_desde = '',
        public string $fecha_hasta = '',
    ) {}
}
