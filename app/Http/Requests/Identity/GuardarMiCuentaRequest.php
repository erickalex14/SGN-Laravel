<?php

namespace App\Http\Requests\Identity;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\EcuadorTelefono;

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
            'telefono' => ['nullable', 'string', new EcuadorTelefono()],
            'correo' => ['nullable', 'email', 'max:100'],
            'actual' => ['required_if:accion,password', 'nullable', 'string'],
            'nueva' => ['required_if:accion,password', 'nullable', 'string', 'min:6', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.regex' => 'El nombre sólo debe contener letras, tildes y espacios.',
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
