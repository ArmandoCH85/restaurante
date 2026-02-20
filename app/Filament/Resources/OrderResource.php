<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Table as TableModel;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Reportes y Analisis';

    protected static ?string $navigationLabel = 'Dashboard de Ventas';

    protected static ?string $modelLabel = 'Reporte de Ventas';

    protected static ?string $pluralModelLabel = 'Dashboard de Ventas';

    protected static ?string $slug = 'reportes/dashboard-ventas';

    protected static ?int $navigationSort = 1;

    // ❌ NO PERMITIR CREAR/EDITAR - SOLO LECTURA
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        // ❌ Formulario vacío ya que no se permite crear/editar
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Orden #')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dine_in' => 'primary',
                        'delivery' => 'warning',
                        'takeout' => 'info',
                        'drive_thru' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dine_in' => '🍽️ Mesa',
                        'delivery' => '🚚 Delivery',
                        'takeout' => '🥡 Para Llevar',
                        'drive_thru' => '🚗 Drive Thru',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('table.number')
                    ->label('Mesa')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ? "Mesa $state" : 'Venta Directa')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 20 ? $state : null;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Mesero')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'gray',
                        'in_preparation' => 'warning',
                        'ready' => 'info',
                        'delivered' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Abierta',
                        'in_preparation' => 'En Preparación',
                        'ready' => 'Lista',
                        'delivered' => 'Entregada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\IconColumn::make('billed')
                    ->label('Facturada')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('order_datetime')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->order_datetime->format('d/m/Y H:i:s')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Tipo de Servicio')
                    ->options([
                        'dine_in' => '🍽️ Mesa',
                        'takeout' => '🥡 Para Llevar',
                        'delivery' => '🚚 Delivery',
                        'drive_thru' => '🚗 Drive Thru',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierta',
                        'in_preparation' => 'En Preparación',
                        'ready' => 'Lista',
                        'delivered' => 'Entregada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('billed')
                    ->label('Estado Facturación')
                    ->placeholder('Todos')
                    ->trueLabel('Facturadas')
                    ->falseLabel('Pendientes'),

                Tables\Filters\Filter::make('today')
                    ->label('Solo Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->default(),

                Tables\Filters\Filter::make('this_week')
                    ->label('Esta Semana')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
            ])
            ->actions([
                // 👁️ SOLO VER - NO EDITAR
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalles')
                    ->icon('heroicon-m-eye'),
            ])
            ->bulkActions([
                // ❌ NO BULK ACTIONS PARA EVITAR MODIFICACIONES
            ])
            ->defaultSort('order_datetime', 'desc')
            ->poll('30s') // 🔄 ACTUALIZACIÓN AUTOMÁTICA CADA 30 SEGUNDOS
            ->striped()
            ->paginated([10, 25, 50])
            ->extremePaginationLinks();
    }

    public static function getRelations(): array
    {
        return [
            // Sin relaciones editables
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'), // Solo vista, sin edición
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'employee', 'table', 'orderDetails.product'])
            ->latest('order_datetime');
    }

    // 📊 WIDGETS DEL DASHBOARD
    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SalesStatsWidget::class,
        ];
    }
}
