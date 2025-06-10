<?php

namespace App\Filament\Resources;

use App\Models\Table as TableModel;
use App\Models\Floor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SimpleTableResource extends Resource
{
    protected static ?string $model = TableModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = '⚙️ Configuración';

    protected static ?string $navigationLabel = 'Mesas';

    protected static ?string $modelLabel = 'Mesa';

    protected static ?string $pluralModelLabel = 'Mesas';

    protected static ?string $slug = 'tables';

    protected static ?int $navigationSort = 3;

    /**
     * OPTIMIZACIÓN: Agregar eager loading para evitar N+1 queries
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['floor']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('floor_id')
                    ->label('Piso')
                    ->options(Floor::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->helperText('Seleccione el piso donde se encuentra la mesa'),

                Forms\Components\TextInput::make('number')
                    ->label('Número de Mesa')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(999)
                    ->placeholder('1-999')
                    ->prefix('#')
                    ->helperText('Identificador único de la mesa'),

                Forms\Components\Select::make('shape')
                    ->label('Forma de la mesa')
                    ->options([
                        'square' => 'Cuadrada',
                        'round' => 'Redonda',
                    ])
                    ->default('square')
                    ->required(),

                Forms\Components\TextInput::make('capacity')
                    ->label('Capacidad')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->placeholder('1-20')
                    ->suffix('personas')
                    ->helperText('Número máximo de comensales'),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'En Mantenimiento',
                    ])
                    ->required()
                    ->default('available'),

                Forms\Components\Select::make('location')
                    ->label('Ubicación')
                    ->options([
                        'interior' => 'Interior',
                        'exterior' => 'Terraza',
                        'bar' => 'Barra',
                        'private' => 'Sala Privada',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('qr_code')
                    ->label('Código QR')
                    ->placeholder('URL del código QR')
                    ->helperText('Enlace al código QR de la mesa'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('floor.name')
                    ->label('Piso')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('number')
                    ->label('Mesa #')
                    ->formatStateUsing(fn ($state) => "#{$state}")
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('shape')
                    ->label('Forma')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'square' => 'Cuadrada',
                        'round' => 'Redonda',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacidad')
                    ->formatStateUsing(fn ($state) => "{$state} personas"),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        'maintenance' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'En Mantenimiento',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'interior' => 'Interior',
                        'exterior' => 'Terraza',
                        'bar' => 'Barra',
                        'private' => 'Sala Privada',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'En Mantenimiento',
                    ]),
                Tables\Filters\SelectFilter::make('floor_id')
                    ->label('Piso')
                    ->relationship('floor', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('number', 'asc');
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
            'index' => \App\Filament\Resources\SimpleTableResource\Pages\ListSimpleTables::route('/'),
            'create' => \App\Filament\Resources\SimpleTableResource\Pages\CreateSimpleTable::route('/create'),
            'edit' => \App\Filament\Resources\SimpleTableResource\Pages\EditSimpleTable::route('/{record}/edit'),
        ];
    }
}
