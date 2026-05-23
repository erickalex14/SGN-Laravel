<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class InformeFoto extends Model
{
    protected $table = 'informefotos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'informe_id',
        'foto_data',
        'caption',
        'nombre_archivo',
        'tipo_mime',
        'orden_foto'
    ];

    public function informe()
    {
        return $this->belongsTo(Informe::class, 'informe_id', 'id');
    }
}
