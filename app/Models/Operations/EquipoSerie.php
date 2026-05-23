<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class EquipoSerie extends Model
{
    protected $table = 'equiposseries';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'equipo_id',
        'serie',
        'orden'
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id', 'id');
    }
}
