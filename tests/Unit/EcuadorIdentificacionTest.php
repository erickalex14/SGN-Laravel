<?php

use App\Rules\EcuadorIdentificacion;

it('rechaza identificaciones numericas largas que no son cedula ni ruc', function () {
    $rule = new EcuadorIdentificacion;
    $errores = [];

    $rule->validate('cli_identificacion', '866915084895327', function ($mensaje) use (&$errores) {
        $errores[] = $mensaje;
    });

    expect($errores)->not->toBeEmpty();
});

it('acepta pasaporte alfanumerico', function () {
    $rule = new EcuadorIdentificacion;
    $errores = [];

    $rule->validate('cli_identificacion', 'AB12345', function ($mensaje) use (&$errores) {
        $errores[] = $mensaje;
    });

    expect($errores)->toBeEmpty();
});
