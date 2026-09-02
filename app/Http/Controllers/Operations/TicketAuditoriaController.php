<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\Usuario;
use App\Models\Operations\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAuditoriaController extends Controller
{
    /**
     * Muestra el panel ejecutivo de auditoría y reportería de tickets.
     */
    public function index(Request $request)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return redirect()->route('login');
        }

        // 1. Filtros
        $fechaDesde = $request->input('fecha_desde', Carbon::now()->subDays(30)->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->format('Y-m-d'));
        $estado = $request->input('estado');
        $tipoTicket = $request->input('tipo_ticket');
        $empresaOrigen = $request->input('empresa_origen');
        $asignadoAId = $request->input('asignado_a_id');
        $sucursalClienteId = $request->input('sucursal_cliente_id');
        $prioridad = $request->input('prioridad');
        $categoria = $request->input('categoria');
        $q = trim($request->input('q', ''));

        // 2. Base Query con rango de fechas
        $baseQuery = Ticket::query();
        if ($fechaDesde) {
            $baseQuery->whereDate('fecha_apertura', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $baseQuery->whereDate('fecha_apertura', '<=', $fechaHasta);
        }

        // Clones para KPIs y Gráficos globales del periodo
        $totalPeriodo = (clone $baseQuery)->count();
        $resueltosPeriodo = (clone $baseQuery)->whereIn('estado', ['resuelto', 'cerrado'])->count();
        $abiertosPeriodo = (clone $baseQuery)->whereIn('estado', ['abierto', 'en_proceso', 'en_espera'])->count();
        $enMbaPeriodo = (clone $baseQuery)->where('estado', 'en_mba')->count();
        $totalMbaPeriodo = (clone $baseQuery)->where(function ($q) {
            $q->where('estado', 'en_mba')
              ->orWhereNotNull('numero_ticket_mba')
              ->orWhere('categoria', 'Casos MBA3')
              ->orWhere('categoria', 'LIKE', '%MBA%');
        })->count();
        $canceladosPeriodo = (clone $baseQuery)->where('estado', 'cancelado')->count();
        $tasaResolucion = $totalPeriodo > 0 ? round(($resueltosPeriodo / $totalPeriodo) * 100, 1) : 0;

        // Calificación promedio
        $califPromedio = (clone $baseQuery)->whereNotNull('calificacion')->avg('calificacion');
        $califPromedio = $califPromedio ? round($califPromedio, 1) : null;
        $totalCalificados = (clone $baseQuery)->whereNotNull('calificacion')->count();

        // Tiempo promedio de resolución (en horas)
        $ticketsConResolucion = (clone $baseQuery)
            ->whereNotNull('fecha_resolucion')
            ->whereNotNull('fecha_apertura')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, fecha_apertura, fecha_resolucion)) as avg_minutos'))
            ->first();

        $avgMinutosResolucion = $ticketsConResolucion ? round($ticketsConResolucion->avg_minutos ?? 0) : 0;
        $tiempoPromedioResolucion = $this->formatearMinutos($avgMinutosResolucion);

        // Desglose por Tipo (Soporte vs Sistemas)
        $desgloseTipo = (clone $baseQuery)
            ->select('tipo_ticket', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_ticket')
            ->pluck('total', 'tipo_ticket')
            ->toArray();

        // Desglose por Empresa
        $desgloseEmpresa = (clone $baseQuery)
            ->select('empresa_origen', DB::raw('COUNT(*) as total'))
            ->groupBy('empresa_origen')
            ->pluck('total', 'empresa_origen')
            ->toArray();

        // Top 5 Categorías
        $topCategorias = (clone $baseQuery)
            ->select('categoria', DB::raw('COUNT(*) as total'))
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        // Rendimiento por Técnico
        $rendimientoTecnicos = (clone $baseQuery)
            ->whereNotNull('asignado_a_id')
            ->select(
                'asignado_a_id',
                DB::raw('COUNT(*) as total_asignados'),
                DB::raw('SUM(CASE WHEN estado IN ("resuelto", "cerrado") THEN 1 ELSE 0 END) as total_resueltos'),
                DB::raw('AVG(calificacion) as promedio_calificacion'),
                DB::raw('AVG(CASE WHEN fecha_resolucion IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, fecha_apertura, fecha_resolucion) ELSE NULL END) as avg_min_resolucion')
            )
            ->groupBy('asignado_a_id')
            ->with('asignadoA')
            ->get();

        // 3. Aplicar filtros detallados para la tabla
        $tableQuery = clone $baseQuery;

        if ($estado) {
            $tableQuery->where('estado', $estado);
        }
        if ($tipoTicket) {
            $tableQuery->where('tipo_ticket', $tipoTicket);
        }
        if ($empresaOrigen) {
            $tableQuery->where('empresa_origen', $empresaOrigen);
        }
        if ($asignadoAId) {
            $tableQuery->where('asignado_a_id', $asignadoAId);
        }
        if ($sucursalClienteId) {
            $tableQuery->where('sucursal_cliente_id', $sucursalClienteId);
        }
        if ($prioridad) {
            $tableQuery->where('prioridad', $prioridad);
        }
        if ($categoria) {
            $tableQuery->where('categoria', $categoria);
        }
        if ($q !== '') {
            $tableQuery->where(function ($sub) use ($q) {
                $sub->where('codigo_ticket', 'LIKE', "%{$q}%")
                    ->orWhere('numero_ticket_mba', 'LIKE', "%{$q}%")
                    ->orWhere('titulo', 'LIKE', "%{$q}%")
                    ->orWhere('descripcion', 'LIKE', "%{$q}%")
                    ->orWhere('solucion', 'LIKE', "%{$q}%")
                    ->orWhere('tienda_nombre', 'LIKE', "%{$q}%")
                    ->orWhereHas('solicitante', function ($u) use ($q) {
                        $u->where('nombre_tecnico', 'LIKE', "%{$q}%")
                          ->orWhere('usuario', 'LIKE', "%{$q}%");
                    });
            });
        }

        $tickets = $tableQuery->with(['solicitante', 'asignadoA', 'sucursalCliente', 'adjuntos'])
            ->orderByDesc('fecha_apertura')
            ->paginate(25)
            ->withQueryString();

        // Listas auxiliares para selectores de filtro
        $tecnicos = Usuario::tecnicosOperativos()
            ->orderBy('nombre_tecnico')
            ->get();

        $tiendas = SucursalCliente::where('activa', 1)
            ->orderBy('nombre')
            ->get();

        $kpis = [
            'total' => $totalPeriodo,
            'resueltos' => $resueltosPeriodo,
            'abiertos' => $abiertosPeriodo,
            'en_mba' => $enMbaPeriodo,
            'total_mba' => $totalMbaPeriodo,
            'cancelados' => $canceladosPeriodo,
            'tasa_resolucion' => $tasaResolucion,
            'calif_promedio' => $califPromedio,
            'total_calificados' => $totalCalificados,
            'tiempo_promedio_resolucion' => $tiempoPromedioResolucion,
            'desglose_tipo' => $desgloseTipo,
            'desglose_empresa' => $desgloseEmpresa,
            'top_categorias' => $topCategorias,
            'rendimiento_tecnicos' => $rendimientoTecnicos,
        ];

        return view('tickets.auditoria', compact(
            'tickets',
            'kpis',
            'tecnicos',
            'tiendas',
            'fechaDesde',
            'fechaHasta',
            'estado',
            'tipoTicket',
            'empresaOrigen',
            'asignadoAId',
            'sucursalClienteId',
            'prioridad',
            'categoria',
            'q'
        ));
    }

    /**
     * Exporta los tickets filtrados a Excel / CSV descargable con codificación UTF-8 BOM.
     */
    public function exportarExcel(Request $request): StreamedResponse
    {
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $estado = $request->input('estado');
        $tipoTicket = $request->input('tipo_ticket');
        $empresaOrigen = $request->input('empresa_origen');
        $asignadoAId = $request->input('asignado_a_id');
        $sucursalClienteId = $request->input('sucursal_cliente_id');
        $prioridad = $request->input('prioridad');
        $categoria = $request->input('categoria');
        $q = trim($request->input('q', ''));

        $query = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente']);

        if ($fechaDesde) {
            $query->whereDate('fecha_apertura', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('fecha_apertura', '<=', $fechaHasta);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }
        if ($tipoTicket) {
            $query->where('tipo_ticket', $tipoTicket);
        }
        if ($empresaOrigen) {
            $query->where('empresa_origen', $empresaOrigen);
        }
        if ($asignadoAId) {
            $query->where('asignado_a_id', $asignadoAId);
        }
        if ($sucursalClienteId) {
            $query->where('sucursal_cliente_id', $sucursalClienteId);
        }
        if ($prioridad) {
            $query->where('prioridad', $prioridad);
        }
        if ($categoria) {
            $query->where('categoria', $categoria);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo_ticket', 'LIKE', "%{$q}%")
                    ->orWhere('numero_ticket_mba', 'LIKE', "%{$q}%")
                    ->orWhere('titulo', 'LIKE', "%{$q}%")
                    ->orWhere('descripcion', 'LIKE', "%{$q}%")
                    ->orWhere('solucion', 'LIKE', "%{$q}%")
                    ->orWhere('tienda_nombre', 'LIKE', "%{$q}%");
            });
        }

        $tickets = $query->orderByDesc('fecha_apertura')->get();

        $filename = 'Reporte_Auditoria_Tickets_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM para soporte correcto de caracteres especiales en Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($handle, [
                'CÓDIGO TICKET',
                'TIPO',
                'CATEGORÍA',
                'PRIORIDAD',
                'ESTADO',
                'N° TICKET MBA',
                'FECHA ESCALADO MBA (48H)',
                'EMPRESA',
                'TIENDA / SUCURSAL',
                'SOLICITANTE',
                'TELÉFONO CONTACTO',
                'TÉCNICO / ASIGNADO',
                'FECHA APERTURA',
                'FECHA ASIGNACIÓN',
                'FECHA 1RA RESPUESTA',
                'FECHA RESOLUCIÓN',
                'TIEMPO RESOLUCIÓN (HORAS)',
                'CALIFICACIÓN (1-5)',
                'COMENTARIO CALIFICACIÓN',
                'TÍTULO',
                'DESCRIPCIÓN',
                'SOLUCIÓN REGISTRADA',
            ], ';');

            foreach ($tickets as $t) {
                $tiempoHoras = '';
                if ($t->fecha_apertura && $t->fecha_resolucion) {
                    $mins = $t->fecha_apertura->diffInMinutes($t->fecha_resolucion);
                    $tiempoHoras = round($mins / 60, 2);
                }

                $tipoNombre = $t->tipo_ticket === 'soporte_tecnico' ? 'Soporte Técnico' : 'Sistemas / TI';
                $solicitanteNombre = $t->solicitante ? ($t->solicitante->nombre_tecnico ?: $t->solicitante->usuario) : '—';
                $asignadoNombre = $t->asignadoA ? ($t->asignadoA->nombre_tecnico ?: $t->asignadoA->usuario) : 'Sin asignar';
                $tiendaNombre = $t->tienda_nombre ?: ($t->sucursalCliente ? ($t->sucursalCliente->codigo . ' - ' . $t->sucursalCliente->nombre) : '—');

                fputcsv($handle, [
                    $t->codigo_ticket,
                    $tipoNombre,
                    $t->categoria,
                    strtoupper($t->prioridad),
                    strtoupper(str_replace('_', ' ', $t->estado)),
                    $t->numero_ticket_mba ?: '—',
                    $t->fecha_escalado_mba ? $t->fecha_escalado_mba->format('Y-m-d H:i:s') : '—',
                    $t->empresa_origen,
                    $tiendaNombre,
                    $solicitanteNombre,
                    $t->contacto_telefono ?: '—',
                    $asignadoNombre,
                    $t->fecha_apertura ? $t->fecha_apertura->format('Y-m-d H:i:s') : '',
                    $t->fecha_asignacion ? $t->fecha_asignacion->format('Y-m-d H:i:s') : '',
                    $t->fecha_primera_respuesta ? $t->fecha_primera_respuesta->format('Y-m-d H:i:s') : '',
                    $t->fecha_resolucion ? $t->fecha_resolucion->format('Y-m-d H:i:s') : '',
                    $tiempoHoras,
                    $t->calificacion ?: '',
                    $t->comentario_calificacion ?: '',
                    $t->titulo,
                    str_replace(["\r", "\n"], ' ', $t->descripcion),
                    str_replace(["\r", "\n"], ' ', $t->solucion ?: ''),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Retorna el dataset completo de auditoría en JSON para generación de Excel Enterprise con ExcelJS.
     */
    public function dataExcel(Request $request)
    {
        $usuario = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuario) {
            return response()->json(['ok' => false, 'error' => 'No autenticado.'], 401);
        }

        try {
            $fechaInicio = $request->input('fecha_desde', $request->input('fecha_inicio', Carbon::now()->subDays(30)->toDateString()));
            $fechaFin = $request->input('fecha_hasta', $request->input('fecha_fin', Carbon::now()->toDateString()));
        $estado = $request->input('estado');
        $tipoTicket = $request->input('tipo_ticket');
        $empresaOrigen = $request->input('empresa_origen');
        $asignadoAId = $request->input('asignado_a_id');
        $sucursalClienteId = $request->input('sucursal_cliente_id');
        $prioridad = $request->input('prioridad');
        $categoria = $request->input('categoria');
        $q = trim((string) $request->input('q', ''));

        $query = Ticket::whereBetween('fecha_apertura', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay(),
        ]);

        if ($estado) $query->where('estado', $estado);
        if ($tipoTicket) $query->where('tipo_ticket', $tipoTicket);
        if ($empresaOrigen) $query->where('empresa_origen', $empresaOrigen);
        if ($asignadoAId) $query->where('asignado_a_id', $asignadoAId);
        if ($sucursalClienteId) $query->where('sucursal_cliente_id', $sucursalClienteId);
        if ($prioridad) $query->where('prioridad', $prioridad);
        if ($categoria) $query->where('categoria', $categoria);
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo_ticket', 'LIKE', "%{$q}%")
                    ->orWhere('numero_ticket_mba', 'LIKE', "%{$q}%")
                    ->orWhere('titulo', 'LIKE', "%{$q}%")
                    ->orWhere('descripcion', 'LIKE', "%{$q}%")
                    ->orWhere('solucion', 'LIKE', "%{$q}%")
                    ->orWhere('tienda_nombre', 'LIKE', "%{$q}%");
            });
        }

        $tickets = $query->with(['solicitante.sucursalCliente', 'asignadoA', 'sucursalCliente'])
            ->orderByDesc('fecha_apertura')
            ->get();

        $rows = $tickets->map(function($t) {
            $solicitanteNombre = $t->solicitante ? ($t->solicitante->nombre_tecnico ?: $t->solicitante->usuario) : ($t->solicitante_nombre ?: 'Solicitante');
            $asignadoNombre = $t->asignadoA ? ($t->asignadoA->nombre_tecnico ?: $t->asignadoA->usuario) : 'Sin Asignar';
            $tiendaNombre = $t->tienda_nombre ?: ($t->sucursalCliente ? ($t->sucursalCliente->codigo . ' - ' . $t->sucursalCliente->nombre) : 'Tienda Externa');
            
            $tiempoHoras = null;
            $tiempoFormateado = '—';
            if ($t->fecha_apertura && $t->fecha_resolucion) {
                $mins = $t->fecha_apertura->diffInMinutes($t->fecha_resolucion);
                $tiempoHoras = round($mins / 60, 2);
                $tiempoFormateado = $this->formatearMinutos($mins);
            }

            // Extraer AnyDesk de la descripción si existe
            $anydesk = '';
            if (preg_match('/(?:anydesk|any\s*desk|id\s*anydesk)[:\s\-]*([0-9\s]{9,12})/i', $t->descripcion ?? '', $m)) {
                $anydesk = trim($m[1]);
            }

            return [
                'id' => $t->id,
                'codigo_ticket' => $t->codigo_ticket,
                'tipo_ticket' => $t->tipo_ticket === 'sistemas' ? 'Sistemas / TI' : 'Soporte Técnico',
                'categoria' => $t->categoria,
                'prioridad' => strtoupper($t->prioridad),
                'estado' => $t->estado,
                'estado_label' => strtoupper(str_replace('_', ' ', $t->estado)),
                'numero_ticket_mba' => $t->numero_ticket_mba ?: '—',
                'fecha_escalado_mba' => $t->fecha_escalado_mba ? $t->fecha_escalado_mba->format('Y-m-d H:i') : '—',
                'empresa_origen' => $t->empresa_origen,
                'tienda_nombre' => $tiendaNombre,
                'solicitante_nombre' => $solicitanteNombre,
                'contacto_telefono' => $t->contacto_telefono ?: ($t->solicitante?->telefono ?: '—'),
                'anydesk' => $anydesk ?: '—',
                'asignado_nombre' => $asignadoNombre,
                'asignado_id' => $t->asignado_a_id,
                'fecha_apertura' => $t->fecha_apertura ? $t->fecha_apertura->format('Y-m-d H:i') : '',
                'fecha_asignacion' => $t->fecha_asignacion ? $t->fecha_asignacion->format('Y-m-d H:i') : '',
                'fecha_primera_respuesta' => $t->fecha_primera_respuesta ? $t->fecha_primera_respuesta->format('Y-m-d H:i') : '',
                'fecha_resolucion' => $t->fecha_resolucion ? $t->fecha_resolucion->format('Y-m-d H:i') : '',
                'tiempo_resolucion_horas' => $tiempoHoras,
                'tiempo_resolucion_formateado' => $tiempoFormateado,
                'calificacion' => $t->calificacion,
                'comentario_calificacion' => $t->comentario_calificacion ?: '',
                'titulo' => $t->titulo,
                'descripcion' => $t->descripcion,
                'solucion' => $t->solucion_texto ?: ($t->solucion ?: ''),
                'pdf_url' => route('tickets.imprimir', $t->id),
            ];
        });

        return response()->json([
            'ok' => true,
            'total' => $rows->count(),
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
                'tipo_ticket' => $tipoTicket,
                'empresa_origen' => $empresaOrigen,
                'prioridad' => $prioridad,
                'categoria' => $categoria,
            ],
            'tickets' => $rows
        ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error al procesar datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Retorna el detalle completo de un ticket en formato JSON para el modal de auditoría rápida.
     */
    public function detalleModal(int $id)
    {
        try {
            $ticket = Ticket::with(['solicitante', 'asignadoA', 'sucursalCliente', 'adjuntos', 'mensajes.usuario'])
                ->find($id);

            if (!$ticket) {
                return response()->json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
            }

            $solicitanteNombre = $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : ($ticket->solicitante_id ? 'ID ' . $ticket->solicitante_id : 'Solicitante');
            $asignadoNombre = $ticket->asignadoA ? ($ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario) : ($ticket->asignado_a_id ? 'ID ' . $ticket->asignado_a_id : 'Sin asignar');
            $tiendaNombre = $ticket->tienda_nombre ?: ($ticket->sucursalCliente ? ($ticket->sucursalCliente->codigo . ' - ' . $ticket->sucursalCliente->nombre) : '—');
            $solucionTexto = $ticket->solucion ?: $ticket->solucion_texto;

            return response()->json([
                'ok' => true,
                'ticket' => $ticket,
                'solicitante_nombre' => $solicitanteNombre,
                'asignado_nombre' => $asignadoNombre,
                'tienda_nombre' => $tiendaNombre,
                'solucion_texto' => $solucionTexto,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatearMinutos(int $minutos): string
    {
        if ($minutos <= 0) {
            return '—';
        }
        if ($minutos < 60) {
            return "{$minutos} min";
        }
        $horas = floor($minutos / 60);
        $restoMin = $minutos % 60;
        if ($horas < 24) {
            return "{$horas}h {$restoMin}m";
        }
        $dias = floor($horas / 24);
        $restoHoras = $horas % 24;
        return "{$dias}d {$restoHoras}h";
    }
}
