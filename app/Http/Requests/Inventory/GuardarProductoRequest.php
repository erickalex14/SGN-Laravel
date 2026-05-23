<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarProductoRequest extends FormRequest
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
            $reglas['codigo']              = ['required', 'string', 'max:100'];
            $reglas['descripcion']         = ['required', 'string', 'max:255'];
            $reglas['marca_id']            = ['required', 'integer', 'exists:marcas,id'];
            $reglas['tipo_dispositivo_id'] = ['required', 'integer', 'exists:tiposdispositivo,id'];
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
