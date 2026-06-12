<?php

namespace App\Services\Operations;

use App\DTOs\Operations\IngresarPreordenDTO;
use App\DTOs\Operations\VerificarPreordenDTO;
use App\Repositories\Operations\OrdenRepository;
use App\Repositories\Operations\PreordenRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Exception;

class PreordenService
{
    protected PreordenRepository $repository;
    protected OrdenRepository $ordenRepository;

    public function __construct(PreordenRepository $repository, OrdenRepository $ordenRepository)
    {
        $this->repository = $repository;
        $this->ordenRepository = $ordenRepository;
    }

    public function obtenerContextoIndex(bool $esSuperadmin, int $sucursalSesion): array
    {
        return [
            'tecnicos' => $this->repository->obtenerTecnicosDisponibles($esSuperadmin, $sucursalSesion),
            'preordenes' => $this->repository->obtenerPreordenesPendientes($esSuperadmin, $sucursalSesion),
        ];
    }

    /**
     * @throws Exception
     */
    public function ingresar(IngresarPreordenDTO $dto): array
    {
        if (!$dto->es_superadmin && $dto->sucursal_sesion_id > 0) {
            $tecnicoValido = $this->repository->tecnicoValidoEnSucursal($dto->tecnico_id, $dto->sucursal_sesion_id);
            if (!$tecnicoValido) {
                throw new Exception('El tecnico no pertenece a tu sucursal.');
            }
        }

        $resultado = DB::transaction(function () use ($dto) {
            $preorden = $this->repository->obtenerPreordenConBloqueo($dto->preorden_id);
            if (!$preorden) {
                throw new Exception('Pre-orden no encontrada.');
            }
            if (!empty($preorden->orden_id)) {
                throw new Exception('Esta pre-orden ya fue ingresada.');
            }

            $sucursalOrdenId = $dto->es_superadmin
                ? (int) ($preorden->sucursal_id ?: $dto->sucursal_sesion_id)
                : $dto->sucursal_sesion_id;

            if ($sucursalOrdenId <= 0) {
                throw new Exception('No se pudo determinar la sucursal de la orden.');
            }

            $lockAdquirido = false;
            try {
                $lockAdquirido = $this->repository->adquirirLockSecuenciaOrden($sucursalOrdenId);
                if (!$lockAdquirido) {
                    throw new Exception('No se pudo obtener el lock. Intente de nuevo.');
                }

                $nroOrdenCandidato = preg_replace('/^PRE(OR)?-/i', '', (string) ($preorden->nro_preorden ?? ''));
                $nroOrdenCandidato = trim((string) $nroOrdenCandidato);
                $nroOrden = $this->ordenRepository->generarNumeroOrden($sucursalOrdenId);
                if ($nroOrdenCandidato !== '' && !$this->repository->existeNumeroOrden($nroOrdenCandidato)) {
                    $nroOrden = $nroOrdenCandidato;
                }

                $ci = preg_replace('/\D/', '', (string) ($preorden->identificacion ?? ''));
                $ciGenerica = ($ci === '' || preg_match('/^0+$/', $ci) === 1);

                $cliente = null;
                if (!$ciGenerica) {
                    $cliente = $this->repository->buscarClientePorIdentificacion($ci);
                }

                if ($cliente) {
                    $cliente->nombres = (string) ($preorden->nombres ?? '');
                    $cliente->apellidos = (string) ($preorden->apellidos ?? '');
                    $cliente->numero_contacto = (string) ($preorden->telefono ?? '');
                    $cliente->correo = (string) ($preorden->correo ?? '');
                    $cliente->direccion_clientes = $dto->direccion;
                    $this->repository->guardarCliente($cliente);
                } else {
                    $identificacion = $ciGenerica ? $this->generarIdentificacionTemporal() : $ci;
                    $cliente = $this->repository->crearCliente([
                        'nombres' => (string) ($preorden->nombres ?? ''),
                        'apellidos' => (string) ($preorden->apellidos ?? ''),
                        'identificacion' => $identificacion,
                        'numero_contacto' => (string) ($preorden->telefono ?? ''),
                        'correo' => (string) ($preorden->correo ?? ''),
                        'direccion_clientes' => $dto->direccion,
                    ]);
                }

                $codigoProducto = trim((string) ($preorden->codigo_producto ?? ''));
                $detalle = (string) ($preorden->detalle_equipo ?? '');
                $fechaFacturacion = $preorden->fecha_facturacion ?: null;
                $serieNormalizada = $this->normalizarSerie($dto->serie);
                $serieFinal = $serieNormalizada !== '' ? $serieNormalizada : ($codigoProducto !== '' ? mb_strtoupper($codigoProducto) : '');
                $observacionFinal = $dto->observacion !== '' ? $dto->observacion : $detalle;

                $productoInventarioCodigo = null;
                if ($codigoProducto !== '' && $this->repository->existeCodigoProductoInventario($codigoProducto)) {
                    $productoInventarioCodigo = $codigoProducto;
                }

                $equipo = $this->repository->crearEquipo([
                    'tipo' => (string) ($preorden->tipo_producto ?? ''),
                    'tipo_servicio_id' => null,
                    'tipo_servicio_texto' => null,
                    'marca' => (string) ($preorden->marca_producto ?? ''),
                    'modelo' => $codigoProducto,
                    'serie' => $serieFinal,
                    'falla' => $detalle,
                    'observacion' => $observacionFinal,
                    'fecha_facturacion' => $fechaFacturacion,
                    'producto_inventario_codigo' => $productoInventarioCodigo,
                ]);

                if ($serieFinal !== '') {
                    $this->repository->crearEquipoSerie($equipo->id, $serieFinal);
                }

                $orden = $this->repository->crearOrden([
                    'cliente_id' => $cliente->id,
                    'equipo_id' => $equipo->id,
                    'tecnico_id' => $dto->tecnico_id,
                    'sucursal_id' => $sucursalOrdenId,
                    'nro_factura' => (string) ($preorden->nro_factura ?? ''),
                    'nro_factura_2' => '',
                    'motivo_ingreso' => 'Validacion de Garantia',
                    'nro_sucursal_cliente' => $preorden->nro_sucursal_cliente ?: null,
                    'estado_repuesto' => 'No requerido',
                    'nro_orden' => $nroOrden,
                    'estado_garantia' => 'Pendiente',
                    'ingresado_por' => $dto->usuario_sesion_id,
                    'fecha_prometido' => $dto->fecha_prometido,
                    'valor_estandar_id' => null,
                ]);

                $this->repository->enlazarPreordenConOrden($preorden, $orden->id);

                return [
                    'nro_orden' => $nroOrden,
                    'orden_id' => $orden->id,
                    'tecnico_id' => $dto->tecnico_id,
                    'sucursal_id' => $sucursalOrdenId,
                    'motivo_ingreso' => 'Validacion de Garantia',
                ];
            } finally {
                if ($lockAdquirido) {
                    $this->repository->liberarLockSecuenciaOrden($sucursalOrdenId);
                }
            }
        });

        try {
            $ordenCompleta = \App\Models\Operations\Orden::find((int) $resultado['orden_id']);
            if ($ordenCompleta) {
                \App\Services\Operations\SgnMailService::enviarOrdenCreada($ordenCompleta);
            }
        } catch (\Throwable $e) {
            Log::error('Error al enviar notificacion de nueva orden desde preorden', ['error' => $e->getMessage()]);
        }

        return $resultado;
    }

