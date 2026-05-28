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
        $esEmpresa = $this->input('motivo_ingreso') === 'Servicios a Empresas';

        $reglas = [
            // Validacion de Cliente
            'cli_identificacion' => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:20'],
            'cli_nombres'        => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:100'],
            'cli_apellidos'      => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:100'],
            'cli_telefono'       => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:20'],
            'cli_correo'         => ['nullable', 'email', 'max:100'],
            'cli_direccion'      => ['nullable', 'string', 'max:200'],

            // Validacion de Equipo
            'eq_tipo'                  => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:50'],
            'eq_marca'                 => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:50'],
            'eq_modelo'                => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:100'],
            'eq_contrasena'            => ['nullable', 'string', 'max:100'],
            'eq_falla'                 => [$esEmpresa ? 'nullable' : 'required', 'string'],
            'eq_observacion'           => ['nullable', 'string'],
            'eq_tipo_servicio'         => ['nullable', 'integer'],
            'tipo_servicio_texto'      => ['required_if:motivo_ingreso,Servicio Cliente Externo', 'nullable', 'string', 'max:100'],
            'producto_inventario_codigo' => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:50'],

            'series'                   => [$esEmpresa ? 'nullable' : 'required', 'array', 'min:1'],
            'series.*'                 => ['nullable', 'string', 'max:100'],

            // Validacion de Orden
            'ord_tecnico_id'           => ['required', 'integer', 'exists:usuarios,id'],
            'motivo_ingreso'           => ['required', 'string', 'max:50', Rule::in(['Servicio Cliente Externo', 'Validacion de Garantia', 'Servicios a Empresas'])],
            'nro_factura'              => ['required_if:motivo_ingreso,Validacion de Garantia', 'nullable', 'string', 'max:50'],
            'nro_factura_2'            => ['nullable', 'string', 'max:50'],
            'fecha_facturacion'        => ['required_if:motivo_ingreso,Validacion de Garantia', 'nullable', 'date'],
            'fecha_prometido'          => [$esEmpresa ? 'nullable' : 'required', 'date'],
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

        if ($esEmpresa) {
            $subtipo = $this->input('subtipo_empresa');

            $reglas = array_merge($reglas, [
                'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
                'subtipo_empresa' => ['required', Rule::in(['Autoconsumo', 'Servicios'])],
                'emp_tipo_servicio' => [$subtipo === 'Servicios' ? 'required' : 'nullable', 'string', 'max:255'],
                'emp_nro_ticket' => [$subtipo === 'Servicios' ? 'required' : 'nullable', 'string', 'max:100'],
                'emp_descripcion' => [$subtipo === 'Servicios' ? 'required' : 'nullable', 'string'],
                'emp_tipo_equipo' => [$subtipo === 'Autoconsumo' ? 'required' : 'nullable', 'string', 'max:50'],
                'emp_marca' => [$subtipo === 'Autoconsumo' ? 'required' : 'nullable', 'string', 'max:50'],
                'emp_modelo' => [$subtipo === 'Autoconsumo' ? 'required' : 'nullable', 'string', 'max:100'],
                'emp_series' => [$subtipo === 'Autoconsumo' ? 'required' : 'nullable', 'array', 'min:1'],
                'emp_series.*' => ['nullable', 'string', 'max:100'],
                'emp_falla' => [$subtipo === 'Autoconsumo' ? 'required' : 'nullable', 'string'],
                'emp_observacion' => [$subtipo === 'Autoconsumo' ? 'required' : 'nullable', 'string'],
                'emp_tipo_servicio_id' => ['nullable', 'integer', 'exists:tiposservicio,id'],
                'emp_fecha_prometido' => ['required', 'date'],
            ]);
        }

        return $reglas;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación: ' . $validator->errors()->first()
        ]));
    }
}
