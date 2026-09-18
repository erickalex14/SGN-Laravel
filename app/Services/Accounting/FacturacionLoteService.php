<?php

namespace App\Services\Accounting;

use App\Models\Accounting\FacturacionLote;
use App\Models\Accounting\FacturacionLoteOrden;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use Illuminate\Support\Facades\DB;
use Exception;

class FacturacionLoteService
{
    /**
     * Estados permitidos para facturación de órdenes
     */
    public const ESTADOS_ELEGIBLES = [
        'Finalizada',
        'Reparada',
        'Entregada',
        'Cerrado',
        'Cerrada',
        'Lista para entrega',
        'Entregado en Recepcion para Entrega',
        'Nota de Credito',
    ];

    /**
     * Fecha mínima de corte establecida por el negocio
     */
    public const FECHA_MINIMA = '2026-09-05';

    /**
     * Obtiene las órdenes elegibles desde vista_ordenes aplicando filtros
     */
    public function obtenerOrdenesElegibles(array $filtros = []): array
    {
        $query = DB::table('vista_ordenes')
            ->where('fecha_de_ingreso', '>=', self::FECHA_MINIMA)
            ->whereIn('estado_orden', self::ESTADOS_ELEGIBLES);

        // Filtro por tipo de orden (personal / empresa)
        if (!empty($filtros['tipo_orden'])) {
            $query->where('tipo_orden', $filtros['tipo_orden']);
        }

        // Filtro por estado_facturacion ('Pendiente', 'Facturado', 'todos')
        $estadoFacturacion = $filtros['estado_facturacion'] ?? 'Pendiente';
        if ($estadoFacturacion !== 'todos') {
            if ($estadoFacturacion === 'Pendiente') {
                $query->where(function ($q) {
                    $q->whereNull('estado_facturacion')
                      ->orWhere('estado_facturacion', 'Pendiente')
                      ->orWhere('estado_facturacion', '');
                });
            } else {
                $query->where('estado_facturacion', $estadoFacturacion);
            }
        }

        // Filtro por sucursal
        if (!empty($filtros['sucursal_id'])) {
            $query->where('sucursal_id', $filtros['sucursal_id']);
        }

        // Búsqueda libre (nro_orden, cliente, identificación, serie, modelo)
        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(function ($q) use ($buscar) {
                $q->where('nro_orden', 'LIKE', "%{$buscar}%")
                  ->orWhere('cliente', 'LIKE', "%{$buscar}%")
                  ->orWhere('identificacion', 'LIKE', "%{$buscar}%")
                  ->orWhere('serie', 'LIKE', "%{$buscar}%")
                  ->orWhere('modelo', 'LIKE', "%{$buscar}%")
                  ->orWhere('nro_factura', 'LIKE', "%{$buscar}%");
            });
        }

