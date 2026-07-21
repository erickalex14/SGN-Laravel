<?php

namespace App\Services\Operations;

use App\DTOs\Operations\CrearOrdenDTO;
use App\Models\Identity\Usuario;
use App\Models\Inventory\Marca;
use App\Models\Inventory\ProductoInventario;
use App\Models\Inventory\TipoDispositivo;
use App\Models\Operations\CredencialEquipo;
use App\Models\Operations\Equipo;
use App\Models\Operations\EquipoSerie;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Repositories\Directory\ClienteRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\OrdenRepuestoRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Operations\AuditLogger;

class CrearOrdenService
{
    protected ClienteRepository $clienteRepo;

    protected OrdenRepository $ordenRepo;

    protected OrdenRepuestoRepository $ordenRepuestoRepo;

    public function __construct(
        ClienteRepository $clienteRepo,
        OrdenRepository $ordenRepo,
        OrdenRepuestoRepository $ordenRepuestoRepo
    ) {
        $this->clienteRepo = $clienteRepo;
        $this->ordenRepo = $ordenRepo;
        $this->ordenRepuestoRepo = $ordenRepuestoRepo;
    }

    /**
     * @throws Exception
     */
    public function crearOrden(CrearOrdenDTO $dto): Orden
    {
        try {
            $orden = $this->ordenRepo->ejecutarConLockSecuencial($dto->sucursal_id, function () use ($dto) {
                return DB::transaction(function () use ($dto) {
                    $motivoIngreso = trim($dto->motivo_ingreso);
                    $esValidacionGarantia = $motivoIngreso === 'Validacion de Garantia';
                    $tipoServicioId = $dto->tipo_servicio_id;
                    $tipoServicioTexto = $dto->tipo_servicio_texto ? strtoupper(trim($dto->tipo_servicio_texto)) : null;
                    $garantiaTipo = $this->normalizarGarantiaTipo($dto->garantia_tipo);
                    $casId = $dto->cas_id;

                    if ($motivoIngreso === 'Servicio Cliente Externo') {
                        $tipoServicioId = null;
                    }

                    $series = $this->normalizarSeries($dto->series);
                    $seriePrincipal = $series[0] ?? '';
                    $nroSucursalCliente = $dto->nro_sucursal_cliente;
                    $estadoRepuesto = $this->normalizarEstadoRepuesto($dto->estado_repuesto);

                    $repuestosSeleccionados = $dto->repuestos_seleccionados ?? [];
                    if (empty($repuestosSeleccionados) && $dto->repuesto_inventario_id) {
                        $repuestosSeleccionados[] = $dto->repuesto_inventario_id;
                    }

                    if ($motivoIngreso === 'Servicio Cliente Externo') {
                        $nroSucursalCliente = 999;
                    }

                    if ($estadoRepuesto === 'Con stock' && empty($repuestosSeleccionados)) {
                        throw new Exception('Debe seleccionar al menos un repuesto del inventario cuando el estado es Con stock.');
                    }

                    if ($esValidacionGarantia) {
                        if ($garantiaTipo !== 'externa') {
                            $casId = null;
                        }
                    } else {
                        $garantiaTipo = null;
                        $casId = null;
                    }

                    if ($motivoIngreso === 'Servicio Cliente Externo') {
                        $casId = null;
                    }

                    $codigoProductoInventario = strtoupper(trim((string) $dto->producto_inventario_codigo));
                    $codigoFinal = $codigoProductoInventario !== '' ? $codigoProductoInventario : $dto->modelo;

                    // 1. Gestionar Cliente (Crear o Actualizar si ya existe)
                    $cliente = $this->clienteRepo->actualizarOCrear([
                        'identificacion' => $dto->identificacion,
                        'nombres' => strtoupper(trim($dto->nombres)),
                        'apellidos' => strtoupper(trim($dto->apellidos)),
                        'numero_contacto' => $dto->telefono,
                        'correo' => $dto->correo,
                        'direccion_clientes' => $dto->direccion ? strtoupper(trim($dto->direccion)) : null,
                    ]);

                    // 2. Crear Registro del Equipo
                    $equipo = new Equipo;
                    $equipo->tipo = strtoupper(trim($dto->tipo_equipo));
                    $equipo->marca = strtoupper(trim($dto->marca));
                    $equipo->modelo = strtoupper(trim($codigoFinal));
                    $equipo->serie = $seriePrincipal;
                    $equipo->falla = trim($dto->falla);
                    $equipo->observacion = trim($dto->observacion);
                    $equipo->tipo_servicio_id = $tipoServicioId;
                    $equipo->tipo_servicio_texto = $tipoServicioTexto;
                    $equipo->fecha_facturacion = $dto->fecha_facturacion;
                    $equipo->contrasena_equipo = $dto->contrasena_equipo;

                    if ($codigoProductoInventario !== '') {
                        $this->asegurarProductoInventario(
                            $codigoProductoInventario,
                            $dto->modelo,
                            $dto->marca,
                            $dto->tipo_equipo
                        );
                        $equipo->producto_inventario_codigo = $codigoProductoInventario;
                    }
                    $equipo->save();

                    // Guardar series adicionales
                    foreach ($series as $idx => $serie) {
                        EquipoSerie::create([
                            'equipo_id' => $equipo->id,
                            'serie' => $serie,
                            'orden' => $idx + 1,
                        ]);
                    }

                    // Guardar credenciales
                    foreach ($dto->credenciales as $credencial) {
                        $contrasena = trim((string) ($credencial['contrasena'] ?? ''));
                        if ($contrasena === '') {
                            continue;
                        }
                        CredencialEquipo::create([
                            'equipo_id' => $equipo->id,
                            'usuario' => trim((string) ($credencial['usuario'] ?? '')),
                            'contrasena' => $contrasena,
                            'es_patron' => (int) ($credencial['es_patron'] ?? 0),
                        ]);
                    }

                    // Identificar si el usuario creador pertenece a un CAS
                    $usuarioCreador = Usuario::find($dto->ingresado_por);
                    $casAsignado = $usuarioCreador ? $usuarioCreador->casAsignados()->first() : null;

                    $casIdParaCodigo = null;
                    if ($casAsignado) {
                        $casIdParaCodigo = $casAsignado->id;
                    }

                    // 3. Generar Nro de Orden y Crear la Orden
                    $nroOrden = $this->ordenRepo->generarNumeroOrden($dto->sucursal_id, $casIdParaCodigo);

                    $orden = new Orden;
                    $orden->nro_orden = $nroOrden;
                    $orden->cliente_id = $cliente->id;
                    $orden->equipo_id = $equipo->id;
                    $orden->tecnico_id = $dto->tecnico_id;
                    $orden->sucursal_id = $dto->sucursal_id;
                    $orden->ingresado_por = $dto->ingresado_por;
                    $orden->fecha_de_ingreso = $dto->fecha_ingreso;
                    $orden->estado_orden = 'Pendiente';
                    $orden->motivo_ingreso = $motivoIngreso;
                    $orden->nro_factura = $dto->nro_factura;
                    $orden->nro_factura_2 = $dto->nro_factura_2;
                    $orden->nro_sucursal_cliente = $nroSucursalCliente;
                    $orden->estado_repuesto = $estadoRepuesto;
                    $orden->estado_garantia = $esValidacionGarantia ? 'Pendiente' : null;
                    $orden->fecha_prometido = $dto->fecha_prometido;
                    $orden->garantia_tipo = $garantiaTipo;
                    $orden->cas_id = $casAsignado ? $casAsignado->id : $casId;
                    $orden->repuesto_inventario_id = ! empty($repuestosSeleccionados) ? (int) $repuestosSeleccionados[0] : null;
                    $orden->fecha_facturacion = $esValidacionGarantia ? $dto->fecha_facturacion : null;

                    $orden->save();

                    if ($estadoRepuesto === 'Con stock' && ! empty($repuestosSeleccionados)) {
                        foreach ($repuestosSeleccionados as $repId) {
                            $this->ordenRepuestoRepo->registrarDesdeCreacion(
                                (int) $orden->id,
                                (int) $repId,
                                (int) $dto->ingresado_por
                            );
                        }
                    }

                    Log::info('Orden de Servicio creada exitosamente.', [
                        'nro_orden' => $nroOrden,
                        'cliente_id' => $cliente->id,
                    ]);

                    if ($orden->tecnico_id) {
                        \App\Models\Identity\Notificacion::create([
                            'usuario_id' => $orden->tecnico_id,
                            'tipo' => 'orden_asignada',
                            'mensaje' => "Se te ha asignado una nueva orden: {$orden->nro_orden}",
                            'orden_id' => $orden->id,
                            'nro_orden' => $orden->nro_orden,
                        ]);
                    }

                    return $orden;
                });
            });

            AuditLogger::registrar('CREAR_ORDEN', 'ordenes', (string)$orden->id, [
                'nro_orden' => $orden->nro_orden,
                'cliente' => $orden->cliente ? trim($orden->cliente->nombres . ' ' . $orden->cliente->apellidos) : 'Desconocido',
                'tipo_orden' => 'personal'
            ]);

            try {
                SgnMailService::enviarOrdenCreada($orden);
            } catch (\Throwable $e) {
                Log::error('Error al enviar notificacion de nueva orden', ['error' => $e->getMessage()]);
            }

            return $orden;
        } catch (\Throwable $e) {
            Log::error('Fallo transaccional al crear orden de servicio.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error al generar la orden. Los cambios han sido revertidos.');
        }
    }

