<?php

declare(strict_types=1);

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use App\Models\IngredientStock;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ViewWarehouseInventory extends ViewRecord implements HasTable
{
    protected static string $resource = WarehouseInventoryResource::class;

    // Implementar interfaz HasTable para mostrar inventario
    use Tables\Concerns\InteractsWithTable;
    
    protected function getTableQuery(): Builder
    {
        return IngredientStock::query()
            ->where('warehouse_id', $this->record->id)
            ->where('status', '!=', IngredientStock::STATUS_EXPIRED)
            ->with(['ingredient']);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('ingredient.name')
                ->label('Ingrediente')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('ingredient.code')
                ->label('Código')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('quantity')
                ->label('Cantidad')
                ->numeric(decimalPlaces: 3)
                ->sortable(),

            Tables\Columns\TextColumn::make('ingredient.unit_of_measure')
                ->label('Unidad')
                ->sortable(),

            Tables\Columns\TextColumn::make('unit_cost')
                ->label('Costo Unitario')
                ->money('PEN')
                ->sortable(),

            Tables\Columns\TextColumn::make('total_value')
                ->label('Valor Total')
                ->money('PEN')
                ->state(fn (IngredientStock $record): float => 
                    round($record->quantity * $record->unit_cost, 2)
                )
                ->sortable(),

            Tables\Columns\TextColumn::make('expiry_date')
                ->label('Vencimiento')
                ->date()
                ->sortable()
                ->color(fn (IngredientStock $record): string => 
                    $record->expiry_date && $record->expiry_date->isPast() ? 'danger' : 
                    ($record->expiry_date && $record->expiry_date->diffInDays(now()) < 30 ? 'warning' : 'gray')
                ),

            Tables\Columns\TextColumn::make('status')
                ->label('Estado')
                ->badge()
                ->color(fn (string $state): string => 
                    match($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    }
                )
                ->formatStateUsing(fn (string $state): string =>
                    match($state) {
                        'available' => 'Disponible',
                        'reserved' => 'Reservado',
                        'expired' => 'Vencido',
                        default => $state,
                    }
                ),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->label('Estado')
                ->options([
                    'available' => 'Disponible',
                    'reserved' => 'Reservado',
                    'expired' => 'Vencido',
                ]),

            Tables\Filters\Filter::make('expiring_soon')
                ->label('Por Vencer')
                ->query(function (Builder $query) {
                    return $query->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '>', now())
                        ->whereDate('expiry_date', '<=', now()->addDays(30));
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('transfer')
                ->label('Trasladar')
                ->icon('heroicon-o-arrows-right-left')
                ->color('primary')
                ->form([
                    Forms\Components\Hidden::make('ingredient_id')
                        ->dehydrated()
                        ->default(fn (IngredientStock $record) => $record->ingredient_id),
                        
                    Forms\Components\TextInput::make('ingredient_name')
                        ->label('Ingrediente')
                        ->default(fn (IngredientStock $record) => $record->ingredient->name)
                        ->disabled(),
                        
                    Forms\Components\TextInput::make('available_quantity')
                        ->label('Cantidad disponible')
                        ->numeric()
                        ->default(fn (IngredientStock $record) => $record->quantity)
                        ->disabled(),
                        
                    Forms\Components\TextInput::make('unit_of_measure')
                        ->label('Unidad')
                        ->default(fn (IngredientStock $record) => $record->ingredient->unit_of_measure)
                        ->disabled(),
                        
                    Forms\Components\Select::make('destination_warehouse_id')
                        ->label('Almacén de Destino')
                        ->options(function (): array {
                            return \App\Models\Warehouse::where('id', '!=', $this->record->id)
                                ->where('active', true)
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->required()
                        ->searchable(),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Cantidad a Trasladar')
                        ->required()
                        ->numeric()
                        ->minValue(0.001)
                        ->maxValue(function (IngredientStock $record): float {
                            return $record->quantity;
                        })
                        ->step(0.001)
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('max_quantity')
                                ->icon('heroicon-m-arrow-up-circle')
                                ->action(function (Forms\Components\Actions\Action $action, Forms\Get $get, Forms\Set $set) {
                                    $set('quantity', $get('available_quantity'));
                                })
                        )
                        ->helperText(function (IngredientStock $record): string {
                            return "Disponible: {$record->quantity} {$record->ingredient->unit_of_measure}";
                        }),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->maxLength(1000),
                ])
                ->action(function (array $data, IngredientStock $record): void {
                    // Validar que haya suficiente stock
                    if ($data['quantity'] > $record->quantity) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body("No hay suficiente stock disponible. Stock actual: {$record->quantity}")
                            ->danger()
                            ->send();
                        return;
                    }

                    // Verificar almacén destino
                    $destinationWarehouse = \App\Models\Warehouse::find($data['destination_warehouse_id']);
                    if (!$destinationWarehouse) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body('El almacén de destino no existe')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        // Iniciar transacción
                        \DB::beginTransaction();

                        // Reducir stock en almacén origen
                        $record->quantity -= $data['quantity'];
                        $record->save();

                        // Buscar o crear registro en almacén destino
                        $destinationStock = \App\Models\IngredientStock::firstOrCreate([
                            'ingredient_id' => $record->ingredient_id,
                            'warehouse_id' => $data['destination_warehouse_id'],
                            'status' => \App\Models\IngredientStock::STATUS_AVAILABLE,
                        ], [
                            'quantity' => 0,
                            'unit_cost' => $record->unit_cost,
                            'expiry_date' => $record->expiry_date,
                        ]);

                        // Aumentar stock en almacén destino
                        $destinationStock->quantity += $data['quantity'];
                        $destinationStock->save();

                        // Registrar movimientos
                        \App\Models\InventoryMovement::create([
                            'product_id' => $record->ingredient_id,
                            'warehouse_id' => $record->warehouse_id,
                            'movement_type' => \App\Models\InventoryMovement::TYPE_ADJUSTMENT,
                            'quantity' => -$data['quantity'],
                            'unit_cost' => $record->unit_cost,
                            'reference_document' => 'Traslado entre almacenes',
                            'created_by' => \Illuminate\Support\Facades\Auth::id(),
                            'notes' => "Traslado hacia almacén: {$destinationWarehouse->name}. " . ($data['notes'] ?? ''),
                        ]);

                        \App\Models\InventoryMovement::create([
                            'product_id' => $record->ingredient_id,
                            'warehouse_id' => $data['destination_warehouse_id'],
                            'movement_type' => \App\Models\InventoryMovement::TYPE_ADJUSTMENT,
                            'quantity' => $data['quantity'],
                            'unit_cost' => $record->unit_cost,
                            'reference_document' => 'Traslado entre almacenes',
                            'created_by' => \Illuminate\Support\Facades\Auth::id(),
                            'notes' => "Traslado desde almacén: {$this->record->name}. " . ($data['notes'] ?? ''),
                        ]);

                        \DB::commit();

                        \Filament\Notifications\Notification::make()
                            ->title('Traslado Exitoso')
                            ->body("Se han trasladado {$data['quantity']} unidades al almacén {$destinationWarehouse->name}")
                            ->success()
                            ->send();

                        $this->refreshTable();
                    } catch (\Exception $e) {
                        \DB::rollBack();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body('Ocurrió un error al trasladar el stock: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn (IngredientStock $record): bool => 
                    $record->quantity > 0 && $record->status === \App\Models\IngredientStock::STATUS_AVAILABLE
                ),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => route('filament.admin.resources.inventario.por-almacen.index')),
        ];
    }
    
    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('bulk_transfer')
                ->label('Traslado Masivo')
                ->icon('heroicon-o-arrows-right-left')
                ->form([
                    Forms\Components\Select::make('ingredient_id')
                        ->label('Ingrediente')
                        ->options(function (): array {
                            return \App\Models\Ingredient::whereHas('stocks', function ($query) {
                                $query->where('warehouse_id', $this->record->id)
                                    ->where('quantity', '>', 0)
                                    ->where('status', \App\Models\IngredientStock::STATUS_AVAILABLE);
                            })->pluck('name', 'id')->toArray();
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            $stock = \App\Models\IngredientStock::where('warehouse_id', $this->record->id)
                                ->where('ingredient_id', $state)
                                ->where('status', \App\Models\IngredientStock::STATUS_AVAILABLE)
                                ->first();
                            
                            if ($stock) {
                                $set('available_quantity', $stock->quantity);
                                $set('unit_of_measure', $stock->ingredient->unit_of_measure);
                                $set('unit_cost', $stock->unit_cost);
                            } else {
                                $set('available_quantity', 0);
                                $set('unit_of_measure', '');
                                $set('unit_cost', 0);
                            }
                        }),

                    Forms\Components\TextInput::make('available_quantity')
                        ->label('Cantidad Disponible')
                        ->disabled(),
                        
                    Forms\Components\TextInput::make('unit_of_measure')
                        ->label('Unidad')
                        ->disabled(),
                        
                    Forms\Components\TextInput::make('unit_cost')
                        ->label('Costo Unitario')
                        ->disabled(),

                    Forms\Components\Select::make('destination_warehouse_id')
                        ->label('Almacén de Destino')
                        ->options(function (): array {
                            return \App\Models\Warehouse::where('id', '!=', $this->record->id)
                                ->where('active', true)
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Cantidad a Trasladar')
                        ->required()
                        ->numeric()
                        ->minValue(0.001)
                        ->step(0.001)
                        ->reactive()
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('max_quantity')
                                ->label('Máximo')
                                ->icon('heroicon-m-arrow-up-circle')
                                ->action(function (Forms\Components\Actions\Action $action, Forms\Get $get, Forms\Set $set) {
                                    $set('quantity', $get('available_quantity'));
                                })
                        )
                        ->helperText(function (Forms\Get $get): string {
                            $qty = $get('available_quantity') ?? 0;
                            $unit = $get('unit_of_measure') ?? '';
                            return "Disponible: {$qty} {$unit}";
                        }),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->maxLength(1000),
                ])
                ->action(function (array $data): void {
                    // Validar disponibilidad
                    $originStock = \App\Models\IngredientStock::where('warehouse_id', $this->record->id)
                        ->where('ingredient_id', $data['ingredient_id'])
                        ->where('status', \App\Models\IngredientStock::STATUS_AVAILABLE)
                        ->first();

                    if (!$originStock) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body('No se encontró stock disponible para este ingrediente')
                            ->danger()
                            ->send();
                        return;
                    }

                    if ($originStock->quantity < $data['quantity']) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body("No hay suficiente stock disponible. Stock actual: {$originStock->quantity}")
                            ->danger()
                            ->send();
                        return;
                    }

                    // Verificar almacén destino
                    $destinationWarehouse = \App\Models\Warehouse::find($data['destination_warehouse_id']);
                    if (!$destinationWarehouse) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body('El almacén de destino no existe')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        // Iniciar transacción
                        \DB::beginTransaction();

                        // Reducir stock en almacén origen
                        $originStock->quantity -= $data['quantity'];
                        $originStock->save();

                        // Buscar o crear registro en almacén destino
                        $destinationStock = \App\Models\IngredientStock::firstOrCreate([
                            'ingredient_id' => $data['ingredient_id'],
                            'warehouse_id' => $data['destination_warehouse_id'],
                            'status' => \App\Models\IngredientStock::STATUS_AVAILABLE,
                        ], [
                            'quantity' => 0,
                            'unit_cost' => $originStock->unit_cost,
                            'expiry_date' => $originStock->expiry_date,
                        ]);

                        // Aumentar stock en almacén destino
                        $destinationStock->quantity += $data['quantity'];
                        $destinationStock->save();

                        // Registrar movimientos de inventario
                        \App\Models\InventoryMovement::create([
                            'product_id' => $data['ingredient_id'],
                            'warehouse_id' => $this->record->id,
                            'movement_type' => \App\Models\InventoryMovement::TYPE_ADJUSTMENT,
                            'quantity' => -$data['quantity'],
                            'unit_cost' => $originStock->unit_cost,
                            'reference_document' => 'Traslado entre almacenes',
                            'created_by' => \Illuminate\Support\Facades\Auth::id(),
                            'notes' => "Traslado hacia almacén: {$destinationWarehouse->name}. " . ($data['notes'] ?? ''),
                        ]);

                        \App\Models\InventoryMovement::create([
                            'product_id' => $data['ingredient_id'],
                            'warehouse_id' => $data['destination_warehouse_id'],
                            'movement_type' => \App\Models\InventoryMovement::TYPE_ADJUSTMENT,
                            'quantity' => $data['quantity'],
                            'unit_cost' => $originStock->unit_cost,
                            'reference_document' => 'Traslado entre almacenes',
                            'created_by' => \Illuminate\Support\Facades\Auth::id(),
                            'notes' => "Traslado desde almacén: {$this->record->name}. " . ($data['notes'] ?? ''),
                        ]);

                        \DB::commit();

                        \Filament\Notifications\Notification::make()
                            ->title('Traslado Exitoso')
                            ->body("Se han trasladado {$data['quantity']} unidades al almacén {$destinationWarehouse->name}")
                            ->success()
                            ->send();

                        $this->refreshTable();
                    } catch (\Exception $e) {
                        \DB::rollBack();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body('Ocurrió un error al trasladar el stock: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
