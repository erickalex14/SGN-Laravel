<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;

class FacturacionLoteOrden extends Model
{
    protected $table = 'facturacion_lote_ordenes';

    protected $fillable = [
        'facturacion_lote_id',
        'tipo_orden',
        'orden_id',
        'nro_orden',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(FacturacionLote::class, 'facturacion_lote_id', 'id');
    }

    public function ordenPersonal(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function ordenEmpresa(): BelongsTo
    {
        return $this->belongsTo(OrdenEmpresa::class, 'orden_id', 'id');
    }
}
