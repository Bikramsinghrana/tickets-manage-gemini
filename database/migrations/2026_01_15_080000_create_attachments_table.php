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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size'); // In bytes
            $table->string('file_path', 500);
            $table->string('disk', 50)->default('public');
            
            // Polymorphic relationship (can attach to tickets, comments, etc.)
            $table->morphs('attachable');
            
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes (morphs already creates index for attachable_type + attachable_id)
            $table->index('uploaded_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
