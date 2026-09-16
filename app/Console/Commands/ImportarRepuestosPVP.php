<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory\Repuesto;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use Illuminate\Support\Facades\DB;

class ImportarRepuestosPVP extends Command
{
    protected $signature = 'repuestos:importar-pvp {--recalcular-ordenes : Recalcular valor_repuestos en ordenes existentes}';
    protected $description = 'Importa y sincroniza el inventario de repuestos con precios PVP desde el archivo JSON y recalcula órdenes';

    public function handle(): int
    {
        $jsonPath = database_path('data/repuestos_pvp.json');
        if (!file_exists($jsonPath)) {
            $this->error("No se encontró el archivo {$jsonPath}");
            return 1;
        }

        $items = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($items)) {
            $this->error("Formato de JSON inválido");
            return 1;
        }

        $this->info("Iniciando importación de " . count($items) . " repuestos...");

        $actualizados = 0;
        $creados = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $it) {
                $codigo = trim((string) ($it['codigo'] ?? ''));
                if ($codigo === '') {
                    continue;
                }

                $costo = round((float) ($it['costo'] ?? 0.0), 2);
                $pvp = round((float) ($it['pvp'] ?? 0.0), 2);
                $stock = (int) ($it['stock'] ?? 0);
                $nombre = trim((string) ($it['nombre'] ?? $codigo));

                $repuesto = Repuesto::where('codigo', $codigo)->first();

                if ($repuesto) {
                    $repuesto->costo = $costo;
                    $repuesto->pvp = $pvp;
                    $repuesto->stock = $stock;
                    if (empty($repuesto->nombre) || $repuesto->nombre === $codigo) {
                        $repuesto->nombre = $nombre;
                    }
                    $repuesto->save();
                    $actualizados++;
                } else {
                    Repuesto::create([
                        'codigo' => $codigo,
                        'nombre' => $nombre,
                        'costo' => $costo,
                        'pvp' => $pvp,
                        'stock' => $stock,
                        'bodega' => 1
                    ]);
                    $creados++;
                }
            }

            DB::commit();
            $this->info("Importación completada: {$actualizados} actualizados, {$creados} creados.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Error durante la importación: " . $e->getMessage());
            return 1;
        }

        // Recalcular órdenes que tienen repuestos asociados en orden_repuestos
        $this->info("Recalculando valor de repuestos con PVP en órdenes existentes...");
        $ordenesRecalculadas = 0;

        // 1. Órdenes personales
        $ordenesPersonales = Orden::has('ordenRepuestos')->with('ordenRepuestos.repuesto')->get();
        foreach ($ordenesPersonales as $ord) {
            $totalPvp = 0.0;
            foreach ($ord->ordenRepuestos as $orp) {
                $cant = (int) ($orp->cantidad ?? 1);
                $repPvp = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                $totalPvp += round($cant * $repPvp, 2);
            }
            $ord->valor_repuestos = round($totalPvp, 2);
            $ord->save();
            $ordenesRecalculadas++;
        }

        // 2. Órdenes de empresa
        $ordenesEmpresas = OrdenEmpresa::has('ordenRepuestos')->with('ordenRepuestos.repuesto')->get();
        foreach ($ordenesEmpresas as $ordE) {
            $totalPvp = 0.0;
            foreach ($ordE->ordenRepuestos as $orp) {
                $cant = (int) ($orp->cantidad ?? 1);
                $repPvp = (float) (($orp->repuesto && (float)$orp->repuesto->pvp > 0) ? $orp->repuesto->pvp : ($orp->repuesto->costo ?? 0.0));
                $totalPvp += round($cant * $repPvp, 2);
            }
            $ordE->valor_repuestos = round($totalPvp, 2);
            $ordE->save();
            $ordenesRecalculadas++;
        }

        $this->info("Se recalcularon {$ordenesRecalculadas} órdenes con el PVP de sus repuestos.");
        return 0;
    }
}
