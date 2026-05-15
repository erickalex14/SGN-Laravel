<?php

namespace App\Http\Requests\Directory\Empresa;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
{
    /**
     * Determina si el usuario esta autorizado para realizar esta solicitud.
     * Aqui validaremos los permisos mas adelante.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validacion que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            // Validamos que el RUC sea unico directamente en la base de datos
            'ruc' => 'required|string|max:20|unique:empresas,ruc',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'direccion_empresa' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Mensajes de error personalizados (opcional pero recomendado para la UI).
     */
    public function messages(): array
    {
        return [
            'ruc.unique' => 'El RUC ingresado ya se encuentra registrado en nuestro sistema.',
            'correo.email' => 'El formato del correo electronico no es valido.',
        ];
    }
}
