<?php

namespace App\Http\Requests\Identity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
            'id'             => ['nullable', 'integer'],
            'usuario'        => ['required', 'string', 'max:100'],
            'nombre_tecnico' => ['required', 'string', 'max:100'],
            'telefono'       => ['nullable', 'string', 'max:20'],
            'correo_tec'     => ['nullable', 'email', 'max:100'],
            'rol_id'         => ['required', 'integer'],
            'grupo_id'       => ['required', 'integer'],
            'sucursal_id'    => ['required', 'integer'],
            'acceso_nc'      => ['nullable', 'boolean'],
            'sucursales'     => ['nullable', 'array'],
            'permisos'       => ['nullable', 'array'],
        ];

        if (!$this->input('id')) {
            $rules['clave'] = ['required', 'string', 'min:3']; // Obligatorio al crear
        } else {
            $rules['clave'] = ['nullable', 'string', 'min:3']; // Opcional al editar
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'usuario.required' => 'El campo usuario es obligatorio.',
            'nombre_tecnico.required' => 'El campo nombre técnico es obligatorio.',
            'rol_id.required' => 'El campo rol es obligatorio.',
            'grupo_id.required' => 'El campo grupo de acceso es obligatorio.',
            'sucursal_id.required' => 'El campo sucursal es obligatorio.',
            'clave.required' => 'El campo clave es obligatorio al crear un nuevo usuario.',
            'clave.min' => 'La clave debe tener al menos 3 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => $validator->errors()->first()
        ]));
    }
}
