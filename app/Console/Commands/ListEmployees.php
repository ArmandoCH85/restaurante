<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class ListEmployees extends Command
{
    protected $signature = 'employees:list';
    protected $description = 'Listar todos los empleados y sus posiciones';

    public function handle()
    {
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->info('No hay empleados registrados');
            return 0;
        }

        $this->info("👥 EMPLEADOS REGISTRADOS ({$employees->count()}):");
        $this->line("");

        foreach ($employees as $employee) {
            $this->line("ID: {$employee->id}");
            $this->line("   👤 Nombre: {$employee->full_name}");
            $this->line("   🏷️  Posición: '{$employee->position}'");
            $this->line("   📱 Teléfono: {$employee->phone}");
            $this->line("   📧 Usuario: " . ($employee->user ? $employee->user->email : 'Sin usuario'));
            $this->line("   📅 Contratado: {$employee->hire_date}");
            $this->line("   " . str_repeat("─", 40));
        }

        return 0;
    }
}
