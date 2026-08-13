<?php

return [
    'base_url' => env('FACTURACION_API_URL', 'http://127.0.0.1:5080'),
    'timeout' => (int) env('FACTURACION_API_TIMEOUT', 20),
    'establishments' => [
        'quito' => '002',
        'novitec quito' => '002',
        'guayaquil' => '003',
        'novitec guayaquil' => '003',
        'manta' => '004',
        'novitec manta' => '004',
    ],
];
