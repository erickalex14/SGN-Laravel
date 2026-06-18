<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\ListaCompraRepository;
use App\DTOs\Inventory\ListaCompraDTO;
use App\Models\Inventory\ListaCompra;
use App\Models\Operations\SolicitudRepuesto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ListaCompraService
{
    protected ListaCompraRepository $repository;

    public function __construct(ListaCompraRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function generarLista(ListaCompraDTO $dto, int $usuarioId, string $usuarioNombre): string
    {
        try {
            return DB::transaction(function () use ($dto, $usuarioId, $usuarioNombre) {
                $nroLista = $this->repository->generarNumeroLista();

                $lista = new ListaCompra();
                $lista->nro_lista      = $nroLista;
                $lista->creado_por     = $usuarioNombre;
                $lista->creado_por_id  = $usuarioId;
                $lista->fecha_creacion = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');
                // La lista ya sale aprobada para compra al momento de consolidarse.
                $lista->estado         = 'Completada';
                $lista->observacion    = trim($dto->observacion);
                $lista->save();

                // Asociar las solicitudes seleccionadas a la nueva lista de compra
                SolicitudRepuesto::whereIn('id', $dto->solicitudes_ids)
                    ->update(['lista_compra_id' => $lista->id]);

                Log::info('Lista de compra generada exitosamente.', [
                    'lista_id'  => $lista->id,
                    'nro_lista' => $nroLista,
                    'items'     => count($dto->solicitudes_ids)
                ]);

                return $nroLista;
            });
        } catch (Exception $e) {
            Log::error('Fallo transaccional al generar lista de compra.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error interno al consolidar la lista de compras.');
        }
    }
}
