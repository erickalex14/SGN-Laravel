<?php

namespace App\Models\Operations;

use App\Models\Identity\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAdjunto extends Model
{
    protected $table = 'ticket_adjuntos';

    protected $fillable = [
        'ticket_id',
        'mensaje_id',
        'usuario_id',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamano_bytes',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(TicketMensaje::class, 'mensaje_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
