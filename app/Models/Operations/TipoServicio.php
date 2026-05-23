<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class TipoServicio extends Model
{
    protected $table = 'tiposservicio';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'tipo_servicio_id', 'id');
    }
}
