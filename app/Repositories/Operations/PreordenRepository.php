<?php

namespace App\Repositories\Operations;

use App\Models\Directory\Cliente;
use App\Models\Operations\Equipo;
use App\Models\Operations\EquipoSerie;
use App\Models\Operations\Orden;
use App\Models\Operations\Preorden;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PreordenRepository
{
    public function adquirirLockSecuenciaOrden(int $sucursalId): bool
    {
        $lockName = 'orden_seq_lock_' . $sucursalId;
        $row = DB::selectOne('SELECT GET_LOCK(?, 10) AS ok', [$lockName]);
        return ((int) ($row->ok ?? 0)) === 1;
    }

    public function liberarLockSecuenciaOrden(int $sucursalId): void
    {
        $lockName = 'orden_seq_lock_' . $sucursalId;
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
    }

    public function obtenerTecnicosDisponibles(bool $esSuperadmin, int $sucursalSesion): Collection
    {
        return DB::table('usuarios as u')
            ->join('roles as r', 'u.rol_id', '=', 'r.id')
            ->select('u.id', 'u.nombre_tecnico')
            ->whereIn('r.rol', ['tecnico', 'tecnico master'])
            ->when(!$esSuperadmin && $sucursalSesion > 0, function ($q) use ($sucursalSesion) {
                $q->where('u.sucursal_id', $sucursalSesion);
            })
            ->orderBy('u.nombre_tecnico')
            ->get();
    }

    public function obtenerPreordenesPendientes(bool $esSuperadmin, int $sucursalSesion): Collection
    {
        return DB::table('preordenes as po')
            ->leftJoin('sucursales as s', 'po.sucursal_id', '=', 's.id')
            ->leftJoin('sucursalescliente as sc', 'po.nro_sucursal_cliente', '=', 'sc.id')
            ->selectRaw(
                'po.id, po.nro_preorden, po.nombres, po.apellidos, po.telefono, po.correo,' .
                'po.nro_factura, po.fecha_facturacion, po.codigo_producto, po.desc_producto,' .
                'po.marca_producto, po.tipo_producto, po.detalle_equipo, po.foto_1, po.foto_2, po.foto_3, po.foto_4,' .
                'po.nro_sucursal_cliente, po.sucursal_id, po.fecha_registro, po.orden_id,' .
                's.secuencial, s.ciudad AS sucursal_ciudad, sc.nombre AS sucursal_cliente_nombre, sc.numero AS sucursal_cliente_numero'
            )
            ->whereNull('po.orden_id')
            ->when(!$esSuperadmin && $sucursalSesion > 0, function ($q) use ($sucursalSesion) {
                $q->where('po.sucursal_id', $sucursalSesion);
            })
            ->orderByDesc('po.fecha_registro')
            ->limit(100)
            ->get();
    }

    public function tecnicoValidoEnSucursal(int $tecnicoId, int $sucursalSesion): bool
    {
        return DB::table('usuarios as u')
            ->join('roles as r', 'u.rol_id', '=', 'r.id')
            ->where('u.id', $tecnicoId)
            ->where('u.sucursal_id', $sucursalSesion)
            ->whereIn('r.rol', ['tecnico', 'tecnico master'])
            ->exists();
    }

    public function obtenerPreordenConBloqueo(int $preordenId): ?Preorden
    {
        return Preorden::where('id', $preordenId)->lockForUpdate()->first();
    }

    public function existeNumeroOrden(string $nroOrden): bool
    {
        return DB::table('ordenes')->where('nro_orden', $nroOrden)->exists()
            || DB::table('ordenesempresas')->where('nro_orden', $nroOrden)->exists();
    }

    public function existeCodigoProductoInventario(string $codigo): bool
    {
        return DB::table('productosinventario')
            ->where('codigo', $codigo)
            ->exists();
    }

    public function buscarClientePorIdentificacion(string $identificacion): ?Cliente
    {
        return Cliente::where('identificacion', $identificacion)->first();
    }

    public function crearCliente(array $data): Cliente
    {
        return Cliente::create($data);
    }

    public function guardarCliente(Cliente $cliente): void
    {
        $cliente->save();
    }

    public function crearEquipo(array $data): Equipo
    {
        return Equipo::create($data);
    }

    public function crearEquipoSerie(int $equipoId, string $serie): EquipoSerie
    {
        return EquipoSerie::create([
            'equipo_id' => $equipoId,
            'serie' => $serie,
            'orden' => 1,
        ]);
    }

    public function crearOrden(array $data): Orden
    {
        return Orden::create($data);
    }

    public function enlazarPreordenConOrden(Preorden $preorden, int $ordenId): void
    {
        $preorden->orden_id = $ordenId;
        $preorden->save();
    }

    public function obtenerPreordenReporte(int $preordenId): ?object
    {
        return DB::table('preordenes as po')
            ->leftJoin('sucursalescliente as sc', 'po.nro_sucursal_cliente', '=', 'sc.id')
            ->leftJoin('ordenes as o', 'po.orden_id', '=', 'o.id')
            ->selectRaw('po.*, sc.nombre as sucursal_cliente_nombre, sc.numero as sucursal_cliente_numero, o.nro_orden as orden_ref')
            ->where('po.id', $preordenId)
            ->first();
    }

    public function obtenerNumeroOrdenPorId(int $ordenId): ?string
    {
        $row = DB::table('ordenes')
            ->select('nro_orden')
            ->where('id', $ordenId)
            ->first();

        return $row ? (string) $row->nro_orden : null;
    }

    public function obtenerCorreoTecnico(int $tecnicoId): ?string
    {
        $row = DB::table('usuarios')
            ->select('correo_tec')
            ->where('id', $tecnicoId)
            ->first();

        $correo = trim((string) ($row->correo_tec ?? ''));
        return $correo !== '' ? $correo : null;
    }

    public function obtenerCorreosAdministradores(int $sucursalId, ?string $excluirCorreo = null): array
    {
        $query = DB::table('usuarios as u')
            ->join('roles as r', 'u.rol_id', '=', 'r.id')
            ->select('u.correo_tec')
            ->whereIn('r.rol', ['administrador', 'admin', 'administrador master'])
            ->where(function ($q) use ($sucursalId) {
                $q->where('u.sucursal_id', $sucursalId)
                    ->orWhere('r.rol', 'administrador master');
            })
            ->whereNotNull('u.correo_tec')
            ->where('u.correo_tec', '!=', '')
            ->limit(10);

        $correos = $query->pluck('correo_tec')
            ->map(fn ($correo) => trim((string) $correo))
            ->filter(fn ($correo) => $correo !== '')
            ->unique()
            ->values()
            ->all();

        if ($excluirCorreo) {
            $correos = array_values(array_filter($correos, fn ($correo) => strcasecmp($correo, $excluirCorreo) !== 0));
        }

        return $correos;
    }
}
