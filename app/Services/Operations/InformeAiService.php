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
            throw new Exception('El servicio de asistencia por IA no se encuentra configurado o no tiene una clave API válida.');
        }

        // Definición de directrices del sistema y estructura estricta de retorno (incluye 'Desguace').
        $promptSystem = "Eres un ingeniero de soporte técnico experto. Tu tarea consiste en transformar notas cortas o informales redactadas por un técnico en un informe formal y profesional en español.\n\n"
            . "Debes responder única y obligatoriamente con un objeto JSON que contenga los siguientes campos:\n"
            . "- antecedentes: Descripción detallada del estado inicial del equipo, fallas reportadas o síntomas observados.\n"
            . "- proceso: Pasos lógicos del diagnóstico, mediciones hechas, componentes revisados y la reparación o mantenimiento ejecutado.\n"
            . "- conclusion: Diagnóstico técnico conclusivo que justifique el estado en el que queda el dispositivo.\n"
            . "- recomendaciones: Consejos técnicos orientados al cliente para prevenir futuras fallas (opcional).\n"
            . "- estado_equipo: Debe ser exactamente uno de los siguientes valores fijos correspondientes al estado final del equipo:\n"
            . "  'Operativo', 'Reparado parcialmente', 'Sin reparación posible', 'Desguace', 'En espera de repuesto'.\n\n"
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
                ->timeout(25)
                ->post($config['url'], $body);

            if ($response->failed()) {
                Log::error('La API externa de IA retornó un código de error de procesamiento.', [
                    'proveedor' => $provider,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('No se pudo procesar la solicitud con el motor de inteligencia artificial. Intente de nuevo.');
            }

            $resultado = $response->json();
            $textoJson = $resultado['choices'][0]['message']['content'] ?? '{}';

            // Saneamiento robusto de bloques de código Markdown que los LLMs suelen incluir
            $textoJson = trim($textoJson);
            if (str_starts_with($textoJson, '```json')) {
                $textoJson = substr($textoJson, 7);
            } elseif (str_starts_with($textoJson, '```')) {
                $textoJson = substr($textoJson, 3);
            }
            if (str_ends_with($textoJson, '```')) {
                $textoJson = substr($textoJson, 0, -3);
            }
            $textoJson = trim($textoJson);

            $datosInforme = json_decode($textoJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($datosInforme)) {
                Log::error('La respuesta entregada por la IA no contiene una estructura JSON limpia o legible.', [
                    'texto_recibido' => $textoJson,
                    'json_error' => json_last_error_msg()
                ]);
                throw new Exception('Error al interpretar la estructura del informe generado automáticamente.');
            }

            return $datosInforme;
        } catch (Exception $e) {
            Log::error('Excepción crítica durante el procesamiento del informe con IA.', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception($e->getMessage() ?: 'Ocurrió un error interno al interactuar con el proveedor de Inteligencia Artificial.');
        }
    }
}
