<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\CashRegister;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class ListCashRegisters extends ListRecords
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        $openRegister = CashRegister::getOpenRegister();

        return [
            Actions\CreateAction::make()
                ->label('Abrir Nueva Caja')
                ->icon('heroicon-m-calculator')
                ->color('success')
                ->button()
                ->visible(function () {
                    // Solo mostrar el botón si no hay una caja abierta
                    return !CashRegister::hasOpenRegister();
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('✅ Caja abierta exitosamente')
                        ->body('La caja ha sido abierta correctamente y está lista para operar.')
                        ->duration(5000)
                ),

            Actions\Action::make('closeCashRegister')
                ->label('Cerrar Caja Actual')
                ->icon('heroicon-m-lock-closed')
                ->color('warning')
                ->button()
                ->visible(fn () => CashRegister::hasOpenRegister())
                ->url(fn () => $openRegister ? CashRegisterResource::getUrl('edit', ['record' => $openRegister]) : null)
                ->extraAttributes([
                    'class' => 'animate-pulse shadow-lg',
                ])
                ->tooltip('Cerrar la caja registradora actualmente abierta'),

            Actions\Action::make('viewActiveCashRegister')
                ->label('Ver Caja Actual')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn () => CashRegister::hasOpenRegister())
                ->url(fn () => $openRegister ? CashRegisterResource::getUrl('view', ['record' => $openRegister]) : null)
                ->tooltip('Ver detalles de la caja registradora actualmente abierta'),

            Actions\Action::make('exportCashRegisters')
                ->label('Exportar Cajas')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Obtener todos los registros
                    $cashRegisters = \App\Models\CashRegister::with(['openedBy', 'closedBy', 'approvedBy'])->get();

                    // Crear el contenido CSV
                    $headers = [
                        'ID',
                        'Fecha de Apertura',
                        'Fecha de Cierre',
                        'Abierto por',
                        'Cerrado por',
                        'Monto Inicial',
                        'Ventas en Efectivo',
                        'Ventas con Tarjeta',
                        'Otras Ventas',
                        'Ventas Totales',
                        'Monto Esperado',
                        'Monto Final',
                        'Diferencia',
                        'Estado',
                        'Aprobado',
                        'Aprobado por',
                        'Fecha de Aprobación',
                        'Observaciones',
                    ];

                    $callback = function() use ($cashRegisters, $headers) {
                        $file = fopen('php://output', 'w');

                        // Escribir encabezados
                        fputcsv($file, $headers);

                        // Escribir datos
                        foreach ($cashRegisters as $register) {
                            $row = [
                                $register->id,
                                $register->opening_datetime ? $register->opening_datetime->format('d/m/Y H:i') : '',
                                $register->closing_datetime ? $register->closing_datetime->format('d/m/Y H:i') : 'No cerrada',
                                $register->openedBy ? $register->openedBy->name : '',
                                $register->closedBy ? $register->closedBy->name : '',
                                number_format($register->opening_amount, 2),
                                number_format($register->cash_sales, 2),
                                number_format($register->card_sales, 2),
                                number_format($register->other_sales, 2),
                                number_format($register->total_sales, 2),
                                number_format($register->expected_amount, 2),
                                number_format($register->actual_amount, 2),
                                number_format($register->difference, 2),
                                $register->status,
                                $register->is_approved ? 'Sí' : 'No',
                                $register->approvedBy ? $register->approvedBy->name : '',
                                $register->approval_datetime ? $register->approval_datetime->format('d/m/Y H:i') : '',
                                $register->observations,
                            ];

                            fputcsv($file, $row);
                        }

                        fclose($file);
                    };

                    $filename = 'operaciones-caja-' . now()->format('Y-m-d') . '.csv';

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Exportación exitosa')
                        ->body('Se han exportado ' . $cashRegisters->count() . ' registros.')
                        ->send();

                    return \Illuminate\Support\Facades\Response::stream($callback, 200, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                })
                ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager']))
                ->tooltip('Exportar datos de operaciones de caja a CSV'),
        ];
    }

    public function getHeading(): string
    {
        $openRegister = CashRegister::getOpenRegister();

        if ($openRegister) {
            return 'Apertura y Cierre de Caja - Hay una caja abierta (ID: ' . $openRegister->id . ')';
        }

        return 'Apertura y Cierre de Caja';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ListCashRegisters\Widgets\ActiveCashRegisterStats::class,
        ];
    }
}
