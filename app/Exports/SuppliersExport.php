<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $active;

    public function __construct($active = null)
    {
        $this->active = $active;
    }

    public function query()
    {
        $query = Supplier::query();
        
        if ($this->active !== null) {
            $query->where('active', $this->active);
        }
        
        return $query;
    }

    public function headings(): array
    {
        return [
            'RUC',
            'Razón Social',
            'Teléfono',
            'Correo',
            'Estado'
        ];
    }

    public function map($supplier): array
    {
        return [
            $supplier->tax_id,
            $supplier->business_name,
            $supplier->phone,
            $supplier->email,
            $supplier->active ? 'Activo' : 'Inactivo'
        ];
    }
}
