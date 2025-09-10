<?php

namespace App\Filament\Resources\SummaryResource\Pages;

use App\Filament\Resources\SummaryResource;
use App\Models\Summary;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateSummary extends CreateRecord
{
    protected static string $resource = SummaryResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Verificar si ya existe un resumen para esta fecha
        $existingSummary = Summary::where('fecha_referencia', $data['fecha_referencia'])->first();
        
        if ($existingSummary) {
            throw ValidationException::withMessages([
                'fecha_referencia' => "Ya existe un resumen para la fecha {$data['fecha_referencia']} (ID: {$existingSummary->id}, Correlativo: {$existingSummary->correlativo}). No se pueden crear resúmenes duplicados para la misma fecha."
            ]);
        }
        
        // Verificar que existan boletas para la fecha seleccionada
        $boletasCount = Invoice::where('invoice_type', 'receipt')
            ->whereDate('issue_date', $data['fecha_referencia'])
            ->where('sunat_status', 'ACEPTADO')
            ->count();
            
        if ($boletasCount === 0) {
            throw ValidationException::withMessages([
                'fecha_referencia' => "No se encontraron boletas ACEPTADAS para la fecha {$data['fecha_referencia']}. Verifique que las boletas hayan sido enviadas y aceptadas por SUNAT antes de crear el resumen."
            ]);
        }
        
        // Generar correlativo automáticamente
        $data['correlativo'] = Summary::generateCorrelativo($data['fecha_referencia']);
        
        // Establecer fecha de generación si no está presente
        if (!isset($data['fecha_generacion'])) {
            $data['fecha_generacion'] = now()->format('Y-m-d');
        }
        
        // Establecer estado por defecto
        if (!isset($data['status'])) {
            $data['status'] = Summary::STATUS_PENDING;
        }
        
        // Calcular y establecer contadores iniciales
        $boletas = Invoice::where('invoice_type', 'receipt')
            ->whereDate('issue_date', $data['fecha_referencia'])
            ->where('sunat_status', 'ACEPTADO')
            ->get();
            
        $data['receipts_count'] = $boletas->count();
        $data['total_amount'] = $boletas->sum('total');
        
        return $data;
    }
}
