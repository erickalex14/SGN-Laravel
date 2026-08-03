<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class SolicitudVacacion extends Model
{
    protected $table = 'solicitudes_vacaciones';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'datos_nomina_id',
        'dias_solicitados',
        'fecha_inicio',
        'fecha_fin',
        'observacion_empleado',
        'estado',
        'dias_aprobados',
        'fecha_inicio_aprobada',
        'fecha_fin_aprobada',
        'observacion_admin',
        'aprobado_por',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_inicio_aprobada' => 'date',
        'fecha_fin_aprobada' => 'date',
        'fecha_aprobacion' => 'datetime',
        'dias_solicitados' => 'integer',
        'dias_aprobados' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function datosNomina()
    {
        return $this->belongsTo(DatosNomina::class, 'datos_nomina_id', 'id');
    }

    public function aprobador()
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por', 'id');
    }
}
