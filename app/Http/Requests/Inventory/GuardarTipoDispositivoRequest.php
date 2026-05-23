<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarTipoDispositivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'accion' => ['required', 'string', 'in:crear,editar,eliminar'],
            'id'     => ['nullable', 'integer']
        ];

        if ($this->input('accion') !== 'eliminar') {
            $rules['codigo'] = ['required', 'string', 'max:50'];
            $rules['nombre'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => $validator->errors()->first()
        ]));
    }
}
