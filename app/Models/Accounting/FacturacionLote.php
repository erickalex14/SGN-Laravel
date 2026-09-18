<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Identity\Usuario;

class FacturacionLote extends Model
{
    protected $table = 'facturacion_lotes';

    protected $fillable = [
        'nro_factura',
        'nro_autorizacion',
        'valor_facturado',
        'fecha_factura',
        'creado_por',
        'observaciones',
    ];

    protected $casts = [
        'valor_facturado' => 'float',
        'fecha_factura' => 'date',
    ];

    public function ordenes(): HasMany
    {
        return $this->hasMany(FacturacionLoteOrden::class, 'facturacion_lote_id', 'id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por', 'id');
    }
}
