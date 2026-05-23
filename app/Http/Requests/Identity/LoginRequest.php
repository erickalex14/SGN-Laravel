<?php

namespace App\Http\Requests\Identity;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario' => 'required|string|max:50',
            'clave' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'usuario.required' => 'El campo usuario es obligatorio',
            'clave.required' => 'El campo clave es obligatorio',
        ];
    }
}
