<?php

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'min:1'],
            'nro_sucursal' => ['required', 'integer', 'min:1', 'max:999'],
            'ciudad' => ['required', 'string', 'max:100'],
            'secuencial' => ['required', 'string', 'min:2', 'max:10', 'regex:/^[A-Z0-9]+$/'],
            'nro_base'     => ['nullable', 'string', 'regex:/^09\d{8}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nro_sucursal.required' => 'El número de sucursal debe estar entre 1 y 999.',
            'nro_sucursal.min'      => 'El número de sucursal debe estar entre 1 y 999.',
            'nro_sucursal.max'      => 'El número de sucursal debe estar entre 1 y 999.',
            'ciudad.required'       => 'El nombre/ciudad es obligatorio.',
            'secuencial.required'   => 'El secuencial debe tener entre 2 y 10 caracteres.',
            'secuencial.regex'      => 'El secuencial solo puede contener letras y números (sin espacios ni tildes).',
            'nro_base.regex'        => 'El Nro. Base debe empezar por 09 y tener 10 dígitos.'
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $error = $validator->errors()->first() ?: 'Validation errors';
        throw new HttpResponseException(response()->json([
            'ok' => false,
            'error' => $error,
            'errors' => $validator->errors(),
        ], 422));
    }
}
