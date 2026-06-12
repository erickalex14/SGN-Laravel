<?php

namespace App\Services\Operations;

use App\Repositories\Operations\OrdenRepository;
use App\DTOs\Operations\ActualizarOrdenDTO;
use App\Models\Operations\Equipo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ActualizarOrdenService
{
    protected OrdenRepository $ordenRepo;

    public function __construct(OrdenRepository $ordenRepo)
    {
        $this->ordenRepo = $ordenRepo;
    }

    /**
     * @throws Exception
     */
    public function actualizarOrden(ActualizarOrdenDTO $dto): void
    {
        $orden = $this->ordenRepo->buscarPorId($dto->orden_id);
        
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        $estadoAnterior = (string) $orden->estado_orden;
        $estadoCambiado = false;
        $nuevoEstado = null;

        try {
            DB::transaction(function () use ($orden, $dto, &$estadoCambiado, &$nuevoEstado, $estadoAnterior) {
                // 1. Actualizar datos del Cliente
                $cliente = \App\Models\Directory\Cliente::find($orden->cliente_id);
                if ($cliente) {
                    $identificacionNormalizada = trim($dto->cli_identificacion);
                    $clienteExistente = \App\Models\Directory\Cliente::where('identificacion', $identificacionNormalizada)
                        ->where('id', '!=', $cliente->id)
                        ->first();

                    if ($clienteExistente) {
                        $clienteExistente->nombres = strtoupper(trim($dto->cli_nombres));
                        $clienteExistente->apellidos = strtoupper(trim($dto->cli_apellidos));
                        $clienteExistente->numero_contacto = $dto->cli_telefono;
                        $clienteExistente->correo = $dto->cli_correo;
                        $clienteExistente->direccion_clientes = $dto->cli_direccion ? strtoupper(trim($dto->cli_direccion)) : null;
                        $clienteExistente->save();

                        $orden->cliente_id = $clienteExistente->id;
                    } else {
                        $cliente->identificacion = $identificacionNormalizada;
                        $cliente->nombres = strtoupper(trim($dto->cli_nombres));
                        $cliente->apellidos = strtoupper(trim($dto->cli_apellidos));
                        $cliente->numero_contacto = $dto->cli_telefono;
                        $cliente->correo = $dto->cli_correo;
                        $cliente->direccion_clientes = $dto->cli_direccion ? strtoupper(trim($dto->cli_direccion)) : null;
                        $cliente->save();
                    }
                }

                // 2. Actualizar datos del Equipo y sus Series
                $equipo = Equipo::find($dto->equipo_id);
                if ($equipo) {
                    $equipo->falla            = trim($dto->falla);
                    $equipo->observacion      = trim($dto->observacion);
                    $equipo->tipo_servicio_id = $dto->tipo_servicio_id;
                    
                    // Normalizar y guardar series
                    $seriesNormalizadas = $this->normalizarSeries($dto->series);
                    $seriePrincipal = $seriesNormalizadas[0] ?? '';
                    $equipo->serie = strtoupper(trim($seriePrincipal));
                    $equipo->save();

                    // Reemplazar series adicionales
                    \App\Models\Operations\EquipoSerie::where('equipo_id', $equipo->id)->delete();
                    foreach ($seriesNormalizadas as $idx => $serie) {
                        $serie = trim((string)$serie);
                        if ($serie === '') continue;
                        \App\Models\Operations\EquipoSerie::create([
                            'equipo_id' => $equipo->id,
                            'serie' => strtoupper($serie),
                            'orden' => $idx + 1
                        ]);
                    }
                }

                // 3. Actualizar datos de la Orden
                $nuevoEstado = $this->normalizarEstado($dto->estado_orden);
                if ($estadoAnterior !== $nuevoEstado) {
                    $estadoCambiado = true;
                }
                $orden->estado_orden           = $nuevoEstado;
                $orden->valor_estandar_id      = $dto->valor_estandar_id;
                $orden->repuesto_inventario_id = $dto->repuesto_inventario_id;
                $orden->fecha_prometido        = $dto->fecha_prometido;
                if ($dto->tecnico_id > 0) {
                    $orden->tecnico_id         = $dto->tecnico_id;
                }
                
                // Si la orden es por garantía, actualizar campos de factura y CAS
                if ($orden->motivo_ingreso === 'Validacion de Garantia') {
                    $orden->cas_id               = $dto->cas_id;
                    $orden->nro_factura          = $dto->nro_factura;
                    $orden->nro_factura_2        = $dto->nro_factura_2;
                    $orden->nro_sucursal_cliente = $dto->nro_sucursal_cliente;
                    $orden->fecha_facturacion    = $dto->fecha_facturacion;
                }
                $orden->modificado_por         = $dto->usuario_modificacion_id;
                $orden->fecha_modificacion     = Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s');

                // Cierre automatico si el estado corresponde
                if (in_array($orden->estado_orden, ['Finalizada', 'Entregada', 'Devuelto sin reparar', 'Nota de Credito', 'REPARADO', 'ENTREGADO', 'DEVUELTO SIN REPARAR'], true)) {
                    if (!$orden->fecha_finalizacion) {
                        $orden->fecha_finalizacion = $orden->fecha_modificacion;
                    }
                }

                $orden->save();

                Log::info('Orden de servicio actualizada mediante modulo de edicion.', [
                    'orden_id' => $orden->id,
                    'nro_orden' => $orden->nro_orden,
                    'tecnico_id' => $dto->usuario_modificacion_id
                ]);
            });

            if ($estadoCambiado) {
                try {
                    \App\Services\Operations\SgnMailService::enviarOrdenEstadoCambiado($orden, $estadoAnterior, $nuevoEstado);
                } catch (\Throwable $e) {
                    Log::error('Error al enviar notificacion de cambio de estado', ['error' => $e->getMessage()]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error transaccional al actualizar orden.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error interno al actualizar la orden. Los cambios fueron revertidos.');
        }
    }

    /**
     * @throws Exception
     */
    public function actualizarOrdenEmpresa(array $data, int $usuarioModificacionId): void
    {
        $orden = \App\Models\Operations\OrdenEmpresa::find((int) $data['orden_id']);
        
        if (!$orden) {
            throw new Exception('La orden especificada no existe.');
        }

        $estadoAnterior = (string) $orden->estado;
        $estadoCambiado = false;
        $nuevoEstado = null;

        try {
            DB::transaction(function () use ($orden, $data, $usuarioModificacionId, &$estadoCambiado, &$nuevoEstado, $estadoAnterior) {
                // 1. Actualizar datos de la Orden
                $nuevoEstado = $this->normalizarEstado($data['estado']);
                if ($estadoAnterior !== $nuevoEstado) {
                    $estadoCambiado = true;
                }
                $orden->estado = $nuevoEstado;
                $orden->fecha_prometido = $data['fecha_prometido'];
                $orden->descripcion = trim($data['descripcion']);

                $esServicios = ($orden->subtipo === 'Servicios');

                if ($esServicios) {
                    $orden->valor_hora = (float) ($data['valor_hora'] ?? 0);
                    $orden->horas_trabajadas = (float) ($data['horas_trabajadas'] ?? 0);

                    $tecnicosAsignados = $data['tecnicos_asignados'] ?? [];
                    if (!is_array($tecnicosAsignados)) {
                        $tecnicosAsignados = [$tecnicosAsignados];
                    }
                    $tecnicosAsignados = array_map('intval', array_filter($tecnicosAsignados));

                    if (!empty($tecnicosAsignados)) {
                        $orden->tecnico_id = (int) $tecnicosAsignados[0];
                        $orden->tecnicos()->sync($tecnicosAsignados);
                    }
                } else {
                    $orden->cas_id = isset($data['cas_id_empresa']) && $data['cas_id_empresa'] !== '' ? (int) $data['cas_id_empresa'] : null;
                    if (isset($data['tecnico_id']) && (int) $data['tecnico_id'] > 0) {
                        $orden->tecnico_id = (int) $data['tecnico_id'];
                    }
                }

                $orden->save();

                // 2. Actualizar datos del Equipo
                $equipo = Equipo::find((int) $data['equipo_id']);
                if ($equipo) {
                    $equipo->observacion = trim($data['eq_observacion'] ?? '');
                    if ($orden->subtipo !== 'Servicios') {
                        $equipo->falla = trim($data['descripcion']);
                    }
                    $equipo->save();
                }

                Log::info('Orden de empresa actualizada mediante modulo de edicion.', [
                    'orden_id' => $orden->id,
                    'nro_orden' => $orden->nro_orden,
                    'tecnico_id' => $usuarioModificacionId
                ]);
            });

            if ($estadoCambiado) {
                try {
                    \App\Services\Operations\SgnMailService::enviarOrdenEstadoCambiado($orden, $estadoAnterior, $nuevoEstado);
                } catch (\Throwable $e) {
                    Log::error('Error al enviar notificacion de cambio de estado empresa', ['error' => $e->getMessage()]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error transaccional al actualizar orden de empresa.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error interno al actualizar la orden corporativa. Los cambios fueron revertidos.');
        }
    }

    private function normalizarEstado(string $estado): string
    {
        $estado = trim($estado);

        $map = [
            'INGRESO' => 'Pendiente',
            'REVISIÓN' => 'En proceso',
            'REVISION' => 'En proceso',
            'ESPERA REPUESTO' => 'En proceso',
            'REPARADO' => 'Finalizada',
            'ENTREGADO' => 'Entregada',
            'DEVUELTO SIN REPARAR' => 'Devuelto sin reparar'
        ];

        return $map[$estado] ?? $estado;
    }

    private function normalizarSeries(array $series): array
    {
        $resultado = [];

        foreach ($series as $serie) {
            $serie = trim((string)$serie);
            if ($serie === '' || preg_match('/^(s[\/\-]?n|sin[\s_\-]?(serie|n[uú]mero|num)?|n[\/\-]?a|na|ninguna|none|no[\s_]?aplica|-)$/i', $serie)) {
                $serie = 'SN-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
            }
            $resultado[] = strtoupper($serie);
        }

        if (empty($resultado)) {
            $resultado[] = '';
        }

        return $resultado;
    }
}