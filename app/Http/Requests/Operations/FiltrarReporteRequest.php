<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FiltrarReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado'       => ['nullable', 'string', 'max:50'],
            'estado_repuesto' => ['nullable', 'string', 'max:50'],
            'estado_garantia' => ['nullable', 'string', 'max:50'],
            'motivo_ingreso'  => ['nullable', 'string', 'max:80'],
            'marca'           => ['nullable', 'string', 'max:80'],
            'tipo_equipo'     => ['nullable', 'string', 'max:80'],
            'tipo_orden'      => ['nullable', 'string', 'in:personal,empresa'],
            'tecnico_id'   => ['nullable', 'integer', 'exists:usuarios,id'],
            'sucursal_id'  => ['nullable', 'integer', 'exists:sucursales,id']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error en los parámetros de filtrado: ' . $validator->errors()->first()
        ]));
    }
}
