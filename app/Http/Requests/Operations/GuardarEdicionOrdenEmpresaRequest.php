<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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

        if ($ordenId) {
            $orden = \App\Models\Operations\OrdenEmpresa::find($ordenId);
            if ($orden && $orden->subtipo === 'Servicios') {
                $requiereServicioCampos = true;
            }
        }

        return [
            'orden_id'           => ['required', 'integer', 'exists:ordenesempresas,id'],
            'equipo_id'          => ['required', 'integer', 'exists:equipos,id'],
            'estado'             => ['required', 'string', 'max:50'],
            'descripcion'        => ['required', 'string'],
            'eq_observacion'     => ['nullable', 'string'],
            'fecha_prometido'    => ['required', 'date'],
            'valor_hora'         => ['nullable', 'numeric', 'min:0'],
            'horas_trabajadas'   => ['nullable', 'numeric', 'min:0'],
            'tecnicos_asignados' => [$requiereServicioCampos ? 'required' : 'nullable', 'array', 'min:1', 'max:5'],
            'tecnicos_asignados.*' => ['integer', 'exists:usuarios,id'],
            'cas_id_empresa'     => ['nullable', 'integer', 'exists:cas,id'],
            'tecnico_id'         => ['nullable', 'integer', 'exists:usuarios,id'],
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
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación: ' . $validator->errors()->first()
        ]));
    }
}
