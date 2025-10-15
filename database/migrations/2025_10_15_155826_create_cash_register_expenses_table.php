<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create cash_register_expenses table for detailed expense tracking
 * 
 * Purpose: Creates a dedicated table to store itemized expenses for each cash register session.
 * This table works in conjunction with the cash_registers table to provide detailed expense tracking
 * when the expense_method is set to 'detailed'.
 * 
 * Table Structure:
 * - id: Primary key
 * - cash_register_id: Foreign key linking to cash_registers table
 * - concept: Description of the expense (e.g., "Office supplies", "Maintenance", etc.)
 * - amount: Monetary amount of the expense
 * - notes: Additional details or observations about the expense
 * - timestamps: Created and updated timestamps
 * 
 * Business Use Cases:
 * - Track individual expense items per cash register session
 * - Categorize expenses by concept for better reporting
 * - Maintain detailed audit trail of all expenses
 * - Support expense analysis and budgeting
 * 
 * Relationships:
 * - Belongs to cash_registers (many-to-one)
 * - Cascade delete when cash register is deleted
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
     * Creates the cash_register_expenses table with the following structure:
     * - Primary key (id)
     * - Foreign key to cash_registers table with cascade delete
     * - Expense concept/description field
     * - Decimal amount field with precision for currency
     * - Optional notes field for additional details
     * - Standard Laravel timestamps
     */
    public function up(): void
    {
        Schema::create('cash_register_expenses', function (Blueprint $table) {
            // Primary key
            $table->id()->comment('Primary key for expense records');
            
            // Foreign key to cash_registers table with cascade delete
            $table->foreignId('cash_register_id')
                  ->constrained('cash_registers')
                  ->onDelete('cascade')
                  ->comment('References the cash register session this expense belongs to');
            
            // Expense concept/description
            $table->string('concept')
                  ->comment('Description or category of the expense (e.g., Office supplies, Maintenance)');
            
            // Expense amount with precision for currency
            $table->decimal('amount', 12, 2)
                  ->comment('Monetary amount of the expense with 2 decimal precision');
            
            // Optional notes for additional details
            $table->text('notes')->nullable()
                  ->comment('Additional notes or observations about this expense');
            
            // Standard Laravel timestamps
            $table->timestamps();
            
            // Add index for better query performance
            $table->index(['cash_register_id', 'created_at'], 'idx_cash_register_expenses_session_date');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Drops the cash_register_expenses table and all its data
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_expenses');
    }
};
