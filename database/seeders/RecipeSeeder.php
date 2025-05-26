<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\RecipeDetail;
use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get products that have recipes
        $productsWithRecipes = Product::where('has_recipe', true)->get();

        foreach ($productsWithRecipes as $product) {
            $recipe = $this->createRecipeForProduct($product);
            if ($recipe) {
                $this->createRecipeDetails($recipe, $product);
            }
        }

        $this->command->info('Recipes seeded successfully!');
    }

    private function createRecipeForProduct(Product $product): ?Recipe
    {
        $recipeData = $this->getRecipeDataByProduct($product);
        
        if (!$recipeData) {
            return null;
        }

        return Recipe::create([
            'product_id' => $product->id,
            'preparation_instructions' => $recipeData['instructions'],
            'expected_cost' => $recipeData['expected_cost'],
            'preparation_time' => $recipeData['preparation_time'],
        ]);
    }

    private function createRecipeDetails(Recipe $recipe, Product $product): void
    {
        $ingredients = $this->getIngredientsForProduct($product);

        foreach ($ingredients as $ingredientData) {
            // Try to find existing ingredient first
            $ingredient = Ingredient::where('name', 'like', '%' . $ingredientData['name'] . '%')->first();
            
            if (!$ingredient) {
                // Create as Product ingredient if not found in Ingredient model
                $ingredient = Product::where('name', 'like', '%' . $ingredientData['name'] . '%')
                    ->where('product_type', Product::TYPE_INGREDIENT)
                    ->first();
            }

            if ($ingredient) {
                RecipeDetail::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $ingredientData['quantity'],
                    'unit_of_measure' => $ingredientData['unit'],
                ]);
            }
        }
    }

    private function getRecipeDataByProduct(Product $product): ?array
    {
        $recipes = [
            'Lomo Saltado' => [
                'instructions' => "1. Cortar la carne en tiras de 1cm\n2. Sazonar con sal y pimienta\n3. Calentar aceite en wok a fuego alto\n4. Saltear la carne hasta dorar\n5. Agregar cebolla en gajos\n6. Incorporar tomate en gajos\n7. Añadir las papas fritas\n8. Sazonar con sillao y vinagre\n9. Servir con arroz blanco",
                'expected_cost' => 16.50,
                'preparation_time' => 25.00,
            ],
            'Ají de Gallina' => [
                'instructions' => "1. Deshuesar y desmechar el pollo cocido\n2. Licuar ají amarillo con leche\n3. Hacer sofrito con cebolla y ajo\n4. Agregar la crema de ají\n5. Incorporar el pollo desmechado\n6. Añadir pan remojado en leche\n7. Agregar nueces molidas\n8. Cocinar hasta espesar\n9. Servir con papa sancochada",
                'expected_cost' => 14.20,
                'preparation_time' => 45.00,
            ],
            'Arroz con Pollo' => [
                'instructions' => "1. Trozar el pollo y sazonar\n2. Dorar el pollo en aceite caliente\n3. Agregar sofrito de cebolla y ajo\n4. Licuar cilantro con caldo\n5. Incorporar el arroz y mezclar\n6. Añadir la crema de cilantro\n7. Agregar caldo caliente\n8. Cocinar hasta que el arroz esté tierno\n9. Decorar con arvejas y pimiento",
                'expected_cost' => 12.80,
                'preparation_time' => 40.00,
            ],
            'Ceviche de Pescado' => [
                'instructions' => "1. Cortar el pescado en cubos de 2cm\n2. Sazonar con sal y dejar reposar\n3. Exprimir limones para obtener jugo fresco\n4. Licuar ají limo con un poco de jugo\n5. Mezclar pescado con jugo de limón\n6. Agregar cebolla en pluma\n7. Incorporar ají molido\n8. Marinar por 10-15 minutos\n9. Servir con camote y choclo",
                'expected_cost' => 18.50,
                'preparation_time' => 20.00,
            ],
            'Pollo a la Brasa' => [
                'instructions' => "1. Marinar el pollo con especias por 4 horas\n2. Preparar la marinada con ají panca, comino, sillao\n3. Ensartar el pollo en el espetón\n4. Cocinar en horno rotatorio a 200°C\n5. Girar constantemente por 1 hora\n6. Verificar cocción con termómetro\n7. Retirar y dejar reposar 5 minutos\n8. Cortar y servir con papas fritas\n9. Acompañar con ensalada y cremas",
                'expected_cost' => 20.00,
                'preparation_time' => 90.00,
            ],
            'Papa a la Huancaína' => [
                'instructions' => "1. Sancochar papas amarillas enteras\n2. Licuar ají amarillo con leche evaporada\n3. Agregar queso fresco a la licuadora\n4. Incorporar galletas de soda\n5. Sazonar con sal y pimienta\n6. Licuar hasta obtener crema homogénea\n7. Cortar papas en rodajas\n8. Bañar con salsa huancaína\n9. Decorar con huevo duro y aceitunas",
                'expected_cost' => 6.50,
                'preparation_time' => 30.00,
            ],
            'Anticuchos' => [
                'instructions' => "1. Limpiar y cortar corazón en trozos\n2. Preparar marinada con ají panca, vinagre, comino\n3. Marinar la carne por 2 horas mínimo\n4. Ensartar en palitos de anticucho\n5. Asar a la parrilla a fuego medio\n6. Pincelar con marinada durante cocción\n7. Cocinar 3-4 minutos por lado\n8. Servir con papa sancochada\n9. Acompañar con choclo y salsa criolla",
                'expected_cost' => 10.20,
                'preparation_time' => 35.00,
            ],
            'Causa Limeña' => [
                'instructions' => "1. Sancochar papas amarillas y hacer puré\n2. Sazonar puré con limón, sal y ají amarillo\n3. Preparar relleno de pollo desmechado\n4. Mezclar pollo con mayonesa y palta\n5. Armar la causa en capas\n6. Primera capa de papa, luego relleno\n7. Cubrir con otra capa de papa\n8. Refrigerar por 2 horas\n9. Decorar con huevo duro y aceitunas",
                'expected_cost' => 8.50,
                'preparation_time' => 60.00,
            ],
            'Chicha Morada' => [
                'instructions' => "1. Hervir maíz morado con especias\n2. Agregar piña, manzana y membrillo\n3. Cocinar por 45 minutos\n4. Colar el líquido concentrado\n5. Endulzar con azúcar al gusto\n6. Agregar jugo de limón\n7. Enfriar completamente\n8. Servir con hielo\n9. Decorar con frutas picadas",
                'expected_cost' => 2.80,
                'preparation_time' => 60.00,
            ],
            'Limonada' => [
                'instructions' => "1. Exprimir limones frescos\n2. Colar el jugo para quitar semillas\n3. Mezclar con agua fría\n4. Endulzar con azúcar al gusto\n5. Agregar hielo picado\n6. Decorar con rodajas de limón\n7. Añadir hojas de menta\n8. Servir inmediatamente\n9. Remover antes de servir",
                'expected_cost' => 1.80,
                'preparation_time' => 10.00,
            ],
            'Suspiro Limeño' => [
                'instructions' => "1. Preparar manjar blanco con leche condensada\n2. Cocinar a fuego lento hasta espesar\n3. Batir claras de huevo a punto de nieve\n4. Agregar azúcar gradualmente\n5. Incorporar vino de oporto\n6. Continuar batiendo hasta merengue firme\n7. Servir manjar en copas\n8. Cubrir con merengue\n9. Espolvorear con canela",
                'expected_cost' => 4.20,
                'preparation_time' => 45.00,
            ],
            'Mazamorra Morada' => [
                'instructions' => "1. Preparar chicha morada concentrada\n2. Colar y reservar el líquido\n3. Disolver chuño en agua fría\n4. Hervir la chicha colada\n5. Agregar chuño disuelto\n6. Cocinar revolviendo hasta espesar\n7. Añadir frutas picadas\n8. Endulzar con azúcar\n9. Servir frío con canela",
                'expected_cost' => 3.50,
                'preparation_time' => 40.00,
            ],
            'Salsa Criolla' => [
                'instructions' => "1. Cortar cebolla en pluma fina\n2. Remojar en agua con sal por 10 minutos\n3. Escurrir y enjuagar\n4. Cortar ají en tiras finas\n5. Mezclar cebolla con ají\n6. Agregar cilantro picado\n7. Aliñar con limón y sal\n8. Dejar macerar 15 minutos\n9. Servir como acompañamiento",
                'expected_cost' => 1.20,
                'preparation_time' => 20.00,
            ],
            'Salsa Huancaína' => [
                'instructions' => "1. Remojar ají amarillo en agua caliente\n2. Licuar ají con leche evaporada\n3. Agregar queso fresco\n4. Incorporar galletas de soda\n5. Añadir aceite gradualmente\n6. Sazonar con sal y pimienta\n7. Licuar hasta obtener crema\n8. Ajustar consistencia con leche\n9. Refrigerar hasta usar",
                'expected_cost' => 1.80,
                'preparation_time' => 15.00,
            ],
        ];

        return $recipes[$product->name] ?? null;
    }

    private function getIngredientsForProduct(Product $product): array
    {
        $ingredients = [
            'Lomo Saltado' => [
                ['name' => 'Carne de Res', 'quantity' => 0.300, 'unit' => 'KG'],
                ['name' => 'Cebolla', 'quantity' => 0.100, 'unit' => 'KG'],
                ['name' => 'Tomate', 'quantity' => 0.150, 'unit' => 'KG'],
                ['name' => 'Papa', 'quantity' => 0.200, 'unit' => 'KG'],
                ['name' => 'Aceite Vegetal', 'quantity' => 0.030, 'unit' => 'LT'],
                ['name' => 'Sal', 'quantity' => 5.000, 'unit' => 'GR'],
                ['name' => 'Pimienta', 'quantity' => 2.000, 'unit' => 'GR'],
            ],
            'Ají de Gallina' => [
                ['name' => 'Pollo', 'quantity' => 0.250, 'unit' => 'KG'],
                ['name' => 'Papa', 'quantity' => 0.300, 'unit' => 'KG'],
                ['name' => 'Leche', 'quantity' => 0.200, 'unit' => 'LT'],
                ['name' => 'Queso', 'quantity' => 0.050, 'unit' => 'KG'],
                ['name' => 'Cebolla', 'quantity' => 0.080, 'unit' => 'KG'],
                ['name' => 'Ajo', 'quantity' => 0.010, 'unit' => 'KG'],
            ],
            'Arroz con Pollo' => [
                ['name' => 'Pollo', 'quantity' => 0.300, 'unit' => 'KG'],
                ['name' => 'Arroz', 'quantity' => 0.150, 'unit' => 'KG'],
                ['name' => 'Cilantro', 'quantity' => 0.050, 'unit' => 'KG'],
                ['name' => 'Cebolla', 'quantity' => 0.080, 'unit' => 'KG'],
                ['name' => 'Ajo', 'quantity' => 0.010, 'unit' => 'KG'],
                ['name' => 'Aceite Vegetal', 'quantity' => 0.025, 'unit' => 'LT'],
            ],
            'Ceviche de Pescado' => [
                ['name' => 'Pescado', 'quantity' => 0.200, 'unit' => 'KG'],
                ['name' => 'Limón', 'quantity' => 8.000, 'unit' => 'UND'],
                ['name' => 'Cebolla', 'quantity' => 0.100, 'unit' => 'KG'],
                ['name' => 'Cilantro', 'quantity' => 0.020, 'unit' => 'KG'],
                ['name' => 'Sal', 'quantity' => 3.000, 'unit' => 'GR'],
            ],
            'Pollo a la Brasa' => [
                ['name' => 'Pollo', 'quantity' => 1.000, 'unit' => 'KG'],
                ['name' => 'Papa', 'quantity' => 0.300, 'unit' => 'KG'],
                ['name' => 'Aceite Vegetal', 'quantity' => 0.050, 'unit' => 'LT'],
                ['name' => 'Sal', 'quantity' => 10.000, 'unit' => 'GR'],
                ['name' => 'Comino', 'quantity' => 3.000, 'unit' => 'GR'],
                ['name' => 'Pimienta', 'quantity' => 2.000, 'unit' => 'GR'],
            ],
            'Papa a la Huancaína' => [
                ['name' => 'Papa', 'quantity' => 0.400, 'unit' => 'KG'],
                ['name' => 'Queso', 'quantity' => 0.080, 'unit' => 'KG'],
                ['name' => 'Leche', 'quantity' => 0.100, 'unit' => 'LT'],
                ['name' => 'Huevos', 'quantity' => 1.000, 'unit' => 'UND'],
                ['name' => 'Aceite Vegetal', 'quantity' => 0.020, 'unit' => 'LT'],
            ],
            'Anticuchos' => [
                ['name' => 'Carne de Res', 'quantity' => 0.200, 'unit' => 'KG'],
                ['name' => 'Papa', 'quantity' => 0.200, 'unit' => 'KG'],
                ['name' => 'Ajo', 'quantity' => 0.015, 'unit' => 'KG'],
                ['name' => 'Comino', 'quantity' => 2.000, 'unit' => 'GR'],
                ['name' => 'Sal', 'quantity' => 3.000, 'unit' => 'GR'],
            ],
            'Causa Limeña' => [
                ['name' => 'Papa', 'quantity' => 0.500, 'unit' => 'KG'],
                ['name' => 'Pollo', 'quantity' => 0.150, 'unit' => 'KG'],
                ['name' => 'Limón', 'quantity' => 3.000, 'unit' => 'UND'],
                ['name' => 'Huevos', 'quantity' => 1.000, 'unit' => 'UND'],
            ],
            'Chicha Morada' => [
                ['name' => 'Azúcar', 'quantity' => 0.100, 'unit' => 'KG'],
                ['name' => 'Limón', 'quantity' => 2.000, 'unit' => 'UND'],
            ],
            'Limonada' => [
                ['name' => 'Limón', 'quantity' => 4.000, 'unit' => 'UND'],
                ['name' => 'Azúcar', 'quantity' => 0.050, 'unit' => 'KG'],
            ],
            'Suspiro Limeño' => [
                ['name' => 'Leche', 'quantity' => 0.200, 'unit' => 'LT'],
                ['name' => 'Azúcar', 'quantity' => 0.150, 'unit' => 'KG'],
                ['name' => 'Huevos', 'quantity' => 2.000, 'unit' => 'UND'],
            ],
            'Mazamorra Morada' => [
                ['name' => 'Azúcar', 'quantity' => 0.080, 'unit' => 'KG'],
                ['name' => 'Harina', 'quantity' => 0.030, 'unit' => 'KG'],
            ],
            'Salsa Criolla' => [
                ['name' => 'Cebolla', 'quantity' => 0.150, 'unit' => 'KG'],
                ['name' => 'Cilantro', 'quantity' => 0.020, 'unit' => 'KG'],
                ['name' => 'Limón', 'quantity' => 1.000, 'unit' => 'UND'],
                ['name' => 'Sal', 'quantity' => 2.000, 'unit' => 'GR'],
            ],
            'Salsa Huancaína' => [
                ['name' => 'Queso', 'quantity' => 0.100, 'unit' => 'KG'],
                ['name' => 'Leche', 'quantity' => 0.150, 'unit' => 'LT'],
                ['name' => 'Aceite Vegetal', 'quantity' => 0.030, 'unit' => 'LT'],
                ['name' => 'Sal', 'quantity' => 2.000, 'unit' => 'GR'],
            ],
        ];

        return $ingredients[$product->name] ?? [];
    }
}
