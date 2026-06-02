<?php

namespace App\Http\Requests\Identity;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarMiCuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accion' => ['required', 'string', 'in:nombre,perfil,password'],
            'nombre' => ['required_if:accion,nombre,perfil', 'nullable', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'],
            'telefono' => ['nullable', 'string', 'regex:/^0[0-9]{9}$/'],
            'correo' => ['nullable', 'email', 'max:100'],
            'actual' => ['required_if:accion,password', 'nullable', 'string'],
            'nueva' => ['required_if:accion,password', 'nullable', 'string', 'min:6', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.regex' => 'El nombre sólo debe contener letras, tildes y espacios.',
            'telefono.regex' => 'El teléfono debe tener el formato ecuatoriano de 10 números (ej: 0987654321).',
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
