<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class TicketSolicitantesController extends Controller
{
    /**
     * Listado y administración de usuarios generadores de tickets (externos / tiendas).
     */
    public function index(Request $request)
    {
        $usuarioSesion = auth()->user() ?: Usuario::find(session('tecnico_id'));
        if (!$usuarioSesion) {
            return redirect()->route('login');
        }

        if (!$this->verificarAccesoAdmin($usuarioSesion)) {
            return redirect()->route('mistickets.index');
        }

        // Buscar grupo de generadores
        $grupoGenerador = GrupoAcceso::where('nombre', 'LIKE', '%Generador%')
            ->orWhere('nombre', 'LIKE', '%Solicitante%')
            ->first();

        $query = Usuario::with(['grupo', 'rol', 'sucursalCliente'])
            ->where(function ($q) use ($grupoGenerador) {
                if ($grupoGenerador) {
                    $q->where('grupo_id', $grupoGenerador->id);
                } else {
                    $q->where('rol_id', 1)->whereNull('correo_tec'); // Fallback
                }
            });

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('usuario', 'LIKE', "%{$q}%")
                    ->orWhere('nombre_tecnico', 'LIKE', "%{$q}%")
                    ->orWhere('telefono', 'LIKE', "%{$q}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', (int) $request->input('activo'));
        }

        $solicitantes = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $tiendasNovicompu = SucursalCliente::where('activa', 1)->orderBy('nombre')->get();

        return view('tickets.solicitantes', compact('solicitantes', 'tiendasNovicompu', 'usuarioSesion'));
    }

    /**
     * Crear un nuevo usuario generador de tickets.
     */
    public function store(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|max:50|unique:usuarios,usuario',
            'nombre_tecnico' => 'required|string|max:100',
            'clave' => 'required|string|min:4',
            'correo_tec' => 'nullable|email|max:100',
            'empresa_origen' => 'required|in:NOVICOMPU,ENV,OTRO',
            'sucursal_cliente_id' => 'required|integer',
            'telefono' => 'nullable|string|max:30',
            'usuario_mba' => 'nullable|string|max:60',
            'codigo_usuario' => 'nullable|string|max:60',
            'anydesk_id' => 'nullable|string|max:50',
        ]);

        try {
            // Asegurar grupo 'Generador de Tickets (Tiendas)'
            $grupo = GrupoAcceso::firstOrCreate(
                ['nombre' => 'Generador de Tickets (Tiendas)'],
                ['es_superadmin' => 0]
            );

            $usuario = Usuario::create([
                'usuario' => trim($request->input('usuario')),
                'nombre_tecnico' => trim($request->input('nombre_tecnico')),
                'clave_hash' => Hash::make($request->input('clave')),
                'clave' => '', // Legacy
                'correo_tec' => $request->input('correo_tec') ? trim($request->input('correo_tec')) : null,
                'telefono' => $request->input('telefono'),
                'grupo_id' => $grupo->id,
                'rol_id' => 1, // Rol básico
                'sucursal_id' => 1, // Default Quito
                'sucursal_cliente_id' => (int) $request->input('sucursal_cliente_id'),
                'empresa_origen' => $request->input('empresa_origen', 'NOVICOMPU'),
                'usuario_mba' => $request->input('usuario_mba') ? trim($request->input('usuario_mba')) : null,
                'codigo_usuario' => $request->input('codigo_usuario') ? trim($request->input('codigo_usuario')) : null,
                'anydesk_id' => $request->input('anydesk_id') ? trim($request->input('anydesk_id')) : null,
                'activo' => 1,
                'acceso_nc' => 0,
            ]);

            // Asignar permisos básicos para tickets
            DB::table('permisosusuario')->updateOrInsert(
                ['usuario_id' => $usuario->id, 'modulo' => 'tickets', 'accion' => 'ver'],
                ['permitido' => 1]
            );
            DB::table('permisosusuario')->updateOrInsert(
                ['usuario_id' => $usuario->id, 'modulo' => 'tickets', 'accion' => 'crear'],
                ['permitido' => 1]
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'mensaje' => "Usuario solicitante '{$usuario->usuario}' creado con éxito."]);
            }

            return back()->with('success', "Usuario solicitante '{$usuario->usuario}' creado con éxito.");
        } catch (Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => 'Error al crear usuario: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos, tienda, empresa, correo, AnyDesk, MBA, contraseña o estado del usuario solicitante.
     */
    public function update(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre_tecnico' => 'required|string|max:100',
            'correo_tec' => 'nullable|email|max:100',
            'empresa_origen' => 'required|in:NOVICOMPU,ENV,OTRO',
            'sucursal_cliente_id' => 'required|integer',
            'telefono' => 'nullable|string|max:30',
            'usuario_mba' => 'nullable|string|max:60',
            'codigo_usuario' => 'nullable|string|max:60',
            'anydesk_id' => 'nullable|string|max:50',
            'clave' => 'nullable|string|min:4',
            'activo' => 'required|in:0,1',
        ]);

        try {
            $data = [
                'nombre_tecnico' => trim($request->input('nombre_tecnico')),
                'correo_tec' => $request->input('correo_tec') ? trim($request->input('correo_tec')) : null,
                'empresa_origen' => $request->input('empresa_origen'),
                'sucursal_cliente_id' => (int) $request->input('sucursal_cliente_id'),
                'telefono' => $request->input('telefono'),
                'usuario_mba' => $request->input('usuario_mba') ? trim($request->input('usuario_mba')) : null,
                'codigo_usuario' => $request->input('codigo_usuario') ? trim($request->input('codigo_usuario')) : null,
                'anydesk_id' => $request->input('anydesk_id') ? trim($request->input('anydesk_id')) : null,
                'activo' => (int) $request->input('activo'),
            ];

            if ($request->filled('clave')) {
                $data['clave_hash'] = Hash::make($request->input('clave'));
                $data['clave'] = '';
            }

            $usuario->update($data);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'mensaje' => 'Usuario actualizado correctamente.']);
            }

            return back()->with('success', 'Usuario actualizado correctamente.');
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    private function verificarAccesoAdmin(Usuario $usuario): bool
    {
        $sa = (bool) session('es_superadmin', false);
        $rolNombre = mb_strtolower(trim((string) ($usuario->rol?->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) ($usuario->grupo?->nombre ?? '')));
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        if ($sa || (bool)($usuario->grupo?->es_superadmin ?? false)) {
            return true;
        }

        if (in_array($rolNombre, ['admin master', 'administrador master', 'admin', 'administrador'], true)) {
            return true;
        }

        if (in_array($grupoNombre, ['admin master', 'administrador master', 'superadministrador', 'admin', 'administrador'], true)) {
            return true;
        }

        if (in_array($sessionGrupo, ['admin master', 'administrador master', 'superadministrador', 'admin', 'administrador'], true)) {
            return true;
        }

        return false;
    }
}
