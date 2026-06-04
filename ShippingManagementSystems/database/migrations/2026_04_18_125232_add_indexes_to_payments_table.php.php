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
        Schema::table('payments', function (Blueprint $table) {
            // Index for filtering by user (customer)
            $table->index('user_id', 'idx_payments_user_id');

            // Index for filtering by date range
            $table->index('paid_at', 'idx_payments_paid_at');

            // Index for searching by tracking or transaction ID
            $table->index('tracking_id', 'idx_payments_tracking_id');
            $table->index('transaction_id', 'idx_payments_transaction_id');

            // Index for filtering by payment status
            $table->index('payment_status', 'idx_payments_status');

            // Optional: full-text index for search if your DB supports it (MySQL example)
            // $table->fullText(['tracking_id', 'transaction_id', 'payment_status'], 'ft_payments_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_user_id');
            $table->dropIndex('idx_payments_paid_at');
            $table->dropIndex('idx_payments_tracking_id');
            $table->dropIndex('idx_payments_transaction_id');
            $table->dropIndex('idx_payments_status');

        });
    }
};