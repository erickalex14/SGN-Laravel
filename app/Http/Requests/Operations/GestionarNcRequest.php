<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GestionarNcRequest extends FormRequest
{
    public function authorize(): bool
    {
        // En el controlador verificaremos los permisos del admin
        return true; 
    }

    public function rules(): array
    {
        return [
            'solicitud_id'   => ['required', 'integer', 'exists:solicitudesnc,id'],
            'estado'         => ['required', 'string', 'in:APROBADA,RECHAZADA'],
            'motivo_rechazo' => ['required_if:estado,RECHAZADA', 'nullable', 'string', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_rechazo.required_if' => 'El motivo de rechazo es obligatorio cuando se rechaza la solicitud.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error: ' . $validator->errors()->first()
        ]));
    }
}