<?php

namespace App\Models\Operations;

use App\Models\Identity\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketLlamada extends Model
{
    protected $table = 'ticket_llamadas';

    protected $fillable = [
        'ticket_id',
        'iniciador_id',
        'receptor_id',
        'estado',
        'signal_offer',
        'signal_answer',
        'signal_ice_iniciador',
        'signal_ice_receptor',
        'duracion_segundos',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'duracion_segundos' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function iniciador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'iniciador_id');
    }

    public function receptor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'receptor_id');
    }
}
