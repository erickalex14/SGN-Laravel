<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Directory\Sucursal;
use App\Models\Directory\SucursalCliente;

class Preorden extends Model
{
    protected $table = 'preordenes';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'orden_id',
        'fecha_registro',
        'nro_preorden',
        'sucursal_id',
        'nombres',
        'apellidos',
        'identificacion',
        'telefono',
        'correo',
        'nro_factura',
        'codigo_producto',
        'desc_producto',
        'marca_producto',
        'tipo_producto',
        'detalle_equipo',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
        'estado',
        'nro_sucursal_cliente',
        'fecha_facturacion',
        'ciudad_procedencia'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function sucursal()
    {
        // RELATION_REQUIRES_CONFIRMATION: No hay FK estricta a sucursales pero el nombre asume relación.
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function sucursalCliente()
    {
        return $this->belongsTo(SucursalCliente::class, 'nro_sucursal_cliente', 'id');
    }
}
