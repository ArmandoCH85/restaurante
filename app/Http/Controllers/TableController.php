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

        $oldStatus = $table->status;
        $table->status = $validated['status'];

        // Si la mesa se está ocupando, registrar el tiempo
        if ($validated['status'] === 'occupied') {
            $table->occupied_at = now();
        } else if ($oldStatus === 'occupied' && $validated['status'] !== 'occupied') {
            // Si la mesa estaba ocupada y ahora cambia a otro estado, limpiar el tiempo
            $table->occupied_at = null;
        }

        $table->save();

        // Si es una solicitud AJAX, devolver una respuesta JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estado de mesa actualizado correctamente',
                'table' => $table
            ]);
        }

        // Si es una solicitud normal, redirigir
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
