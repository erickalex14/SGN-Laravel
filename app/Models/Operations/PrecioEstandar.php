<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class PrecioEstandar extends Model
{
    protected $table = 'preciosestandar';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'creado_en';
    public const UPDATED_AT = null;

    protected $fillable = [
        'servicio',
        'precio',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'valor_estandar_id', 'id');
    }
}
