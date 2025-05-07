<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class ValidateTimezoneCommand extends Command
{
    protected $signature = 'timezone:validate';
    protected $description = 'Valida y muestra la configuración de zona horaria del sistema';

    public function handle()
    {
        $this->info('Validando configuración de zona horaria...');
        
        // Mostrar configuración actual
        $this->info('PHP timezone: ' . date_default_timezone_get());
        $this->info('Laravel timezone: ' . config('app.timezone'));
        $this->info('Carbon timezone: ' . Carbon::now()->tzName);
        
        // Verificar si está configurado correctamente
        if (date_default_timezone_get() !== 'America/Lima') {
            $this->warn('La zona horaria PHP no está configurada como America/Lima');
            date_default_timezone_set('America/Lima');
            $this->info('Zona horaria PHP actualizada a: ' . date_default_timezone_get());
        }
        
        // Mostrar hora actual
        $this->info('Hora actual del sistema: ' . Carbon::now()->format('Y-m-d H:i:s'));
        $this->info('Offset GMT: ' . Carbon::now()->tzName . ' (' . Carbon::now()->offset / 3600 . ' horas)');
        
        // Limpiar caché de configuración
        $this->call('config:clear');
        $this->call('cache:clear');
        
        $this->info('Validación completada.');
    }
}