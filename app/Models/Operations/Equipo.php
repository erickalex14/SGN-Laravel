<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $table = 'equipos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'tipo_servicio_id',
        'tipo_servicio_texto',
        'marca',
        'modelo',
        'serie',
        'contrasena_equipo',
        'falla',
        'observacion',
        'fecha_facturacion',
        'producto_inventario_codigo'
    ];

    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class, 'tipo_servicio_id', 'id');
    }

    public function series()
    {
        return $this->hasMany(EquipoSerie::class, 'equipo_id', 'id');
    }

    public function credenciales()
    {
        return $this->hasMany(CredencialEquipo::class, 'equipo_id', 'id');
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'equipo_id', 'id');
    }
}
