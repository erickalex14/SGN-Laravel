<?php

namespace App\Services\Operations;

use App\DTOs\Operations\OrdenesAsignadasContextDTO;
use App\Repositories\Operations\OrdenesAsignadasRepository;

class OrdenesAsignadasService
{
    protected OrdenesAsignadasRepository $repository;

    public function __construct(OrdenesAsignadasRepository $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerAgrupadas(OrdenesAsignadasContextDTO $contexto): array
    {
        $tecnicos = $this->repository->obtenerTecnicosConOrdenes(
            $contexto->es_superadmin,
            $contexto->sucursal_id
        );

        $porTecnico = [];
        foreach ($tecnicos as $tecnico) {
            $porTecnico[] = [
                'tecnico' => $tecnico,
                'en_curso' => $this->repository->obtenerOrdenesPorTecnico((int) $tecnico->id, false),
                'entregadas' => $this->repository->obtenerOrdenesPorTecnico((int) $tecnico->id, true),
            ];
        }

        return $porTecnico;
    }
}

