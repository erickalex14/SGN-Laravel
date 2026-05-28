<?php

namespace App\Repositories\Operations;

use App\Models\Operations\Informe;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Inventory\Repuesto;
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
            ->with(['cliente', 'equipo', 'tecnico', 'usuarioIngreso'])
            ->when(!$esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && !$esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->orderByDesc('id')
            ->get()
            ->map(function (Orden $orden) use ($idsConInforme) {
                $clienteNombre = trim((string) (($orden->cliente->nombres ?? '') . ' ' . ($orden->cliente->apellidos ?? '')));
                $equipoTipo = (string) ($orden->equipo->tipo ?? '');
                $equipoMarca = (string) ($orden->equipo->marca ?? '');
                $equipoModelo = (string) ($orden->equipo->modelo ?? '');
                $equipoSerie = (string) ($orden->equipo->serie ?? '');
                $equipoNombre = trim($equipoTipo . ' ' . $equipoMarca . ' ' . $equipoModelo);

                return (object) [
                    'id' => $orden->id,
                    'tipo_orden' => 'personal',
                    'nro_orden' => (string) $orden->nro_orden,
                    'estado_orden' => (string) ($orden->estado_orden ?? ''),
                    'nro_factura' => (string) ($orden->nro_factura ?? ''),
                    'nro_factura_2' => (string) ($orden->nro_factura_2 ?? ''),
                    'cliente_nombre' => $clienteNombre,
                    'cliente_identificacion' => (string) ($orden->cliente->identificacion ?? ''),
                    'cliente_telefono' => (string) ($orden->cliente->numero_contacto ?? ''),
                    'cliente_correo' => (string) ($orden->cliente->correo ?? ''),
                    'cliente_direccion' => (string) ($orden->cliente->direccion_clientes ?? ''),
                    'equipo_nombre' => $equipoNombre,
                    'equipo_tipo' => $equipoTipo,
                    'equipo_marca' => $equipoMarca,
                    'equipo_modelo' => $equipoModelo,
                    'equipo_serie' => $equipoSerie,
                    'tecnico' => (string) ($orden->tecnico->nombre_tecnico ?? ''),
                    'ingresado_por_nombre' => (string) ($orden->usuarioIngreso->nombre_tecnico ?? ''),
                    'tiene_informe' => in_array((int) $orden->id, $idsConInforme, true),
                ];
            });

        $empresas = OrdenEmpresa::query()
            ->with(['empresa', 'equipo', 'tecnico', 'ingresadoPor'])
            ->when(!$esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && !$esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->orderByDesc('id')
            ->get()
            ->map(function (OrdenEmpresa $orden) use ($idsConInforme) {
                $equipoTipo = (string) ($orden->equipo->tipo ?? '');
                $equipoMarca = (string) ($orden->equipo->marca ?? '');
                $equipoModelo = (string) ($orden->equipo->modelo ?? '');
                $equipoSerie = (string) ($orden->equipo->serie ?? '');
                $equipoNombre = trim($equipoTipo . ' ' . $equipoMarca . ' ' . $equipoModelo);

                return (object) [
                    'id' => -1 * (int) $orden->id,
                    'tipo_orden' => 'empresa',
                    'nro_orden' => (string) $orden->nro_orden,
                    'estado_orden' => (string) ($orden->estado ?? ''),
                    'cliente_nombre' => trim((string) ($orden->empresa->nombre ?? 'EMPRESA') . ' - ' . (string) ($orden->subtipo ?? '')),
                    'cliente_identificacion' => (string) ($orden->empresa->ruc ?? ''),
                    'cliente_telefono' => (string) ($orden->empresa->telefono ?? ''),
                    'cliente_correo' => (string) ($orden->empresa->correo ?? ''),
                    'cliente_direccion' => (string) ($orden->empresa->direccion_empresa ?? ''),
                    'equipo_nombre' => $equipoNombre !== '' ? $equipoNombre : (string) ($orden->tipo_servicio ?? 'Servicio'),
                    'equipo_tipo' => $equipoTipo,
                    'equipo_marca' => $equipoMarca,
                    'equipo_modelo' => $equipoModelo,
                    'equipo_serie' => $equipoSerie,
                    'tecnico' => (string) ($orden->tecnico->nombre_tecnico ?? ''),
                    'ingresado_por_nombre' => (string) ($orden->ingresadoPor->nombre_tecnico ?? ''),
                    'nro_factura' => (string) ($orden->nro_ticket ?? ''),
                    'nro_factura_2' => '',
                    'tiene_informe' => in_array(-1 * (int) $orden->id, $idsConInforme, true),
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
                $join->on(DB::raw('ABS(i.orden_id)'), '=', 'oe.id')
                    ->where('i.orden_id', '<', 0)
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
        if ($ordenId < 0) {
            $ordenEmpresaId = abs($ordenId);
            $ordenEmpresa = OrdenEmpresa::query()
                ->select(['id', 'tecnico_id', 'sucursal_id', 'estado'])
                ->where('id', $ordenEmpresaId)
                ->when(!$esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
                ->when($esAdmin && !$esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
                ->first();

            if (!$ordenEmpresa) {
                return null;
            }

            return [
                'id' => -1 * (int) $ordenEmpresa->id,
                'tipo_orden' => 'empresa',
                'estado' => (string) ($ordenEmpresa->estado ?? ''),
            ];
        }

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
                ->find(abs((int) $informe->orden_id));
            $informe->setRelation('ordenEmpresa', $ordenEmpresa);
        }

        if ($informe->orden && !Schema::hasTable('orden_repuestos')) {
            $informe->orden->setRelation('ordenRepuestos', collect());
        }

        return $informe;
    }

    public function obtenerRepuestosUsados(int $ordenId): array
    {
        if ($ordenId < 0) {
            return [];
        }

        $repuestos = [];

        if (Schema::hasTable('orden_repuestos')) {
            $filas = DB::table('orden_repuestos as orep')
                ->join('repuestos as r', 'r.id', '=', 'orep.repuesto_id')
                ->where('orep.orden_id', $ordenId)
                ->orderBy('orep.fecha')
                ->get(['r.codigo', 'r.nombre', 'r.nro_parte']);

            foreach ($filas as $fila) {
                $repuestos[] = [
                    'codigo' => (string) ($fila->codigo ?? ''),
                    'nombre' => (string) ($fila->nombre ?? ''),
                    'nro_parte' => (string) ($fila->nro_parte ?? ''),
                ];
            }
        }

        if (!empty($repuestos)) {
            return $repuestos;
        }

        $orden = Orden::query()->select(['id', 'repuesto_inventario_id'])->find($ordenId);
        if (!$orden || empty($orden->repuesto_inventario_id)) {
            return [];
        }

        $repuesto = Repuesto::query()
            ->select(['codigo', 'nombre', 'nro_parte'])
            ->find((int) $orden->repuesto_inventario_id);

        if (!$repuesto) {
            return [];
        }

        return [[
            'codigo' => (string) ($repuesto->codigo ?? ''),
            'nombre' => (string) ($repuesto->nombre ?? ''),
            'nro_parte' => (string) ($repuesto->nro_parte ?? ''),
        ]];
    }
}
