<?php

namespace App\Http\Requests\Operations;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AsignarRepuestoOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $table = $this->input('tipo_orden') === 'empresa' ? 'ordenesempresas' : 'ordenes';
        return [
            'orden_id' => ['required', 'integer', "exists:{$table},id"],
            'repuesto_inventario_id' => ['required', 'integer', 'exists:repuestos,id'],
            'tipo_orden' => ['nullable', 'string', 'in:personal,empresa'],
            'cantidad' => ['nullable', 'integer', 'min:1']
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'ok' => false,
            'error' => $validator->errors()->first(),
        ]));
    }
}

