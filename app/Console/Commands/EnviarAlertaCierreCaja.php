<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Operations\CajaMensualidad;
use App\Models\Directory\Sucursal;
use App\Services\Operations\SgnMailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnviarAlertaCierreCaja extends Command
{
    protected $signature = 'caja:enviar-alerta-cierre {--force : Forzar envío sin verificar si es el último día del mes}';
    protected $description = 'Envía alertas por correo electrónico para recordar a los administradores y superadmins que cierren la caja al final del mes.';

    public function handle()
    {
        $this->info('Iniciando proceso de alertas de cierre de caja...');
        $hoy = Carbon::now('America/Guayaquil');
        
        $esFinDeMes = $hoy->isLastOfMonth();

        if (!$esFinDeMes && !$this->option('force')) {
            $this->warn('Hoy no es el último día del mes. Use el flag --force si desea enviar la alerta para pruebas.');
            return;
        }

        $mes = $hoy->month;
        $anio = $hoy->year;

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $mesNombre = $meses[$mes] ?? 'Mes ' . $mes;

        // Obtener todas las mensualidades abiertas para el mes y año actual
        $mensualidadesAbiertas = CajaMensualidad::where('mes', $mes)
            ->where('anio', $anio)
            ->where('cerrado', false)
            ->with('caja.sucursal')
            ->get();

        if ($mensualidadesAbiertas->isEmpty()) {
            $this->info('No hay cajas abiertas pendientes de cierre para este mes.');
            return;
        }

        // Agrupar por sucursal para enviar un solo correo por sucursal
        $sucursalesConCajaAbierta = [];
        foreach ($mensualidadesAbiertas as $m) {
            $sucursal = $m->caja->sucursal;
            if ($sucursal) {
                $sucursalesConCajaAbierta[$sucursal->id] = $sucursal;
            }
        }

        foreach ($sucursalesConCajaAbierta as $sucursalId => $sucursal) {
            $nombreSucursal = $sucursal->nombre ?? $sucursal->ciudad ?? 'Sucursal ' . $sucursalId;
            $destinatarios = SgnMailService::obtenerCorreosNotificacionAdmins($sucursalId);

            if (empty($destinatarios)) {
                Log::warning("No se encontraron correos de administradores para enviar alerta de cierre de caja", ['sucursal_id' => $sucursalId]);
                $this->error("No se encontraron destinatarios para la sucursal: {$nombreSucursal}");
                continue;
            }

            $this->line("Enviando recordatorio de cierre de caja para sucursal {$nombreSucursal} a: " . implode(', ', $destinatarios));
            SgnMailService::enviarAlertaCierreCaja($destinatarios, $nombreSucursal, $mesNombre, $anio);
        }

        $this->info('Envío de alertas finalizado.');
    }
}
