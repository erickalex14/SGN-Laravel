<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class ActividadDiaria extends Model
{
    protected $table = 'actividades_diarias';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'tipo_accion',
        'descripcion',
        'modulo',
        'referencia_id',
        'referencia_tipo',
        'metadata_json',
        'ip_address',
        'fecha_hora',
        'fecha'
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'fecha_hora' => 'datetime',
        'fecha' => 'date'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function scopeDelDia($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
