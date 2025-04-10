<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

class UpdateTablesStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tables:update-status {status=available : El estado al que se actualizarán todas las mesas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza todas las mesas al estado especificado (por defecto: available)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->argument('status');

        // Verificar que el status sea válido
        $validStatuses = ['available', 'occupied', 'reserved', 'maintenance'];
        if (!in_array($status, $validStatuses)) {
            $this->error("Estado no válido. Los estados permitidos son: " . implode(', ', $validStatuses));
            return 1;
        }

        // Contar las mesas antes de la actualización
        $mesasAntes = Table::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get()
                    ->pluck('total', 'status')
                    ->toArray();

        $this->info("Estado de las mesas antes de la actualización:");
        foreach ($mesasAntes as $tableStatus => $total) {
            $this->line("- $tableStatus: $total mesas");
        }

        // Actualizar todas las mesas al estado especificado
        $actualizadas = Table::where('status', '!=', $status)
                      ->update(['status' => $status]);

        $this->info("\nSe actualizaron $actualizadas mesas a estado '$status'.");

        // Verificar el estado final
        $mesasDespues = Table::select('status', DB::raw('count(*) as total'))
                      ->groupBy('status')
                      ->get()
                      ->pluck('total', 'status')
                      ->toArray();

        $this->info("\nEstado de las mesas después de la actualización:");
        foreach ($mesasDespues as $tableStatus => $total) {
            $this->line("- $tableStatus: $total mesas");
        }

        $this->info("\n¡Actualización completada!");
        return 0;
    }
}
