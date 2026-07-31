<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('paid_by_company')->default(true)->after('spent_by_user_id');
            $table->timestamp('repaid_at')->nullable()->after('spent_at');
            $table->index(['event_id', 'paid_by_company', 'repaid_at']);
        });

        DB::table('expenses')
            ->whereNotNull('spent_by_user_id')
            ->update(['paid_by_company' => false]);

        Schema::dropIfExists('investments');

        DB::table('users')
            ->where('role', 'investor')
            ->update(['role' => 'check_in']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('share_percent', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestamp('invested_at');
            $table->timestamps();

            $table->index(['event_id', 'user_id']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'paid_by_company', 'repaid_at']);
            $table->dropColumn(['paid_by_company', 'repaid_at']);
        });
    }
};
