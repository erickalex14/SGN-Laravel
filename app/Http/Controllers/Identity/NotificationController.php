<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $usuarioId = (int) session('tecnico_id', 0);
        if ($usuarioId <= 0) {
            return response()->json(['ok' => false, 'notificaciones' => [], 'no_leidas' => 0]);
        }

        if (!Schema::hasTable('notificaciones')) {
            return response()->json(['ok' => true, 'notificaciones' => [], 'no_leidas' => 0]);
        }

        $notificaciones = DB::table('notificaciones')
            ->select([
                'id',
                'tipo',
                'mensaje',
                'nc_id',
                'orden_id',
                'nro_orden',
                'leida',
                DB::raw("DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as fecha"),
            ])
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $noLeidas = $notificaciones->filter(fn ($n) => (int) $n->leida === 0)->count();

        return response()->json([
            'ok' => true,
            'notificaciones' => $notificaciones,
            'no_leidas' => $noLeidas,
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $usuarioId = (int) session('tecnico_id', 0);
        if ($usuarioId <= 0) {
            return response()->json(['ok' => false]);
        }

        if (!Schema::hasTable('notificaciones')) {
            return response()->json(['ok' => true]);
        }

        $accion = (string) $request->input('accion', 'una');
        $notificacionId = (int) $request->input('id', 0);

        if ($accion === 'todas') {
            DB::table('notificaciones')
                ->where('usuario_id', $usuarioId)
                ->where('leida', 0)
                ->update(['leida' => 1]);
        } elseif ($notificacionId > 0) {
            DB::table('notificaciones')
                ->where('id', $notificacionId)
                ->where('usuario_id', $usuarioId)
                ->update(['leida' => 1]);
        }

        return response()->json(['ok' => true]);
    }
}

