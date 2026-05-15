<?php

namespace App\Models\Identity;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Directory\Sucursal;
use App\Models\Operations\Orden;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'clave', // Legacy naming, se debe mantener
        'nombre_tecnico',
        'telefono',
        'correo_tec',
        'acceso_nc',
        'rol_id',
        'grupo_id',
        'sucursal_id',
        'activo'
    ];

    protected $hidden = [
        'clave',
    ];

    protected $casts = [
        'acceso_nc' => 'boolean',
        'activo' => 'boolean',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }

    public function grupo()
    {
        return $this->belongsTo(GrupoAcceso::class, 'grupo_id', 'id');
    }

    public function sucursalPrincipal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function sucursalesAsignadas()
    {
        return $this->belongsToMany(Sucursal::class, 'usuariosucursales', 'usuario_id', 'sucursal_id');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoUsuario::class, 'usuario_id', 'id');
    }

    public function ordenesTecnico()
    {
        return $this->hasMany(Orden::class, 'tecnico_id', 'id');
    }
}
