<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\CashRegister;
use App\Models\Payment;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;

class ViewCashRegister extends ViewRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Cerrar Caja')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(function ($record) {
                    // Solo permitir cerrar cajas abiertas
                    return $record->is_active && $this->canCloseCashRegister();
                }),

            Actions\Action::make('print')
                ->label('Imprimir Informe')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn ($record) => url("/admin/print-cash-register/{$record->id}"))
                ->openUrlInNewTab()
                ->visible(function ($record) {
                    // Solo permitir imprimir cajas cerradas
                    return !$record->is_active || Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager']);
                }),

            Actions\Action::make('reconcile')
                ->label('Reconciliar y Aprobar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Section::make('Reconciliación de Caja')
                        ->description('Verifique los montos contados contra los esperados')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('expected_amount')
                                        ->label('Monto Esperado')
                                        ->disabled()
                                        ->prefix('S/')
                                        ->formatStateUsing(fn ($record) => number_format($record->expected_amount, 2)),

                                    Forms\Components\TextInput::make('actual_amount')
                                        ->label('Monto Contado')
                                        ->disabled()
                                        ->prefix('S/')
                                        ->formatStateUsing(fn ($record) => number_format($record->actual_amount, 2)),

                                    Forms\Components\TextInput::make('difference')
                                        ->label('Diferencia')
                                        ->disabled()
                                        ->prefix('S/')
                                        ->formatStateUsing(fn ($record) => number_format($record->difference, 2))
                                        ->extraAttributes(function ($record) {
                                            $color = $record->difference < 0 ? 'red' : ($record->difference > 0 ? 'orange' : 'green');
                                            return ['style' => "color: {$color}; font-weight: bold;"];
                                        }),
                                ]),

                            Forms\Components\Textarea::make('approval_notes')
                                ->label('Notas de Aprobación')
                                ->placeholder('Ingrese notas sobre la reconciliación (obligatorio si hay diferencias)')
                                ->required(function ($record) {
                                    return $record->difference != 0;
                                })
                                ->helperText('Explique las razones de las diferencias y las acciones tomadas'),

                            Forms\Components\Toggle::make('is_approved')
                                ->label('Aprobar Cierre de Caja')
                                ->helperText('Al aprobar, confirma que ha verificado los montos y acepta las diferencias')
                                ->default(true)
                                ->required(),
                        ]),
                ])
                ->action(function (array $data, $record) {
                    try {
                        // Usar el nuevo método de reconciliación
                        $record->reconcile(
                            $data['is_approved'],
                            $data['approval_notes']
                        );

                        // Mostrar notificación de éxito
                        Notification::make()
                            ->title('Operación de caja reconciliada correctamente')
                            ->body($data['is_approved']
                                ? 'La operación de caja ha sido aprobada y reconciliada.'
                                : 'La operación de caja ha sido marcada como no aprobada.')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        // Mostrar notificación de error
                        Notification::make()
                            ->title('Error al reconciliar la operación de caja')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Reconciliación Final de Operación de Caja')
                ->modalSubmitActionLabel('Confirmar Reconciliación')
                ->visible(function ($record) {
                    $user = auth()->user();
                    // Solo visible para supervisores y cajas cerradas no aprobadas
                    return !$record->is_active && !$record->is_approved &&
                           $user->hasAnyRole(['admin', 'super_admin', 'manager']);
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $isSupervisor = Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager']);

        return $infolist
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('id')
                                            ->label('ID de Caja')
                                            ->weight(FontWeight::Bold),
                                        TextEntry::make('status')
                                            ->label('Estado')
                                            ->badge()
                                            ->color(fn (string $state): string => $state === 'Abierta' ? 'success' : 'danger'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('opening_datetime')
                                            ->label('Fecha de Apertura')
                                            ->dateTime('d/m/Y H:i'),
                                        TextEntry::make('closing_datetime')
                                            ->label('Fecha de Cierre')
                                            ->dateTime('d/m/Y H:i')
                                            ->placeholder('No cerrada'),
                                    ]),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('openedBy.name')
                                            ->label('Abierto por'),
                                        TextEntry::make('closedBy.name')
                                            ->label('Cerrado por')
                                            ->placeholder('No cerrada'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('opening_amount')
                                            ->label('Monto Inicial')
                                            ->money('PEN'),
                                        TextEntry::make('observations')
                                            ->label('Observaciones')
                                            ->placeholder('Sin observaciones'),
                                    ]),
                                ]),
                        ]),
                    ]),

                Section::make('Resumen de Ventas')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('cash_sales')
                                    ->label('Ventas en Efectivo')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if ($isSupervisor) {
                                            return $record->cash_sales;
                                        } else {
                                            return 'Información reservada';
                                        }
                                    })
                                    ->money(fn () => $isSupervisor ? 'PEN' : null)
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('card_sales')
                                    ->label('Ventas con Tarjeta')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if ($isSupervisor) {
                                            return $record->card_sales;
                                        } else {
                                            return 'Información reservada';
                                        }
                                    })
                                    ->money(fn () => $isSupervisor ? 'PEN' : null),
                                TextEntry::make('other_sales')
                                    ->label('Otras Ventas')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if ($isSupervisor) {
                                            return $record->other_sales;
                                        } else {
                                            return 'Información reservada';
                                        }
                                    })
                                    ->money(fn () => $isSupervisor ? 'PEN' : null),
                                TextEntry::make('total_sales')
                                    ->label('Ventas Totales')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if ($isSupervisor) {
                                            return $record->total_sales;
                                        } else {
                                            return 'Información reservada';
                                        }
                                    })
                                    ->money(fn () => $isSupervisor ? 'PEN' : null)
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                            ]),
                        TextEntry::make('sales_info')
                            ->label('Nota Importante')
                            ->state('Esta información solo es visible para supervisores')
                            ->visible(!$isSupervisor)
                            ->color('warning')
                            ->icon('heroicon-o-information-circle'),
                    ]),

                Section::make('Información de Cierre')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('expected_amount')
                                    ->label('Monto Esperado')
                                    ->money('PEN')
                                    ->visible($isSupervisor),
                                TextEntry::make('actual_amount')
                                    ->label('Monto Final')
                                    ->money('PEN'),
                                TextEntry::make('difference')
                                    ->label('Diferencia')
                                    ->money('PEN')
                                    ->color(fn ($record) => $record->difference < 0 ? 'danger' : ($record->difference > 0 ? 'warning' : 'success'))
                                    ->visible($isSupervisor),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reconciliationStatus')
                                    ->label('Estado de Aprobación')
                                    ->badge()
                                    ->color(fn ($record) => match($record->reconciliationStatus) {
                                        'Aprobada' => 'success',
                                        'Rechazada' => 'danger',
                                        default => 'warning',
                                    })
                                    ->icon(fn ($record) => match($record->reconciliationStatus) {
                                        'Aprobada' => 'heroicon-o-check-circle',
                                        'Rechazada' => 'heroicon-o-x-circle',
                                        default => 'heroicon-o-clock',
                                    })
                                    ->visible($isSupervisor),
                                TextEntry::make('approvedBy.name')
                                    ->label('Revisado por')
                                    ->placeholder('Pendiente de revisión')
                                    ->visible($isSupervisor),
                            ]),
                        TextEntry::make('approval_datetime')
                            ->label('Fecha de Revisión')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pendiente de revisión')
                            ->visible(fn ($record) => $isSupervisor && ($record->is_approved || (!$record->is_approved && $record->approval_notes && $record->approval_datetime))),
                        TextEntry::make('approval_notes')
                            ->label(fn ($record) => $record->is_approved ? 'Notas de Aprobación' : 'Motivo del Rechazo')
                            ->placeholder('Sin notas')
                            ->color(fn ($record) => !$record->is_approved && $record->approval_notes ? 'danger' : 'gray')
                            ->visible(fn ($record) => $isSupervisor && ($record->is_approved || (!$record->is_approved && $record->approval_notes))),
                    ])
                    ->visible(fn ($record) => !$record->is_active),

                Section::make('Reconciliación Final')
                    ->description('Detalles del proceso de reconciliación y aprobación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reconciliationStatus')
                                    ->label('Estado de Reconciliación')
                                    ->badge()
                                    ->color(fn ($record) => match($record->reconciliationStatus) {
                                        'Aprobada' => 'success',
                                        'Rechazada' => 'danger',
                                        'Pendiente de cierre' => 'info',
                                        default => 'warning',
                                    })
                                    ->icon(fn ($record) => match($record->reconciliationStatus) {
                                        'Aprobada' => 'heroicon-o-check-circle',
                                        'Rechazada' => 'heroicon-o-x-circle',
                                        'Pendiente de cierre' => 'heroicon-o-lock-open',
                                        default => 'heroicon-o-clock',
                                    }),

                                TextEntry::make('reconciliation_summary')
                                    ->label('Resumen de Reconciliación')
                                    ->state(function ($record) {
                                        if (!$record->is_active) {
                                            if ($record->is_approved) {
                                                $diffText = $record->difference == 0 ? 'sin diferencias' :
                                                            'con una diferencia de S/ ' . number_format(abs($record->difference), 2) .
                                                            ($record->difference < 0 ? ' faltante' : ' sobrante');

                                                return "Operación de caja reconciliada y aprobada {$diffText}";
                                            } elseif (!$record->is_approved && $record->approval_notes && $record->approval_datetime) {
                                                return "Operación de caja rechazada. Motivo: " . $record->approval_notes;
                                            } else {
                                                return 'Operación de caja cerrada, pendiente de reconciliación por un supervisor';
                                            }
                                        } else {
                                            return 'Operación de caja actualmente abierta';
                                        }
                                    })
                                    ->color(fn ($record) => !$record->is_active && !$record->is_approved && $record->approval_notes ? 'danger' : null),
                            ]),

                        TextEntry::make('reconciliation_instructions')
                            ->label('Instrucciones')
                            ->state(function ($record) use ($isSupervisor) {
                                if ($record->is_active) {
                                    if ($isSupervisor) {
                                        return 'La operación de caja debe ser cerrada por un cajero antes de poder realizar la reconciliación.';
                                    } else {
                                        return 'Debe cerrar la operación de caja para que un supervisor pueda realizar la reconciliación final.';
                                    }
                                } elseif (!$record->is_approved) {
                                    if ($isSupervisor) {
                                        return 'Utilice el botón "Reconciliar y Aprobar" para completar el proceso de cierre de operación de caja.';
                                    } else {
                                        return 'Un supervisor debe realizar la reconciliación final de esta operación de caja.';
                                    }
                                } else {
                                    return 'El proceso de reconciliación ha sido completado.';
                                }
                            })
                            ->color(function ($record) {
                                if ($record->is_approved) {
                                    return 'success';
                                } elseif ($record->is_active) {
                                    return 'info';
                                } else {
                                    return 'warning';
                                }
                            }),
                    ])
                    ->visible($isSupervisor),

                Section::make('Métodos de Pago')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payments_count_cash')
                                    ->label('Pagos en Efectivo')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', Payment::METHOD_CASH)->count();
                                    }),
                                TextEntry::make('payments_count_card')
                                    ->label('Pagos con Tarjeta')
                                    ->state(function ($record) {
                                        return $record->payments()->whereIn('payment_method', [
                                            Payment::METHOD_CREDIT_CARD,
                                            Payment::METHOD_DEBIT_CARD
                                        ])->count();
                                    }),
                                TextEntry::make('payments_count_other')
                                    ->label('Otros Pagos')
                                    ->state(function ($record) {
                                        return $record->payments()->whereNotIn('payment_method', [
                                            Payment::METHOD_CASH,
                                            Payment::METHOD_CREDIT_CARD,
                                            Payment::METHOD_DEBIT_CARD
                                        ])->count();
                                    }),
                            ]),
                    ]),
            ]);
    }

    protected function canCloseCashRegister(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['cashier', 'admin', 'super_admin', 'manager']);
    }
}
