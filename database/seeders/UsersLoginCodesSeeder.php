<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UsersLoginCodesSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que la columna exista
        if (! Schema::hasColumn('users', 'login_code')) {
            $this->command?->error('La columna users.login_code no existe. Ejecuta las migraciones primero: php artisan migrate');
            return;
        }

        $this->command?->info('Asignando códigos de acceso (6 dígitos) a usuarios que no tengan uno...');

        $rows = [];

        User::query()->orderBy('id')->chunk(200, function ($users) use (&$rows) {
            foreach ($users as $user) {
                // Si no tiene código o no cumple el formato de 6 dígitos, generar uno
                if (!preg_match('/^\d{6}$/', (string) $user->login_code)) {
                    $user->login_code = $this->generateUniqueCode();
                    $user->save();
                }

                $roles = method_exists($user, 'getRoleNames')
                    ? $user->getRoleNames()->implode(',')
                    : '';

                $rows[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'code' => $user->login_code,
                    'roles' => $roles,
                ];
            }
        });

        // Guardar CSV en storage/app
        $filename = 'login_codes_' . now()->format('Ymd_His') . '.csv';
        $csv = $this->toCsv($rows, ['id', 'name', 'email', 'code', 'roles']);
        Storage::disk('local')->put($filename, $csv);

        $this->command?->info('Archivo generado: ' . storage_path('app/' . $filename));

        // Mostrar una tabla en consola (limitar a 100 filas para legibilidad)
        $display = array_slice($rows, 0, 100);
        if (method_exists($this->command, 'table')) {
            $this->command->table(['ID', 'Nombre', 'Email', 'Código', 'Roles'], array_map(function ($r) {
                return [$r['id'], $r['name'], $r['email'], $r['code'], $r['roles']];
            }, $display));
        }

        if (count($rows) > 100) {
            $this->command?->warn('Mostrando solo los primeros 100 usuarios. Revisa el CSV para la lista completa.');
        }
    }

    protected function generateUniqueCode(): string
    {
        $attempts = 0;
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = User::where('login_code', $code)->exists();
            $attempts++;
        } while ($exists && $attempts < 20);

        return $code;
    }

    protected function toCsv(array $rows, array $headers): string
    {
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, $headers);
        foreach ($rows as $row) {
            fputcsv($fh, [
                $row['id'] ?? '',
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['code'] ?? '',
                $row['roles'] ?? '',
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return (string) $csv;
    }
}
