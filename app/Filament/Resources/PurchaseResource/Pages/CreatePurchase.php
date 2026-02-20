<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Purchase;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

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

    protected function afterCreate(): void
    {
        try {
            $purchase = $this->record->fresh();

            if ($purchase?->status === Purchase::STATUS_COMPLETED) {
                Notification::make()
                    ->title('Compra registrada exitosamente')
                    ->body('La compra se guardo como COMPLETADA y el stock se registro automaticamente.')
                    ->success()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Compra registrada')
                ->body('La compra quedo en PENDIENTE. El stock se registrara cuando pase a COMPLETADA.')
                ->success()
                ->send();
        } catch (QueryException $e) {
            Notification::make()
                ->title('Problema al registrar compra')
                ->body($this->getFriendlyErrorMessage($e))
                ->danger()
                ->persistent()
                ->send();

            Log::error('Error en afterCreate de Purchase', [
                'purchase_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('Ocurrio algo inesperado. Intenta nuevamente.')
                ->danger()
                ->send();

            Log::error('Error general en afterCreate de Purchase', [
                'purchase_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
