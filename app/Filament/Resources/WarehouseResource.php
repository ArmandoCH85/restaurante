<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Filament\Resources\WarehouseResource\RelationManagers;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Almacenes';

    protected static ?string $modelLabel = 'Almacén';

    protected static ?string $pluralModelLabel = 'Almacenes';

    protected static ?string $slug = 'inventario/almacenes';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre'),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->label('Código'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->label('Descripción'),
                    ])->columns(2),

                Forms\Components\Section::make('Ubicación y Estado')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255)
                            ->label('Ubicación'),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Almacén Principal')
                            ->helperText('Solo puede haber un almacén principal'),
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->label('Ubicación'),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Principal'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creado'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Actualizado'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('active')
                    ->options([
                        true => 'Activos',
                        false => 'Inactivos',
                    ])
                    ->label('Estado'),
                Tables\Filters\SelectFilter::make('is_default')
                    ->options([
                        true => 'Principal',
                        false => 'Secundario',
                    ])
                    ->label('Tipo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryMovementsRelationManager::class,
            RelationManagers\IngredientStocksRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
