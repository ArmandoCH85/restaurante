<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Permissions
    |--------------------------------------------------------------------------
    |
    | Define custom permissions here that will be available in the Shield UI
    | to assign to roles. These permissions will be automatically registered
    | when the application boots.
    |
    */
    'access_pos' => [
        'label' => 'Acceder al POS',
        'description' => 'Permite acceder al sistema de punto de venta (POS)',
        'group' => 'Sistema',
    ],
    'access_tables' => [
        'label' => 'Acceder al Mapa de Mesas',
        'description' => 'Permite acceder al mapa de mesas y gestión de pedidos',
        'group' => 'Sistema',
    ],
    'access_delivery' => [
        'label' => 'Acceder a Delivery',
        'description' => 'Permite acceder a la gestión de pedidos de delivery',
        'group' => 'Sistema',
    ],
    'access_tables_maintenance' => [
        'label' => 'Acceder a Mantenimiento de Mesas',
        'description' => 'Permite acceder al mantenimiento de mesas',
        'group' => 'Sistema',
    ],
];
