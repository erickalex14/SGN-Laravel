<?php

namespace App\Services\Operations;

use App\DTOs\Operations\OrdenesAsignadasContextDTO;
use App\Repositories\Operations\OrdenesAsignadasRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
                'en_curso' => [],
                'entregadas' => [],
            ];
        }

        return $porTecnico;
    }

    public function obtenerOrdenesPaginadas(
        int $tecnicoId,
        bool $entregadas,
        int $perPage = 12,
        ?string $q = null,
        ?string $estado = null,
        ?string $motivo = null,
        ?string $repuesto = null
    ): LengthAwarePaginator {
        return $this->repository->obtenerOrdenesPorTecnicoPaginado(
            $tecnicoId,
            $entregadas,
            $perPage,
            $q,
            $estado,
            $motivo,
            $repuesto
        );
    }
}
