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
    ) {
    }

    /**
     * @throws Exception
     */
    public function buscar(BuscarOrdenDTO $dto): Collection
    {
        if (trim($dto->q) === '') {
            throw new Exception('Ingresa un valor para buscar.');
        }

        return $this->repository->buscar($dto);
    }
}

