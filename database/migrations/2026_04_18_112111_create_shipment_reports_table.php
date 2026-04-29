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
        Schema::create('shipment_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();

            $table->integer('total_shipments')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);

            $table->integer('pending_assigned')->default(0);
            $table->integer('pending_payment')->default(0);
            $table->integer('assigned')->default(0);
            $table->integer('picked_up')->default(0);
            $table->integer('in_transit')->default(0);
            $table->integer('out_for_delivery')->default(0);
            $table->integer('delivered')->default(0);
            $table->integer('failed_delivery')->default(0);
            $table->integer('delayed')->default(0);
            $table->integer('canceled')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_reports');
    }
};
