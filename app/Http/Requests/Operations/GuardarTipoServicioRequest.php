<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarTipoServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reglas = [
            'accion' => ['required', 'string', 'in:crear,editar,eliminar'],
            'id'     => ['nullable', 'integer']
        ];

        if ($this->input('accion') !== 'eliminar') {
            $reglas['nombre']      = ['required', 'string', 'max:255'];
            $reglas['precio']      = ['required', 'numeric', 'min:0'];
            $reglas['descripcion'] = ['nullable', 'string'];
            $reglas['activo']      = ['nullable', 'integer', 'in:0,1'];
        }

        return $reglas;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => $validator->errors()->first()
        ]));
    }
}
