<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre'
    ];

    public function productosInventario()
    {
        return $this->hasMany(ProductoInventario::class, 'marca_id', 'id');
    }
}
