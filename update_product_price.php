<?php

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Buscar el producto
$product = App\Models\Product::where('name', 'Arroz Chaufa Especial')->first();

if ($product) {
    echo "🔍 PRODUCTO ENCONTRADO:\n";
    echo "ID: {$product->id}\n";
    echo "Nombre: {$product->name}\n";
    echo "Precio de venta actual: S/ " . number_format($product->sale_price, 2) . "\n\n";

    // Actualizar solo sale_price (que es el campo que existe)
    $product->sale_price = 25.00;
    $product->save();

    echo "✅ PRODUCTO ACTUALIZADO:\n";
    echo "Nuevo precio de venta: S/ " . number_format($product->sale_price, 2) . "\n\n";
    
    echo "📝 NOTA: Este precio (S/ 25.00) YA INCLUYE IGV del 18%\n";
    echo "Desglose correcto:\n";
    echo "- Subtotal sin IGV: S/ " . number_format(25.00 / 1.18, 2) . "\n";
    echo "- IGV incluido: S/ " . number_format(25.00 / 1.18 * 0.18, 2) . "\n";
    echo "- Total con IGV: S/ 25.00\n";
} else {
    echo "❌ Producto 'Arroz Chaufa Especial' no encontrado\n";
    
    // Buscar productos similares
    $products = App\Models\Product::where('name', 'like', '%Arroz%')->get();
    if ($products->count() > 0) {
        echo "\n🔍 Productos similares encontrados:\n";
        foreach ($products as $p) {
            echo "- ID: {$p->id}, Nombre: {$p->name}, Precio de venta: S/ " . number_format($p->sale_price, 2) . "\n";
        }
    }
}
