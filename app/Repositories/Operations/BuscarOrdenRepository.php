<?php

namespace App\Repositories\Operations;

use App\DTOs\Operations\BuscarOrdenDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BuscarOrdenRepository
{
    public function buscar(BuscarOrdenDTO $dto): Collection
    {
        $query = DB::table('vista_ordenes as vo')
            ->leftJoin('ordenesempresas as oe', function ($join) {
                $join->on('oe.id', '=', 'vo.orden_id')
                    ->where('vo.tipo_orden', '=', 'empresa');
            })
            ->leftJoin('informes as inf', function ($join) {
                $join->on('inf.orden_id', '=', DB::raw("CASE WHEN vo.tipo_orden = 'empresa' THEN -vo.orden_id ELSE vo.orden_id END"));
            })
            ->select([
                'vo.orden_id',
                'vo.nro_orden',
                'vo.tipo_orden',
                'vo.estado_orden',
                'vo.estado_repuesto',
                'vo.estado_garantia',
                DB::raw("CASE WHEN vo.tipo_orden = 'empresa' THEN oe.nro_ticket ELSE vo.nro_factura END as nro_factura"),
                'vo.nro_factura_2',
                'vo.motivo_ingreso',
                'vo.nro_sucursal_cliente',
                'vo.tecnico_id',
                'vo.cliente_id',
                'vo.empresa_id',
                'vo.equipo_id',
                'vo.cliente',
                'vo.nombres',
                'vo.apellidos',
                'vo.identificacion',
                'vo.numero_contacto',
                'vo.correo',
                'vo.tipo',
                'vo.marca',
                'vo.modelo',
                'vo.serie',
                'vo.falla',
                'vo.observacion',
                'vo.fecha_facturacion',
                DB::raw('vo.fecha_de_ingreso_fmt as fecha_de_ingreso'),
                DB::raw('vo.fecha_entrega_fmt as fecha_entrega'),
                'vo.tecnico',
                'vo.sucursal',
                'inf.id as informe_id',
                'inf.antecedentes',
                'inf.proceso',
                'inf.conclusion',
                'inf.recomendaciones',
                'inf.estado_equipo',
                DB::raw("DATE_FORMAT(inf.fecha_informe, '%d/%m/%Y') as fecha_informe"),
            ]);

        $q = trim($dto->q);
        $qLike = '%' . $q . '%';

        switch ($dto->tipo) {
            case 'nro_orden':
                $query->where(function ($inner) use ($q, $qLike) {
                    $inner->where('vo.nro_orden', 'like', $qLike);
                    if (is_numeric($q)) {
                        $inner->orWhereRaw("SUBSTRING_INDEX(vo.nro_orden, '-', -1) LIKE ?", [$qLike]);
                    }
                });
                break;
            case 'cedula':
                $query->where('vo.identificacion', 'like', $qLike);
                break;
            case 'nombre':
                $query->where(function ($inner) use ($qLike) {
                    $inner->where('vo.nombres', 'like', $qLike)
                        ->orWhere('vo.apellidos', 'like', $qLike)
                        ->orWhere('vo.cliente', 'like', $qLike);
                });
                break;
            case 'serie':
                $query->where('vo.serie', 'like', $qLike);
                break;
            case 'factura':
                $query->where(function ($inner) use ($qLike) {
                    $inner->where('vo.nro_factura', 'like', $qLike)
                        ->orWhere('vo.nro_factura_2', 'like', $qLike)
                        ->orWhere('oe.nro_ticket', 'like', $qLike);
                });
                break;
        }

        if (!$dto->es_superadmin && $dto->sucursal_id > 0) {
            $query->where('vo.sucursal_id', '=', $dto->sucursal_id);
        }

        return $query
            ->orderByDesc('vo.fecha_de_ingreso')
            ->limit(50)
            ->get();
    }
}
