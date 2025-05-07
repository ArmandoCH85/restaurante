<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableResetController extends Controller
{
    /**
     * Reset all tables to available status
     */
    public function resetAllTables()
    {
        try {
            $count = Table::count();
            $updated = Table::query()->update(['status' => 'available']);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updated} of {$count} tables to 'available' status."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error updating tables: " . $e->getMessage()
            ], 500);
        }
    }
}
