<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Customer;

class FixDeliveryAddresses extends Command
{
    protected $signature = 'delivery:fix-addresses {--dry-run : Solo mostrar qué se actualizaría sin hacer cambios}';
    protected $description = 'Corregir direcciones de delivery que tienen valores por defecto';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 MODO DRY-RUN: Solo mostrando qué se actualizaría');
        } else {
            $this->info('🔧 MODO EJECUCIÓN: Aplicando correcciones');
        }
        
        $this->line('');
        
        // Buscar delivery orders con direcciones por defecto
        $deliveryOrders = DeliveryOrder::where('delivery_address', 'Dirección pendiente de completar')
            ->orWhere('delivery_references', 'Referencias pendientes')
            ->with(['order.customer'])
            ->get();
            
        if ($deliveryOrders->isEmpty()) {
            $this->info('✅ No se encontraron delivery orders con direcciones por defecto');
            return 0;
        }
        
        $this->info("📋 Encontrados {$deliveryOrders->count()} delivery orders con direcciones por defecto");
        $this->line('');
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($deliveryOrders as $delivery) {
            $this->line("🚚 Delivery ID: {$delivery->id}");
            $this->line("   Order ID: {$delivery->order_id}");
            $this->line("   Dirección actual: {$delivery->delivery_address}");
            $this->line("   Referencias actuales: {$delivery->delivery_references}");
            
            // Buscar el cliente de la orden
            $customer = null;
            if ($delivery->order && $delivery->order->customer_id) {
                $customer = $delivery->order->customer;
            } else {
                // Si la orden no tiene customer_id, buscar en las boletas asociadas
                $invoice = Invoice::where('order_id', $delivery->order_id)->first();
                if ($invoice && $invoice->customer_id) {
                    $customer = Customer::find($invoice->customer_id);
                }
            }
            
            if (!$customer) {
                $this->line("   ❌ No se encontró cliente asociado");
                $skipped++;
                $this->line('');
                continue;
            }
            
            $this->line("   👤 Cliente: {$customer->name} ({$customer->document_number})");
            $this->line("   📍 Dirección del cliente: " . ($customer->address ?: 'N/A'));
            $this->line("   📝 Referencias del cliente: " . ($customer->address_references ?: 'N/A'));
            
            $needsUpdate = false;
            $changes = [];
            
            // Verificar si necesita actualizar la dirección
            if ($delivery->delivery_address === 'Dirección pendiente de completar' && !empty($customer->address)) {
                $needsUpdate = true;
                $changes[] = "Dirección: '{$delivery->delivery_address}' → '{$customer->address}'";
            }
            
            // Verificar si necesita actualizar las referencias
            if ($delivery->delivery_references === 'Referencias pendientes' && !empty($customer->address_references)) {
                $needsUpdate = true;
                $changes[] = "Referencias: '{$delivery->delivery_references}' → '{$customer->address_references}'";
            }
            
            if ($needsUpdate) {
                $this->line("   🔄 Cambios a aplicar:");
                foreach ($changes as $change) {
                    $this->line("      - {$change}");
                }
                
                if (!$isDryRun) {
                    // Aplicar los cambios
                    if ($delivery->delivery_address === 'Dirección pendiente de completar' && !empty($customer->address)) {
                        $delivery->delivery_address = $customer->address;
                    }
                    
                    if ($delivery->delivery_references === 'Referencias pendientes' && !empty($customer->address_references)) {
                        $delivery->delivery_references = $customer->address_references;
                    }
                    
                    $delivery->save();
                    $this->line("   ✅ Actualizado correctamente");
                } else {
                    $this->line("   ℹ️  Se actualizaría (dry-run)");
                }
                
                $updated++;
            } else {
                $this->line("   ⏭️  No necesita actualización (cliente sin dirección/referencias)");
                $skipped++;
            }
            
            $this->line('');
        }
        
        // Resumen
        $this->line('📊 RESUMEN:');
        $this->line("   Total encontrados: {$deliveryOrders->count()}");
        $this->line("   " . ($isDryRun ? 'Se actualizarían' : 'Actualizados') . ": {$updated}");
        $this->line("   Omitidos: {$skipped}");
        
        if ($isDryRun && $updated > 0) {
            $this->line('');
            $this->info('💡 Para aplicar los cambios, ejecuta el comando sin --dry-run:');
            $this->line('   php artisan delivery:fix-addresses');
        }
        
        return 0;
    }
}
