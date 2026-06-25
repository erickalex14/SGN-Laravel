<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Services\Identity\ActividadDiariaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActividadDiariaController extends Controller
{
    protected ActividadDiariaService $service;

    public function __construct(ActividadDiariaService $service)
    {
        $this->service = $service;
    }

    /**
     * Vista de mis actividades (Técnico).
     */
    public function index(): View
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->debeLlenarActividades()) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $fechaHoy = Carbon::now('America/Guayaquil')->toDateString();
        $nombreTecnico = session('nombre') ?? session('usuario') ?? 'Técnico';
        return view('identity.actividades.index', compact('fechaHoy', 'nombreTecnico'));
    }

    /**
     * Listar mis actividades en formato JSON.
     */
    public function listar(Request $request): JsonResponse
    {
        $tecnicoId = (int) session('tecnico_id', 0);
        if ($tecnicoId === 0) {
            return response()->json(['ok' => false, 'error' => 'Sesión no identificada.'], 403);
        }

        $usuario = auth()->user();
        if (!$usuario || !$usuario->debeLlenarActividades()) {
            return response()->json(['ok' => false, 'error' => 'No tienes permiso para registrar actividades.'], 403);
        }

        $fecha = $request->query('fecha') ?: Carbon::now('America/Guayaquil')->toDateString();
        if ($fecha < '2026-06-25') {
            $fecha = '2026-06-25';
        }
        $actividades = $this->service->obtenerActividadesDelDia($tecnicoId, $fecha);

        return response()->json([
            'ok' => true,
            'fecha' => $fecha,
            'actividades' => $actividades
        ]);
    }

    /**
     * Vista de gestión de actividades (Admin/Admin Master).
     */
    public function indexAdmin(): View
    {
        $fechaHoy = Carbon::now('America/Guayaquil')->toDateString();
        $tecnicos = $this->service->obtenerTecnicosActivos();
        return view('identity.actividades.admin', compact('fechaHoy', 'tecnicos'));
    }

    /**
     * Listar actividades de un técnico para administración en formato JSON.
     */
    public function listarAdmin(Request $request): JsonResponse
    {
        $tecnicoId = (int) $request->query('tecnico_id', 0);
        if ($tecnicoId === 0) {
            return response()->json(['ok' => false, 'error' => 'Debe seleccionar un técnico.'], 422);
        }

        $fecha = $request->query('fecha') ?: Carbon::now('America/Guayaquil')->toDateString();
        if ($fecha < '2026-06-25') {
            $fecha = '2026-06-25';
        }
        $actividades = $this->service->obtenerActividadesDelDia($tecnicoId, $fecha);

        return response()->json([
            'ok' => true,
            'fecha' => $fecha,
            'actividades' => $actividades
        ]);
    }

    /**
     * Vista de historial de actividades para el técnico.
     */
    public function historial(): View
    {
        $usuario = auth()->user();
        if (!$usuario || !$usuario->debeLlenarActividades()) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $fechaHoy = Carbon::now('America/Guayaquil')->toDateString();
        $nombreTecnico = session('nombre') ?? session('usuario') ?? 'Técnico';
        return view('identity.actividades.historial', compact('fechaHoy', 'nombreTecnico'));
    }

    /**
     * Guardar modificaciones de actividades diarias de hoy.
     */
    public function guardar(Request $request): JsonResponse
    {
        $tecnicoId = (int) session('tecnico_id', 0);
        if ($tecnicoId === 0) {
            return response()->json(['ok' => false, 'error' => 'Sesión no identificada.'], 403);
        }

        $usuario = auth()->user();
        if (!$usuario || !$usuario->debeLlenarActividades()) {
            return response()->json(['ok' => false, 'error' => 'No tienes permiso para registrar actividades.'], 403);
        }

        $fecha = $request->input('fecha');
        $now = Carbon::now('America/Guayaquil');
        $fechaHoy = $now->toDateString();

        if ($fecha !== $fechaHoy) {
            return response()->json(['ok' => false, 'error' => 'Solo se permite editar las actividades del día de hoy.'], 403);
        }

        // Limit: allow editing only until 6:30 PM (18:30) of today
        if ($now->hour > 18 || ($now->hour === 18 && $now->minute > 30)) {
            return response()->json(['ok' => false, 'error' => 'La edición de actividades está permitida solo hasta las 6:30 PM de hoy.'], 403);
        }

        $actividades = $request->input('actividades', []);
        if (!is_array($actividades)) {
            return response()->json(['ok' => false, 'error' => 'Datos de entrada no válidos.'], 422);
        }

        foreach ($actividades as $hora => $data) {
            $horaInt = (int) $hora;
            if ($horaInt >= 9 && $horaInt <= 17) {
                $this->service->guardarRegistroManual($tecnicoId, $fechaHoy, $horaInt, $data);
            }
        }

        return response()->json([
            'ok' => true,
            'mensaje' => 'Actividades de hoy guardadas correctamente.'
        ]);
    }
}