    public function obtenerReporte(int $preordenId): ?object
    {
        return $this->repository->obtenerPreordenReporte($preordenId);
    }

    public function obtenerNumeroOrdenPorId(int $ordenId): ?string
    {
        return $this->repository->obtenerNumeroOrdenPorId($ordenId);
    }

    public function verificarPreorden(VerificarPreordenDTO $dto): ?object
    {
        return $this->repository->buscarPendientePorCiOCodigo(
            trim($dto->ci),
            trim($dto->codigo)
        );
    }

    private function generarIdentificacionTemporal(): string
    {
        return substr('0' . str_pad((string) (time() . random_int(100, 999)), 12, '0', STR_PAD_LEFT), 0, 13);
    }

    private function normalizarSerie(string $serie): string
    {
        $valor = trim($serie);
        if (
            $valor === '' ||
            preg_match('/^(s[\/\-]?n|sin[\s_\-]?(serie|numero|num)?|n[\/\-]?a|na|ninguna|none|no[\s_]?aplica|-)$/i', $valor)
        ) {
            return 'SN-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        }

        return strtoupper($valor);
    }

    private function notificarNuevaOrden(
        string $nroOrden,
        int $tecnicoId,
        int $sucursalId,
        int $usuarioSesionId,
        string $motivoIngreso
    ): void {
        try {
            $correoTecnico = $this->repository->obtenerCorreoTecnico($tecnicoId);
            $correosAdmin = $this->repository->obtenerCorreosAdministradores($sucursalId, $correoTecnico);

            if (!$correoTecnico && empty($correosAdmin)) {
                return;
            }

            $usuarioAccion = (string) (session('nombre') ?: session('usuario') ?: 'Usuario');
            if ($usuarioSesionId > 0 && $usuarioAccion === 'Usuario') {
                $usuarioAccion = 'ID ' . $usuarioSesionId;
            }

            $asunto = '[SGN] Nueva Orden (Pre-Orden): ' . $nroOrden;
            $cuerpo = view('emails.preorden_creada', [
                'nro_orden' => $nroOrden,
                'motivo_ingreso' => $motivoIngreso,
                'usuario_accion' => $usuarioAccion,
                'fecha' => now('America/Guayaquil')->format('d/m/Y H:i:s'),
            ])->render();

            $destinatario = $correoTecnico;
            $correosCc = $correosAdmin;
            if (!$destinatario && !empty($correosCc)) {
                $destinatario = array_shift($correosCc);
            }

            Mail::html($cuerpo, function ($message) use ($asunto, $destinatario, $correosCc) {
                if ($destinatario) {
                    $message->to($destinatario);
                }

                if (!empty($correosCc)) {
                    $message->cc($correosCc);
                }

                $message->subject($asunto);
            });
        } catch (\Throwable $e) {
            Log::error('Error enviando correo de preorden ingresada', [
                'nro_orden' => $nroOrden,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
