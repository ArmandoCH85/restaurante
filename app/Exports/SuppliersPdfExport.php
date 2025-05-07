<?php

namespace App\Exports;

use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;

class SuppliersPdfExport
{
    protected $active;

    public function __construct($active = null)
    {
        $this->active = $active;
    }

    public function view(): View
    {
        $query = Supplier::query();
        
        if ($this->active !== null) {
            $query->where('active', $this->active);
        }
        
        return view('exports.suppliers', [
            'suppliers' => $query->get()
        ]);
    }

    public function download()
    {
        return Pdf::loadView('exports.suppliers', ['suppliers' => $this->view()->suppliers])
            ->download('proveedores.pdf');
    }
}
