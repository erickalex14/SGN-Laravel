<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Identity\Usuario;
use App\Models\Inventory\Repuesto;
use App\Models\Operations\OrdenRepuesto;
use Illuminate\Support\Facades\View;

try {
    $auditorias = OrdenRepuesto::with(['repuesto', 'orden.tecnico', 'orden.sucursal', 'usuario'])
        ->orderBy('fecha', 'desc')
        ->take(10)
        ->get();

    $repuestosList = Repuesto::orderBy('nombre', 'asc')->take(5)->get();
    $tecnicosList = Usuario::orderBy('nombre_tecnico', 'asc')->take(5)->get();
    $filtrosTxt = ['Filtro Test'];

    echo "Rendering inventory.repuestos.auditoria...\n";
    $html1 = View::make('inventory.repuestos.auditoria', compact('auditorias', 'repuestosList', 'tecnicosList'))->render();
    echo 'Success! Auditoria View HTML Length: '.strlen($html1)."\n";

    echo "Rendering inventory.repuestos.imprimir_reporte...\n";
    $html2 = View::make('inventory.repuestos.imprimir_reporte', compact('auditorias', 'filtrosTxt'))->render();
    echo 'Success! Imprimir Reporte HTML Length: '.strlen($html2)."\n";

} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}
