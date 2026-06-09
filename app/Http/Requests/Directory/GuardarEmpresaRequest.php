<?php

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\EcuadorIdentificacion;
use App\Rules\EcuadorTelefono;

class GuardarEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorizacion se maneja por el middleware de permisos
    }

    public function rules(): array
    {
        // Si la accion es eliminar, solo requerimos el ID
        if ($this->input('accion') === 'eliminar') {
            return ['id' => ['required', 'integer', 'min:1']];
        }

        return [
            'nombre'    => ['required', 'string', 'max:15'],
            'ruc'       => ['required', 'string', new EcuadorIdentificacion('ruc')],
            'telefono'  => ['nullable', 'string', new EcuadorTelefono()],
            'correo'    => ['nullable', 'email', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'ruc.required'    => 'El RUC es obligatorio.',
            'id.required'     => 'ID inválido.'
        ];
    }

    /**
     * Sobrescribimos este metodo para devolver el JSON exacto que espera tu JS original.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'ok'    => false,
            'error' => $validator->errors()->first()
        ]));
    }
}
