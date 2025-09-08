<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class ListDeliveryOrders extends ListRecords
{
    protected static string $resource = DeliveryOrderResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key)
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            return "🚫 Hay pedidos de delivery que no se pueden mostrar porque tienen datos relacionados eliminados.";
        }

        // Errores de conexión
        if (in_array($errorCode, ['2002', '2003', '2006'])) {
            return "🌐 Problema de conexión al cargar la lista de pedidos de delivery. Espera 10 segundos y vuelve a intentar.";
        }

        // Deadlock o bloqueo de datos
        if ($errorCode == 1213) {
            return "⏳ Los datos están ocupados. Cierra esta ventana, espera 5 segundos y abre de nuevo.";
        }

        // Error genérico
        return "😅 Ocurrió un problema al cargar la lista de pedidos de delivery. Intenta recargar la página.";
    }

    protected function getHeaderActions(): array
    {
        // Obtener el usuario actual
        $user = \Illuminate\Support\Facades\Auth::user();

        $actions = [];

        // Si el usuario NO tiene rol Delivery, mostrar el botón de crear
        if (!$user || $user->roles->where('name', 'Delivery')->count() == 0) {
            $actions[] = Actions\CreateAction::make()
                ->successNotificationTitle('¡Pedido de delivery creado!')
                ->successNotification(function () {
                    return Notification::make()
                        ->title('¡Pedido de delivery creado!')
                        ->body('El pedido de delivery ha sido registrado correctamente 🚚')
                        ->success();
                })
                ->failureNotification(function (QueryException $exception) {
                    $friendlyMessage = $this->getFriendlyErrorMessage($exception);

                    return Notification::make()
                        ->title('Problema al crear el pedido de delivery')
                        ->body($friendlyMessage)
                        ->danger()
                        ->persistent();
                });
        }

        // Acción adicional para recargar la lista
        $actions[] = Actions\Action::make('refresh')
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
            });

        return $actions;
    }

    protected function getTableBulkActions(): array
    {
        return [
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar pedidos de delivery seleccionados')
                    ->modalDescription('¿Estás seguro de que quieres eliminar estos pedidos de delivery? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar pedidos')
                    ->before(function () {
                        // Validar que los pedidos seleccionados puedan ser eliminados
                        $selectedRecords = $this->getSelectedTableRecords();

                        foreach ($selectedRecords as $record) {
                            // Verificar si el pedido tiene pagos registrados
                            if ($record->order && $record->order->payments()->exists()) {
                                throw new \Exception("Uno o más pedidos no pueden ser eliminados porque tienen pagos registrados.");
                            }

                            // Verificar si el pedido está completado
                            if ($record->order && $record->order->status === 'completed') {
                                throw new \Exception("Uno o más pedidos no pueden ser eliminados porque ya están completados.");
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
                                ->title('¡Pedidos de delivery eliminados!')
                                ->body("Se eliminaron {$count} pedidos de delivery correctamente ✅")
                                ->success()
                                ->send();

                        } catch (QueryException $e) {
                            $friendlyMessage = $this->getFriendlyErrorMessage($e);

                            Notification::make()
                                ->title('No se pueden eliminar los pedidos de delivery')
                                ->body($friendlyMessage)
                                ->danger()
                                ->persistent()
                                ->send();

                            \Illuminate\Support\Facades\Log::error('Error en bulk delete de delivery orders: ' . $e->getMessage(), [
                                'selected_count' => $this->getSelectedTableRecords()->count(),
                                'error_code' => $e->getCode(),
                                'error_message' => $e->getMessage()
                            ]);

                        } catch (Exception $e) {
                            $friendlyMessage = "🚫 " . $e->getMessage();

                            Notification::make()
                                ->title('No se pueden eliminar los pedidos de delivery')
                                ->body($friendlyMessage)
                                ->danger()
                                ->persistent()
                                ->send();

                            \Illuminate\Support\Facades\Log::error('Error general en bulk delete de delivery orders: ' . $e->getMessage(), [
                                'selected_count' => $this->getSelectedTableRecords()->count()
                            ]);
                        }
                    }),
            ]),
        ];
    }
}