    /**
     * @throws Exception
     */
    public function crearOrdenEmpresa(array $data): OrdenEmpresa
    {
        try {
            $sucursalId = (int) $data['sucursal_id'];

            $orden = $this->ordenRepo->ejecutarConLockSecuencial($sucursalId, function () use ($data, $sucursalId) {
                return DB::transaction(function () use ($data, $sucursalId) {
                    $subtipo = trim((string) $data['subtipo_empresa']);
                    $descripcion = trim((string) ($data['emp_descripcion'] ?? ''));

                    if (! in_array($subtipo, ['Autoconsumo', 'Servicios', 'Stock'], true)) {
                        throw new Exception('Selecciona el tipo (Autoconsumo, Servicios o Stock).');
                    }

                    if ($subtipo === 'Autoconsumo' || $subtipo === 'Stock') {
                        $series = $this->normalizarSeries((array) ($data['emp_series'] ?? []));
                        $codigoProducto = strtoupper(trim((string) ($data['emp_modelo'] ?? '')));

                        if ($codigoProducto !== '') {
                            $this->asegurarProductoInventario(
                                $codigoProducto,
                                $codigoProducto,
                                (string) $data['emp_marca'],
                                (string) $data['emp_tipo_equipo']
                            );
                        }

                        $equipo = $this->crearEquipoBase([
                            'tipo' => $data['emp_tipo_equipo'],
                            'marca' => $data['emp_marca'],
                            'modelo' => $codigoProducto,
                            'serie' => implode(', ', $series),
                            'falla' => $data['emp_falla'],
                            'observacion' => $data['emp_observacion'] ?? '',
                            'tipo_servicio_id' => ! empty($data['emp_tipo_servicio_id']) ? (int) $data['emp_tipo_servicio_id'] : null,
                            'producto_inventario_codigo' => $codigoProducto !== '' ? $codigoProducto : null,
                        ]);

                        foreach ($series as $idx => $serie) {
                            EquipoSerie::create([
                                'equipo_id' => $equipo->id,
                                'serie' => $serie,
                                'orden' => $idx + 1,
                            ]);
                        }
                    } else {
                        $equipo = $this->crearEquipoBase([
                            'tipo' => 'Servicio',
                            'marca' => '',
                            'modelo' => '',
                            'serie' => '',
                            'falla' => $descripcion,
                            'observacion' => '',
                        ]);
                    }

                    $esNovisolutionsServicio = ($subtipo === 'Servicios');

                    $tecnicosAsignados = $data['tecnicos_asignados'] ?? [];
                    if (! is_array($tecnicosAsignados)) {
                        $tecnicosAsignados = [$tecnicosAsignados];
                    }
                    $tecnicosAsignados = array_map('intval', array_filter($tecnicosAsignados));
                    $tecnicoEncargado = ! empty($data['tecnico_encargado']) ? (int) $data['tecnico_encargado'] : null;

                    $primaryTecnicoId = $esNovisolutionsServicio
                        ? ($tecnicoEncargado ?: (! empty($tecnicosAsignados) ? (int) $tecnicosAsignados[0] : 0))
                        : (int) $data['ord_tecnico_id'];

                    $nroOrden = $this->ordenRepo->generarNumeroOrden($sucursalId);

                    $ticketVal = null;
                    if ($subtipo === 'Servicios') {
                        $ticketVal = trim((string) ($data['emp_nro_ticket'] ?? ''));
                        if ($ticketVal === '') {
                            $count = DB::table('ordenesempresas')->where('subtipo', 'Servicios')->count();
                            $siguienteSecuencial = $count + 1;
                            $codigoAleatorio = strtoupper(substr(md5(uniqid('', true)), 0, 4));
                            $ticketVal = 'TK-'.$codigoAleatorio.'-'.str_pad($siguienteSecuencial, 4, '0', STR_PAD_LEFT);
                        }
                    }

                    $casId = ! empty($data['cas_id_empresa']) ? (int) $data['cas_id_empresa'] : null;

                    $empresaObj = \App\Models\Directory\Empresa::find((int) $data['empresa_id']);
                    $isRbHealth = ($empresaObj && trim(strtoupper($empresaObj->nombre)) === 'RB-HEALTH ECUADOR CIA LTDA');

                    $valHora = $esNovisolutionsServicio ? (float) ($data['valor_hora'] ?? 0) : null;
                    if ($isRbHealth) {
                        $valHora = 52.0;
                    }

                    $orden = OrdenEmpresa::create([
                        'nro_orden' => $nroOrden,
                        'empresa_id' => (int) $data['empresa_id'],
                        'subtipo' => $subtipo,
                        'equipo_id' => $equipo->id,
                        'tipo_servicio' => $subtipo === 'Servicios' ? trim((string) $data['emp_tipo_servicio']) : null,
                        'nro_ticket' => $ticketVal,
                        'descripcion' => $subtipo === 'Servicios' ? $descripcion : trim((string) $data['emp_falla']),
                        'tecnico_id' => $primaryTecnicoId,
                        'sucursal_id' => $sucursalId,
                        'cas_id' => $casId,
                        'ingresado_por' => (int) $data['ingresado_por'],
                        'fecha_prometido' => $data['emp_fecha_prometido'],
                        'estado' => 'Pendiente',
                        'fecha_ingreso' => $data['fecha_ingreso'],
                        'nro_sucursal_cliente' => in_array($subtipo, ['Stock', 'Servicios', 'Autoconsumo'], true) ? (string) ($data['nro_sucursal_cliente'] ?? null) : null,
                        'valor_hora' => $valHora,
                        'horas_trabajadas' => $esNovisolutionsServicio ? (float) ($data['horas_trabajadas'] ?? 0) : null,
                    ]);

                    if ($esNovisolutionsServicio && ! empty($tecnicosAsignados)) {
                        $orden->tecnicos()->sync($tecnicosAsignados);
                    } else {
                        $orden->tecnicos()->sync([$primaryTecnicoId]);
                    }

                    // Registrar en Inventario Físico ST si es Novisolutions y Stock
                    if ((int)$orden->empresa_id === 1 && $subtipo === 'Stock') {
                        $prod = \App\Models\Inventory\ProductoInventario::whereRaw('UPPER(TRIM(codigo)) = ?', [strtoupper(trim($codigoProducto))])->first();
                        $nombreProducto = $prod ? $prod->descripcion : $codigoProducto;

                        foreach ($series as $serie) {
                            \App\Models\Inventory\ProductoInventarioFisicoSt::create([
                                'orden_empresa_id' => $orden->id,
                                'sucursal_id' => $orden->sucursal_id,
                                'codigo' => $codigoProducto,
                                'serie' => strtoupper(trim($serie)),
                                'nombre' => $nombreProducto,
                                'estado' => 'Tienda',
                            ]);
                        }
                    }

                    Log::info('Orden de empresa creada exitosamente.', [
                        'nro_orden' => $nroOrden,
                        'empresa_id' => (int) $data['empresa_id'],
                    ]);

                    if ($orden->tecnico_id) {
                        \App\Models\Identity\Notificacion::create([
                            'usuario_id' => $orden->tecnico_id,
                            'tipo' => 'orden_asignada',
                            'mensaje' => "Se te ha asignado una nueva orden de empresa: {$orden->nro_orden}",
                            'orden_id' => $orden->id,
                            'nro_orden' => $orden->nro_orden,
                        ]);
                    }

                    return $orden;
                });
            });

            AuditLogger::registrar('CREAR_ORDEN', 'ordenes', (string)$orden->id, [
                'nro_orden' => $orden->nro_orden,
                'empresa' => $orden->empresa ? $orden->empresa->nombre : 'Desconocida',
                'tipo_orden' => 'empresa'
            ]);

            try {
                SgnMailService::enviarOrdenCreada($orden);
            } catch (\Throwable $e) {
                Log::error('Error al enviar notificacion de nueva orden empresa', ['error' => $e->getMessage()]);
            }

            return $orden;
        } catch (\Throwable $e) {
            Log::error('Fallo transaccional al crear orden de empresa.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrio un error al generar la orden de empresa. Los cambios han sido revertidos.');
        }
    }

