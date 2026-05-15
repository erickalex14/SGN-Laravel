<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Operations\Orden;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id';
    public $timestamps = false; // Sin timestamps en SQL

    protected $fillable = [
        'nombres',
        'apellidos',
        'identificacion',
        'numero_contacto',
        'correo',
        'direccion_clientes'
    ];

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'cliente_id', 'id');
    }
}
