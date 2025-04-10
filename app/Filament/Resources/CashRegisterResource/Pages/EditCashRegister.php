<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditCashRegister extends EditRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('close')
                ->label('Cerrar Caja')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'open')
                ->form([
                    \Filament\Forms\Components\TextInput::make('actual_cash')
                        ->label('Efectivo Real en Caja')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('S/'),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->placeholder('Observaciones sobre el cierre de caja'),
                    \Filament\Forms\Components\Checkbox::make('confirm')
                        ->label('Confirmo que la información es correcta y deseo cerrar la caja')
                        ->required()
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    if (!$data['confirm']) {
                        Notification::make()
                            ->title('Debe confirmar el cierre de caja')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Calcular totales
                    $payments = Payment::where('cash_register_id', $this->record->id)
                        ->select(
                            'payment_method',
                            DB::raw('SUM(amount) as total')
                        )
                        ->groupBy('payment_method')
                        ->get()
                        ->keyBy('payment_method');

                    $cashSales = $payments->get('cash', (object)['total' => 0])->total;
                    $cardSales = $payments->get('credit_card', (object)['total' => 0])->total +
                                $payments->get('debit_card', (object)['total' => 0])->total;
                    $otherSales = $payments->get('bank_transfer', (object)['total' => 0])->total +
                                 $payments->get('digital_wallet', (object)['total' => 0])->total;
                    $totalSales = $cashSales + $cardSales + $otherSales;
                    $expectedCash = $this->record->opening_amount + $cashSales;
                    $actualCash = $data['actual_cash'];
                    $difference = $actualCash - $expectedCash;

                    // Actualizar el registro
                    $closeData = [
                        'closed_by' => Auth::id(),
                        'cash_sales' => $cashSales,
                        'card_sales' => $cardSales,
                        'other_sales' => $otherSales,
                        'total_sales' => $totalSales,
                        'expected_cash' => $expectedCash,
                        'actual_cash' => $actualCash,
                        'difference' => $difference,
                        'notes' => $data['notes'],
                    ];

                    if ($this->record->close($closeData)) {
                        Notification::make()
                            ->title('Caja cerrada correctamente')
                            ->success()
                            ->send();
                        $this->redirect(CashRegisterResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->title('Error al cerrar la caja')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
