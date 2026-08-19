<?php

namespace App\Models\Identity;

use App\Models\Directory\Cas;
use App\Models\Directory\Sucursal;
use App\Models\Operations\Orden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'usuario',
        'clave', // Legacy naming, se debe mantener
        'clave_hash',
        'nombre_tecnico',
        'telefono',
        'correo_tec',
        'acceso_nc',
        'rol_id',
        'grupo_id',
        'sucursal_id',
        'activo',
    ];

    protected $hidden = [
        'clave',
        'clave_hash',
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

    public function casAsignados()
    {
        return $this->belongsToMany(Cas::class, 'usuariocas', 'usuario_id', 'cas_id');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoUsuario::class, 'usuario_id', 'id');
    }

    public function ordenesTecnico()
    {
        return $this->hasMany(Orden::class, 'tecnico_id', 'id');
    }

    public function datosNomina()
    {
        return $this->hasOne(DatosNomina::class, 'usuario_id', 'id');
    }

    public function validarClave(string $clave): bool
    {
        $claveNormalizada = trim($clave);

        if ($this->clave_hash !== null && $this->clave_hash !== '') {
            return Hash::check($claveNormalizada, $this->clave_hash);
        }

        return (string) $this->clave === $claveNormalizada;
    }

    public function usaClaveLegacy(): bool
    {
        return ($this->clave_hash === null || $this->clave_hash === '') && (string) $this->clave !== '';
    }

    public function establecerClaveSegura(string $clave): void
    {
        $this->clave_hash = Hash::make(trim($clave));
        $this->clave = '';
    }

    public function debeLlenarActividades(): bool
    {
        if ((int) $this->grupo_id === 6 || mb_strtolower($this->grupo?->nombre ?? '') === 'admin solo lectura') {
            return false;
        }

        $nombresExcluidos = [
            'carlos ramos',
            'antonio pulido',
            'evelin vaca'
        ];
        $usuariosExcluidos = [
            '1721443610', // Carlos Ramos
            '0921998878', // Antonio Pulido
            '0957967847'  // Evelin Vaca
        ];

        $nombreNorm = mb_strtolower(trim($this->nombre_tecnico));
        $usuarioNorm = trim($this->usuario);

        if (in_array($nombreNorm, $nombresExcluidos, true) || in_array($usuarioNorm, $usuariosExcluidos, true)) {
            return false;
        }

        return true;
    }
}
