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
        Schema::table('transactions', function (Blueprint $table) {
            // Add composite indexes for optimized queries with millions of rows
            // These indexes help when querying transactions by sender/receiver with time ordering
            $table->index(['sender_id', 'created_at'], 'idx_sender_created');
            $table->index(['receiver_id', 'created_at'], 'idx_receiver_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_sender_created');
            $table->dropIndex('idx_receiver_created');
        });
    }
};
