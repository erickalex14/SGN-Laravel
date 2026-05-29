# Plan de Integración de Inteligencia Artificial para el Módulo de Informes Técnicos (SGN-Laravel)

Este documento contiene las especificaciones técnicas y el código necesario para integrar la generación de informes asistida por IA en **SGN-Laravel**.

El objetivo es permitir que los técnicos ingresen anotaciones rápidas e informales del diagnóstico/reparación y convertirlas automáticamente en un borrador técnico profesional estructurado en formato JSON, manteniendo la arquitectura existente (`Controller -> Service`).

Se implementa soporte para dos proveedores con capas de desarrollo gratuitas: **Groq API** y **OpenRouter**.

---

## 1. Configuración del entorno y proveedores

### 1.1 Archivo `.env`

Se deben añadir las credenciales y la variable de control para alternar de forma transparente entre proveedores de IA.

```env
# Configuración del Asistente de IA (Opciones: groq / openrouter)
AI_PROVIDER=groq
GROQ_API_KEY=tu_api_key_aqui
OPENROUTER_API_KEY=tu_api_key_aqui
```

### 1.2 Archivo `config/services.php`

Se registra la estructura centralizada de configuración dentro del arreglo de servicios de Laravel.

```php
'ai' => [
    'provider' => env('AI_PROVIDER', 'groq'),
    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'url' => 'https://api.groq.com/openai/v1/chat/completions',
        'model' => 'llama3-8b-8192',
    ],
    'openrouter' => [
        'key' => env('OPENROUTER_API_KEY'),
        'url' => 'https://openrouter.ai/api/v1/chat/completions',
        'model' => 'meta-llama/llama-3-8b-instruct:free',
    ],
],
```

---

## 2. Capa de servicio (`App\Services\Operations\InformeAiService.php`)

Crear este nuevo archivo de servicio.

La lógica utiliza el cliente HTTP nativo de Laravel (`Illuminate\Support\Facades\Http`) para consumir endpoints compatibles con OpenAI. Se aplica JSON Mode en Groq y una restricción estricta en el prompt del sistema para garantizar compatibilidad estructural en los campos de salida.

```php
<?php

namespace App\Services\Operations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class InformeAiService
{
    /**
     * Procesa notas rápidas utilizando el proveedor de IA configurado para devolver un borrador de informe estructurado.
     *
     * @param string $notasTecnico
     * @return array
     * @throws Exception
     */
    public function generarBorradorInforme(string $notasTecnico): array
    {
        $provider = config('services.ai.provider');
        $config = config("services.ai.{$provider}");

        if (empty($config) || empty($config['key'])) {
            Log::error('Configuración de IA no válida o clave de API faltante para el proveedor seleccionado.', [
                'proveedor' => $provider,
            ]);
            throw new Exception('El servicio de asistencia por IA no se encuentra disponible actualmente.');
        }

        // Definición de directrices del sistema y estructura estricta de retorno.
        $promptSystem = "Eres un ingeniero de soporte técnico experto. Tu tarea consiste en transformar notas cortas o informales redactadas por un técnico en un informe formal y profesional en español. "
            . "Debes responder única y obligatoriamente con un objeto JSON que contenga los siguientes campos:\n"
            . "- antecedentes: Descripción detallada del estado inicial del equipo, fallas reportadas o síntomas observados.\n"
            . "- proceso: Pasos lógicos del diagnóstico, mediciones hechas, componentes revisados y la reparación o mantenimiento ejecutado.\n"
            . "- conclusion: Diagnóstico técnico conclusivo que justifique el estado en el que queda el dispositivo.\n"
            . "- recomendaciones: Consejos técnicos orientados al cliente para prevenir futuras fallas (opcional).\n"
            . "- estado_equipo: Debe ser exactamente uno de los siguientes valores fijos: 'Operativo', 'Reparado parcialmente', 'Sin reparación posible', 'En espera de repuesto'.\n"
            . "No debes incluir explicaciones fuera del JSON, bloques de Markdown ni caracteres decorativos.";

        $body = [
            'model' => $config['model'],
            'messages' => [
                ['role' => 'system', 'content' => $promptSystem],
                ['role' => 'user', 'content' => "Notas del técnico para procesar:\n" . $notasTecnico],
            ],
            'temperature' => 0.2,
        ];

        // Habilitar formato de objeto JSON nativo si el proveedor es Groq.
        if ($provider === 'groq') {
            $body['response_format'] = ['type' => 'json_object'];
        }

        try {
            Log::info('Iniciando solicitud de generación de informe técnico con IA.', [
                'proveedor' => $provider,
                'modelo' => $config['model'],
            ]);

            $response = Http::withToken($config['key'])
                ->timeout(20)
                ->post($config['url'], $body);

            if ($response->failed()) {
                Log::error('La API externa de IA retornó un código de error de procesamiento.', [
                    'proveedor' => $provider,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('No se pudo procesar la solicitud con el motor de inteligencia artificial.');
            }

            $resultado = $response->json();
            $textoJson = $resultado['choices'][0]['message']['content'] ?? '{}';

            $datosInforme = json_decode(trim($textoJson), true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($datosInforme)) {
                Log::error('La respuesta entregada por la IA no contiene una estructura JSON limpia o legible.', [
                    'texto_recibido' => $textoJson,
                ]);
                throw new Exception('Error al interpretar la estructura del informe generado automáticamente.');
            }

            return $datosInforme;
        } catch (Exception $e) {
            Log::error('Excepción crítica durante el procesamiento del informe con IA.', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Ocurrió un error interno al interactuar con el proveedor de Inteligencia Artificial.');
        }
    }
}
```

