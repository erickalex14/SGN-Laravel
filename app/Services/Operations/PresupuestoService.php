<?php

namespace App\Services\Operations;

use App\DTOs\Operations\PresupuestoContextDTO;
use App\Repositories\Operations\PresupuestoRepository;

class PresupuestoService
{
    protected PresupuestoRepository $repository;

    public function __construct(PresupuestoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerContextoIndex(PresupuestoContextDTO $contexto): array
    {
        return [
            'ordenes' => $this->repository->obtenerOrdenes($contexto),
            'catalogo' => $this->repository->obtenerCatalogoActivo(),
        ];
    }
}

