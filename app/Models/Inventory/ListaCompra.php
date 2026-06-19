<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class ListaCompra extends Model
{
    protected $table = 'listascompra';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'nro_lista',
        'creado_por',
        'creado_por_id',
        'fecha_creacion',
        'estado',
        'observacion'
    ];

    public function solicitudes()
    {
        return $this->hasMany(\App\Models\Operations\SolicitudRepuesto::class, 'lista_compra_id', 'id');
    }
}

//En el modelo de la BD no hay FK en el creado por osea no hay una relacion con quien creo esa lista
//En lista para implementar despues