        return $query->orderBy('fecha_de_ingreso', 'desc')->get()->toArray();
    }

    /**
     * Registra una factura de lote de Milenium y actualiza las órdenes vinculadas
     *
     * @param array $datos {nro_factura, nro_autorizacion, valor_facturado, fecha_factura, observaciones}
     * @param array $ordenesSeleccionadas Array de ['tipo_orden' => '...', 'orden_id' => ..., 'nro_orden' => '...']
     * @param int|null $usuarioId
     * @return FacturacionLote
     * @throws Exception
     */
    public function guardarLoteFacturacion(array $datos, array $ordenesSeleccionadas, ?int $usuarioId = null): FacturacionLote
    {
        if (empty($ordenesSeleccionadas)) {
            throw new Exception("Debe seleccionar al menos una orden para facturar.");
        }

        return DB::transaction(function () use ($datos, $ordenesSeleccionadas, $usuarioId) {
            // 1. Crear cabecera del lote
            $lote = FacturacionLote::create([
                'nro_factura' => trim($datos['nro_factura']),
                'nro_autorizacion' => !empty($datos['nro_autorizacion']) ? trim($datos['nro_autorizacion']) : null,
                'valor_facturado' => (float) ($datos['valor_facturado'] ?? 0),
                'fecha_factura' => $datos['fecha_factura'] ?? date('Y-m-d'),
                'creado_por' => $usuarioId,
                'observaciones' => !empty($datos['observaciones']) ? trim($datos['observaciones']) : null,
            ]);

            $nroFactura = $lote->nro_factura;
            $nroAutorizacion = $lote->nro_autorizacion;
            $fechaFactura = $lote->fecha_factura;
            $valorFacturado = $lote->valor_facturado;

            // 2. Asociar cada orden y actualizar las tablas base
            foreach ($ordenesSeleccionadas as $item) {
                $tipo = $item['tipo_orden'];
                $ordenId = (int) $item['orden_id'];
                $nroOrden = $item['nro_orden'] ?? '';

                // Insertar o actualizar registro pivote
                FacturacionLoteOrden::updateOrCreate(
                    [
                        'tipo_orden' => $tipo,
                        'orden_id' => $ordenId,
                    ],
                    [
                        'facturacion_lote_id' => $lote->id,
                        'nro_orden' => $nroOrden,
                    ]
                );

                // Actualizar orden base
                if ($tipo === 'personal') {
                    Orden::where('id', $ordenId)->update([
                        'nro_factura' => $nroFactura,
                        'nro_autorizacion_factura' => $nroAutorizacion,
                        'fecha_facturacion' => $fechaFactura,
                        'estado_facturacion' => 'Facturado',
                    ]);
                } elseif ($tipo === 'empresa') {
                    OrdenEmpresa::where('id', $ordenId)->update([
                        'nro_factura' => $nroFactura,
                        'nro_autorizacion_factura' => $nroAutorizacion,
                        'fecha_facturacion' => $fechaFactura,
                        'estado_facturacion' => 'Facturado',
                    ]);
                }
            }

            return $lote;
        });
    }

    /**
     * Obtiene el listado de facturas/lotes de Milenium con sus órdenes
     */
    public function obtenerHistorialLotes(array $filtros = []): array
    {
        $query = FacturacionLote::with(['ordenes', 'creadoPor'])
            ->orderBy('id', 'desc');

        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(function ($q) use ($buscar) {
                $q->where('nro_factura', 'LIKE', "%{$buscar}%")
                  ->orWhere('nro_autorizacion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('ordenes', function ($oq) use ($buscar) {
                      $oq->where('nro_orden', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('fecha_factura', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fecha_factura', '<=', $filtros['fecha_hasta']);
        }

        return $query->get()->toArray();
    }

    /**
     * Detalle de un lote de facturación específico
     */
    public function obtenerDetalleLote(int $id): ?FacturacionLote
    {
        return FacturacionLote::with(['ordenes', 'creadoPor'])->find($id);
    }

    /**
     * Desvincular una orden de un lote (revertir su facturación)
     */
    public function desvincularOrden(int $loteOrdenId): bool
    {
        return DB::transaction(function () use ($loteOrdenId) {
            $pivote = FacturacionLoteOrden::find($loteOrdenId);
            if (!$pivote) {
                return false;
            }

            $tipo = $pivote->tipo_orden;
            $ordenId = $pivote->orden_id;

            // Revertir en orden base
            if ($tipo === 'personal') {
                Orden::where('id', $ordenId)->update([
                    'nro_factura' => null,
                    'nro_autorizacion_factura' => null,
                    'fecha_facturacion' => null,
                    'estado_facturacion' => 'Pendiente',
                ]);
            } elseif ($tipo === 'empresa') {
                OrdenEmpresa::where('id', $ordenId)->update([
                    'nro_factura' => null,
                    'nro_autorizacion_factura' => null,
                    'fecha_facturacion' => null,
                    'estado_facturacion' => 'Pendiente',
                ]);
            }

            $pivote->delete();
            return true;
        });
    }
}
