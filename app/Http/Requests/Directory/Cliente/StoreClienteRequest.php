<?php

namespace App\Http\Requests\Directory\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'identificacion' => 'required|string|max:15|unique:clientes,identificacion',
            'numero_contacto' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'direccion_clientes' => 'nullable|string|max:1000',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'identificacion.unique' => 'La identificacion ingresada ya se encuentra registrada en nuestro sistema.',
            'correo.email' => 'El formato del correo electronico no es valido.',
        ];
    }
}
