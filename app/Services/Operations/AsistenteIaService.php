<?php

namespace App\Services\Operations;

use App\Models\Operations\Orden;
use App\Models\Operations\SolicitudNc;
use App\Models\Operations\SolicitudRepuesto;
use App\Models\Operations\Informe;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AsistenteIaService
{
    /**
     * Procesa la consulta del usuario, extrae el contexto seguro y llama a la API de IA.
     *
     * @param string $consulta
     * @param array $contextoSesion
     * @return string
     * @throws Exception
     */
    public function responderConsulta(string $consulta, array $contextoSesion): string
    {
        $provider = config('services.ai.provider');
        $config = config("services.ai.{$provider}");

        if (empty($config) || empty($config['key'])) {
            throw new Exception('El servicio de asistencia por IA no se encuentra disponible actualmente (clave API no configurada).');
        }

        // 1. Extraer el contexto dinámico y seguro de la base de datos
        $datosContexto = $this->extraerDatosContexto($consulta, $contextoSesion);

        // 2. Construir Prompt del Sistema con el contexto de base de datos
        $promptSystem = "Eres un asistente de inteligencia artificial experto para SGN-Novitec (Sistema de Gestión Novitec).\n"
            . "Tu objetivo es ayudar a los técnicos y administradores a consultar el estado operativo, órdenes, informes y solicitudes de notas de crédito.\n\n"
            . "DATOS EN TIEMPO REAL DEL SISTEMA PARA ESTA CONSULTA:\n"
            . "--------------------------------------------------\n"
            . "Usuario actual: {$contextoSesion['nombre']} (Rol: " . ($contextoSesion['es_admin'] ? 'Administrador/Master' : 'Técnico') . ")\n"
            . "Sucursal ID: {$contextoSesion['sucursal_id']}\n"
            . "Contexto extraído:\n" . json_encode($datosContexto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
            . "--------------------------------------------------\n\n"
            . "DIRECTRICES DE RESPUESTA:\n"
            . "- Responde de forma clara, concisa, profesional y en español.\n"
            . "- Si el usuario pregunta por una orden o solicitud específica y los datos de arriba no la contienen o no se encuentra, indícalo de forma amable.\n"
            . "- Usa formato Markdown para que la respuesta sea visualmente atractiva (listas, negritas, tablas de texto si es necesario).\n"
            . "- No menciones que recibiste un JSON o variables internas de base de datos. Responde con total naturalidad.";

        $body = [
            'model' => $config['model'],
            'messages' => [
                ['role' => 'system', 'content' => $promptSystem],
                ['role' => 'user', 'content' => $consulta],
            ],
            'temperature' => 0.4,
        ];

        try {
            $response = Http::withToken($config['key'])
                ->timeout(25)
                ->post($config['url'], $body);

            if ($response->failed()) {
                Log::error('Fallo en la API de IA.', [
                    'proveedor' => $provider,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('No se pudo procesar la solicitud con el motor de inteligencia artificial.');
            }

            $resultado = $response->json();
            return $resultado['choices'][0]['message']['content'] ?? 'No se pudo obtener una respuesta del asistente en este momento.';
        } catch (Exception $e) {
            Log::error('Error en AsistenteIaService: ' . $e->getMessage());
            throw new Exception($e->getMessage() ?: 'Ocurrió un error al procesar tu pregunta con la inteligencia artificial.');
        }
    }

    /**
     * Extrae de forma segura registros relevantes de la base de datos para alimentar el contexto.
     */
    private function extraerDatosContexto(string $consulta, array $contexto): array
    {
        $datos = [];
        $tecnicoId = $contexto['tecnico_id'];
        $sucursalId = $contexto['sucursal_id'];
        $esAdmin = $contexto['es_admin'];

        // Buscar códigos de órdenes (ej. UIO-000001, GYE-000025, etc.)
        preg_match_all('/[a-zA-Z]{3}-\d+/', $consulta, $matches);
        $ordenesMencionadas = array_unique(array_map('strtoupper', $matches[0] ?? []));

        if (!empty($ordenesMencionadas)) {
            $datos['ordenes_consultadas'] = [];
            foreach ($ordenesMencionadas as $nroOrden) {
                $query = Orden::with(['cliente', 'equipo', 'solicitudesNc', 'solicitudesRepuesto', 'informes'])
                    ->where('nro_orden', $nroOrden);

                // Si no es admin, limitar a su sucursal o su tecnico_id por seguridad
                if (!$esAdmin) {
                    $query->where(function ($q) use ($tecnicoId, $sucursalId) {
                        $q->where('tecnico_id', $tecnicoId)
                          ->orWhere('sucursal_id', $sucursalId);
                    });
                }

                $orden = $query->first();
                if ($orden) {
                    $datos['ordenes_consultadas'][] = [
                        'nro_orden' => $orden->nro_orden,
                        'cliente' => ($orden->cliente->nombres ?? '—') . ' ' . ($orden->cliente->apellidos ?? ''),
                        'equipo' => ($orden->equipo->tipo ?? '—') . ' ' . ($orden->equipo->marca ?? '') . ' ' . ($orden->equipo->modelo ?? ''),
                        'estado_orden' => $orden->estado_orden,
                        'estado_repuesto' => $orden->estado_repuesto,
                        'estado_garantia' => $orden->estado_garantia,
                        'fecha_ingreso' => $orden->fecha_de_ingreso,
                        'falla_reportada' => $orden->equipo->falla ?? '—',
                        'informes' => $orden->informes->map(fn($inf) => [
                            'estado_equipo' => $inf->estado_equipo,
                            'conclusion' => $inf->conclusion,
                            'fecha' => $inf->fecha_informe
                        ]),
                        'notas_credito' => $orden->solicitudesNc->map(fn($nc) => [
                            'nro_solicitud' => $nc->nro_solicitud,
                            'asunto' => $nc->asunto,
                            'estado' => $nc->estado,
                            'motivo_rechazo' => $nc->motivo_rechazo
                        ]),
                        'solicitudes_repuestos' => $orden->solicitudesRepuesto->map(fn($rep) => [
                            'nro_solicitud' => $rep->nro_solicitud,
                            'repuesto_nombre' => $rep->repuesto_nombre ?? '—',
                            'estado' => $rep->estado
                        ])
                    ];
                }
            }
        }

        // 1. Consultar órdenes por Técnico (estadísticas de rendimiento)
        if (preg_match('/tecnico|tecnicos|rendimiento|asignadas/i', $consulta)) {
            $query = Orden::join('usuarios', 'ordenes.tecnico_id', '=', 'usuarios.id')
                ->select('usuarios.nombre_tecnico as tecnico_nombre')
                ->selectRaw('count(ordenes.id) as total')
                ->selectRaw('sum(case when ordenes.estado_orden in ("Entregada", "Finalizada", "Entregado", "Finalizado") then 1 else 0 end) as finalizadas')
                ->selectRaw('sum(case when ordenes.estado_orden not in ("Entregada", "Finalizada", "Entregado", "Finalizado") then 1 else 0 end) as pendientes')
                ->whereNotNull('usuarios.nombre_tecnico')
                ->where('usuarios.nombre_tecnico', '!=', '');

            if ($sucursalId > 0 && $esAdmin) {
                $query->where('ordenes.sucursal_id', $sucursalId);
            }

            $datos['estadisticas_ordenes_por_tecnico'] = $query->groupBy('usuarios.nombre_tecnico')->get();
        }

        // 2. Consultar órdenes por Fecha (hoy, meses o rangos)
        if (preg_match('/fecha|hoy|mes|semana|dia|ingreso/i', $consulta)) {
            $hoy = date('Y-m-d');
            $queryHoy = Orden::with(['cliente', 'equipo'])->whereDate('fecha_de_ingreso', $hoy);
            
            if (!$esAdmin) {
                $queryHoy->where('tecnico_id', $tecnicoId);
            } else if ($sucursalId > 0) {
                $queryHoy->where('sucursal_id', $sucursalId);
            }
            
            $ordenesHoy = $queryHoy->get();
            $datos['ordenes_ingresadas_hoy'] = $ordenesHoy->map(fn($o) => [
                'nro_orden' => $o->nro_orden,
                'cliente' => ($o->cliente->nombres ?? '—') . ' ' . ($o->cliente->apellidos ?? ''),
                'equipo' => ($o->equipo->tipo ?? '—') . ' ' . ($o->equipo->marca ?? ''),
                'estado_orden' => $o->estado_orden
            ]);
            $datos['total_ordenes_ingresadas_hoy'] = $ordenesHoy->count();

            // Histórico últimos 6 meses
            $queryMeses = Orden::selectRaw('DATE_FORMAT(fecha_de_ingreso, "%Y-%m") as mes')
                ->selectRaw('count(*) as total');
            if ($sucursalId > 0 && $esAdmin) {
                $queryMeses->where('sucursal_id', $sucursalId);
            }
            $datos['ordenes_por_mes_ultimos_6_meses'] = $queryMeses->groupBy('mes')
                ->orderBy('mes', 'desc')
                ->take(6)
                ->get();
        }

        // 3. Consultar órdenes por Estado
        if (preg_match('/estado|estados|cuantas/i', $consulta)) {
            $query = Orden::select('estado_orden')->selectRaw('count(*) as total');
            if (!$esAdmin) {
                $query->where('tecnico_id', $tecnicoId);
            } else if ($sucursalId > 0) {
                $query->where('sucursal_id', $sucursalId);
            }
            $datos['ordenes_por_estado'] = $query->groupBy('estado_orden')->get();
        }

        // 4. Consultar órdenes por Empresa
        if (preg_match('/empresa|empresas|juridico/i', $consulta)) {
            $query = Orden::with(['cliente', 'equipo'])->where('tipo_orden', 'empresa');
            if ($sucursalId > 0 && $esAdmin) {
                $query->where('sucursal_id', $sucursalId);
            }
            $ordenesEmpresa = $query->orderBy('fecha_de_ingreso', 'desc')->take(10)->get();
            $datos['ordenes_empresa_recientes'] = $ordenesEmpresa->map(fn($o) => [
                'nro_orden' => $o->nro_orden,
                'empresa' => ($o->cliente->nombres ?? '—') . ' ' . ($o->cliente->apellidos ?? ''),
                'equipo' => ($o->equipo->tipo ?? '—') . ' ' . ($o->equipo->marca ?? ''),
                'estado_orden' => $o->estado_orden
            ]);
            $datos['total_ordenes_empresa'] = Orden::where('tipo_orden', 'empresa')->count();
        }

        // 5. Consultar órdenes por Cliente específico
        if (preg_match('/cliente|clientes|usuario/i', $consulta)) {
            // Intentar detectar si menciona un nombre de cliente (ej: "cliente Juan")
            preg_match('/cliente\s+([a-zA-Z\s]+)/i', $consulta, $clientMatches);
            $nombreBuscado = trim($clientMatches[1] ?? '');

            $query = Orden::with(['cliente', 'equipo']);
            if (!empty($nombreBuscado) && strlen($nombreBuscado) > 2) {
                $query->whereHas('cliente', function ($q) use ($nombreBuscado) {
                    $q->where('nombres', 'like', "%{$nombreBuscado}%")
                      ->orWhere('apellidos', 'like', "%{$nombreBuscado}%");
                });
                $datos['cliente_buscado'] = $nombreBuscado;
            }

            if (!$esAdmin) {
                $query->where('tecnico_id', $tecnicoId);
            } else if ($sucursalId > 0) {
                $query->where('sucursal_id', $sucursalId);
            }

            $ordenesCliente = $query->orderBy('fecha_de_ingreso', 'desc')->take(10)->get();
            $datos['ordenes_por_cliente_recientes'] = $ordenesCliente->map(fn($o) => [
                'nro_orden' => $o->nro_orden,
                'cliente' => ($o->cliente->nombres ?? '—') . ' ' . ($o->cliente->apellidos ?? ''),
                'equipo' => ($o->equipo->tipo ?? '—') . ' ' . ($o->equipo->marca ?? ''),
                'estado_orden' => $o->estado_orden,
                'fecha_ingreso' => $o->fecha_de_ingreso
            ]);
        }

        // 6. Consultar órdenes pendientes / en proceso si se detectan palabras afines
        if (preg_match('/pendiente|proceso|activa|tengo|orden|ot/i', $consulta) && !isset($datos['resumen_ordenes_activas'])) {
            $query = Orden::with(['cliente', 'equipo'])
                ->whereNotIn('estado_orden', ['Entregada', 'Finalizada', 'Entregado', 'Finalizado'])
                ->orderBy('fecha_de_ingreso', 'desc');

            if (!$esAdmin) {
                $query->where('tecnico_id', $tecnicoId);
            } else {
                if ($sucursalId > 0) {
                    $query->where('sucursal_id', $sucursalId);
                }
            }

            $ordenesActivas = $query->take(15)->get();
            $datos['resumen_ordenes_activas'] = $ordenesActivas->map(fn($o) => [
                'nro_orden' => $o->nro_orden,
                'cliente' => ($o->cliente->nombres ?? '—') . ' ' . ($o->cliente->apellidos ?? ''),
                'equipo' => ($o->equipo->tipo ?? '—') . ' ' . ($o->equipo->marca ?? ''),
                'estado_orden' => $o->estado_orden,
                'fecha_ingreso' => $o->fecha_de_ingreso
            ]);
            $datos['total_ordenes_activas_mostradas'] = $ordenesActivas->count();
        }

        // 7. Consultar Notas de Crédito si se detectan palabras afines
        if (preg_match('/nc|nota|credito|solicitud nc/i', $consulta) && !isset($datos['notas_credito_recientes'])) {
            $query = SolicitudNc::with('orden');
            if (!$esAdmin) {
                $query->where('tecnico_id', $tecnicoId);
            } else {
                if ($sucursalId > 0) {
                    $query->whereHas('orden', fn($q) => $q->where('sucursal_id', $sucursalId));
                }
            }
            $recentNc = $query->orderBy('creado_en', 'desc')->take(10)->get();
            $datos['notas_credito_recientes'] = $recentNc->map(fn($nc) => [
                'nro_solicitud' => $nc->nro_solicitud,
                'nro_orden' => $nc->orden->nro_orden ?? '—',
                'asunto' => $nc->asunto,
                'estado' => $nc->estado,
                'motivo_rechazo' => $nc->motivo_rechazo
            ]);
        }

        // 8. Consultar solicitudes de repuesto si se detectan palabras afines
        if (preg_match('/repuesto|bodega|solicitud repuesto/i', $consulta) && !isset($datos['solicitudes_repuestos_recientes'])) {
            $query = SolicitudRepuesto::with('orden');
            if (!$esAdmin) {
                $query->where('tecnico_id', $tecnicoId);
            } else {
                if ($sucursalId > 0) {
                    $query->whereHas('orden', fn($q) => $q->where('sucursal_id', $sucursalId));
                }
            }
            $recentRep = $query->orderBy('fecha_solicitud', 'desc')->take(10)->get();
            $datos['solicitudes_repuestos_recientes'] = $recentRep->map(fn($rep) => [
                'nro_solicitud' => $rep->nro_solicitud,
                'nro_orden' => $rep->orden->nro_orden ?? '—',
                'repuesto_nombre' => $rep->repuesto_nombre ?? '—',
                'cantidad' => $rep->cantidad ?? 1,
                'estado' => $rep->estado,
                'motivo_rechazo' => $rep->motivo_rechazo
            ]);
        }

        // 9. Consultar informes recientes si se detectan palabras afines
        if (preg_match('/informe|reporte|borrador/i', $consulta) && !isset($datos['informes_recientes'])) {
            $query = Informe::with(['orden', 'orden.cliente']);
            if (!$esAdmin) {
                $query->where('tecnico_id', $tecnicoId);
            } else {
                if ($sucursalId > 0) {
                    $query->whereHas('orden', fn($q) => $q->where('sucursal_id', $sucursalId));
                }
            }
            $recentReports = $query->orderBy('fecha_informe', 'desc')->take(10)->get();
            $datos['informes_recientes'] = $recentReports->map(fn($inf) => [
                'nro_orden' => $inf->orden->nro_orden ?? '—',
                'cliente' => ($inf->orden->cliente->nombres ?? '—') . ' ' . ($inf->orden->cliente->apellidos ?? ''),
                'estado_equipo' => $inf->estado_equipo,
                'fecha_informe' => $inf->fecha_informe
            ]);
        }

        return $datos;
    }
}
