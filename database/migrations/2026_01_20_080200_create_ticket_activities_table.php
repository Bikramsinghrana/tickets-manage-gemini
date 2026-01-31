<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Activity log for audit trail
     */
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('ticket_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->string('action', 50); // created, assigned, status_changed, commented, etc.
            $table->string('field_changed', 50)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
