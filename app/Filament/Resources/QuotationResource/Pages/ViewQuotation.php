<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Quotation;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información General')
                    ->schema([
                        Infolists\Components\TextEntry::make('quotation_number')
                            ->label('Número de Cotización'),

                        Infolists\Components\TextEntry::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->date('d/m/Y'),

                        Infolists\Components\TextEntry::make('valid_until')
                            ->label('Válido Hasta')
                            ->date('d/m/Y')
                            ->color(fn (Quotation $record): string =>
                                $record->valid_until < now() && !$record->isConverted() ? 'danger' : 'success'
                            ),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state): string =>
                                match ($state) {
                                    'draft' => 'gray',
                                    'sent' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'expired' => 'warning',
                                    'converted' => 'primary',
                                    default => 'gray',
                                }
                            )
                            ->formatStateUsing(fn (string $state): string =>
                                match ($state) {
                                    'draft' => 'Borrador',
                                    'sent' => 'Enviada',
                                    'approved' => 'Aprobada',
                                    'rejected' => 'Rechazada',
                                    'expired' => 'Vencida',
                                    'converted' => 'Convertida',
                                    default => $state,
                                }
                            ),

                        Infolists\Components\TextEntry::make('payment_terms')
                            ->label('Términos de Pago')
                            ->formatStateUsing(fn (string $state): string =>
                                match ($state) {
                                    'cash' => 'Contado',
                                    'credit_15' => 'Crédito 15 días',
                                    'credit_30' => 'Crédito 30 días',
                                    'credit_60' => 'Crédito 60 días',
                                    default => $state,
                                }
                            ),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Usuario'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Cliente')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Nombre'),

                        Infolists\Components\TextEntry::make('customer.document_type')
                            ->label('Tipo de Documento'),

                        Infolists\Components\TextEntry::make('customer.document_number')
                            ->label('Número de Documento'),

                        Infolists\Components\TextEntry::make('customer.phone')
                            ->label('Teléfono'),

                        Infolists\Components\TextEntry::make('customer.email')
                            ->label('Correo Electrónico'),

                        Infolists\Components\TextEntry::make('customer.address')
                            ->label('Dirección'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Productos')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('details')
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Producto'),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Cantidad'),

                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('Precio Unitario')
                                    ->money('PEN'),

                                Infolists\Components\TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('PEN'),

                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notas')
                                    ->columnSpan(4),
                            ])
                            ->columns(4),
                    ]),

                Infolists\Components\Section::make('Totales')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('PEN'),

                        Infolists\Components\TextEntry::make('tax')
                            ->label('IGV (18%)')
                            ->money('PEN'),

                        Infolists\Components\TextEntry::make('discount')
                            ->label('Descuento')
                            ->money('PEN'),

                        Infolists\Components\TextEntry::make('total')
                            ->label('Total')
                            ->money('PEN')
                            ->size('xl')
                            ->weight('bold')
                            ->color('primary'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Notas y Condiciones')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notas')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('terms_and_conditions')
                            ->label('Términos y Condiciones')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (Quotation $record) => !$record->isConverted()),

            Actions\Action::make('send')
                ->label('Enviar')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn () => $this->record->isDraft() && !$this->record->isConverted())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->status = Quotation::STATUS_SENT;
                    $this->record->save();

                    Notification::make()
                        ->title('Cotización enviada correctamente')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('approve')
                ->label('Aprobar')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record->isSent() && !$this->record->isConverted())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->status = Quotation::STATUS_APPROVED;
                    $this->record->save();

                    Notification::make()
                        ->title('Cotización aprobada correctamente')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject')
                ->label('Rechazar')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record->isSent() && !$this->record->isConverted())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->status = Quotation::STATUS_REJECTED;
                    $this->record->save();

                    Notification::make()
                        ->title('Cotización rechazada correctamente')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('convert')
                ->label('Convertir a Pedido')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->visible(fn () => $this->record->isApproved() && !$this->record->isConverted())
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        // Convertir la cotización a pedido
                        $order = $this->record->convertToOrder();

                        // Actualizar el estado de la cotización
                        $this->record->status = Quotation::STATUS_CONVERTED;
                        $this->record->order_id = $order->id;
                        $this->record->save();

                        // Notificar al usuario
                        Notification::make()
                            ->title('Cotización convertida a pedido correctamente')
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('Ver Pedido')
                                    ->url('/pos?order_id=' . $order->id)
                                    ->openUrlInNewTab(),
                            ])
                            ->send();
                    } catch (\Exception $e) {
                        // Si hay un error, notificar al usuario
                        Notification::make()
                            ->title('Error al convertir la cotización a pedido')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('print')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.quotations.print', ['quotation' => $this->record]))
                ->openUrlInNewTab(),

            Actions\Action::make('download')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.quotations.download', ['quotation' => $this->record]))
                ->openUrlInNewTab(),

            Actions\Action::make('email')
                ->label('Enviar por Email')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->required()
                        ->default(fn () => $this->record->customer->email ?? ''),

                    \Filament\Forms\Components\TextInput::make('subject')
                        ->label('Asunto')
                        ->default(fn () => 'Cotización ' . $this->record->quotation_number),

                    \Filament\Forms\Components\Textarea::make('message')
                        ->label('Mensaje')
                        ->default('Adjuntamos la cotización solicitada. Por favor, revise los detalles y no dude en contactarnos si tiene alguna pregunta.'),
                ])
                ->action(function (array $data) {
                    // Enviar la cotización por correo electrónico
                    $response = \Illuminate\Support\Facades\Http::post(
                        route('filament.admin.resources.quotations.email', ['quotation' => $this->record]),
                        $data
                    );

                    if ($response->successful()) {
                        Notification::make()
                            ->title('Cotización enviada')
                            ->body('La cotización ha sido enviada correctamente a ' . $data['email'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error')
                            ->body('Ha ocurrido un error al enviar la cotización: ' . $response->body())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
