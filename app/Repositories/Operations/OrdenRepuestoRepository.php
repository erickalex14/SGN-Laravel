<?php

namespace App\Repositories\Operations;

use App\Models\Inventory\Repuesto;
use App\Models\Operations\OrdenRepuesto;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrdenRepuestoRepository
{
    public function registrarDesdeCreacion(int $ordenId, int $repuestoId, int $usuarioId): void
    {
        if (!$this->tablaOrdenRepuestosDisponible()) {
            $repuesto = Repuesto::find($repuestoId);
            if (!$repuesto) {
                throw new Exception('El repuesto seleccionado no existe.');
            }

            $this->descontarStock($repuesto);
            Log::warning('No se pudo registrar orden_repuestos durante la creacion; se desconto stock usando compatibilidad legacy.', [
                'orden_id' => $ordenId,
                'repuesto_id' => $repuestoId,
                'usuario_id' => $usuarioId,
            ]);
            return;
        }

        $this->asignarRepuestoEnOrden($ordenId, $repuestoId, $usuarioId, true);
    }

    /**
     * @throws Exception
     */
    public function asignarRepuestoEnOrden(int $ordenId, int $repuestoId, int $usuarioId, bool $descontarStock = true): void
    {
        $this->asegurarTablaOrdenRepuestos();

        $repuesto = Repuesto::find($repuestoId);
        if (!$repuesto) {
            throw new Exception('El repuesto seleccionado no existe.');
        }

        $existeVinculo = OrdenRepuesto::query()
            ->where('orden_id', $ordenId)
            ->where('repuesto_id', $repuestoId)
            ->exists();

        if ($existeVinculo) {
            throw new Exception('Este repuesto ya fue agregado a esta orden.');
        }

        if ($descontarStock) {
            $this->descontarStock($repuesto);
        }

        $item = new OrdenRepuesto();
        $item->orden_id = $ordenId;
        $item->repuesto_id = $repuestoId;
        $item->cantidad = 1;
        $item->usuario_id = $usuarioId > 0 ? $usuarioId : null;
        $item->save();
    }

    public function revertirRepuestosDeOrden(int $ordenId, ?int $repuestoId = null): void
    {
        $this->asegurarTablaOrdenRepuestos();

        $query = OrdenRepuesto::query()->where('orden_id', $ordenId);
        if ($repuestoId !== null && $repuestoId > 0) {
            $query->where('repuesto_id', $repuestoId);
        }

        $items = $query->get();
        foreach ($items as $item) {
            DB::table('repuestos')
                ->where('id', $item->repuesto_id)
                ->increment('stock', max(1, (int) $item->cantidad));
        }

        $query->delete();
    }

    private function asegurarTablaOrdenRepuestos(): void
    {
        if ($this->tablaOrdenRepuestosDisponible()) {
            return;
        }

        throw new Exception("Falta la tabla 'orden_repuestos'. Debe existir en el esquema antes de asignar/revertir repuestos.");
    }

    private function tablaOrdenRepuestosDisponible(): bool
    {
        try {
            DB::table('orden_repuestos')->select('id')->limit(1)->get();
            return true;
        } catch (QueryException $e) {
            $sqlState = (string) ($e->errorInfo[0] ?? '');
            if ($sqlState === '42S02') {
                return false;
            }

            throw $e;
        }
    }

    private function descontarStock(Repuesto $repuesto): void
    {
        $actualizado = DB::table('repuestos')
            ->where('id', $repuesto->id)
            ->where('stock', '>=', 1)
            ->decrement('stock', 1);

        if ($actualizado === 0) {
            throw new Exception("Stock insuficiente para el repuesto '{$repuesto->nombre}'.");
        }
    }
}