---

## 3. Capa de controlador (`App\Http\Controllers\Operations\InformeController.php`)

Agregar el siguiente método dentro del controlador de informes. Este método aprovecha `resolverContextoInformes()` para validar los permisos del usuario activo en sesión.

```php
/**
 * Endpoint AJAX para procesar notas y estructurar el borrador del informe mediante Inteligencia Artificial.
 *
 * @param \Illuminate\Http\Request $request
 * @param \App\Services\Operations\InformeAiService $aiService
 * @return \Illuminate\Http\JsonResponse
 */
public function generarConIa(Request $request, InformeAiService $aiService): JsonResponse
{
    $contexto = $this->resolverContextoInformes();

    if (!$contexto['puede_escribir_informe']) {
        return response()->json([
            'ok' => false,
            'error' => 'No tiene los permisos necesarios para redactar o modificar informes técnicos.',
        ]);
    }

    $notas = trim((string) $request->input('notas'));

    if (strlen($notas) < 10) {
        return response()->json([
            'ok' => false,
            'error' => 'La descripción provista es demasiado corta. Ingrese más detalles de la revisión.',
        ]);
    }

    try {
        $borrador = $aiService->generarBorradorInforme($notas);

        return response()->json([
            'ok' => true,
            'borrador' => $borrador,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
        ]);
    }
}
```

---

## 4. Definición de rutas (`routes/web.php`)

Registrar el nuevo endpoint AJAX bajo el grupo de rutas protegido correspondiente al módulo de operaciones e informes técnicos.

```php
use App\Http\Controllers\Operations\InformeController;

Route::middleware(['auth'])->prefix('operaciones/informes')->group(function () {
    // Rutas existentes...
    Route::post('/generar-con-ia', [InformeController::class, 'generarConIa'])->name('informes.generar.ia');
});
```

---

## 5. Cambios en la vista e interfaz (`resources/views/operations/informes/crear.blade.php`)

### 5.1 Bloque HTML de interfaz (Blade)

Insertar este bloque justo encima del contenedor del **PASO 2: Redactar Informe**.

```html
<div style="background: #f8fafc; border: 1.5px dashed #3b82f6; border-radius: 12px; padding: 18px; margin-bottom: 18px;">
    <h4 style="font-size: 13.5px; font-weight: 700; color: #1e40af; margin: 0 0 6px 0; display: flex; align-items: center; gap: 6px;">
        <i class="bi bi-cpu"></i> Asistente de Redacción con Inteligencia Artificial
    </h4>
    <p style="font-size: 12px; color: #475569; margin: 0 0 12px 0;">
        Escriba notas breves del diagnóstico y reparación realizados. El motor de IA estructurará de forma técnica
        los antecedentes, procesos y conclusiones requeridos por el formulario.
    </p>
    <div style="display: flex; gap: 10px;">
        <input type="text" id="ai-notas" class="ci-input" style="padding-left: 14px;" placeholder="Ej: Llego sin encender, corto en línea de entrada de 19v, se cambia condensador de superficie dañado, enciende OK.">
        <button type="button" id="btn-generar-ai" class="ci-btn-buscar" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);" onclick="_generarInformeConIa()">
            <i class="bi bi-magic"></i> <span>Procesar Notas</span>
        </button>
    </div>
</div>
```

### 5.2 Lógica JavaScript nativa (IIFE o sección de scripts)

Agregar la siguiente función para enviar la solicitud asíncrona con `fetch` y poblar automáticamente los campos del formulario al recibir respuesta exitosa.

```javascript
window._generarInformeConIa = function () {
    var notasInput = document.getElementById('ai-notas');
    var btnAi = document.getElementById('btn-generar-ai');
    var notasTxt = notasInput.value.trim();

    if (notasTxt.length < 10) {
        _msgForm('err', 'Debe ingresar una descripción más completa de las actividades para alimentar a la IA.');
        return;
    }

    btnAi.disabled = true;
    btnAi.innerHTML = '<span class="spin"></span> <span>Procesando...</span>';

    fetch('/operaciones/informes/generar-con-ia', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ notas: notasTxt }),
    })
        .then(function (res) {
            return res.json();
        })
        .then(function (data) {
            btnAi.disabled = false;
            btnAi.innerHTML = '<i class="bi bi-magic"></i> <span>Procesar Notas</span>';

            if (!data.ok) {
                _msgForm('err', data.error || 'Ocurrió un error inesperado al procesar el texto con la IA.');
                return;
            }

            // Asignación de valores directamente sobre el formulario existente.
            _setVal('inf-antecedentes', data.borrador.antecedentes || '');
            _setVal('inf-proceso', data.borrador.proceso || '');
            _setVal('inf-conclusion', data.borrador.conclusion || '');
            _setVal('inf-recomendaciones', data.borrador.recomendaciones || '');
            _setVal('inf-estado-equipo', data.borrador.estado_equipo || 'Operativo');

            _msgForm('ok', 'Borrador estructurado con éxito. Revise el contenido generado antes de almacenar el registro.');
        })
        .catch(function () {
            btnAi.disabled = false;
            btnAi.innerHTML = '<i class="bi bi-magic"></i> <span>Procesar Notas</span>';
            _msgForm('err', 'Error de red o comunicación al conectar con el servidor de asistencia.');
        });
};
```
