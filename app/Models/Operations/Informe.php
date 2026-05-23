<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Identity\Usuario;

class Informe extends Model
{
    protected $table = 'informes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'orden_id',
        'tecnico_id',
        'antecedentes',
        'proceso',
        'conclusion',
        'recomendaciones',
        'estado_equipo',
        'fecha_informe',
        'fecha_creacion',
        'presupuesto_json'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id', 'id');
    }

    public function fotos()
    {
        return $this->hasMany(InformeFoto::class, 'informe_id', 'id');
    }
}
