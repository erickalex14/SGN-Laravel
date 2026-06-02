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
            'valor_hora'         => [$requiereServicioCampos ? 'required' : 'nullable', 'numeric', 'min:0'],
            'horas_trabajadas'   => [$requiereServicioCampos ? 'required' : 'nullable', 'numeric', 'min:0'],
            'tecnicos_asignados' => [$requiereServicioCampos ? 'required' : 'nullable', 'array', 'min:1', 'max:5'],
            'tecnicos_asignados.*' => ['integer', 'exists:usuarios,id'],
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
