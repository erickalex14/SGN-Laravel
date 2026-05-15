<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Operations\Orden;

class ProductoInventario extends Model
{
    protected $table = 'productosinventario';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'marca_id',
        'tipo_dispositivo_id',
        'tipo_dispositivo_codigo'
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id', 'id');
    }

    public function tipoDispositivo()
    {
        return $this->belongsTo(TipoDispositivo::class, 'tipo_dispositivo_id', 'id');
    }

    public function ordenesAsociadas()
    {
        return $this->hasMany(Orden::class, 'repuesto_inventario_id', 'id');
    }
}
