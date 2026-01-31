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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->boolean('is_internal')->default(false); // Internal notes only visible to staff
            
            $table->foreignId('ticket_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('comments')
                  ->cascadeOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
