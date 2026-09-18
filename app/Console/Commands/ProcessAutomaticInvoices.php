<?php

namespace App\Console\Commands;

use App\Services\Facturacion\AutomaticInvoiceService;
use Illuminate\Console\Command;

class ProcessAutomaticInvoices extends Command
{
    protected $signature = 'facturacion:procesar';
    protected $description = 'Despacha y sincroniza facturas automáticas pendientes';

    public function handle(AutomaticInvoiceService $service): int
    {
        $result = $service->processPending();
        $this->info("Despachadas: {$result['dispatched']}; sincronizadas: {$result['synced']}.");
        return self::SUCCESS;
    }
}
