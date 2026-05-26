<?php

namespace App\Repositories\Operations;

use App\Models\Operations\Informe;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InformeRepository
{
    public function obtenerOrdenesSinInforme(
        int $tecnicoId,
        bool $esAdmin,
        bool $esMaster,
        int $sucursalSesion
    ): Collection {
        $idsConInforme = Informe::query()
            ->select('orden_id')
            ->pluck('orden_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $personales = Orden::query()
            ->with(['cliente', 'equipo'])
            ->when(!$esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && !$esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->orderByDesc('id')
            ->get()
            ->map(function (Orden $orden) use ($idsConInforme) {
                $clienteNombre = trim((string) (($orden->cliente->nombres ?? '') . ' ' . ($orden->cliente->apellidos ?? '')));
                $equipoNombre = trim((string) (($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')));

                return (object) [
                    'id' => $orden->id,
                    'tipo_orden' => 'personal',
                    'nro_orden' => (string) $orden->nro_orden,
                    'estado_orden' => (string) ($orden->estado_orden ?? ''),
                    'cliente_nombre' => $clienteNombre,
                    'equipo_nombre' => $equipoNombre,
                    'tiene_informe' => in_array((int) $orden->id, $idsConInforme, true),
                ];
            });

        $empresas = OrdenEmpresa::query()
            ->with(['empresa', 'equipo'])
            ->where('subtipo', 'Autoconsumo')
            ->when(!$esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && !$esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->orderByDesc('id')
            ->get()
            ->map(function (OrdenEmpresa $orden) use ($idsConInforme) {
                $equipoNombre = trim((string) (($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? '')));

                return (object) [
                    'id' => $orden->id,
                    'tipo_orden' => 'empresa',
                    'nro_orden' => (string) $orden->nro_orden,
                    'estado_orden' => (string) ($orden->estado ?? ''),
                    'cliente_nombre' => (string) ($orden->empresa->nombre ?? 'EMPRESA'),
                    'equipo_nombre' => $equipoNombre,
                    'tiene_informe' => in_array((int) $orden->id, $idsConInforme, true),
                ];
            });

        return $personales
            ->concat($empresas)
            ->sortByDesc('nro_orden')
            ->values();
    }

    public function obtenerInformesPorTecnico(
        int $tecnicoId,
        bool $esAdmin,
        bool $esMaster,
        int $sucursalSesion
    ): Collection {
        $query = DB::table('informes as i')
            ->leftJoin('ordenes as o', 'i.orden_id', '=', 'o.id')
            ->leftJoin('clientes as c', 'o.cliente_id', '=', 'c.id')
            ->leftJoin('ordenesempresas as oe', function ($join) {
                $join->on('i.orden_id', '=', 'oe.id')
                    ->whereNull('o.id');
            })
            ->leftJoin('empresas as emp', 'oe.empresa_id', '=', 'emp.id')
            ->leftJoin('equipos as eq', function ($join) {
                $join->on('eq.id', '=', DB::raw('COALESCE(o.equipo_id, oe.equipo_id)'));
            })
            ->selectRaw("
                i.id,
                i.orden_id,
                i.fecha_informe,
                i.estado_equipo,
                COALESCE(o.nro_orden, oe.nro_orden) as nro_orden,
                COALESCE(CONCAT(c.nombres, ' ', c.apellidos), emp.nombre) as cliente_nombre,
                TRIM(CONCAT(COALESCE(eq.marca, ''), ' ', COALESCE(eq.modelo, ''))) as equipo_nombre
            ")
            ->orderByDesc('i.fecha_creacion');

        if (!$esAdmin) {
            $query->where('i.tecnico_id', $tecnicoId);
        } elseif (!$esMaster && $sucursalSesion > 0) {
            $query->whereRaw('COALESCE(o.sucursal_id, oe.sucursal_id) = ?', [$sucursalSesion]);
        }

        return $query->get();
    }

    public function buscarOrdenValidaParaInforme(
        int $ordenId,
        int $tecnicoId,
        bool $esAdmin,
        bool $esMaster,
        int $sucursalSesion
    ): ?array {
        $ordenPersonal = Orden::query()
            ->select(['id', 'tecnico_id', 'sucursal_id', 'estado_orden'])
            ->where('id', $ordenId)
            ->when(!$esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && !$esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->first();

        if ($ordenPersonal) {
            return [
                'id' => (int) $ordenPersonal->id,
                'tipo_orden' => 'personal',
                'estado' => (string) ($ordenPersonal->estado_orden ?? ''),
            ];
        }

        $ordenEmpresa = OrdenEmpresa::query()
            ->select(['id', 'tecnico_id', 'sucursal_id', 'estado'])
            ->where('id', $ordenId)
            ->where('subtipo', 'Autoconsumo')
            ->when(!$esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && !$esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->first();

        if ($ordenEmpresa) {
            return [
                'id' => (int) $ordenEmpresa->id,
                'tipo_orden' => 'empresa',
                'estado' => (string) ($ordenEmpresa->estado ?? ''),
            ];
        }

        return null;
    }

    public function buscarPorOrdenId(int $ordenId): ?Informe
    {
        return Informe::with('fotos')->where('orden_id', $ordenId)->first();
    }

    public function buscarPorId(int $id): ?Informe
    {
        $relations = ['orden', 'orden.cliente', 'orden.equipo', 'tecnico', 'fotos'];

        if (Schema::hasTable('orden_repuestos')) {
            $relations[] = 'orden.ordenRepuestos';
            $relations[] = 'orden.ordenRepuestos.repuesto';
        }

        $informe = Informe::with($relations)->find($id);

        if (!$informe) {
            return null;
        }

        if (!$informe->orden) {
            $ordenEmpresa = OrdenEmpresa::query()
                ->with(['empresa', 'equipo'])
                ->find($informe->orden_id);
            $informe->setRelation('ordenEmpresa', $ordenEmpresa);
        }

        if ($informe->orden && !Schema::hasTable('orden_repuestos')) {
            $informe->orden->setRelation('ordenRepuestos', collect());
        }

        return $informe;
    }
}
