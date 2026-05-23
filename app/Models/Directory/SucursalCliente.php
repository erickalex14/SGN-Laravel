<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Operations\Preorden;

class SucursalCliente extends Model
{
    protected $table = 'sucursalescliente';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'codigo',
        'numero',
        'nombre',
        'provincia',
        'novitec_sucursal',
        'activa'
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function preordenes()
    {
        return $this->hasMany(Preorden::class, 'nro_sucursal_cliente', 'id');
    }
}
