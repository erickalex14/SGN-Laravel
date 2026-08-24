<?php

namespace App\Models\Operations;

use App\Models\Identity\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMensaje extends Model
{
    protected $table = 'ticket_mensajes';

    protected $fillable = [
        'ticket_id',
        'usuario_id',
        'mensaje',
        'es_nota_interna',
        'cambio_estado',
    ];

    protected $casts = [
        'es_nota_interna' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(TicketAdjunto::class, 'mensaje_id');
    }
}
