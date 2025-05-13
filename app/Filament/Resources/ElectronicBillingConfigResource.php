<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ElectronicBillingConfigResource\Pages;
use App\Models\AppSetting;
use App\Models\ElectronicBillingConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\HtmlString;

class ElectronicBillingConfigResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Facturación Electrónica';

    protected static ?string $modelLabel = 'Configuración de Facturación';

    protected static ?string $pluralModelLabel = 'Configuraciones de Facturación';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'configuracion/facturacion-electronica';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('tab', 'FacturacionElectronica');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Facturación Electrónica')
                    ->description('Configuración para la conexión con SUNAT')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label(function ($record) {
                                return match ($record->key) {
                                    'soap_type' => 'Tipo de Conexión SOAP',
                                    'environment' => 'Entorno',
                                    'sol_user' => 'Usuario SOL',
                                    'sol_password' => 'Contraseña SOL',
                                    'certificate_path' => 'Ruta del Certificado',
                                    'certificate_password' => 'Contraseña del Certificado',
                                    'send_automatically' => 'Enviar Automáticamente',
                                    'generate_pdf' => 'Generar PDF',
                                    'igv_percent' => 'Porcentaje de IGV',
                                    default => ucfirst(str_replace('_', ' ', $record->key)),
                                };
                            })
                            ->helperText(function ($record) {
                                return match ($record->key) {
                                    'soap_type' => 'Tipo de conexión SOAP (sunat o ose)',
                                    'environment' => 'Entorno (beta, homologacion, produccion)',
                                    'sol_user' => 'Usuario secundario SOL',
                                    'sol_password' => 'Contraseña SOL (se guardará cifrada)',
                                    'certificate_path' => 'Ruta al certificado digital',
                                    'certificate_password' => 'Contraseña del certificado (se guardará cifrada)',
                                    'send_automatically' => 'Si los comprobantes se envían automáticamente (true/false)',
                                    'generate_pdf' => 'Si se generan PDFs automáticamente (true/false)',
                                    'igv_percent' => 'Porcentaje de IGV (18.00)',
                                    default => null,
                                };
                            })
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->password(function ($record) {
                                // Mostrar como contraseña para campos sensibles
                                return in_array($record->key, ['sol_password', 'certificate_password']);
                            })
                            ->dehydrateStateUsing(function ($state, $record) {
                                // Cifrar valores sensibles
                                if (in_array($record->key, ['sol_password', 'certificate_password']) && $state) {
                                    return Crypt::encryptString($state);
                                }
                                return $state;
                            })
                            ->afterStateHydrated(function ($component, $state, $record) {
                                // No mostrar valores cifrados en el formulario
                                if (in_array($record->key, ['sol_password', 'certificate_password'])) {
                                    $component->state('');
                                }
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
                                    <p>Esta información es utilizada para la conexión con SUNAT para la emisión de comprobantes electrónicos.</p>
                                    <p class="mt-2">Las contraseñas se almacenan cifradas en la base de datos por seguridad.</p>
                                    <p class="mt-2 text-amber-500 font-medium">Importante: En entorno de producción, asegúrese de utilizar credenciales reales y un certificado digital válido.</p>
                                </div>
                            ')),

                        Forms\Components\Placeholder::make('default')
                            ->label('Valor por Defecto')
                            ->content(function ($record) {
                                if (in_array($record->key, ['sol_password', 'certificate_password'])) {
                                    return '********';
                                }
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
            'index' => Pages\ManageElectronicBillingConfig::route('/'),
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
                            'soap_type' => 'Tipo de Conexión SOAP',
                            'environment' => 'Entorno',
                            'sol_user' => 'Usuario SOL',
                            'sol_password' => 'Contraseña SOL',
                            'certificate_path' => 'Ruta del Certificado',
                            'certificate_password' => 'Contraseña del Certificado',
                            'send_automatically' => 'Enviar Automáticamente',
                            'generate_pdf' => 'Generar PDF',
                            'igv_percent' => 'Porcentaje de IGV',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if (in_array($record->key, ['sol_password', 'certificate_password'])) {
                            return '********';
                        }
                        return $state;
                    })
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
