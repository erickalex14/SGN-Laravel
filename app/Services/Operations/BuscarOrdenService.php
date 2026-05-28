<?php

namespace App\Services\Operations;

use App\DTOs\Operations\BuscarOrdenDTO;
use App\Repositories\Operations\BuscarOrdenRepository;
use Illuminate\Support\Collection;
use Exception;

class BuscarOrdenService
{
    public function __construct(
        protected BuscarOrdenRepository $repository
    ) {}

    /**
     * @throws Exception
     */
    public function buscar(BuscarOrdenDTO $dto): Collection
    {
        $q       = trim($dto->q);
        $hayFiltros = $dto->estado !== ''
            || $dto->tecnico_id > 0
            || $dto->fecha_desde !== ''
            || $dto->fecha_hasta !== '';

        if ($q === '' && !$hayFiltros) {
            throw new Exception('Ingresa un valor para buscar o selecciona al menos un filtro.');
        }

        return $this->repository->buscar($dto);
    }
}
