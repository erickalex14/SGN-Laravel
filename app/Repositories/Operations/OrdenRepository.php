<?php

namespace App\Repositories\Operations;

use App\Models\Operations\Orden;
use App\Models\Directory\Sucursal;

class OrdenRepository
{
    public function generarNumeroOrden(int $sucursalId): string
    {
        $sucursal = Sucursal::find($sucursalId);
        
        // Obtenemos la ultima orden de esta sucursal para generar el secuencial
        $ultimaOrden = Orden::where('sucursal_id', $sucursalId)->orderBy('id', 'desc')->first();
        
        $siguienteNumero = 1;
        if ($ultimaOrden && preg_match('/-(\d+)$/', $ultimaOrden->nro_orden, $matches)) {
            $siguienteNumero = (int)$matches[1] + 1;
        }

        return sprintf("NOV-%s-%06d", strtoupper($sucursal->secuencial), $siguienteNumero);
    }

    public function obtenerOrdenesPorTecnico(int $tecnicoId): Collection
    {
        return Orden::with(['cliente', 'equipo', 'sucursal'])
            ->where('tecnico_id', $tecnicoId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function buscarPorId(int $id): ?Orden
    {
        return Orden::find($id);
    }

    public function obtenerOrdenCompleta(int $id): ?Orden
    {
        return Orden::with([
            'cliente', 
            'equipo', 
            'tecnico', 
            'sucursal', 
            'precioEstandar', 
            'repuestoInventario'
        ])->find($id);
    }

    public function buscarPorNumeroONombre(string $termino): Collection
    {
        return Orden::with(['cliente', 'equipo'])
            ->where('nro_orden', 'like', "%{$termino}%")
            ->orWhereHas('cliente', function ($query) use ($termino) {
                $query->where('identificacion', 'like', "%{$termino}%")
                      ->orWhere('nombres', 'like', "%{$termino}%")
                      ->orWhere('apellidos', 'like', "%{$termino}%");
            })
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();
    }

    public function contarOrdenesActivasGlobales(): int
    {
        return Orden::whereNotIn('estado_orden', ['ENTREGADO', 'DEVUELTO SIN REPARAR'])->count();
    }

    public function contarOrdenesActivasPorTecnico(int $tecnicoId): int
    {
        return Orden::where('tecnico_id', $tecnicoId)
            ->whereNotIn('estado_orden', ['ENTREGADO', 'DEVUELTO SIN REPARAR'])
            ->count();
    }

    public function contarEquiposReparadosMesActual(): int
    {
        return Orden::where('estado_orden', 'REPARADO')
            ->whereMonth('fecha_modificacion', Carbon::now()->month)
            ->whereYear('fecha_modificacion', Carbon::now()->year)
            ->count();
    }
}