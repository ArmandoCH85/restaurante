<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaSistemaAnteriorResource\Pages;
use App\Models\VentaSistemaAnterior;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VentaSistemaAnteriorResource extends Resource
{
    protected static ?string $model = VentaSistemaAnterior::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Ventas Sistema Anterior';

    protected static ?string $modelLabel = 'Venta Sistema Anterior';

    protected static ?string $pluralModelLabel = 'Ventas Sistema Anterior';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_venta')
                    ->label('Fecha Venta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('caja')
                    ->label('Caja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cliente')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('documento')
                    ->label('Documento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('canal_venta')
                    ->label('Canal Venta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_pago')
                    ->label('Tipo Pago')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Importación')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('fecha_venta')
                    ->form([
                        
                    ])
                    ->query(function ($query, array $data) {
                        return $query;
                    }),
            ])
            ->actions([
                
            ])
            ->bulkActions([
                
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentaSistemaAnteriores::route('/'),
            'view' => Pages\ViewVentaSistemaAnterior::route('/{record}'),
        ];
    }
}