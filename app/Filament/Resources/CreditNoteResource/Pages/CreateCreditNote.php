<?php

namespace App\Filament\Resources\CreditNoteResource\Pages;

use App\Filament\Resources\CreditNoteResource;
use App\Models\Invoice;
use App\Helpers\SunatServiceHelper;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateCreditNote extends CreateRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Obtener la factura relacionada
        $invoice = Invoice::findOrFail($data['invoice_id']);
        
        // Generar serie y número automáticamente
        $lastCreditNote = \App\Models\CreditNote::where('serie', 'LIKE', 'NC%')
            ->orderBy('numero', 'desc')
            ->first();
        
        $nextNumber = $lastCreditNote ? $lastCreditNote->numero + 1 : 1;
        
        $data['serie'] = 'NC01';
        $data['numero'] = $nextNumber;
        $data['fecha_emision'] = now();
        $data['sunat_status'] = 'PENDIENTE';
        
        // Copiar importes de la factura
        $data['monto_operaciones_gravadas'] = $invoice->taxable_amount;
        $data['monto_igv'] = $invoice->tax;
        $data['monto_total'] = $invoice->total;
        
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Crear el registro de la nota de crédito
            $creditNote = static::getModel()::create($data);
            
            // Enviar a SUNAT automáticamente
            $invoice = Invoice::findOrFail($data['invoice_id']);
            $sunatService = SunatServiceHelper::createIfNotTesting();
            
            if ($sunatService === null) {
                $creditNote->update([
                    'sunat_status' => 'PENDIENTE',
                    'sunat_response' => 'Modo testing - SUNAT deshabilitado',
                ]);
                
                Notification::make()
                    ->title('Nota de crédito creada (modo testing)')
                    ->body("Serie: {$creditNote->serie}-{$creditNote->numero}")
                    ->success()
                    ->send();
                
                return $creditNote;
            }
            
            $result = $sunatService->emitirNotaCredito(
                $invoice,
                $data['motivo_codigo'],
                $data['motivo_descripcion']
            );
            
            // Actualizar el registro con la información de SUNAT
            if ($result['success']) {
                $creditNote->update([
                    'xml_path' => $result['credit_note']->xml_path ?? null,
                    'cdr_path' => $result['credit_note']->cdr_path ?? null,
                    'sunat_status' => $result['credit_note']->sunat_status ?? 'PENDIENTE',
                    'sunat_response' => json_encode($result['sunat_response']) ?? null,
                ]);
            } else {
                $creditNote->update([
                    'sunat_status' => 'ERROR',
                    'sunat_response' => $result['error'] ?? 'Error desconocido',
                ]);
            }
            
            Notification::make()
                ->title('Nota de crédito creada y enviada a SUNAT')
                ->body("Serie: {$creditNote->serie}-{$creditNote->numero}")
                ->success()
                ->send();
            
            return $creditNote;
            
        } catch (\Exception $e) {
            // Si falla el envío a SUNAT, crear solo el registro local
            $creditNote = static::getModel()::create(array_merge($data, [
                'sunat_status' => 'ERROR',
                'sunat_response' => 'Error al enviar a SUNAT: ' . $e->getMessage(),
            ]));
            
            Notification::make()
                ->title('Nota de crédito creada con errores')
                ->body('La nota se creó localmente pero no se pudo enviar a SUNAT: ' . $e->getMessage())
                ->warning()
                ->send();
            
            return $creditNote;
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Crear y Enviar a SUNAT')
            ->requiresConfirmation()
            ->modalHeading('Confirmar creación de Nota de Crédito')
            ->modalDescription('¿Está seguro de que desea crear esta nota de crédito y enviarla automáticamente a SUNAT?')
            ->modalSubmitActionLabel('Sí, crear y enviar');
    }

    public function getTitle(): string
    {
        return 'Crear Nota de Crédito';
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}