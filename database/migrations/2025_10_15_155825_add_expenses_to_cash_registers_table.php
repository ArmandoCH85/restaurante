<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add expenses tracking functionality to cash_registers table
 * 
 * Purpose: Extends the cash_registers table to support expense management
 * allowing restaurants to track both detailed and summary expenses per cash register session.
 * 
 * Features added:
 * - total_expenses: Stores the total amount of expenses for the cash register session
 * - expenses_notes: Allows storing general notes about expenses
 * - expense_method: Defines whether expenses are tracked in detail or as a total amount
 * 
 * Business Impact: Enables complete financial tracking including both income and expenses
 * for accurate profit calculation and financial reporting.
 * 
 * Dependencies: Requires cash_registers table to exist
 * Related: Works with create_cash_register_expenses_table migration for detailed expense tracking
 * 
 * @author Restaurant Management System
 * @version 1.0
 * @date 2025-10-15
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds expense tracking columns to the cash_registers table:
     * - total_expenses: Decimal field for storing total expense amount
     * - expenses_notes: Text field for general expense notes
     * - expense_method: Enum to define tracking method (detailed/total)
     */
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            // Total expenses amount for this cash register session
            $table->decimal('total_expenses', 12, 2)->nullable()->after('total_sales')
                  ->comment('Total amount of expenses for this cash register session');
            
            // General notes about expenses
            $table->text('expenses_notes')->nullable()->after('total_expenses')
                  ->comment('General notes or observations about expenses');
            
            // Method used to track expenses (detailed breakdown or total amount)
            $table->enum('expense_method', ['detailed', 'total'])->nullable()->after('expenses_notes')
                  ->comment('Method used to track expenses: detailed (itemized) or total (lump sum)');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Removes the expense tracking columns from cash_registers table
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn(['total_expenses', 'expenses_notes', 'expense_method']);
        });
    }
};
