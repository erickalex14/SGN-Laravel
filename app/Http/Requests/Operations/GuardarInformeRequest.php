<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarInformeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_id'        => ['required', 'integer', 'min:1'],
            'antecedentes'    => ['required', 'string'],
            'proceso'         => ['required', 'string'],
            'conclusion'      => ['required', 'string'],
            'recomendaciones' => ['nullable', 'string'],
            'estado_equipo'   => ['required', 'string', 'in:Operativo,Reparado parcialmente,Desguace,En espera de repuesto,OPERATIVO,OPERATIVO PARCIAL,NO OPERATIVO'],
            'fotos'           => ['nullable', 'array', 'max:10'],
            'fotos.*'         => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación: ' . $validator->errors()->first()
        ]));
    }
}
