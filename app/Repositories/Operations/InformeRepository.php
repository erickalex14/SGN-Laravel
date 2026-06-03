<?php

namespace App\Repositories\Operations;

use App\Models\Inventory\Repuesto;
use App\Models\Operations\Informe;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
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
            ->when(! $esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && ! $esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->orderByDesc('id')
            ->get()
            ->map(function (Orden $orden) use ($idsConInforme) {
                $clienteNombre = trim((string) (($orden->cliente->nombres ?? '').' '.($orden->cliente->apellidos ?? '')));
                $equipoTipo = (string) ($orden->equipo->tipo ?? '');
                $equipoMarca = (string) ($orden->equipo->marca ?? '');
                $equipoModelo = (string) ($orden->equipo->modelo ?? '');
                $equipoSerie = (string) ($orden->equipo->serie ?? '');
                $equipoNombre = trim($equipoTipo.' '.$equipoMarca.' '.$equipoModelo);

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
            ->whereRaw("subtipo COLLATE utf8mb4_0900_ai_ci IN ('Autoconsumo', 'Servicios', 'Stock')")
            ->when(! $esAdmin, function ($q) use ($tecnicoId) {
                $q->where(function ($query) use ($tecnicoId) {
                    $query->where('tecnico_id', $tecnicoId)
                          ->orWhereHas('tecnicos', function ($inner) use ($tecnicoId) {
                              $inner->where('usuarios.id', $tecnicoId);
                          });
                });
            })
            ->when($esAdmin && ! $esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
            ->orderByDesc('id')
            ->get()
            ->map(function (OrdenEmpresa $orden) use ($idsConInforme) {
                $equipoTipo = (string) ($orden->equipo->tipo ?? '');
                $equipoMarca = (string) ($orden->equipo->marca ?? '');
                $equipoModelo = (string) ($orden->equipo->modelo ?? '');
                $equipoSerie = (string) ($orden->equipo->serie ?? '');
                $equipoNombre = trim($equipoTipo.' '.$equipoMarca.' '.$equipoModelo);

                return (object) [
                    'id' => -1 * (int) $orden->id,
                    'tipo_orden' => 'empresa',
                    'nro_orden' => (string) $orden->nro_orden,
                    'estado_orden' => (string) ($orden->estado ?? ''),
                    'cliente_nombre' => trim((string) ($orden->empresa->nombre ?? 'EMPRESA').' - '.(string) ($orden->subtipo ?? '')),
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

        if (! $esAdmin) {
            $query->where('i.tecnico_id', $tecnicoId);
        } elseif (! $esMaster && $sucursalSesion > 0) {
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
                ->whereIn('subtipo', ['Autoconsumo', 'Servicios', 'Stock'])
                ->when(! $esAdmin, function ($q) use ($tecnicoId) {
                    $q->where(function ($query) use ($tecnicoId) {
                        $query->where('tecnico_id', $tecnicoId)
                              ->orWhereHas('tecnicos', function ($inner) use ($tecnicoId) {
                                  $inner->where('usuarios.id', $tecnicoId);
                              });
                    });
                })
                ->when($esAdmin && ! $esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
                ->first();

            if (! $ordenEmpresa) {
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
            ->when(! $esAdmin, fn ($q) => $q->where('tecnico_id', $tecnicoId))
            ->when($esAdmin && ! $esMaster && $sucursalSesion > 0, fn ($q) => $q->where('sucursal_id', $sucursalSesion))
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

        if (! $informe) {
            return null;
        }

        if (! $informe->orden) {
            $ordenEmpresa = OrdenEmpresa::query()
                ->with(['empresa', 'equipo'])
                ->find(abs((int) $informe->orden_id));
            $informe->setRelation('ordenEmpresa', $ordenEmpresa);
        }

        if ($informe->orden && ! Schema::hasTable('orden_repuestos')) {
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

        if (! empty($repuestos)) {
            return $repuestos;
        }

        $orden = Orden::query()->select(['id', 'repuesto_inventario_id'])->find($ordenId);
        if (! $orden || empty($orden->repuesto_inventario_id)) {
            return [];
        }

        $repuesto = Repuesto::query()
            ->select(['codigo', 'nombre', 'nro_parte'])
            ->find((int) $orden->repuesto_inventario_id);

        if (! $repuesto) {
            return [];
        }

        return [[
            'codigo'    => (string) ($repuesto->codigo   ?? ''),
            'nombre'    => (string) ($repuesto->nombre    ?? ''),
            'nro_parte' => (string) ($repuesto->nro_parte ?? ''),
        ]];
    }

    /**
     * BÃƒÂºsqueda de informes con filtros mÃƒÂºltiples.
     * COLLATE explÃƒÂ­cito para evitar error 1267 en servidores con utf8mb4_0900_ai_ci.
     */
    public function buscarInformes(
        array $filtros,
        int   $tecnicoId,
        bool  $esAdmin,
        bool  $esMaster,
        int   $sucursalSesion
    ): Collection {
        $q     = trim($filtros['q'] ?? '');
        $qLike = '%' . $q . '%';
        $tipo  = $filtros['tipo'] ?? 'nro_orden';

        $query = DB::table('informes as i')
            ->leftJoin('ordenes as o',   'i.orden_id', '=', 'o.id')
            ->leftJoin('clientes as c',  'o.cliente_id', '=', 'c.id')
            ->leftJoin('ordenesempresas as oe', function ($join) {
                $join->on(DB::raw('ABS(i.orden_id)'), '=', 'oe.id')
                     ->where('i.orden_id', '<', 0)
                     ->whereNull('o.id');
            })
            ->leftJoin('empresas as emp', 'oe.empresa_id', '=', 'emp.id')
            ->leftJoin('usuarios as u',   'i.tecnico_id', '=', 'u.id')
            ->leftJoin('equipos as eq', function ($join) {
                $join->on('eq.id', '=', DB::raw('COALESCE(o.equipo_id, oe.equipo_id)'));
            })
            ->selectRaw("
                i.id,
                i.orden_id,
                i.tecnico_id,
                DATE_FORMAT(i.fecha_informe, '%d/%m/%Y')       as fecha_informe,
                DATE_FORMAT(i.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion,
                i.estado_equipo,
                i.antecedentes,
                i.conclusion,
                COALESCE(o.nro_orden, oe.nro_orden)            as nro_orden,
                CASE WHEN i.orden_id < 0 THEN 'empresa' ELSE 'personal' END as tipo_orden,
                COALESCE(
                    NULLIF(CONCAT(TRIM(COALESCE(c.nombres,'')), ' ', TRIM(COALESCE(c.apellidos,''))), ' '),
                    emp.nombre
                )                                               as cliente_nombre,
                COALESCE(c.identificacion, emp.ruc)            as identificacion,
                COALESCE(o.nro_factura, oe.nro_ticket)         as nro_factura,
                TRIM(CONCAT(
                    COALESCE(eq.tipo,''), ' ',
                    COALESCE(eq.marca,''), ' ',
                    COALESCE(eq.modelo,'')
                ))                                              as equipo_nombre,
                COALESCE(eq.serie,'')                          as equipo_serie,
                u.nombre_tecnico                               as tecnico,
                COALESCE(o.sucursal_id, oe.sucursal_id)        as sucursal_id
            ")
            ->orderByDesc('i.fecha_creacion');

        // Ã¢â€â‚¬Ã¢â€â‚¬ Criterio de texto Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
        if ($q !== '') {
            switch ($tipo) {
                case 'nro_orden':
                    $query->whereRaw(
                        "COALESCE(o.nro_orden, oe.nro_orden) COLLATE utf8mb4_0900_ai_ci LIKE ?",
                        [$qLike]
                    );
                    break;

                case 'nombre':
                    $query->where(function ($inner) use ($qLike) {
                        $inner->whereRaw("CONCAT(COALESCE(c.nombres,''), ' ', COALESCE(c.apellidos,'')) COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike])
                              ->orWhereRaw("emp.nombre COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                    });
                    break;

                case 'tecnico':
                    $query->whereRaw("u.nombre_tecnico COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                    break;

                case 'empresa':
                    $query->where('i.orden_id', '<', 0)
                          ->whereRaw("emp.nombre COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                    break;

                case 'cedula':
                    $query->where(function ($inner) use ($qLike) {
                        $inner->whereRaw("c.identificacion COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike])
                              ->orWhereRaw("emp.ruc         COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                    });
                    break;

                case 'serie':
                    $query->whereRaw("eq.serie COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                    break;
            }
        }

        // Ã¢â€â‚¬Ã¢â€â‚¬ Filtros adicionales Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
        $estado     = $filtros['estado']     ?? '';
        $tecFiltro  = (int) ($filtros['tecnico_id']  ?? 0);
        $fechaDesde = $filtros['fecha_desde'] ?? '';
        $fechaHasta = $filtros['fecha_hasta'] ?? '';

        if ($estado !== '') {
            $query->whereRaw("i.estado_equipo COLLATE utf8mb4_0900_ai_ci = ?", [$estado]);
        }
        if ($tecFiltro > 0) {
            $query->where('i.tecnico_id', $tecFiltro);
        }
        if ($fechaDesde !== '') {
            $query->whereRaw("DATE(i.fecha_informe) >= ?", [$fechaDesde]);
        }
        if ($fechaHasta !== '') {
            $query->whereRaw("DATE(i.fecha_informe) <= ?", [$fechaHasta]);
        }

        // Ã¢â€â‚¬Ã¢â€â‚¬ Scope por sesiÃƒÂ³n Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
        if (!$esAdmin) {
            $query->where('i.tecnico_id', $tecnicoId);
        } elseif (!$esMaster && $sucursalSesion > 0) {
            $query->whereRaw('COALESCE(o.sucursal_id, oe.sucursal_id) = ?', [$sucursalSesion]);
        }

        return $query->limit(150)->get();
    }

    /**
     * TÃƒÂ©cnicos activos para el selector de filtros.
     */
    public function obtenerTecnicosActivos(int $sucursalId, bool $esMaster): Collection
    {
        return DB::table('usuarios')
            ->select(['id', 'nombre_tecnico'])
            ->where('activo', 1)
            ->when(!$esMaster && $sucursalId > 0, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->orderBy('nombre_tecnico')
            ->get();
    }

    /**
     * BÃƒÂºsqueda AJAX de ÃƒÂ³rdenes para el formulario de creaciÃƒÂ³n de informe.
     * Soporta tipo: nro_orden | nombre | cedula | factura | empresa | id
     * COLLATE explÃƒÂ­cito para evitar error 1267.
     */
    public function buscarOrdenesParaInforme(
        string $q,
        string $tipo,
        int    $tecnicoId,
        bool   $esAdmin,
        bool   $esMaster,
        int    $sucursalSesion,
        bool   $esSuperadmin = false
    ): array {
        $qLike = '%' . $q . '%';

        // IDs que ya tienen informe (para marcarlos)
        $idsConInforme = DB::table('informes')->pluck('orden_id')->map(fn ($v) => (int) $v)->toArray();

        // Ã¢â€â‚¬Ã¢â€â‚¬ BÃƒÂºsqueda directa por ID (desde Mis Informes Ã¢â€ â€™ Editar) Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
        if ($tipo === 'id') {
            $ordenId = (int) $q;
            if ($ordenId === 0) return [];

            if ($ordenId > 0) {
                $o = DB::table('ordenes as o')
                    ->leftJoin('clientes as c', 'o.cliente_id', '=', 'c.id')
                    ->leftJoin('equipos as eq', 'o.equipo_id', '=', 'eq.id')
                    ->leftJoin('usuarios as u', 'o.tecnico_id', '=', 'u.id')
                    ->where('o.id', $ordenId)
                    ->selectRaw("
                        o.id,
                        'personal' as tipo_orden,
                        o.nro_orden,
                        o.estado_orden as estado_orden,
                        TRIM(CONCAT(COALESCE(c.nombres,''), ' ', COALESCE(c.apellidos,''))) as cliente_nombre,
                        COALESCE(c.identificacion,'') as cliente_identificacion,
                        COALESCE(c.numero_contacto,'') as cliente_telefono,
                        COALESCE(c.correo,'') as cliente_correo,
                        COALESCE(c.direccion_clientes,'') as cliente_direccion,
                        TRIM(CONCAT(COALESCE(eq.tipo,''),' ',COALESCE(eq.marca,''),' ',COALESCE(eq.modelo,''))) as equipo_nombre,
                        COALESCE(eq.tipo,'') as equipo_tipo,
                        COALESCE(eq.marca,'') as equipo_marca,
                        COALESCE(eq.modelo,'') as equipo_modelo,
                        COALESCE(eq.serie,'') as equipo_serie,
                        COALESCE(o.nro_factura,'') as nro_factura,
                        '' as nro_factura_2,
                        COALESCE(u.nombre_tecnico,'') as tecnico,
                        o.tecnico_id
                    ")->first();
                if (!$o) return [];
                $o->tiene_informe = in_array($ordenId, $idsConInforme, true);
                return [$o];
            }

            // Negativo Ã¢â€ â€™ empresa
            $absId = abs($ordenId);
            $oe = DB::table('ordenesempresas as oe')
                ->leftJoin('empresas as emp', 'oe.empresa_id', '=', 'emp.id')
                ->leftJoin('equipos as eq', 'oe.equipo_id', '=', 'eq.id')
                ->leftJoin('usuarios as u', 'oe.tecnico_id', '=', 'u.id')
                ->where('oe.id', $absId)
                ->selectRaw("
                    -CAST(oe.id AS SIGNED) as id,
                    'empresa' as tipo_orden,
                    oe.nro_orden,
                    oe.estado as estado_orden,
                    TRIM(CONCAT(COALESCE(emp.nombre,''), ' - ', COALESCE(oe.subtipo,''))) as cliente_nombre,
                    COALESCE(emp.ruc,'') as cliente_identificacion,
                    COALESCE(emp.telefono,'') as cliente_telefono,
                    COALESCE(emp.correo,'') as cliente_correo,
                    COALESCE(emp.direccion_empresa,'') as cliente_direccion,
                    TRIM(CONCAT(COALESCE(eq.tipo,''),' ',COALESCE(eq.marca,''),' ',COALESCE(eq.modelo,''))) as equipo_nombre,
                    COALESCE(eq.tipo,'') as equipo_tipo,
                    COALESCE(eq.marca,'') as equipo_marca,
                    COALESCE(eq.modelo,'') as equipo_modelo,
                    COALESCE(eq.serie,'') as equipo_serie,
                    COALESCE(oe.nro_ticket,'') as nro_factura,
                    '' as nro_factura_2,
                    COALESCE(u.nombre_tecnico,'') as tecnico,
                    oe.tecnico_id
                ")->first();
            if (!$oe) return [];
            $oe->tiene_informe = in_array($ordenId, $idsConInforme, true);
            return [$oe];
        }

        // Ã¢â€â‚¬Ã¢â€â‚¬ Ordenes personales Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
        $qOrdenes = DB::table('ordenes as o')
            ->leftJoin('clientes as c', 'o.cliente_id', '=', 'c.id')
            ->leftJoin('equipos as eq', 'o.equipo_id', '=', 'eq.id')
            ->leftJoin('usuarios as u', 'o.tecnico_id', '=', 'u.id')
            ->selectRaw("
                o.id,
                'personal' as tipo_orden,
                o.nro_orden,
                o.estado_orden as estado_orden,
                TRIM(CONCAT(COALESCE(c.nombres,''), ' ', COALESCE(c.apellidos,''))) as cliente_nombre,
                COALESCE(c.identificacion,'') as cliente_identificacion,
                COALESCE(c.numero_contacto,'') as cliente_telefono,
                COALESCE(c.correo,'') as cliente_correo,
                COALESCE(c.direccion_clientes,'') as cliente_direccion,
                TRIM(CONCAT(COALESCE(eq.tipo,''),' ',COALESCE(eq.marca,''),' ',COALESCE(eq.modelo,''))) as equipo_nombre,
                COALESCE(eq.tipo,'') as equipo_tipo,
                COALESCE(eq.marca,'') as equipo_marca,
                COALESCE(eq.modelo,'') as equipo_modelo,
                COALESCE(eq.serie,'') as equipo_serie,
                COALESCE(o.nro_factura,'') as nro_factura,
                '' as nro_factura_2,
                COALESCE(u.nombre_tecnico,'') as tecnico,
                o.tecnico_id
            ");

        switch ($tipo) {
            case 'nro_orden':
                $qOrdenes->whereRaw("o.nro_orden COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
            case 'nombre':
                $qOrdenes->whereRaw("TRIM(CONCAT(COALESCE(c.nombres,''),' ',COALESCE(c.apellidos,''))) COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
            case 'cedula':
                $qOrdenes->whereRaw("c.identificacion COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
            case 'factura':
                $qOrdenes->whereRaw("o.nro_factura COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
            default:
                $qOrdenes->whereRaw("o.nro_orden COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
        }

        // Filtro de alcance:
        // - Superadmin: ve todas las órdenes
        // - Cualquier otro (admin o técnico): solo sus órdenes asignadas al crear informe
        if (!$esSuperadmin) {
            $qOrdenes->where('o.tecnico_id', $tecnicoId);
        } elseif (!$esMaster && $sucursalSesion > 0) {
            $qOrdenes->where('o.sucursal_id', $sucursalSesion);
        }

        $personales = $qOrdenes->orderByDesc('o.id')->limit(12)->get();

        // Ã¢â€â‚¬Ã¢â€â‚¬ Ordenes empresa Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
        $qEmpresas = DB::table('ordenesempresas as oe')
            ->leftJoin('empresas as emp', 'oe.empresa_id', '=', 'emp.id')
            ->leftJoin('equipos as eq', 'oe.equipo_id', '=', 'eq.id')
            ->leftJoin('usuarios as u', 'oe.tecnico_id', '=', 'u.id')
            ->selectRaw("
                -CAST(oe.id AS SIGNED) as id,
                'empresa' as tipo_orden,
                oe.nro_orden,
                oe.estado as estado_orden,
                TRIM(CONCAT(COALESCE(emp.nombre,''), ' - ', COALESCE(oe.subtipo,''))) as cliente_nombre,
                COALESCE(emp.ruc,'') as cliente_identificacion,
                COALESCE(emp.telefono,'') as cliente_telefono,
                COALESCE(emp.correo,'') as cliente_correo,
                COALESCE(emp.direccion_empresa,'') as cliente_direccion,
                TRIM(CONCAT(COALESCE(eq.tipo,''),' ',COALESCE(eq.marca,''),' ',COALESCE(eq.modelo,''))) as equipo_nombre,
                COALESCE(eq.tipo,'') as equipo_tipo,
                COALESCE(eq.marca,'') as equipo_marca,
                COALESCE(eq.modelo,'') as equipo_modelo,
                COALESCE(eq.serie,'') as equipo_serie,
                COALESCE(oe.nro_ticket,'') as nro_factura,
                '' as nro_factura_2,
                COALESCE(u.nombre_tecnico,'') as tecnico,
                oe.tecnico_id
            ");

        switch ($tipo) {
            case 'nro_orden':
                $qEmpresas->whereRaw("oe.nro_orden COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
            case 'nombre':
            case 'empresa':
                $qEmpresas->whereRaw("emp.nombre COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
            case 'cedula':
                $qEmpresas->whereRaw("emp.ruc COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                break;
            case 'factura':
            case 'nro_ticket':
                // Buscar por ticket de empresa O por nro_orden
                $qEmpresas->where(function ($inner) use ($qLike) {
                    $inner->whereRaw("oe.nro_ticket COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike])
                          ->orWhereRaw("oe.nro_orden COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
                });
                break;
            default:
                $qEmpresas->whereRaw("oe.nro_orden COLLATE utf8mb4_0900_ai_ci LIKE ?", [$qLike]);
        }

        // Filtro de alcance:
        // - Superadmin: ve todas las órdenes
        // - Cualquier otro (admin o técnico): solo sus órdenes asignadas al crear informe
        if (!$esSuperadmin) {
            $qEmpresas->where(function ($query) use ($tecnicoId) {
                $query->where('oe.tecnico_id', $tecnicoId)
                      ->orWhereExists(function ($inner) use ($tecnicoId) {
                          $inner->select(DB::raw(1))
                                ->from('orden_empresa_tecnicos')
                                ->whereColumn('orden_empresa_tecnicos.orden_empresa_id', 'oe.id')
                                ->where('orden_empresa_tecnicos.tecnico_id', $tecnicoId);
                      });
            });
        } elseif (!$esMaster && $sucursalSesion > 0) {
            $qEmpresas->where('oe.sucursal_id', $sucursalSesion);
        }

        $empresas = $qEmpresas->orderByDesc('oe.id')->limit(8)->get();

        return collect($personales)
            ->concat($empresas)
            ->map(function ($o) use ($idsConInforme) {
                $o->tiene_informe = in_array((int) $o->id, $idsConInforme, true);
                return $o;
            })
            ->sortByDesc('nro_orden')
            ->values()
            ->take(20)
            ->toArray();
    }

    /**
     * Lista de informes del tÃƒÂ©cnico para "Mis Informes".
     */
    public function obtenerMisInformes(int $tecnicoId, bool $esMaster, int $sucursalSesion): Collection
    {
        return DB::table('informes as i')
            ->leftJoin('ordenes as o', 'i.orden_id', '=', 'o.id')
            ->leftJoin('clientes as c', 'o.cliente_id', '=', 'c.id')
            ->leftJoin('ordenesempresas as oe', function ($join) {
                $join->on(DB::raw('ABS(i.orden_id)'), '=', 'oe.id')
                     ->where('i.orden_id', '<', 0);
            })
            ->leftJoin('empresas as emp', 'oe.empresa_id', '=', 'emp.id')
            ->leftJoin('equipos as eq', function ($join) {
                $join->on('eq.id', '=', DB::raw('COALESCE(o.equipo_id, oe.equipo_id)'));
            })
            ->selectRaw("
                i.id,
                i.orden_id,
                DATE_FORMAT(i.fecha_informe, '%d/%m/%Y') as fecha_informe,
                DATE_FORMAT(i.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion,
                i.estado_equipo,
                COALESCE(o.nro_orden, oe.nro_orden) as nro_orden,
                CASE WHEN i.orden_id < 0 THEN 'empresa' ELSE 'personal' END as tipo_orden,
                COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(c.nombres,''), ' ', COALESCE(c.apellidos,''))), ''),
                    emp.nombre
                ) as cliente_nombre,
                TRIM(CONCAT(
                    COALESCE(eq.tipo,''), ' ',
                    COALESCE(eq.marca,''), ' ',
                    COALESCE(eq.modelo,'')
                )) as equipo_nombre,
                COALESCE(eq.serie,'') as equipo_serie,
                COALESCE(o.estado_orden, oe.estado) as estado_orden
            ")
            ->where('i.tecnico_id', $tecnicoId)
            ->when(!$esMaster && $sucursalSesion > 0, function ($q) use ($sucursalSesion) {
                $q->whereRaw('COALESCE(o.sucursal_id, oe.sucursal_id) = ?', [$sucursalSesion]);
            })
            ->orderByDesc('i.fecha_creacion')
            ->limit(200)
            ->get();
    }
}

