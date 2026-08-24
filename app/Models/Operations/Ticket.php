<?php

namespace App\Models\Operations;

use App\Models\Directory\Sucursal;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'codigo_ticket',
        'tipo_ticket',
        'categoria',
        'prioridad',
        'estado',
        'solicitante_id',
        'empresa_origen',
        'sucursal_cliente_id',
        'tienda_nombre',
        'contacto_telefono',
        'sucursal_atencion_id',
        'asignado_a_id',
        'titulo',
        'descripcion',
        'fecha_apertura',
        'fecha_asignacion',
        'fecha_primera_respuesta',
        'fecha_resolucion',
        'fecha_cierre',
        'solucion',
        'calificacion',
        'comentario_calificacion',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_asignacion' => 'datetime',
        'fecha_primera_respuesta' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'fecha_cierre' => 'datetime',
        'calificacion' => 'integer',
    ];

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id');
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_a_id');
    }

    public function sucursalAtencion(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_atencion_id');
    }

    public function sucursalCliente(): BelongsTo
    {
        return $this->belongsTo(SucursalCliente::class, 'sucursal_cliente_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(TicketMensaje::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(TicketAdjunto::class, 'ticket_id')->orderBy('created_at', 'desc');
    }

    public function getSolucionTextoAttribute(): ?string
    {
        if (!empty($this->solucion)) {
            return $this->solucion;
        }
        $msgResuelto = $this->mensajes
            ->filter(fn($m) => $m->cambio_estado === 'resuelto' || str_contains(strtolower($m->mensaje), 'solución registrada:'))
            ->last();
        if ($msgResuelto) {
            return preg_replace('/^Estado cambiado.*?Solución registrada:\s*/is', '', $msgResuelto->mensaje);
        }
        return null;
    }
}
