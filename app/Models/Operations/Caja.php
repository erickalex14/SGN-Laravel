<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Directory\Sucursal;

class Caja extends Model
{
    protected $table = 'cajas';
    protected $fillable = ['sucursal_id', 'tipo', 'balance'];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function mensualidades()
    {
        return $this->hasMany(CajaMensualidad::class, 'caja_id');
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class, 'caja_id');
    }
}