    private function crearEquipoBase(array $data): Equipo
    {
        $equipo = new Equipo;
        $equipo->tipo = strtoupper(trim((string) $data['tipo']));
        $equipo->marca = strtoupper(trim((string) $data['marca']));
        $equipo->modelo = strtoupper(trim((string) $data['modelo']));
        $equipo->serie = strtoupper(trim((string) $data['serie']));
        $equipo->falla = trim((string) $data['falla']);
        $equipo->observacion = trim((string) ($data['observacion'] ?? ''));
        $equipo->tipo_servicio_id = $data['tipo_servicio_id'] ?? null;
        $equipo->producto_inventario_codigo = $data['producto_inventario_codigo'] ?? null;
        $equipo->save();

        return $equipo;
    }

    private function normalizarSeries(array $series): array
    {
        $resultado = [];

        foreach ($series as $serie) {
            $serie = trim((string) $serie);
            if ($serie === '' || preg_match('/^(s[\/\-]?n|sin[\s_\-]?(serie|n[uú]mero|num)?|n[\/\-]?a|na|ninguna|none|no[\s_]?aplica|-)$/i', $serie)) {
                $serie = 'N/A';
            } else {
                $serie = strtoupper($serie);
            }
            $resultado[] = $serie;
        }

        if (empty($resultado)) {
            $resultado[] = 'N/A';
        }

        return $resultado;
    }

