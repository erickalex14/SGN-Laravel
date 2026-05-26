<?php

namespace App\Repositories\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function obtenerDatosTecnico(int $tecnicoId): array
    {
        $totalAsignadas = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes WHERE tecnico_id = ?
                UNION ALL
                SELECT id FROM ordenesempresas WHERE tecnico_id = ?
            ) t",
            [$tecnicoId, $tecnicoId]
        );

        $pendientes = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes WHERE tecnico_id = ? AND estado_orden = 'Pendiente'
                UNION ALL
                SELECT id FROM ordenesempresas WHERE tecnico_id = ? AND estado = 'Pendiente'
            ) t",
            [$tecnicoId, $tecnicoId]
        );

        $enProceso = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes WHERE tecnico_id = ? AND estado_orden = 'En proceso'
                UNION ALL
                SELECT id FROM ordenesempresas WHERE tecnico_id = ? AND estado = 'En proceso'
            ) t",
            [$tecnicoId, $tecnicoId]
        );

        $finalizadas = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes WHERE tecnico_id = ? AND estado_orden = 'Finalizada'
                UNION ALL
                SELECT id FROM ordenesempresas WHERE tecnico_id = ? AND estado = 'Finalizada'
            ) t",
            [$tecnicoId, $tecnicoId]
        );

        $entregadas = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes WHERE tecnico_id = ? AND estado_orden = 'Entregada'
                UNION ALL
                SELECT id FROM ordenesempresas WHERE tecnico_id = ? AND estado = 'Entregada'
            ) t",
            [$tecnicoId, $tecnicoId]
        );

        $hoy = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes WHERE tecnico_id = ? AND DATE(fecha_de_ingreso) = CURDATE()
                UNION ALL
                SELECT id FROM ordenesempresas WHERE tecnico_id = ? AND DATE(fecha_ingreso) = CURDATE()
            ) t",
            [$tecnicoId, $tecnicoId]
        );

        $tasa = $totalAsignadas > 0 ? (int) round(($entregadas / $totalAsignadas) * 100) : 0;

        $rowsDias = DB::select(
            "SELECT dia, SUM(total) AS total FROM (
                SELECT DATE(fecha_de_ingreso) AS dia, COUNT(*) AS total
                FROM ordenes
                WHERE tecnico_id = ? AND fecha_de_ingreso >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(fecha_de_ingreso)
                UNION ALL
                SELECT DATE(fecha_ingreso) AS dia, COUNT(*) AS total
                FROM ordenesempresas
                WHERE tecnico_id = ? AND fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(fecha_ingreso)
            ) t
            GROUP BY dia
            ORDER BY dia ASC",
            [$tecnicoId, $tecnicoId]
        );

        $diasMap = [];
        foreach ($rowsDias as $row) {
            $diasMap[(string) $row->dia] = (int) ($row->total ?? 0);
        }

        $diasLabels = [];
        $diasData = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now('America/Guayaquil')->subDays($i);
            $key = $fecha->format('Y-m-d');
            $diasLabels[] = $fecha->format('d/m');
            $diasData[] = $diasMap[$key] ?? 0;
        }

        $rowsEquipos = DB::select(
            "SELECT e.tipo, COUNT(*) AS total FROM (
                SELECT equipo_id FROM ordenes WHERE tecnico_id = ?
                UNION ALL
                SELECT equipo_id FROM ordenesempresas WHERE tecnico_id = ?
            ) t
            JOIN equipos e ON e.id = t.equipo_id
            GROUP BY e.tipo
            ORDER BY total DESC",
            [$tecnicoId, $tecnicoId]
        );

        $equiposLabels = [];
        $equiposData = [];
        foreach ($rowsEquipos as $row) {
            $equiposLabels[] = (string) ($row->tipo ?? 'Sin tipo');
            $equiposData[] = (int) ($row->total ?? 0);
        }

        $rowsMeses = DB::select(
            "SELECT mes_ord, SUM(total) AS total FROM (
                SELECT DATE_FORMAT(fecha_de_ingreso, '%Y-%m') AS mes_ord, COUNT(*) AS total
                FROM ordenes
                WHERE tecnico_id = ? AND fecha_de_ingreso >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                GROUP BY mes_ord
                UNION ALL
                SELECT DATE_FORMAT(fecha_ingreso, '%Y-%m') AS mes_ord, COUNT(*) AS total
                FROM ordenesempresas
                WHERE tecnico_id = ? AND fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                GROUP BY mes_ord
            ) t
            GROUP BY mes_ord
            ORDER BY mes_ord ASC",
            [$tecnicoId, $tecnicoId]
        );

        $mesLabels = [];
        $mesData = [];
        $mesesEs = [
            '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic',
        ];

        foreach ($rowsMeses as $row) {
            $mesOrd = (string) ($row->mes_ord ?? '');
            [$anio, $mes] = array_pad(explode('-', $mesOrd), 2, '');
            if ($anio !== '' && $mes !== '') {
                $mesLabels[] = ($mesesEs[$mes] ?? $mes) . ' ' . substr($anio, 2);
                $mesData[] = (int) ($row->total ?? 0);
            }
        }

        return [
            'modo' => 'tecnico',
            'kpis' => [
                'mis_ordenes' => $totalAsignadas,
                'pendientes' => $pendientes,
                'en_proceso' => $enProceso,
                'finalizadas' => $finalizadas,
                'entregadas' => $entregadas,
                'hoy' => $hoy,
                'tasa_resolucion' => $tasa,
            ],
            'charts' => [
                'dias' => ['labels' => $diasLabels, 'data' => $diasData],
                'equipos' => ['labels' => $equiposLabels, 'data' => $equiposData],
                'mensual' => ['labels' => $mesLabels, 'data' => $mesData],
            ],
        ];
    }

    public function obtenerDatosGestion(bool $esSuperadmin, int $sucursalId): array
    {
        $filtroPersonal = $esSuperadmin ? '1=1' : 'o.sucursal_id = ?';
        $filtroEmpresa = $esSuperadmin ? '1=1' : 'o.sucursal_id = ?';
        $filtroUsuario = $esSuperadmin ? '1=1' : 'u.sucursal_id = ?';
        $paramsSucursal = $esSuperadmin ? [] : [$sucursalId];

        $totalOrdenes = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes o WHERE {$filtroPersonal}
                UNION ALL
                SELECT id FROM ordenesempresas o WHERE {$filtroEmpresa}
            ) t",
            $esSuperadmin ? [] : [$sucursalId, $sucursalId]
        );

        $ordenesHoy = $this->scalar(
            "SELECT COUNT(*) FROM (
                SELECT id FROM ordenes o WHERE DATE(fecha_de_ingreso) = CURDATE() AND {$filtroPersonal}
                UNION ALL
                SELECT id FROM ordenesempresas o WHERE DATE(fecha_ingreso) = CURDATE() AND {$filtroEmpresa}
            ) t",
            $esSuperadmin ? [] : [$sucursalId, $sucursalId]
        );

        $totalTecnicos = $this->scalar(
            "SELECT COUNT(*) FROM usuarios u WHERE {$filtroUsuario}",
            $paramsSucursal
        );

        $totalClientes = $this->scalar("SELECT COUNT(*) FROM clientes");
        $totalSucursales = $esSuperadmin
            ? $this->scalar("SELECT COUNT(*) FROM sucursales")
            : 1;

        $rowsEstados = DB::select(
            "SELECT estado_ord, SUM(total) AS total FROM (
                SELECT estado_orden COLLATE utf8mb4_unicode_ci AS estado_ord, COUNT(*) AS total
                FROM ordenes o
                WHERE {$filtroPersonal}
                GROUP BY estado_orden
                UNION ALL
                SELECT estado COLLATE utf8mb4_unicode_ci AS estado_ord, COUNT(*) AS total
                FROM ordenesempresas o
                WHERE {$filtroEmpresa}
                GROUP BY estado
            ) t
            GROUP BY estado_ord
            ORDER BY total DESC",
            $esSuperadmin ? [] : [$sucursalId, $sucursalId]
        );

        $mapaColorEstados = [
            'Pendiente' => '#f59e0b',
            'En proceso' => '#3b82f6',
            'Finalizada' => '#8b5cf6',
            'Entregada' => '#10b981',
        ];
        $estadosLabels = [];
        $estadosData = [];
        $estadosColors = [];
        foreach ($rowsEstados as $row) {
            $estado = (string) ($row->estado_ord ?? 'Sin estado');
            $estadosLabels[] = $estado;
            $estadosData[] = (int) ($row->total ?? 0);
            $estadosColors[] = $mapaColorEstados[$estado] ?? '#94a3b8';
        }

        $rowsTecnicos = DB::select(
            "SELECT
                u.id,
                u.nombre_tecnico,
                COUNT(all_o.orden_id) AS total,
                SUM(CASE WHEN all_o.estado = 'Entregada' THEN 1 ELSE 0 END) AS entregadas,
                SUM(CASE WHEN all_o.estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes
            FROM usuarios u
            LEFT JOIN (
                SELECT tecnico_id, id AS orden_id, sucursal_id, estado_orden COLLATE utf8mb4_unicode_ci AS estado
                FROM ordenes
                " . ($esSuperadmin ? '' : 'WHERE sucursal_id = ?') . "
                UNION ALL
                SELECT tecnico_id, id AS orden_id, sucursal_id, estado COLLATE utf8mb4_unicode_ci AS estado
                FROM ordenesempresas
                " . ($esSuperadmin ? '' : 'WHERE sucursal_id = ?') . "
            ) all_o ON all_o.tecnico_id = u.id
            WHERE {$filtroUsuario}
            GROUP BY u.id, u.nombre_tecnico
            ORDER BY total DESC
            LIMIT 8",
            $esSuperadmin ? [] : [$sucursalId, $sucursalId, $sucursalId]
        );

        $tecnicos = [];
        foreach ($rowsTecnicos as $row) {
            $nombre = trim((string) ($row->nombre_tecnico ?? ''));
            $partes = preg_split('/\s+/', $nombre);
            $nombreCorto = $nombre;
            if (is_array($partes) && count($partes) > 1) {
                $nombreCorto = $partes[0] . ' ' . strtoupper(substr($partes[1], 0, 1)) . '.';
            } elseif (is_array($partes) && count($partes) === 1) {
                $nombreCorto = $partes[0];
            }

            $tecnicos[] = [
                'nombre' => $nombreCorto,
                'total' => (int) ($row->total ?? 0),
                'entregadas' => (int) ($row->entregadas ?? 0),
                'pendientes' => (int) ($row->pendientes ?? 0),
            ];
        }

        $rowsDias = DB::select(
            "SELECT dia, SUM(total) AS total FROM (
                SELECT DATE(fecha_de_ingreso) AS dia, COUNT(*) AS total
                FROM ordenes o
                WHERE fecha_de_ingreso >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND {$filtroPersonal}
                GROUP BY DATE(fecha_de_ingreso)
                UNION ALL
                SELECT DATE(fecha_ingreso) AS dia, COUNT(*) AS total
                FROM ordenesempresas o
                WHERE fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND {$filtroEmpresa}
                GROUP BY DATE(fecha_ingreso)
            ) t
            GROUP BY dia
            ORDER BY dia ASC",
            $esSuperadmin ? [] : [$sucursalId, $sucursalId]
        );

        $diasMap = [];
        foreach ($rowsDias as $row) {
            $diasMap[(string) $row->dia] = (int) ($row->total ?? 0);
        }
        $diasLabels = [];
        $diasData = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now('America/Guayaquil')->subDays($i);
            $key = $fecha->format('Y-m-d');
            $diasLabels[] = $fecha->format('d/m');
            $diasData[] = $diasMap[$key] ?? 0;
        }

        $rowsEquipos = DB::select(
            "SELECT e.tipo, COUNT(*) AS total FROM (
                SELECT equipo_id FROM ordenes o WHERE {$filtroPersonal}
                UNION ALL
                SELECT equipo_id FROM ordenesempresas o WHERE {$filtroEmpresa}
            ) t
            JOIN equipos e ON e.id = t.equipo_id
            GROUP BY e.tipo
            ORDER BY total DESC",
            $esSuperadmin ? [] : [$sucursalId, $sucursalId]
        );

        $equiposLabels = [];
        $equiposData = [];
        foreach ($rowsEquipos as $row) {
            $equiposLabels[] = (string) ($row->tipo ?? 'Sin tipo');
            $equiposData[] = (int) ($row->total ?? 0);
        }

        $rowsRepuestos = DB::select(
            "SELECT estado_repuesto, COUNT(*) AS total
            FROM ordenes o
            WHERE {$filtroPersonal}
            GROUP BY estado_repuesto
            ORDER BY total DESC",
            $paramsSucursal
        );

        $repuestosLabels = [];
        $repuestosData = [];
        foreach ($rowsRepuestos as $row) {
            $repuestosLabels[] = (string) ($row->estado_repuesto ?? 'No definido');
            $repuestosData[] = (int) ($row->total ?? 0);
        }

        $rowsSucursales = DB::select(
            "SELECT s.ciudad, COUNT(all_o.id) AS total
            FROM sucursales s
            LEFT JOIN (
                SELECT id, sucursal_id FROM ordenes
                UNION ALL
                SELECT id, sucursal_id FROM ordenesempresas
            ) all_o ON all_o.sucursal_id = s.id
            " . ($esSuperadmin ? '' : 'WHERE s.id = ?') . "
            GROUP BY s.id, s.ciudad
            ORDER BY total DESC
            LIMIT 5",
            $esSuperadmin ? [] : [$sucursalId]
        );

        $sucursalesLabels = [];
        $sucursalesData = [];
        foreach ($rowsSucursales as $row) {
            $sucursalesLabels[] = (string) ($row->ciudad ?? 'Sucursal');
            $sucursalesData[] = (int) ($row->total ?? 0);
        }

        return [
            'modo' => 'gestion',
            'kpis' => [
                'ordenes_totales' => $totalOrdenes,
                'ordenes_hoy' => $ordenesHoy,
                'tecnicos_activos' => $totalTecnicos,
                'clientes' => $totalClientes,
                'sucursales' => $totalSucursales,
            ],
            'charts' => [
                'dias' => ['labels' => $diasLabels, 'data' => $diasData],
                'estados' => ['labels' => $estadosLabels, 'data' => $estadosData, 'colors' => $estadosColors],
                'equipos' => ['labels' => $equiposLabels, 'data' => $equiposData],
                'repuestos' => ['labels' => $repuestosLabels, 'data' => $repuestosData],
                'sucursales' => ['labels' => $sucursalesLabels, 'data' => $sucursalesData],
            ],
            'tecnicos' => $tecnicos,
        ];
    }

    private function scalar(string $sql, array $bindings = []): int
    {
        $fila = DB::selectOne($sql, $bindings);
        if (!$fila) {
            return 0;
        }
        $valor = (array) $fila;
        return (int) (array_values($valor)[0] ?? 0);
    }
}
