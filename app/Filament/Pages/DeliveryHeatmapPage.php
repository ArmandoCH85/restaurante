<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DeliveryHeatmapPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Mapa de calor - Delivery';

    protected static ?string $title = 'Mapa de calor - Delivery';

    protected static ?string $navigationGroup = 'Reportes y Analisis';

    protected static ?string $slug = 'reportes/mapa-calor-delivery';

    protected static ?int $navigationSort = 7;

    protected static string $view = 'filament.pages.delivery-heatmap-page';

    public ?string $fromDateTime = null;

    public ?string $toDateTime = null;

    public ?string $deliveryStatus = null;

    public string $intensityMode = 'count';

    public bool $autoRefresh = false;

    public int $refreshInterval = 60;

    public function mount(): void
    {
        $this->fromDateTime = request('fromDateTime', now()->startOfDay()->format('Y-m-d H:i'));
        $this->toDateTime = request('toDateTime', now()->endOfDay()->format('Y-m-d H:i'));
        $this->deliveryStatus = request('deliveryStatus');
        $this->intensityMode = request('intensityMode', 'count');
        $this->autoRefresh = (bool) request('autoRefresh', false);
        $this->refreshInterval = (int) request('refreshInterval', 60);

        $this->form->fill([
            'fromDateTime' => $this->fromDateTime,
            'toDateTime' => $this->toDateTime,
            'deliveryStatus' => $this->deliveryStatus,
            'intensityMode' => $this->intensityMode,
            'autoRefresh' => $this->autoRefresh,
            'refreshInterval' => $this->refreshInterval,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('fromDateTime')
                    ->label('Desde')
                    ->required()
                    ->seconds(false)
                    ->native(false),

                Forms\Components\DateTimePicker::make('toDateTime')
                    ->label('Hasta')
                    ->required()
                    ->seconds(false)
                    ->native(false),

                Forms\Components\Select::make('deliveryStatus')
                    ->label('Estado delivery')
                    ->placeholder('Todos')
                    ->options([
                        'pending' => 'Pendiente',
                        'assigned' => 'Asignado',
                        'in_transit' => 'En tránsito',
                        'delivered' => 'Entregado',
                        'cancelled' => 'Cancelado',
                    ]),

                Forms\Components\Radio::make('intensityMode')
                    ->label('Intensidad')
                    ->inline()
                    ->default('count')
                    ->options([
                        'count' => 'Cantidad',
                        'amount' => 'Monto pedido',
                    ])
                    ->required(),

                Forms\Components\Toggle::make('autoRefresh')
                    ->label('Auto refrescar')
                    ->default(false),

                Forms\Components\Select::make('refreshInterval')
                    ->label('Intervalo (segundos)')
                    ->default(60)
                    ->options([
                        60 => '60',
                        90 => '90',
                        120 => '120',
                    ])
                    ->visible(fn (Forms\Get $get): bool => (bool) $get('autoRefresh')),
            ])
            ->columns(3);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply_filters')
                ->label('Aplicar filtros')
                ->icon('heroicon-o-funnel')
                ->action(fn () => $this->applyFilters()),
            Action::make('refresh_now')
                ->label('Refrescar ahora')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->refreshHeatmap()),
        ];
    }

    public function applyFilters(): void
    {
        $this->validate([
            'fromDateTime' => ['required', 'date'],
            'toDateTime' => ['required', 'date', 'after_or_equal:fromDateTime'],
            'deliveryStatus' => ['nullable', 'in:pending,assigned,in_transit,delivered,cancelled'],
            'intensityMode' => ['required', 'in:count,amount'],
            'autoRefresh' => ['boolean'],
            'refreshInterval' => ['required', 'in:60,90,120'],
        ]);
    }

    public function refreshHeatmap(): void
    {
        // Trigger de re-render para recalcular puntos y KPIs.
    }

    public function getKpis(): array
    {
        $baseQuery = $this->baseOrdersQuery();

        $totalDeliveries = (clone $baseQuery)->count();
        $withCoordinates = (clone $baseQuery)
            ->whereNotNull('delivery_orders.delivery_latitude')
            ->whereNotNull('delivery_orders.delivery_longitude')
            ->count();

        return [
            'total_deliveries' => $totalDeliveries,
            'with_coordinates' => $withCoordinates,
            'without_geolocation' => max(0, $totalDeliveries - $withCoordinates),
        ];
    }

    public function getHeatmapPayload(): array
    {
        $rows = $this->coordinatesQuery()
            ->select([
                'delivery_orders.delivery_latitude as lat',
                'delivery_orders.delivery_longitude as lng',
                'orders.total',
            ])
            ->get();

        $points = [];

        foreach ($rows as $row) {
            $lat = (float) $row->lat;
            $lng = (float) $row->lng;
            $intensity = $this->intensityMode === 'amount'
                ? max(0.0, (float) ($row->total ?? 0))
                : 1.0;

            $points[] = [$lat, $lng, $intensity];
        }

        return [
            'points' => $points,
            'mode' => $this->intensityMode,
            'attribution' => '© OpenStreetMap contributors (ODbL)',
        ];
    }

    private function baseOrdersQuery(): Builder
    {
        return DB::table('delivery_orders')
            ->join('orders', 'orders.id', '=', 'delivery_orders.order_id')
            ->where('orders.service_type', 'delivery')
            ->whereBetween('orders.order_datetime', [$this->fromDateTime, $this->toDateTime])
            ->when($this->deliveryStatus, function (Builder $query): void {
                $query->where('delivery_orders.status', $this->deliveryStatus);
            });
    }

    private function coordinatesQuery(): Builder
    {
        return $this->baseOrdersQuery()
            ->whereNotNull('delivery_orders.delivery_latitude')
            ->whereNotNull('delivery_orders.delivery_longitude');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
