<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class CredencialEquipo extends Model
{
    protected $table = 'credencialesequipo';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'equipo_id',
        'usuario',
        'contrasena',
        'es_patron'
    ];

    protected $casts = [
        'es_patron' => 'boolean',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id', 'id');
    }
}
