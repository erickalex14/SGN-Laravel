<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Operations\OrdenEmpresa;

class ProductoInventarioFisicoSt extends Model
{
    protected $table = 'productos_inventario_fisico_st';

    protected $fillable = [
        'orden_empresa_id',
        'sucursal_id',
        'codigo',
        'serie',
        'nombre',
        'estado',
        'detalle_outlet',
    ];

    /**
     * Relación con la orden corporativa (empresa) asociada.
     */
    public function ordenEmpresa()
    {
        return $this->belongsTo(OrdenEmpresa::class, 'orden_empresa_id', 'id');
    }

    /**
     * Relación con la sucursal del inventario físico.
     */
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Directory\Sucursal::class, 'sucursal_id', 'id');
    }
}
