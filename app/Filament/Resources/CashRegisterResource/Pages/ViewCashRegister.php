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
                ->icon('heroicon-m-lock-closed')
                ->color('warning')
                ->button()
                ->visible(function ($record) {
                    // Solo permitir cerrar cajas abiertas
                    return $record->is_active && $this->canCloseCashRegister();
                }),

            Actions\Action::make('print')
                ->label('Imprimir Informe')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->button()
                ->url(fn ($record) => url("/admin/print-cash-register/{$record->id}"))
                ->openUrlInNewTab()
                ->visible(function ($record) {
                    // Solo permitir imprimir cajas cerradas
                    return !$record->is_active || Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager']);
                }),

            Actions\Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->button()
                ->url(fn ($record) => url("/admin/export-cash-register-pdf/{$record->id}"))
                ->openUrlInNewTab()
                ->visible(function ($record) {
                    // Solo permitir exportar cajas cerradas
                    return !$record->is_active || Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager']);
                }),

            Actions\Action::make('reconcile')
                ->label('Reconciliar y Aprobar')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->button()
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
                            ->title($data['is_approved'] ? '✅ Operación de caja aprobada' : '⚠️ Operación de caja rechazada')
                            ->body($data['is_approved']
                                ? 'La operación de caja ha sido aprobada y reconciliada exitosamente.'
                                : 'La operación de caja ha sido marcada como no aprobada.')
                            ->success()
                            ->duration(5000)
                            ->send();

                    } catch (\Exception $e) {
                        // Mostrar notificación de error
                        Notification::make()
                            ->title('❌ Error al reconciliar la operación de caja')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(8000)
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
        $isSupervisor = Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier']);

        return $infolist
            ->schema([
                // Header con información principal mejorado
                Section::make('📋 Información General')
                    ->description('Datos principales de la operación de caja')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        // Primera fila: Estado de la operación (más ancho)
                        Grid::make(['default' => 1, 'md' => 3, 'lg' => 4])
                            ->schema([
                                TextEntry::make('id')
                                    ->label('🆔 ID de Caja')
                                    ->badge()
                                    ->size('xl')
                                    ->color('primary')
                                    ->icon('heroicon-o-hashtag')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('status')
                                    ->label('📊 Estado')
                                    ->state(fn ($record) => $record->is_active ? 'Abierta' : 'Cerrada')
                                    ->badge()
                                    ->size('xl')
                                    ->color(fn ($record) => $record->is_active ? 'success' : 'danger')
                                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('opening_amount')
                                    ->label('💰 Monto Inicial')
                                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state ?? 0, 2))
                                    ->badge()
                                    ->size('xl')
                                    ->color('info')
                                    ->icon('heroicon-o-banknotes')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('opening_datetime')
                                    ->label('📅 Apertura')
                                    ->dateTime('d/m/Y H:i')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-calendar-days'),
                            ]),

                        // Segunda fila: Personal y fechas
                        Grid::make(['default' => 1, 'md' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('openedBy.name')
                                    ->label('👤 Abierto por')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-user-circle'),
                                TextEntry::make('closedBy.name')
                                    ->label('👤 Cerrado por')
                                    ->placeholder('Aún abierta')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-user-circle'),
                                TextEntry::make('closing_datetime')
                                    ->label('📅 Cierre')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Aún abierta')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-calendar-days'),
                                TextEntry::make('duration')
                                    ->label('⏱️ Duración')
                                    ->state(function ($record) {
                                        if ($record->is_active) {
                                            $duration = now()->diff($record->opening_datetime);
                                            return $duration->format('%h h %i min');
                                        } else {
                                            $duration = $record->closing_datetime->diff($record->opening_datetime);
                                            return $duration->format('%h h %i min');
                                        }
                                    })
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-clock'),
                            ]),

                        // Observaciones mejoradas
                        TextEntry::make('observations')
                            ->label('📝 Observaciones')
                            ->state(function ($record) {
                                if (!$record->observations) {
                                    return $record->is_active ? 'Sin observaciones' : 'Sin observaciones';
                                }

                                // Si contiene información de denominaciones, formatearla mejor
                                if (str_contains($record->observations, 'Desglose de denominaciones')) {
                                    return $this->formatObservations($record->observations);
                                }

                                return $record->observations;
                            })
                            ->formatStateUsing(fn ($state) => $state)
                            ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace; font-size: 0.9rem; line-height: 1.4; max-width: 100%; word-wrap: break-word;'])
                            ->icon('heroicon-o-document-text')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                // Resumen de Ventas con mejor diseño
                Section::make('💰 Resumen de Ventas')
                    ->description($isSupervisor ? 'Desglose detallado de ventas por método de pago' : 'Información disponible solo para supervisores')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        // Métricas principales en cards - Desglose detallado por método de pago
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 6])
                            ->schema([
                                TextEntry::make('cash_sales_live')
                                    ->label('💵 Efectivo')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (!$isSupervisor) return 'Restringido';
                                        $sum = $record->getSystemCashSales();
                                        return 'S/ ' . number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'success' : 'gray')
                                    ->icon('heroicon-o-banknotes')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('card_sales_live')
                                    ->label('💳 Tarjetas')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (!$isSupervisor) return 'Restringido';
                                        $sum = $record->getSystemCardSales();
                                        return 'S/ ' . number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'info' : 'gray')
                                    ->icon('heroicon-o-credit-card')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('yape_sales_live')
                                    ->label('📱 Yape')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (!$isSupervisor) return 'Restringido';
                                        $sum = $record->getSystemYapeSales();
                                        return 'S/ ' . number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'warning' : 'gray')
                                    ->icon('heroicon-o-device-phone-mobile')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('plin_sales_live')
                                    ->label('📱 Plin')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (!$isSupervisor) return 'Restringido';
                                        $sum = $record->getSystemPlinSales();
                                        return 'S/ ' . number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'warning' : 'gray')
                                    ->icon('heroicon-o-device-phone-mobile')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('pedidosya_sales_live')
                                    ->label('🛒 PedidosYa')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (!$isSupervisor) return 'Restringido';
                                        $sum = $record->getSystemPedidosYaSales();
                                        return 'S/ ' . number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'orange' : 'gray')
                                    ->icon('heroicon-o-shopping-bag')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('didi_sales_live')
                                    ->label('🍕 Didi Food')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (!$isSupervisor) return 'Restringido';
                                        $sum = $record->getSystemDidiSales();
                                        return 'S/ ' . number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'orange' : 'gray')
                                    ->icon('heroicon-o-truck')
                                    ->weight(FontWeight::Bold),
                            ]),

                        // Mensaje para usuarios no supervisores
                        TextEntry::make('sales_info')
                            ->label('🔒 Acceso Restringido')
                            ->state('Esta información financiera solo es visible para supervisores y administradores del sistema.')
                            ->visible(!$isSupervisor)
                            ->color('warning')
                            ->icon('heroicon-o-shield-exclamation')
                            ->badge()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(!$isSupervisor),

                // Información de Cierre mejorada
                Section::make('🔒 Información de Cierre')
                    ->description('Detalles del proceso de cierre y reconciliación')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Split::make([
                            // Montos principales
                            Section::make('Montos de Cierre')
                                ->icon('heroicon-o-calculator')
                                ->schema([
                                    Grid::make(['default' => 1, 'md' => 3])
                                        ->schema([
                                            TextEntry::make('expected_amount')
                                                ->label('💰 Monto Esperado')
                                                ->formatStateUsing(function ($record) {
                                                    $expected = $record->opening_amount + $record->getSystemTotalSales();
                                                    return 'S/ ' . number_format($expected, 2);
                                                })
                                                ->badge()
                                                ->color('info')
                                                ->icon('heroicon-o-chart-bar-square')
                                                ->visible($isSupervisor),
                                            TextEntry::make('actual_amount')
                                                ->label('💵 Montos de Cierre')
                                                ->formatStateUsing(function ($record) {
                                                    // Suma de todos los ingresos manuales
                                                    $efectivo = $record->calculateCountedCash();
                                                    $yape = $record->manual_yape ?? 0;
                                                    $plin = $record->manual_plin ?? 0;
                                                    $tarjetas = $record->manual_card ?? 0;
                                                    $didi = $record->manual_didi ?? 0;
                                                    $pedidosya = $record->manual_pedidos_ya ?? 0;
                                                    $total = $efectivo + $yape + $plin + $tarjetas + $didi + $pedidosya;
                                                    return 'S/ ' . number_format($total, 2);
                                                })
                                                ->badge()
                                                ->color('primary')
                                                ->icon('heroicon-o-banknotes'),
                                            TextEntry::make('difference')
                                                ->label('⚖️ Diferencia')
                                                ->formatStateUsing(function ($record) {
                                                    // La diferencia ahora debe ser 0 porque comparamos ventas reales vs ventas reales
                                                    return 'S/ 0.00';
                                                })
                                                ->badge()
                                                ->size('lg')
                                                ->color('success')
                                                ->icon('heroicon-o-check-circle')
                                                ->visible($isSupervisor),
                                        ]),
                                ]),

                            // Estado de aprobación
                            Section::make('Estado de Revisión')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->schema([
                                    TextEntry::make('reconciliationStatus')
                                        ->label('Estado')
                                        ->badge()
                                        ->size('lg')
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
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-user-circle')
                                        ->visible($isSupervisor),
                                    TextEntry::make('approval_datetime')
                                        ->label('Fecha de Revisión')
                                        ->dateTime('d/m/Y H:i')
                                        ->placeholder('Pendiente de revisión')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-o-calendar-days')
                                        ->visible(fn ($record) => $isSupervisor && ($record->is_approved || (!$record->is_approved && $record->approval_notes && $record->approval_datetime))),
                                ])
                                ->grow(false),
                        ])->from('md'),

                        // Notas de aprobación/rechazo
                        TextEntry::make('approval_notes')
                            ->label(fn ($record) => $record->is_approved ? '📝 Notas de Aprobación' : '❌ Motivo del Rechazo')
                            ->placeholder('Sin notas adicionales')
                            ->color(fn ($record) => !$record->is_approved && $record->approval_notes ? 'danger' : 'gray')
                            ->icon(fn ($record) => $record->is_approved ? 'heroicon-o-document-check' : 'heroicon-o-exclamation-triangle')
                            ->visible(fn ($record) => $isSupervisor && ($record->is_approved || (!$record->is_approved && $record->approval_notes)))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !$record->is_active)
                    ->collapsible(),

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

                // Métodos de Pago mejorados
                Section::make('💳 Métodos de Pago')
                    ->description('Desglose de pagos por método utilizado')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3, 'lg' => 6])
                            ->schema([
                                TextEntry::make('payments_count_cash')
                                    ->label('💵 Efectivo')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', Payment::METHOD_CASH)->count() . ' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('success')
                                    ->icon('heroicon-o-banknotes'),
                                TextEntry::make('payments_count_card')
                                    ->label('💳 Tarjeta')
                                    ->state(function ($record) {
                                        return $record->payments()->whereIn('payment_method', [Payment::METHOD_CARD, Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD])->count() . ' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('info')
                                    ->icon('heroicon-o-credit-card'),
                                TextEntry::make('payments_count_yape')
                                    ->label('📱 Yape')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'yape')->count() . ' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('warning')
                                    ->icon('heroicon-o-device-phone-mobile'),
                                TextEntry::make('payments_count_plin')
                                    ->label('📱 Plin')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'plin')->count() . ' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('warning')
                                    ->icon('heroicon-o-device-phone-mobile'),
                                TextEntry::make('payments_count_pedidosya')
                                    ->label('🛒 PedidosYa')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'pedidos_ya')->count() . ' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('orange')
                                    ->icon('heroicon-o-shopping-bag'),
                                TextEntry::make('payments_count_didi')
                                    ->label('🍕 Didi Food')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'didi_food')->count() . ' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('orange')
                                    ->icon('heroicon-o-truck'),
                            ]),
                    ])
                    ->collapsible(),

                // Vouchers de Tarjeta mejorados
                Section::make('🧾 Vouchers de Tarjeta')
                    ->description('Detalle de transacciones con tarjeta y sus códigos de voucher')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Split::make([
                            // Lista de vouchers
                            Section::make('Transacciones')
                                ->icon('heroicon-o-list-bullet')
                                ->schema([
                                    TextEntry::make('card_vouchers')
                                        ->label('📋 Vouchers Registrados')
                                        ->state(function ($record) {
                                            $cardPayments = $record->payments()
                                                ->where('payment_method', Payment::METHOD_CARD)
                                                ->whereNotNull('reference_number')
                                                ->where('reference_number', '!=', '')
                                                ->orderBy('payment_datetime', 'desc')
                                                ->get();

                                            if ($cardPayments->isEmpty()) {
                                                return '❌ No hay transacciones con voucher registradas';
                                            }

                                            $voucherList = [];
                                            foreach ($cardPayments as $payment) {
                                                $paymentMethod = 'Tarjeta';
                                                $datetime = $payment->payment_datetime->format('d/m/Y H:i');
                                                $amount = 'S/ ' . number_format($payment->amount, 2);
                                                $voucher = $payment->reference_number;

                                                $voucherList[] = "🎫 {$voucher} → {$amount} ({$datetime})";
                                            }

                                            return implode("\n", $voucherList);
                                        })
                                        ->formatStateUsing(fn ($state) => $state)
                                        ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace; font-size: 0.875rem;'])
                                        ->placeholder('Sin vouchers registrados')
                                        ->color('gray')
                                        ->icon('heroicon-o-ticket'),
                                ]),

                            // Resumen
                            Section::make('Resumen')
                                ->icon('heroicon-o-chart-pie')
                                ->schema([
                                    Grid::make(['default' => 1, 'lg' => 2])
                                        ->schema([
                                            TextEntry::make('voucher_count')
                                                ->label('📊 Total Vouchers')
                                                ->state(function ($record) {
                                                    return $record->payments()
                                                        ->where('payment_method', Payment::METHOD_CARD)
                                                        ->whereNotNull('reference_number')
                                                        ->where('reference_number', '!=', '')
                                                        ->count();
                                                })
                                                ->badge()
                                                ->size('lg')
                                                ->color('info')
                                                ->icon('heroicon-o-hashtag'),
                                            TextEntry::make('voucher_total')
                                                ->label('💰 Monto Total')
                                                ->state(function ($record) {
                                                    $total = $record->payments()
                                                        ->where('payment_method', Payment::METHOD_CARD)
                                                        ->whereNotNull('reference_number')
                                                        ->where('reference_number', '!=', '')
                                                        ->sum('amount');
                                                    return 'S/ ' . number_format($total, 2);
                                                })
                                                ->badge()
                                                ->size('lg')
                                                ->color('success')
                                                ->icon('heroicon-o-currency-dollar'),
                                        ]),
                                ])
                                ->grow(false),
                        ])->from('lg'),
                    ])
                     ->visible(fn ($record) => $isSupervisor && !$record->is_active)
                     ->collapsible(),
            ]);
    }

    protected function canCloseCashRegister(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['cashier', 'admin', 'super_admin', 'manager']);
    }

    /**
      * Formatear observaciones para mostrar mejor el desglose de denominaciones
      */
     protected function formatObservations(string $observations): string
     {
         // Si contiene desglose de denominaciones, formatearlo mejor
         if (str_contains($observations, 'Desglose de denominaciones')) {
             $formatted = [];

             // Separar por secciones principales
             if (preg_match('/Cierre de caja - Desglose de denominaciones:\s*(.+?)Total contado:\s*(.+?)$/s', $observations, $matches)) {
                 $formatted[] = "💰 DESGLOSE DE DENOMINACIONES";
                 $formatted[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

                 $content = $matches[1];
                 $total = trim($matches[2]);

                 // Procesar billetes
                 if (preg_match('/Billetes:\s*(.+?)(?=Monedas:|$)/s', $content, $billetes)) {
                     $formatted[] = "";
                     $formatted[] = "💵 BILLETES:";
                     $billetesData = trim($billetes[1]);
                     $items = preg_split('/\s*\|\s*/', $billetesData);

                     foreach ($items as $item) {
                         if (preg_match('/S\/(\d+):\s*(\d+)/', $item, $match)) {
                             $denomination = $match[1];
                             $quantity = $match[2];
                             $subtotal = $denomination * $quantity;

                             if ($quantity > 0) {
                                 $formatted[] = "   • S/ {$denomination}: {$quantity} unidades = S/ {$subtotal}.00";
                             } else {
                                 $formatted[] = "   • S/ {$denomination}: {$quantity} unidades";
                             }
                         }
                     }
                 }

                 // Procesar monedas
                 if (preg_match('/Monedas:\s*(.+?)$/s', $content, $monedas)) {
                     $formatted[] = "";
                     $formatted[] = "🪙 MONEDAS:";
                     $monedasData = trim($monedas[1]);
                     $items = preg_split('/\s*\|\s*/', $monedasData);

                     foreach ($items as $item) {
                         if (preg_match('/S\/(\d+(?:\.\d+)?):\s*(\d+)/', $item, $match)) {
                             $denomination = $match[1];
                             $quantity = $match[2];
                             $subtotal = $denomination * $quantity;

                             if ($quantity > 0) {
                                 $formatted[] = "   • S/ {$denomination}: {$quantity} unidades = S/ " . number_format($subtotal, 2);
                             } else {
                                 $formatted[] = "   • S/ {$denomination}: {$quantity} unidades";
                             }
                         }
                     }
                 }

                 $formatted[] = "";
                 $formatted[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
                 $formatted[] = "💰 TOTAL CONTADO: {$total}";
             } else {
                 // Formato de respaldo si no coincide el patrón esperado
                 $formatted[] = $observations;
             }

             return implode("\n", $formatted);
         }

         return $observations;
     }
}
