<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Operations\Orden;

class Cas extends Model
{
    // Mapeo estricto de la tabla legacy
    protected $table = 'cas';
    protected $primaryKey = 'id';

    // Mapeo de timestamps en español
    public const CREATED_AT = 'creado_en';
    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'marca',
        'telefono',
        'correo',
        'direccion',
        'ciudad',
        'contacto',
        'notas',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones
    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'cas_id', 'id');
    }
}
