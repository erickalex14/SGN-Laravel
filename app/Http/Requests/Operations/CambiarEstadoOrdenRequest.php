<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CambiarEstadoOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoOrden = $this->input('tipo_orden') === 'empresa' ? 'empresa' : 'personal';

        return [
            'id' => ['required', 'integer', $tipoOrden === 'empresa' ? 'exists:ordenesempresas,id' : 'exists:ordenes,id'],
            'tipo_orden' => ['nullable', 'string', 'in:personal,empresa'],
            'estado' => ['required', 'string', $tipoOrden === 'empresa'
                ? 'in:Pendiente,En proceso,Finalizada,Entregada'
                : 'in:Pendiente,En proceso,Finalizada,Entregada,Nota de Credito'],
            'nc_asunto' => ['nullable', 'string', 'max:255', 'required_if:estado,Nota de Credito'],
            'nc_detalles' => ['nullable', 'string', 'max:5000', 'required_if:estado,Nota de Credito'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación al intentar cambiar el estado.'
        ]));
    }
}
