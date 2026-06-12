<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$u = App\Models\Identity\Usuario::where('nombre_tecnico', 'like', '%Jahaira%')->first();
if ($u) {
    echo "USER_FOUND\n";
    print_r($u->toArray());
    echo "\nROL:\n";
    print_r($u->rol ? $u->rol->toArray() : 'NO_ROL');
    echo "\nGRUPO:\n";
    print_r($u->grupo ? $u->grupo->toArray() : 'NO_GRUPO');
} else {
    echo "USER_NOT_FOUND\n";
}
