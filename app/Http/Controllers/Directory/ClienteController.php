<?php

namespace App\Http\Controllers\Directory;

use App\DTOs\Directory\ClienteDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\Cliente\StoreClienteRequest;
use App\Http\Requests\Directory\Cliente\UpdateClienteRequest;
use App\Services\Directory\ClienteService;
use Exception;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    protected ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    //Guardar cliente
    public function store(StoreClienteRequest $request): JsonResponse
    {
        try {
            $dto = new ClienteDTO(
                $request->input('nombres'),
                $request->input('apellidos'),
                $request->input('identificacion'),
                $request->input('numero_contacto'),
                $request->input('correo'),
                $request->input('direccion_clientes')
            );

            $this->clienteService->registrarCliente($dto);

            return response()->json([
                'status' => 'success',
                'message' => 'El registro del cliente se ha completado satisfactoriamente.'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Se ha producido un error interno. Consulte los registros del sistema.'
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->clienteService->listarClientes();
            return response()->json(['status' => 'success', 'data' => $clientes], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al recuperar el listado de clientes.'], 500);
        }
    }

    public function update(UpdateClienteRequest $request, int $id): JsonResponse
    {
        try {
            $dto = new ClienteDTO(
                $request->input('nombres'),
                $request->input('apellidos'),
                $request->input('identificacion'),
                $request->input('numero_contacto'),
                $request->input('correo'),
                $request->input('direccion_clientes')
            );

            $this->clienteService->modificarCliente($id, $dto);

            return response()->json(['status' => 'success', 'message' => 'Cliente actualizado correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->clienteService->removerCliente($id);
            return response()->json(['status' => 'success', 'message' => 'Cliente eliminado del sistema.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
