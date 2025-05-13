<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyConfigResource\Pages;
use App\Models\AppSetting;
use App\Models\CompanyConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CompanyConfigResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Datos de la Empresa';

    protected static ?string $modelLabel = 'Configuración de Empresa';

    protected static ?string $pluralModelLabel = 'Configuraciones de Empresa';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'configuracion/empresa';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('tab', 'Empresa');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Empresa')
                    ->description('Datos generales de la empresa emisora de comprobantes')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label(function ($record) {
                                return match ($record->key) {
                                    'ruc' => 'RUC',
                                    'razon_social' => 'Razón Social',
                                    'nombre_comercial' => 'Nombre Comercial',
                                    'direccion' => 'Dirección',
                                    'ubigeo' => 'Código Ubigeo',
                                    'distrito' => 'Distrito',
                                    'provincia' => 'Provincia',
                                    'departamento' => 'Departamento',
                                    'codigo_pais' => 'Código de País',
                                    'telefono' => 'Teléfono',
                                    'email' => 'Email',
                                    default => ucfirst(str_replace('_', ' ', $record->key)),
                                };
                            })
                            ->helperText(function ($record) {
                                return match ($record->key) {
                                    'ruc' => 'Número de RUC de la empresa (11 dígitos)',
                                    'ubigeo' => 'Código Ubigeo según SUNAT (6 dígitos)',
                                    'codigo_pais' => 'Código ISO del país (PE para Perú)',
                                    'email' => 'Email para facturación electrónica',
                                    default => null,
                                };
                            })
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->disabled(function ($record) {
                                // Deshabilitar la edición de campos críticos
                                return in_array($record->key, ['ruc']);
                            }),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Información Adicional')
                    ->description('Esta configuración se utiliza para la emisión de comprobantes electrónicos')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('info')
                            ->content(new HtmlString('
                                <div class="text-sm text-gray-500">
                                    <p>Esta información es utilizada para la emisión de comprobantes electrónicos ante SUNAT.</p>
                                    <p class="mt-2">Asegúrese de que los datos sean correctos para evitar problemas con la validación de comprobantes.</p>
                                </div>
                            ')),

                        Forms\Components\Placeholder::make('default')
                            ->label('Valor por Defecto')
                            ->content(function ($record) {
                                return $record->default ?? 'No definido';
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),
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
            'index' => Pages\ManageCompanyConfig::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Configuración')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'ruc' => 'RUC',
                            'razon_social' => 'Razón Social',
                            'nombre_comercial' => 'Nombre Comercial',
                            'direccion' => 'Dirección',
                            'ubigeo' => 'Código Ubigeo',
                            'distrito' => 'Distrito',
                            'provincia' => 'Provincia',
                            'departamento' => 'Departamento',
                            'codigo_pais' => 'Código de País',
                            'telefono' => 'Teléfono',
                            'email' => 'Email',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
