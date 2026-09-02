<?php

namespace App\Http\Requests\Operations;

use App\Models\Directory\SucursalCliente;
use App\Rules\EcuadorIdentificacion;
use App\Rules\EcuadorTelefono;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
        $esGarantia = $this->input('motivo_ingreso') === 'Validacion de Garantia';
        $garantiaExterna = in_array(strtolower((string) $this->input('garantia_tipo')), ['externa'], true);
        $todayEcuador = Carbon::now('America/Guayaquil')->format('Y-m-d');

        $isEmpresaRuc = $this->input('cli_tipo') === 'empresa' || 
            (strlen(trim((string)$this->input('cli_identificacion'))) === 13 && 
            ($this->input('cli_apellidos') === '.' || !$this->input('cli_apellidos')));

        $reglas = [
            // Validacion de Cliente
            'cli_identificacion' => [
                $esEmpresa ? 'nullable' : 'required',
                'string',
                new EcuadorIdentificacion,
            ],
            'cli_nombres' => [
                $esEmpresa ? 'nullable' : 'required', 
                'string', 
                'max:100', 
                $isEmpresaRuc ? 'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,\-#&()\/]+$/' : 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'
            ],
            'cli_apellidos' => [
                $esEmpresa || $isEmpresaRuc ? 'nullable' : 'required', 
                'string', 
                'max:100', 
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s.]+$/'
            ],
            'cli_telefono' => [
                $esEmpresa ? 'nullable' : 'required',
                'string',
                new EcuadorTelefono,
            ],
            'cli_correo' => ['nullable', 'email', 'max:100'],
            'cli_direccion' => ['nullable', 'string', 'max:200'],

            // Validacion de Equipo
            'eq_tipo' => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:50'],
            'eq_marca' => [$esEmpresa ? 'nullable' : 'required', 'string', 'max:50'],
            'eq_modelo' => [
                $esEmpresa ? 'nullable' : 'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($esEmpresa) {
                    if ($esEmpresa) {
                        return;
                    }

                    $codigo = strtoupper(trim((string) $this->input('producto_inventario_codigo', '')));
                    if ($codigo === '') {
                        $descripcion = strtoupper(trim((string) $value));
                        if ($descripcion === '' || mb_strlen($descripcion) < 2) {
                            $fail('Debes ingresar la descripción o modelo del equipo.');
                        }
                        return;
                    }

                    $existe = \App\Models\Inventory\ProductoInventario::whereRaw('UPPER(TRIM(codigo)) = ?', [$codigo])->exists();
                    if ($existe) {
                        return;
                    }

                    $descripcion = strtoupper(trim((string) $value));
                    if ($descripcion === '' || $descripcion === $codigo || $descripcion === 'GENERICO' || mb_strlen($descripcion) < 2) {
                        $fail('Debes ingresar una descripción válida para el producto nuevo antes de crear la orden.');
                    }
                },
            ],
            'eq_contrasena' => ['nullable', 'string', 'max:100'],
            'eq_falla' => [$esEmpresa ? 'nullable' : 'required', 'string'],
            'eq_observacion' => ['nullable', 'string'],
            'eq_tipo_servicio' => [$esGarantia ? 'required' : 'nullable', 'integer', 'exists:tiposservicio,id'],
            'tipo_servicio_texto' => ['required_if:motivo_ingreso,Servicio Cliente Externo', 'nullable', 'string', 'max:100'],
            'producto_inventario_codigo' => [$esGarantia ? 'required' : 'nullable', 'string', 'max:50'],

            'series' => [$esEmpresa ? 'nullable' : 'required', 'array', 'min:1'],
            'series.*' => ['nullable', 'string', 'max:100'],

            // Validacion de Orden
            'ord_tecnico_id' => ['required', 'integer', 'exists:usuarios,id'],
            'motivo_ingreso' => ['required', 'string', 'max:50', Rule::in(['Servicio Cliente Externo', 'Validacion de Garantia', 'Servicios a Empresas'])],
            'nro_factura' => ['required_if:motivo_ingreso,Validacion de Garantia', 'nullable', 'string', 'max:50'],
            'nro_factura_2' => ['nullable', 'string', 'max:50'],
            'fecha_facturacion' => ['required_if:motivo_ingreso,Validacion de Garantia', 'nullable', 'date', 'before_or_equal:'.$todayEcuador],
            'fecha_prometido' => [$esEmpresa ? 'nullable' : 'required', 'date', 'after_or_equal:'.$todayEcuador],
            'nro_sucursal_cliente' => [
                $esGarantia ? 'required' : 'nullable',
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
            'estado_repuesto' => ['nullable', 'string', 'max:50'],
            'empresa_garantia' => [
                $esGarantia ? 'required' : 'nullable',
                'string',
                'max:50',
                Rule::in(['NOVISOLUTIONS', 'ENV', 'novisolutions', 'env', 'Novisolutions', 'Env'])
            ],
            'garantia_tipo' => [
                $esGarantia ? 'required' : 'nullable',
                'string',
                'max:50',
                Rule::in(['propia', 'externa', 'PROPIA', 'EXTERNA', 'interna', 'INTERNA']),
            ],
            'cas_id' => [$garantiaExterna ? 'required' : 'nullable', 'integer', 'exists:cas,id'],
            'repuesto_inventario_id' => ['required_if:estado_repuesto,Con stock', 'nullable', 'integer', 'exists:repuestos,id'],
            'repuestos_seleccionados' => ['nullable', 'array'],
            'repuestos_seleccionados.*' => ['integer', 'exists:repuestos,id'],

            'cred_usuario' => ['nullable', 'array'],
            'cred_contrasena' => ['nullable', 'array'],
            'cred_es_patron' => ['nullable', 'array'],
        ];

        if ($esEmpresa) {
            $subtipo = $this->input('subtipo_empresa');
            $requiereEquipo = in_array($subtipo, ['Autoconsumo', 'Stock'], true);

            $esNovisolutionsServicio = ($subtipo === 'Servicios');

            $reglas = array_merge($reglas, [
                'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
                'subtipo_empresa' => ['required', Rule::in(['Autoconsumo', 'Servicios', 'Stock'])],
                'emp_tipo_servicio' => [$subtipo === 'Servicios' ? 'required' : 'nullable', 'string', 'max:255'],
                'emp_nro_ticket' => [$subtipo === 'Servicios' ? 'required' : 'nullable', 'string', 'max:100'],
                'emp_descripcion' => [$subtipo === 'Servicios' ? 'required' : 'nullable', 'string'],
                'emp_tipo_equipo' => [$requiereEquipo ? 'required' : 'nullable', 'string', 'max:50'],
                'emp_marca' => [$requiereEquipo ? 'required' : 'nullable', 'string', 'max:50'],
                'emp_modelo' => [$requiereEquipo ? 'required' : 'nullable', 'string', 'max:100'],
                'emp_series' => ['nullable', 'array'],
                'emp_series.*' => ['nullable', 'string', 'max:100'],
                'emp_falla' => [$requiereEquipo ? 'required' : 'nullable', 'string'],
                'emp_observacion' => [$requiereEquipo ? 'required' : 'nullable', 'string'],
                'emp_tipo_servicio_id' => ['nullable', 'integer', 'exists:tiposservicio,id'],
                'emp_fecha_prometido' => ['required', 'date'],
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

                // Nuevas reglas para NOVISOLUTIONS
                'valor_hora' => ['nullable', 'numeric', 'min:0'],
                'horas_trabajadas' => ['nullable', 'numeric', 'min:0'],
                'tecnicos_asignados' => [$esNovisolutionsServicio ? 'required' : 'nullable', 'array', 'min:1', 'max:5'],
                'tecnicos_asignados.*' => ['integer', 'exists:usuarios,id'],
                'tecnico_encargado' => [
                    $esNovisolutionsServicio ? 'required' : 'nullable',
                    'integer',
                    'exists:usuarios,id',
                    function ($attribute, $value, $fail) use ($esNovisolutionsServicio) {
                        if (! $esNovisolutionsServicio || $value === null || $value === '') {
                            return;
                        }

                        $tecnicosAsignados = $this->input('tecnicos_asignados', []);
                        if (! is_array($tecnicosAsignados)) {
                            $tecnicosAsignados = [$tecnicosAsignados];
                        }

                        $idsAsignados = array_map('intval', array_filter($tecnicosAsignados));
                        if (! in_array((int) $value, $idsAsignados, true)) {
                            $fail('El técnico encargado debe estar dentro de los técnicos asignados.');
                        }
                    },
                ],
                'cas_id_empresa' => ['nullable', 'integer', 'exists:cas,id'],
            ]);

            if ($esNovisolutionsServicio) {
                $reglas['ord_tecnico_id'] = ['nullable', 'integer'];
            }
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'cli_nombres.regex' => 'El nombre del cliente sólo debe contener letras, tildes y espacios.',
            'cli_apellidos.regex' => 'El apellido del cliente sólo debe contener letras, tildes y espacios.',
            'fecha_facturacion.before_or_equal' => 'La fecha de facturación no puede ser superior al día de hoy.',
            'fecha_prometido.after_or_equal' => 'La fecha prometida de entrega no puede ser anterior al día de hoy.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('valor_hora') && $this->input('valor_hora') === '') {
            $this->merge(['valor_hora' => null]);
        }
        if ($this->has('horas_trabajadas') && $this->input('horas_trabajadas') === '') {
            $this->merge(['horas_trabajadas' => null]);
        }
        if ($this->has('cas_id') && $this->input('cas_id') === '') {
            $this->merge(['cas_id' => null]);
        }
        if ($this->has('garantia_tipo') && $this->input('garantia_tipo') === '') {
            $this->merge(['garantia_tipo' => null]);
        }
        if ($this->has('eq_tipo_servicio') && $this->input('eq_tipo_servicio') === '') {
            $this->merge(['eq_tipo_servicio' => null]);
        }
        if ($this->has('nro_sucursal_cliente') && $this->input('nro_sucursal_cliente') === '') {
            $this->merge(['nro_sucursal_cliente' => null]);
        }
        if ($this->has('cas_id_empresa') && $this->input('cas_id_empresa') === '') {
            $this->merge(['cas_id_empresa' => null]);
        }
        if ($this->has('tecnico_encargado') && $this->input('tecnico_encargado') === '') {
            $this->merge(['tecnico_encargado' => null]);
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



