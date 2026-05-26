<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CambiarEstadoOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:ordenes,id'],
            'estado' => ['required', 'string', 'in:Pendiente,En proceso,Finalizada,Entregada,Nota de Credito'],
            'nc_asunto' => ['nullable', 'string', 'max:255', 'required_if:estado,Nota de Credito'],
            'nc_detalles' => ['nullable', 'string', 'max:5000', 'required_if:estado,Nota de Credito'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación al intentar cambiar el estado.'
        ]));
    }
}
