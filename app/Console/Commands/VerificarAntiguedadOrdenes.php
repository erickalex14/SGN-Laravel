<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Operations\Orden;
use App\Models\Operations\OrdenEmpresa;
use App\Models\Identity\Notificacion;
use App\Services\Operations\SgnMailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VerificarAntiguedadOrdenes extends Command
{
    protected $signature = 'ordenes:verificar-antiguedad';
    protected $description = 'Verifica la antigüedad de las órdenes activas y envía alertas a los 3 y 5 días.';

    public function handle()
    {
        $this->info('Iniciando verificación de antigüedad de órdenes...');
        $hoy = Carbon::now('America/Guayaquil');

        // 1. Órdenes Personales en proceso o pendientes
        $ordenes = Orden::whereIn('estado_orden', ['Pendiente', 'En proceso'])->get();
        foreach ($ordenes as $o) {
            $fecha = Carbon::parse($o->fecha_de_ingreso, 'America/Guayaquil');
            $diffDays = $fecha->diffInDays($hoy);

            if ($diffDays >= 5) {
                $this->procesarAlerta5Dias($o, 'personal');
            } elseif ($diffDays >= 3) {
                $this->procesarAlerta3Dias($o, 'personal');
            }
        }

        // 2. Órdenes Empresa en proceso o pendientes
        $ordenesEmp = OrdenEmpresa::whereIn('estado', ['Pendiente', 'En proceso'])->get();
        foreach ($ordenesEmp as $o) {
            $fecha = Carbon::parse($o->fecha_ingreso, 'America/Guayaquil');
            $diffDays = $fecha->diffInDays($hoy);

            if ($diffDays >= 5) {
                $this->procesarAlerta5Dias($o, 'empresa');
            } elseif ($diffDays >= 3) {
                $this->procesarAlerta3Dias($o, 'empresa');
            }
        }

        $this->info('Verificación completada exitosamente.');
    }

    private function procesarAlerta3Dias($orden, string $tipo)
    {
        if (!$orden->tecnico_id) return;

        // Comprobar si ya existe notificación de 3 días para evitar duplicados
        $existe = Notificacion::where('usuario_id', $orden->tecnico_id)
            ->where('tipo', 'orden_atrasada_3_dias')
            ->where('nro_orden', $orden->nro_orden)
            ->exists();

        if (!$existe) {
            $msg = "La orden {$orden->nro_orden} lleva 3 días asignada sin completarse.";
            Notificacion::create([
                'usuario_id' => $orden->tecnico_id,
                'tipo' => 'orden_atrasada_3_dias',
                'mensaje' => $msg,
                'orden_id' => $tipo === 'personal' ? $orden->id : null,
                'nro_orden' => $orden->nro_orden,
            ]);
            $this->line("Notificación de 3 días enviada al técnico para la orden {$orden->nro_orden}.");
        }
    }

    private function procesarAlerta5Dias($orden, string $tipo)
    {
        if (!$orden->tecnico_id) return;

        // Comprobar si ya existe notificación de 5 días
        $existe = Notificacion::where('usuario_id', $orden->tecnico_id)
            ->where('tipo', 'orden_atrasada_5_dias')
            ->where('nro_orden', $orden->nro_orden)
            ->exists();

        if (!$existe) {
            $msg = "La orden {$orden->nro_orden} lleva 5 días o más sin completarse.";
            Notificacion::create([
                'usuario_id' => $orden->tecnico_id,
                'tipo' => 'orden_atrasada_5_dias',
                'mensaje' => $msg,
                'orden_id' => $tipo === 'personal' ? $orden->id : null,
                'nro_orden' => $orden->nro_orden,
            ]);
            $this->line("Notificación de 5 días enviada al técnico para la orden {$orden->nro_orden}.");

            // Enviar correo al cliente
            try {
                \App\Services\Operations\SgnMailService::enviarEmailCliente(
                    $orden,
                    "Actualización sobre el estado de su orden: {$orden->nro_orden}",
                    "Estimado cliente,\n\nLe escribimos para informarle que la revisión técnica de su equipo bajo la orden {$orden->nro_orden} requerirá un tiempo de diagnóstico o procesamiento adicional al estimado inicialmente. Estamos trabajando arduamente para garantizar la calidad del servicio y la solución definitiva para su equipo. Le mantendremos informado ante cualquier novedad.\n\nAtentamente,\nEl equipo técnico de Novitec."
                );
                $this->line("Correo de retraso enviado al cliente de la orden {$orden->nro_orden}.");
            } catch (\Throwable $e) {
                Log::error("Error al enviar correo de retraso para la orden {$orden->nro_orden}: " . $e->getMessage());
            }
        }
    }
}
