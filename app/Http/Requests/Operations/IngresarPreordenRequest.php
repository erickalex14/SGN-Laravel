<?php

namespace App\Http\Requests\Operations;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IngresarPreordenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preorden_id' => ['required', 'integer', 'exists:preordenes,id'],
            'tecnico_id' => ['required', 'integer', 'exists:usuarios,id'],
            'direccion' => ['required', 'string', 'max:200'],
            'serie' => ['nullable', 'string', 'max:100'],
            'observacion' => ['nullable', 'string'],
            'fecha_prometido' => ['required', 'date', 'after:today'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'ok' => false,
            'error' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}

