<?php

namespace App\Http\Requests\Directory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarCasRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'accion'    => ['required', 'string', 'in:crear,editar'],
            'id'        => ['required_if:accion,editar', 'nullable', 'integer'],
            'nombre'    => ['required', 'string', 'max:120'],
            'marca'     => ['nullable', 'string'],
            'telefono'  => ['nullable', 'string', 'max:30'],
            'correo'    => ['nullable', 'email', 'max:120'],
            'ciudad'    => ['nullable', 'string', 'max:80'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'contacto'  => ['nullable', 'string', 'max:100'],
            'notas'     => ['nullable', 'string'],
            'activo'    => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    public function messages() : array
    {
        return [
            'nombre.required' => 'El nombre del cas es obligatorio.',
            'accion.required' => 'Acción no reconocida.',
            'id.required_if'  => 'ID inválido.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => $validator->errors()->first()
        ]));
    }
}
