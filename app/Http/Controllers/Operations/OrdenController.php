<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\GuardarOrdenRequest;
use App\Services\Operations\CrearOrdenService;
use App\Repositories\Directory\ClienteRepository;
use App\Repositories\Identity\UsuarioRepository;
use App\Repositories\Operations\TipoServicioRepository;
use App\DTOs\Operations\CrearOrdenDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Exception;

class OrdenController extends Controller
{
    protected CrearOrdenService $service;
    protected ClienteRepository $clienteRepo;
    protected UsuarioRepository $usuarioRepo;
    protected TipoServicioRepository $tipoServicioRepo;

    public function __construct(
        CrearOrdenService $service,
        ClienteRepository $clienteRepo,
        UsuarioRepository $usuarioRepo,
        TipoServicioRepository $tipoServicioRepo
    ) {
        $this->service = $service;
        $this->clienteRepo = $clienteRepo;
        $this->usuarioRepo = $usuarioRepo;
        $this->tipoServicioRepo = $tipoServicioRepo;
    }

    public function create(): View
    {
        // Cargamos tecnicos activos y tipos de servicio para los selects
        $tecnicos = $this->usuarioRepo->obtenerTodosConRelaciones()->where('activo', 1);
        $tiposServicio = $this->tipoServicioRepo->obtenerTodos()->where('activo', 1);

        return view('operations.ordenes.crear', compact('tecnicos', 'tiposServicio'));
    }

    public function store(GuardarOrdenRequest $request): JsonResponse
    {
        try {
            $dto = new CrearOrdenDTO(
                null, // cliente_id se resuelve en el service
                $request->input('cli_identificacion'),
                $request->input('cli_nombres'),
                $request->input('cli_apellidos'),
                $request->input('cli_telefono'),
                $request->input('cli_correo'),
                $request->input('cli_direccion'),
                
                $request->input('eq_tipo'),
                $request->input('eq_marca'),
                $request->input('eq_modelo'),
                $request->input('eq_serie'),
                $request->input('eq_contrasena'),
                $request->input('eq_falla'),
                $request->input('eq_observacion'),
                $request->input('eq_tipo_servicio') ? (int)$request->input('eq_tipo_servicio') : null,
                null, null,
                
                session('sucursal_id'), // Extraido directo de la sesion del usuario logueado
                (int) $request->input('ord_tecnico_id'),
                session('tecnico_id'), // Usuario que registra
                Carbon::now('America/Guayaquil')->format('Y-m-d H:i:s'), // Forzamos timezone legacy
                $request->input('ord_motivo')
            );

            $orden = $this->service->crearOrden($dto);

            return response()->json([
                'ok' => true, 
                'mensaje' => 'Orden ' . $orden->nro_orden . ' generada con éxito.',
                'nro_orden' => $orden->nro_orden,
                'orden_id' => $orden->id
            ]);

        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // Endpoint AJAX para autocompletar datos del cliente al digitar la cedula
    public function buscarCliente(Request $request): JsonResponse
    {
        $identificacion = $request->query('identificacion');
        if (!$identificacion) return response()->json(['ok' => false]);

        $cliente = $this->clienteRepo->buscarPorIdentificacion($identificacion);
        
        if ($cliente) {
            return response()->json(['ok' => true, 'cliente' => $cliente]);
        }

        return response()->json(['ok' => false, 'error' => 'Cliente no encontrado']);
    }
}