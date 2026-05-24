<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarSolicitudRepuestoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'orden_id'        => ['required', 'integer', 'exists:ordenes,id'],
            'cantidad'        => ['required', 'integer', 'min:1'],
            'repuesto_nombre' => ['required_without:repuesto_inv_id', 'nullable', 'string', 'max:255'],
            'repuesto_inv_id' => ['nullable', 'integer', 'exists:repuestos,id'],
            'nro_parte'       => ['nullable', 'string', 'max:100'],
            'link_compra'     => ['nullable', 'url'],
            'descripcion'     => ['nullable', 'string']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok' => false, 'error' => 'Validación: ' . $validator->errors()->first()
        ]));
    }
}