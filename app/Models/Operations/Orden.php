<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use App\Models\Directory\Cliente;
use App\Models\Directory\Sucursal;
use App\Models\Directory\Cas;
use App\Models\Identity\Usuario;
use App\Models\Inventory\ProductoInventario;
use App\Models\Operations\SolicitudNc;
use App\Models\Operations\SolicitudRepuesto;

class Orden extends Model
{
    protected $table = 'ordenes';
    protected $primaryKey = 'id';

    // Las columnas de timestamp no siguen convención
    public $timestamps = false;

    protected $appends = ['nc_estado', 'nc_motivo_rechazo'];

    protected $fillable = [
        'nro_orden',
        'nro_factura',
        'nro_factura_2',
        'motivo_ingreso',
        'nro_sucursal_cliente',
        'cliente_id',
        'equipo_id',
        'tecnico_id',
        'sucursal_id',
        'fecha_de_ingreso',
        'estado_orden',
        'estado_repuesto',
        'estado_garantia',
        'garantia_tipo',
        'garantia_cas',
        'cas_id',
        'cas_fecha_envio',
        'cas_fecha_retorno',
        'cas_numero_caso',
        'ingresado_por',
        'fecha_prometido',
        'modificado_por',
        'fecha_modificacion',
        'fecha_entrega',
        'fecha_finalizacion',
        'valor_estandar_id',
        'repuesto_inventario_id',
        'observacion',
        'tipo_servicio_id',
        'tipo_servicio_texto',
        'fecha_facturacion',
        'transferencia_plataforma',
        'transferencia_numero'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id', 'id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function cas()
    {
        return $this->belongsTo(Cas::class, 'cas_id', 'id');
    }

    public function usuarioIngreso()
    {
        return $this->belongsTo(Usuario::class, 'ingresado_por', 'id');
    }

    public function usuarioModificacion()
    {
        return $this->belongsTo(Usuario::class, 'modificado_por', 'id');
    }

    public function precioEstandar()
    {
        return $this->belongsTo(PrecioEstandar::class, 'valor_estandar_id', 'id');
    }

    public function repuestoInventario()
    {
        return $this->belongsTo(ProductoInventario::class, 'repuesto_inventario_id', 'id');
    }

    public function informes()
    {
        return $this->hasMany(Informe::class, 'orden_id', 'id');
    }

    public function solicitudesNc()
    {
        return $this->hasMany(SolicitudNc::class, 'orden_id', 'id');
    }

    public function solicitudesRepuesto()
    {
        return $this->hasMany(SolicitudRepuesto::class, 'orden_id', 'id');
    }

    public function ordenRepuestos()
    {
        return $this->hasMany(OrdenRepuesto::class, 'orden_id', 'id');
    }

    public function preciosOrden()
    {
        return $this->hasMany(PrecioOrden::class, 'orden_id', 'id');
    }

    public function llamadas()
    {
        return $this->hasMany(LlamadaOrden::class, 'orden_id')->latest('fecha_hora');
    }

    public function getNcEstadoAttribute()
    {
        return $this->solicitudesNc->first()->estado ?? null;
    }

    public function getNcMotivoRechazoAttribute()
    {
        return $this->solicitudesNc->first()->motivo_rechazo ?? null;
    }
}
