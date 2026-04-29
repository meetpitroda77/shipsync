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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id')->unique();

            $table->string('sender_name');
            $table->string('sender_phone');
            $table->foreignId('sender_address_id')->constrained('addresses')->cascadeOnDelete();

            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->foreignId('receiver_address_id')->nullable()->constrained('addresses')->nullOnDelete();

            $table->string('package_type');
            $table->text('notes')->nullable();

            $table->enum('delivery_method', ['standard', 'express'])->default('standard');

            $table->enum('status', [
                'created',
                'pending_assigned',
                'pending_payment',
                'assigned',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed_delivery',
                'delayed',
                'canceled'
            ])->default('created');

            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->string('courier_company')->nullable();
            $table->enum('shipping_mode', ['air', 'surface', 'sea'])->default('surface');

            $table->timestamps();

            $table->index(['status']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
