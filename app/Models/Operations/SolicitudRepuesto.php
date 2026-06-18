<?php

namespace App\Models\Operations;

use App\Models\Identity\Usuario;
use App\Models\Inventory\ListaCompra;
use App\Models\Inventory\Repuesto;
use Illuminate\Database\Eloquent\Model;

class SolicitudRepuesto extends Model
{
    protected $table = 'solicitudesrepuesto';

    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nro_solicitud',
        'orden_id',
        'tecnico_id',
        'tecnico_nombre',
        'repuesto_nombre',
        'nro_parte',
        'nro_parte_inv_id',
        'repuesto_codigo',
        'repuesto_inv_id',
        'link_compra',
        'cantidad',
        'descripcion',
        'estado',
        'motivo_rechazo',
        'aprobado_por',
        'repuesto_id',
        'lista_compra_id',
        'fecha_solicitud',
        'fecha_gestion',
    ];

    public function orden()
    {
        // RELATION_REQUIRES_CONFIRMATION: Falta FK formal a nivel SQL, pero por nombre se infiere
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function tecnico()
    {
        // RELATION_REQUIRES_CONFIRMATION: Igual que orden, sin FK explícita.
        return $this->belongsTo(Usuario::class, 'tecnico_id', 'id');
    }

    public function repuestoAsignado()
    {
        // RELATION_REQUIRES_CONFIRMATION: repuesto_id apunta previsiblemente a 'repuestos'
        return $this->belongsTo(Repuesto::class, 'repuesto_id', 'id');
    }

    public function repuestoCatalogo()
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_inv_id', 'id');
    }

    public function listaCompra()
    {
        return $this->belongsTo(ListaCompra::class, 'lista_compra_id', 'id');
    }
}
