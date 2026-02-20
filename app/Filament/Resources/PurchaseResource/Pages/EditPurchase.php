<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Purchase;
use Exception;
use Filament\Actions;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    /**
     * Convierte errores tecnicos de base de datos en mensajes simples para usuarios.
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = (string) $exception->getCode();
        $errorMessage = $exception->getMessage();

        if ($errorCode === '23000' && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'supplier_id')) {
                return 'El proveedor seleccionado no existe. Verifica que este registrado correctamente.';
            }
            if (str_contains($errorMessage, 'product_id')) {
                return 'Uno de los productos seleccionados no existe. Revisa la lista de productos.';
            }
            if (str_contains($errorMessage, 'warehouse_id')) {
                return 'El almacen seleccionado no existe. Elige otro almacen.';
            }

            return 'No se puede guardar porque hay datos relacionados que no existen.';
        }

        if ($errorCode === '23000' && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'document_number')) {
                return 'Ya existe una compra con ese numero de documento. Cambia el numero.';
            }

            return 'Ya existe un registro con esos datos. Revisa y cambia los valores duplicados.';
        }

        if ($errorCode === '23000' && str_contains($errorMessage, 'cannot be null')) {
            return 'Faltan completar algunos campos obligatorios. Revisa los marcados con asterisco (*).';
        }

        if (in_array($errorCode, ['2002', '2003', '2006'], true)) {
            return 'Problema de conexion. Espera unos segundos y vuelve a intentar.';
        }

        if ($errorCode === '1213') {
            return 'Los datos estan ocupados por otro proceso. Cierra esta ventana y vuelve a intentar.';
        }

        return 'Ocurrio un problema al guardar. Revisa los datos e intenta de nuevo.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('register_stock')
                ->label('Registrar Stock')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== Purchase::STATUS_COMPLETED)
                ->action(function (): void {
                    $this->registerStock();
                }),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->status === Purchase::STATUS_COMPLETED) {
            return;
        }

        Notification::make()
            ->title('Desea registrar el stock?')
            ->body('La compra fue guardada. Puedes registrar el stock ahora o despues.')
            ->actions([
                NotificationAction::make('register')
                    ->label('Registrar ahora')
                    ->color('success')
                    ->button()
                    ->close()
                    ->action(function (): void {
                        $this->registerStock();
                    }),
                NotificationAction::make('later')
                    ->label('Mas tarde')
                    ->close(),
            ])
            ->persistent()
            ->send();
    }

    private function registerStock(): void
    {
        try {
            $purchase = $this->record->fresh();

            if (! $purchase || $purchase->status === Purchase::STATUS_COMPLETED) {
                Notification::make()
                    ->title('La compra ya esta completada')
                    ->body('El stock ya fue registrado previamente.')
                    ->warning()
                    ->send();

                return;
            }

            $purchase->status = Purchase::STATUS_COMPLETED;
            $purchase->save();

            $this->record = $purchase->fresh();

            Notification::make()
                ->title('Stock registrado')
                ->body('La compra fue marcada como COMPLETADA y el stock se proceso automaticamente.')
                ->success()
                ->send();
        } catch (QueryException $e) {
            Notification::make()
                ->title('Problema al registrar el stock')
                ->body($this->getFriendlyErrorMessage($e))
                ->danger()
                ->persistent()
                ->send();

            Log::error('Error en register_stock action', [
                'purchase_id' => $this->record->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('Ocurrio algo inesperado. Intenta de nuevo.')
                ->danger()
                ->send();

            Log::error('Error general en register_stock', [
                'purchase_id' => $this->record->id,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
