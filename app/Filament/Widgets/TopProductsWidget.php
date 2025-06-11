<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = '🏆 Productos Más Vendidos';

    protected static ?int $sort = 3;

    // 📐 ANCHO COMPLETO PARA LA TABLA
    protected int | string | array $columnSpan = 'full';

        // 🔄 FILTRO TEMPORAL
    public ?string $filter = 'today';

    // 🔑 CLAVE ÚNICA PARA CADA REGISTRO (usando product_id)
    public function getTableRecordKey($record): string
    {
        return (string) $record->product_id;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->badge()
                    ->color(fn ($record, $rowLoop) => match($rowLoop->iteration) {
                        1 => 'warning',  // 🥇 Oro
                        2 => 'gray',     // 🥈 Plata
                        3 => 'danger',   // 🥉 Bronce
                        default => 'primary'
                    })
                    ->formatStateUsing(fn ($record, $rowLoop) => match($rowLoop->iteration) {
                        1 => '🥇 1°',
                        2 => '🥈 2°',
                        3 => '🥉 3°',
                        default => $rowLoop->iteration . '°'
                    }),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('🍽️ Producto')
                    ->searchable()
                    ->weight('bold')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->product->name),

                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('📂 Categoría')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('📊 Vendidos')
                    ->alignCenter()
                    ->weight('bold')
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state . ' unid.'),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('💰 Ingresos')
                    ->money('PEN')
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('avg_price')
                    ->label('💵 Precio Prom.')
                    ->money('PEN')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('📈 % Ventas')
                    ->alignCenter()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%'),
            ])
            ->filters([
                // Sin filtros adicionales - usamos el filtro principal del widget
            ])
            ->actions([
                // Sin acciones - solo vista
            ])
            ->bulkActions([
                // Sin bulk actions
            ])
            ->defaultSort('total_quantity', 'desc')
            ->paginated([10, 25, 50])
            ->poll('60s') // Actualización cada minuto
            ->striped();
    }

    // 🎛️ FILTROS TEMPORALES
    protected function getFilters(): ?array
    {
        return [
            'today' => '📅 Hoy',
            'yesterday' => '📅 Ayer',
            'last_7_days' => '📅 Últimos 7 días',
            'this_week' => '📅 Esta semana',
            'last_30_days' => '📅 Últimos 30 días',
            'this_month' => '📅 Este mes',
            'last_month' => '📅 Mes pasado',
        ];
    }

    // 📊 QUERY PARA OBTENER PRODUCTOS MÁS VENDIDOS
    protected function getTableQuery(): Builder
    {
        $dateRange = $this->getDateRange();

        // Subconsulta para obtener el total de ventas del período para calcular porcentajes
        $totalSalesSubquery = OrderDetail::query()
            ->whereHas('order', function($query) use ($dateRange) {
                $query->where('billed', true)
                      ->whereBetween('created_at', $dateRange);
            })
            ->sum('subtotal');

        return OrderDetail::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue'),
                DB::raw('AVG(unit_price) as avg_price'),
                DB::raw('(SUM(subtotal) / ' . ($totalSalesSubquery ?: 1) . ' * 100) as percentage')
            ])
            ->with(['product.category'])
            ->whereHas('order', function($query) use ($dateRange) {
                $query->where('billed', true)
                      ->whereBetween('created_at', $dateRange);
            })
            ->whereHas('product', function($query) {
                $query->where('active', true);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_quantity');
    }

    // 📅 OBTENER RANGO DE FECHAS SEGÚN FILTRO
    private function getDateRange(): array
    {
        switch ($this->filter) {
            case 'today':
                return [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay()
                ];

            case 'yesterday':
                return [
                    Carbon::yesterday()->startOfDay(),
                    Carbon::yesterday()->endOfDay()
                ];

            case 'last_7_days':
                return [
                    Carbon::today()->subDays(6)->startOfDay(),
                    Carbon::today()->endOfDay()
                ];

            case 'this_week':
                return [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ];

            case 'last_30_days':
                return [
                    Carbon::today()->subDays(29)->startOfDay(),
                    Carbon::today()->endOfDay()
                ];

            case 'this_month':
                return [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ];

            case 'last_month':
                return [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ];

            default:
                return [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay()
                ];
        }
    }

    // 📋 DESCRIPCIÓN DEL WIDGET
    public function getTableDescription(): ?string
    {
        $periodLabel = $this->getFilters()[$this->filter] ?? 'Hoy';
        $dateRange = $this->getDateRange();

        return "Ranking de productos más vendidos - {$periodLabel} | " .
               "Período: " . $dateRange[0]->format('d/m/Y') . " - " . $dateRange[1]->format('d/m/Y');
    }
}
