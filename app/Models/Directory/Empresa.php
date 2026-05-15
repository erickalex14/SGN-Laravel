<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Model;
use App\Models\Operations\OrdenEmpresa;

class Empresa extends Model
{
    protected $table = 'empresas';
    protected $primaryKey = 'id';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null; // No existe columna de actualizacion

    protected $fillable = [
        'nombre',
        'ruc',
        'telefono',
        'correo',
        'direccion_empresa'
    ];

    public function ordenesEmpresas()
    {
        return $this->hasMany(OrdenEmpresa::class, 'empresa_id', 'id');
    }
}
