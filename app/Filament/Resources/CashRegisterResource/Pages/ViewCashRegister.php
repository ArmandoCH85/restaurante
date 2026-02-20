<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\Payment;
use App\Support\CashRegisterClosingSummaryService;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ViewCashRegister extends ViewRecord
{
    protected static string $resource = CashRegisterResource::class;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $closingSummaryCache = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('close_register')
                ->label('Cerrar Caja')
                ->icon('heroicon-m-lock-closed')
                ->color('warning')
                ->button()
                ->slideOver()
                ->modalHeading('Cerrar caja')
                ->modalDescription('Registra el conteo final sin salir del detalle de la caja.')
                ->modalSubmitActionLabel('Cerrar caja')
                ->form([
                    Forms\Components\Section::make('Resumen de sistema')
                        ->schema([
                            Forms\Components\Placeholder::make('expected_amount_info')
                                ->label('Monto esperado')
                                ->content(fn ($record) => 'S/ '.number_format((float) $record->calculateExpectedCash(), 2)),
                            Forms\Components\Placeholder::make('system_cash_info')
                                ->label('Efectivo del sistema')
                                ->content(fn ($record) => 'S/ '.number_format((float) $record->getSystemCashSales(), 2)),
                        ])
                        ->columns(2),
                    Forms\Components\Section::make('Conteo manual')
                        ->schema([
                            Forms\Components\TextInput::make('manual_cash')
                                ->label('Efectivo contado')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('S/')
                                ->helperText('Ingresa el efectivo real contado en caja.')
                                ->validationMessages([
                                    'required' => 'Ingresa el efectivo contado.',
                                    'numeric' => 'El efectivo contado debe ser numérico.',
                                    'min' => 'El efectivo contado no puede ser negativo.',
                                ]),
                            Forms\Components\TextInput::make('manual_yape')
                                ->label('Yape')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('S/'),
                            Forms\Components\TextInput::make('manual_plin')
                                ->label('Plin')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('S/'),
                            Forms\Components\TextInput::make('manual_card')
                                ->label('Tarjeta')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('S/'),
                            Forms\Components\TextInput::make('manual_didi')
                                ->label('Didi Food')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('S/'),
                            Forms\Components\TextInput::make('manual_pedidos_ya')
                                ->label('PedidosYa')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('S/'),
                            Forms\Components\TextInput::make('manual_bita_express')
                                ->label('Bita Express')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('S/'),
                            Forms\Components\Textarea::make('closing_observations')
                                ->label('Observaciones de cierre')
                                ->rows(3)
                                ->placeholder('Anota incidencias del cierre (opcional)')
                                ->columnSpanFull(),
                        ])
                        ->columns(3),
                ])
                ->action(function ($record, array $data): void {
                    $manualCash = (float) ($data['manual_cash'] ?? 0);
                    $systemCash = (float) $record->getSystemCashSales();

                    if ($systemCash > 0.009 && $manualCash <= 0.009) {
                        Notification::make()
                            ->warning()
                            ->title('Falta registrar efectivo contado')
                            ->body('El sistema registra S/ '.number_format($systemCash, 2).' en efectivo.')
                            ->send();

                        return;
                    }

                    $otherPayments = (float) ($data['manual_yape'] ?? 0)
                        + (float) ($data['manual_plin'] ?? 0)
                        + (float) ($data['manual_card'] ?? 0)
                        + (float) ($data['manual_didi'] ?? 0)
                        + (float) ($data['manual_pedidos_ya'] ?? 0)
                        + (float) ($data['manual_bita_express'] ?? 0);

                    $totalCounted = $manualCash + $otherPayments;
                    $expectedAmount = (float) $record->calculateExpectedCash();
                    $difference = ($totalCounted + (float) $record->opening_amount) - $expectedAmount;
                    $totalExpenses = (float) $record->cashRegisterExpenses()->sum('amount');

                    $totalIngresos = (float) $record->getSystemTotalSales();
                    $gananciaReal = $totalIngresos - $totalExpenses;

                    $summaryService = app(CashRegisterClosingSummaryService::class);
                    $summary = $summaryService->build([
                        'total_ingresos' => $totalIngresos,
                        'total_egresos' => $totalExpenses,
                        'ganancia_real' => $gananciaReal,
                        'monto_inicial' => (float) $record->opening_amount,
                        'monto_esperado' => $expectedAmount,
                        'efectivo_total' => $manualCash,
                        'total_manual_ventas' => $totalCounted,
                        'difference' => $difference,
                        'billetes' => [
                            '200' => 0,
                            '100' => 0,
                            '50' => 0,
                            '20' => 0,
                            '10' => 0,
                        ],
                        'monedas' => [
                            '5' => 0,
                            '2' => 0,
                            '1' => 0,
                            '0.50' => 0,
                            '0.20' => 0,
                            '0.10' => 0,
                        ],
                        'otros_metodos' => [
                            'yape' => (float) ($data['manual_yape'] ?? 0),
                            'plin' => (float) ($data['manual_plin'] ?? 0),
                            'tarjeta' => (float) ($data['manual_card'] ?? 0),
                            'didi' => (float) ($data['manual_didi'] ?? 0),
                            'pedidos_ya' => (float) ($data['manual_pedidos_ya'] ?? 0),
                            'bita_express' => (float) ($data['manual_bita_express'] ?? 0),
                            'otros' => 0,
                        ],
                        'closed_by' => Auth::id(),
                        'closing_datetime' => now()->toDateTimeString(),
                        'closing_observations' => $data['closing_observations'] ?? null,
                    ]);

                    $legacySummary = $summaryService->toLegacyText($summary);
                    $baseObservations = trim((string) $record->observations);
                    $finalObservations = trim($baseObservations === '' ? $legacySummary : $baseObservations."\n\n".$legacySummary);

                    $updatePayload = [
                        'manual_yape' => (float) ($data['manual_yape'] ?? 0),
                        'manual_plin' => (float) ($data['manual_plin'] ?? 0),
                        'manual_card' => (float) ($data['manual_card'] ?? 0),
                        'manual_didi' => (float) ($data['manual_didi'] ?? 0),
                        'manual_pedidos_ya' => (float) ($data['manual_pedidos_ya'] ?? 0),
                        'manual_bita_express' => (float) ($data['manual_bita_express'] ?? 0),
                        'closed_by' => Auth::id(),
                        'closing_datetime' => now(),
                        'is_active' => false,
                        'actual_amount' => $totalCounted,
                        'expected_amount' => $expectedAmount,
                        'difference' => $difference,
                        'total_expenses' => $totalExpenses,
                        'observations' => $finalObservations,
                    ];

                    if (Schema::hasColumn('cash_registers', 'closing_summary_json')) {
                        $updatePayload['closing_summary_json'] = $summary;
                    }

                    $record->update($updatePayload);

                    $significantDifference = abs($difference) > 50 || ($expectedAmount > 0 && abs($difference) / $expectedAmount > 0.05);

                    $notification = Notification::make()
                        ->title($significantDifference ? 'Caja cerrada con diferencia significativa' : 'Caja cerrada correctamente')
                        ->body('Diferencia de cierre: S/ '.number_format($difference, 2));

                    if ($significantDifference) {
                        $notification->warning()->duration(8000);
                    } else {
                        $notification->success();
                    }

                    $notification->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                })
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
                    return ! $record->is_active || (Auth::user()?->hasAnyRole(['admin', 'super_admin', 'manager']) ?? false);
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
                    return ! $record->is_active || (Auth::user()?->hasAnyRole(['admin', 'super_admin', 'manager']) ?? false);
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
                            ->title($data['is_approved'] ? 'Operación de caja aprobada' : 'Operación de caja rechazada')
                            ->body($data['is_approved']
                                ? 'La operación de caja ha sido aprobada y reconciliada exitosamente.'
                                : 'La operación de caja ha sido marcada como no aprobada.')
                            ->success()
                            ->duration(5000)
                            ->send();

                    } catch (\Exception $e) {
                        // Mostrar notificación de error
                        Notification::make()
                            ->title('Error al reconciliar la operacion de caja')
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
                    return ! $record->is_active && ! $record->is_approved &&
                        $user->hasAnyRole(['admin', 'super_admin', 'manager']);
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $isSupervisor = Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager']);

        return $infolist
            ->schema([
                // Header con información principal mejorado
                Section::make('Información General')
                    ->description('Datos principales de la operación de caja')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        // Primera fila: Estado de la operación (más ancho)
                        Grid::make(['default' => 1, 'md' => 3, 'lg' => 4])
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID de Caja')
                                    ->badge()
                                    ->size('xl')
                                    ->color('primary')
                                    ->icon('heroicon-m-hashtag')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->state(fn ($record) => $record->is_active ? 'Abierta' : 'Cerrada')
                                    ->badge()
                                    ->size('xl')
                                    ->color(fn ($record) => $record->is_active ? 'success' : 'danger')
                                    ->icon(fn ($record) => $record->is_active ? 'heroicon-m-lock-open' : 'heroicon-m-lock-closed')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('opening_amount')
                                    ->label('Monto Inicial')
                                    ->formatStateUsing(fn ($state) => 'S/ '.number_format($state ?? 0, 2))
                                    ->badge()
                                    ->size('xl')
                                    ->color('info')
                                    ->icon('heroicon-m-banknotes')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('opening_datetime')
                                    ->label('Apertura')
                                    ->dateTime('d/m/Y H:i')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-calendar-days'),
                            ]),

                        // Segunda fila: Personal y fechas
                        Grid::make(['default' => 1, 'md' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('openedBy.name')
                                    ->label('Abierto por')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-user-circle'),
                                TextEntry::make('closedBy.name')
                                    ->label('Cerrado por')
                                    ->placeholder('Aún abierta')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-user-circle'),
                                TextEntry::make('closing_datetime')
                                    ->label('Cierre')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Aún abierta')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-calendar-days'),
                                TextEntry::make('duration')
                                    ->label('Duración')
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
                                    ->icon('heroicon-m-clock'),
                            ]),

                        // Observaciones operativas
                        TextEntry::make('observations')
                            ->label('Observaciones Operativas')
                            ->state(function ($record) {
                                return $this->extractOperationalObservations($record);
                            })
                            ->formatStateUsing(fn ($state) => $state)
                            ->extraAttributes(['style' => 'white-space: pre-line; line-height: 1.5;'])
                            ->icon('heroicon-m-document-text')
                            ->placeholder('Sin observaciones operativas')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Section::make('Resumen Profesional de Cierre')
                    ->description('Vista financiera estructurada para auditoria y toma de decisiones')
                    ->icon('heroicon-m-presentation-chart-line')
                    ->schema([
                        ViewEntry::make('closing_summary_visual')
                            ->label('')
                            ->state(fn ($record) => $this->resolveClosingSummary($record))
                            ->view('filament.cash-register.closing-summary-visual')
                            ->columnSpanFull(),

                        Grid::make(['default' => 1, 'sm' => 2, 'xl' => 4])
                            ->schema([
                                TextEntry::make('summary_total_ingresos')
                                    ->label('Total Ingresos')
                                    ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'kpis.total_ingresos', 0)))
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-arrow-trending-up')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('summary_total_egresos')
                                    ->label('Total Egresos')
                                    ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'kpis.total_egresos', 0)))
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-m-arrow-trending-down')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('summary_ganancia_real')
                                    ->label('Ganancia Real')
                                    ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'kpis.ganancia_real', 0)))
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-chart-bar-square')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('summary_diferencia')
                                    ->label('Diferencia')
                                    ->state(function ($record) {
                                        $difference = (float) $this->summaryValue($record, 'kpis.diferencia', 0);

                                        return $this->money($difference).' '.$this->differenceBadgeLabel($difference);
                                    })
                                    ->badge()
                                    ->color(fn ($record) => $this->differenceColor((float) $this->summaryValue($record, 'kpis.diferencia', 0)))
                                    ->icon(fn ($record) => $this->differenceIcon((float) $this->summaryValue($record, 'kpis.diferencia', 0)))
                                    ->weight(FontWeight::Bold),
                            ]),

                        Grid::make(['default' => 1, 'xl' => 2])
                            ->schema([
                                Section::make('Conciliacion')
                                    ->schema([
                                        TextEntry::make('summary_monto_esperado')
                                            ->label('Monto Esperado')
                                            ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'conciliacion.monto_esperado', 0)))
                                            ->badge()
                                            ->color('primary')
                                            ->icon('heroicon-m-scale'),
                                        TextEntry::make('summary_total_manual')
                                            ->label('Total Manual (Ventas)')
                                            ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'conciliacion.total_manual_ventas', 0)))
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-m-calculator'),
                                        TextEntry::make('summary_monto_inicial')
                                            ->label('Monto Inicial')
                                            ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'conciliacion.monto_inicial', 0)))
                                            ->badge()
                                            ->color('gray')
                                            ->icon('heroicon-m-banknotes'),
                                        TextEntry::make('summary_formula')
                                            ->label('Formula')
                                            ->state(fn ($record) => (string) $this->summaryValue($record, 'conciliacion.formula', '(Manual + Inicial) - Esperado'))
                                            ->icon('heroicon-m-calculator')
                                            ->extraAttributes(['style' => 'white-space: pre-line;']),
                                    ]),

                                Section::make('Efectivo Contado')
                                    ->schema([
                                        TextEntry::make('summary_efectivo_total')
                                            ->label('Total Efectivo')
                                            ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'efectivo.total_contado', 0)))
                                            ->badge()
                                            ->color('success')
                                            ->icon('heroicon-m-banknotes'),
                                        TextEntry::make('summary_billetes')
                                            ->label('Billetes')
                                            ->state(fn ($record) => $this->formatDenominations($this->summaryArray($record, 'efectivo.billetes')))
                                            ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace;'])
                                            ->icon('heroicon-m-ticket'),
                                        TextEntry::make('summary_monedas')
                                            ->label('Monedas')
                                            ->state(fn ($record) => $this->formatDenominations($this->summaryArray($record, 'efectivo.monedas')))
                                            ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace;'])
                                            ->icon('heroicon-m-circle-stack'),
                                    ]),
                            ]),

                        Grid::make(['default' => 1, 'xl' => 2])
                            ->schema([
                                Section::make('Otros Metodos de Pago')
                                    ->schema([
                                        TextEntry::make('summary_otros_total')
                                            ->label('Total Otros Metodos')
                                            ->state(fn ($record) => $this->money($this->sumMethods($this->summaryArray($record, 'otros_metodos'))))
                                            ->badge()
                                            ->color('warning')
                                            ->icon('heroicon-m-credit-card'),
                                        TextEntry::make('summary_otros_detalle')
                                            ->label('Detalle por Canal')
                                            ->state(fn ($record) => $this->formatMethods($this->summaryArray($record, 'otros_metodos')))
                                            ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace;'])
                                            ->icon('heroicon-m-list-bullet'),
                                    ]),
                                Section::make('Egresos Registrados')
                                    ->schema([
                                        TextEntry::make('summary_egresos_total')
                                            ->label('Total Egresos')
                                            ->state(fn ($record) => $this->money((float) $this->summaryValue($record, 'egresos.total', 0)))
                                            ->badge()
                                            ->color('danger')
                                            ->icon('heroicon-m-receipt-percent'),
                                        TextEntry::make('summary_egresos_url')
                                            ->label('Ver Detalles')
                                            ->state(fn ($record) => (string) $this->summaryValue($record, 'egresos.url', '/admin/egresos'))
                                            ->url(fn ($record) => (string) $this->summaryValue($record, 'egresos.url', '/admin/egresos'))
                                            ->openUrlInNewTab()
                                            ->icon('heroicon-m-arrow-top-right-on-square'),
                                    ]),
                            ]),
                    ])
                    ->visible(fn ($record) => ! $record->is_active)
                    ->collapsible()
                    ->collapsed(false),

                // Resumen de Ventas con mejor diseño
                Section::make('Resumen de Ventas')
                    ->description($isSupervisor ? 'Desglose detallado de ventas por método de pago' : 'Información disponible solo para supervisores')
                    ->icon('heroicon-m-chart-bar')
                    ->schema([
                        // Métricas principales en cards - Desglose detallado por método de pago
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 6])
                            ->schema([
                                TextEntry::make('cash_sales_live')
                                    ->label('Efectivo')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (! $isSupervisor) {
                                            return 'Restringido';
                                        }
                                        $sum = $record->getSystemCashSales();

                                        return 'S/ '.number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'success' : 'gray')
                                    ->icon('heroicon-m-banknotes')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('card_sales_live')
                                    ->label('Tarjetas')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (! $isSupervisor) {
                                            return 'Restringido';
                                        }
                                        $sum = $record->getSystemCardSales();

                                        return 'S/ '.number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'info' : 'gray')
                                    ->icon('heroicon-m-credit-card')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('yape_sales_live')
                                    ->label('Yape')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (! $isSupervisor) {
                                            return 'Restringido';
                                        }
                                        $sum = $record->getSystemYapeSales();

                                        return 'S/ '.number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'warning' : 'gray')
                                    ->icon('heroicon-m-device-phone-mobile')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('plin_sales_live')
                                    ->label('Plin')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (! $isSupervisor) {
                                            return 'Restringido';
                                        }
                                        $sum = $record->getSystemPlinSales();

                                        return 'S/ '.number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'warning' : 'gray')
                                    ->icon('heroicon-m-device-phone-mobile')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('pedidosya_sales_live')
                                    ->label('PedidosYa')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (! $isSupervisor) {
                                            return 'Restringido';
                                        }
                                        $sum = $record->getSystemPedidosYaSales();

                                        return 'S/ '.number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'orange' : 'gray')
                                    ->icon('heroicon-m-shopping-bag')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('didi_sales_live')
                                    ->label('Didi Food')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (! $isSupervisor) {
                                            return 'Restringido';
                                        }
                                        $sum = $record->getSystemDidiSales();

                                        return 'S/ '.number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'orange' : 'gray')
                                    ->icon('heroicon-m-truck')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('bita_express_sales_live')
                                    ->label('Bita Express')
                                    ->state(function ($record) use ($isSupervisor) {
                                        if (! $isSupervisor) {
                                            return 'Restringido';
                                        }
                                        $sum = $record->getSystemBitaExpressSales();

                                        return 'S/ '.number_format($sum, 2);
                                    })
                                    ->badge()
                                    ->color($isSupervisor ? 'purple' : 'gray')
                                    ->icon('heroicon-m-rocket-launch')
                                    ->weight(FontWeight::Bold),
                            ]),

                        // Mensaje para usuarios no supervisores
                        TextEntry::make('sales_info')
                            ->label('Acceso Restringido')
                            ->state('Esta información financiera solo es visible para supervisores y administradores del sistema.')
                            ->visible(! $isSupervisor)
                            ->color('warning')
                            ->icon('heroicon-m-shield-exclamation')
                            ->badge()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(! $isSupervisor),

                // Información de Cierre mejorada
                Section::make('Informacion de Cierre')
                    ->description('Detalles del proceso de cierre y reconciliación')
                    ->icon('heroicon-m-lock-closed')
                    ->schema([
                        Split::make([
                            // Montos principales
                            Section::make('Montos de Cierre')
                                ->icon('heroicon-m-calculator')
                                ->schema([
                                    Grid::make(['default' => 1, 'md' => 3])
                                        ->schema([
                                            TextEntry::make('expected_amount')
                                                ->label('Monto Esperado')
                                                ->formatStateUsing(fn ($record) => 'S/ '.number_format((float) $record->expected_amount, 2))
                                                ->badge()
                                                ->color('info')
                                                ->icon('heroicon-m-chart-bar-square')
                                                ->visible($isSupervisor),
                                            TextEntry::make('actual_amount')
                                                ->label('Montos de Cierre')
                                                ->formatStateUsing(fn ($record) => 'S/ '.number_format((float) $record->actual_amount, 2))
                                                ->badge()
                                                ->color('primary')
                                                ->icon('heroicon-m-banknotes'),
                                            TextEntry::make('difference')
                                                ->label('Diferencia')
                                                ->formatStateUsing(function ($record) {
                                                    $difference = (float) $record->difference;
                                                    $status = $difference < 0
                                                        ? '(FALTANTE)'
                                                        : ($difference > 0 ? '(SOBRANTE)' : '(SIN DIFERENCIA)');

                                                    return 'S/ '.number_format($difference, 2)." {$status}";
                                                })
                                                ->badge()
                                                ->size('lg')
                                                ->color(fn ($record) => $record->difference < 0 ? 'danger' : ($record->difference > 0 ? 'warning' : 'success'))
                                                ->icon(fn ($record) => $record->difference < 0 ? 'heroicon-m-x-circle' : ($record->difference > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle'))
                                                ->visible($isSupervisor),
                                        ]),
                                ]),

                            // Estado de aprobación
                            Section::make('Estado de Revisión')
                                ->icon('heroicon-m-clipboard-document-check')
                                ->schema([
                                    TextEntry::make('reconciliationStatus')
                                        ->label('Estado')
                                        ->badge()
                                        ->size('lg')
                                        ->color(fn ($record) => match ($record->reconciliationStatus) {
                                            'Aprobada' => 'success',
                                            'Rechazada' => 'danger',
                                            default => 'warning',
                                        })
                                        ->icon(fn ($record) => match ($record->reconciliationStatus) {
                                            'Aprobada' => 'heroicon-m-check-circle',
                                            'Rechazada' => 'heroicon-m-x-circle',
                                            default => 'heroicon-m-clock',
                                        })
                                        ->visible($isSupervisor),
                                    TextEntry::make('approvedBy.name')
                                        ->label('Revisado por')
                                        ->placeholder('Pendiente de revisión')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-m-user-circle')
                                        ->visible($isSupervisor),
                                    TextEntry::make('approval_datetime')
                                        ->label('Fecha de Revisión')
                                        ->dateTime('d/m/Y H:i')
                                        ->placeholder('Pendiente de revisión')
                                        ->badge()
                                        ->color('gray')
                                        ->icon('heroicon-m-calendar-days')
                                        ->visible(fn ($record) => $isSupervisor && ($record->is_approved || (! $record->is_approved && $record->approval_notes && $record->approval_datetime))),
                                ])
                                ->grow(false),
                        ])->from('md'),

                        // Notas de aprobación/rechazo
                        TextEntry::make('approval_notes')
                            ->label(fn ($record) => $record->is_approved ? 'Notas de Aprobación' : 'Motivo del Rechazo')
                            ->placeholder('Sin notas adicionales')
                            ->color(fn ($record) => ! $record->is_approved && $record->approval_notes ? 'danger' : 'gray')
                            ->icon(fn ($record) => $record->is_approved ? 'heroicon-m-document-check' : 'heroicon-m-exclamation-triangle')
                            ->visible(fn ($record) => $isSupervisor && ($record->is_approved || (! $record->is_approved && $record->approval_notes)))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! $record->is_active)
                    ->collapsible()
                    ->collapsed(true),

                Section::make('Reconciliación Final')
                    ->description('Detalles del proceso de reconciliación y aprobación')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reconciliationStatus')
                                    ->label('Estado de Reconciliación')
                                    ->badge()
                                    ->color(fn ($record) => match ($record->reconciliationStatus) {
                                        'Aprobada' => 'success',
                                        'Rechazada' => 'danger',
                                        'Pendiente de cierre' => 'info',
                                        default => 'warning',
                                    })
                                    ->icon(fn ($record) => match ($record->reconciliationStatus) {
                                        'Aprobada' => 'heroicon-m-check-circle',
                                        'Rechazada' => 'heroicon-m-x-circle',
                                        'Pendiente de cierre' => 'heroicon-m-lock-open',
                                        default => 'heroicon-m-clock',
                                    }),

                                TextEntry::make('reconciliation_summary')
                                    ->label('Resumen de Reconciliación')
                                    ->state(function ($record) {
                                        if (! $record->is_active) {
                                            if ($record->is_approved) {
                                                $diffText = $record->difference == 0 ? 'sin diferencias' :
                                                    'con una diferencia de S/ '.number_format(abs($record->difference), 2).
                                                    ($record->difference < 0 ? ' faltante' : ' sobrante');

                                                return "Operación de caja reconciliada y aprobada {$diffText}";
                                            } elseif (! $record->is_approved && $record->approval_notes && $record->approval_datetime) {
                                                return 'Operación de caja rechazada. Motivo: '.$record->approval_notes;
                                            } else {
                                                return 'Operación de caja cerrada, pendiente de reconciliación por un supervisor';
                                            }
                                        } else {
                                            return 'Operación de caja actualmente abierta';
                                        }
                                    })
                                    ->color(fn ($record) => ! $record->is_active && ! $record->is_approved && $record->approval_notes ? 'danger' : null),
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
                                } elseif (! $record->is_approved) {
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
                    ->visible($isSupervisor)
                    ->collapsible()
                    ->collapsed(true),

                // Métodos de Pago mejorados
                Section::make('Métodos de Pago')
                    ->description('Desglose de pagos por método utilizado')
                    ->icon('heroicon-m-credit-card')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3, 'lg' => 6])
                            ->schema([
                                TextEntry::make('payments_count_cash')
                                    ->label('Efectivo')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', Payment::METHOD_CASH)->count().' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('success')
                                    ->icon('heroicon-m-banknotes'),
                                TextEntry::make('payments_count_card')
                                    ->label('Tarjeta')
                                    ->state(function ($record) {
                                        return $record->payments()->whereIn('payment_method', [Payment::METHOD_CARD, Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD])->count().' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('info')
                                    ->icon('heroicon-m-credit-card'),
                                TextEntry::make('payments_count_yape')
                                    ->label('Yape')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'yape')->count().' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('warning')
                                    ->icon('heroicon-m-device-phone-mobile'),
                                TextEntry::make('payments_count_plin')
                                    ->label('Plin')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'plin')->count().' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('warning')
                                    ->icon('heroicon-m-device-phone-mobile'),
                                TextEntry::make('payments_count_pedidosya')
                                    ->label('PedidosYa')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'pedidos_ya')->count().' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('orange')
                                    ->icon('heroicon-m-shopping-bag'),
                                TextEntry::make('payments_count_didi')
                                    ->label('Didi Food')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'didi_food')->count().' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('orange')
                                    ->icon('heroicon-m-truck'),
                                TextEntry::make('payments_count_bita_express')
                                    ->label('Bita Express')
                                    ->state(function ($record) {
                                        return $record->payments()->where('payment_method', 'bita_express')->count().' usos';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color('purple')
                                    ->icon('heroicon-m-rocket-launch'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(true),

                // Vouchers de Tarjeta mejorados
                Section::make('Vouchers de Tarjeta')
                    ->description('Detalle de transacciones con tarjeta y sus códigos de voucher')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Split::make([
                            // Lista de vouchers
                            Section::make('Transacciones')
                                ->icon('heroicon-m-list-bullet')
                                ->schema([
                                    TextEntry::make('card_vouchers')
                                        ->label('Vouchers Registrados')
                                        ->state(function ($record) {
                                            $cardPayments = $record->payments()
                                                ->where('payment_method', Payment::METHOD_CARD)
                                                ->whereNotNull('reference_number')
                                                ->where('reference_number', '!=', '')
                                                ->orderBy('payment_datetime', 'desc')
                                                ->get();

                                            if ($cardPayments->isEmpty()) {
                                                return 'No hay transacciones con voucher registradas';
                                            }

                                            $voucherList = [];
                                            foreach ($cardPayments as $payment) {
                                                $paymentMethod = 'Tarjeta';
                                                $datetime = $payment->payment_datetime->format('d/m/Y H:i');
                                                $amount = 'S/ '.number_format($payment->amount, 2);
                                                $voucher = $payment->reference_number;

                                                $voucherList[] = "{$voucher} -> {$amount} ({$datetime})";
                                            }

                                            return implode("\n", $voucherList);
                                        })
                                        ->formatStateUsing(fn ($state) => $state)
                                        ->extraAttributes(['style' => 'white-space: pre-line; font-family: monospace; font-size: 0.875rem;'])
                                        ->placeholder('Sin vouchers registrados')
                                        ->color('gray')
                                        ->icon('heroicon-m-ticket'),
                                ]),

                            // Resumen
                            Section::make('Resumen')
                                ->icon('heroicon-m-chart-pie')
                                ->schema([
                                    Grid::make(['default' => 1, 'lg' => 2])
                                        ->schema([
                                            TextEntry::make('voucher_count')
                                                ->label('Total Vouchers')
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
                                                ->icon('heroicon-m-hashtag'),
                                            TextEntry::make('voucher_total')
                                                ->label('Monto Total')
                                                ->state(function ($record) {
                                                    $total = $record->payments()
                                                        ->where('payment_method', Payment::METHOD_CARD)
                                                        ->whereNotNull('reference_number')
                                                        ->where('reference_number', '!=', '')
                                                        ->sum('amount');

                                                    return 'S/ '.number_format($total, 2);
                                                })
                                                ->badge()
                                                ->size('lg')
                                                ->color('success')
                                                ->icon('heroicon-m-currency-dollar'),
                                        ]),
                                ])
                                ->grow(false),
                        ])->from('lg'),
                    ])
                    ->visible(fn ($record) => $isSupervisor && ! $record->is_active)
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveClosingSummary($record): array
    {
        if (isset($this->closingSummaryCache[$record->id])) {
            return $this->closingSummaryCache[$record->id];
        }

        if (is_array($record->closing_summary_json) && ! empty($record->closing_summary_json)) {
            $this->closingSummaryCache[$record->id] = $record->closing_summary_json;

            return $this->closingSummaryCache[$record->id];
        }

        $summary = null;
        if (! empty($record->observations)) {
            $summary = app(CashRegisterClosingSummaryService::class)->parseLegacy($record->observations);
        }

        if (! is_array($summary) || $summary === []) {
            $summary = $this->buildFallbackSummaryFromRecord($record);
        }

        $this->closingSummaryCache[$record->id] = $summary ?? [];

        return $this->closingSummaryCache[$record->id];
    }

    /**
     * @param  mixed  $default
     * @return mixed
     */
    protected function summaryValue($record, string $path, $default = null)
    {
        return data_get($this->resolveClosingSummary($record), $path, $default);
    }

    /**
     * @return array<string, mixed>
     */
    protected function summaryArray($record, string $path): array
    {
        $value = $this->summaryValue($record, $path, []);

        return is_array($value) ? $value : [];
    }

    /**
     * @param  array<string, mixed>  $methods
     */
    protected function sumMethods(array $methods): float
    {
        return (float) array_sum(array_map('floatval', $methods));
    }

    protected function money(float $amount): string
    {
        return 'S/ '.number_format($amount, 2);
    }

    protected function differenceColor(float $difference): string
    {
        if ($difference === 0.0) {
            return 'success';
        }

        return $difference > 0 ? 'warning' : 'danger';
    }

    protected function differenceIcon(float $difference): string
    {
        if ($difference === 0.0) {
            return 'heroicon-m-check-circle';
        }

        return $difference > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-x-circle';
    }

    protected function differenceBadgeLabel(float $difference): string
    {
        if ($difference === 0.0) {
            return '(SIN DIFERENCIA)';
        }

        return $difference > 0 ? '(SOBRANTE)' : '(FALTANTE)';
    }

    /**
     * @param  array<string, mixed>  $values
     */
    protected function formatDenominations(array $values): string
    {
        if ($values === []) {
            return 'Sin datos';
        }

        $lines = [];
        foreach ($values as $label => $quantity) {
            $lines[] = 'S/'.$label.' x '.(int) $quantity;
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $methods
     */
    protected function formatMethods(array $methods): string
    {
        if ($methods === []) {
            return 'Sin datos';
        }

        $lines = [];
        foreach ($methods as $name => $amount) {
            $value = (float) $amount;
            if ($value <= 0) {
                continue;
            }

            $label = match ($name) {
                'pedidos_ya' => 'Pedidos Ya',
                'bita_express' => 'Bita Express',
                default => ucfirst(str_replace('_', ' ', (string) $name)),
            };

            $lines[] = $label.': '.$this->money($value);
        }

        return $lines === [] ? 'Sin datos' : implode("\n", $lines);
    }

    protected function extractOperationalObservations($record): string
    {
        $summary = $this->resolveClosingSummary($record);
        $fromMeta = trim((string) data_get($summary, 'meta.closing_observations', ''));

        if ($fromMeta !== '') {
            return $fromMeta;
        }

        $raw = trim((string) ($record->observations ?? ''));
        if ($raw === '') {
            return 'Sin observaciones operativas';
        }

        if (str_contains($raw, 'CIERRE DE CAJA - RESUMEN COMPLETO')) {
            return 'El detalle financiero se visualiza en la seccion "Resumen Profesional de Cierre".';
        }

        return $raw;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFallbackSummaryFromRecord($record): array
    {
        $totalIngresos = (float) $record->getSystemTotalSales();
        $expensesFromRelation = (float) $record->cashRegisterExpenses()->sum('amount');
        $totalEgresos = $expensesFromRelation > 0
            ? $expensesFromRelation
            : (float) ($record->total_expenses ?? 0);
        $gananciaReal = $totalIngresos - $totalEgresos;

        $manualYape = (float) ($record->manual_yape ?? 0);
        $manualPlin = (float) ($record->manual_plin ?? 0);
        $manualCard = (float) ($record->manual_card ?? 0);
        $manualDidi = (float) ($record->manual_didi ?? 0);
        $manualPedidosYa = (float) ($record->manual_pedidos_ya ?? 0);
        $manualBitaExpress = (float) ($record->manual_bita_express ?? 0);

        $otrosTotal = $manualYape + $manualPlin + $manualCard + $manualDidi + $manualPedidosYa + $manualBitaExpress;
        $totalManualVentas = (float) ($record->actual_amount ?? 0);
        $efectivoTotal = max(0, $totalManualVentas - $otrosTotal);

        return app(CashRegisterClosingSummaryService::class)->build([
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'ganancia_real' => $gananciaReal,
            'monto_inicial' => (float) ($record->opening_amount ?? 0),
            'monto_esperado' => (float) ($record->expected_amount ?? 0),
            'efectivo_total' => $efectivoTotal,
            'total_manual_ventas' => $totalManualVentas,
            'difference' => (float) ($record->difference ?? 0),
            'billetes' => [
                '200' => 0,
                '100' => 0,
                '50' => 0,
                '20' => 0,
                '10' => 0,
            ],
            'monedas' => [
                '5' => 0,
                '2' => 0,
                '1' => 0,
                '0.50' => 0,
                '0.20' => 0,
                '0.10' => 0,
            ],
            'otros_metodos' => [
                'yape' => $manualYape,
                'plin' => $manualPlin,
                'tarjeta' => $manualCard,
                'didi' => $manualDidi,
                'pedidos_ya' => $manualPedidosYa,
                'bita_express' => $manualBitaExpress,
                'otros' => 0,
            ],
            'closed_by' => (int) ($record->closed_by ?? 0),
            'closing_datetime' => optional($record->closing_datetime)->toDateTimeString(),
            'closing_observations' => trim((string) ($record->observations ?? '')),
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
        if (str_contains($observations, 'CIERRE DE CAJA - RESUMEN COMPLETO')) {
            $parsed = app(CashRegisterClosingSummaryService::class)->parseLegacy($observations);

            if ($parsed !== null) {
                return app(CashRegisterClosingSummaryService::class)->toLegacyText($parsed);
            }
        }

        // Si contiene desglose de denominaciones, formatearlo mejor
        if (str_contains($observations, 'Desglose de denominaciones')) {
            $formatted = [];

            // Separar por secciones principales
            if (preg_match('/Cierre de caja - Desglose de denominaciones:\s*(.+?)Total contado:\s*(.+?)$/s', $observations, $matches)) {
                $formatted[] = 'DESGLOSE DE DENOMINACIONES';
                $formatted[] = '--------------------------------';

                $content = $matches[1];
                $total = trim($matches[2]);

                // Procesar billetes
                if (preg_match('/Billetes:\s*(.+?)(?=Monedas:|$)/s', $content, $billetes)) {
                    $formatted[] = '';
                    $formatted[] = 'BILLETES:';
                    $billetesData = trim($billetes[1]);
                    $items = preg_split('/\s*\|\s*/', $billetesData);

                    foreach ($items as $item) {
                        if (preg_match('/S\/(\d+):\s*(\d+)/', $item, $match)) {
                            $denomination = $match[1];
                            $quantity = $match[2];
                            $subtotal = $denomination * $quantity;

                            if ($quantity > 0) {
                                $formatted[] = "   - S/ {$denomination}: {$quantity} unidades = S/ {$subtotal}.00";
                            } else {
                                $formatted[] = "   - S/ {$denomination}: {$quantity} unidades";
                            }
                        }
                    }
                }

                // Procesar monedas
                if (preg_match('/Monedas:\s*(.+?)$/s', $content, $monedas)) {
                    $formatted[] = '';
                    $formatted[] = 'MONEDAS:';
                    $monedasData = trim($monedas[1]);
                    $items = preg_split('/\s*\|\s*/', $monedasData);

                    foreach ($items as $item) {
                        if (preg_match('/S\/(\d+(?:\.\d+)?):\s*(\d+)/', $item, $match)) {
                            $denomination = $match[1];
                            $quantity = $match[2];
                            $subtotal = $denomination * $quantity;

                            if ($quantity > 0) {
                                $formatted[] = "   - S/ {$denomination}: {$quantity} unidades = S/ ".number_format($subtotal, 2);
                            } else {
                                $formatted[] = "   - S/ {$denomination}: {$quantity} unidades";
                            }
                        }
                    }
                }

                $formatted[] = '';
                $formatted[] = '--------------------------------';
                $formatted[] = "TOTAL CONTADO: {$total}";
            } else {
                // Formato de respaldo si no coincide el patrón esperado
                $formatted[] = $observations;
            }

            return implode("\n", $formatted);
        }

        return $observations;
    }
}
