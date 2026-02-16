<?php

namespace App\Filament\Resources;

use App\Models\CashRegister;
use App\Enums\ServiceTypeEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Collection;
use App\Filament\Resources\CashRegisterReportResource\Pages;
use Filament\Support\Colors\Color;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ViewColumn;
use Filament\Support\Components\ViewComponent;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Card;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;

class CashRegisterReportResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reportes de Caja';
    protected static ?string $navigationGroup = 'Caja';
    protected static ?string $modelLabel = 'Reporte de Caja';
    protected static ?string $pluralModelLabel = 'Reportes de Caja';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtros')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha Inicio')
                            ->default(now()->startOfDay())
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha Fin')
                            ->default(now()->endOfDay())
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->select([
                        'cash_registers.id',
                        'cash_registers.opening_datetime',
                        'cash_registers.opening_amount',
                        'cash_registers.actual_amount',
                        'cash_registers.closing_datetime',
                        'cash_registers.opened_by',
                        'cash_registers.opening_datetime as date',
                        DB::raw('COALESCE(o.total, 0) + COALESCE(cm_in.total, 0) as ingresos_totales'),
                        DB::raw('COALESCE(cre.total, 0) + COALESCE(cm_out.total, 0) as egresos_totales'),
                        DB::raw('(cash_registers.opening_amount + COALESCE(o.total, 0) + COALESCE(cm_in.total, 0)) - (COALESCE(cre.total, 0) + COALESCE(cm_out.total, 0)) as saldo_teorico'),
                    ])
                    ->leftJoin(
                        DB::raw('(SELECT cash_register_id, SUM(total) as total FROM orders GROUP BY cash_register_id) as o'),
                        'o.cash_register_id',
                        '=',
                        'cash_registers.id'
                    )
                    ->leftJoin(
                        DB::raw('(SELECT cash_register_id, SUM(amount) as total FROM cash_movements WHERE movement_type = "ingreso" GROUP BY cash_register_id) as cm_in'),
                        'cm_in.cash_register_id',
                        '=',
                        'cash_registers.id'
                    )
                    ->leftJoin(
                        DB::raw('(SELECT cash_register_id, SUM(amount) as total FROM cash_movements WHERE movement_type = "egreso" GROUP BY cash_register_id) as cm_out'),
                        'cm_out.cash_register_id',
                        '=',
                        'cash_registers.id'
                    )
                    ->leftJoin(
                        DB::raw('(SELECT cash_register_id, SUM(amount) as total FROM cash_register_expenses GROUP BY cash_register_id) as cre'),
                        'cre.cash_register_id',
                        '=',
                        'cash_registers.id'
                    )
                    ->orderBy('date', 'desc');
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('opening_amount')
                    ->label('Monto Inicial')
                    ->money('PEN')
                    ->alignEnd(),
                TextColumn::make('ingresos_totales')
                    ->label('Ingresos Totales')
                    ->money('PEN')
                    ->color('success')
                    ->alignEnd()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->money('PEN')
                    ]),
                TextColumn::make('egresos_totales')
                    ->label('Egresos Totales')
                    ->money('PEN')
                    ->color('danger')
                    ->alignEnd()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->money('PEN')
                    ]),
                TextColumn::make('saldo_teorico')
                    ->label('Saldo Final Teórico')
                    ->money('PEN')
                    ->color('info')
                    ->alignEnd()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->money('PEN')
                    ]),
                TextColumn::make('actual_amount')
                    ->label('Monto Real')
                    ->money('PEN')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignEnd(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Filter::make('date')
                    ->form([
                        Section::make('Rango de Fechas')
                            ->description('Seleccione el periodo que desea consultar')
                            ->icon('heroicon-m-calendar')
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Fecha Inicio')
                                    ->default(now()->startOfMonth())
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->closeOnDateSelection(),
                                DatePicker::make('end_date')
                                    ->label('Fecha Fin')
                                    ->default(now()->endOfMonth())
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->closeOnDateSelection(),
                            ])
                            ->columns(2)
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Desde: ' . Carbon::parse($data['start_date'])->format('d/m/Y');
                        }
                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Hasta: ' . Carbon::parse($data['end_date'])->format('d/m/Y');
                        }
                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('opening_datetime', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('opening_datetime', '<=', $date),
                            );
                    }),

                Filter::make('service_type')
                    ->form([
                        Forms\Components\Select::make('service_type')
                            ->label('Tipo de Servicio')
                            ->options([
                                ServiceTypeEnum::DINE_IN->value => ServiceTypeEnum::DINE_IN->getLabel(),
                                ServiceTypeEnum::TAKEOUT->value => ServiceTypeEnum::TAKEOUT->getLabel(),
                                ServiceTypeEnum::DELIVERY->value => ServiceTypeEnum::DELIVERY->getLabel(),
                                ServiceTypeEnum::SELF_SERVICE->value => ServiceTypeEnum::SELF_SERVICE->getLabel(),
                            ])
                            ->placeholder('Todos los tipos')
                            ->native(false),
                    ])
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['service_type'],
                            fn(Builder $query, $type): Builder => $query->whereHas(
                                'orders',
                                fn(Builder $query) => $query->where('service_type', $type)
                            )
                        );
                    }),
            ])
            ->filtersLayout(FiltersLayout::Modal)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalle')
                    ->modalHeading('Detalle de Caja')
                    ->modalWidth('4xl')
                    ->modalContent(fn($record): \Illuminate\View\View => view('filament.resources.cash-register-report-resource.detail', [
                        'record' => CashRegister::with(['openedBy', 'closedBy', 'cashMovements.approvedByUser', 'cashRegisterExpenses', 'orders.user', 'orders.payments'])->findOrFail($record->id),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        // Lógica de exportación
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->poll('60s')
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtros')
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashRegisterReports::route('/'),
            'view' => Pages\ViewCashRegisterReport::route('/{record}'),
        ];
    }
}
