<?php

namespace App\Http\Controllers\Operations;

use App\DTOs\Operations\PresupuestoContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Operations\PresupuestoService;
use Illuminate\View\View;

class PresupuestoController extends Controller
{
    protected PresupuestoService $service;

    public function __construct(PresupuestoService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $permisos = (array) session('permisos', []);
        $esAdminPorPermiso = (($permisos['usuarios']['crear'] ?? false) === true)
            || (($permisos['usuarios_crear']['ver'] ?? false) === true);

        $contexto = new PresupuestoContextDTO(
            (int) session('tecnico_id', 0),
            (int) session('sucursal_id', 0),
            (bool) session('es_superadmin', false) || $esAdminPorPermiso,
            (($permisos['ordenes_asignadas']['ver'] ?? false) === true)
        );

        $data = $this->service->obtenerContextoIndex($contexto);

        return view('operations.presupuestos.index', [
            'ordenes' => $data['ordenes'],
            'catalogo' => $data['catalogo'],
        ]);
    }
}
