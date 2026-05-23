<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UsuarioSucursal extends Pivot
{
    protected $table = 'usuariosucursales';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'sucursal_id'
    ];
}
