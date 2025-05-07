<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_PE'); // Usar configuración regional de Perú

        // Posiciones disponibles en el restaurante
        $positions = [
            'Gerente',
            'Chef',
            'Cocinero',
            'Mesero',
            'Cajero',
            'Delivery',
            'Bartender',
            'Limpieza',
            'Recepcionista',
            'Ayudante de cocina'
        ];

        // Crear 20 empleados
        for ($i = 0; $i < 20; $i++) {
            // Determinar si este empleado tendrá un usuario asociado (solo algunos roles)
            $createUser = in_array($positions[$i % count($positions)], ['Gerente', 'Cajero', 'Delivery', 'Mesero']);
            $userId = null;

            // Si debe tener usuario, crear uno
            if ($createUser) {
                $firstName = $faker->firstName;
                $lastName = $faker->lastName;
                $position = $positions[$i % count($positions)];

                $user = User::create([
                    'name' => $firstName . ' ' . $lastName,
                    'email' => strtolower(str_replace(' ', '.', $firstName)) . '.' . strtolower(str_replace(' ', '.', $lastName)) . '@restaurante.com',
                    'password' => bcrypt('password'), // Contraseña por defecto
                    'email_verified_at' => now(),
                ]);

                // Asignar rol según la posición usando los roles de Filament Shield
                try {
                    if ($position === 'Gerente') {
                        // Intentar asignar el rol de administrador
                        if (\Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
                            $user->assignRole('admin');
                        } elseif (\Spatie\Permission\Models\Role::where('name', 'super_admin')->exists()) {
                            $user->assignRole('super_admin');
                        } else {
                            // Buscar cualquier rol que contenga 'admin'
                            $adminRole = \Spatie\Permission\Models\Role::where('name', 'like', '%admin%')->first();
                            if ($adminRole) {
                                $user->assignRole($adminRole->name);
                            }
                        }
                    } elseif ($position === 'Cajero') {
                        // Para cajeros, buscar roles relacionados con ventas o caja
                        $cashierRole = \Spatie\Permission\Models\Role::where('name', 'like', '%cashier%')
                            ->orWhere('name', 'like', '%sale%')
                            ->orWhere('name', 'like', '%pos%')
                            ->first();

                        if ($cashierRole) {
                            $user->assignRole($cashierRole->name);
                        }
                    } elseif ($position === 'Delivery') {
                        // Para delivery, buscar roles relacionados
                        $deliveryRole = \Spatie\Permission\Models\Role::where('name', 'like', '%delivery%')
                            ->orWhere('name', 'like', '%driver%')
                            ->first();

                        if ($deliveryRole) {
                            $user->assignRole($deliveryRole->name);
                        }
                    } elseif ($position === 'Mesero') {
                        // Para meseros, buscar roles relacionados
                        $waiterRole = \Spatie\Permission\Models\Role::where('name', 'like', '%waiter%')
                            ->orWhere('name', 'like', '%table%')
                            ->first();

                        if ($waiterRole) {
                            $user->assignRole($waiterRole->name);
                        }
                    }
                } catch (\Exception $e) {
                    // Si hay un error al asignar roles, registrarlo pero continuar
                    $this->command->warn("No se pudo asignar rol al usuario {$user->name}: " . $e->getMessage());
                }

                $userId = $user->id;
            } else {
                $firstName = $faker->firstName;
                $lastName = $faker->lastName;
            }

            // Crear el empleado
            Employee::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'document_number' => $faker->numerify('########'), // DNI de 8 dígitos
                'phone' => $faker->numerify('9########'), // Número de celular peruano
                'address' => $faker->address,
                'position' => $positions[$i % count($positions)],
                'hire_date' => Carbon::now()->subMonths(rand(1, 36))->format('Y-m-d'), // Fecha de contratación entre hoy y hace 3 años
                'base_salary' => rand(1200, 5000), // Salario base entre 1200 y 5000 soles
                'user_id' => $userId,
            ]);
        }

        // Asegurarnos de que haya al menos 5 repartidores
        $deliveryCount = Employee::where('position', 'Delivery')->count();

        if ($deliveryCount < 5) {
            $additionalDelivery = 5 - $deliveryCount;

            for ($i = 0; $i < $additionalDelivery; $i++) {
                $firstName = $faker->firstName;
                $lastName = $faker->lastName;

                $user = User::create([
                    'name' => $firstName . ' ' . $lastName,
                    'email' => strtolower(str_replace(' ', '.', $firstName)) . '.' . strtolower(str_replace(' ', '.', $lastName)) . '@restaurante.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);

                // Intentar asignar un rol de delivery usando Filament Shield
                try {
                    // Buscar roles relacionados con delivery
                    $deliveryRole = \Spatie\Permission\Models\Role::where('name', 'like', '%delivery%')
                        ->orWhere('name', 'like', '%driver%')
                        ->first();

                    if ($deliveryRole) {
                        $user->assignRole($deliveryRole->name);
                    }
                } catch (\Exception $e) {
                    // Si hay un error al asignar roles, registrarlo pero continuar
                    $this->command->warn("No se pudo asignar rol al usuario {$user->name}: " . $e->getMessage());
                }

                Employee::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'document_number' => $faker->numerify('########'),
                    'phone' => $faker->numerify('9########'),
                    'address' => $faker->address,
                    'position' => 'Delivery',
                    'hire_date' => Carbon::now()->subMonths(rand(1, 12))->format('Y-m-d'),
                    'base_salary' => rand(1200, 1800),
                    'user_id' => $user->id,
                ]);
            }
        }

        // Mensaje de confirmación
        $this->command->info('Se han creado ' . Employee::count() . ' empleados con éxito.');
    }
}
