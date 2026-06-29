<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Repuesto;

class OrdenRepuesto extends Model
{
    protected $table = 'orden_repuestos';
    protected $primaryKey = 'id';

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $fillable = [
        'orden_id',
        'orden_empresa_id',
        'repuesto_id',
        'cantidad',
        'usuario_id',
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function ordenEmpresa()
    {
        return $this->belongsTo(OrdenEmpresa::class, 'orden_empresa_id', 'id');
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Identity\Usuario::class, 'usuario_id', 'id');
    }
}
