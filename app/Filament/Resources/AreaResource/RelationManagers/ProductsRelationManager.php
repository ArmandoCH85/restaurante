<?php

namespace App\Filament\Resources\AreaResource\RelationManagers;

use App\Models\Area;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Productos del area';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Precio')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\IconColumn::make('available')
                    ->label('Disponible')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('available')
                    ->label('Disponibilidad'),
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Tipo')
                    ->options([
                        Product::TYPE_INGREDIENT => 'Ingrediente',
                        Product::TYPE_SALE_ITEM => 'Articulo de venta',
                        Product::TYPE_BOTH => 'Ambos',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('assign_products')
                    ->label('Asignar productos')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->form([
                        Forms\Components\Toggle::make('allow_reassign')
                            ->label('Permitir reasignar productos con area')
                            ->default(false)
                            ->live(),

                        Forms\Components\Select::make('product_ids')
                            ->label('Productos')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(fn (Get $get): array => $this->getAssignableProductsOptions((bool) $get('allow_reassign')))
                            ->helperText(function (Get $get): string {
                                return $get('allow_reassign')
                                    ? 'Incluye productos con area actual. Se mostrara su area para transparencia.'
                                    : 'Solo se listan productos sin area asignada.';
                            }),

                        Forms\Components\Placeholder::make('selected_summary')
                            ->label('Resumen')
                            ->content(fn (Get $get): string => 'Seleccionaste '.count($get('product_ids') ?? []).' productos.'),

                        Forms\Components\Checkbox::make('confirm_reassignment')
                            ->label('Confirmo mover productos desde su area actual')
                            ->visible(fn (Get $get): bool => (bool) $get('allow_reassign'))
                            ->required(fn (Get $get): bool => (bool) $get('allow_reassign')),
                    ])
                    ->action(function (array $data): void {
                        $targetArea = $this->getOwnerRecord();
                        $allowReassign = (bool) ($data['allow_reassign'] ?? false);
                        $productIds = $data['product_ids'] ?? [];

                        if (empty($productIds)) {
                            Notification::make()
                                ->title('No seleccionaste productos')
                                ->warning()
                                ->send();

                            return;
                        }

                        $products = Product::query()
                            ->whereIn('id', $productIds)
                            ->get();

                        $assignedCount = 0;
                        $omitted = [];

                        foreach ($products as $product) {
                            if (! $allowReassign && ! is_null($product->area_id) && $product->area_id !== $targetArea->id) {
                                $omitted[] = $product->name;

                                continue;
                            }

                            if ($product->area_id !== $targetArea->id) {
                                $product->area_id = $targetArea->id;
                                $product->save();
                                $assignedCount++;
                            }
                        }

                        if (empty($omitted)) {
                            Notification::make()
                                ->title("Se asignaron {$assignedCount} productos al area {$targetArea->name}.")
                                ->success()
                                ->send();
                        } else {
                            $omittedCount = count($omitted);
                            Notification::make()
                                ->title("{$assignedCount} asignados, {$omittedCount} omitidos por ya pertenecer a otra area.")
                                ->body('Omitidos: '.implode(', ', array_slice($omitted, 0, 8)))
                                ->warning()
                                ->send();
                        }

                        $this->resetTable();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Asignar productos')
                    ->modalDescription('Asigna uno o varios productos al area actual.')
                    ->modalSubmitActionLabel('Asignar')
                    ->modalWidth('2xl'),
            ])
            ->actions([
                Tables\Actions\Action::make('remove_from_area')
                    ->label('Quitar del area')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Product $record): void {
                        $record->update(['area_id' => null]);

                        Notification::make()
                            ->title('Producto retirado del area.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('move_to_area')
                    ->label('Mover a otra area')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('new_area_id')
                            ->label('Nueva area')
                            ->required()
                            ->options(function (): array {
                                return Area::query()
                                    ->where('id', '!=', $this->getOwnerRecord()->id)
                                    ->where('active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Product $record, array $data): void {
                        $newAreaId = (int) $data['new_area_id'];
                        $record->update(['area_id' => $newAreaId]);

                        Notification::make()
                            ->title('Producto movido de area correctamente.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('name');
    }

    private function getAssignableProductsOptions(bool $allowReassign): array
    {
        $query = Product::query()
            ->with('area')
            ->orderBy('name');

        if (! $allowReassign) {
            $query->whereNull('area_id');
        }

        return $query
            ->get()
            ->mapWithKeys(function (Product $product) use ($allowReassign): array {
                $label = "{$product->code} - {$product->name}";

                if ($allowReassign && ! is_null($product->area_id)) {
                    $areaName = $product->area?->name ?? 'Sin area';
                    $label .= " (Area actual: {$areaName})";
                }

                return [$product->id => $label];
            })
            ->toArray();
    }
}
