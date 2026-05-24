<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarListaCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitudes_ids'   => ['required', 'array', 'min:1'],
            'solicitudes_ids.*' => ['integer', 'exists:solicitudesrepuesto,id'],
            'observacion'       => ['nullable', 'string', 'max:500']
        ];
    }

    public function messages(): array
    {
        return [
            'solicitudes_ids.required' => 'Debe seleccionar al menos una solicitud para generar la lista.',
            'solicitudes_ids.min'      => 'Debe seleccionar al menos una solicitud para generar la lista.'
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