<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'rol'
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id', 'id');
    }
}
