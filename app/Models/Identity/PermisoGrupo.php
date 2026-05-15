<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class PermisoGrupo extends Model
{
    protected $table = 'permisosgrupo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'grupo_id',
        'modulo',
        'accion',
        'permitido'
    ];

    protected $casts = [
        'permitido' => 'boolean',
    ];

    public function grupo()
    {
        return $this->belongsTo(GrupoAcceso::class, 'grupo_id', 'id');
    }
}

