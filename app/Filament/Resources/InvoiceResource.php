<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Services\SunatService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use App\Models\CompanyConfig;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = '📄 Facturación y Ventas';

    protected static ?string $navigationLabel = 'Comprobantes';

    protected static ?string $modelLabel = 'Comprobante';

    protected static ?string $pluralModelLabel = 'Comprobantes';

    protected static ?string $slug = 'facturacion/comprobantes';

    protected static ?int $navigationSort = 1;

    /**
     * OPTIMIZACIÓN: Agregar eager loading para evitar N+1 queries
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'employee', 'order.table', 'details.product']);
    }

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
                    ->formatStateUsing(function ($record): string {
                        // Para Notas de Venta, mostrar el estado interno
                        if ($record->invoice_type === 'sales_note' || str_starts_with($record->series, 'NV')) {
                            return match ($record->tax_authority_status) {
                                'pending' => 'Registrado',
                                'accepted' => 'Aceptado',
                                'rejected' => 'Rechazado',
                                'voided' => 'Anulado',
                                default => 'Registrado',
                            };
                        }

                        // Para Boletas y Facturas, mostrar el estado SUNAT
                        return match ($record->sunat_status) {
                            'PENDIENTE' => 'Registrado',
                            'ENVIANDO' => 'Enviando',
                            'ACEPTADO' => 'Aceptado',
                            'RECHAZADO' => 'Rechazado',
                            'ERROR' => 'Error',
                            null => 'Registrado',
                            default => 'Registrado',
                        };
                    })
                    ->colors([
                        'warning' => fn ($record) =>
                            ($record->invoice_type === 'sales_note' || str_starts_with($record->series, 'NV'))
                                ? $record->tax_authority_status === 'pending'
                                : in_array($record->sunat_status, ['PENDIENTE', null]),
                        'info' => fn ($record) => $record->sunat_status === 'ENVIANDO',
                        'success' => fn ($record) =>
                            ($record->invoice_type === 'sales_note' || str_starts_with($record->series, 'NV'))
                                ? $record->tax_authority_status === 'accepted'
                                : $record->sunat_status === 'ACEPTADO',
                        'danger' => fn ($record) =>
                            ($record->invoice_type === 'sales_note' || str_starts_with($record->series, 'NV'))
                                ? in_array($record->tax_authority_status, ['rejected'])
                                : in_array($record->sunat_status, ['RECHAZADO', 'ERROR']),
                        'gray' => fn ($record) => $record->tax_authority_status === 'voided',
                    ]),

                Tables\Columns\BadgeColumn::make('sunat_status')
                    ->label('Estado SUNAT')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'PENDIENTE' => 'Pendiente',
                        'ENVIANDO' => 'Enviando',
                        'ACEPTADO' => 'Aceptado',
                        'RECHAZADO' => 'Rechazado',
                        'ERROR' => 'Error',
                        'NO_APLICA' => 'No aplica',
                        default => 'Sin enviar',
                    })
                    ->colors([
                        'gray' => fn (?string $state): bool => $state === null || $state === 'NO_APLICA',
                        'warning' => 'PENDIENTE',
                        'info' => 'ENVIANDO',
                        'success' => 'ACEPTADO',
                        'danger' => ['RECHAZADO', 'ERROR'],
                    ])
                    ->visible(fn ($livewire): bool =>
                        // SOLO mostrar para Boletas y Facturas - NO Notas de Venta
                        in_array($livewire->getTableFilterState('invoice_type'), ['invoice', 'receipt']) ||
                        $livewire->getTableFilterState('invoice_type') === null
                    ),

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
                Action::make('send_to_sunat')
                    ->label('Enviar a SUNAT')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Invoice $record): bool =>
                        // SOLO Boletas y Facturas - NO Notas de Venta
                        in_array($record->invoice_type, ['invoice', 'receipt']) &&
                        ($record->sunat_status === 'PENDIENTE' || $record->sunat_status === null)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Enviar Comprobante a SUNAT')
                    ->modalDescription(fn (Invoice $record): string =>
                        "¿Está seguro de enviar el comprobante {$record->series}-{$record->number} a SUNAT?"
                    )
                    ->modalSubmitActionLabel('Enviar a SUNAT')
                    ->action(function (Invoice $record): void {
                        try {
                            $sunatService = new SunatService();
                            $result = $sunatService->emitirFactura($record->id);

                            if ($result['success']) {
                                Notification::make()
                                    ->title('Comprobante enviado exitosamente')
                                    ->body('El comprobante ha sido enviado y aceptado por SUNAT')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error al enviar comprobante')
                                    ->body($result['message'])
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error inesperado')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
                    ->action(function (Invoice $record) {
                        // Obtener configuración de empresa usando los métodos estáticos
                        $company = [
                            'ruc' => CompanyConfig::getRuc(),
                            'razon_social' => CompanyConfig::getRazonSocial(),
                            'nombre_comercial' => CompanyConfig::getNombreComercial(),
                            'direccion' => CompanyConfig::getDireccion(),
                            'telefono' => CompanyConfig::getTelefono(),
                            'email' => CompanyConfig::getEmail(),
                        ];

                        // Datos para el PDF
                        $data = [
                            'invoice' => $record->load(['customer', 'details.product', 'order.table']),
                            'company' => $company,
                        ];

                        // Determinar la vista según el tipo de documento
                        $view = match($record->invoice_type) {
                            'receipt' => 'pdf.receipt',
                            'sales_note' => 'pdf.sales_note',
                            default => 'pdf.invoice'
                        };

                        // Generar PDF y mostrarlo en navegador para impresión
                        $pdf = Pdf::loadHtml(Blade::render($view, $data));
                        return $pdf->stream($record->series . '-' . $record->number . '.pdf');
                    }),
                Action::make('download_xml')
                    ->label('Descargar XML')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn (Invoice $record): bool =>
                        // Solo mostrar si el comprobante tiene XML generado
                        !empty($record->xml_path) && file_exists($record->xml_path)
                    )
                    ->url(fn (Invoice $record): string => route('filament.admin.invoices.download-xml', $record))
                    ->openUrlInNewTab(),
                Action::make('download_cdr')
                    ->label('Descargar CDR')
                    ->icon('heroicon-o-document-check')
                    ->color('warning')
                    ->visible(fn (Invoice $record): bool =>
                        // Solo mostrar si el comprobante tiene CDR de SUNAT
                        !empty($record->cdr_path) && file_exists($record->cdr_path)
                    )
                    ->url(fn (Invoice $record): string => route('filament.admin.invoices.download-cdr', $record))
                    ->openUrlInNewTab(),
                Action::make('download_pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Invoice $record): string => route('filament.admin.invoices.download-pdf', $record))
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
