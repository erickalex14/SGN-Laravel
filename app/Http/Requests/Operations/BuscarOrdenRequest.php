<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BuscarOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo'        => ['required', 'string', 'in:nro_orden,cedula,nombre,serie,factura,tecnico,empresa'],
            'q'           => ['nullable', 'string', 'max:120'],
            // Filtros opcionales
            'estado'      => ['nullable', 'string', 'max:60'],
            'tecnico_id'  => ['nullable', 'integer'],
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validacion: ' . $validator->errors()->first(),
        ], 422));
    }
}
