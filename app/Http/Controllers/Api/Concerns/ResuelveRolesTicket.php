<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Identity\Usuario;
use App\Services\Operations\TicketService;

/**
 * Resolución de roles para la API móvil.
 *
 * Los controladores de API repetían la comprobación en línea y leían `rol?->nombre`,
 * pero la tabla `roles` tiene la columna `rol` (ver App\Models\Identity\Rol). Esa lectura
 * resolvía siempre a null, así que la mitad de la condición estaba muerta y solo contaba
 * la lista fija de IDs. El resto del sistema (controladores web) usa `rol->rol`.
 */
trait ResuelveRolesTicket
{
    /** Roles que la API considera administradores. */
    private const ROLES_ADMIN = ['administrador', 'admin', 'master'];

    /** Nombres de grupo con acceso administrativo, según verificarAccesoAdmin del módulo web. */
    private const GRUPOS_ADMIN = [
        'admin master', 'administrador master', 'superadministrador', 'admin', 'administrador',
    ];

    protected function nombreRol(?Usuario $usuario): string
    {
        return mb_strtolower(trim((string) ($usuario?->rol?->rol ?? '')));
    }

    /**
     * Técnico de sistemas: ve todos los tickets y las notas internas.
     * Se conserva el criterio original (lista de IDs o rol administrativo).
     */
    protected function esTecnicoSistemas(?Usuario $usuario): bool
    {
        if (!$usuario) {
            return false;
        }

        return in_array((int) $usuario->id, TicketService::TECNICOS_SISTEMAS_IDS, true)
            || in_array($this->nombreRol($usuario), self::ROLES_ADMIN, true);
    }

    /**
     * Acceso administrativo, equivalente sin sesión de
     * TicketSolicitantesController::verificarAccesoAdmin. Gobierna el alta y edición de
     * usuarios solicitantes, que crea credenciales y por tanto no puede quedar abierto.
     */
    protected function esAdminTickets(?Usuario $usuario): bool
    {
        if (!$usuario) {
            return false;
        }

        if ((bool) ($usuario->grupo?->es_superadmin ?? false)) {
            return true;
        }

        if (in_array($this->nombreRol($usuario), self::ROLES_ADMIN, true)) {
            return true;
        }

        $grupo = mb_strtolower(trim((string) ($usuario->grupo?->nombre ?? '')));

        return in_array($grupo, self::GRUPOS_ADMIN, true);
    }
}
