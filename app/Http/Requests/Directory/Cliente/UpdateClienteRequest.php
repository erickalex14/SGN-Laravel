<?php

namespace App\Http\Requests\Directory\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente');

        return [
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'identificacion' => 'required|string|max:15|unique:clientes,identificacion,' . $clienteId,
            'numero_contacto' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'direccion_clientes' => 'nullable|string|max:1000',
        ];
    }
}
