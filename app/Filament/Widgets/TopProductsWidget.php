<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\DateRangeFilterTrait;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TopProductsWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    use DateRangeFilterTrait;

    protected static ?string $heading = 'Top Productos Vendidos';

    protected static ?int $sort = 3;

    // 📐 ANCHO COMPLETO PARA LA TABLA
    protected int | string | array $columnSpan = 'full';

    // 🔄 REACTIVIDAD A FILTROS DEL DASHBOARD
    protected static bool $isLazy = false;

    protected $listeners = [
        'filtersFormUpdated' => '$refresh',
        'updateCharts' => '$refresh',
    ];

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
                        1 => 'warning',
                        2 => 'gray',
                        3 => 'danger',
                        default => 'primary'
                    })
                    ->formatStateUsing(fn ($record, $rowLoop) => match($rowLoop->iteration) {
                        1 => 'TOP 1',
                        2 => 'TOP 2',
                        3 => 'TOP 3',
                        default => $rowLoop->iteration . '°'
                    }),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->product->name),

                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Vendidos')
                    ->alignCenter()
                    ->weight('bold')
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state . ' unid.'),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Ingresos')
                    ->money('PEN')
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('avg_price')
                    ->label('Precio prom.')
                    ->money('PEN')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('% ventas')
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
            ->paginated([10])
            ->striped();
    }

    // 📊 QUERY PARA OBTENER PRODUCTOS MÁS VENDIDOS
    protected function getTableQuery(): Builder
    {
        // Resolver rango desde filtros unificados
        [$start, $end] = $this->resolveDateRange($this->filters ?? []);

        /**
         * Ajustes aplicados:
         * - Simplifica columna temporal (order_datetime si existe, sin COALESCE porque es NOT NULL; fallback created_at).
         * - Permite configurar el tipo de porcentaje (revenue|units) con env TOP_PRODUCTS_PERCENTAGE_BY (default revenue).
         * - Usa whereIn para estados válidos en lugar de != cancelled (mejor uso de índice y semántica explícita).
         * - Simplifica expresión de ventana (SUM(col) OVER()) evitando SUM(SUM()).
         * - Añade MIN(order_details.id) como id para evitar posibles avisos de modelo sin PK al hidratar.
         */

        $timeColumn = Schema::hasColumn('orders', 'order_datetime') ? 'orders.order_datetime' : 'orders.created_at';
        $percentageMode = env('TOP_PRODUCTS_PERCENTAGE_BY', 'revenue'); // 'revenue' o 'units'

        $statusFilter = ['completed']; // Ajustar si se desea incluir otros estados facturados

        $sumValueExpr = $percentageMode === 'units'
            ? 'SUM(order_details.quantity)'
            : 'SUM(order_details.subtotal)';
        // Calcular total del período (denominador) y usarlo siempre (evita problemas con ONLY_FULL_GROUP_BY y ventanas)
        $periodTotal = (float) OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.billed', true)
            ->whereIn('orders.status', $statusFilter)
            ->whereBetween($timeColumn, [$start, $end])
            ->select(DB::raw($sumValueExpr . ' as agg'))
            ->value('agg');

        $denominator = $periodTotal > 0 ? $periodTotal : 1;

        $percentageSelect = $percentageMode === 'units'
            ? DB::raw('(SUM(order_details.quantity) / ' . $denominator . ' * 100) as percentage')
            : DB::raw('(SUM(order_details.subtotal) / ' . $denominator . ' * 100) as percentage');

        return OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->select([
                DB::raw('MIN(order_details.id) as id'),
                'order_details.product_id',
                DB::raw('SUM(order_details.quantity) as total_quantity'),
                DB::raw('SUM(order_details.subtotal) as total_revenue'),
                DB::raw('AVG(order_details.unit_price) as avg_price'),
                $percentageSelect,
            ])
            ->where('orders.billed', true)
            ->whereIn('orders.status', $statusFilter)
            ->whereBetween($timeColumn, [$start, $end])
            ->groupBy('order_details.product_id')
            ->orderByDesc('total_quantity')
            ->with(['product.category'])
            ->whereHas('product', function ($q) {
                $q->where('active', true);
            });
    }

    // 📅 OBTENER RANGO DE FECHAS SEGÚN FILTRO DEL DASHBOARD
    // Eliminado getDateRange: se usa resolveDateRange del trait

    // 📋 DESCRIPCIÓN DEL WIDGET
    public function getTableDescription(): ?string
    {
        [$start, $end] = $this->resolveDateRange($this->filters ?? []);
        $labelMap = [
            'today' => 'Hoy',
            'yesterday' => 'Ayer',
            'last_7_days' => 'Últimos 7 días',
            'last_30_days' => 'Últimos 30 días',
            'this_month' => 'Este mes',
            'last_month' => 'Mes pasado',
            'custom' => 'Personalizado',
        ];
        $code = $this->filters['date_range'] ?? 'today';
        $periodLabel = $labelMap[$code] ?? 'Hoy';

        return "Ranking de productos más vendidos • {$periodLabel} • " .
               $this->humanRangeLabel($start, $end);
    }
}
