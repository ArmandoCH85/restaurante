<?php

namespace App\Filament\Resources;

use App\Models\Floor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SimpleFloorResource extends Resource
{
    protected static ?string $model = Floor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Pisos';

    protected static ?string $modelLabel = 'Piso';

    protected static ?string $pluralModelLabel = 'Pisos';

    protected static ?string $slug = 'floors';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Ej: Primer Piso'),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->nullable()
                    ->maxLength(255)
                    ->placeholder('Descripción del piso y sus características'),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'maintenance' => 'En Mantenimiento',
                        'closed' => 'Cerrado',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activo',
                        'maintenance' => 'En Mantenimiento',
                        'closed' => 'Cerrado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'maintenance' => 'warning',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'maintenance' => 'En Mantenimiento',
                        'closed' => 'Cerrado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => \App\Filament\Resources\SimpleFloorResource\Pages\ListSimpleFloors::route('/'),
            'create' => \App\Filament\Resources\SimpleFloorResource\Pages\CreateSimpleFloor::route('/create'),
            'edit' => \App\Filament\Resources\SimpleFloorResource\Pages\EditSimpleFloor::route('/{record}/edit'),
        ];
    }
}
