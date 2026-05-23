<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Identity\Usuario;
use App\Models\Operations\Orden;

class Sucursal extends Model
{
    protected $table = 'sucursales';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nro_sucursal',
        'ciudad',
        'secuencial',
        'nro_base'
    ];

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'sucursal_id', 'id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'sucursal_id', 'id');
    }

    public function usuariosAsignados()
    {
        // Utilizando la tabla pivote legacy
        return $this->belongsToMany(Usuario::class, 'usuariosucursales', 'sucursal_id', 'usuario_id');
    }
}
