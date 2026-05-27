<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarRepuestoRequest extends FormRequest
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
            $reglas['nro_parte']           = ['nullable', 'string', 'max:100'];
            $reglas['nombre']              = ['required', 'string', 'max:255'];
            $reglas['stock']               = ['required', 'integer', 'min:0'];
            $reglas['costo']               = ['required', 'numeric', 'min:0'];
            $reglas['bodega']              = ['nullable', 'string', 'max:100'];
            $reglas['descripcion']         = ['nullable', 'string'];
            $reglas['marca_id']            = ['nullable', 'string', 'max:100'];
            $reglas['tipo_dispositivo_id'] = ['nullable', 'string', 'max:100'];
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
