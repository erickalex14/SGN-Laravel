<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Directory\Empresa;
use App\Models\Directory\Sucursal;
use App\Models\Identity\Usuario;

class OrdenEmpresa extends Model
{
    protected $table = 'ordenesempresas';
    protected $primaryKey = 'id';

    // Legacy usa fecha_ingreso como creation timestamp, lo desactivamos para inserción manual limpia
    public $timestamps = false;

    protected $fillable = [
        'nro_orden',
        'empresa_id',
        'subtipo',
        'equipo_id',
        'tipo_servicio',
        'nro_ticket',
        'descripcion',
        'tecnico_id',
        'sucursal_id',
        'ingresado_por',
        'fecha_prometido',
        'estado',
        'fecha_ingreso',
        'nro_sucursal_cliente',
        'valor_hora',
        'horas_trabajadas',
        'cas_id',
        'fecha_finalizacion',
        'fecha_entrega'
    ];

    public function tecnicos()
    {
        return $this->belongsToMany(
            \App\Models\Identity\Usuario::class,
            'orden_empresa_tecnicos',
            'orden_empresa_id',
            'tecnico_id'
        );
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id', 'id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function cas()
    {
        return $this->belongsTo(\App\Models\Directory\Cas::class, 'cas_id', 'id');
    }

    public function ingresadoPor()
    {
        return $this->belongsTo(Usuario::class, 'ingresado_por', 'id');
    }
}
