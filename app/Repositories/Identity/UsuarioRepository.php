<?php

namespace App\Repositories\Identity;

use App\Models\Identity\Usuario;
use App\Models\Identity\PermisoUsuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UsuarioRepository
{
    /**
     * Busca un usuario validando credenciales de forma compatible con el sistema legacy.
     */
    public function encontrarPorCredencialesLegadas(string $usuario, string $clave): ?Usuario
    {
        return Usuario::with(['grupo', 'sucursalesAsignadas', 'permisos'])
            ->where(function ($query) use ($usuario) {
                $query->where('usuario', $usuario)
                      ->orWhere('correo_tec', $usuario);
            })
            ->where('clave', $clave)
            ->first();
    }

    // Método para obtener todos los usuarios con sus relaciones, ordenados por nombre de usuario
    public function obtenerTodosConRelaciones(): Collection
    {
        return Usuario::with(['rol', 'grupo', 'sucursalPrincipal'])->orderBy('usuario', 'asc')->get();
    }

    /**
     * Lista tecnicos activos con su carga operativa actual.
     * Carga = ordenes en estado Pendiente o En proceso.
     */
    public function obtenerTecnicosConCargaActual(bool $verTodos = true, ?int $sucursalId = null, ?int $tecnicoActualId = null): Collection
    {
        $casIds = [];
        if ($tecnicoActualId) {
            $casIds = DB::table('usuariocas')
                ->where('usuario_id', $tecnicoActualId)
                ->pluck('cas_id')
                ->all();
        }

        return Usuario::query()
            ->from('usuarios as u')
            ->leftJoin('roles as r', 'u.rol_id', '=', 'r.id')
            ->leftJoin('ordenes as o', 'o.tecnico_id', '=', 'u.id')
            ->where('u.activo', 1)
            ->whereNotNull('u.nombre_tecnico')
            ->when(!$verTodos, function ($query) use ($sucursalId, $tecnicoActualId, $casIds) {
                if (!empty($casIds)) {
                    $query->where(function ($q) use ($casIds, $tecnicoActualId) {
                        $q->whereIn('u.id', function ($sub) use ($casIds) {
                            $sub->select('usuario_id')
                                ->from('usuariocas')
                                ->whereIn('cas_id', $casIds);
                        });
                        if ($tecnicoActualId) {
                            $q->orWhere('u.id', $tecnicoActualId);
                        }
                    });
                } elseif ($sucursalId) {
                    $query->where(function ($q) use ($sucursalId, $tecnicoActualId) {
                        $q->where('u.sucursal_id', $sucursalId);
                        if ($tecnicoActualId) {
                            $q->orWhere('u.id', $tecnicoActualId);
                        }
                    });
                }
            })
            ->when($verTodos, function ($query) use ($sucursalId, $tecnicoActualId, $casIds) {
                $esSuperadmin = (bool) session('es_superadmin', false);
                if (!$esSuperadmin) {
                    if (!empty($casIds)) {
                        $query->where(function ($q) use ($casIds, $tecnicoActualId) {
                            $q->whereIn('u.id', function ($sub) use ($casIds) {
                                $sub->select('usuario_id')
                                    ->from('usuariocas')
                                    ->whereIn('cas_id', $casIds);
                            });
                            if ($tecnicoActualId) {
                                $q->orWhere('u.id', $tecnicoActualId);
                            }
                        });
                    } elseif ($sucursalId) {
                        $query->where(function ($q) use ($sucursalId, $tecnicoActualId) {
                            $q->where('u.sucursal_id', $sucursalId);
                            if ($tecnicoActualId) {
                                $q->orWhere('u.id', $tecnicoActualId);
                            }
                        });
                    }
                }
            })
            ->selectRaw(
                "u.id, u.nombre_tecnico,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(o.estado_orden, ''))) = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(o.estado_orden, ''))) IN ('EN PROCESO', 'EN_PROCESO') THEN 1 ELSE 0 END) as en_proceso"
            )
            ->groupBy('u.id', 'u.nombre_tecnico')
            ->orderByRaw('(pendientes + en_proceso) ASC')
            ->orderBy('u.nombre_tecnico')
            ->get();
    }

    public function tecnicoAsignable(int $tecnicoId, bool $verTodos = true, ?int $sucursalId = null, ?int $tecnicoActualId = null): bool
    {
        $casIds = [];
        if ($tecnicoActualId) {
            $casIds = DB::table('usuariocas')
                ->where('usuario_id', $tecnicoActualId)
                ->pluck('cas_id')
                ->all();
        }

        return Usuario::query()
            ->from('usuarios as u')
            ->where('u.id', $tecnicoId)
            ->where('u.activo', 1)
            ->where(function ($query) use ($verTodos, $sucursalId, $tecnicoActualId, $casIds) {
                $esSuperadmin = (bool) session('es_superadmin', false);
                if ($esSuperadmin && $verTodos) {
                    return;
                }

                if (!empty($casIds)) {
                    $query->whereIn('u.id', function ($sub) use ($casIds) {
                        $sub->select('usuario_id')
                            ->from('usuariocas')
                            ->whereIn('cas_id', $casIds);
                    });
                    if ($tecnicoActualId) {
                        $query->orWhere('u.id', $tecnicoActualId);
                    }
                } elseif ($sucursalId) {
                    $query->where('u.sucursal_id', $sucursalId);
                    if ($tecnicoActualId) {
                        $query->orWhere('u.id', $tecnicoActualId);
                    }
                }
            })
            ->exists();
    }

    // Metodo para verificar si un nombre de usuario ya existe, excluyendo un ID específico (útil para actualizaciones)
    public function existeUsuario(string $usuario, ?int $excluirId = null): bool
    {
        $query = Usuario::where('usuario', $usuario);
        if ($excluirId) $query->where('id', '!=', $excluirId);
        return $query->exists();
    }

    // Metodo para buscar un usuario por su ID
    public function buscarPorId(int $id): ?Usuario
    {
        return Usuario::with(['sucursalesAsignadas', 'casAsignados', 'permisos'])->find($id);
    }

    // Metodo para sincronizar relaciones con las tablas pivote
    public function sincronizarRelaciones(Usuario $usuario, array $sucursalesIds, array $permisos, array $casIds = []): void
    {
        DB::transaction(function () use ($usuario, $sucursalesIds, $permisos, $casIds) {
            // Sincronizar tabla pivote legacy: usuariosucursales
            $usuario->sucursalesAsignadas()->sync($sucursalesIds);

            // Sincronizar tabla pivot: usuariocas
            $usuario->casAsignados()->sync($casIds);

            // Sincronizar tabla: permisosusuario
            PermisoUsuario::where('usuario_id', $usuario->id)->delete();
            $insertData = [];
            foreach ($permisos as $modulo => $acciones) {
                foreach ($acciones as $accion => $permitido) {
                    if ($permitido) {
                        $insertData[] = [
                            'usuario_id' => $usuario->id,
                            'modulo'     => $modulo,
                            'accion'     => $accion,
                            'permitido'  => 1
                        ];
                    }
                }
            }
            if (!empty($insertData)) {
                PermisoUsuario::insert($insertData);
            }
        });
    }
}
