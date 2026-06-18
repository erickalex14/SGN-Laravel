<?php

namespace App\Http\Requests\Identity;

use App\Rules\EcuadorIdentificacion;
use App\Rules\EcuadorTelefono;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'id' => ['nullable', 'integer'],
            'usuario' => [
                'required',
                'string',
                new EcuadorIdentificacion('cedula'),
            ],
            'nombre_tecnico' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', 'min:3'],
            'telefono' => ['nullable', 'string', new EcuadorTelefono],
            'correo_tec' => ['nullable', 'email', 'max:30'],
            'rol_id' => ['required', 'integer'],
            'grupo_id' => ['required', 'integer'],
            'sucursal_id' => ['required', 'integer'],
            'acceso_nc' => ['nullable', 'boolean'],
            'sucursales' => ['nullable', 'array'],
            'cas' => ['nullable', 'array'],
            'permisos' => ['nullable', 'array'],
        ];

        if (! $this->input('id')) {
            $rules['clave'] = ['required', 'string', 'min:6', 'max:12']; // Obligatorio al crear
        } else {
            $rules['clave'] = ['nullable', 'string', 'min:6', 'max:12']; // Opcional al editar
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'usuario.required' => 'El campo usuario es obligatorio.',
            'nombre_tecnico.required' => 'El campo nombre técnico es obligatorio.',
            'nombre_tecnico.regex' => 'El nombre sólo debe contener letras, tildes y espacios.',
            'rol_id.required' => 'El campo rol es obligatorio.',
            'grupo_id.required' => 'El campo grupo de acceso es obligatorio.',
            'sucursal_id.required' => 'El campo sucursal es obligatorio.',
            'clave.required' => 'El campo clave es obligatorio al crear un nuevo usuario.',
            'clave.min' => 'La clave debe tener al menos 6 caracteres.',
            'clave.max' => 'La clave no debe superar los 12 caracteres.',
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
