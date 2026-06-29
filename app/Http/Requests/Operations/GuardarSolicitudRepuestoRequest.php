<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GuardarSolicitudRepuestoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $table = $this->input('tipo_orden') === 'empresa' ? 'ordenesempresas' : 'ordenes';
        return [
            'orden_id'        => ['required', 'integer', "exists:{$table},id"],
            'cantidad'        => ['required', 'integer', 'min:1'],
            'repuesto_nombre' => ['required_without:repuesto_inv_id', 'nullable', 'string', 'max:255'],
            'repuesto_inv_id' => ['nullable', 'integer', 'exists:repuestos,id'],
            'nro_parte'       => ['required', 'string', 'max:100'],
            'link_compra'     => ['nullable', 'string', 'max:500'],
            'descripcion'     => ['nullable', 'string'],
            'tipo_orden'      => ['nullable', 'string', 'in:personal,empresa']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok' => false, 'error' => 'Validación: ' . $validator->errors()->first()
        ]));
    }
}
