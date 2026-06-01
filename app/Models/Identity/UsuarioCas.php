<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class UsuarioCas extends Model
{
    protected $table = 'usuariocas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'cas_id'
    ];
}
