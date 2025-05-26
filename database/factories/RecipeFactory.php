<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory()->saleItem()->withRecipe(),
            'preparation_instructions' => $this->generateInstructions(),
            'expected_cost' => $this->faker->randomFloat(2, 5, 35),
            'preparation_time' => $this->faker->randomFloat(2, 10, 120), // minutes
        ];
    }

    public function simple()
    {
        return $this->state(function (array $attributes) {
            return [
                'preparation_instructions' => $this->generateSimpleInstructions(),
                'expected_cost' => $this->faker->randomFloat(2, 3, 15),
                'preparation_time' => $this->faker->randomFloat(2, 5, 30),
            ];
        });
    }

    public function complex()
    {
        return $this->state(function (array $attributes) {
            return [
                'preparation_instructions' => $this->generateComplexInstructions(),
                'expected_cost' => $this->faker->randomFloat(2, 20, 60),
                'preparation_time' => $this->faker->randomFloat(2, 45, 180),
            ];
        });
    }

    public function beverage()
    {
        return $this->state(function (array $attributes) {
            return [
                'preparation_instructions' => $this->generateBeverageInstructions(),
                'expected_cost' => $this->faker->randomFloat(2, 1, 8),
                'preparation_time' => $this->faker->randomFloat(2, 2, 15),
            ];
        });
    }

    public function dessert()
    {
        return $this->state(function (array $attributes) {
            return [
                'preparation_instructions' => $this->generateDessertInstructions(),
                'expected_cost' => $this->faker->randomFloat(2, 8, 25),
                'preparation_time' => $this->faker->randomFloat(2, 20, 90),
            ];
        });
    }

    public function mainDish()
    {
        return $this->state(function (array $attributes) {
            return [
                'preparation_instructions' => $this->generateMainDishInstructions(),
                'expected_cost' => $this->faker->randomFloat(2, 15, 45),
                'preparation_time' => $this->faker->randomFloat(2, 25, 60),
            ];
        });
    }

    private function generateInstructions()
    {
        $instructions = [
            $this->generateMainDishInstructions(),
            $this->generateSimpleInstructions(),
            $this->generateComplexInstructions(),
            $this->generateBeverageInstructions(),
            $this->generateDessertInstructions()
        ];

        return $this->faker->randomElement($instructions);
    }

    private function generateSimpleInstructions()
    {
        $simple = [
            "1. Preparar todos los ingredientes\n2. Mezclar en un bowl\n3. Servir inmediatamente",
            "1. Calentar la sartén\n2. Agregar los ingredientes\n3. Cocinar por 5 minutos\n4. Servir caliente",
            "1. Lavar y cortar los ingredientes\n2. Mezclar con el aderezo\n3. Dejar reposar 5 minutos\n4. Servir fresco",
            "1. Hervir agua\n2. Agregar los ingredientes\n3. Cocinar por 10 minutos\n4. Servir en platos hondos"
        ];

        return $this->faker->randomElement($simple);
    }

    private function generateComplexInstructions()
    {
        $complex = [
            "1. Marinar la carne por 2 horas\n2. Preparar el sofrito con cebolla y ajo\n3. Sellar la carne a fuego alto\n4. Agregar verduras y condimentos\n5. Cocinar a fuego lento por 45 minutos\n6. Ajustar sazón y servir con guarnición",
            
            "1. Preparar el mise en place de todos los ingredientes\n2. Calentar el wok a fuego alto\n3. Saltear la carne hasta dorar\n4. Agregar verduras en orden de cocción\n5. Incorporar la salsa y mezclar\n6. Servir inmediatamente sobre arroz",
            
            "1. Limpiar y cortar el pescado en cubos\n2. Preparar la leche de tigre con limón y ají\n3. Marinar el pescado por 15 minutos\n4. Cortar las verduras en juliana\n5. Mezclar todos los ingredientes\n6. Servir con camote y choclo",
            
            "1. Deshuesar y cortar el pollo\n2. Preparar el ají amarillo licuado\n3. Hacer un sofrito con cebolla y ajo\n4. Agregar el ají y cocinar\n5. Incorporar el pollo desmenuzado\n6. Añadir pan remojado y nueces\n7. Servir con papa sancochada"
        ];

        return $this->faker->randomElement($complex);
    }

    private function generateMainDishInstructions()
    {
        $mainDishes = [
            "1. Cortar la carne en tiras\n2. Sazonar con sal y pimienta\n3. Calentar aceite en wok\n4. Saltear carne a fuego alto\n5. Agregar cebolla y tomate\n6. Incorporar papas fritas\n7. Servir con arroz",
            
            "1. Limpiar y trozar el pollo\n2. Marinar con especias\n3. Dorar en sartén caliente\n4. Agregar sofrito de verduras\n5. Añadir arroz y caldo\n6. Cocinar hasta que el arroz esté tierno\n7. Decorar con cilantro",
            
            "1. Preparar masa para empanadas\n2. Hacer relleno con carne y verduras\n3. Armar las empanadas\n4. Sellar bien los bordes\n5. Freír en aceite caliente\n6. Escurrir y servir caliente"
        ];

        return $this->faker->randomElement($mainDishes);
    }

    private function generateBeverageInstructions()
    {
        $beverages = [
            "1. Hervir agua con especias\n2. Agregar endulzante al gusto\n3. Colar y servir caliente",
            "1. Licuar frutas con agua\n2. Colar para quitar semillas\n3. Endulzar al gusto\n4. Servir con hielo",
            "1. Preparar chicha morada concentrada\n2. Agregar frutas picadas\n3. Endulzar con azúcar\n4. Servir bien fría",
            "1. Exprimir limones frescos\n2. Mezclar con agua fría\n3. Endulzar al gusto\n4. Servir con hielo y menta"
        ];

        return $this->faker->randomElement($beverages);
    }

    private function generateDessertInstructions()
    {
        $desserts = [
            "1. Batir huevos con azúcar\n2. Agregar harina tamizada\n3. Incorporar mantequilla derretida\n4. Hornear a 180°C por 25 minutos\n5. Decorar con azúcar impalpable",
            
            "1. Preparar manjar blanco\n2. Hacer masa quebrada\n3. Forrar moldes con la masa\n4. Rellenar con manjar\n5. Hornear hasta dorar\n6. Espolvorear con canela",
            
            "1. Remojar galletas en café\n2. Preparar crema mascarpone\n3. Alternar capas en molde\n4. Refrigerar por 4 horas\n5. Espolvorear con cacao\n6. Servir bien frío",
            
            "1. Cocinar quinua con leche\n2. Agregar canela y clavo\n3. Endulzar con azúcar\n4. Cocinar hasta espesar\n5. Servir tibio o frío\n6. Decorar con pasas"
        ];

        return $this->faker->randomElement($desserts);
    }
}
