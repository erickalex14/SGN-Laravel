<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GestionarSolicitudRepuestoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'solicitud_id'   => ['required', 'integer', 'exists:solicitudesrepuesto,id'],
            'estado'         => ['required', 'string', 'in:APROBADA,RECHAZADA,COMPRA'],
            'motivo_rechazo' => ['required_if:estado,RECHAZADA', 'nullable', 'string']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok' => false, 'error' => $validator->errors()->first()
        ]));
    }
}