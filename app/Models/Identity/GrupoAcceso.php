<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class GrupoAcceso extends Model
{
    protected $table = 'gruposacceso';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_superadmin'
    ];

    protected $casts = [
        'es_superadmin' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'grupo_id', 'id');
    }

    public function permisos()
    {
        return $this->hasMany(PermisoGrupo::class, 'grupo_id', 'id');
    }
}
