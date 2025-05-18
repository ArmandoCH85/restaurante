<?php

namespace App\Filament\Widgets;

use App\Models\OrderDetail;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Productos Más Vendidos';

    protected int | string | array $columnSpan = 'full';

    // Filtro de fecha
    public ?string $filter = 'week';

    // Sobrescribir el método para proporcionar una clave única
    public function getTableRecordKey($record): string
    {
        return (string) $record->product_id;
    }

    public function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('period')
                ->label('Período')
                ->options([
                    'day' => 'Hoy',
                    'week' => 'Esta semana',
                    'month' => 'Este mes',
                    'year' => 'Este año',
                ])
                ->default('week')
                ->attribute('filter')
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $startDate = match ($this->filter) {
                    'day' => Carbon::today(),
                    'week' => Carbon::now()->startOfWeek(),
                    'month' => Carbon::now()->startOfMonth(),
                    'year' => Carbon::now()->startOfYear(),
                    default => Carbon::now()->startOfWeek(),
                };

                $endDate = Carbon::now();

                return OrderDetail::whereHas('order', function (Builder $query) use ($startDate, $endDate) {
                        $query->whereBetween('order_datetime', [$startDate, $endDate])
                            ->where('billed', true);
                    })
                    ->select(
                        'product_id',
                        DB::raw('SUM(quantity) as quantity_sold'),
                        DB::raw('SUM(subtotal) as total_sales'),
                        DB::raw('AVG(unit_price) as average_price')
                    )
                    ->with('product:id,name,category_id,current_cost,image_path', 'product.category:id,name')
                    ->groupBy('product_id')
                    ->orderByDesc('quantity_sold');
            })
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Categoría')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity_sold')
                    ->label('Cantidad Vendida')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Ventas Totales')
                    ->money('PEN')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('average_price')
                    ->label('Precio Promedio')
                    ->money('PEN')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('profit')
                    ->label('Ganancia Estimada')
                    ->money('PEN')
                    ->state(function ($record): float {
                        $cost = $record->product->current_cost ?? 0;
                        return $record->total_sales - ($cost * $record->quantity_sold);
                    })
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'danger')
                    ->alignRight(),
            ])
            ->defaultSort('quantity_sold', 'desc')
            ->paginated([10, 25, 50])
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
