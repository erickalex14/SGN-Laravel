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

        $estadosValidosEmpresa = [
            'Pendiente',
            'Recibida',
            'En proceso',
            'Finalizada',
            'Lista para entrega',
            'Entregada',
            'Incinerox',
            'Devuelto sin reparar'
        ];

        $estadosValidosPersonal = [
            // Nuevo Flujo
            'Recibido en Recepcion',
            'Entregado al Tecnico',
            'Pendiente',
            'En reparacion',
            'Reparada',
            'Entregado en Recepcion para Entrega',
            'Cerrado',
            // Flujo Tradicional / Compatibilidad
            'Recibida',
            'En proceso',
            'Finalizada',
            'Lista para entrega',
            'Entregada',
            'Nota de Credito',
            'Devuelto sin reparar'
        ];

        return [
            'id' => ['required', 'integer', $tipoOrden === 'empresa' ? 'exists:ordenesempresas,id' : 'exists:ordenes,id'],
            'tipo_orden' => ['nullable', 'string', 'in:personal,empresa'],
            'estado' => ['required', 'string', $tipoOrden === 'empresa'
                ? 'in:' . implode(',', $estadosValidosEmpresa)
                : 'in:' . implode(',', $estadosValidosPersonal)],
            'nc_asunto' => ['nullable', 'string', 'max:255', 'required_if:estado,Nota de Credito'],
            'nc_detalles' => ['nullable', 'string', 'max:5000', 'required_if:estado,Nota de Credito'],
            'horas_trabajadas' => ['nullable', 'numeric', 'min:0'],
            'memo_entrega' => ['nullable', 'string', 'max:5000'],
            'foto_evidencia' => ['nullable', 'file', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'id.required' => 'El identificador de la orden es obligatorio.',
            'id.exists' => 'La orden seleccionada no existe en el sistema.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errores = implode(' ', $validator->errors()->all());
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => 'Error de validación: ' . ($errores ?: 'Datos inválidos.')
        ]));
    }
}
