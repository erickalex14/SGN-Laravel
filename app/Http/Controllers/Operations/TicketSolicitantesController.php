<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Directory\SucursalCliente;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Usuario;
use App\Services\Operations\TicketMailService;
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
        $grupo = GrupoAcceso::where('nombre', 'Generador de Tickets (Tiendas)')->first();
        $grupoId = $grupo ? $grupo->id : null;

        $q = trim($request->input('q', ''));
        $activo = $request->input('activo');

        $query = Usuario::query()
            ->with('sucursalCliente')
            ->where('grupo_id', $grupoId);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('usuario', 'LIKE', "%{$q}%")
                    ->orWhere('nombre_tecnico', 'LIKE', "%{$q}%")
                    ->orWhere('correo_tec', 'LIKE', "%{$q}%")
                    ->orWhere('telefono', 'LIKE', "%{$q}%")
                    ->orWhere('usuario_mba', 'LIKE', "%{$q}%")
                    ->orWhere('codigo_usuario', 'LIKE', "%{$q}%")
                    ->orWhere('anydesk_id', 'LIKE', "%{$q}%")
                    ->orWhereHas('sucursalCliente', function ($sc) use ($q) {
                        $sc->where('nombre', 'LIKE', "%{$q}%")
                            ->orWhere('codigo', 'LIKE', "%{$q}%");
                    });
            });
        }

        if ($activo !== null && $activo !== '') {
            $query->where('activo', (int) $activo);
        }

        $solicitantes = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();
        $tiendasNovicompu = SucursalCliente::where('activa', 1)->orderBy('codigo')->get();

        return view('tickets.solicitantes', compact('solicitantes', 'tiendasNovicompu'));
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
            'departamento' => 'nullable|string|max:100',
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

            $clavePlana = trim($request->input('clave'));

            $usuario = Usuario::create([
                'usuario' => trim($request->input('usuario')),
                'nombre_tecnico' => trim($request->input('nombre_tecnico')),
                'clave_hash' => Hash::make($clavePlana),
                'clave' => '', // Legacy
                'correo_tec' => $request->input('correo_tec') ? trim($request->input('correo_tec')) : null,
                'telefono' => $request->input('telefono'),
                'grupo_id' => $grupo->id,
                'rol_id' => 1, // Rol básico
                'sucursal_id' => 1, // Default Quito
                'sucursal_cliente_id' => (int) $request->input('sucursal_cliente_id'),
                'empresa_origen' => $request->input('empresa_origen', 'NOVICOMPU'),
                'departamento' => $request->input('departamento') ? trim($request->input('departamento')) : null,
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

            // Enviar credenciales al correo institucional registrado
            if ($usuario->correo_tec) {
                TicketMailService::enviarCredencialesSolicitante($usuario, $clavePlana);
            }

            $mensajeExito = "Usuario solicitante '{$usuario->usuario}' creado con éxito" . ($usuario->correo_tec ? " y credenciales enviadas a {$usuario->correo_tec}." : ".");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'mensaje' => $mensajeExito]);
            }

            return back()->with('success', $mensajeExito);
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
            'departamento' => 'nullable|string|max:100',
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
                'departamento' => $request->input('departamento') ? trim($request->input('departamento')) : null,
                'sucursal_cliente_id' => (int) $request->input('sucursal_cliente_id'),
                'telefono' => $request->input('telefono'),
                'usuario_mba' => $request->input('usuario_mba') ? trim($request->input('usuario_mba')) : null,
                'codigo_usuario' => $request->input('codigo_usuario') ? trim($request->input('codigo_usuario')) : null,
                'anydesk_id' => $request->input('anydesk_id') ? trim($request->input('anydesk_id')) : null,
                'activo' => (int) $request->input('activo'),
            ];

            $claveCambiada = false;
            $nuevaClavePlana = '';
            if ($request->filled('clave')) {
                $nuevaClavePlana = trim($request->input('clave'));
                $data['clave_hash'] = Hash::make($nuevaClavePlana);
                $data['clave'] = '';
                $claveCambiada = true;
            }

            $usuario->update($data);

            // Si se cambió la contraseña y tiene correo, enviar notificación de credenciales actualizadas
            if ($claveCambiada && ($usuario->correo_tec || $data['correo_tec'])) {
                $usuarioMail = clone $usuario;
                $usuarioMail->fill($data);
                TicketMailService::enviarCredencialesSolicitante($usuarioMail, $nuevaClavePlana);
            }

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
