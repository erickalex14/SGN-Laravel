<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class TipoDispositivo extends Model
{
    protected $table = 'tiposdispositivo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre'
    ];

    public function productosInventario()
    {
        return $this->hasMany(ProductoInventario::class, 'tipo_dispositivo_id', 'id');
    }
}
