<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Identificación')
                    ->description('Datos de identificación del cliente')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options(Customer::DOCUMENT_TYPES)
                            ->required()
                            ->default('DNI')
                            ->reactive(),

                        Forms\Components\TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(15)
                            ->placeholder(fn (callable $get) =>
                                $get('document_type') === 'DNI'
                                    ? '12345678'
                                    : '20123456789'
                            )
                            ->rules([
                                fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $docType = $get('document_type');

                                    if ($docType === 'DNI' && strlen($value) !== 8) {
                                        $fail('El DNI debe tener 8 dígitos.');
                                    } elseif ($docType === 'RUC' && strlen($value) !== 11) {
                                        $fail('El RUC debe tener 11 dígitos.');
                                    }
                                },
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre/Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(fn (callable $get) =>
                                $get('document_type') === 'DNI'
                                    ? 'Nombre Completo'
                                    : 'Razón Social de la Empresa'
                            )
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Información de Contacto')
                    ->description('Datos para contactar al cliente')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('999-999-999')
                            ->suffixIcon('heroicon-m-phone'),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('cliente@ejemplo.com')
                            ->suffixIcon('heroicon-m-envelope'),

                        Forms\Components\Textarea::make('address')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->placeholder('Dirección completa del cliente')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('address_references')
                            ->label('Referencias de Dirección')
                            ->maxLength(255)
                            ->placeholder('Detalles adicionales para ubicar la dirección')
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Información Fiscal')
                    ->description('Datos fiscales y de facturación')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Toggle::make('tax_validated')
                            ->label('Validación Fiscal')
                            ->helperText('Indica si los datos fiscales del cliente han sido validados')
                            ->required()
                            ->default(false)
                            ->onIcon('heroicon-s-check-badge')
                            ->offIcon('heroicon-s-x-mark'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre/Razón Social')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('formattedDocument')
                    ->label('Documento')
                    ->searchable(['document_type', 'document_number'])
                    ->sortable(['document_type', 'document_number'])
                    ->copyable()
                    ->icon('heroicon-o-identification'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->copyable(),

                Tables\Columns\TextColumn::make('fullAddress')
                    ->label('Dirección')
                    ->searchable(['address', 'address_references'])
                    ->limit(30)
                    ->tooltip(fn (Customer $record): ?string => $record->fullAddress)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('tax_validated')
                    ->label('Validado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Tipo de Documento')
                    ->options(Customer::DOCUMENT_TYPES),

                Tables\Filters\TernaryFilter::make('tax_validated')
                    ->label('Estado de Validación')
                    ->placeholder('Todos los clientes')
                    ->trueLabel('Validados')
                    ->falseLabel('No validados'),

                Tables\Filters\TrashedFilter::make()
                    ->label('Mostrar eliminados'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->color('gray'),
                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->color('danger'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Restaurar'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Acciones'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restaurar Seleccionados'),
                    Tables\Actions\BulkAction::make('marcar_validados')
                        ->label('Marcar como Validados')
                        ->icon('heroicon-o-check-badge')
                        ->action(function (Collection $records): void {
                            $records->each(function (Customer $record): void {
                                $record->update(['tax_validated' => true]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading('No hay clientes registrados')
            ->emptyStateDescription('Registra tu primer cliente para comenzar a gestionar tus ventas.')
            ->emptyStateActions([
                Tables\Actions\Action::make('crear')
                    ->label('Crear Cliente')
                    ->url(route('filament.admin.resources.customers.create'))
                    ->icon('heroicon-m-plus')
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return '👥 Clientes';
    }
}
