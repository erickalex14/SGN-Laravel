<?php

namespace App\Http\Requests\Directory\Sucursal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSucursalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre_sucursal' => 'required|string|max:100',
            'ciudad' => 'nullable|string|max:200',
            'secuencial' => 'nullable|string|max:20',
            'nro_caso' => 'nullable|string|max:20',
        ];
    }
}
