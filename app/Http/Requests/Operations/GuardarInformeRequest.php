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
            'orden_id'        => ['required', 'integer', 'not_in:0'],
            'antecedentes'    => ['required', 'string'],
            'proceso'         => ['required', 'string'],
            'conclusion'      => ['required', 'string'],
            'recomendaciones' => ['nullable', 'string'],
            'estado_equipo'   => ['required', 'string', 'in:Operativo,Reparado parcialmente,Sin reparación posible,En espera de repuesto,Desguace,OPERATIVO,OPERATIVO PARCIAL,NO OPERATIVO'],
            'fecha_informe'   => ['nullable', 'date'],
            'fotos'           => ['nullable', 'array', 'max:10'],
            'fotos.*'         => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'captions'        => ['nullable', 'array', 'max:10'],
            'captions.*'      => ['nullable', 'string', 'max:255'],
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
