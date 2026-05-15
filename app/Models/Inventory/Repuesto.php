<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Repuesto extends Model
{
    protected $table = 'repuestos';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'creado_en';
    public const UPDATED_AT = 'modificado_en';

    protected $fillable = [
        'codigo',
        'nro_parte',
        'nombre',
        'stock',
        'costo',
        'bodega',
        'descripcion',
        'marca_id',
        'tipo_dispositivo_id'
    ];

    // RELATION_REQUIRES_CONFIRMATION: 'marca_id' y 'tipo_dispositivo_id' están definidas como varchar(36)
    // mientras que las tablas correspondientes tienen int auto_increment.
    // Mientras se migra se mantendra eso, hasta que se tenga el sistema en funcionamineto y corregir
}
