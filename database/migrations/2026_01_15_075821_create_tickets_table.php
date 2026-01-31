<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique(); // TKT-2026-00001
            $table->string('title', 255);
            $table->text('description');
            
            // Status: assigned, in_process, completed, on_hold, cancelled
            $table->string('status', 20)->default('assigned');
            
            // Priority: low, medium, high, urgent
            $table->string('priority', 20)->default('medium');
            
            // Foreign keys
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
            
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->foreignId('assigned_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Timestamps for workflow tracking
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Estimated and actual hours
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            
            // Additional fields
            $table->text('resolution_notes')->nullable();
            $table->unsignedTinyInteger('satisfaction_rating')->nullable(); // 1-5
            
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for performance
            $table->index('ticket_number');
            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
            $table->index('due_date');
            $table->index(['status', 'priority']);
            $table->index(['status', 'assigned_to']);
            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['created_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
