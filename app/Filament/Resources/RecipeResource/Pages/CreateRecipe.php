<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key)
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'product_id')) {
                return "🚫 El producto seleccionado no existe. Verifica que esté registrado correctamente.";
            }
            if (str_contains($errorMessage, 'ingredient_id')) {
                return "🚫 Uno de los ingredientes seleccionados no existe. Revisa la lista de ingredientes.";
            }
            return "🚫 Hay datos relacionados que no existen. Revisa las selecciones.";
        }

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'name')) {
                return "📝 Ya existe una receta con ese nombre. Usa un nombre diferente.";
            }
            return "📝 Ya existe una receta con esos datos. Revisa y cambia los valores.";
        }

        // Errores de campo requerido (not null)
        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
            if (str_contains($errorMessage, 'name')) {
                return "📝 El nombre de la receta es obligatorio. Completa este campo.";
            }
            if (str_contains($errorMessage, 'product_id')) {
                return "🍽️ Es obligatorio seleccionar un producto para la receta.";
            }
            return "📝 Faltan completar algunos campos obligatorios. Revisa los marcados con asterisco (*).";
        }

        // Errores de conexión
        if (in_array($errorCode, ['2002', '2003', '2006'])) {
            return "🌐 Problema de conexión. Espera 10 segundos y vuelve a intentar.";
        }

        // Deadlock o bloqueo de datos
        if ($errorCode == 1213) {
            return "⏳ Los datos están ocupados. Cierra esta ventana, espera 5 segundos y abre de nuevo.";
        }

        // Error genérico
        return "😅 Ocurrió un problema al crear la receta. Revisa los datos e intenta de nuevo.";
    }

    protected function afterCreate(): void
    {
        try {
            Notification::make()
                ->title('¡Receta creada!')
                ->body('La receta ha sido registrada correctamente 👨‍🍳')
                ->success()
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('Problema al crear la receta')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error en afterCreate de Recipe: ' . $e->getMessage(), [
                'recipe_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('😅 Ocurrió algo inesperado. Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterCreate de Recipe: ' . $e->getMessage(), [
                'recipe_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
