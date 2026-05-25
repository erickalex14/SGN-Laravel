<?php

namespace App\Repositories\Operations;

use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Directory\Sucursal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdenRepository
{
    public function generarNumeroOrden(int $sucursalId): string
    {
        $sucursal = Sucursal::find($sucursalId);
        $secuencial = $sucursal ? strtoupper($sucursal->secuencial) : 'NOV';
        $prefijo = $secuencial . '-';

        // Obtener el maximo consecutivo en ordenes y ordenesempresas
        $maxOrden = Orden::where('sucursal_id', $sucursalId)
            ->where('nro_orden', 'like', $prefijo . '%')
            ->max(DB::raw("CAST(SUBSTRING_INDEX(nro_orden, '-', -1) AS UNSIGNED)"));

        $maxEmpresa = OrdenEmpresa::where('sucursal_id', $sucursalId)
            ->where('nro_orden', 'like', $prefijo . '%')
            ->max(DB::raw("CAST(SUBSTRING_INDEX(nro_orden, '-', -1) AS UNSIGNED)"));

        $siguienteNumero = max((int)$maxOrden, (int)$maxEmpresa) + 1;

        return $prefijo . str_pad((string)$siguienteNumero, 6, '0', STR_PAD_LEFT);
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
        return Orden::whereNotIn('estado_orden', [
            'Entregada',
            'Devuelto sin reparar',
            'Nota de Credito',
            'ENTREGADO',
            'DEVUELTO SIN REPARAR'
        ])->count();
    }

    public function contarOrdenesActivasPorTecnico(int $tecnicoId): int
    {
        return Orden::where('tecnico_id', $tecnicoId)
            ->whereNotIn('estado_orden', [
                'Entregada',
                'Devuelto sin reparar',
                'Nota de Credito',
                'ENTREGADO',
                'DEVUELTO SIN REPARAR'
            ])
            ->count();
    }

    public function contarEquiposReparadosMesActual(): int
    {
        return Orden::whereIn('estado_orden', ['Finalizada', 'REPARADO'])
            ->whereMonth('fecha_modificacion', Carbon::now()->month)
            ->whereYear('fecha_modificacion', Carbon::now()->year)
            ->count();
    }

    public function filtrarParaReporte(ReporteFiltroDTO $filtro): Collection
    {
        $query = Orden::with(['cliente', 'equipo', 'tecnico', 'sucursal']);

        if (!empty($filtro->fecha_inicio)) {
            $query->whereDate('fecha_de_ingreso', '>=', $filtro->fecha_inicio);
        }

        if (!empty($filtro->fecha_fin)) {
            $query->whereDate('fecha_de_ingreso', '<=', $filtro->fecha_fin);
        }

        if (!empty($filtro->estado)) {
            $query->where('estado_orden', $filtro->estado);
        }

        if (!empty($filtro->tecnico_id)) {
            $query->where('tecnico_id', $filtro->tecnico_id);
        }

        if (!empty($filtro->sucursal_id)) {
            $query->where('sucursal_id', $filtro->sucursal_id);
        }

        // Orden cronologico descendente por defecto
        return $query->orderBy('fecha_de_ingreso', 'desc')->get();
    }
}