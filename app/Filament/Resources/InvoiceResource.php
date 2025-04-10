<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Facturación';

    protected static ?string $navigationLabel = 'Comprobantes';

    protected static ?string $modelLabel = 'Comprobante';

    protected static ?string $pluralModelLabel = 'Comprobantes';

    protected static ?string $slug = 'facturacion/comprobantes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Comprobante')
                    ->schema([
                        Forms\Components\Select::make('invoice_type')
                            ->label('Tipo de Comprobante')
                            ->options([
                                'invoice' => 'Factura',
                                'receipt' => 'Boleta',
                                'sales_note' => 'Nota de Venta',
                            ])
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('series')
                            ->label('Serie')
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('number')
                            ->label('Número')
                            ->disabled()
                            ->required(),

                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('tax_authority_status')
                            ->label('Estado SUNAT')
                            ->options([
                                'pending' => 'Pendiente',
                                'accepted' => 'Aceptado',
                                'rejected' => 'Rechazado',
                                'voided' => 'Anulado',
                            ])
                            ->disabled()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Cliente')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->disabled()
                            ->searchable()
                            ->preload(),
                    ]),

                Forms\Components\Section::make('Importes')
                    ->schema([
                        Forms\Components\TextInput::make('taxable_amount')
                            ->label('Base Imponible')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('tax')
                            ->label('IGV')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),
                    ])->columns(3),

                Forms\Components\Section::make('Anulación')
                    ->schema([
                        Forms\Components\DatePicker::make('voided_date')
                            ->label('Fecha de Anulación')
                            ->disabled(),

                        Forms\Components\Textarea::make('voided_reason')
                            ->label('Motivo de Anulación')
                            ->disabled()
                            ->visible(fn (Invoice $record): bool => $record->tax_authority_status === 'voided'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('formattedNumber')
                    ->label('Número')
                    ->searchable(['series', 'number'])
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('invoice_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state, $record): string =>
                        // Si la serie comienza con 'NV', es una Nota de Venta
                        str_starts_with($record->series, 'NV')
                            ? 'Nota de Venta'
                            : match ($state) {
                                'invoice' => 'Factura',
                                'receipt' => 'Boleta',
                                default => 'Nota de Venta',
                            }
                    )
                    ->colors([
                        'primary' => 'invoice',
                        'success' => fn ($state, $record) => $state === 'receipt' && !str_starts_with($record->series, 'NV'),
                        'info' => fn ($state, $record) => str_starts_with($record->series, 'NV'),
                        'warning' => fn ($state, $record) => $state !== 'invoice' && $state !== 'receipt' && !str_starts_with($record->series, 'NV'),
                    ]),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Fecha Emisión')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('tax_authority_status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                        'voided' => 'Anulado',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                        'gray' => 'voided',
                    ]),

                Tables\Columns\TextColumn::make('voided_date')
                    ->label('Fecha Anulación')
                    ->date('d/m/Y')
                    ->visible(fn ($livewire): bool => $livewire->getTableFilterState('tax_authority_status') === 'voided')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('invoice_type')
                    ->label('Tipo')
                    ->options([
                        'invoice' => 'Factura',
                        'receipt' => 'Boleta',
                        'sales_note' => 'Nota de Venta',
                    ]),
                Tables\Filters\SelectFilter::make('tax_authority_status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                        'voided' => 'Anulado',
                    ]),
                Tables\Filters\Filter::make('issue_date')
                    ->form([
                        Forms\Components\DatePicker::make('issued_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('issued_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['issued_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['issued_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('void')
                    ->label('Anular')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Invoice $record): bool => $record->canBeVoided())
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo de Anulación')
                            ->required()
                            ->minLength(5)
                            ->maxLength(255),
                        Forms\Components\Checkbox::make('confirm')
                            ->label('Confirmo que deseo anular este comprobante y entiendo que esta acción no se puede deshacer.')
                            ->required()
                            ->default(false),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Anular Comprobante')
                    ->modalDescription('Esta acción es irreversible. El comprobante quedará registrado como anulado tanto en el sistema como en SUNAT.')
                    ->modalSubmitActionLabel('Anular Comprobante')
                    ->action(function (Invoice $record, array $data): void {
                        if (!$data['confirm']) {
                            Notification::make()
                                ->title('Debe confirmar la anulación')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (!$record->canBeVoided()) {
                            Notification::make()
                                ->title('No se puede anular este comprobante')
                                ->body('Verifique que no hayan pasado más de 7 días desde su emisión y que no haya sido anulado previamente.')
                                ->danger()
                                ->send();
                            return;
                        }

                        if ($record->void($data['reason'])) {
                            Notification::make()
                                ->title('Comprobante anulado correctamente')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error al anular el comprobante')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Invoice $record): string => route('pos.invoice.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No bulk actions for invoices
                ]),
            ])
            ->defaultSort('issue_date', 'desc');
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
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('tax_authority_status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
