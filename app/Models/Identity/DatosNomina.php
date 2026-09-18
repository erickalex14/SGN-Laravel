<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class DatosNomina extends Model
{
    protected $table = 'datos_nomina';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'nombres_completos',
        'cedula',
        'cargo',
        'telefono',
        'email_personal',
        'contacto_emergencia',
        'foto_url',
        'hoja_vida_url',
        'fecha_ingreso',
        'fecha_salida',
        'estado_afiliacion',
        'sueldo_base',
        'bonificaciones',
        'sanciones',
        'total_a_recibir',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_salida' => 'date',
        'sueldo_base' => 'decimal:2',
        'bonificaciones' => 'decimal:2',
        'sanciones' => 'decimal:2',
        'total_a_recibir' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function solicitudesVacaciones()
    {
        return $this->hasMany(SolicitudVacacion::class, 'datos_nomina_id', 'id');
    }

    /**
     * Calcula los años cumplidos de antigüedad desde fecha_ingreso.
     */
    public function calcularAniosAntiguedad(): int
    {
        if (!$this->fecha_ingreso) {
            return 0;
        }

        return (int) \Carbon\Carbon::parse($this->fecha_ingreso)->diffInYears(\Carbon\Carbon::now());
    }

    /**
     * Calcula los días de vacaciones anuales según Código de Trabajo (Ecuador):
     * 1 a 5 años = 15 días anuales.
     * Desde el 6º año = 15 + (años - 5) días (máximo 30).
     */
    public function calcularDiasVacacionesAnuales(): int
    {
        $anios = $this->calcularAniosAntiguedad();
        if ($anios < 1) {
            return 0;
        }

        if ($anios >= 6) {
            $diasExtra = $anios - 5;
            return min(30, 15 + $diasExtra);
        }

        return 15;
    }

    /**
     * Suma de días aprobados en solicitudes de vacaciones.
     */
    public function calcularDiasTomados(): int
    {
        return (int) $this->solicitudesVacaciones()
            ->where('estado', 'Aprobado')
            ->sum('dias_aprobados');
    }

    /**
     * Días totales acumulados por los años servidos.
     */
    public function calcularDiasTotalesAcumulados(): int
    {
        $anios = $this->calcularAniosAntiguedad();
        if ($anios < 1) {
            return 0;
        }

        $total = 0;
        for ($i = 1; $i <= $anios; $i++) {
            if ($i >= 6) {
                $total += min(30, 15 + ($i - 5));
            } else {
                $total += 15;
            }
        }
        return $total;
    }

    /**
     * Días de vacaciones disponibles (totales acumulados - tomados).
     */
    public function calcularDiasPendientes(): int
    {
        $acumulados = $this->calcularDiasTotalesAcumulados();
        $tomados = $this->calcularDiasTomados();
        return max(0, $acumulados - $tomados);
    }

    /**
     * Retorna el estado actual de vacaciones.
     */
    public function obtenerEstadoVacaciones(): string
    {
        $hoy = \Carbon\Carbon::today();

        $enVacacionesAhora = $this->solicitudesVacaciones()
            ->where('estado', 'Aprobado')
            ->where(function($q) use ($hoy) {
                $q->where(function($sub) use ($hoy) {
                    $sub->whereNotNull('fecha_inicio_aprobada')
                        ->where('fecha_inicio_aprobada', '<=', $hoy)
                        ->where('fecha_fin_aprobada', '>=', $hoy);
                })->orWhere(function($sub) use ($hoy) {
                    $sub->whereNull('fecha_inicio_aprobada')
                        ->where('fecha_inicio', '<=', $hoy)
                        ->where('fecha_fin', '>=', $hoy);
                });
            })->exists();

        if ($enVacacionesAhora) {
            return 'En Vacaciones';
        }

        $tomados = $this->calcularDiasTomados();
        $pendientes = $this->calcularDiasPendientes();

        if ($pendientes > 0) {
            return 'Pendientes por Tomar';
        }

        if ($tomados > 0) {
            return 'Vacaciones Tomadas';
        }

        if ($this->calcularAniosAntiguedad() < 1) {
            return 'Sin Derecho Aún (< 1 año)';
        }

        return 'Al Día';
    }

    public function recargarTotalARecibir(): float
    {
        $total = max(0, (float)$this->sueldo_base + (float)$this->bonificaciones - (float)$this->sanciones);
        $this->total_a_recibir = $total;
        return $total;
    }
}
