<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Credit Note Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Esta configuración define los parámetros de logging específicos para
    | las notas de crédito y su integración con SUNAT.
    |
    */

    'enabled' => env('CREDIT_NOTE_LOGGING_ENABLED', true),

    'channels' => [
        'credit_notes' => [
            'driver' => 'daily',
            'path' => storage_path('logs/credit-notes.log'),
            'level' => env('CREDIT_NOTE_LOG_LEVEL', 'debug'),
            'days' => 30,
            'replace_placeholders' => true,
        ],
        
        'sunat_integration' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sunat-credit-notes.log'),
            'level' => env('SUNAT_CREDIT_NOTE_LOG_LEVEL', 'info'),
            'days' => 60,
            'replace_placeholders' => true,
        ],
    ],

    'log_levels' => [
        'creation' => 'info',
        'sunat_send' => 'info',
        'sunat_response' => 'info',
        'errors' => 'error',
        'debug' => 'debug',
    ],

    'include_sensitive_data' => env('CREDIT_NOTE_LOG_SENSITIVE', false),

    'max_xml_log_length' => 2000, // Máximo de caracteres del XML a loggear
];