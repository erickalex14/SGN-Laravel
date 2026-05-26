<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarInformeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_id'        => ['required', 'integer', 'exists:ordenes,id'],
            'antecedentes'    => ['required', 'string'],
            'proceso'         => ['required', 'string'],
            'conclusion'      => ['required', 'string'],
            'recomendaciones' => ['nullable', 'string'],
            'estado_equipo'   => ['required', 'string', 'max:100'],
            'fotos'           => ['nullable', 'array', 'max:4'], // Maximo 4 fotos por informe
            'fotos.*'         => ['image', 'mimes:jpeg,png,jpg', 'max:5120'] // Max 5MB por imagen
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