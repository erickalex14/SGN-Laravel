<?php

namespace App\Services\Operations;

use App\Repositories\Directory\ClienteRepository;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\OrdenRepuestoRepository;
use App\DTOs\Operations\CrearOrdenDTO;
use App\Models\Inventory\ProductoInventario;
use App\Models\Inventory\Marca;
use App\Models\Inventory\TipoDispositivo;
use App\Models\Operations\CredencialEquipo;
use App\Models\Operations\Equipo;
use App\Models\Operations\EquipoSerie;
use App\Models\Operations\Orden;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CrearOrdenService
{
    protected ClienteRepository $clienteRepo;
    protected OrdenRepository $ordenRepo;
    protected OrdenRepuestoRepository $ordenRepuestoRepo;

    public function __construct(
        ClienteRepository $clienteRepo,
        OrdenRepository $ordenRepo,
        OrdenRepuestoRepository $ordenRepuestoRepo
    )
    {
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
            return DB::transaction(function () use ($dto) {
                $motivoIngreso = trim($dto->motivo_ingreso);
                $esValidacionGarantia = $motivoIngreso === 'Validacion de Garantia';
                $tipoServicioId = $dto->tipo_servicio_id;
                $tipoServicioTexto = $dto->tipo_servicio_texto ? strtoupper(trim($dto->tipo_servicio_texto)) : null;
                $garantiaTipo = $this->normalizarGarantiaTipo($dto->garantia_tipo);
                $casId = $dto->cas_id;

                if ($esValidacionGarantia) {
                    $tipoServicioId = null;
                    $tipoServicioTexto = null;
                }

                if ($motivoIngreso === 'Servicio Cliente Externo') {
                    $tipoServicioId = null;
                }

                $series = $this->normalizarSeries($dto->series);
                $seriePrincipal = $series[0] ?? '';
                $nroSucursalCliente = $dto->nro_sucursal_cliente;
                $estadoRepuesto = $this->normalizarEstadoRepuesto($dto->estado_repuesto);
                $repuestoSeleccionadoId = $dto->repuesto_inventario_id ? (int) $dto->repuesto_inventario_id : 0;

                if ($motivoIngreso === 'Servicio Cliente Externo') {
                    $nroSucursalCliente = 999;
                }

                if ($estadoRepuesto === 'Con stock' && $repuestoSeleccionadoId <= 0) {
                    throw new Exception('Debe seleccionar un repuesto del inventario cuando el estado es Con stock.');
                }

                if ($esValidacionGarantia && $garantiaTipo === null) {
                    throw new Exception('Debe seleccionar el tipo de garantia para la validacion.');
                }

                if ($garantiaTipo !== 'externa') {
                    $casId = null;
                } elseif (empty($casId)) {
                    throw new Exception('Debe seleccionar el CAS para garantia externa.');
                }

                if (!$esValidacionGarantia) {
                    $garantiaTipo = null;
                    $casId = null;
                }

                $codigoProductoInventario = strtoupper(trim((string) $dto->producto_inventario_codigo));
                $codigoFinal = $codigoProductoInventario !== '' ? $codigoProductoInventario : $dto->modelo;
                
                // 1. Gestionar Cliente (Crear o Actualizar si ya existe)
                $cliente = $this->clienteRepo->actualizarOCrear([
                    'identificacion'     => $dto->identificacion,
                    'nombres'            => strtoupper(trim($dto->nombres)),
                    'apellidos'          => strtoupper(trim($dto->apellidos)),
                    'numero_contacto'    => $dto->telefono,
                    'correo'             => $dto->correo,
                    'direccion_clientes' => $dto->direccion ? strtoupper(trim($dto->direccion)) : null
                ]);

                // 2. Crear Registro del Equipo
                $equipo = new Equipo();
                $equipo->tipo             = strtoupper(trim($dto->tipo_equipo));
                $equipo->marca            = strtoupper(trim($dto->marca));
                $equipo->modelo           = strtoupper(trim($codigoFinal));
                $equipo->serie            = $seriePrincipal;
                $equipo->falla            = trim($dto->falla);
                $equipo->observacion      = trim($dto->observacion);
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
                        'orden' => $idx + 1
                    ]);
                }

                // Guardar credenciales
                foreach ($dto->credenciales as $credencial) {
                    $contrasena = trim((string)($credencial['contrasena'] ?? ''));
                    if ($contrasena === '') {
                        continue;
                    }
                    CredencialEquipo::create([
                        'equipo_id' => $equipo->id,
                        'usuario' => trim((string)($credencial['usuario'] ?? '')),
                        'contrasena' => $contrasena,
                        'es_patron' => (int)($credencial['es_patron'] ?? 0)
                    ]);
                }

                // 3. Generar Nro de Orden y Crear la Orden
                $nroOrden = $this->ordenRepo->generarNumeroOrden($dto->sucursal_id);

                $orden = new Orden();
                $orden->nro_orden        = $nroOrden;
                $orden->cliente_id       = $cliente->id;
                $orden->equipo_id        = $equipo->id;
                $orden->tecnico_id       = $dto->tecnico_id;
                $orden->sucursal_id      = $dto->sucursal_id;
                $orden->ingresado_por    = $dto->ingresado_por;
                $orden->fecha_de_ingreso = $dto->fecha_ingreso;
                $orden->estado_orden     = 'Pendiente';
                $orden->motivo_ingreso   = $motivoIngreso;
                $orden->nro_factura      = $dto->nro_factura;
                $orden->nro_factura_2    = $dto->nro_factura_2;
                $orden->nro_sucursal_cliente = $nroSucursalCliente;
                $orden->estado_repuesto  = $estadoRepuesto;
                $orden->estado_garantia  = $esValidacionGarantia ? 'Pendiente' : null;
                $orden->fecha_prometido  = $dto->fecha_prometido;
                $orden->garantia_tipo    = $garantiaTipo;
                $orden->cas_id           = $casId;
                $orden->repuesto_inventario_id = $repuestoSeleccionadoId > 0 ? $repuestoSeleccionadoId : null;
                
                $orden->save();

                if ($estadoRepuesto === 'Con stock' && $repuestoSeleccionadoId > 0) {
                    $this->ordenRepuestoRepo->registrarDesdeCreacion(
                        (int) $orden->id,
                        $repuestoSeleccionadoId,
                        (int) $dto->ingresado_por
                    );
                }

                Log::info('Orden de Servicio creada exitosamente.', [
                    'nro_orden' => $nroOrden,
                    'cliente_id' => $cliente->id
                ]);

                return $orden;
            });
        } catch (Exception $e) {
            Log::error('Fallo transaccional al crear orden de servicio.', ['error' => $e->getMessage()]);
            throw new Exception('Ocurrió un error al generar la orden. Los cambios han sido revertidos.');
        }
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
        if (ProductoInventario::where('codigo', $codigo)->exists()) {
            return;
        }

        $marca = Marca::query()
            ->whereRaw('UPPER(nombre) = ?', [strtoupper(trim($marcaNombre))])
            ->first();
        if (!$marca) {
            throw new Exception('La marca seleccionada no existe para registrar el producto de inventario.');
        }

        $tipo = TipoDispositivo::query()
            ->whereRaw('UPPER(nombre) = ?', [strtoupper(trim($tipoNombre))])
            ->first();
        if (!$tipo) {
            throw new Exception('El tipo de equipo seleccionado no existe para registrar el producto de inventario.');
        }

        ProductoInventario::create([
            'codigo' => $codigo,
            'descripcion' => strtoupper(trim($descripcion !== '' ? $descripcion : $codigo)),
            'marca_id' => $marca->id,
            'tipo_dispositivo_id' => $tipo->id,
            'tipo_dispositivo_codigo' => $tipo->codigo,
        ]);
    }
}
