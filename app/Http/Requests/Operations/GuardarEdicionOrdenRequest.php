<?php

namespace App\Http\Requests\Operations;

use App\Models\Directory\SucursalCliente;
use App\Rules\EcuadorIdentificacion;
use App\Rules\EcuadorTelefono;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarEdicionOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isEmpresaRuc = $this->input('cli_tipo') === 'empresa' || 
            (strlen(trim((string)$this->input('cli_identificacion'))) === 13 && 
            ($this->input('cli_apellidos') === '.' || !$this->input('cli_apellidos')));

        return [
            'orden_id' => ['required', 'integer', 'exists:ordenes,id'],
            'equipo_id' => ['required', 'integer', 'exists:equipos,id'],
            'estado_orden' => ['required', 'string', 'max:50'],
            'eq_falla' => ['required', 'string'],
            'eq_observacion' => ['nullable', 'string'],
            'tipo_servicio_id' => ['nullable', 'integer'],
            'valor_estandar_id' => ['nullable', 'integer'],
            'repuesto_inventario_id' => ['nullable', 'integer'],
            'fecha_prometido' => ['nullable', 'date'],
            'cas_id' => ['nullable', 'integer', 'exists:cas,id'],
            'tecnico_id' => ['required', 'integer', 'exists:usuarios,id'],

            // Campos de cliente
            'cli_identificacion' => ['required', 'string', new EcuadorIdentificacion],
            'cli_nombres' => [
                'required', 
                'string', 
                'max:100', 
                $isEmpresaRuc ? 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,\-#&()\/]+$/' : 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'
            ],
            'cli_apellidos' => [
                $isEmpresaRuc ? 'nullable' : 'required', 
                'string', 
                'max:100', 
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s.]+$/'
            ],
            'cli_telefono' => ['required', 'string', new EcuadorTelefono],
            'cli_correo' => ['nullable', 'email', 'max:100'],
            'cli_direccion' => ['nullable', 'string', 'max:200'],

            // Campos de factura / garantía
            'nro_factura' => ['nullable', 'string', 'max:50'],
            'nro_factura_2' => ['nullable', 'string', 'max:50'],
            'fecha_facturacion' => ['nullable', 'date'],
            'nro_sucursal_cliente' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $exists = SucursalCliente::where('codigo', $value)
                        ->orWhere('numero', (int) $value)
                        ->exists();
                    if (! $exists) {
                        $fail('El campo nro sucursal cliente seleccionado es inválido.');
                    }
                },
            ],

            // Series del equipo
            'series' => ['required', 'array', 'min:1'],
            'series.*' => ['nullable', 'string', 'max:100'],

            // Nuevos campos del equipo
            'eq_tipo' => ['required', 'string', 'max:100'],
            'eq_marca' => ['required', 'string', 'max:100'],
            'eq_modelo' => ['nullable', 'string', 'max:100'],
            'eq_contrasena' => ['nullable', 'string', 'max:100'],

            // Nuevos campos de la orden
            'motivo_ingreso' => ['required', 'string', 'max:100'],
            'garantia_tipo' => ['nullable', 'string', 'max:50'],
            'empresa_garantia' => ['nullable', 'string', 'max:50'],
            'observacion_orden' => ['nullable', 'string'],
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
            'ok' => false,
            'error' => 'Error de validación: '.$validator->errors()->first(),
        ]));
    }
}
