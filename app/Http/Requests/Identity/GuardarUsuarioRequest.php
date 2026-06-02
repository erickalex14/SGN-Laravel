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
            'usuario'        => [
                'required',
                'string',
                'size:10',
                'regex:/^[0-9]+$/',
                function ($attribute, $value, $fail) {
                    if (strlen($value) !== 10) {
                        return $fail('El usuario debe ser un número de cédula ecuatoriano de 10 dígitos.');
                    }
                    $provincia = (int) substr($value, 0, 2);
                    if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
                        return $fail('El código de provincia de la cédula no es válido.');
                    }
                    $tercerDigito = (int) $value[2];
                    if ($tercerDigito >= 6) {
                        return $fail('El número de cédula no es válido (tercer dígito incorrecto).');
                    }
                    $decimoDigito = (int) $value[9];
                    $suma = 0;
                    for ($i = 0; $i < 9; $i++) {
                        $coef = ($i % 2 === 0) ? 2 : 1;
                        $val = (int) $value[$i] * $coef;
                        if ($val >= 10) {
                            $val -= 9;
                        }
                        $suma += $val;
                    }
                    $residuo = $suma % 10;
                    $resultado = ($residuo === 0) ? 0 : 10 - $residuo;
                    if ($resultado !== $decimoDigito) {
                        return $fail('El número de cédula ingresado no es una cédula ecuatoriana válida.');
                    }
                }
            ],
            'nombre_tecnico' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', 'min:3'],
            'telefono'       => ['nullable', 'string', 'regex:/^0[0-9]{9}$/'],
            'correo_tec'     => ['nullable', 'email', 'max:30'],
            'rol_id'         => ['required', 'integer'],
            'grupo_id'       => ['required', 'integer'],
            'sucursal_id'    => ['required', 'integer'],
            'acceso_nc'      => ['nullable', 'boolean'],
            'sucursales'     => ['nullable', 'array'],
            'cas'            => ['nullable', 'array'],
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
            'usuario.size'     => 'El usuario debe ser un número de cédula de 10 dígitos.',
            'usuario.regex'    => 'El usuario sólo debe contener números.',
            'nombre_tecnico.required' => 'El campo nombre técnico es obligatorio.',
            'nombre_tecnico.regex'    => 'El nombre sólo debe contener letras, tildes y espacios.',
            'telefono.regex'   => 'El teléfono debe ser un formato ecuatoriano válido de 10 dígitos empezando con 0.',
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
