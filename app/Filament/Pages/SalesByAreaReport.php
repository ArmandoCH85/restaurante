<?php

namespace App\Filament\Pages;

use App\Models\Area;
use App\Services\SalesByAreaReportService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesByAreaReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Ventas por Area';

    protected static ?string $title = 'Reporte: Ventas por Area';

    protected static ?string $navigationGroup = 'Reportes y Analisis';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'reportes/ventas-por-area';

    protected static string $view = 'filament.pages.sales-by-area-report';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public ?int $areaId = null;

    public ?string $groupByMode = 'month';

    public array $drilldownRows = [];

    public ?string $drilldownAreaName = null;

    public ?string $drilldownPeriod = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');

        $this->form->fill([
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'areaId' => $this->areaId,
            'groupByMode' => $this->groupByMode,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fromDate')
                    ->label('Desde')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('toDate')
                    ->label('Hasta')
                    ->required()
                    ->native(false),

                Forms\Components\Select::make('areaId')
                    ->label('Area')
                    ->options(fn (): array => Area::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas las areas'),

                Forms\Components\Radio::make('groupByMode')
                    ->label('Agrupar')
                    ->options([
                        'day' => 'Por dia',
                        'month' => 'Por mes',
                    ])
                    ->default('month')
                    ->inline()
                    ->required(),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => app(SalesByAreaReportService::class)->aggregateQuery(
                from: $this->fromDate,
                to: $this->toDate,
                areaId: $this->areaId,
                groupBy: $this->groupByMode
            ))
            ->columns([
                Tables\Columns\TextColumn::make('area_name')
                    ->label('Area')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period_label')
                    ->label('Periodo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('units_sold')
                    ->label('Unidades vendidas')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_sold')
                    ->label('Neto vendido')
                    ->money('PEN')
                    ->sortable(),
            ])
            ->defaultSort('period_key', 'asc')
            ->actions([
                Tables\Actions\Action::make('view_products')
                    ->label('Ver productos')
                    ->icon('heroicon-o-list-bullet')
                    ->action(function (object $record): void {
                        $rows = app(SalesByAreaReportService::class)
                            ->drillDownQuery(
                                from: $this->fromDate,
                                to: $this->toDate,
                                areaId: (int) $record->area_id,
                                groupBy: $this->groupByMode,
                                period: (string) $record->period_key,
                            )
                            ->get();

                        $this->drilldownRows = $rows
                            ->map(fn (object $row): array => [
                                'product_code' => $row->product_code,
                                'product_name' => $row->product_name,
                                'units_sold' => (float) $row->units_sold,
                                'net_sold' => (float) $row->net_sold,
                            ])
                            ->values()
                            ->all();

                        $this->drilldownAreaName = (string) $record->area_name;
                        $this->drilldownPeriod = (string) $record->period_label;
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply_filters')
                ->label('Aplicar filtros')
                ->icon('heroicon-o-funnel')
                ->action(fn () => $this->applyFilters()),
            Action::make('export_csv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
            Action::make('export_xlsx')
                ->label('Exportar XLSX')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->exportXlsx()),
        ];
    }

    public function applyFilters(): void
    {
        $this->validate([
            'fromDate' => ['required', 'date'],
            'toDate' => ['required', 'date', 'after_or_equal:fromDate'],
            'areaId' => ['nullable', 'integer', 'exists:areas,id'],
            'groupByMode' => ['required', 'in:day,month'],
        ]);

        $this->drilldownRows = [];
        $this->drilldownAreaName = null;
        $this->drilldownPeriod = null;

        $this->resetTable();
    }

    public function exportCsv(): StreamedResponse
    {
        $service = app(SalesByAreaReportService::class);

        $rows = $service
            ->aggregateQuery($this->fromDate, $this->toDate, $this->areaId, $this->groupByMode)
            ->get();

        $filename = 'ventas_por_area_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows, $service): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Area', 'Periodo', 'Unidades vendidas', 'Neto vendido']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->area_name,
                    $row->period_label,
                    (float) $row->units_sold,
                    (float) $row->net_sold,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Productos vendidos (detalle)']);
            fputcsv($handle, []);

            foreach ($rows as $row) {
                $products = $service->drillDownQuery(
                    from: (string) $this->fromDate,
                    to: (string) $this->toDate,
                    areaId: (int) $row->area_id,
                    groupBy: (string) $this->groupByMode,
                    period: (string) $row->period_key,
                )->get();

                fputcsv($handle, ["Area: {$row->area_name} | Periodo: {$row->period_label}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['Producto', 'Unidades', 'Neto']);

                if ($products->isEmpty()) {
                    fputcsv($handle, ['Sin productos', '', '']);
                } else {
                    foreach ($products as $product) {
                        $code = trim((string) ($product->product_code ?? ''));
                        $name = trim((string) $product->product_name);
                        $productLabel = $code !== '' ? "{$code} - {$name}" : $name;

                        fputcsv($handle, [
                            $productLabel,
                            number_format((float) $product->units_sold, 3, '.', ''),
                            'S/ '.number_format((float) $product->net_sold, 2, '.', ''),
                        ]);
                    }
                }

                fputcsv($handle, []);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportXlsx(): StreamedResponse
    {
        $service = app(SalesByAreaReportService::class);

        $rows = $service
            ->aggregateQuery($this->fromDate, $this->toDate, $this->areaId, $this->groupByMode)
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ventas por Area');

        $sheet->setCellValue('A1', 'Area');
        $sheet->setCellValue('B1', 'Periodo');
        $sheet->setCellValue('C1', 'Unidades vendidas');
        $sheet->setCellValue('D1', 'Neto vendido');

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$rowNumber, $row->area_name);
            $sheet->setCellValue('B'.$rowNumber, $row->period_label);
            $sheet->setCellValue('C'.$rowNumber, (float) $row->units_sold);
            $sheet->setCellValue('D'.$rowNumber, (float) $row->net_sold);
            $rowNumber++;
        }

        $detailSheet = $spreadsheet->createSheet();
        $detailSheet->setTitle('Productos vendidos');

        $detailRow = 1;
        $detailSectionRows = [];
        $detailHeaderRows = [];

        foreach ($rows as $row) {
            $products = $service->drillDownQuery(
                from: (string) $this->fromDate,
                to: (string) $this->toDate,
                areaId: (int) $row->area_id,
                groupBy: (string) $this->groupByMode,
                period: (string) $row->period_key,
            )->get();

            $detailSheet->setCellValue('A'.$detailRow, "Area: {$row->area_name} | Periodo: {$row->period_label}");
            $detailSectionRows[] = $detailRow;
            $detailRow += 2;

            $detailSheet->setCellValue('A'.$detailRow, 'Producto');
            $detailSheet->setCellValue('B'.$detailRow, 'Unidades');
            $detailSheet->setCellValue('C'.$detailRow, 'Neto');
            $detailHeaderRows[] = $detailRow;
            $detailRow++;

            if ($products->isEmpty()) {
                $detailSheet->setCellValue('A'.$detailRow, 'Sin productos');
                $detailRow += 2;
                continue;
            }

            foreach ($products as $product) {
                $code = trim((string) ($product->product_code ?? ''));
                $name = trim((string) $product->product_name);
                $productLabel = $code !== '' ? "{$code} - {$name}" : $name;

                $detailSheet->setCellValue('A'.$detailRow, $productLabel);
                $detailSheet->setCellValue('B'.$detailRow, number_format((float) $product->units_sold, 3, '.', ''));
                $detailSheet->setCellValue('C'.$detailRow, 'S/ '.number_format((float) $product->net_sold, 2, '.', ''));
                $detailRow++;
            }

            $detailRow++;
        }

        foreach ($detailSectionRows as $rowIndex) {
            $detailSheet->getStyle('A'.$rowIndex.':C'.$rowIndex)->getFont()->setBold(true);
        }

        foreach ($detailHeaderRows as $rowIndex) {
            $detailSheet->getStyle('A'.$rowIndex.':C'.$rowIndex)->getFont()->setBold(true);
        }

        foreach (['A', 'B', 'C', 'D'] as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        foreach (['A', 'B', 'C'] as $column) {
            $detailSheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'ventas_por_area_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
