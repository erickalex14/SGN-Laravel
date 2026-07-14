<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    // Usar la tabla bitacoras
    protected $table = 'bitacoras';

    // Desactivar timestamps automáticos de Eloquent ya que solo usamos created_at
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'usuario_nombre',
        'accion',
        'modulo',
        'registro_id',
        'detalles',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que ejecutó la acción.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
