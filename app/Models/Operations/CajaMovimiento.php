<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Identity\Usuario;

class CajaMovimiento extends Model
{
    protected $table = 'cajas_movimientos';
    protected $fillable = [
        'caja_id',
        'tipo',
        'categoria',
        'monto',
        'descripcion',
        'usuario_id',
        'tecnico_id',
        'fecha',
        'justificante_1',
        'justificante_2'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id');
    }
}
