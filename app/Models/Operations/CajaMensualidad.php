<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class CajaMensualidad extends Model
{
    protected $table = 'cajas_mensualidades';
    protected $fillable = [
        'caja_id',
        'mes',
        'anio',
        'saldo_inicial',
        'monto_ingreso',
        'saldo_cierre',
        'cerrado'
    ];

    protected $casts = [
        'cerrado' => 'boolean',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }
}
