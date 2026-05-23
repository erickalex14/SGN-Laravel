<?php

namespace App\Http\Requests\Directory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarSucursalClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reglas = [
            'id'               => ['nullable', 'integer'],
            'nombre'           => ['required', 'string', 'max:100'],
            'provincia'        => ['nullable', 'string', 'max:100'],
            'novitec_sucursal' => ['nullable', 'string', 'max:50'],
            'activa'           => ['nullable', 'integer', 'in:0,1'],
        ];

        // En la creacion, numero y codigo son obligatorios
        if (!$this->input('id')) {
            $reglas['numero'] = ['required', 'integer', 'min:1', 'max:9999'];
            $reglas['codigo'] = ['required', 'string', 'max:10'];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'El número de sucursal es obligatorio.',
            'codigo.required' => 'El código es obligatorio.',
            'nombre.required' => 'El nombre es obligatorio.',
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
