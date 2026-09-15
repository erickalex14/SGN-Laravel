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

it('acepta cualquier pasaporte numerico o alfanumerico cuando el tipo es pasaporte', function () {
    $rule = new EcuadorIdentificacion('pasaporte');
    $errores = [];

    $rule->validate('cli_identificacion', '31725536', function ($mensaje) use (&$errores) {
        $errores[] = $mensaje;
    });

    expect($errores)->toBeEmpty();
});

it('rechaza cedula con menos o mas de 10 digitos cuando el tipo es cedula', function () {
    $rule = new EcuadorIdentificacion('cedula');
    $erroresMenos = [];
    $rule->validate('cli_identificacion', '31725536', function ($mensaje) use (&$erroresMenos) {
        $erroresMenos[] = $mensaje;
    });
    expect($erroresMenos)->not->toBeEmpty();

    $erroresMas = [];
    $rule->validate('cli_identificacion', '17123456789', function ($mensaje) use (&$erroresMas) {
        $erroresMas[] = $mensaje;
    });
    expect($erroresMas)->not->toBeEmpty();
});

it('rechaza ruc con menos o mas de 13 digitos cuando el tipo es ruc', function () {
    $rule = new EcuadorIdentificacion('ruc');
    $errores = [];
    $rule->validate('cli_identificacion', '1712345678', function ($mensaje) use (&$errores) {
        $errores[] = $mensaje;
    });
    expect($errores)->not->toBeEmpty();
});

