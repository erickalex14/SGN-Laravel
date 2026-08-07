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
            'id'     => ['nullable']
        ];

        if ($this->input('accion') !== 'eliminar') {
            $reglas['codigo']              = ['required', 'string', 'max:100'];
            $reglas['nro_parte']           = ['nullable'];
            $reglas['nombre']              = ['required', 'string', 'max:255'];
            $reglas['stock']               = ['required', 'numeric', 'min:0'];
            $reglas['costo']               = ['required', 'numeric', 'min:0'];
            $reglas['bodega']              = ['nullable'];
            $reglas['descripcion']         = ['nullable'];
            $reglas['marca_id']            = ['nullable'];
            $reglas['tipo_dispositivo_id'] = ['nullable'];
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
