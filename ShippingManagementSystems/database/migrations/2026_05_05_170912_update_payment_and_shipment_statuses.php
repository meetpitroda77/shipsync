<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_status', [
                'pending',
                'processing',
                'paid',
                'failed'
            ])->default('pending')->change();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('status', [
                'created',
                'pending_assigned',
                'pending_payment',
                'processing_payment',
                'assigned',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed_delivery',
                'delayed',
                'canceled'
            ])->default('created')->change();
        });
    }
};
