<?php

namespace App\Repositories\Operations;

use App\Models\Inventory\Repuesto;
use App\Models\Operations\OrdenRepuesto;
use Illuminate\Support\Facades\DB;
use Exception;

class OrdenRepuestoRepository
{
    public function registrarDesdeCreacion(int $ordenId, int $repuestoId, int $usuarioId): void
    {
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
            $actualizado = DB::table('repuestos')
                ->where('id', $repuestoId)
                ->where('stock', '>=', 1)
                ->decrement('stock', 1);

            if ($actualizado === 0) {
                throw new Exception("Stock insuficiente para el repuesto '{$repuesto->nombre}'.");
            }
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
        DB::statement("
            CREATE TABLE IF NOT EXISTS orden_repuestos (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                orden_id INT UNSIGNED NOT NULL,
                repuesto_id INT UNSIGNED NOT NULL,
                cantidad INT NOT NULL DEFAULT 1,
                fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                usuario_id INT UNSIGNED NULL,
                INDEX idx_orden_id (orden_id),
                INDEX idx_repuesto_id (repuesto_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}
