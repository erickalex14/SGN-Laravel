<?php

namespace App\DTOs\Inventory;

readonly class ListaCompraDTO
{
    public function __construct(
        public array $solicitudes_ids, // IDs de SolicitudesRepuesto a agrupar
        public ?string $observacion
    ) {}
}