<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\DeliveryOrder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DiagnoseDeliveryAssignment extends Command
{
    protected $signature = 'delivery:diagnose-assignment';
    protected $description = 'Diagnosticar problemas con la asignación de repartidores';

    public function handle()
    {
        $this->info("🔍 DIAGNÓSTICO DE ASIGNACIÓN DE REPARTIDORES");
        $this->line("");

        // 1. Verificar empleados con posición "Delivery"
        $this->checkDeliveryEmployees();
        $this->line("");

        // 2. Verificar roles de delivery
        $this->checkDeliveryRoles();
        $this->line("");

        // 3. Verificar usuarios con rol de delivery
        $this->checkUsersWithDeliveryRole();
        $this->line("");

        // 4. Verificar deliveries pendientes
        $this->checkPendingDeliveries();
        $this->line("");

        // 5. Probar asignación
        $this->testAssignment();

        return 0;
    }

    private function checkDeliveryEmployees(): void
    {
        $this->info("1️⃣ EMPLEADOS CON POSICIÓN 'DELIVERY':");

        $deliveryEmployees = Employee::where('position', 'Delivery')->get();

        if ($deliveryEmployees->isEmpty()) {
            $this->error("   ❌ No hay empleados con posición 'Delivery'");
            $this->line("   💡 Solución: Crear empleado con position = 'Delivery'");
        } else {
            foreach ($deliveryEmployees as $employee) {
                $this->line("   ✅ ID: {$employee->id} | {$employee->full_name}");
                $this->line("      📧 Usuario: " . ($employee->user ? $employee->user->email : 'Sin usuario'));
                $this->line("      📱 Teléfono: {$employee->phone}");
                $this->line("      📅 Contratado: {$employee->hire_date}");
            }
        }
    }

    private function checkDeliveryRoles(): void
    {
        $this->info("2️⃣ ROLES DE DELIVERY EN EL SISTEMA:");

        $deliveryRoles = Role::where('name', 'like', '%delivery%')
            ->orWhere('name', 'like', '%driver%')
            ->get();

        if ($deliveryRoles->isEmpty()) {
            $this->error("   ❌ No hay roles relacionados con delivery");
            $this->line("   💡 Solución: Crear rol 'delivery' en Filament Shield");
        } else {
            foreach ($deliveryRoles as $role) {
                $usersCount = $role->users()->count();
                $this->line("   ✅ Rol: '{$role->name}' | Usuarios: {$usersCount}");
            }
        }
    }

    private function checkUsersWithDeliveryRole(): void
    {
        $this->info("3️⃣ USUARIOS CON ROL DE DELIVERY:");

        $deliveryRole = Role::where('name', 'delivery')->orWhere('name', 'Delivery')->first();

        if (!$deliveryRole) {
            $this->error("   ❌ No existe el rol 'delivery' ni 'Delivery'");
            return;
        }

        $usersWithDeliveryRole = $deliveryRole->users()->get();

        if ($usersWithDeliveryRole->isEmpty()) {
            $this->error("   ❌ No hay usuarios con rol 'delivery'");
            $this->line("   💡 Solución: Asignar rol 'delivery' a usuarios repartidores");
        } else {
            foreach ($usersWithDeliveryRole as $user) {
                $employee = Employee::where('user_id', $user->id)->first();
                $this->line("   ✅ Usuario: {$user->email}");
                $this->line("      👤 Empleado: " . ($employee ? $employee->full_name : 'Sin empleado asociado'));
                $this->line("      🏷️  Posición: " . ($employee ? $employee->position : 'N/A'));
            }
        }
    }

    private function checkPendingDeliveries(): void
    {
        $this->info("4️⃣ DELIVERIES PENDIENTES:");

        $pendingDeliveries = DeliveryOrder::where('status', 'pending')->get();

        if ($pendingDeliveries->isEmpty()) {
            $this->line("   ℹ️  No hay deliveries pendientes");
        } else {
            foreach ($pendingDeliveries as $delivery) {
                $this->line("   📦 Delivery #{$delivery->id} | Orden #{$delivery->order_id}");
                $this->line("      📍 Dirección: {$delivery->delivery_address}");
                $this->line("      📊 Estado: {$delivery->status}");
            }
        }
    }

    private function testAssignment(): void
    {
        $this->info("5️⃣ PRUEBA DE ASIGNACIÓN:");

        // Buscar un delivery pendiente
        $pendingDelivery = DeliveryOrder::where('status', 'pending')->first();

        if (!$pendingDelivery) {
            $this->line("   ℹ️  No hay deliveries pendientes para probar");
            return;
        }

        // Buscar empleados de delivery
        $deliveryEmployees = Employee::where('position', 'Delivery')->get();

        if ($deliveryEmployees->isEmpty()) {
            $this->error("   ❌ No hay empleados de delivery para asignar");
            return;
        }

        $employee = $deliveryEmployees->first();

        $this->line("   🧪 Probando asignar delivery #{$pendingDelivery->id} a {$employee->full_name}");

        try {
            $success = $pendingDelivery->assignDeliveryPerson($employee);

            if ($success) {
                $this->line("   ✅ Asignación exitosa");
                $this->line("   📊 Nuevo estado: {$pendingDelivery->status}");
                $this->line("   ⏰ Tiempo estimado: {$pendingDelivery->estimated_delivery_time}");
            } else {
                $this->error("   ❌ Asignación falló");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error en asignación: " . $e->getMessage());
            $this->line("   📍 Archivo: " . $e->getFile());
            $this->line("   📍 Línea: " . $e->getLine());
        }
    }
}
