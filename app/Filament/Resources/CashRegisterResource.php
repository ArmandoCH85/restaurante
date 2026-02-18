<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashRegisterResource\Pages;
use App\Models\CashRegister;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Caja';

    // Mostrar en el menú de navegación
    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationLabel = 'Apertura y Cierre de Caja';

    protected static ?string $modelLabel = 'Operación de Caja';

    protected static ?string $pluralModelLabel = 'Operaciones de Caja';

    protected static ?string $slug = 'operaciones-caja';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    // Aplicar color de fondo destacado al item de navegación
    public static function getNavigationLabel(): string
    {
        return 'Apertura y Cierre de Caja';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calculator';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Caja';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Apertura')
                    ->description('Datos de apertura de caja')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('opening_amount')
                            ->label('Monto Inicial')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->prefix('S/')
                            ->columnSpan(2),
                        Forms\Components\DateTimePicker::make('opening_datetime')
                            ->label('Fecha y Hora de Apertura')
                            ->required()
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('opened_by_name')
                            ->label('Abierto por')
                            ->formatStateUsing(fn ($record) => $record->openedBy->name ?? '')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(2),
                        Forms\Components\Hidden::make('opened_by')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && ! $record->is_active),

                Forms\Components\Section::make('Información de Apertura')
                    ->description('Datos de apertura de caja')
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        Forms\Components\TextInput::make('opening_amount')
                            ->label('Monto Inicial')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('S/')
                            ->placeholder('0.00')
                            ->helperText('Ingrese el monto inicial con el que abre la caja')
                            ->rules(['required', 'numeric', 'min:0'])
                            ->validationMessages([
                                'required' => 'El monto inicial es obligatorio',
                                'numeric' => 'El monto inicial debe ser un número',
                                'min' => 'El monto inicial debe ser mayor o igual a cero',
                            ])
                            ->columnSpan(2),
                        Forms\Components\DateTimePicker::make('opening_datetime')
                            ->label('Fecha y Hora de Apertura')
                            ->required()
                            ->default(now())
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('opened_by_name')
                            ->label('Abierto por')
                            ->default(auth()->user()->name)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(2),
                        Forms\Components\Hidden::make('opened_by')
                            ->default(auth()->id())
                            ->required(),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->placeholder('Observaciones sobre la apertura de caja')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => ! $record || $record->is_active),

                Forms\Components\Section::make('Resumen de Ventas')
                    ->description('Comparativo: Sistema vs Conteo Manual')
                    ->icon('heroicon-m-chart-bar')
                    ->schema(function () {
                        $user = auth()->user();
                        $isSupervisor = $user->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier']);

                        if ($isSupervisor) {
                            return [
                                Forms\Components\Grid::make()
                                    ->columns([
                                        'default' => 1,
                                        'md' => 4,
                                    ])
                                    ->columnSpanFull()
                                    ->schema([
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('metodo_efectivo')->label('Método')->content('Efectivo'),
                                                Forms\Components\Placeholder::make('sistema_efectivo')->label('Sistema')->content(function ($record) {
                                                    return $record ? 'S/ '.number_format($record->getSystemCashSales(), 2) : 'S/ 0.00';
                                                }),
                                                Forms\Components\TextInput::make('manual_cash')
                                                    ->label('Contado')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->default(0)
                                                    ->helperText(function ($record) {
                                                        if (! $record) {
                                                            return null;
                                                        }

                                                        $systemCash = (float) $record->getSystemCashSales();

                                                        if ($systemCash <= 0.009) {
                                                            return 'El sistema no registra ventas en efectivo para este turno.';
                                                        }

                                                        return 'Si dejas este valor en 0.00 no podrás cerrar la caja. Efectivo sistema: S/ '.number_format($systemCash, 2);
                                                    })
                                                    ->live(),
                                                Forms\Components\Placeholder::make('diff_efectivo')
                                                    ->label('Diferencia')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        $sistema = $record ? $record->getSystemCashSales() : 0;
                                                        $manual = floatval($get('manual_cash') ?? 0);
                                                        $diff = $manual - $sistema;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('metodo_yape')->label('Método')->content('Yape'),
                                                Forms\Components\Placeholder::make('sistema_yape')->label('Sistema')->content(function ($record) {
                                                    return $record ? 'S/ '.number_format($record->getSystemYapeSales(), 2) : 'S/ 0.00';
                                                }),
                                                Forms\Components\TextInput::make('manual_yape')
                                                    ->label('Contado')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->default(0)
                                                    ->live(),
                                                Forms\Components\Placeholder::make('diff_yape')
                                                    ->label('Diferencia')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        $sistema = $record ? $record->getSystemYapeSales() : 0;
                                                        $manual = floatval($get('manual_yape') ?? 0);
                                                        $diff = $manual - $sistema;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('metodo_plin')->label('Método')->content('Plin'),
                                                Forms\Components\Placeholder::make('sistema_plin')->label('Sistema')->content(function ($record) {
                                                    return $record ? 'S/ '.number_format($record->getSystemPlinSales(), 2) : 'S/ 0.00';
                                                }),
                                                Forms\Components\TextInput::make('manual_plin')
                                                    ->label('Contado')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->default(0)
                                                    ->live(),
                                                Forms\Components\Placeholder::make('diff_plin')
                                                    ->label('Diferencia')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        $sistema = $record ? $record->getSystemPlinSales() : 0;
                                                        $manual = floatval($get('manual_plin') ?? 0);
                                                        $diff = $manual - $sistema;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('metodo_tarjetas')->label('Método')->content('Tarjetas'),
                                                Forms\Components\Placeholder::make('sistema_tarjetas')->label('Sistema')->content(function ($record) {
                                                    return $record ? 'S/ '.number_format($record->getSystemCardSales(), 2) : 'S/ 0.00';
                                                }),
                                                Forms\Components\TextInput::make('manual_card')
                                                    ->label('Contado')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->default(0)
                                                    ->live(),
                                                Forms\Components\Placeholder::make('diff_tarjetas')
                                                    ->label('Diferencia')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        $sistema = $record ? $record->getSystemCardSales() : 0;
                                                        $manual = floatval($get('manual_card') ?? 0);
                                                        $diff = $manual - $sistema;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('metodo_didi')->label('Método')->content('Didi Food'),
                                                Forms\Components\Placeholder::make('sistema_didi')->label('Sistema')->content(function ($record) {
                                                    return $record ? 'S/ '.number_format($record->getSystemDidiSales(), 2) : 'S/ 0.00';
                                                }),
                                                Forms\Components\TextInput::make('manual_didi')
                                                    ->label('Contado')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->default(0)
                                                    ->live(),
                                                Forms\Components\Placeholder::make('diff_didi')
                                                    ->label('Diferencia')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        $sistema = $record ? $record->getSystemDidiSales() : 0;
                                                        $manual = floatval($get('manual_didi') ?? 0);
                                                        $diff = $manual - $sistema;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('metodo_pedidosya')->label('Método')->content('PedidosYa'),
                                                Forms\Components\Placeholder::make('sistema_pedidosya')->label('Sistema')->content(function ($record) {
                                                    return $record ? 'S/ '.number_format($record->getSystemPedidosYaSales(), 2) : 'S/ 0.00';
                                                }),
                                                Forms\Components\TextInput::make('manual_pedidos_ya')
                                                    ->label('Contado')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->default(0)
                                                    ->live(),
                                                Forms\Components\Placeholder::make('diff_pedidosya')
                                                    ->label('Diferencia')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        $sistema = $record ? $record->getSystemPedidosYaSales() : 0;
                                                        $manual = floatval($get('manual_pedidos_ya') ?? 0);
                                                        $diff = $manual - $sistema;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->schema([
                                                Forms\Components\Placeholder::make('metodo_bita')->label('Método')->content('Bita Express'),
                                                Forms\Components\Placeholder::make('sistema_bita')->label('Sistema')->content(function ($record) {
                                                    return $record ? 'S/ '.number_format($record->getSystemBitaExpressSales(), 2) : 'S/ 0.00';
                                                }),
                                                Forms\Components\TextInput::make('manual_bita_express')
                                                    ->label('Contado')
                                                    ->numeric()
                                                    ->prefix('S/')
                                                    ->default(0)
                                                    ->live(),
                                                Forms\Components\Placeholder::make('diff_bita')
                                                    ->label('Diferencia')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        $sistema = $record ? $record->getSystemBitaExpressSales() : 0;
                                                        $manual = floatval($get('manual_bita_express') ?? 0);
                                                        $diff = $manual - $sistema;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                        Forms\Components\Grid::make()
                                            ->columns(4)
                                            ->columnSpanFull()
                                            ->schema([
                                                Forms\Components\Placeholder::make('total_label')->label('')->content(''),
                                                Forms\Components\Placeholder::make('total_sistema')
                                                    ->label('TOTAL')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        return $record ? 'S/ '.number_format($record->getSystemTotalSales(), 2) : 'S/ 0.00';
                                                    }),
                                                Forms\Components\Placeholder::make('total_contado')
                                                    ->label('TOTAL')
                                                    ->live()
                                                    ->content(function ($get) {
                                                        $total = floatval($get('manual_cash') ?? 0) + floatval($get('manual_yape') ?? 0) + floatval($get('manual_plin') ?? 0) + floatval($get('manual_card') ?? 0) + floatval($get('manual_didi') ?? 0) + floatval($get('manual_pedidos_ya') ?? 0) + floatval($get('manual_bita_express') ?? 0);

                                                        return 'S/ '.number_format($total, 2);
                                                    }),
                                                Forms\Components\Placeholder::make('diferencia_total')
                                                    ->label('DIFERENCIA CIERRE')
                                                    ->live()
                                                    ->content(function ($record, $get) {
                                                        if (! $record) {
                                                            return 'S/ 0.00';
                                                        }
                                                        $contado = floatval($get('manual_cash') ?? 0) + floatval($get('manual_yape') ?? 0) + floatval($get('manual_plin') ?? 0) + floatval($get('manual_card') ?? 0) + floatval($get('manual_didi') ?? 0) + floatval($get('manual_pedidos_ya') ?? 0) + floatval($get('manual_bita_express') ?? 0);
                                                        $expected = (float) $record->calculateExpectedCash();
                                                        $diff = ($contado + (float) $record->opening_amount) - $expected;
                                                        $color = abs($diff) < 0.01 ? 'success' : ($diff > 0 ? 'warning' : 'danger');

                                                        return new \Illuminate\Support\HtmlString("<span class='text-{$color}-600 font-bold'>S/ ".number_format($diff, 2).'</span>');
                                                    }),
                                            ]),
                                    ]),
                            ];
                        }

                        return [
                            Forms\Components\Placeholder::make('blind_closing')
                                ->label('Cierre a Ciegas')
                                ->content('Ingrese el monto total de efectivo sin consultar los montos del sistema')
                                ->columnSpan('full'),
                            Forms\Components\TextInput::make('manual_cash')
                                ->label('Efectivo Contado')
                                ->numeric()
                                ->prefix('S/')
                                ->helperText(function ($record) {
                                    if (! $record) {
                                        return null;
                                    }

                                    $systemCash = (float) $record->getSystemCashSales();

                                    if ($systemCash <= 0.009) {
                                        return 'El sistema no registra ventas en efectivo para este turno.';
                                    }

                                    return 'Si dejas este valor en 0.00 no podrás cerrar la caja. Efectivo sistema: S/ '.number_format($systemCash, 2);
                                })
                                ->default(0),
                        ];
                    })
                    ->columns(1)
                    ->visible(fn ($record) => $record && $record->is_active),

                Forms\Components\Section::make('Resumen de Cierre')
                    ->description('Resumen del cierre de caja')
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Placeholder::make('monto_inicial')
                                    ->label('Monto Inicial')
                                    ->content(function ($record) {
                                        return $record ? 'S/ '.number_format($record->opening_amount, 2) : 'S/ 0.00';
                                    }),
                                Forms\Components\Placeholder::make('total_ingresos')
                                    ->label('Ingresos')
                                    ->content(function ($record) {
                                        return $record ? 'S/ '.number_format($record->getSystemTotalSales(), 2) : 'S/ 0.00';
                                    }),
                                Forms\Components\Placeholder::make('total_egresos')
                                    ->label('Egresos')
                                    ->content(function ($record) {
                                        if (! $record) {
                                            return 'S/ 0.00';
                                        }

                                        return 'S/ '.number_format($record->getCachedExpenses(), 2);
                                    }),
                                Forms\Components\Placeholder::make('ganancia_real')
                                    ->label('Ganancia Real')
                                    ->content(function ($record) {
                                        if (! $record) {
                                            return 'S/ 0.00';
                                        }
                                        $ingresos = $record->getSystemTotalSales();
                                        $egresos = $record->getCachedExpenses();

                                        return 'S/ '.number_format($ingresos - $egresos, 2);
                                    }),
                            ]),
                    ])
                    ->visible(fn ($record) => $record && $record->is_active),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->prefix('#'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(fn ($record) => $record->is_active ? 'Abierta' : 'Cerrada')
                    ->colors([
                        'success' => 'Abierta',
                        'danger' => 'Cerrada',
                    ]),
                Tables\Columns\TextColumn::make('opening_datetime')
                    ->label('Apertura')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('closing_datetime')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En curso'),
                Tables\Columns\TextColumn::make('openedBy.name')
                    ->label('Abierto por'),
                Tables\Columns\TextColumn::make('closedBy.name')
                    ->label('Cerrado por')
                    ->placeholder('En curso'),
                Tables\Columns\TextColumn::make('opening_amount')
                    ->label('Monto Inicial')
                    ->money('PEN')
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier'])),
            ])
            ->filters([
                // Filtro de estado mejorado con iconos
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Estado de Operación')
                    ->options([
                        1 => 'Abierta',
                        0 => 'Cerrada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null)
                    ->multiple(false),

                // Filtro de aprobación mejorado
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('Estado de Aprobación')
                    ->options([
                        1 => 'Aprobada',
                        0 => 'Pendiente/Rechazada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null)
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier'])),

                // Filtro de responsable
                Tables\Filters\SelectFilter::make('opened_by')
                    ->label('Responsable')
                    ->relationship('openedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos los responsables'),

                // Filtro de fecha mejorado con presets
                Tables\Filters\Filter::make('opening_datetime')
                    ->label('Período')
                    ->form([
                        Forms\Components\Section::make('Rango de Fechas')
                            ->description('Seleccione el período de operaciones')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('desde')
                                            ->label('Desde')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                        Forms\Components\DatePicker::make('hasta')
                                            ->label('Hasta')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                    ]),

                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('hoy')
                                                ->label('Hoy')
                                                ->color('primary')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', today()->format('Y-m-d'));
                                                    $set('hasta', today()->format('Y-m-d'));
                                                }),
                                            Forms\Components\Actions\Action::make('ayer')
                                                ->label('Ayer')
                                                ->color('gray')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', yesterday()->format('Y-m-d'));
                                                    $set('hasta', yesterday()->format('Y-m-d'));
                                                }),
                                            Forms\Components\Actions\Action::make('semana')
                                                ->label('Esta Semana')
                                                ->color('info')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', now()->startOfWeek()->format('Y-m-d'));
                                                    $set('hasta', now()->endOfWeek()->format('Y-m-d'));
                                                }),
                                            Forms\Components\Actions\Action::make('mes')
                                                ->label('Este Mes')
                                                ->color('success')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', now()->startOfMonth()->format('Y-m-d'));
                                                    $set('hasta', now()->endOfMonth()->format('Y-m-d'));
                                                }),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('opening_datetime', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('opening_datetime', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde: '.\Carbon\Carbon::parse($data['desde'])->format('d/m/Y');
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta: '.\Carbon\Carbon::parse($data['hasta'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('closing_datetime')
                    ->form([
                        Forms\Components\DatePicker::make('desde_cierre')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta_cierre')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde_cierre'],
                                fn (Builder $query, $date): Builder => $query->whereDate('closing_datetime', '>=', $date),
                            )
                            ->when(
                                $data['hasta_cierre'],
                                fn (Builder $query, $date): Builder => $query->whereDate('closing_datetime', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde_cierre'] ?? null) {
                            $indicators['desde_cierre'] = 'Cierre desde '.$data['desde_cierre'];
                        }

                        if ($data['hasta_cierre'] ?? null) {
                            $indicators['hasta_cierre'] = 'Cierre hasta '.$data['hasta_cierre'];
                        }

                        return $indicators;
                    })
                    ->label('Fecha de Cierre'),

                Tables\Filters\SelectFilter::make('opened_by')
                    ->label('Abierto por')
                    ->relationship('openedBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('closed_by')
                    ->label('Cerrado por')
                    ->relationship('closedBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('difference')
                    ->form([
                        Forms\Components\TextInput::make('min_difference')
                            ->label('Diferencia mínima')
                            ->numeric()
                            ->placeholder('-100.00'),
                        Forms\Components\TextInput::make('max_difference')
                            ->label('Diferencia máxima')
                            ->numeric()
                            ->placeholder('100.00'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_difference'] !== null,
                                fn (Builder $query, $min): Builder => $query->where('difference', '>=', $data['min_difference']),
                            )
                            ->when(
                                $data['max_difference'] !== null,
                                fn (Builder $query, $max): Builder => $query->where('difference', '<=', $data['max_difference']),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (isset($data['min_difference'])) {
                            $indicators['min_difference'] = 'Diferencia mín: S/ '.$data['min_difference'];
                        }

                        if (isset($data['max_difference'])) {
                            $indicators['max_difference'] = 'Diferencia máx: S/ '.$data['max_difference'];
                        }

                        return $indicators;
                    })
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier'])),
            ])
            ->filtersFormColumns(3)
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-m-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Cerrar')
                    ->icon('heroicon-m-lock-closed')
                    ->visible(fn ($record) => $record->is_active),
                Tables\Actions\Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->url(fn ($record) => url('/admin/print-cash-register/'.$record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_active && ! $record->is_approved && auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_approved' => true, 'approval_notes' => 'Aprobado manualmente']);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Caja aprobada')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin'])),
                ]),
            ])
            ->headerActions([
                // Acción principal: Abrir nueva caja (solo cuando no hay caja abierta)
                Tables\Actions\CreateAction::make()
                    ->label('Abrir Nueva Caja')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->size('lg')
                    ->button()
                    ->visible(function () {
                        return ! CashRegister::getOpenRegister();
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['opened_by'] = auth()->id();
                        $data['opening_datetime'] = now();
                        $data['is_active'] = true;

                        return $data;
                    })
                    ->successNotificationTitle('Caja abierta correctamente')
                    ->after(function () {
                        redirect()->to('/admin/pos-interface');
                    }),

                // Reconciliación visible (principio KISS)
                Tables\Actions\Action::make('reconcile_all')
                    ->label('Reconciliar Pendientes')
                    ->icon('heroicon-m-scale')
                    ->color('warning')
                    ->button()
                    ->visible(function () {
                        $user = auth()->user();
                        if (! $user->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier'])) {
                            return false;
                        }

                        // Solo mostrar si hay cajas pendientes de reconciliar
                        return CashRegister::where('is_active', false)
                            ->where('is_approved', false)
                            ->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reconciliar Cajas Pendientes')
                    ->modalDescription('¿Desea marcar todas las cajas cerradas como reconciliadas?')
                    ->action(function () {
                        $count = CashRegister::where('is_active', false)
                            ->where('is_approved', false)
                            ->update(['is_approved' => true, 'approval_notes' => 'Reconciliación masiva']);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("{$count} cajas reconciliadas")
                            ->send();
                    }),

                // Exportar (acción secundaria simple)
                Tables\Actions\Action::make('export_today')
                    ->label('Exportar Hoy')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('info')
                    ->button()
                    ->action(function () {
                        \Filament\Notifications\Notification::make()
                            ->info()
                            ->title('Exportando...')
                            ->body('Se está generando el reporte del día.')
                            ->send();
                    }),
            ])
            ->defaultSort('opening_datetime', 'desc')
            ->emptyStateIcon('heroicon-o-calculator')
            ->emptyStateHeading('No hay cajas registradas')
            ->emptyStateDescription('Use el botón "Abrir Nueva Caja" en la parte superior para comenzar.');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashRegisters::route('/'),
            'create' => Pages\CreateCashRegister::route('/create'),
            'edit' => Pages\EditCashRegister::route('/{record}/edit'),
            'view' => Pages\ViewCashRegister::route('/{record}'),
        ];
    }
}
