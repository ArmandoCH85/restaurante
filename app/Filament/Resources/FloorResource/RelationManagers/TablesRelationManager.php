<?php

namespace App\Filament\Resources\FloorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Table as TableModel;

class TablesRelationManager extends RelationManager
{
    protected static string $relationship = 'tables';

    protected static ?string $recordTitleAttribute = 'number';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->label('Número de Mesa')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(999)
                    ->placeholder('1-999')
                    ->prefix('#'),

                Forms\Components\Radio::make('shape')
                    ->label('Forma de la mesa')
                    ->options([
                        'square' => '⬜ Cuadrada',
                        'round' => '⭕ Redonda',
                    ])
                    ->descriptions([
                        'square' => 'Mesa con forma cuadrada o rectangular',
                        'round' => 'Mesa con forma circular u ovalada',
                    ])
                    ->inline()
                    ->default('square')
                    ->required(),

                Forms\Components\TextInput::make('capacity')
                    ->label('Capacidad')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->placeholder('1-20')
                    ->suffix('personas'),

                Forms\Components\Select::make('location')
                    ->label('Ubicación')
                    ->options([
                        'interior' => 'Interior',
                        'terraza' => 'Terraza',
                        'vip' => 'Zona VIP',
                        'barra' => 'Barra',
                    ])
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'En Mantenimiento',
                    ])
                    ->default('available')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Número')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('shape')
                    ->label('Forma')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'square' => '⬜ Cuadrada',
                        'round' => '⭕ Redonda',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacidad')
                    ->suffix(' personas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'interior' => 'Interior',
                        'terraza' => 'Terraza',
                        'vip' => 'Zona VIP',
                        'barra' => 'Barra',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'En Mantenimiento',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        'maintenance' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
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

                Tables\Filters\SelectFilter::make('shape')
                    ->label('Forma')
                    ->options([
                        'square' => 'Cuadrada',
                        'round' => 'Redonda',
                    ]),

                Tables\Filters\SelectFilter::make('location')
                    ->label('Ubicación')
                    ->options([
                        'interior' => 'Interior',
                        'terraza' => 'Terraza',
                        'vip' => 'Zona VIP',
                        'barra' => 'Barra',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
