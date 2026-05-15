<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Identity\Usuario;

class SolicitudNc extends Model
{
    //SOLICITUD DE NOTAS DE CREDITO

    protected $table = 'solicitudesnc';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'creado_en'; // Existe creado_en y created_at. Mapeamos uno formal.
    public const UPDATED_AT = null;

    protected $fillable = [
        'nro_solicitud',
        'orden_id',
        'fecha_solicitud',
        'asunto',
        'detalles',
        'nombre_admin',
        'motivo_rechazo',
        'tecnico_nombre',
        'tecnico_id',
        'estado'
    ];

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id', 'id');
    }
}
