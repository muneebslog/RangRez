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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('receipt_path')->nullable();
            $table->timestamp('spent_at');
            $table->timestamps();

            $table->index(['event_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
