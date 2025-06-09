<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ACCIÓN PARA CAMBIAR ESTADO
            Actions\Action::make('change_status')
                ->label('Cambiar Estado')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => !$this->record->billed && $this->record->status !== 'cancelled')
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('Nuevo Estado')
                        ->options([
                            'open' => 'Abierta',
                            'in_preparation' => 'En Preparación',
                            'ready' => 'Lista',
                            'delivered' => 'Entregada',
                            'completed' => 'Completada',
                            'cancelled' => 'Cancelada',
                        ])
                        ->default($this->record->status)
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas adicionales')
                        ->placeholder('Motivo del cambio...')
                        ->visible(fn (Forms\Get $get) => $get('status') === 'cancelled'),
                ])
                ->action(function (array $data) {
                    try {
                        if ($data['status'] === 'cancelled') {
                            $this->record->cancelOrder($data['notes'] ?? null);
                        } else {
                            $this->record->update(['status' => $data['status']]);
                        }

                        Notification::make()
                            ->title('Estado actualizado exitosamente')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al cambiar estado')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ACCIÓN RÁPIDA PARA AGREGAR PRODUCTOS
            Actions\Action::make('add_product')
                ->label('Agregar Producto')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->visible(fn () => $this->record->status === 'open')
                ->form([
                    Forms\Components\Section::make('Agregar Producto')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Producto')
                                ->options(Product::where('product_type', 'sale_item')->orWhere('product_type', 'both')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->sale_price);
                                        }
                                    }
                                }),

                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Cantidad')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required(),

                                    Forms\Components\TextInput::make('unit_price')
                                        ->label('Precio Unitario')
                                        ->numeric()
                                        ->prefix('S/')
                                        ->required(),

                                    Forms\Components\Textarea::make('notes')
                                        ->label('Notas')
                                        ->placeholder('Sin cebolla, extra salsa...')
                                        ->rows(2),
                                ]),
                        ]),
                ])
                ->action(function (array $data) {
                    try {
                        $this->record->addProduct(
                            $data['product_id'],
                            $data['quantity'],
                            $data['unit_price'],
                            $data['notes'] ?? null
                        );

                        Notification::make()
                            ->title('Producto agregado exitosamente')
                            ->success()
                            ->send();

                        // Recargar la página para mostrar el nuevo producto
                        redirect($this->getUrl());

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al agregar producto')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ACCIÓN PARA VER PAGOS
            Actions\Action::make('view_payments')
                ->label('Ver Pagos')
                ->icon('heroicon-o-banknotes')
                ->color('info')
                ->visible(fn () => $this->record->payments->count() > 0)
                ->form([
                    Forms\Components\Section::make('Pagos Registrados')
                        ->schema([
                            Forms\Components\Placeholder::make('payments_info')
                                ->label('')
                                ->content(function () {
                                    $payments = $this->record->payments;
                                    $content = '';

                                    foreach ($payments as $payment) {
                                        $method = match($payment->payment_method) {
                                            'cash' => 'Efectivo',
                                            'credit_card' => 'Tarjeta de Crédito',
                                            'debit_card' => 'Tarjeta de Débito',
                                            'bank_transfer' => 'Transferencia',
                                            'digital_wallet' => 'Billetera Digital',
                                            default => $payment->payment_method
                                        };

                                        $content .= "• {$method}: S/ {$payment->amount}";
                                        if ($payment->reference_number) {
                                            $content .= " - Ref: {$payment->reference_number}";
                                        }
                                        $content .= " ({$payment->payment_datetime->format('d/m/Y H:i')})\n";
                                    }

                                    $content .= "\nTotal Pagado: S/ {$this->record->getTotalPaid()}";
                                    $content .= "\nSaldo Pendiente: S/ {$this->record->getRemainingBalance()}";

                                    return new \Illuminate\Support\HtmlString(nl2br($content));
                                }),
                        ]),
                ])
                ->action(function () {
                    // Solo cerrar el modal
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'open' && !$this->record->billed),
        ];
    }
}
