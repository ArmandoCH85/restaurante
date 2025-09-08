<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key)
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            return "🚫 Hay proveedores que no se pueden mostrar porque tienen datos relacionados eliminados.";
        }

        // Errores de conexión
        if (in_array($errorCode, ['2002', '2003', '2006'])) {
            return "🌐 Problema de conexión al cargar la lista de proveedores. Espera 10 segundos y vuelve a intentar.";
        }

        // Deadlock o bloqueo de datos
        if ($errorCode == 1213) {
            return "⏳ Los datos están ocupados. Cierra esta ventana, espera 5 segundos y abre de nuevo.";
        }

        // Error genérico
        return "😅 Ocurrió un problema al cargar la lista de proveedores. Intenta recargar la página.";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successNotificationTitle('¡Proveedor creado!')
                ->successNotification(function () {
                    return Notification::make()
                        ->title('¡Proveedor creado!')
                        ->body('El proveedor ha sido agregado correctamente a la lista ✅')
                        ->success();
                })
                ->failureNotification(function (QueryException $exception) {
                    $friendlyMessage = $this->getFriendlyErrorMessage($exception);

                    return Notification::make()
                        ->title('Problema al crear el proveedor')
                        ->body($friendlyMessage)
                        ->danger()
                        ->persistent();
                }),

            // Acción adicional para recargar la lista
            Actions\Action::make('refresh')
                ->label('Actualizar lista')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    try {
                        // Forzar recarga de la página
                        return redirect()->refresh();

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Problema al actualizar')
                            ->body('😅 No se pudo actualizar la lista. Intenta recargar la página manualmente.')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar proveedores seleccionados')
                    ->modalDescription('¿Estás seguro de que quieres eliminar estos proveedores? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar proveedores')
                    ->before(function () {
                        // Validar que los proveedores seleccionados puedan ser eliminados
                        $selectedRecords = $this->getSelectedTableRecords();

                        foreach ($selectedRecords as $record) {
                            if ($record->purchases()->exists()) {
                                throw new \Exception("Uno o más proveedores no pueden ser eliminados porque tienen compras registradas.");
                            }

                            if ($record->products()->exists()) {
                                throw new \Exception("Uno o más proveedores no pueden ser eliminados porque están asignados a productos.");
                            }
                        }
                    })
                    ->action(function () {
                        try {
                            $selectedRecords = $this->getSelectedTableRecords();
                            $count = $selectedRecords->count();

                            foreach ($selectedRecords as $record) {
                                $record->delete();
                            }

                            Notification::make()
                                ->title('¡Proveedores eliminados!')
                                ->body("Se eliminaron {$count} proveedores correctamente ✅")
                                ->success()
                                ->send();

                        } catch (QueryException $e) {
                            $friendlyMessage = $this->getFriendlyErrorMessage($e);

                            Notification::make()
                                ->title('No se pueden eliminar los proveedores')
                                ->body($friendlyMessage)
                                ->danger()
                                ->persistent()
                                ->send();

                            \Illuminate\Support\Facades\Log::error('Error en bulk delete de proveedores: ' . $e->getMessage(), [
                                'selected_count' => $this->getSelectedTableRecords()->count(),
                                'error_code' => $e->getCode(),
                                'error_message' => $e->getMessage()
                            ]);

                        } catch (Exception $e) {
                            $friendlyMessage = "🚫 " . $e->getMessage();

                            Notification::make()
                                ->title('No se pueden eliminar los proveedores')
                                ->body($friendlyMessage)
                                ->danger()
                                ->persistent()
                                ->send();

                            \Illuminate\Support\Facades\Log::error('Error general en bulk delete de proveedores: ' . $e->getMessage(), [
                                'selected_count' => $this->getSelectedTableRecords()->count()
                            ]);
                        }
                    }),
            ]),
        ];
    }
}
