<?php

namespace App\Services\Directory;
use App\DTOs\Directory\ClienteDTO;
use App\Repositories\Contracts\Directory\ClienteRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class ClienteService
{
    protected ClienteRepositoryInterface $clienteRepository;

    public function __construct(ClienteRepositoryInterface $clienteRepository)
    {
        $this->clienteRepository = $clienteRepository;
    }

    public function registrarCliente(ClienteDTO $dto): object
    {
        Log::info('Iniciando proceso de registro para nuevo cliente B2C.', [
            'identificacion' => $dto->identificacion
        ]);

        try {
            $clienteExistente = $this->clienteRepository->buscarPorIdentificacion($dto->identificacion);
            if ($clienteExistente) {
                Log::warning('Intento de registro de cliente con identificacion duplicada.', [
                    'identificacion' => $dto->identificacion
                ]);
                throw new Exception('El numero de identificacion ingresado ya pertenece a un cliente registrado.');
            }

            $cliente = $this->clienteRepository->crear($dto->toArray());

            Log::info('Cliente registrado exitosamente en el sistema.', [
                'cliente_id' => $cliente->id
            ]);

            return $cliente;

        }catch (Exception $e) {
            Log::error('Error al registrar cliente B2C.', [
                'identificacion' => $dto->identificacion,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function listarClientes(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->clienteRepository->obtenerTodos();
    }

    public function modificarCliente(int $id, ClienteDTO $dto): bool
    {
        Log::info('Iniciando actualizacion de datos del cliente B2C.', ['cliente_id' => $id]);

        try {
            $actualizado = $this->clienteRepository->actualizar($id, $dto->toArray());

            if (!$actualizado) {
                Log::warning('Intento de actualizacion sobre cliente inexistente.', ['cliente_id' => $id]);
                throw new Exception('El cliente solicitado no existe en la base de datos.');
            }

            Log::info('Datos del cliente actualizados exitosamente.', ['cliente_id' => $id]);
            return true;

        } catch (Exception $e) {
            Log::error('Fallo critico al modificar el cliente.', ['mensaje' => $e->getMessage()]);
            throw $e;
        }
    }

    public function removerCliente(int $id): bool
    {
        Log::info('Iniciando proceso de eliminacion de cliente.', ['cliente_id' => $id]);

        try {
            $eliminado = $this->clienteRepository->eliminar($id);
            if (!$eliminado) {
                throw new Exception('No se encontro el registro del cliente para eliminar.');
            }

            Log::info('Registro de cliente eliminado del sistema.', ['cliente_id' => $id]);
            return true;
        } catch (Exception $e) {
            Log::error('Error al intentar eliminar el cliente.', ['mensaje' => $e->getMessage()]);
            throw $e;
        }
    }

}
