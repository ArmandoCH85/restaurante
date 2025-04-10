<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    /**
     * Display a listing of the tables.
     */
    public function index()
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $tables = Table::orderBy('number')->get();
        return view('tables.maintenance', ['tables' => $tables]);
    }

    /**
     * Show the form for creating a new table.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        return view('tables.create');
    }

    /**
     * Store a newly created table in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:tables,number',
            'capacity' => 'required|integer|min:1|max:20',
            'location' => 'required|in:interior,exterior,bar,private',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'qr_code' => 'nullable|string',
        ]);

        Table::create($validated);

        return redirect()->route('tables.maintenance')->with('success', 'Mesa creada correctamente');
    }

    /**
     * Show the form for editing the specified table.
     */
    public function edit(Table $table)
    {
        if (!Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        return view('tables.edit', ['table' => $table]);
    }

    /**
     * Update the specified table in storage.
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'number' => 'required|integer|min:1|unique:tables,number,' . $table->id,
            'capacity' => 'required|integer|min:1|max:20',
            'location' => 'required|in:interior,exterior,bar,private',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'qr_code' => 'nullable|string',
        ]);

        $table->update($validated);

        return redirect()->route('tables.maintenance')->with('success', 'Mesa actualizada correctamente');
    }

    /**
     * Update the status of the specified table.
     */
    public function updateStatus(Request $request, Table $table)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        $table->status = $validated['status'];
        $table->save();

        return redirect()->route('tables.maintenance')->with('success', 'Estado de mesa actualizado correctamente');
    }

    /**
     * Remove the specified table from storage.
     */
    public function destroy(Table $table)
    {
        $table->delete();
        return redirect()->route('tables.maintenance')->with('success', 'Mesa eliminada correctamente');
    }
}
