<?php

return [
    'base_url' => env('FACTURACION_API_URL', 'http://127.0.0.1:5080'),
    'timeout' => (int) env('FACTURACION_API_TIMEOUT', 20),
    'establishments' => [
        'quito' => '001',
        'novitec quito' => '001',
        'guayaquil' => '002',
        'novitec guayaquil' => '002',
        'manta' => '003',
        'novitec manta' => '003',
    ],
];
