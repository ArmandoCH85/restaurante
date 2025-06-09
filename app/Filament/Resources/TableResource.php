<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableResource\Pages;
use App\Models\Table as TableModel;
use App\Models\Floor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;

class TableResource extends Resource
{
    protected static ?string $model = TableModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Mesas';

    protected static ?string $modelLabel = 'Mesa';

    protected static ?string $pluralModelLabel = 'Mesas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('floor_id')
                    ->label('Piso')
                    ->options(Floor::where('status', 'active')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('number')
                    ->label('Número')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Select::make('shape')
                    ->label('Forma')
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
                    ->minValue(1),
                Forms\Components\Select::make('location')
                    ->label('Ubicación')
                    ->options([
                        'interior' => 'Interior',
                        'exterior' => 'Exterior',
                        'terraza' => 'Terraza',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'Mantenimiento',
                    ])
                    ->default('available')
                    ->required(),
            ]);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Número')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor.name')
                    ->label('Piso')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'Mantenimiento',
                        default => $state,
                    })
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        'maintenance' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'interior' => 'Interior',
                        'exterior' => 'Exterior',
                        'terraza' => 'Terraza',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('shape')
                    ->label('Forma')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'square' => 'Cuadrada',
                        'round' => 'Redonda',
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
                        'maintenance' => 'Mantenimiento',
                    ]),
                Tables\Filters\SelectFilter::make('location')
                    ->label('Ubicación')
                    ->options([
                        'interior' => 'Interior',
                        'exterior' => 'Exterior',
                        'terraza' => 'Terraza',
                    ]),
                Tables\Filters\SelectFilter::make('floor_id')
                    ->label('Piso')
                    ->relationship('floor', 'name'),
                Tables\Filters\SelectFilter::make('capacity')
                    ->label('Capacidad')
                    ->options([
                        '1-2' => '1-2 personas',
                        '3-4' => '3-4 personas',
                        '5-8' => '5-8 personas',
                        '9+' => '9+ personas',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            '1-2' => $query->whereBetween('capacity', [1, 2]),
                            '3-4' => $query->whereBetween('capacity', [3, 4]),
                            '5-8' => $query->whereBetween('capacity', [5, 8]),
                            '9+' => $query->where('capacity', '>=', 9),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('Ver QR')
                    ->icon('heroicon-o-qr-code')
                    ->action(fn ($record, Table\Actions\Action $action) => $action->getLivewire()->dispatch('showQrCode', [$record->id])),
                Tables\Actions\Action::make('Ver en POS')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn ($record): string => route('pos.index', ['table_id' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'available' => 'Disponible',
                                    'occupied' => 'Ocupada',
                                    'reserved' => 'Reservada',
                                    'maintenance' => 'Mantenimiento',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $records, array $data): void {
                            foreach ($records as $record) {
                                $table = Table::find($record);
                                if ($table) {
                                    $table->status = $data['status'];
                                    $table->save();
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información de Mesa')
                    ->schema([
                        Infolists\Components\TextEntry::make('number')
                            ->label('Número'),
                        Infolists\Components\TextEntry::make('floor.name')
                            ->label('Piso'),
                        Infolists\Components\TextEntry::make('capacity')
                            ->label('Capacidad')
                            ->suffix(' personas'),
                        Infolists\Components\TextEntry::make('location')
                            ->label('Ubicación')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'interior' => 'Interior',
                                'exterior' => 'Exterior',
                                'terraza' => 'Terraza',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('shape')
                            ->label('Forma')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'square' => 'Cuadrada',
                                'round' => 'Redonda',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('status')
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
                                'maintenance' => 'Mantenimiento',
                                default => $state,
                            }),
                    ])
                    ->columns(2),
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
            'index' => Pages\ListTables::route('/'),
            'create' => Pages\CreateTable::route('/create'),
            'edit' => Pages\EditTable::route('/{record}/edit'),
            'view' => Pages\ViewTable::route('/{record}'),
            // Página de mapa removida para evitar conflictos - usar /admin/mapa-mesas
        ];
    }
}
