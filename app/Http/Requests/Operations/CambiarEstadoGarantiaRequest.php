<?php

namespace App\Http\Requests\Operations;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CambiarEstadoGarantiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_id' => ['required', 'integer', 'exists:ordenes,id'],
            'estado_garantia' => ['required', 'string', 'in:Pendiente,Aceptada,Rechazada'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'ok' => false,
            'error' => $validator->errors()->first(),
        ]));
    }
}