    private function normalizarEstadoRepuesto(?string $estado): string
    {
        $valor = trim((string) $estado);
        if ($valor === '') {
            return 'No requerido';
        }

        return match (mb_strtoupper($valor)) {
            'NO REQUERIDO' => 'No requerido',
            'REQUERIDO' => 'Requerido',
            'CON STOCK' => 'Con stock',
            default => $valor,
        };
    }

    private function normalizarGarantiaTipo(?string $tipo): ?string
    {
        $valor = trim((string) $tipo);
        if ($valor === '') {
            return null;
        }

        return match (mb_strtoupper($valor)) {
            'INTERNA', 'PROPIA' => 'propia',
            'EXTERNA' => 'externa',
            default => null,
        };
    }

    private function asegurarProductoInventario(string $codigo, string $descripcion, string $marcaNombre, string $tipoNombre): void
    {
        $codigoNormalizado = strtoupper(trim($codigo));

        if (ProductoInventario::whereRaw('UPPER(TRIM(codigo)) = ?', [$codigoNormalizado])->exists()) {
            return;
        }

        $marca = Marca::query()
            ->whereRaw('UPPER(nombre) = ?', [strtoupper(trim($marcaNombre))])
            ->first();
        if (! $marca) {
            throw new Exception('La marca seleccionada no existe para registrar el producto de inventario.');
        }

        $tipo = TipoDispositivo::query()
            ->whereRaw('UPPER(nombre) = ?', [strtoupper(trim($tipoNombre))])
            ->first();
        if (! $tipo) {
            throw new Exception('El tipo de equipo seleccionado no existe para registrar el producto de inventario.');
        }

        ProductoInventario::create([
            'codigo' => $codigoNormalizado,
            'descripcion' => strtoupper(trim($descripcion !== '' ? $descripcion : $codigoNormalizado)),
            'marca_id' => $marca->id,
            'tipo_dispositivo_id' => $tipo->id,
            'tipo_dispositivo_codigo' => $tipo->codigo,
        ]);
    }
}
