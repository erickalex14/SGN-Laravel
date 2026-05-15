<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class PrecioOrden extends Model
{
    protected $table = 'preciosorden';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'creado_en';
    public const UPDATED_AT = null;

    protected $fillable = [
        'orden_id',
        'precio_estandar_id',
        'servicio',
        'precio',
        'descripcion'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function precioEstandar()
    {
        return $this->belongsTo(PrecioEstandar::class, 'precio_estandar_id', 'id');
    }
}
