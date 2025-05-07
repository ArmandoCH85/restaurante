<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableResource\Pages;
use App\Filament\Resources\TableResource\RelationManagers;
use App\Models\Table as TableModel;
use App\Models\Floor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;

class TableResource extends Resource
{
    protected static ?string $model = TableModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Mantenimiento';

    protected static ?string $navigationLabel = 'Mesas';

    protected static ?string $modelLabel = 'Mesa';

    protected static ?string $pluralModelLabel = 'Mesas';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Información General')
                            ->description('Datos básicos de la mesa')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Select::make('floor_id')
                                    ->label('Piso')
                                    ->options(Floor::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(50),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->nullable(),
                                    ])
                                    ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                        return $action
                                            ->modalHeading('Crear nuevo piso')
                                            ->modalWidth('md');
                                    })
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
                                    ->helperText('Identificador único de la mesa')
                                    ->autofocus(),

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
                                    ->required()
                                    ->helperText('Seleccione la forma de la mesa'),

                                Forms\Components\TextInput::make('capacity')
                                    ->label('Capacidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->placeholder('1-20')
                                    ->suffix('personas')
                                    ->helperText('Número máximo de comensales'),
                            ])
                            ->columnSpan(2),

                        Forms\Components\Section::make('Estado y Ubicación')
                            ->description('Configuración de disponibilidad')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'available' => 'Disponible',
                                        'occupied' => 'Ocupada',
                                        'reserved' => 'Reservada',
                                        'maintenance' => 'En Mantenimiento',
                                    ])
                                    ->required()
                                    ->default('available')
                                    ->helperText('Estado actual de la mesa')
                                    ->reactive(),
                                Forms\Components\Select::make('location')
                                    ->label('Ubicación')
                                    ->options([
                                        'interior' => 'Interior',
                                        'exterior' => 'Terraza',
                                        'bar' => 'Barra',
                                        'private' => 'Sala Privada',
                                    ])
                                    ->required()
                                    ->helperText('Zona donde se encuentra la mesa'),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('Información QR')
                            ->description('Código QR de la mesa')
                            ->icon('heroicon-o-qr-code')
                            ->schema([
                                Forms\Components\TextInput::make('qr_code')
                                    ->label('Código QR')
                                    ->placeholder('URL del código QR')
                                    ->helperText('Enlace al código QR de la mesa')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
                '2xl' => 4,
            ])
            ->columns([
                Tables\Columns\TextColumn::make('floor.name')
                    ->label('Piso')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('shape')
                    ->label('Forma')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'square' => '⬜ Cuadrada',
                        'round' => '⭕ Redonda',
                        default => $state,
                    })
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('number')
                    ->label('Mesa #')
                    ->formatStateUsing(fn ($state) => "#{$state}")
                    ->alignCenter()
                    ->weight('bold')
                    ->size('lg')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->alignCenter()
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
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'interior' => 'info',
                        'exterior' => 'success',
                        'bar' => 'warning',
                        'private' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'interior' => 'Interior',
                        'exterior' => 'Terraza',
                        'bar' => 'Barra',
                        'private' => 'Sala Privada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacidad')
                    ->formatStateUsing(fn ($state) => "{$state} personas")
                    ->alignCenter()
                    ->color(fn ($state) => $state >= 8 ? 'success' : ($state >= 4 ? 'info' : 'gray')),
                Tables\Columns\TextColumn::make('qr_code')
                    ->label('QR')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->alignCenter()
                    ->size('sm')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->multiple()
                    ->preload()
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Ocupada',
                        'reserved' => 'Reservada',
                        'maintenance' => 'En Mantenimiento',
                    ])
                    ->indicator('Estado'),
                Tables\Filters\SelectFilter::make('floor_id')
                    ->label('Piso')
                    ->relationship('floor', 'name')
                    ->preload()
                    ->searchable()
                    ->indicator('Piso'),

                Tables\Filters\SelectFilter::make('shape')
                    ->label('Forma')
                    ->options([
                        'square' => 'Cuadrada',
                        'round' => 'Redonda',
                    ])
                    ->indicator('Forma'),

                Tables\Filters\SelectFilter::make('location')
                    ->label('Ubicación')
                    ->multiple()
                    ->preload()
                    ->options([
                        'interior' => 'Interior',
                        'exterior' => 'Terraza',
                        'bar' => 'Barra',
                        'private' => 'Sala Privada',
                    ])
                    ->indicator('Ubicación'),
                Tables\Filters\Filter::make('capacity')
                    ->form([
                        Forms\Components\TextInput::make('min_capacity')
                            ->label('Capacidad mínima')
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\TextInput::make('max_capacity')
                            ->label('Capacidad máxima')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_capacity'],
                                fn (Builder $query, $min): Builder => $query->where('capacity', '>=', $min),
                            )
                            ->when(
                                $data['max_capacity'],
                                fn (Builder $query, $max): Builder => $query->where('capacity', '<=', $max),
                            );
                    })
                    ->indicator('Capacidad'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->tooltip('Editar mesa'),
                    Tables\Actions\Action::make('cambiar_estado')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Nuevo estado')
                                ->options([
                                    'available' => 'Disponible',
                                    'occupied' => 'Ocupada',
                                    'reserved' => 'Reservada',
                                    'maintenance' => 'En Mantenimiento',
                                ])
                                ->required(),
                        ])
                        ->action(function (TableModel $record, array $data): void {
                            $record->status = $data['status'];
                            $record->save();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->tooltip('Eliminar mesa'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('cambiar_estado_masivo')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Nuevo estado')
                                ->options([
                                    'available' => 'Disponible',
                                    'occupied' => 'Ocupada',
                                    'reserved' => 'Reservada',
                                    'maintenance' => 'En Mantenimiento',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->status = $data['status'];
                                $record->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('number', 'asc')
            ->poll('10s')
            ->emptyStateIcon('heroicon-o-table-cells')
            ->emptyStateHeading('No hay mesas')
            ->emptyStateDescription('Crea tu primera mesa para empezar a gestionar tu restaurante.')
            ->emptyStateActions([
                Tables\Actions\Action::make('crear')
                    ->label('Crear mesa')
                    ->url(route('filament.admin.resources.tables.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
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
        ];
    }
}
