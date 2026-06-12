<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\EcuadorIdentificacion;
use App\Rules\EcuadorTelefono;

class GuardarEdicionOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_id'               => ['required', 'integer', 'exists:ordenes,id'],
            'equipo_id'              => ['required', 'integer', 'exists:equipos,id'],
            'estado_orden'           => ['required', 'string', 'max:50'],
            'eq_falla'               => ['required', 'string'],
            'eq_observacion'         => ['nullable', 'string'],
            'tipo_servicio_id'       => ['nullable', 'integer'],
            'valor_estandar_id'      => ['nullable', 'integer'],
            'repuesto_inventario_id' => ['nullable', 'integer'],
            'fecha_prometido'        => ['nullable', 'date'],
            'cas_id'                 => ['nullable', 'integer', 'exists:cas,id'],
            'tecnico_id'             => ['required', 'integer', 'exists:usuarios,id'],

            // Campos de cliente
            'cli_identificacion'     => ['required', 'string', new EcuadorIdentificacion()],
            'cli_nombres'            => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'],
            'cli_apellidos'          => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'],
            'cli_telefono'           => ['required', 'string', new EcuadorTelefono()],
            'cli_correo'             => ['nullable', 'email', 'max:100'],
            'cli_direccion'          => ['nullable', 'string', 'max:200'],

            // Campos de factura / garantía
            'nro_factura'            => ['nullable', 'string', 'max:50'],
            'nro_factura_2'          => ['nullable', 'string', 'max:50'],
            'fecha_facturacion'      => ['nullable', 'date'],
            'nro_sucursal_cliente'   => ['nullable', 'integer'],

            // Series del equipo
            'series'                 => ['required', 'array', 'min:1'],
            'series.*'               => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('cas_id') && $this->input('cas_id') === '') {
            $this->merge(['cas_id' => null]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación: ' . $validator->errors()->first()
        ]));
    }
}