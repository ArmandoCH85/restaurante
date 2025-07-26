<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the enum to include the new values
        DB::statement("ALTER TABLE tables MODIFY COLUMN status ENUM('available', 'occupied', 'reserved', 'maintenance', 'pending_payment', 'prebill') DEFAULT 'available'");
    }

    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE tables MODIFY COLUMN status ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available'");
    }
};