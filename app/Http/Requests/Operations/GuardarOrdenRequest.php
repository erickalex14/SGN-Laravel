<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
            'eq_tipo'                  => ['required', 'string', 'max:50'],
            'eq_marca'                 => ['required', 'string', 'max:50'],
            'eq_modelo'                => ['required', 'string', 'max:100'],
            'eq_contrasena'            => ['nullable', 'string', 'max:100'],
            'eq_falla'                 => ['required', 'string'],
            'eq_observacion'           => ['nullable', 'string'],
            'eq_tipo_servicio'         => ['nullable', 'integer'],
            'tipo_servicio_texto'      => ['required_if:motivo_ingreso,Servicio Cliente Externo', 'nullable', 'string', 'max:100'],
            'producto_inventario_codigo' => ['nullable', 'string', 'max:50'],

            'series'                   => ['required', 'array', 'min:1'],
            'series.*'                 => ['nullable', 'string', 'max:100'],

            // Validacion de Orden
            'ord_tecnico_id'           => ['required', 'integer', 'exists:usuarios,id'],
            'motivo_ingreso'           => ['required', 'string', 'max:50'],
            'nro_factura'              => ['required_if:motivo_ingreso,Validacion de Garantia', 'nullable', 'string', 'max:50'],
            'nro_factura_2'            => ['nullable', 'string', 'max:50'],
            'fecha_facturacion'        => ['required_if:motivo_ingreso,Validacion de Garantia', 'nullable', 'date'],
            'fecha_prometido'          => ['required', 'date'],
            'nro_sucursal_cliente'     => ['nullable', 'integer'],
            'estado_repuesto'          => ['nullable', 'string', 'max:50'],
            'garantia_tipo'            => [
                'required_if:motivo_ingreso,Validacion de Garantia',
                'nullable',
                'string',
                'max:50',
                Rule::in(['propia', 'externa', 'PROPIA', 'EXTERNA', 'interna', 'INTERNA'])
            ],
            'cas_id'                   => ['required_if:garantia_tipo,externa,EXTERNA', 'nullable', 'integer', 'exists:cas,id'],
            'repuesto_inventario_id'   => ['required_if:estado_repuesto,Con stock', 'nullable', 'integer', 'exists:repuestos,id'],

            'cred_usuario'             => ['nullable', 'array'],
            'cred_contrasena'          => ['nullable', 'array'],
            'cred_es_patron'           => ['nullable', 'array']
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
