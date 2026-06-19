<?php

namespace App\Http\Requests\Operations;

use App\Models\Directory\SucursalCliente;
use App\Models\Operations\OrdenEmpresa;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarEdicionOrdenEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ordenId = $this->input('orden_id');
        $requiereServicioCampos = false;
        $esServicios = false;

        if ($ordenId) {
            $orden = OrdenEmpresa::find($ordenId);
            if ($orden) {
                if ($orden->subtipo === 'Servicios') {
                    $requiereServicioCampos = true;
                    $esServicios = true;
                }
            }
        }

        return [
            'orden_id' => ['required', 'integer', 'exists:ordenesempresas,id'],
            'equipo_id' => ['required', 'integer', 'exists:equipos,id'],
            'estado' => ['required', 'string', 'max:50'],
            'descripcion' => ['required', 'string'],
            'eq_observacion' => ['nullable', 'string'],
            'fecha_prometido' => ['required', 'date'],
            'valor_hora' => ['nullable', 'numeric', 'min:0'],
            'horas_trabajadas' => ['nullable', 'numeric', 'min:0'],
            'tecnicos_asignados' => [$requiereServicioCampos ? 'required' : 'nullable', 'array', 'min:1', 'max:5'],
            'tecnicos_asignados.*' => ['integer', 'exists:usuarios,id'],
            'tecnico_encargado' => [
                $requiereServicioCampos ? 'required' : 'nullable',
                'integer',
                'exists:usuarios,id',
                function ($attribute, $value, $fail) use ($requiereServicioCampos) {
                    if (! $requiereServicioCampos || $value === null || $value === '') {
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
            'tecnico_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'cas_id_empresa' => ['nullable', 'integer', 'exists:cas,id'],

            // Campos adicionales de equipo corporativo
            'eq_tipo' => ['nullable', 'string', 'max:100'],
            'eq_marca' => ['nullable', 'string', 'max:100'],
            'eq_modelo' => ['nullable', 'string', 'max:100'],
            'eq_serie' => ['nullable', 'string', 'max:100'],
            'eq_contrasena' => ['nullable', 'string', 'max:100'],

            // Series
            'series' => [$esServicios ? 'nullable' : 'required', 'array', 'min:1'],
            'series.*' => ['nullable', 'string', 'max:100'],
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
