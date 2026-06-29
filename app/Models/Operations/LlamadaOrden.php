<?php

namespace App\Models\Operations;

use App\Models\Identity\Usuario;
use Illuminate\Database\Eloquent\Model;

class LlamadaOrden extends Model
{
    protected $table = 'ordenes_llamadas';
    protected $fillable = ['orden_id', 'orden_empresa_id', 'usuario_id', 'fecha_hora', 'observacion'];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function ordenEmpresa()
    {
        return $this->belongsTo(OrdenEmpresa::class, 'orden_empresa_id', 'id');
    }
}
