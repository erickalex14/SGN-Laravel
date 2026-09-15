<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenAdjunto extends Model
{
    protected $table = 'orden_adjuntos';

    protected $fillable = [
        'orden_id',
        'orden_empresa_id',
        'tipo_orden',
        'tipo_adjunto',
        'archivo_path',
        'nombre_original',
        'mime_type',
        'tamanio_bytes',
    ];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function ordenEmpresa(): BelongsTo
    {
        return $this->belongsTo(OrdenEmpresa::class, 'orden_empresa_id', 'id');
    }
}
