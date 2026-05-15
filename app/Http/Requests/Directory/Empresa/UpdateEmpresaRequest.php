<?php

namespace App\Http\Requests\Directory\Empresa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID de la URL (ej: /empresas/{id})
        $empresaId = $this->route('empresa');

        return [
            'nombre' => 'required|string|max:200',
            'ruc' => 'required|string|max:20|unique:empresas,ruc,' . $empresaId,
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'direccion_empresa' => 'nullable|string|max:1000',
        ];
    }
}
