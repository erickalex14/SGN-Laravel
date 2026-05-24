<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Validacion de Cliente
            'cli_identificacion' => ['required', 'string', 'max:20'],
            'cli_nombres'        => ['required', 'string', 'max:100'],
            'cli_apellidos'      => ['required', 'string', 'max:100'],
            'cli_telefono'       => ['required', 'string', 'max:20'],
            'cli_correo'         => ['nullable', 'email', 'max:100'],
            'cli_direccion'      => ['nullable', 'string', 'max:200'],

            // Validacion de Equipo
            'eq_tipo'            => ['required', 'string', 'max:50'],
            'eq_marca'           => ['required', 'string', 'max:50'],
            'eq_modelo'          => ['required', 'string', 'max:100'],
            'eq_serie'           => ['required', 'string', 'max:100'],
            'eq_contrasena'      => ['nullable', 'string', 'max:100'],
            'eq_falla'           => ['required', 'string'],
            'eq_observacion'     => ['nullable', 'string'],
            'eq_tipo_servicio'   => ['nullable', 'integer'],
            
            // Validacion de Orden
            'ord_tecnico_id'     => ['required', 'integer', 'exists:usuarios,id'],
            'ord_motivo'         => ['nullable', 'string', 'max:255']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación: ' . $validator->errors()->first()
        ]));
    }
}