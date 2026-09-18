<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Models\Directory\Sucursal;
use App\Models\Identity\DatosNomina;
use App\Models\Identity\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NominaController extends Controller
{
    /**
     * Verifica si el usuario autenticado posee permisos de Admin Master / Superadmin.
     */
    private function esSuperAdminOMaster(): bool
    {
        $usuario = auth()->user();
        if (!$usuario) return false;

        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));
        $rol = $usuario->rol ? mb_strtolower(trim((string) $usuario->rol->rol)) : '';
        $grupo = $usuario->grupo ? mb_strtolower(trim((string) $usuario->grupo->nombre)) : '';

        $superRoles = [
            'admin master', 'administrador master', 'superadmin', 'superadministrador', 'master', 'admin'
        ];

        return session('es_superadmin') === true
            || in_array($rol, $superRoles, true)
            || in_array($grupo, $superRoles, true)
            || in_array($sessionGrupo, $superRoles, true);
    }

    /**
     * Vista de Mis Datos Personales / Nómina para el usuario autenticado.
     */
    public function misDatos()
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        if ((int)$usuario->grupo_id === 6 || mb_strtolower($usuario->grupo?->nombre ?? '') === 'admin solo lectura') {
            return redirect()->route('mis_ordenes.index')->with('info', 'Tu usuario de lectura externa no pertenece al registro de nómina.');
        }

        $datosNomina = DatosNomina::firstOrCreate(
            ['usuario_id' => $usuario->id],
            [
                'nombres_completos' => $usuario->nombre_tecnico ?? $usuario->usuario,
                'cedula' => $usuario->usuario,
                'telefono' => null,
                'email_personal' => null,
                'estado_afiliacion' => 'Por Afiliar',
                'sueldo_base' => 0.00,
                'bonificaciones' => 0.00,
                'sanciones' => 0.00,
                'total_a_recibir' => 0.00,
            ]
        );

        $solicitudesVacaciones = \App\Models\Identity\SolicitudVacacion::where('usuario_id', $usuario->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('identity.nomina.mis_datos', [
            'usuario' => $usuario,
            'datosNomina' => $datosNomina,
            'solicitudesVacaciones' => $solicitudesVacaciones,
            'esMaster' => $this->esSuperAdminOMaster(),
        ]);
    }

    /**
     * Actualiza los datos de contacto personales del usuario (Foto, Hoja de vida, Teléfono, etc.)
     */
    public function guardarMisDatos(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        if ((int)$usuario->grupo_id === 6 || mb_strtolower($usuario->grupo?->nombre ?? '') === 'admin solo lectura') {
            return redirect()->route('mis_ordenes.index')->with('info', 'Tu usuario de lectura externa no pertenece al registro de nómina.');
        }

        $request->validate([
            'nombres_completos' => 'nullable|string|max:255',
            'cedula' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email_personal' => 'nullable|email|max:255',
            'contacto_emergencia' => 'nullable|string|max:1000',
            'foto_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hoja_vida_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $datosNomina = DatosNomina::firstOrCreate(['usuario_id' => $usuario->id]);

        $datosNomina->nombres_completos = trim($request->input('nombres_completos') ?? '');
        $datosNomina->cedula = trim($request->input('cedula') ?? '');
        $datosNomina->telefono = trim($request->input('telefono') ?? '');
        $datosNomina->email_personal = trim($request->input('email_personal') ?? '');
        $datosNomina->contacto_emergencia = trim($request->input('contacto_emergencia') ?? '');

        // Procesar foto de perfil
        if ($request->hasFile('foto_file')) {
            $file = $request->file('foto_file');
            $filename = 'foto_' . $usuario->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/nomina/fotos', $filename);
            $datosNomina->foto_url = Storage::url($path);
        }

        // Procesar hoja de vida
        if ($request->hasFile('hoja_vida_file')) {
            $file = $request->file('hoja_vida_file');
            $filename = 'cv_' . $usuario->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/nomina/hojas_vida', $filename);
            $datosNomina->hoja_vida_url = Storage::url($path);
        }

        $datosNomina->save();

        return redirect()->back()->with('success', 'Tus datos personales y archivos de nómina han sido actualizados correctamente.');
    }

    /**
     * Dashboard de Administración de Nómina (Exclusivo Admin Master / Superadmin).
     */
    public function adminIndex(Request $request)
    {
        if (!$this->esSuperAdminOMaster()) {
            abort(403, 'No posees permisos de Admin Master para acceder a la Nómina general.');
        }

        $query = Usuario::with(['datosNomina', 'sucursalPrincipal', 'rol', 'grupo'])
            ->where('activo', 1)
            ->where(function($sub) {
                $sub->whereNull('grupo_id')
                    ->orWhere('grupo_id', '!=', 6);
            })
            ->whereDoesntHave('grupo', function($g) {
                $g->whereRaw('LOWER(nombre) = ?', ['admin solo lectura']);
            });

        // Filtro de búsqueda
        if ($request->filled('buscar')) {
            $b = trim($request->input('buscar'));
            $query->where(function ($sub) use ($b) {
                $sub->where('usuario', 'like', "%{$b}%")
                    ->orWhere('nombre_tecnico', 'like', "%{$b}%")
                    ->orWhereHas('datosNomina', function ($q) use ($b) {
                        $q->where('nombres_completos', 'like', "%{$b}%")
                          ->orWhere('cedula', 'like', "%{$b}%");
                    });
            });
        }

        // Filtro por sucursal
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', (int)$request->input('sucursal_id'));
        }

        $usuarios = $query->orderBy('id', 'asc')->get();

        // Asegurar que cada usuario tenga su registro de datos_nomina
        foreach ($usuarios as $u) {
            if (!$u->datosNomina) {
                $u->datosNomina = DatosNomina::create([
                    'usuario_id' => $u->id,
                    'nombres_completos' => $u->nombre_tecnico ?? $u->usuario,
                    'cedula' => $u->usuario,
                    'telefono' => null,
                    'email_personal' => null,
                    'estado_afiliacion' => 'Por Afiliar',
                    'sueldo_base' => 0.00,
                    'bonificaciones' => 0.00,
                    'sanciones' => 0.00,
                    'total_a_recibir' => 0.00,
                ]);
            }
        }

        // Cálculos métricos de nómina
        $totalEmpleados = $usuarios->count();
        $totalSueldoBase = $usuarios->sum(fn($u) => (float)($u->datosNomina->sueldo_base ?? 0));
        $totalBonificaciones = $usuarios->sum(fn($u) => (float)($u->datosNomina->bonificaciones ?? 0));
        $totalSanciones = $usuarios->sum(fn($u) => (float)($u->datosNomina->sanciones ?? 0));
        $totalNeto = $usuarios->sum(fn($u) => (float)($u->datosNomina->total_a_recibir ?? 0));

        $solicitudesVacaciones = \App\Models\Identity\SolicitudVacacion::with(['usuario', 'datosNomina'])
            ->orderBy('id', 'desc')
            ->get();

        $sucursales = Sucursal::orderBy('ciudad')->get();

        return view('identity.nomina.admin', compact(
            'usuarios',
            'sucursales',
            'totalEmpleados',
            'totalSueldoBase',
            'totalBonificaciones',
            'totalSanciones',
            'totalNeto',
            'solicitudesVacaciones'
        ));
    }

    /**
     * Guarda / Actualiza los datos de nómina de cualquier usuario (Admin Master).
     */
    public function guardarDatosNominaAdmin(Request $request, $usuarioId)
    {
        if (!$this->esSuperAdminOMaster()) {
            return response()->json(['ok' => false, 'error' => 'No autorizado.'], 403);
        }

        $usuario = Usuario::findOrFail($usuarioId);

        $request->validate([
            'nombres_completos' => 'nullable|string|max:255',
            'cedula' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email_personal' => 'nullable|email|max:255',
            'contacto_emergencia' => 'nullable|string|max:1000',
            'fecha_ingreso' => 'nullable|date',
            'fecha_salida' => 'nullable|date',
            'estado_afiliacion' => 'nullable|string|max:100',
            'sueldo_base' => 'required|numeric|min:0',
            'bonificaciones' => 'nullable|numeric|min:0',
            'sanciones' => 'nullable|numeric|min:0',
            'foto_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hoja_vida_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $datosNomina = DatosNomina::firstOrCreate(['usuario_id' => $usuario->id]);

        $datosNomina->nombres_completos = trim($request->input('nombres_completos') ?? '');
        $datosNomina->cedula = trim($request->input('cedula') ?? '');
        $datosNomina->cargo = trim($request->input('cargo') ?? '');
        $datosNomina->telefono = trim($request->input('telefono') ?? '');
        $datosNomina->email_personal = trim($request->input('email_personal') ?? '');
        $datosNomina->contacto_emergencia = trim($request->input('contacto_emergencia') ?? '');

        $datosNomina->fecha_ingreso = $request->input('fecha_ingreso') ?: null;
        $datosNomina->fecha_salida = $request->input('fecha_salida') ?: null;
        $datosNomina->estado_afiliacion = trim($request->input('estado_afiliacion') ?? 'Por Afiliar');

        $datosNomina->sueldo_base = (float)$request->input('sueldo_base', 0.00);
        $datosNomina->bonificaciones = (float)$request->input('bonificaciones', 0.00);
        $datosNomina->sanciones = (float)$request->input('sanciones', 0.00);
        $datosNomina->recargarTotalARecibir();

        // Procesar foto
        if ($request->hasFile('foto_file')) {
            $file = $request->file('foto_file');
            $filename = 'foto_' . $usuario->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/nomina/fotos', $filename);
            $datosNomina->foto_url = Storage::url($path);
        }

        // Procesar hoja de vida
        if ($request->hasFile('hoja_vida_file')) {
            $file = $request->file('hoja_vida_file');
            $filename = 'cv_' . $usuario->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/nomina/hojas_vida', $filename);
            $datosNomina->hoja_vida_url = Storage::url($path);
        }

        $datosNomina->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'mensaje' => 'Datos de nómina actualizados correctamente.',
                'datos' => $datosNomina
            ]);
        }

        return redirect()->back()->with('success', "Datos de nómina para {$usuario->usuario} actualizados con éxito.");
    }

    /**
     * Registra una nueva solicitud de vacaciones por parte del empleado.
     */
    public function solicitarVacaciones(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'dias_solicitados' => 'required|integer|min:1|max:60',
            'observacion_empleado' => 'nullable|string|max:1000',
        ]);

        $datosNomina = DatosNomina::firstOrCreate(['usuario_id' => $usuario->id]);

        $solicitud = \App\Models\Identity\SolicitudVacacion::create([
            'usuario_id' => $usuario->id,
            'datos_nomina_id' => $datosNomina->id,
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'dias_solicitados' => (int)$request->input('dias_solicitados'),
            'observacion_empleado' => trim($request->input('observacion_empleado') ?? ''),
            'estado' => 'Pendiente',
        ]);

        return redirect()->back()->with('success', 'Tu solicitud de vacaciones por ' . $solicitud->dias_solicitados . ' día(s) fue enviada con éxito para revisión del Admin Master.');
    }

    /**
     * Aprueba o Ajusta una solicitud de vacaciones (Admin Master).
     */
    public function aprobarVacaciones(Request $request, $id)
    {
        if (!$this->esSuperAdminOMaster()) {
            return response()->json(['ok' => false, 'error' => 'No autorizado.'], 403);
        }

        $solicitud = \App\Models\Identity\SolicitudVacacion::findOrFail($id);

        $request->validate([
            'dias_aprobados' => 'required|integer|min:1|max:60',
            'fecha_inicio_aprobada' => 'nullable|date',
            'fecha_fin_aprobada' => 'nullable|date',
            'observacion_admin' => 'nullable|string|max:1000',
        ]);

        $solicitud->estado = 'Aprobado';
        $solicitud->dias_aprobados = (int)$request->input('dias_aprobados');
        $solicitud->fecha_inicio_aprobada = $request->input('fecha_inicio_aprobada') ?: $solicitud->fecha_inicio;
        $solicitud->fecha_fin_aprobada = $request->input('fecha_fin_aprobada') ?: $solicitud->fecha_fin;
        $solicitud->observacion_admin = trim($request->input('observacion_admin') ?? '');
        $solicitud->aprobado_por = auth()->id();
        $solicitud->fecha_aprobacion = now();
        $solicitud->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'mensaje' => 'Solicitud de vacaciones aprobada correctamente.',
                'solicitud' => $solicitud
            ]);
        }

        return redirect()->back()->with('success', 'Solicitud de vacaciones aprobada con éxito.');
    }

    /**
     * Rechaza una solicitud de vacaciones (Admin Master).
     */
    public function rechazarVacaciones(Request $request, $id)
    {
        if (!$this->esSuperAdminOMaster()) {
            return response()->json(['ok' => false, 'error' => 'No autorizado.'], 403);
        }

        $solicitud = \App\Models\Identity\SolicitudVacacion::findOrFail($id);

        $solicitud->estado = 'Rechazado';
        $solicitud->observacion_admin = trim($request->input('observacion_admin') ?? 'Solicitud no aprobada por la administración.');
        $solicitud->aprobado_por = auth()->id();
        $solicitud->fecha_aprobacion = now();
        $solicitud->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'mensaje' => 'Solicitud de vacaciones rechazada.',
                'solicitud' => $solicitud
            ]);
        }

        return redirect()->back()->with('success', 'Solicitud de vacaciones rechazada.');
    }

    /**
     * Muestra la vista limpia e imprimible (Comprobante PDF / Imprimible) de una solicitud de vacaciones.
     */
    public function imprimirSolicitudVacaciones($id)
    {
        $usuario = auth()->user();
        if (!$usuario) {
            return redirect()->route('login');
        }

        $solicitud = \App\Models\Identity\SolicitudVacacion::with(['usuario', 'datosNomina', 'aprobador'])->findOrFail($id);

        if ($solicitud->usuario_id !== $usuario->id && !$this->esSuperAdminOMaster()) {
            abort(403, 'No tienes permiso para ver esta solicitud.');
        }

        return view('identity.nomina.imprimir_solicitud_vacaciones', [
            'solicitud' => $solicitud,
            'datosNomina' => $solicitud->datosNomina,
            'empleado' => $solicitud->usuario,
            'aprobador' => $solicitud->aprobador,
        ]);
    }

    /**
     * Exporta el Rol de Pagos Mensual en formato Excel (.csv / .xls download).
     */
    public function exportarExcel(Request $request)
    {
        if (!$this->esSuperAdminOMaster()) {
            abort(403, 'Acceso denegado.');
        }

        $query = Usuario::with(['datosNomina', 'sucursalPrincipal', 'rol', 'grupo'])
            ->where('activo', 1);

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', (int)$request->input('sucursal_id'));
        }

        $usuarios = $query->orderBy('id', 'asc')->get();

        $filename = "rol_de_pagos_nomina_" . date('Y_m_d_H_i') . ".xls";

        $headers = [
            "Content-Type" => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $html = view('identity.nomina.excel_rol_pagos', [
            'usuarios' => $usuarios,
            'fechaEmision' => date('d/m/Y H:i')
        ])->render();

        return response($html, 200, $headers);
    }
}
