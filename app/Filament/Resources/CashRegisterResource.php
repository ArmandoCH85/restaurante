<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashRegisterResource\Pages;
use App\Models\CashRegister;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = '📄 Facturación y Ventas';

    // Mostrar en el menú de navegación
    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationLabel = 'Apertura y Cierre de Caja';

    protected static ?string $modelLabel = 'Operación de Caja';

    protected static ?string $pluralModelLabel = 'Operaciones de Caja';

    protected static ?string $slug = 'operaciones-caja';

    protected static ?int $navigationSort = 10;

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
                    ->visible(fn ($record) => $record && !$record->is_active),

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
                    ->visible(fn ($record) => !$record || $record->is_active),

                Forms\Components\Section::make('Conteo de Efectivo')
                    ->description('Ingrese la cantidad de billetes y monedas para el cierre de caja')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Section::make('Billetes')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('bill_10')
                                                    ->label('S/10')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($state ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('bill_20')
                                                    ->label('S/20')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($state ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('bill_50')
                                                    ->label('S/50')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($state ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('bill_100')
                                                    ->label('S/100')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($state ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('bill_200')
                                                    ->label('S/200')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($state ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                            ]),
                                    ]),
                                Forms\Components\Section::make('Monedas')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('coin_010')
                                                    ->label('S/0.10')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($state ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('coin_020')
                                                    ->label('S/0.20')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($state ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('coin_050')
                                                    ->label('S/0.50')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($state ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('coin_1')
                                                    ->label('S/1')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($state ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('coin_2')
                                                    ->label('S/2')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($get('coin_5') ?? 0)) * 5 +
                                                                   (floatval($state ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                                Forms\Components\TextInput::make('coin_5')
                                                    ->label('S/5')
                                                    ->inputMode('decimal')
                                                    ->default(0)
                                                    ->rules(['required', 'numeric', 'min:0'])
                                                    ->required()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                                   (floatval($get('bill_100') ?? 0)) * 100 +
                                                                   (floatval($get('bill_50') ?? 0)) * 50 +
                                                                   (floatval($get('bill_20') ?? 0)) * 20 +
                                                                   (floatval($get('bill_10') ?? 0)) * 10 +
                                                                   (floatval($state ?? 0)) * 5 +
                                                                   (floatval($get('coin_2') ?? 0)) * 2 +
                                                                   (floatval($get('coin_1') ?? 0)) * 1 +
                                                                   (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                                   (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                                   (floatval($get('coin_010') ?? 0)) * 0.1;
                                                        $set('calculated_cash_display', $efectivo);
                                                        
                                                        // También actualizar el total manual
                                                        $otros = (floatval($get('manual_yape') ?? 0)) +
                                                                (floatval($get('manual_plin') ?? 0)) +
                                                                (floatval($get('manual_card') ?? 0)) +
                                                                (floatval($get('manual_didi') ?? 0)) +
                                                                (floatval($get('manual_pedidos_ya') ?? 0));
                                                        $total = $efectivo + $otros;
                                                        $set('calculated_total_manual', $total);
                                                    }),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Textarea::make('closing_observations')
                            ->label('Observaciones de Cierre')
                            ->placeholder('Observaciones sobre el cierre de caja')
                            ->columnSpan('full'),
                            
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('calcular_totales')
                                ->label('🔄 Calcular Totales')
                                ->color('primary')
                                ->button()
                                ->action(function ($set, $get) {
                                    // Calcular efectivo
                                    $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                               (floatval($get('bill_100') ?? 0)) * 100 +
                                               (floatval($get('bill_50') ?? 0)) * 50 +
                                               (floatval($get('bill_20') ?? 0)) * 20 +
                                               (floatval($get('bill_10') ?? 0)) * 10 +
                                               (floatval($get('coin_5') ?? 0)) * 5 +
                                               (floatval($get('coin_2') ?? 0)) * 2 +
                                               (floatval($get('coin_1') ?? 0)) * 1 +
                                               (floatval($get('coin_050') ?? 0)) * 0.5 +
                                               (floatval($get('coin_020') ?? 0)) * 0.2 +
                                               (floatval($get('coin_010') ?? 0)) * 0.1;
                                    
                                    $set('calculated_cash_display', $efectivo);
                                    
                                    // Calcular otros métodos
                                    $otros = (floatval($get('manual_yape') ?? 0)) +
                                            (floatval($get('manual_plin') ?? 0)) +
                                            (floatval($get('manual_card') ?? 0)) +
                                            (floatval($get('manual_didi') ?? 0)) +
                                            (floatval($get('manual_pedidos_ya') ?? 0)) +
                                            (floatval($get('manual_otros') ?? 0));
                                    
                                    // Calcular total manual
                                    $total = $efectivo + $otros;
                                    $set('calculated_total_manual', $total);
                                }),
                        ])->columnSpan('full'),
                    ])
                    ->visible(fn ($record) => $record && $record->is_active),


                Forms\Components\Section::make('Resumen de Ventas')
                    ->description('Comparativo: Montos del Sistema vs Conteo Manual')
                    ->icon('heroicon-m-chart-bar')
                    ->schema(function () {
                        $user = auth()->user();
                        $isSupervisor = $user->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier']);

                        if ($isSupervisor) {
                            return [
                                // Comparativo de Efectivo
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('cash_sales_display')
                                            ->label('💻 Sistema: Efectivo')
                                            ->content(function ($record) {
                                                if (!$record) return 'S/ 0.00';
                                                $cashSales = $record->getSystemCashSales();
                                                return 'S/ ' . number_format($cashSales, 2);
                                            })
                                            ->helperText('Ventas en efectivo registradas en el sistema'),
                                        Forms\Components\TextInput::make('calculated_cash_display')
                                            ->label('👥 Manual: Efectivo')
                                            ->disabled()
                                            ->prefix('S/')
                                            ->lazy()
                                            ->default(function ($get) {
                                                $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                           (floatval($get('bill_100') ?? 0)) * 100 +
                                                           (floatval($get('bill_50') ?? 0)) * 50 +
                                                           (floatval($get('bill_20') ?? 0)) * 20 +
                                                           (floatval($get('bill_10') ?? 0)) * 10 +
                                                           (floatval($get('coin_5') ?? 0)) * 5 +
                                                           (floatval($get('coin_2') ?? 0)) * 2 +
                                                           (floatval($get('coin_1') ?? 0)) * 1 +
                                                           (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                           (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                           (floatval($get('coin_010') ?? 0)) * 0.1;
                                                return number_format($efectivo, 2);
                                            })
                                            ->helperText('Billetes y monedas contados manualmente'),
                                    ])
                                    ->columnSpan('full'),

                                // Separador visual
                                Forms\Components\Placeholder::make('separator_1')
                                    ->content('──────────────────────────────────────')
                                    ->columnSpan('full'),

                                // Comparativo de Yape
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('yape_sales_display')
                                            ->label('💻 Sistema: Yape')
                                            ->content(function ($record) {
                                                if (!$record) return 'S/ 0.00';
                                                $yapeSales = $record->getSystemYapeSales();
                                                return 'S/ ' . number_format($yapeSales, 2);
                                            })
                                            ->helperText('Ventas Yape registradas en el sistema'),
                                        Forms\Components\TextInput::make('manual_yape')
                                            ->label('👥 Manual: Yape')
                                            ->inputMode('decimal')
                                            ->default(0)
                                            ->rules(['required', 'numeric', 'min:0'])
                                            ->prefix('S/')
                                            ->placeholder('0.00')
                                            ->helperText('Yape contado manualmente')
                                            ->required()
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                // Calcular efectivo
                                                $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                           (floatval($get('bill_100') ?? 0)) * 100 +
                                                           (floatval($get('bill_50') ?? 0)) * 50 +
                                                           (floatval($get('bill_20') ?? 0)) * 20 +
                                                           (floatval($get('bill_10') ?? 0)) * 10 +
                                                           (floatval($get('coin_5') ?? 0)) * 5 +
                                                           (floatval($get('coin_2') ?? 0)) * 2 +
                                                           (floatval($get('coin_1') ?? 0)) * 1 +
                                                           (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                           (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                           (floatval($get('coin_010') ?? 0)) * 0.1;
                                                
                                                // Actualizar efectivo display
                                                $set('calculated_cash_display', $efectivo);
                                                
                                                // Calcular otros métodos
                                                $otros = (floatval($state ?? 0)) +
                                                        (floatval($get('manual_plin') ?? 0)) +
                                                        (floatval($get('manual_card') ?? 0)) +
                                                        (floatval($get('manual_didi') ?? 0)) +
                                                        (floatval($get('manual_pedidos_ya') ?? 0)) +
                                                        (floatval($get('manual_otros') ?? 0));
                                                
                                                // Actualizar total manual
                                                $total = $efectivo + $otros;
                                                $set('calculated_total_manual', $total);
                                            }),
                                    ])
                                    ->columnSpan('full'),

                                // Comparativo de Plin
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('plin_sales_display')
                                            ->label('💻 Sistema: Plin')
                                            ->content(function ($record) {
                                                if (!$record) return 'S/ 0.00';
                                                $plinSales = $record->getSystemPlinSales();
                                                return 'S/ ' . number_format($plinSales, 2);
                                            })
                                            ->helperText('Ventas Plin registradas en el sistema'),
                                        Forms\Components\TextInput::make('manual_plin')
                                            ->label('👥 Manual: Plin')
                                            ->inputMode('decimal')
                                            ->default(0)
                                            ->rules(['required', 'numeric', 'min:0'])
                                            ->prefix('S/')
                                            ->placeholder('0.00')
                                            ->helperText('Plin contado manualmente')
                                            ->required()
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                // Calcular efectivo
                                                $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                           (floatval($get('bill_100') ?? 0)) * 100 +
                                                           (floatval($get('bill_50') ?? 0)) * 50 +
                                                           (floatval($get('bill_20') ?? 0)) * 20 +
                                                           (floatval($get('bill_10') ?? 0)) * 10 +
                                                           (floatval($get('coin_5') ?? 0)) * 5 +
                                                           (floatval($get('coin_2') ?? 0)) * 2 +
                                                           (floatval($get('coin_1') ?? 0)) * 1 +
                                                           (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                           (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                           (floatval($get('coin_010') ?? 0)) * 0.1;
                                                
                                                // Actualizar efectivo display
                                                $set('calculated_cash_display', $efectivo);
                                                
                                                // Calcular otros métodos
                                                $otros = (floatval($get('manual_yape') ?? 0)) +
                                                        (floatval($state ?? 0)) +
                                                        (floatval($get('manual_card') ?? 0)) +
                                                        (floatval($get('manual_didi') ?? 0)) +
                                                        (floatval($get('manual_pedidos_ya') ?? 0)) +
                                                        (floatval($get('manual_otros') ?? 0));
                                                
                                                // Actualizar total manual
                                                $total = $efectivo + $otros;
                                                $set('calculated_total_manual', $total);
                                            }),
                                    ])
                                    ->columnSpan('full'),

                                // Comparativo de Tarjetas
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('card_sales_display')
                                            ->label('💻 Sistema: Tarjetas')
                                            ->content(function ($record) {
                                                if (!$record) return 'S/ 0.00';
                                                $cardSales = $record->getSystemCardSales();
                                                return 'S/ ' . number_format($cardSales, 2);
                                            })
                                            ->helperText('Ventas con tarjeta registradas en el sistema'),
                                        Forms\Components\TextInput::make('manual_card')
                                            ->label('👥 Manual: Tarjetas')
                                            ->inputMode('decimal')
                                            ->default(0)
                                            ->rules(['required', 'numeric', 'min:0'])
                                            ->prefix('S/')
                                            ->placeholder('0.00')
                                            ->helperText('Tarjetas contadas manualmente')
                                            ->required()
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                // Calcular efectivo
                                                $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                           (floatval($get('bill_100') ?? 0)) * 100 +
                                                           (floatval($get('bill_50') ?? 0)) * 50 +
                                                           (floatval($get('bill_20') ?? 0)) * 20 +
                                                           (floatval($get('bill_10') ?? 0)) * 10 +
                                                           (floatval($get('coin_5') ?? 0)) * 5 +
                                                           (floatval($get('coin_2') ?? 0)) * 2 +
                                                           (floatval($get('coin_1') ?? 0)) * 1 +
                                                           (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                           (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                           (floatval($get('coin_010') ?? 0)) * 0.1;
                                                
                                                // Actualizar efectivo display
                                                $set('calculated_cash_display', $efectivo);
                                                
                                                // Calcular otros métodos
                                                $otros = (floatval($get('manual_yape') ?? 0)) +
                                                        (floatval($get('manual_plin') ?? 0)) +
                                                        (floatval($state ?? 0)) +
                                                        (floatval($get('manual_didi') ?? 0)) +
                                                        (floatval($get('manual_pedidos_ya') ?? 0)) +
                                                        (floatval($get('manual_otros') ?? 0));
                                                
                                                // Actualizar total manual
                                                $total = $efectivo + $otros;
                                                $set('calculated_total_manual', $total);
                                            }),
                                    ])
                                    ->columnSpan('full'),

                                // Comparativo de Didi
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('didi_sales_display')
                                            ->label('💻 Sistema: Didi Food')
                                            ->content(function ($record) {
                                                if (!$record) return 'S/ 0.00';
                                                $didiSales = $record->getSystemDidiSales();
                                                return 'S/ ' . number_format($didiSales, 2);
                                            })
                                            ->helperText('Ventas Didi Food registradas en el sistema'),
                                        Forms\Components\TextInput::make('manual_didi')
                                            ->label('👥 Manual: Didi Food')
                                            ->inputMode('decimal')
                                            ->default(0)
                                            ->rules(['required', 'numeric', 'min:0'])
                                            ->prefix('S/')
                                            ->placeholder('0.00')
                                            ->helperText('Didi Food contado manualmente')
                                            ->required()
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                           (floatval($get('bill_100') ?? 0)) * 100 +
                                                           (floatval($get('bill_50') ?? 0)) * 50 +
                                                           (floatval($get('bill_20') ?? 0)) * 20 +
                                                           (floatval($get('bill_10') ?? 0)) * 10 +
                                                           (floatval($get('coin_5') ?? 0)) * 5 +
                                                           (floatval($get('coin_2') ?? 0)) * 2 +
                                                           (floatval($get('coin_1') ?? 0)) * 1 +
                                                           (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                           (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                           (floatval($get('coin_010') ?? 0)) * 0.1;
                                                $set('calculated_cash_display', $efectivo);
                                                $otros = (floatval($get('manual_yape') ?? 0)) +
                                                        (floatval($get('manual_plin') ?? 0)) +
                                                        (floatval($get('manual_card') ?? 0)) +
                                                        (floatval($state ?? 0)) +
                                                        (floatval($get('manual_pedidos_ya') ?? 0)) +
                                                        (floatval($get('manual_otros') ?? 0));
                                                $total = $efectivo + $otros;
                                                $set('calculated_total_manual', $total);
                                            }),
                                    ])
                                    ->columnSpan('full'),

                                // Comparativo de PedidosYa
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('pedidos_ya_sales_display')
                                            ->label('💻 Sistema: PedidosYa')
                                            ->content(function ($record) {
                                                if (!$record) return 'S/ 0.00';
                                                $pedidosYaSales = $record->getSystemPedidosYaSales();
                                                return 'S/ ' . number_format($pedidosYaSales, 2);
                                            })
                                            ->helperText('Ventas PedidosYa registradas en el sistema'),
                                        Forms\Components\TextInput::make('manual_pedidos_ya')
                                            ->label('👥 Manual: PedidosYa')
                                            ->inputMode('decimal')
                                            ->default(0)
                                            ->rules(['required', 'numeric', 'min:0'])
                                            ->prefix('S/')
                                            ->placeholder('0.00')
                                            ->helperText('PedidosYa contado manualmente')
                                            ->required()
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                           (floatval($get('bill_100') ?? 0)) * 100 +
                                                           (floatval($get('bill_50') ?? 0)) * 50 +
                                                           (floatval($get('bill_20') ?? 0)) * 20 +
                                                           (floatval($get('bill_10') ?? 0)) * 10 +
                                                           (floatval($get('coin_5') ?? 0)) * 5 +
                                                           (floatval($get('coin_2') ?? 0)) * 2 +
                                                           (floatval($get('coin_1') ?? 0)) * 1 +
                                                           (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                           (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                           (floatval($get('coin_010') ?? 0)) * 0.1;
                                                $set('calculated_cash_display', $efectivo);
                                                $otros = (floatval($get('manual_yape') ?? 0)) +
                                                        (floatval($get('manual_plin') ?? 0)) +
                                                        (floatval($get('manual_card') ?? 0)) +
                                                        (floatval($get('manual_didi') ?? 0)) +
                                                        (floatval($state ?? 0)) +
                                                        (floatval($get('manual_otros') ?? 0));
                                                $total = $efectivo + $otros;
                                                $set('calculated_total_manual', $total);
                                            }),
                                    ])
                                    ->columnSpan('full'),


                                // Separador visual
                                Forms\Components\Placeholder::make('separator_2')
                                    ->content('──────────────────────────────────────')
                                    ->columnSpan('full'),

                                // Totales comparativos
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Placeholder::make('total_sistema_display')
                                            ->label('💻 TOTAL SISTEMA')
                                            ->content(function ($record) {
                                                if (!$record) return 'S/ 0.00';
                                                $totalSistema = $record->getSystemCashSales() +
                                                              $record->getSystemYapeSales() +
                                                              $record->getSystemPlinSales() +
                                                              $record->getSystemCardSales() +
                                                              $record->getSystemDidiSales() +
                                                              $record->getSystemPedidosYaSales() +
                                                              $record->getSystemBankTransferSales() +
                                                              $record->getSystemOtherDigitalWalletSales();
                                                return 'S/ ' . number_format($totalSistema, 2);
                                            })
                                            ->helperText('Total de todas las ventas registradas en el sistema')
                                            ->extraAttributes(['class' => 'font-bold text-lg text-blue-600']),
                                        Forms\Components\Placeholder::make('calculated_total_manual')
                                            ->label('👥 TOTAL MANUAL')
                                            ->content(function ($get) {
                                                // 👥 Manual: Efectivo (billetes y monedas)
                                                $efectivo = (floatval($get('bill_200') ?? 0)) * 200 +
                                                           (floatval($get('bill_100') ?? 0)) * 100 +
                                                           (floatval($get('bill_50') ?? 0)) * 50 +
                                                           (floatval($get('bill_20') ?? 0)) * 20 +
                                                           (floatval($get('bill_10') ?? 0)) * 10 +
                                                           (floatval($get('coin_5') ?? 0)) * 5 +
                                                           (floatval($get('coin_2') ?? 0)) * 2 +
                                                           (floatval($get('coin_1') ?? 0)) * 1 +
                                                           (floatval($get('coin_050') ?? 0)) * 0.5 +
                                                           (floatval($get('coin_020') ?? 0)) * 0.2 +
                                                           (floatval($get('coin_010') ?? 0)) * 0.1;
                                                
                                                // Métodos de pago digitales manuales
                                                $yape = floatval($get('manual_yape') ?? 0);
                                                $plin = floatval($get('manual_plin') ?? 0);
                                                $tarjetas = floatval($get('manual_card') ?? 0);
                                                $didi = floatval($get('manual_didi') ?? 0);
                                                $pedidosya = floatval($get('manual_pedidos_ya') ?? 0);
                                                
                                                // TOTAL MANUAL = Efectivo + Yape + Plin + Tarjetas + Didi + PedidosYa
                                                $total = $efectivo + $yape + $plin + $tarjetas + $didi + $pedidosya;
                                                return 'S/ ' . number_format($total, 2);
                                            })
                                            ->helperText('Total de todo lo contado manualmente')
                                            ->extraAttributes(['class' => 'font-bold text-lg text-green-600']),
                                    ])
                                    ->columnSpan('full'),
                            ];
                        } else {
                            return [
                                Forms\Components\Placeholder::make('sales_info')
                                    ->label('Información de Ventas')
                                    ->content('Esta información solo es visible para supervisores')
                                    ->columnSpan('full'),
                                Forms\Components\Placeholder::make('blind_closing_info')
                                    ->label('Cierre a Ciegas')
                                    ->content('Por favor, realice el conteo de efectivo sin conocer los montos esperados')
                                    ->columnSpan('full'),
                            ];
                        }
                    })
                    ->columns(1)
                    ->visible(fn ($record) => $record && $record->is_active),

                Forms\Components\Section::make('Resumen Final del Cierre')
                    ->description('Cálculos automáticos del cierre de caja')
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('monto_esperado')
                                    ->label('💰 Monto Esperado')
                                    ->content(function ($record) {
                                        if (!$record) return 'S/ 0.00';
                                        $esperado = $record->getSystemCashSales() +
                                                   $record->getSystemYapeSales() +
                                                   $record->getSystemPlinSales() +
                                                   $record->getSystemCardSales() +
                                                   $record->getSystemDidiSales() +
                                                   $record->getSystemPedidosYaSales() +
                                                   $record->getSystemBankTransferSales() +
                                                   $record->getSystemOtherDigitalWalletSales();
                                        return 'S/ ' . number_format($esperado, 2);
                                    })
                                    ->helperText('Sumatoria de todos los ingresos registrados en el sistema'),
                                    
                                Forms\Components\Placeholder::make('total_contado')
                                    ->label('👥 Total Manual')
                                    ->content(function ($record, $get) {
                                        // Calcular total de efectivo (billetes y monedas)
                                        $efectivo = floatval($get('bill_200') ?? 0) * 200 +
                                                   floatval($get('bill_100') ?? 0) * 100 +
                                                   floatval($get('bill_50') ?? 0) * 50 +
                                                   floatval($get('bill_20') ?? 0) * 20 +
                                                   floatval($get('bill_10') ?? 0) * 10 +
                                                   floatval($get('coin_5') ?? 0) * 5 +
                                                   floatval($get('coin_2') ?? 0) * 2 +
                                                   floatval($get('coin_1') ?? 0) * 1 +
                                                   floatval($get('coin_050') ?? 0) * 0.5 +
                                                   floatval($get('coin_020') ?? 0) * 0.2 +
                                                   floatval($get('coin_010') ?? 0) * 0.1;
                                        
                                        // Sumar otros métodos de pago
                                        $otros = floatval($get('manual_yape') ?? 0) +
                                                floatval($get('manual_plin') ?? 0) +
                                                floatval($get('manual_card') ?? 0) +
                                                floatval($get('manual_didi') ?? 0) +
                                                floatval($get('manual_pedidos_ya') ?? 0);
                                                
                                        $total = $efectivo + $otros;
                                        return 'S/ ' . number_format($total, 2);
                                    })
                                    ->helperText('Total de todo lo contado manualmente'),
                                    
                                Forms\Components\Placeholder::make('diferencia')
                                    ->label('⚖️ Diferencia')
                                    ->content(function ($record, $get) {
                                        if (!$record) return 'S/ 0.00';
                                        
                                        $esperado = $record->getSystemCashSales() +
                                                   $record->getSystemYapeSales() +
                                                   $record->getSystemPlinSales() +
                                                   $record->getSystemCardSales() +
                                                   $record->getSystemDidiSales() +
                                                   $record->getSystemPedidosYaSales() +
                                                   $record->getSystemBankTransferSales() +
                                                   $record->getSystemOtherDigitalWalletSales();
                                        
                                        // Calcular total contado
                                        $efectivo = floatval($get('bill_200') ?? 0) * 200 +
                                                   floatval($get('bill_100') ?? 0) * 100 +
                                                   floatval($get('bill_50') ?? 0) * 50 +
                                                   floatval($get('bill_20') ?? 0) * 20 +
                                                   floatval($get('bill_10') ?? 0) * 10 +
                                                   floatval($get('coin_5') ?? 0) * 5 +
                                                   floatval($get('coin_2') ?? 0) * 2 +
                                                   floatval($get('coin_1') ?? 0) * 1 +
                                                   floatval($get('coin_050') ?? 0) * 0.5 +
                                                   floatval($get('coin_020') ?? 0) * 0.2 +
                                                   floatval($get('coin_010') ?? 0) * 0.1;
                                        
                                        $otros = floatval($get('manual_yape') ?? 0) +
                                                floatval($get('manual_plin') ?? 0) +
                                                floatval($get('manual_card') ?? 0) +
                                                floatval($get('manual_didi') ?? 0) +
                                                floatval($get('manual_pedidos_ya') ?? 0);
                                                
                                        $totalContado = $efectivo + $otros;
                                        $diferencia = $totalContado - $esperado;
                                        
                                        $color = $diferencia == 0 ? 'success' : ($diferencia > 0 ? 'warning' : 'danger');
                                        $icon = $diferencia == 0 ? '✅' : ($diferencia > 0 ? '⚠️ Sobrante:' : '❌ Faltante:');
                                        
                                        return $icon . ' S/ ' . number_format($diferencia, 2);
                                    })
                                    ->helperText('Total Manual - Monto Esperado'),
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
                    ->label('🔄 Estado de Operación')
                    ->options([
                        1 => '🟢 Abierta',
                        0 => '🔴 Cerrada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null)
                    ->multiple(false),

                // Filtro de aprobación mejorado
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('✅ Estado de Aprobación')
                    ->options([
                        1 => '✅ Aprobada',
                        0 => '⏳ Pendiente/Rechazada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null)
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier'])),

                // Filtro de responsable
                Tables\Filters\SelectFilter::make('opened_by')
                    ->label('👤 Responsable')
                    ->relationship('openedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos los responsables'),

                // Filtro de fecha mejorado con presets
                Tables\Filters\Filter::make('opening_datetime')
                    ->label('📅 Período')
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
                            $indicators['desde'] = '📅 Desde: ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y');
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = '📅 Hasta: ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y');
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
                            $indicators['desde_cierre'] = 'Cierre desde ' . $data['desde_cierre'];
                        }

                        if ($data['hasta_cierre'] ?? null) {
                            $indicators['hasta_cierre'] = 'Cierre hasta ' . $data['hasta_cierre'];
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
                            $indicators['min_difference'] = 'Diferencia mín: S/ ' . $data['min_difference'];
                        }

                        if (isset($data['max_difference'])) {
                            $indicators['max_difference'] = 'Diferencia máx: S/ ' . $data['max_difference'];
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
                    ->url(fn ($record) => url('/admin/print-cash-register/' . $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_active && !$record->is_approved && auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_approved' => true, 'approval_notes' => 'Aprobado manualmente']);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('✅ Caja aprobada')
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
                    ->label('🏦 Abrir Nueva Caja')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->size('lg')
                    ->button()
                    ->visible(function () {
                        return !CashRegister::getOpenRegister();
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['opened_by'] = auth()->id();
                        $data['opening_datetime'] = now();
                        $data['is_active'] = true;
                        return $data;
                    })
                    ->successNotificationTitle('✅ Caja abierta correctamente')
                    ->after(function () {
                        redirect()->to('/admin/pos-interface');
                    }),

                // Reconciliación visible (principio KISS)
                Tables\Actions\Action::make('reconcile_all')
                    ->label('⚖️ Reconciliar Pendientes')
                    ->icon('heroicon-m-scale')
                    ->color('warning')
                    ->button()
                    ->visible(function () {
                        $user = auth()->user();
                        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier'])) {
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
                            ->title("✅ {$count} cajas reconciliadas")
                            ->send();
                    }),

                // Exportar (acción secundaria simple)
                Tables\Actions\Action::make('export_today')
                    ->label('📊 Exportar Hoy')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('info')
                    ->button()
                    ->action(function () {
                        \Filament\Notifications\Notification::make()
                            ->info()
                            ->title('📊 Exportando...')
                            ->body('Se está generando el reporte del día.')
                            ->send();
                    }),
            ])
            ->defaultSort('opening_datetime', 'desc')
            ->emptyStateIcon('heroicon-o-calculator')
            ->emptyStateHeading('🏦 No hay cajas registradas')
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
